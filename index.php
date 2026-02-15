<?php
session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');

$DB_FILE = "database.txt";
$ADMIN_PASS = "Anhnguyendz_99";

if (!file_exists($DB_FILE)) file_put_contents($DB_FILE, "");

/* =================================================
   🔒 CHẶN TRUY CẬP SAI API
================================================= */

// ❌ chặn người dùng gọi GET ?check_key=
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_key'])) {
    http_response_code(403);
    exit("Forbidden");
}

/* =================================================
   🛰️ API AUTH (CHỈ TOOL POST)
================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_key'])) {

    // chống bot request thô
    if (empty($_SERVER['HTTP_USER_AGENT'])) exit;

    $key = trim($_POST['check_key']);
    $status = "NOT_FOUND";

    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($rows as $row) {
        $data = explode("|", $row);
        if ($data[0] === $key) {
            if (date("Y-m-d") > $data[1]) $status = "EXPIRED";
            else $status = "SUCCESS";
            break;
        }
    }

    header("Content-Type: text/plain");

    if ($status === "SUCCESS") {

        // 🔒 LUA SCRIPT ẨN
        $lua = '
print("AutoWalk Loaded")
';

        // mã hóa tránh đọc trực tiếp
        echo "AUTH_SUCCESS|" . base64_encode($lua);

    } else {
        echo "AUTH_ERR|" . $status;
    }

    exit;
}

/* =================================================
   🔐 ADMIN LOGIN
================================================= */

if (isset($_POST['login_boss'])) {
    if ($_POST['boss_pw'] === $ADMIN_PASS) {
        $_SESSION['kn_boss'] = true;
    } else {
        $error = "Sai mật khẩu!";
    }
}

/* =================================================
   🔑 CREATE / UPDATE KEY
================================================= */

if (isset($_POST['create_key']) && isset($_SESSION['kn_boss'])) {

    $name = trim($_POST['key_name']);
    $days = (int)$_POST['key_days'];

    if ($name && $days > 0) {

        $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES);
        $updated = [];
        $found = false;

        foreach ($rows as $r) {
            $x = explode("|", $r);
            if ($x[0] === $name) {
                $found = true;
                $x[1] = date('Y-m-d', strtotime("+$days days"));
            }
            $updated[] = implode("|", $x);
        }

        if (!$found) {
            $updated[] = "$name|" . date('Y-m-d', strtotime("+$days days"));
        }

        file_put_contents($DB_FILE, implode("\n", $updated));
    }
}

/* =================================================
   ❌ DELETE KEY
================================================= */

if (isset($_GET['del_key']) && isset($_SESSION['kn_boss'])) {

    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES);
    $new = [];

    foreach ($rows as $r) {
        if (explode("|", $r)[0] !== $_GET['del_key']) $new[] = $r;
    }

    file_put_contents($DB_FILE, implode("\n", $new));
    header("Location: index.php");
    exit;
}

/* =================================================
   LOGOUT
================================================= */

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>KN BOSS PANEL</title>
<style>
body{background:#0f051d;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
.box{width:380px;padding:35px;border-radius:20px;background:#14051e;border:1px solid #d946ef;text-align:center}
input{width:100%;padding:12px;margin:10px 0;background:#000;border:1px solid #333;color:#fff;border-radius:8px;text-align:center}
.btn{width:100%;padding:14px;border:none;background:#d946ef;color:#fff;font-weight:bold;border-radius:10px;cursor:pointer}
table{width:100%;margin-top:15px;border-collapse:collapse;font-size:12px}
th,td{padding:8px;border:1px solid #333}
</style>
</head>
<body>
<div class="box">
<?php if (!isset($_SESSION['kn_boss'])): ?>

<h2>KN BALLAS</h2>
<form method="POST">
<input type="password" name="boss_pw" placeholder="Admin password" required>
<button class="btn" name="login_boss">Đăng nhập</button>
</form>
<?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>

<?php else: ?>

<h3>ADMIN PANEL</h3>

<form method="POST">
<input type="text" name="key_name" placeholder="Tên key" required>
<input type="number" name="key_days" placeholder="Số ngày" required>
<button class="btn" name="create_key">Tạo / Gia hạn</button>
</form>

<table>
<tr><th>KEY</th><th>Hạn</th><th>X</th></tr>
<?php
$rows = file($DB_FILE, FILE_IGNORE_NEW_LINES);
foreach ($rows as $r):
$d = explode("|", $r);
?>
<tr>
<td><?=htmlspecialchars($d[0])?></td>
<td><?=$d[1]?></td>
<td><a href="?del_key=<?=$d[0]?>" style="color:red;text-decoration:none">✕</a></td>
</tr>
<?php endforeach; ?>
</table>

<br><a href="?logout=true" style="color:#777">Đăng xuất</a>

<?php endif; ?>
</div>
</body>
</html>

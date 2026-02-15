<?php
/**
 * KN BALLAS SECURE KEY SYSTEM
 * HARDENED VERSION
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header_remove("X-Powered-By");

$DB_FILE = "database.txt";
$ADMIN_PASS = "Anhnguyendz_99";
$SECRET_HEADER = "KN_SECRET_2026"; // tool phải gửi header này

if (!file_exists($DB_FILE)) file_put_contents($DB_FILE, "");

/* =========================================================
   🔐 SECURITY LAYER
========================================================= */

// ❌ chặn truy cập trực tiếp
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_SESSION['kn_boss'])) {
    http_response_code(403);
    exit("403 Forbidden");
}

// ❌ chỉ cho tool truy cập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SERVER['HTTP_X_KN_AUTH']) ||
        $_SERVER['HTTP_X_KN_AUTH'] !== $SECRET_HEADER) {
        exit("ACCESS_DENIED");
    }

    // chặn bot request rỗng
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        exit("BAD_REQUEST");
    }

    // chống spam dump
    if (!isset($_SESSION['last_req'])) $_SESSION['last_req'] = 0;
    if (time() - $_SESSION['last_req'] < 2) exit("TOO_FAST");
    $_SESSION['last_req'] = time();
}

/* =========================================================
   🛰️ TOOL AUTH REQUEST
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_key'])) {

    $user_key = trim($_POST['check_key']);
    $auth = "NOT_FOUND";

    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($rows as $row) {
        $data = explode("|", $row);

        if ($data[0] === $user_key) {
            if (date("Y-m-d") > $data[1]) $auth = "EXPIRED";
            else $auth = "SUCCESS";
            break;
        }
    }

    header('Content-Type: text/plain');

    if ($auth === "SUCCESS") {

        // ===== LUA SCRIPT =====
        $lua_script = '
print("AutoWalk Loaded")
';

        // mã hóa tránh đọc trực tiếp
        $encoded = base64_encode($lua_script);

        echo "AUTH_SUCCESS|" . $encoded;
    } else {
        echo "AUTH_ERR|" . $auth;
    }
    exit;
}

/* =========================================================
   🔐 ADMIN LOGIN
========================================================= */

if (isset($_POST['login_boss'])) {
    if ($_POST['boss_pw'] === $ADMIN_PASS) {
        $_SESSION['kn_boss'] = true;
    } else {
        $error = "Sai mật khẩu!";
    }
}

/* =========================================================
   🔑 CREATE KEY
========================================================= */

if (isset($_POST['create_key']) && $_SESSION['kn_boss']) {

    $name = trim($_POST['key_name']);
    $days = (int)$_POST['key_days'];

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

/* =========================================================
   ❌ DELETE KEY
========================================================= */

if (isset($_GET['del_key']) && $_SESSION['kn_boss']) {

    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES);
    $new = [];

    foreach ($rows as $r) {
        if (explode("|", $r)[0] !== $_GET['del_key']) $new[] = $r;
    }

    file_put_contents($DB_FILE, implode("\n", $new));
    header("Location: index.php");
    exit;
}

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
<?php if (!$_SESSION['kn_boss']): ?>
<h2>KN BALLAS</h2>
<form method="POST">
<input type="password" name="boss_pw" placeholder="Admin password" required>
<button class="btn" name="login_boss">Đăng nhập</button>
</form>
<?php if($error) echo "<p style='color:red'>$error</p>"; ?>
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

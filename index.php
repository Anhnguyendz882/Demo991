<?php
session_start();

$admin_password = "admin123"; // đổi mật khẩu admin tại đây
$db_file = "database.txt";

/* ================= API CHECK KEY ================= */

if (isset($_GET['check_key'])) {

    header("Content-Type: text/plain");

    $key = trim($_GET['check_key']);

    if (!file_exists($db_file)) {
        echo "INVALID";
        exit;
    }

    $lines = file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        list($saved_key, $created, $ip) = explode("|", $line);

        if ($key === trim($saved_key)) {

            // kiểm tra IP nếu có giới hạn
            if ($ip !== "all" && $ip !== $_SERVER['REMOTE_ADDR']) {
                echo "INVALID";
                exit;
            }

            echo "AUTH_SUCCESS|";
            readfile("AutoWalk.lua");
            exit;
        }
    }

    echo "INVALID";
    exit;
}

/* ================= LOGIN ================= */

if (isset($_POST['login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin'] = true;
    } else {
        $error = "Sai mật khẩu!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

/* ================= CREATE KEY ================= */

if (isset($_POST['create_key']) && isset($_SESSION['admin'])) {

    $new_key = strtoupper(substr(md5(rand()), 0, 10));
    $created = date("Y-m-d H:i");
    $ip = empty($_POST['ip']) ? "all" : $_POST['ip'];

    file_put_contents($db_file, "$new_key|$created|$ip\n", FILE_APPEND);
}

/* ================= DELETE KEY ================= */

if (isset($_GET['delete']) && isset($_SESSION['admin'])) {

    $delete_key = $_GET['delete'];

    $lines = file($db_file, FILE_IGNORE_NEW_LINES);

    $new_data = [];

    foreach ($lines as $line) {
        if (strpos($line, $delete_key) === false) {
            $new_data[] = $line;
        }
    }

    file_put_contents($db_file, implode("\n", $new_data) . "\n");
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>BLACK CAT AUTH</title>
<style>
body{
    background:#0d0f1a;
    color:#fff;
    font-family:Arial;
    text-align:center;
}
.container{
    width:90%;
    max-width:800px;
    margin:auto;
    margin-top:40px;
    background:#111428;
    padding:25px;
    border-radius:15px;
    box-shadow:0 0 25px #00ffc3;
}
input,button{
    padding:10px;
    border-radius:8px;
    border:none;
    margin:5px;
}
button{
    background:#00ffc3;
    cursor:pointer;
    font-weight:bold;
}
table{
    width:100%;
    margin-top:20px;
    border-collapse:collapse;
}
td,th{
    padding:10px;
    border-bottom:1px solid #333;
}
.keybox{
    background:#0b0e1c;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}
</style>
</head>
<body>

<div class="container">

<?php if (!isset($_SESSION['admin'])): ?>

<h2>🔐 ADMIN LOGIN</h2>

<form method="post">
<input type="password" name="password" placeholder="Nhập mật khẩu">
<br>
<button name="login">Đăng nhập</button>
</form>

<?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>

<?php else: ?>

<h2>🐱 BLACK CAT AUTH PANEL</h2>

<div class="keybox">
<b>Bio:</b><br>
AutoWalk VIP Loader<br>
Secure Key System<br>
Made for SA-MP Mobile
</div>

<form method="post">
<input type="text" name="ip" placeholder="Giới hạn IP (để trống = all)">
<button name="create_key">Tạo Key</button>
</form>

<a href="?logout" style="color:#00ffc3">Đăng xuất</a>

<h3>Danh sách Key</h3>

<table>
<tr>
<th>KEY</th>
<th>CREATED</th>
<th>IP</th>
<th></th>
</tr>

<?php
if (file_exists($db_file)) {
    $lines = file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        list($key,$time,$ip) = explode("|",$line);
        echo "<tr>
        <td>$key</td>
        <td>$time</td>
        <td>$ip</td>
        <td><a style='color:red' href='?delete=$key'>Xóa</a></td>
        </tr>";
    }
}
?>

</table>

<?php endif; ?>

</div>
</body>
</html>

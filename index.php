<?php
session_start();

// --- CẤU HÌNH ---
$admin_pass = "123456"; 
$db_file = "database.txt";

// TỰ ĐỘNG FIX QUYỀN GHI KHI CHẠY CODE
if (!file_exists($db_file)) {
    @file_put_contents($db_file, "");
}
@chmod($db_file, 0777);

// --- PHẦN 1: API CHECK KEY ---
if (isset($_GET['check_key'])) {
    $key_input = $_GET['check_key'];
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $new_data = [];
    $found = false;
    $response = "NOT_FOUND";

    foreach ($data as $line) {
        $parts = explode("|", $line);
        if (count($parts) < 2) continue;
        $saved_key = $parts[0];
        $expiry = $parts[1];
        $locked_ip = isset($parts[2]) ? $parts[2] : "";

        if ($saved_key === $key_input) {
            $found = true;
            if (date("Y-m-d") > $expiry) {
                $response = "EXPIRED";
            } else {
                if ($locked_ip === "") {
                    $locked_ip = $user_ip;
                    $response = "OK|" . $expiry;
                } elseif ($locked_ip !== $user_ip) {
                    $response = "WRONG_IP";
                } else {
                    $response = "OK|" . $expiry;
                }
            }
        }
        $new_data[] = "$saved_key|$expiry|$locked_ip";
    }

    if ($found) {
        @file_put_contents($db_file, implode("\n", $new_data) . "\n");
    }
    
    if ($response === "WRONG_IP") die("WRONG_IP");
    if (strpos($response, "OK") !== false) {
        $date_parts = explode("|", $response);
        $diff = strtotime($date_parts[1]) - strtotime(date("Y-m-d"));
        die("OK|" . ceil($diff / 86400));
    }
    die($response);
}

// --- PHẦN 2: LOGIC QUẢN LÝ ---
if (isset($_POST['login'])) {
    if ($_POST['pw'] == $admin_pass) $_SESSION['admin'] = true;
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ?");
}

// FIX LỖI LINE 70: ÉP QUYỀN TRƯỚC KHI GHI
if (isset($_POST['add_key']) && isset($_SESSION['admin'])) {
    $new = trim($_POST['k']) . "|" . $_POST['d'] . "|\n";
    @chmod($db_file, 0777); 
    if (@file_put_contents($db_file, $new, FILE_APPEND) === false) {
        $error_msg = "Không thể ghi file! Thử xóa file database.txt trên GitHub rồi tạo lại.";
    }
}

if (isset($_GET['del']) && isset($_SESSION['admin'])) {
    $data = file($db_file, FILE_IGNORE_NEW_LINES);
    unset($data[$_GET['del']]);
    file_put_contents($db_file, (count($data) > 0 ? implode("\n", $data) . "\n" : ""));
    header("Location: ?");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Black Cat</title>
    <style>
        :root { --p: #00ffd5; }
        body { margin: 0; font-family: sans-serif; background: #0b0b0b; display: flex; justify-content: center; align-items: center; min-height: 100vh; color: white; }
        .card { background: #161616; padding: 25px; border-radius: 15px; border: 1px solid var(--p); text-align: center; width: 320px; box-shadow: 0 0 20px rgba(0,255,213,0.2); }
        .avatar { width: 80px; height: 80px; border-radius: 50%; border: 2px solid var(--p); margin-bottom: 10px; }
        input { width: 100%; padding: 10px; margin: 5px 0; border-radius: 8px; border: 1px solid #333; background: #222; color: #fff; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: var(--p); color: #000; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .key-list { margin-top: 15px; text-align: left; font-size: 13px; max-height: 150px; overflow-y: auto; }
        .item { padding: 8px; border-bottom: 1px solid #222; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
<div class="card">
    <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
    <h3>ADMIN PANEL</h3>
    <?php if (isset($error_msg)) echo "<p style='color:red;font-size:12px'>$error_msg</p>"; ?>
    <?php if (!isset($_SESSION['admin'])): ?>
        <form method="POST"><input type="password" name="pw" placeholder="Mật khẩu..."><button type="submit" name="login">ĐĂNG NHẬP</button></form>
    <?php else: ?>
        <form method="POST"><input type="text" name="k" placeholder="Nhập Key..." required><input type="date" name="d" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"><button type="submit" name="add_key">TẠO KEY</button></form>
        <div class="key-list">
            <?php if (file_exists($db_file)) {
                $data = file($db_file, FILE_IGNORE_NEW_LINES);
                foreach ($data as $idx => $line) {
                    $p = explode("|", $line); if(empty($p[0])) continue;
                    echo "<div class='item'><span>$p[0] ($p[1])</span><a href='?del=$idx' style='color:red;text-decoration:none'>XÓA</a></div>";
                }
            } ?>
        </div>
        <a href="?logout" style="color:#555; font-size: 11px; text-decoration:none; margin-top:10px; display:block;">ĐĂNG XUẤT</a>
    <?php endif; ?>
</div>
</body>
</html>

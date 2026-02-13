<?php
session_start();

// --- CẤU HÌNH ---
$admin_pass = "123456"; 
$db_file = "database.txt";

// FIX LỖI QUYỀN GHI: Tự động tạo file và cấp quyền nếu chưa có
if (!file_exists($db_file)) {
    @file_put_contents($db_file, "");
    @chmod($db_file, 0777);
}

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

// FIX LỖI DÒNG 70: Kiểm tra quyền ghi trước khi lưu
if (isset($_POST['add_key']) && isset($_SESSION['admin'])) {
    $new = trim($_POST['k']) . "|" . $_POST['d'] . "|\n";
    if (@file_put_contents($db_file, $new, FILE_APPEND) === false) {
        $error_msg = "Lỗi: Không có quyền ghi vào file database.txt!";
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
        :root { --primary: #00ffd5; }
        body { 
            margin: 0; font-family: sans-serif; 
            background: #000 url('https://w0.peakpx.com/wallpaper/594/540/wallpaper-interstellar-black-hole-digital-art.jpg') no-repeat center center fixed; 
            background-size: cover; display: flex; justify-content: center; align-items: center; min-height: 100vh; color: white;
        }
        .container { background: rgba(0,0,0,0.85); padding: 30px; border-radius: 20px; border: 1px solid var(--primary); text-align: center; width: 350px; backdrop-filter: blur(10px); }
        .profile-img { width: 100px; height: 100px; border-radius: 50%; border: 3px solid var(--primary); margin-bottom: 15px; }
        input { width: 100%; padding: 10px; margin: 8px 0; border-radius: 10px; border: 1px solid #333; background: #111; color: #fff; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: var(--primary); color: #000; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .key-item { background: rgba(255,255,255,0.05); padding: 10px; margin: 8px 0; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; text-align: left; border-left: 3px solid var(--primary); }
        .btn-del { color: #ff4d4d; text-decoration: none; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">
    <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="profile-img">
    <h2 style="margin-top:0">ADMIN PANEL</h2>
    <?php if (isset($error_msg)) echo "<p style='color:red'>$error_msg</p>"; ?>
    <?php if (!isset($_SESSION['admin'])): ?>
        <form method="POST"><input type="password" name="pw" placeholder="Mật khẩu..."><button type="submit" name="login">LOGIN</button></form>
    <?php else: ?>
        <form method="POST"><input type="text" name="k" placeholder="Key mới..." required><input type="date" name="d" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"><button type="submit" name="add_key">TẠO KEY</button></form>
        <div style="max-height: 200px; overflow-y: auto; margin-top: 15px;">
            <?php if (file_exists($db_file)) {
                $data = file($db_file, FILE_IGNORE_NEW_LINES);
                foreach ($data as $idx => $line) {
                    $p = explode("|", $line);
                    if(empty($p[0])) continue;
                    $ip = (isset($p[2]) && $p[2] != "") ? $p[2] : "No IP";
                    echo "<div class='key-item'><span><b>$p[0]</b><br><small>$p[1] ($ip)</small></span><a href='?del=$idx' class='btn-del'>XÓA</a></div>";
                }
            } ?>
        </div>
        <a href="?logout" style="color:#666; font-size: 11px; text-decoration:none; margin-top:10px; display:block;">LOGOUT</a>
    <?php endif; ?>
</div>
</body>
</html>

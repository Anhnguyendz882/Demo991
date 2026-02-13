<?php
session_start();

// --- CẤU HÌNH ---
$admin_pass = "123456"; // Đổi mật khẩu Admin của bạn tại đây
$db_file = "database.txt";

// Tự động tạo file database nếu chưa có
if (!file_exists($db_file)) {
    file_put_contents($db_file, "");
    chmod($db_file, 0666);
}

// --- PHẦN 1: API CHECK KEY (DÀNH CHO LUA) ---
if (isset($_GET['check_key'])) {
    $key_input = $_GET['check_key'];
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $data = file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $new_data = [];
    $found = false;
    $response = "NOT_FOUND";

    foreach ($data as $line) {
        $parts = explode("|", $line);
        $saved_key = $parts[0];
        $expiry = $parts[1];
        $locked_ip = isset($parts[2]) ? $parts[2] : "";

        if ($saved_key === $key_input) {
            $found = true;
            if (date("Y-m-d") > $expiry) {
                $response = "EXPIRED";
            } else {
                if ($locked_ip === "") {
                    $locked_ip = $user_ip; // Khóa IP người dùng đầu tiên
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
        file_put_contents($db_file, implode("\n", $new_data) . "\n");
    }
    
    if ($response === "WRONG_IP") die("WRONG_IP");
    if (strpos($response, "OK") !== false) {
        $date_parts = explode("|", $response);
        $diff = strtotime($date_parts[1]) - strtotime(date("Y-m-d"));
        die("OK|" . ceil($diff / 86400));
    }
    die($response);
}

// --- PHẦN 2: LOGIC QUẢN LÝ (GIAO DIỆN WEB) ---
if (isset($_POST['login'])) {
    if ($_POST['pw'] == $admin_pass) $_SESSION['admin'] = true;
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ?");
}
if (isset($_POST['add_key']) && isset($_SESSION['admin'])) {
    $new = $_POST['k'] . "|" . $_POST['d'] . "|\n";
    file_put_contents($db_file, $new, FILE_APPEND);
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
    <title>Premium Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #00ffd5; --glass: rgba(0, 0, 0, 0.85); --border: rgba(255, 255, 255, 0.1); }
        body { 
            margin: 0; font-family: 'Poppins', sans-serif; 
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://w0.peakpx.com/wallpaper/594/540/wallpaper-interstellar-black-hole-digital-art.jpg'); 
            background-size: cover; background-position: center; background-attachment: fixed;
            display: flex; justify-content: center; align-items: center; min-height: 100vh; color: white;
        }
        .container { 
            width: 90%; max-width: 400px; background: var(--glass); backdrop-filter: blur(15px); 
            border-radius: 25px; border: 1px solid var(--border); padding: 35px 25px; text-align: center;
            box-shadow: 0 0 30px rgba(0, 255, 213, 0.2);
        }
        .profile-img { width: 100px; height: 100px; border-radius: 50%; border: 3px solid var(--primary); box-shadow: 0 0 15px var(--primary); margin-bottom: 15px; }
        input { 
            width: 100%; padding: 12px; margin: 8px 0; background: rgba(255,255,255,0.05); 
            border: 1px solid var(--border); border-radius: 12px; color: #fff; box-sizing: border-box; outline: none; 
        }
        button { 
            width: 100%; padding: 12px; margin-top: 10px; background: var(--primary); 
            color: #000; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; 
        }
        .key-list { margin-top: 20px; max-height: 250px; overflow-y: auto; text-align: left; }
        .key-item { 
            background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px; margin-bottom: 8px; 
            display: flex; justify-content: space-between; align-items: center; border-left: 4px solid var(--primary);
        }
        .key-item b { color: var(--primary); font-family: monospace; }
        .key-item small { color: #aaa; font-size: 11px; display: block; }
        .btn-del { color: #ff4d4d; text-decoration: none; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="profile-img">
    <h2 style="margin: 0 0 20px 0; letter-spacing: 2px;">ADMIN PANEL</h2>

    <?php if (!isset($_SESSION['admin'])): ?>
        <form method="POST">
            <input type="password" name="pw" placeholder="Mật khẩu Admin..." required>
            <button type="submit" name="login">ĐĂNG NHẬP</button>
        </form>
    <?php else: ?>
        <form method="POST">
            <input type="text" name="k" placeholder="Nhập Key mới..." required>
            <input type="date" name="d" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
            <button type="submit" name="add_key">TẠO KEY</button>
        </form>

        <div class="key-list">
            <?php
            $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES) : [];
            foreach ($data as $idx => $line) {
                if(empty($line)) continue;
                $p = explode("|", $line);
                $ip_display = (isset($p[2]) && $p[2] != "") ? "Locked: ".$p[2] : "Ready (No IP)";
                echo "
                <div class='key-item'>
                    <div>
                        <b>$p[0]</b>
                        <small>Hết hạn: $p[1]</small>
                        <small style='color:orange'>$ip_display</small>
                    </div>
                    <a href='?del=$idx' class='btn-del' onclick='return confirm(\"Xóa Key này?\")'>XÓA</a>
                </div>";
            }
            ?>
        </div>
        <p><a href="?logout" style="color:rgba(255,255,255,0.3); text-decoration:none; font-size:11px;">ĐĂNG XUẤT</a></p>
    <?php endif; ?>
</div>
</body>
</html>

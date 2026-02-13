<?php
session_start();

// --- CẤU HÌNH HỆ THỐNG ---
$admin_pass = "123456"; // Mật khẩu trang Admin
$db_file = "database.txt"; // File lưu trữ key và IP

// Đảm bảo file database tồn tại và có quyền ghi
if (!file_exists($db_file)) { @file_put_contents($db_file, ""); }
@chmod($db_file, 0777);

// --- PHẦN 1: API XÁC THỰC CHO SCRIPT GAME ---
if (isset($_GET['check_key'])) {
    $key_input = trim($_GET['check_key']);
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $new_data = [];
    $status = "NOT_FOUND";
    $script_content = "";

    foreach ($data as $line) {
        $p = explode("|", $line);
        if (count($p) < 2) continue;
        
        $s_key = $p[0]; 
        $expiry = $p[1];
        $l_ip = isset($p[2]) ? trim($p[2]) : "";

        if ($s_key === $key_input) {
            // 1. Kiểm tra ngày hết hạn
            if (date("Y-m-d") > $expiry) {
                $status = "EXPIRED";
            } else {
                // 2. Logic Khóa IP (Anti-Share)
                if ($l_ip === "") {
                    $l_ip = $user_ip; // Lưu IP thằng đầu tiên dùng key
                    $status = "AUTH_SUCCESS";
                } elseif ($l_ip === $user_ip) {
                    $status = "AUTH_SUCCESS"; // Đúng máy cũ
                } else {
                    $status = "WRONG_IP"; // Máy lạ dùng key
                }
            }
        }
        $new_data[] = "$s_key|$expiry|$l_ip";
    }

    if ($status === "AUTH_SUCCESS") {
        @file_put_contents($db_file, implode("\n", $new_data) . "\n");
        header('Content-Type: text/plain');
        echo "AUTH_SUCCESS|";
?>
-- [[ ĐÂY LÀ NƠI CHỨA SCRIPT LUA CỦA MÀY ]] --
local imgui = require("mimgui")
local show = imgui.new.bool(true)
imgui.OnFrame(function() return show[0] end, function()
    imgui.Begin("Black Cat VIP", show)
    imgui.TextColored(imgui.ImVec4(0, 1, 0.8, 1), "DANG NHAP THANH CONG!")
    imgui.Text("Chuc ban choi game vui ve.")
    imgui.End()
end)
<?php
        exit;
    } else {
        die($status);
    }
}

// --- PHẦN 2: LOGIC QUẢN TRỊ ADMIN ---
if (isset($_POST['login']) && $_POST['pw'] == $admin_pass) $_SESSION['admin'] = true;
if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); exit; }

// Thêm Key mới
if (isset($_POST['add_key']) && isset($_SESSION['admin'])) {
    $k = trim($_POST['k_name']);
    $d = $_POST['k_date'];
    if(!empty($k)) @file_put_contents($db_file, "$k|$d|\n", FILE_APPEND);
}

// Xóa Key
if (isset($_GET['del']) && isset($_SESSION['admin'])) {
    $data = file($db_file, FILE_IGNORE_NEW_LINES);
    unset($data[$_GET['del']]);
    file_put_contents($db_file, (count($data) > 0 ? implode("\n", $data)."\n" : ""));
    header("Location: ?"); exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Black Cat Admin - Premium System</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --main: #00ffd5; --glass: rgba(0, 0, 0, 0.75); }
        * { box-sizing: border-box; cursor: none !important; }
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; font-family: sans-serif; background: #000; }
        
        /* Video Background */
        #bg-video {
            position: fixed; top: 0; left: 0; min-width: 100%; min-height: 100%;
            z-index: -2; object-fit: cover; filter: brightness(0.5);
        }
        
        canvas { position: fixed; top: 0; left: 0; pointer-events: none; z-index: 5; }
        
        .container {
            display: flex; justify-content: center; align-items: center; height: 100vh;
        }

        .card {
            position: relative; z-index: 10;
            background: var(--glass);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 255, 213, 0.3);
            border-radius: 30px; padding: 40px; width: 380px;
            text-align: center; box-shadow: 0 0 50px rgba(0,0,0,0.9);
        }
        
        .avatar {
            width: 90px; height: 90px; border-radius: 50%;
            border: 3px solid var(--main); padding: 5px;
            box-shadow: 0 0 20px var(--main); margin-bottom: 15px;
        }
        
        h2 { font-family: 'Orbitron', sans-serif; color: var(--main); letter-spacing: 5px; margin: 10px 0; text-shadow: 0 0 10px var(--main); }
        .sys-text { font-size: 10px; color: var(--main); margin-bottom: 25px; opacity: 0.8; letter-spacing: 2px; }
        
        input {
            width: 100%; background: rgba(0,0,0,0.6); border: 1px solid #333;
            padding: 14px; border-radius: 12px; color: #fff; margin-bottom: 15px;
            outline: none; text-align: center;
        }
        input:focus { border-color: var(--main); }
        
        button {
            width: 100%; background: var(--main); color: #000;
            padding: 15px; border-radius: 12px; font-weight: 900;
            border: none; font-family: 'Orbitron', sans-serif; transition: 0.3s;
        }
        button:hover { transform: scale(1.05); box-shadow: 0 0 25px var(--main); }
        
        .key-list { margin-top: 20px; max-height: 180px; overflow-y: auto; text-align: left; }
        .key-item {
            background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 8px; border: 1px solid rgba(255,255,255,0.05);
        }
        .key-info b { color: var(--main); font-size: 14px; }
        .key-info span { font-size: 9px; color: #888; display: block; }
        .del-btn { color: #ff4d4d; text-decoration: none; font-size: 11px; font-weight: bold; }
        
        ::-webkit-scrollbar { width: 3px; }
        ::-webkit-scrollbar-thumb { background: var(--main); }
    </style>
</head>
<body onclick="startMedia()">
    <video autoplay muted loop playsinline id="bg-video">
        <source src="bg.mp4" type="video/mp4">
    </video>

    <audio id="bg-music" loop>
        <source src="https://files.catbox.moe/uclsqn.mp3" type="audio/mpeg">
    </audio>

    <canvas id="canvas"></canvas>

    <div class="container">
        <div class="card">
            <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
            <h2>BLACK CAT</h2>
            <div class="sys-text">MP4 VIDEO & SOUND SYSTEM</div>

            <?php if (!isset($_SESSION['admin'])): ?>
                <form method="POST">
                    <input type="password" name="pw" placeholder="ENTER SYSTEM PASSWORD" required>
                    <button type="submit">LOGIN SYSTEM</button>
                </form>
            <?php else: ?>
                <form method="POST">
                    <input type="text" name="k_name" placeholder="Key Name" required>
                    <input type="date" name="k_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                    <button type="submit" name="add_key">GENERATE KEY</button>
                </form>
                
                <div class="key-list">
                    <?php 
                    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES) : [];
                    foreach ($data as $idx => $line) {
                        $p = explode("|", $line); if(empty($p[0])) continue;
                        $ip_stt = (empty($p[2])) ? "WAITING..." : "LOCKED IP";
                        echo "<div class='key-item'>
                                <div class='key-info'>
                                    <b>$p[0]</b>
                                    <span>Exp: $p[1]</span>
                                    <span style='color:".(empty($p[2])?"#777":"var(--main)")."'>$ip_stt</span>
                                </div>
                                <a href='?del=$idx' class='del-btn'>DEL</a>
                              </div>";
                    }
                    ?>
                </div>
                <a href="?logout" style="color:#333; text-decoration:none; font-size: 9px; display:block; margin-top:20px;">LOGOUT</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // --- MEDIA CONTROL ---
        function startMedia() {
            document.getElementById('bg-music').play().catch(()=>{});
        }

        // --- CANVAS EFFECTS (RIPPLE & PARTICLES) ---
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let particles = [];
        let ripples = [];

        class Particle {
            constructor(x, y) {
                this.x = x; this.y = y;
                this.size = Math.random() * 3 + 1;
                this.speedX = Math.random() * 2 - 1;
                this.speedY = Math.random() * 2 - 1;
            }
            update() {
                this.x += this.speedX; this.y += this.speedY;
                if (this.size > 0.1) this.size -= 0.05;
            }
            draw() {
                ctx.fillStyle = 'rgba(0, 255, 213, 0.5)';
                ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2); ctx.fill();
            }
        }

        class Ripple {
            constructor(x, y) {
                this.x = x; this.y = y;
                this.radius = 0; this.opacity = 0.5;
            }
            update() { this.radius += 2.5; this.opacity -= 0.01; }
            draw() {
                ctx.beginPath(); ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.strokeStyle = `rgba(0, 255, 213, ${this.opacity})`;
                ctx.lineWidth = 1.5; ctx.stroke();
            }
        }

        window.addEventListener('mousemove', (e) => {
            for (let i = 0; i < 2; i++) particles.push(new Particle(e.clientX, e.clientY));
            if (Math.random() > 0.9) ripples.push(new Ripple(e.clientX, e.clientY));
        });

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach((p, i) => {
                p.update(); p.draw();
                if (p.size <= 0.1) particles.splice(i, 1);
            });
            ripples.forEach((r, i) => {
                r.update(); r.draw();
                if (r.opacity <= 0) ripples.splice(i, 1);
            });
            requestAnimationFrame(animate);
        }
        animate();

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        });
    </script>
</body>
</html>

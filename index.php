<?php
session_start();

// --- CẤU HÌNH ---
$admin_pass = "123456"; 
$db_file = "database.txt";

// Tự động tạo file dữ liệu nếu chưa có
if (!file_exists($db_file)) { @file_put_contents($db_file, ""); }
@chmod($db_file, 0777);

// --- PHẦN 1: API XÁC THỰC (CHO SCRIPT LUA) ---
if (isset($_GET['check_key'])) {
    $key_input = trim($_GET['check_key']);
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $new_data = [];
    $found = false;
    $status = "NOT_FOUND";

    foreach ($data as $line) {
        $p = explode("|", $line);
        if (count($p) < 2) continue;
        
        $s_key = $p[0]; 
        $expiry = $p[1];
        $l_ip = isset($p[2]) ? trim($p[2]) : "";

        if ($s_key === $key_input) {
            $found = true;
            if (date("Y-m-d") > $expiry) {
                $status = "EXPIRED";
            } else {
                if ($l_ip === "") {
                    $l_ip = $user_ip; // Khóa IP lần đầu
                    $status = "AUTH_SUCCESS";
                } elseif ($l_ip === $user_ip) {
                    $status = "AUTH_SUCCESS"; // Đúng IP đã khóa
                } else {
                    $status = "WRONG_IP"; // IP lạ truy cập
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
-- [[ CODE LUA CỦA MÀY TRẢ VỀ KHI KEY ĐÚNG ]] --
local imgui = require("mimgui")
local show = imgui.new.bool(true)
imgui.OnFrame(function() return show[0] end, function()
    imgui.SetNextWindowSize(imgui.ImVec2(250, 150), imgui.Cond.FirstUseEver)
    imgui.Begin("Black Cat VIP", show)
    imgui.TextColored(imgui.ImVec4(0, 1, 0.8, 1), "LOGIN SUCCESS!")
    imgui.Separator()
    imgui.Text("Welcome to Premium System")
    imgui.End()
end)
<?php
        exit;
    } else {
        die($status);
    }
}

// --- PHẦN 2: QUẢN LÝ ADMIN PANEL ---
if (isset($_POST['login']) && $_POST['pw'] == $admin_pass) $_SESSION['admin'] = true;
if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); exit; }

if (isset($_POST['add_key']) && isset($_SESSION['admin'])) {
    $k = trim($_POST['k_name']);
    $d = $_POST['k_date'];
    if(!empty($k)) @file_put_contents($db_file, "$k|$d|\n", FILE_APPEND);
}

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
    <title>Black Cat Admin - Premium Edition</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;500;800&display=swap" rel="stylesheet">
    <style>
        :root { --main: #00ffd5; --glass: rgba(15, 15, 15, 0.85); }
        * { box-sizing: border-box; cursor: none !important; }
        body { 
            margin: 0; padding: 0; 
            background: #000 url('bg.gif') no-repeat center center fixed; 
            background-size: cover; height: 100vh; width: 100vw;
            display: flex; justify-content: center; align-items: center; 
            color: #fff; overflow: hidden; font-family: 'Inter', sans-serif;
        }
        canvas { position: fixed; top: 0; left: 0; pointer-events: none; z-index: 5; }
        
        .card {
            position: relative; z-index: 10;
            background: var(--glass);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(0, 255, 213, 0.25);
            border-radius: 35px;
            padding: 45px;
            width: 400px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.8), inset 0 0 20px rgba(0,255,213,0.05);
        }
        .avatar {
            width: 100px; height: 100px; border-radius: 50%;
            border: 3px solid var(--main); padding: 5px;
            box-shadow: 0 0 25px var(--main); margin-bottom: 20px;
            transition: 0.5s;
        }
        .avatar:hover { transform: rotate(360deg); }
        h2 { 
            font-family: 'Orbitron', sans-serif; font-size: 24px; 
            color: var(--main); margin: 5px 0; letter-spacing: 5px; 
            text-shadow: 0 0 15px var(--main);
        }
        .status-bar { font-size: 10px; color: var(--main); margin-bottom: 25px; letter-spacing: 1px; opacity: 0.8; }
        
        input {
            width: 100%; background: rgba(0,0,0,0.6); border: 1px solid #333;
            padding: 15px; border-radius: 15px; color: #fff; margin-bottom: 15px;
            outline: none; text-align: center; transition: 0.3s;
        }
        input:focus { border-color: var(--main); box-shadow: 0 0 10px rgba(0,255,213,0.2); }
        
        button {
            width: 100%; background: var(--main); color: #000; border: none;
            padding: 16px; border-radius: 15px; font-weight: 800; font-family: 'Orbitron', sans-serif;
            transition: 0.4s; cursor: pointer;
        }
        button:hover { transform: translateY(-3px); box-shadow: 0 10px 30px var(--main); }
        
        .key-list { margin-top: 25px; max-height: 200px; overflow-y: auto; text-align: left; padding-right: 5px; }
        .key-item {
            background: rgba(255,255,255,0.03); padding: 15px; border-radius: 15px;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 10px; border: 1px solid rgba(255,255,255,0.05);
        }
        .key-item b { color: var(--main); font-size: 15px; }
        .key-item span { font-size: 10px; color: #888; display: block; margin-top: 2px; }
        .del-btn { color: #ff4d4d; text-decoration: none; font-size: 12px; font-weight: bold; padding: 5px 10px; border-radius: 8px; background: rgba(255, 77, 77, 0.1); }
        .del-btn:hover { background: rgba(255, 77, 77, 0.3); }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: var(--main); border-radius: 10px; }
    </style>
</head>
<body onclick="initAudio()">
    <canvas id="canvas"></canvas>
    
    <audio id="bgMusic" loop>
        <source src="LINK_NHAC_MP3_CUA_MAY_O_DAY" type="audio/mpeg">
    </audio>

    <div class="card">
        <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
        <h2>BLACK CAT</h2>
        <div class="status-bar">AUTHENTICATED BY AI SYSTEM</div>

        <?php if (!isset($_SESSION['admin'])): ?>
            <form method="POST">
                <input type="password" name="pw" placeholder="Enter System Password" required>
                <button type="submit" name="login">ACCESS SYSTEM</button>
            </form>
        <?php else: ?>
            <form method="POST">
                <input type="text" name="k_name" placeholder="Key Name..." required>
                <input type="date" name="k_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                <button type="submit" name="add_key">GENERATE KEY</button>
            </form>
            
            <div class="key-list">
                <?php 
                $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES) : [];
                foreach ($data as $idx => $line) {
                    $p = explode("|", $line); if(empty($p[0])) continue;
                    $ip_status = (empty($p[2])) ? "WAITING FOR IP..." : "LOCKED TO: " . substr($p[2], 0, 10) . "...";
                    echo "<div class='key-item'>
                            <div>
                                <b>$p[0]</b>
                                <span>Exp: $p[1]</span>
                                <span style='color:".(empty($p[2])?"#777":"var(--main)")."'>$ip_status</span>
                            </div>
                            <a href='?del=$idx' class='del-btn'>DEL</a>
                          </div>";
                }
                ?>
            </div>
            <a href="?logout" style="color:#444; text-decoration:none; font-size: 10px; display:block; margin-top:25px; letter-spacing: 2px;">LOGOUT SESSION</a>
        <?php endif; ?>
    </div>

    <script>
        // --- NHẠC MP3 ---
        function initAudio() {
            const audio = document.getElementById('bgMusic');
            audio.play().catch(e => console.log("Audio play blocked"));
        }

        // --- HIỆU ỨNG CHUỘT ---
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let particles = [];
        let ripples = [];

        class Particle {
            constructor(x, y) {
                this.x = x; this.y = y;
                this.size = Math.random() * 4 + 1;
                this.speedX = Math.random() * 2 - 1;
                this.speedY = Math.random() * 2 - 1;
                this.color = 'rgba(0, 255, 213, 0.7)';
            }
            update() {
                this.x += this.speedX; this.y += this.speedY;
                if (this.size > 0.1) this.size -= 0.1;
            }
            draw() {
                ctx.fillStyle = this.color;
                ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2); ctx.fill();
            }
        }

        class Ripple {
            constructor(x, y) {
                this.x = x; this.y = y;
                this.radius = 0; this.opacity = 0.6;
            }
            update() { this.radius += 2.5; this.opacity -= 0.015; }
            draw() {
                ctx.beginPath(); ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.strokeStyle = `rgba(0, 255, 213, ${this.opacity})`;
                ctx.lineWidth = 2; ctx.stroke();
            }
        }

        window.addEventListener('mousemove', (e) => {
            // Tạo bụi sáng
            for (let i = 0; i < 2; i++) particles.push(new Particle(e.clientX, e.clientY));
            // Tạo sóng biển (Ripple) khi lướt
            if (Math.random() > 0.9) ripples.push(new Ripple(e.clientX, e.clientY));
        });

        // Click tạo sóng mạnh
        window.addEventListener('mousedown', (e) => {
            for(let i=0; i<3; i++) ripples.push(new Ripple(e.clientX, e.clientY));
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

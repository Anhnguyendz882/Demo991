<?php
session_start();

// --- CẤU HÌNH HỆ THỐNG ---
$admin_pass = "123456"; 
$db_file = "database.txt";

if (!file_exists($db_file)) { 
    @file_put_contents($db_file, ""); 
}
@chmod($db_file, 0777);

// --- 1. CHỨC NĂNG API XÁC THỰC (DÀNH CHO GAME) ---
if (isset($_GET['check_key'])) {
    $key_input = trim($_GET['check_key']);
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $new_data = []; 
    $status = "NOT_FOUND";

    foreach ($data as $line) {
        $p = explode("|", trim($line));
        if (count($p) < 2) continue;
        if ($p[0] === $key_input) {
            if (date("Y-m-d") > $p[1]) { 
                $status = "EXPIRED"; 
            } else {
                if (empty($p[2])) { 
                    $p[2] = $user_ip; 
                    $status = "AUTH_SUCCESS"; 
                } elseif ($p[2] === $user_ip) { 
                    $status = "AUTH_SUCCESS"; 
                } else { 
                    $status = "WRONG_IP"; 
                }
            }
        }
        $new_data[] = implode("|", $p);
    }

    if ($status === "AUTH_SUCCESS") {
        @file_put_contents($db_file, implode("\n", array_filter($new_data)) . "\n");
        header('Content-Type: text/plain');
        echo "AUTH_SUCCESS|"; 
?>
-- [[ KỊCH BẢN LUA AUTOWALK PREMIUM ]] --
local imgui = require 'mimgui'
local encoding = require 'encoding'
encoding.default = 'CP1251'
local u8 = encoding.UTF8
local autoWalk = imgui.new.bool(false)

imgui.OnFrame(function() return true end, function()
    imgui.SetNextWindowSize(imgui.ImVec2(300, 200), imgui.Cond.FirstUseEver)
    imgui.Begin(u8"BLACK CAT - VIP SYSTEM", nil)
    if imgui.Checkbox(u8"Kích hoạt AutoWalk (Phím W)", autoWalk) then
        sampAddChatMessage(autoWalk[0] and "{00ffd5}[BC]: ON" or "{ff4d4d}[BC]: OFF", -1)
    end
    imgui.Separator()
    imgui.Text(u8"Trạng thái: Đã xác thực Key")
    imgui.End()
end)

function main()
    while true do
        wait(0)
        if autoWalk[0] then
            setGameKeyState(1, 255) -- Nhấn giữ phím W
        end
    end
end
<?php
        exit;
    } 
    die($status);
}

// --- 2. CHỨC NĂNG QUẢN TRỊ (ADMIN PANEL) ---
if (isset($_POST['login']) && $_POST['pw'] === $admin_pass) {
    $_SESSION['admin'] = true;
}
if (isset($_GET['logout'])) { 
    session_destroy(); 
    header("Location: ?"); 
    exit; 
}

if (isset($_SESSION['admin'])) {
    // Thêm Key mới
    if (isset($_POST['add_key'])) {
        $k = trim($_POST['k']); 
        $d = $_POST['d'];
        if(!empty($k)) {
            file_put_contents($db_file, "$k|$d|\n", FILE_APPEND);
            header("Location: ?"); exit;
        }
    }
    // Xóa Key
    if (isset($_GET['del'])) {
        $data = file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        unset($data[$_GET['del']]);
        file_put_contents($db_file, count($data) > 0 ? implode("\n", array_filter($data)) . "\n" : "");
        header("Location: ?"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLACK CAT VIP - PREMIUM PANEL</title>
    <style>
        :root { --p: #00ffd5; --bg: rgba(10, 10, 10, 0.85); }
        * { box-sizing: border-box; }
        body, html { 
            margin: 0; padding: 0; height: 100%; width: 100%;
            overflow: hidden; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #000; color: #fff; user-select: none;
        }

        /* 3. CHỨC NĂNG VIDEO BACKGROUND */
        #video-bg {
            position: fixed; top: 50%; left: 50%;
            min-width: 100%; min-height: 100%;
            width: auto; height: auto;
            z-index: -2; transform: translate(-50%, -50%);
            object-fit: cover; filter: brightness(0.4) contrast(1.1);
        }

        /* 4. CHỨC NĂNG HIỆU ỨNG CANVAS (HẠT & BÓNG NƯỚC) */
        canvas { position: fixed; top: 0; left: 0; pointer-events: none; z-index: 5; }

        .container { 
            display: flex; justify-content: center; align-items: center; 
            height: 100vh; width: 100%; position: relative; z-index: 10; 
        }

        /* MENU STYLE (Giống ảnh 1 mày gửi) */
        .card { 
            background: var(--bg); 
            padding: 40px; 
            border-radius: 30px; 
            border: 1px solid rgba(0, 255, 213, 0.3); 
            width: 380px; 
            text-align: center; 
            backdrop-filter: blur(20px); 
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.9), inset 0 0 10px rgba(0, 255, 213, 0.1);
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .avatar-wrapper { position: relative; display: inline-block; margin-bottom: 20px; }
        .avatar { 
            width: 100px; height: 100px; border-radius: 50%; 
            border: 3px solid var(--p); padding: 5px;
            box-shadow: 0 0 25px var(--p);
        }

        h2 { 
            color: var(--p); letter-spacing: 5px; text-transform: uppercase; 
            font-size: 24px; margin: 10px 0; text-shadow: 0 0 10px var(--p);
        }
        
        .status-text { font-size: 10px; color: var(--p); letter-spacing: 2px; margin-bottom: 30px; opacity: 0.8; }

        input { 
            width: 100%; background: rgba(0, 0, 0, 0.6); 
            border: 1px solid #333; padding: 15px; border-radius: 12px; 
            color: #fff; margin-bottom: 15px; outline: none; 
            text-align: center; transition: 0.3s;
        }
        input:focus { border-color: var(--p); box-shadow: 0 0 10px rgba(0, 255, 213, 0.3); }

        button { 
            width: 100%; background: var(--p); color: #000; 
            padding: 16px; border-radius: 12px; font-weight: 900; 
            border: none; cursor: pointer; transition: 0.4s; 
            text-transform: uppercase; letter-spacing: 1px;
        }
        button:hover { transform: scale(1.05); box-shadow: 0 0 30px var(--p); }

        /* KEY LIST STYLE */
        .key-list { margin-top: 20px; max-height: 200px; overflow-y: auto; text-align: left; }
        .key-item { 
            background: rgba(255, 255, 255, 0.03); padding: 12px; border-radius: 12px; 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 10px; border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .key-info b { color: var(--p); font-size: 14px; display: block; }
        .key-info small { color: #777; font-size: 10px; }
        .del-btn { color: #ff4d4d; text-decoration: none; font-weight: bold; font-size: 12px; }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: var(--p); border-radius: 10px; }
    </style>
</head>
<body onclick="initMedia(event)">

    <video autoplay muted loop playsinline id="video-bg">
        <source src="bg.mp4" type="video/mp4">
    </video>
    <audio id="bg-music" loop preload="auto">
        <source src="song.mp3" type="audio/mpeg">
    </audio>

    <canvas id="c"></canvas>

    <div class="container">
        <div class="card">
            <div class="avatar-wrapper">
                <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
            </div>
            <h2>BLACK CAT VIP</h2>
            <p class="status-text">AUTOWALK SYSTEM ACTIVE</p>

            <?php if (!isset($_SESSION['admin'])): ?>
                <form method="POST">
                    <input type="password" name="pw" placeholder="PASSWORD: 123456" required>
                    <button type="submit" name="login">ĐĂNG NHẬP HỆ THỐNG</button>
                </form>
            <?php else: ?>
                <form method="POST">
                    <input type="text" name="k" placeholder="Nhập tên Key..." required>
                    <input type="date" name="d" value="<?=date('Y-m-d', strtotime('+30 days'))?>">
                    <button type="submit" name="add_key">TẠO KEY MỚI</button>
                </form>
                <div class="key-list">
                    <?php 
                    $data = file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($data as $i => $l) {
                        $p = explode("|", trim($l));
                        if(empty($p[0])) continue;
                        $ip_status = empty($p[2]) ? "Chưa khóa IP" : "IP: ".$p[2];
                        echo "<div class='key-item'>
                                <div class='key-info'>
                                    <b>$p[0]</b>
                                    <small>Hết hạn: $p[1]</small>
                                    <small style='color:var(--p)'>$ip_status</small>
                                </div>
                                <a href='?del=$i' class='del-btn'>XÓA</a>
                              </div>";
                    }
                    ?>
                </div>
                <a href="?logout" style="color:#444; text-decoration:none; font-size:10px; margin-top:15px; display:block; letter-spacing:1px;">ĐĂNG XUẤT QUẢN TRỊ</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const c = document.getElementById('c'), ctx = c.getContext('2d');
        c.width = window.innerWidth; c.height = window.innerHeight;
        let particles = [], ripples = [];

        // Khởi tạo Media và Bóng nước khi click
        function initMedia(e) {
            document.getElementById('bg-music').play().catch(()=>{});
            document.getElementById('video-bg').play();
            
            // TẠO BÓNG NƯỚC (RIPPLE EFFECT)
            ripples.push({ x: e.clientX, y: e.clientY, r: 0, o: 0.6 });
        }

        // Hiệu ứng hạt theo chuột
        window.onmousemove = (e) => {
            for(let i=0; i<2; i++) {
                particles.push({
                    x: e.clientX, y: e.clientY, 
                    s: Math.random() * 3 + 1, 
                    vx: Math.random() * 2 - 1, 
                    vy: Math.random() * 2 - 1, 
                    o: 1
                });
            }
        };

        function draw() {
            ctx.clearRect(0, 0, c.width, c.height);

            // Vẽ Hạt (Particles)
            particles.forEach((p, i) => {
                p.x += p.vx; p.y += p.vy; p.o -= 0.015;
                if (p.o <= 0) particles.splice(i, 1);
                ctx.fillStyle = `rgba(0, 255, 213, ${p.o})`;
                ctx.beginPath(); ctx.arc(p.x, p.y, p.s, 0, Math.PI * 2); ctx.fill();
            });

            // Vẽ Bóng nước (Ripple)
            ripples.forEach((r, i) => {
                r.r += 3; r.o -= 0.012;
                if (r.o <= 0) ripples.splice(i, 1);
                ctx.strokeStyle = `rgba(0, 255, 213, ${r.o})`;
                ctx.lineWidth = 2;
                ctx.beginPath(); ctx.arc(r.x, r.y, r.r, 0, Math.PI * 2); ctx.stroke();
            });

            requestAnimationFrame(draw);
        }
        draw();

        window.onresize = () => { c.width = window.innerWidth; c.height = window.innerHeight; };
    </script>
</body>
</html>

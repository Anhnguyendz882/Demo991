<?php
/**
 * BLACK CAT VIP - PREMIUM MANAGEMENT PANEL
 * Full Functions: AutoWalk, AutoY, Pathfinding, Security IP, Neon Effects
 * Written for: Anhnguyendz882
 */

session_start();
date_default_timezone_set("Asia/Ho_Chi_Minh");

// =========================================================
// 1. CẤU HÌNH HỆ THỐNG (BẢO MẬT CAO)
// =========================================================
$admin_pass = "Anhnguyendz_99"; // MẬT KHẨU ADMIN CỦA MÀY
$db_file    = "database.txt";

// Tự động khởi tạo file database nếu Render xóa mất
if (!file_exists($db_file)) {
    @file_put_contents($db_file, "");
    @chmod($db_file, 0777);
}

// =========================================================
// 2. CHỨC NĂNG API - XỬ LÝ CHO LOADER GAME (MOONLOADER)
// =========================================================
if (isset($_GET['check_key'])) {
    $key_input = trim($_GET['check_key']);
    $user_ip   = $_SERVER['REMOTE_ADDR'];
    $status    = "NOT_FOUND";
    
    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $updated_rows = [];

    foreach ($data as $line) {
        $parts = explode("|", trim($line));
        if (count($parts) < 2) continue;

        $key_name = $parts[0];
        $expiry   = $parts[1];
        $locked_ip = isset($parts[2]) ? $parts[2] : "";

        if ($key_name === $key_input) {
            if (date("Y-m-d") > $expiry) {
                $status = "EXPIRED";
            } else {
                if (empty($locked_ip)) {
                    $parts[2] = $user_ip; // Khóa IP lần đầu
                    $status = "AUTH_SUCCESS";
                } elseif ($locked_ip === $user_ip) {
                    $status = "AUTH_SUCCESS";
                } else {
                    $status = "WRONG_IP";
                }
            }
        }
        $updated_rows[] = implode("|", $parts);
    }

    if ($status === "AUTH_SUCCESS") {
        @file_put_contents($db_file, implode("\n", array_filter($updated_rows)) . "\n");
        header('Content-Type: text/plain; charset=utf-8');
        echo "AUTH_SUCCESS|";
?>
---------------------------------------------------------
-- INTERNAL LUA SCRIPT - AUTO PATHFINDER & PACKET Y
---------------------------------------------------------
local imgui = require 'mimgui'
local encoding = require 'encoding'
encoding.default = 'CP1251'
local u8 = encoding.UTF8

local active = imgui.new.bool(false)
local running = false
local points = {}
local current_idx = 1
local last_y_time = 0

-- Packet Function: Gửi phím Y trực tiếp qua Data Onfoot
function sendPacketY()
    local playerId = select(2, sampGetPlayerIdByCharHandle(PLAYER_PED))
    local bs = allocateMemory(68)
    if sampStorePlayerOnfootData(playerId, bs) then
        setStructElement(bs, 36, 1, 64, false) 
        sampSendOnfootData(bs)
    end
    freeMemory(bs)
end

-- Logic di chuyển tới tọa độ
function processWalking(target)
    local px, py, pz = getCharCoordinates(PLAYER_PED)
    local dx, dy = target[1] - px, target[2] - py
    local distance = math.sqrt(dx*dx + dy*dy)
    
    if distance > 1.5 then
        setCharHeading(PLAYER_PED, math.deg(math.atan2(-dx, dy)))
        setGameKeyState(1, 255) -- Nhấn giữ W
        return false
    else
        setGameKeyState(1, 0)
        return true
    end
end

imgui.OnFrame(function() return active[0] end, function()
    imgui.SetNextWindowSize(imgui.ImVec2(400, 300), imgui.Cond.FirstUseEver)
    imgui.Begin(u8"BLACK CAT - PATHFINDER ENGINE", active)
    imgui.Separator()
    imgui.Text(u8"Tọa độ đã lưu: " .. #points)
    imgui.Text(u8"Trạng thái: " .. (running and u8"ĐANG CHẠY" or u8"ĐANG DỪNG"))
    
    if imgui.Button(u8"Lấy tọa độ hiện tại (Add Point)") then
        local x, y, z = getCharCoordinates(PLAYER_PED)
        table.insert(points, {x, y, z})
        sampAddChatMessage("{00ffd5}[Black Cat]: {ffffff}Đã thêm điểm tọa độ mới.", -1)
    end
    
    if imgui.Button(u8"BẮT ĐẦU CHU KỲ") then
        if #points > 0 then running = true current_idx = 1 end
    end
    imgui.SameLine()
    if imgui.Button(u8"DỪNG LẠI") then 
        running = false 
        setGameKeyState(1, 0) 
    end
    
    if imgui.Button(u8"XÓA TOÀN BỘ DỮ LIỆU") then
        points = {}
        running = false
    end
    imgui.End()
end)

function main()
    sampRegisterChatCommand("bcmenu", function() active[0] = not active[0] end)
    while true do
        wait(0)
        if running and #points > 0 then
            if processWalking(points[current_idx]) then
                -- Khi tới điểm, spam Y trong 1.5s
                local start_y = os.clock()
                while os.clock() - start_y < 1.5 do
                    sendPacketY()
                    wait(100)
                end
                current_idx = (current_idx % #points) + 1
            end
        end
    end
end
<?php
        exit;
    } 
    die($status);
}

// =========================================================
// 3. LOGIC QUẢN TRỊ VIÊN (ADMIN PANEL)
// =========================================================
if (isset($_POST['admin_login'])) {
    if ($_POST['admin_password'] === $admin_pass) {
        $_SESSION['is_admin'] = true;
    } else {
        $error = "Sai mật khẩu rồi bro!";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Xử lý thêm/xóa Key
if (isset($_SESSION['is_admin'])) {
    if (isset($_POST['create_key'])) {
        $new_k = trim($_POST['k_name']);
        $new_d = $_POST['k_date'];
        if (!empty($new_k)) {
            @file_put_contents($db_file, "$new_k|$new_d|\n", FILE_APPEND);
            header("Location: index.php"); exit;
        }
    }
    if (isset($_GET['delete_idx'])) {
        $lines = file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        unset($lines[$_GET['delete_idx']]);
        @file_put_contents($db_file, count($lines) > 0 ? implode("\n", array_filter($lines)) . "\n" : "");
        header("Location: index.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLACK CAT VIP - CÔNG CỤ QUẢN LÝ KEY</title>
    <style>
        /* CSS DESIGN BY CHATGPT - PREMIUM NEON STYLE */
        :root {
            --primary: #00ffd5;
            --bg-dark: rgba(10, 10, 10, 0.9);
            --border-glow: 0 0 20px rgba(0, 255, 213, 0.4);
            --text-glow: 0 0 10px rgba(0, 255, 213, 0.8);
        }

        * { box-sizing: border-box; transition: all 0.3s ease; }
        
        body, html {
            margin: 0; padding: 0;
            width: 100%; height: 100%;
            background: #000;
            font-family: 'Segoe UI', Arial, sans-serif;
            overflow: hidden;
            color: #fff;
            user-select: none;
        }

        /* VIDEO BACKGROUND SETUP */
        #video-background {
            position: fixed;
            top: 50%; left: 50%;
            min-width: 100%; min-height: 100%;
            width: auto; height: auto;
            z-index: -2;
            transform: translate(-50%, -50%);
            object-fit: cover;
            filter: brightness(0.3) contrast(1.2);
        }

        /* CANVAS FOR PARTICLES & RIPPLES */
        #effect-canvas {
            position: fixed;
            top: 0; left: 0;
            z-index: 1;
            pointer-events: none;
        }

        .main-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            width: 100%;
            position: relative;
            z-index: 10;
        }

        .glass-card {
            background: var(--bg-dark);
            width: 400px;
            padding: 40px;
            border-radius: 35px;
            border: 1px solid rgba(0, 255, 213, 0.2);
            text-align: center;
            backdrop-filter: blur(25px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5), var(--border-glow);
            animation: slideUp 1s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-img {
            width: 110px; height: 110px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            padding: 5px;
            margin-bottom: 20px;
            box-shadow: 0 0 30px var(--primary);
            object-fit: cover;
        }

        h1 {
            font-size: 28px;
            margin: 0;
            letter-spacing: 6px;
            color: var(--primary);
            text-shadow: var(--text-glow);
            text-transform: uppercase;
        }

        .sub-title {
            font-size: 11px;
            color: var(--primary);
            letter-spacing: 3px;
            margin-bottom: 30px;
            opacity: 0.7;
        }

        /* FORM STYLING */
        .input-group { margin-bottom: 15px; position: relative; }
        
        input {
            width: 100%;
            padding: 16px;
            border-radius: 15px;
            border: 1px solid #222;
            background: rgba(20, 20, 20, 0.8);
            color: #fff;
            text-align: center;
            outline: none;
            font-size: 14px;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 255, 213, 0.2);
        }

        .btn-action {
            width: 100%;
            padding: 16px;
            border-radius: 15px;
            border: none;
            background: var(--primary);
            color: #000;
            font-weight: 900;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 5px 15px rgba(0, 255, 213, 0.3);
        }

        .btn-action:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px var(--primary);
        }

        /* KEY LIST BOX */
        .list-container {
            margin-top: 25px;
            max-height: 220px;
            overflow-y: auto;
            text-align: left;
            padding-right: 5px;
        }

        .key-row {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .key-row:hover { background: rgba(0, 255, 213, 0.05); }

        .key-info b { color: var(--primary); font-size: 15px; display: block; }
        .key-info span { color: #888; font-size: 11px; }

        .del-link {
            color: #ff4d4d;
            text-decoration: none;
            font-weight: bold;
            font-size: 12px;
            padding: 5px 10px;
            border: 1px solid #ff4d4d;
            border-radius: 8px;
        }

        .del-link:hover { background: #ff4d4d; color: #fff; }

        .logout-link {
            display: block;
            margin-top: 20px;
            color: #555;
            font-size: 11px;
            text-decoration: none;
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 10px; }

    </style>
</head>
<body onclick="activateSystems(event)">

    <video autoplay muted loop playsinline id="video-background">
        <source src="bg.mp4" type="video/mp4">
    </video>
    <audio id="audio-player" loop>
        <source src="song.mp3" type="audio/mpeg">
    </audio>

    <canvas id="effect-canvas"></canvas>

    <div class="main-wrapper">
        <div class="glass-card">
            <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="profile-img">
            <h1>BLACK CAT VIP</h1>
            <div class="sub-title">PREMIUM KEY SYSTEM</div>

            <?php if (!isset($_SESSION['is_admin'])): ?>
                <form method="POST">
                    <div class="input-group">
                        <input type="password" name="admin_password" placeholder="MẬT KHẨU ADMIN" required>
                    </div>
                    <button type="submit" name="admin_login" class="btn-action">XÁC MINH DANH TÍNH</button>
                    <?php if(isset($error)) echo "<p style='color:red; font-size:12px;'>$error</p>"; ?>
                </form>
            <?php else: ?>
                <form method="POST">
                    <div class="input-group">
                        <input type="text" name="k_name" placeholder="TÊN KEY MỚI" required>
                    </div>
                    <div class="input-group">
                        <input type="date" name="k_date" value="<?=date('Y-m-d', strtotime('+30 days'))?>">
                    </div>
                    <button type="submit" name="create_key" class="btn-action">KHỞI TẠO KEY</button>
                </form>

                <div class="list-container">
                    <?php 
                    $keys = file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    if(empty($keys)) echo "<p style='color:#555; font-size:12px;'>Chưa có key nào được tạo.</p>";
                    foreach ($keys as $idx => $val) {
                        $p = explode("|", trim($val));
                        if(empty($p[0])) continue;
                        $ip_info = empty($p[2]) ? "Chưa sử dụng" : "Đã khóa: " . $p[2];
                        echo "
                        <div class='key-row'>
                            <div class='key-info'>
                                <b>$p[0]</b>
                                <span>Hạn: $p[1]</span><br>
                                <span style='color:var(--primary)'>$ip_info</span>
                            </div>
                            <a href='?delete_idx=$idx' class='del-link'>XÓA</a>
                        </div>";
                    }
                    ?>
                </div>
                <a href="?action=logout" class="logout-link">THOÁT KHỎI HỆ THỐNG</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // XỬ LÝ CANVAS (HIỆU ỨNG HẠT VÀ BÓNG NƯỚC)
        const canvas = document.getElementById('effect-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        let ripples = [];

        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.onresize = resize;
        resize();

        // Kích hoạt âm thanh và video khi người dùng tương tác
        function activateSystems(e) {
            document.getElementById('audio-player').play().catch(() => {});
            document.getElementById('video-background').play();
            
            // Tạo hiệu ứng sóng nước (Ripple)
            ripples.push({
                x: e.clientX,
                y: e.clientY,
                r: 0,
                o: 0.8
            });
        }

        // Tạo hiệu ứng hạt bay theo chuột
        window.onmousemove = (e) => {
            for(let i=0; i<3; i++) {
                particles.push({
                    x: e.clientX,
                    y: e.clientY,
                    s: Math.random() * 3 + 1,
                    vx: Math.random() * 2 - 1,
                    vy: Math.random() * 2 - 1,
                    o: 1
                });
            }
        };

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Vẽ hạt Neon
            particles.forEach((p, i) => {
                p.x += p.vx;
                p.y += p.vy;
                p.o -= 0.012;
                if (p.o <= 0) particles.splice(i, 1);
                ctx.fillStyle = `rgba(0, 255, 213, ${p.o})`;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.s, 0, Math.PI * 2);
                ctx.fill();
            });

            // Vẽ bóng nước loang
            ripples.forEach((r, i) => {
                r.r += 3.5;
                r.o -= 0.015;
                if (r.o <= 0) ripples.splice(i, 1);
                ctx.strokeStyle = `rgba(0, 255, 213, ${r.o})`;
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.arc(r.x, r.y, r.r, 0, Math.PI * 2);
                ctx.stroke();
            });

            requestAnimationFrame(animate);
        }
        animate();
    </script>
</body>
</html>

<?php
/**
 * 🔗 PROJECT: BLACKCAT.LOL (BIO-LINK STYLE)
 * 👤 OWNER: ANHNGUYENDZ
 * 🛠 ENGINE: ORIGINAL AUTOWALK & AUTO-Y
 * 🎨 UI: INSPIRED BY GUNS.LOL / ZYO.LOL
 */

session_start();
error_reporting(0);

// ==========================================
// [1] CẤU HÌNH BẢO MẬT
// ==========================================
$admin_pass = "Anhnguyendz_99"; 
$db_file    = "database.txt";
$log_file   = "logs.txt";

if (!file_exists($db_file)) { @file_put_contents($db_file, ""); @chmod($db_file, 0777); }
if (!file_exists($log_file)) { @file_put_contents($log_file, ""); @chmod($log_file, 0777); }

// ==========================================
// [2] API CORE (TRẢ VỀ SCRIPT GỐC)
// ==========================================
if (isset($_GET['check_key'])) {
    $key_input = trim($_GET['check_key']);
    $user_ip   = $_SERVER['REMOTE_ADDR'];
    $status    = "NOT_FOUND";
    
    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $updated_rows = [];

    foreach ($data as $line) {
        $parts = explode("|", trim($line));
        if (count($parts) < 2) continue;
        if ($parts[0] === $key_input) {
            if (date("Y-m-d") > $parts[1]) { $status = "EXPIRED"; } 
            else {
                if (empty($parts[2])) { $parts[2] = $user_ip; $status = "AUTH_SUCCESS"; } 
                elseif ($parts[2] === $user_ip) { $status = "AUTH_SUCCESS"; } 
                else { $status = "WRONG_IP"; }
            }
        }
        $updated_rows[] = implode("|", $parts);
    }

    if ($status === "AUTH_SUCCESS") {
        @file_put_contents($db_file, implode("\n", array_filter($updated_rows)) . "\n");
        header('Content-Type: text/plain; charset=utf-8');
        echo "AUTH_SUCCESS|";
?>
-------------------------------------------------
-- SCRIPT AUTOWALK AUTOY (NGUYÊN BẢN 100%)
-------------------------------------------------
script_name("AutoWalk AutoY")
script_author("ChatGPT")
require "lib.moonloader"
local imgui = require "mimgui"
local spamTime, show, running, points, idx = 1500, imgui.new.bool(true), false, {}, 1

function sendY()
    local pId = select(2, sampGetPlayerIdByCharHandle(PLAYER_PED))
    local mem = allocateMemory(68)
    sampStorePlayerOnfootData(pId, mem)
    setStructElement(mem, 36, 1, 64, false)
    sampSendOnfootData(mem)
    freeMemory(mem)
end

local function walk(p)
    local x,y,z = getCharCoordinates(PLAYER_PED)
    local dx, dy = p[1]-x, p[2]-y
    local dist = math.sqrt(dx*dx+dy*dy)
    if dist > 1.2 then
        setCharHeading(PLAYER_PED, math.deg(math.atan2(-dx,dy)))
        setGameKeyState(1, 255)
        return false
    else
        setGameKeyState(1, 0)
        return true
    end
end

imgui.OnFrame(function() return show[0] end, function()
    imgui.Begin("BLACK CAT VIP", show)
    imgui.Text("Points: "..#points.." | Current: "..idx)
    if imgui.Button("Add Point") then table.insert(points,{getCharCoordinates(PLAYER_PED)}) end
    if imgui.Button("START") then if #points>0 then running, idx = true, 1 end end
    if imgui.Button("STOP") then running = false setGameKeyState(1,0) end
    imgui.End()
end)

function main()
    repeat wait(0) until isSampAvailable()
    sampRegisterChatCommand("awui", function() show[0]=not show[0] end)
    while true do
        wait(0)
        if running and #points>0 then
            if walk(points[idx]) then
                local t = os.clock()
                while os.clock()-t < spamTime/1000 do sendY() wait(120) end
                idx = (idx % #points) + 1
            end
        end
    end
end
-------------------------------------------------
<?php
        exit;
    } die($status);
}

// ==========================================
// [3] ADMIN LOGIC
// ==========================================
if (isset($_POST['login'])) { if ($_POST['pw'] === $admin_pass) $_SESSION['admin'] = true; }
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }

if (isset($_SESSION['admin'])) {
    if (isset($_POST['add'])) {
        $k = trim($_POST['k']); $d = $_POST['d'];
        if ($k) file_put_contents($db_file, "$k|$d|\n", FILE_APPEND);
    }
    if (isset($_GET['del'])) {
        $l = file($db_file, FILE_IGNORE_NEW_LINES); unset($l[$_GET['del']]);
        file_put_contents($db_file, implode("\n", array_filter($l)) . "\n");
        header("Location: index.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>blackcat.lol | bio</title>
    <style>
        /* CSS THEO STYLE GUNS.LOL - TỐI GIẢN, HIỆN ĐẠI */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

        :root {
            --bg: #050505;
            --card: rgba(15, 15, 15, 0.6);
            --accent: #ffffff;
            --gray: #888;
        }

        body, html {
            margin: 0; padding: 0;
            width: 100%; height: 100%;
            background: var(--bg);
            font-family: 'Inter', sans-serif;
            color: var(--accent);
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* VIDEO NỀN MỜ */
        #bg-video {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: -2;
            object-fit: cover;
            filter: brightness(0.2) blur(5px);
        }

        /* CARD CHÍNH */
        .container {
            width: 450px;
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            backdrop-filter: blur(15px);
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            animation: slideUp 1s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .avatar {
            width: 100px; height: 100px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
            transition: 0.5s;
        }

        .avatar:hover {
            transform: scale(1.1) rotate(5deg);
            border-color: var(--accent);
        }

        h1 {
            font-size: 24px;
            margin: 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        /* TEXT CHẠY (TYPING EFFECT) */
        .typing {
            font-size: 14px;
            color: var(--gray);
            margin: 10px 0 30px 0;
            min-height: 20px;
        }

        .typing::after {
            content: "|";
            animation: blink 0.8s infinite;
        }

        @keyframes blink { 50% { opacity: 0; } }

        /* BUTTON STYLE */
        .btn-link {
            display: block;
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--accent);
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: 0.3s;
            cursor: pointer;
        }

        .btn-link:hover {
            background: rgba(255, 255, 255, 1);
            color: #000;
            transform: translateY(-3px);
        }

        input {
            width: 100%;
            padding: 14px;
            margin-bottom: 10px;
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #fff;
            text-align: center;
            outline: none;
        }

        .admin-section {
            margin-top: 30px;
            text-align: left;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
        }

        .key-list {
            max-height: 150px;
            overflow-y: auto;
        }

        .key-item {
            display: flex;
            justify-content: space-between;
            background: rgba(255,255,255,0.03);
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 8px;
            font-size: 13px;
        }

        .key-item a { color: #ff4d4d; text-decoration: none; }

        /* SCROLLBAR */
        ::-webkit-scrollbar { width: 0px; }

        /* HIỆU ỨNG RIPPLE VÀ HẠT */
        canvas {
            position: fixed;
            top: 0; left: 0;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body onclick="interact()">

    <video id="bg-video" loop muted playsinline>
        <source src="bg.mp4" type="video/mp4">
    </video>
    <audio id="bg-music" loop>
        <source src="song.mp3" type="audio/mpeg">
    </audio>
    <canvas id="canvas"></canvas>

    <div class="container">
        <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
        <h1>black cat</h1>
        <div class="typing" id="typed-text"></div>

        <?php if (!isset($_SESSION['admin'])): ?>
            <div class="user-view">
                <a href="#" class="btn-link">Discord Server</a>
                <a href="#" class="btn-link">YouTube Channel</a>
                <form method="POST" style="margin-top:20px;">
                    <input type="password" name="pw" placeholder="Enter Admin Password">
                    <button class="btn-link" name="login">Admin Login</button>
                </form>
            </div>
        <?php else: ?>
            <div class="admin-section">
                <h3 style="font-size:14px; margin-bottom:15px;">Key Management</h3>
                <form method="POST">
                    <input type="text" name="k" placeholder="Key Name" required>
                    <input type="date" name="d" value="<?=date('Y-m-d', strtotime('+30 days'))?>">
                    <button class="btn-link" name="add" style="background:var(--accent); color:#000;">Generate Key</button>
                </form>
                
                <div class="key-list">
                    <?php 
                    $keys = file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($keys as $idx => $line) {
                        $p = explode("|", trim($line));
                        echo "<div class='key-item'>
                                <span><b>$p[0]</b> ($p[1])</span>
                                <a href='?del=$idx'>Delete</a>
                              </div>";
                    }
                    ?>
                </div>
                <a href="?logout" class="logout-link" style="font-size:10px; color:#555; display:block; margin-top:15px; text-decoration:none; text-align:center;">Logout Session</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // TYPING EFFECT NHƯ GUNS.LOL
        const text = "developer | samp enthusiast | black cat owner";
        let i = 0;
        function type() {
            if (i < text.length) {
                document.getElementById("typed-text").innerHTML += text.charAt(i);
                i++;
                setTimeout(type, 100);
            }
        }
        window.onload = type;

        // XỬ LÝ NHẠC VÀ VIDEO
        function interact() {
            document.getElementById('bg-music').play().catch(()=>{});
            document.getElementById('bg-video').play();
        }

        // CANVAS HIỆU ỨNG HẠT MỜ
        const cvs = document.getElementById('canvas'), ctx = cvs.getContext('2d');
        cvs.width = window.innerWidth; cvs.height = window.innerHeight;
        let particles = [];

        class Particle {
            constructor() {
                this.x = Math.random() * cvs.width;
                this.y = Math.random() * cvs.height;
                this.size = Math.random() * 2 + 0.5;
                this.speedX = Math.random() * 1 - 0.5;
                this.speedY = Math.random() * 1 - 0.5;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                if (this.size > 0.2) this.size -= 0.001;
            }
            draw() {
                ctx.fillStyle = 'rgba(255,255,255,0.5)';
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function handleParticles() {
            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();
                if (particles[i].size <= 0.3) {
                    particles.splice(i, 1); i--;
                }
            }
        }

        function animate() {
            ctx.clearRect(0,0,cvs.width, cvs.height);
            if (particles.length < 50) particles.push(new Particle());
            handleParticles();
            requestAnimationFrame(animate);
        }
        animate();
    </script>
</body>
</html>

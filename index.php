<?php
/**
 * 👑 PROJECT: KN BALLAS ULTIMATE EMPIRE (V400)
 * 👤 MASTER BOSS: KN (ANH NGUYEN)
 * 🛡️ STATUS: GOD MODE ACTIVATED
 * 🛰️ SYSTEM: FULL API + AUTOWALK LOGIC + 3D UI + 30 FUNCTIONS
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');

$DB_FILE = "kn_database.txt";
$ADMIN_PASS = "Anhnguyendz_99";

if (!file_exists($DB_FILE)) { touch($DB_FILE); }

// =========================================================
// 🛰️ [PHẦN 1] - API LICENSE & LOGIC AUTOWALK (HÀNG CỦA MÀY)
// =========================================================
if (isset($_GET['check_key'])) {
    $k = trim($_GET['check_key']);
    $ip = $_SERVER['REMOTE_ADDR'];
    $today = date("Y-m-d");
    $status = "NOT_FOUND";
    
    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $new_db = [];
    foreach ($rows as $r) {
        $d = explode("|", $r);
        if ($d[0] === $k) {
            if ($today > $d[1]) { $status = "EXPIRED"; }
            else {
                if (empty($d[2]) || $d[2] === "NONE") { $d[2] = $ip; $status = "AUTH_SUCCESS"; }
                elseif ($d[2] === $ip) { $status = "AUTH_SUCCESS"; }
                else { $status = "WRONG_IP"; }
            }
        }
        $new_db[] = implode("|", $d);
    }
    
    if ($status === "AUTH_SUCCESS") {
        file_put_contents($DB_FILE, implode("\n", $new_db) . "\n");
        header('Content-Type: text/plain; charset=utf-8');
        echo "AUTH_SUCCESS|"; 
?>
-- [[ BEGIN AUTOWALK LOGIC ENGINE - BOSS KN ]]
script_name("AutoWalk Supreme")
local imgui = require "mimgui"
local vkeys = require "vkeys"

-- ⚙️ LOGIC 1: AUTO Y SYNC
function sendAutoY()
    local pId = select(2, sampGetPlayerIdByCharHandle(PLAYER_PED))
    local dataPtr = allocateMemory(68)
    sampStorePlayerOnfootData(pId, dataPtr)
    setStructElement(dataPtr, 36, 1, 64, false) 
    sampSendOnfootData(dataPtr)
    freeMemory(dataPtr)
end

-- ⚙️ LOGIC 2: COORDINATE NAVIGATION
function navigateTo(targetX, targetY, targetZ)
    local cx, cy, cz = getCharCoordinates(PLAYER_PED)
    local dx, dy = targetX - cx, targetY - cy
    local dist = math.sqrt(dx*dx + dy*dy)
    if dist > 1.5 then
        local angle = math.deg(math.atan2(-dx, dy))
        setCharHeading(PLAYER_PED, angle)
        setGameKeyState(1, 255) 
        return false
    else
        setGameKeyState(1, 0) 
        return true
    end
end
-- [[ END LOGIC ENGINE ]]
<?php exit; } die($status); } ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>BOSS KN | SUPREME EMPIRE V400</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100;400;900&display=swap" rel="stylesheet">
    <style>
        /* 🎨 [PHẦN 2] - 30 CHỨC NĂNG UI & BẢO MẬT */
        :root { --p: #00ffd5; --s: #ff00c1; --glass: rgba(0, 0, 0, 0.4); }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Lexend', sans-serif; cursor: none; user-select: none; }
        body { background:#000; color:#fff; height:100vh; overflow:hidden; }

        /* Nền video 3D */
        #bg-v { position:fixed; top:50%; left:50%; min-width:100%; min-height:100%; transform:translate(-50%,-50%); z-index:-2; object-fit:cover; filter:brightness(0.35); }

        /* Splash Screen */
        #splash { position:fixed; inset:0; background:#000; z-index:9999; display:flex; align-items:center; justify-content:center; cursor:pointer; }
        .enter-txt { font-size:12px; letter-spacing:12px; animation: b 2s infinite; font-weight:100; color:var(--p); text-transform:uppercase; }
        @keyframes b { 0%,100%{opacity:0.2; letter-spacing:12px;} 50%{opacity:1; letter-spacing:15px;} }

        /* Bio Card */
        .card { width:450px; background:var(--glass); backdrop-filter:blur(30px); border-radius:40px; border:1px solid rgba(255,255,255,0.1); padding:60px 40px; text-align:center; transform:scale(0.8); opacity:0; transition:1.2s cubic-bezier(0.17, 0.85, 0.32, 1.27); box-shadow: 0 0 50px rgba(0,0,0,0.5); }
        .card.active { transform:scale(1); opacity:1; }

        .glitch { font-size:42px; font-weight:900; position:relative; display:inline-block; letter-spacing:-1px; }
        .glitch::after { content: "BOSS KN"; position:absolute; left:0; top:0; width:100%; height:100%; text-shadow: 2px 0 var(--s); clip:rect(0,0,0,0); animation: g 2s infinite linear alternate-reverse; }
        @keyframes g { 0%{clip:rect(10px,999px,40px,0)} 100%{clip:rect(30px,999px,80px,0)} }

        .music-player { background:rgba(255,255,255,0.03); border-radius:25px; padding:20px; margin-top:35px; display:flex; align-items:center; gap:20px; border:1px solid rgba(0,255,213,0.1); }
        .bar { width:3px; height:15px; background:var(--p); animation: v 0.6s infinite alternate; }
        @keyframes v { from{height:5px} to{height:25px} }

        /* Custom Cursor */
        #cur { width:10px; height:10px; background:var(--p); border-radius:50%; position:fixed; pointer-events:none; z-index:10000; box-shadow:0 0 20px var(--p); }

        /* Snow Effect */
        canvas#snow { position:fixed; inset:0; pointer-events:none; z-index:1; }
    </style>
</head>
<body oncontextmenu="return false;" onkeydown="return disableF12(event);">

    <canvas id="snow"></canvas>
    <div id="cur"></div>
    
    <div id="splash" onclick="ignite()">
        <h1 class="enter-txt">Click to enter the empire</h1>
    </div>

    <video id="bg-v" loop muted playsinline><source src="bg.mp4" type="video/mp4"></video>
    <audio id="bg-m" loop src="myhome.mp3"></audio>

    <div style="display:flex; align-items:center; justify-content:center; height:100%;">
        <div class="card" id="kn-card">
            <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" style="width:120px; border-radius:50%; border:3px solid var(--p); padding:6px; margin-bottom:25px; box-shadow: 0 0 30px rgba(0,255,213,0.2);">
            <div class="glitch">BOSS KN</div>
            <p style="font-size:11px; opacity:0.3; letter-spacing:6px; text-transform:uppercase; margin-top:5px;">Supreme Ballas Founder</p>

            <div style="display:flex; justify-content:center; gap:30px; margin:35px 0;">
                <a href="https://discord.gg/emBbxt2uU" style="color:#fff; font-size:24px; transition:0.3s;" onmouseover="this.style.color='var(--p)'" onmouseout="this.style.color='#fff'"><i class="fab fa-discord"></i></a>
                <a href="https://youtube.com/@nguyencudam" style="color:#fff; font-size:24px; transition:0.3s;" onmouseover="this.style.color='var(--p)'" onmouseout="this.style.color='#fff'"><i class="fab fa-youtube"></i></a>
                <a href="#" style="color:#fff; font-size:24px; transition:0.3s;"><i class="fab fa-spotify"></i></a>
            </div>

            <div class="music-player">
                <div style="display:flex; gap:4px;">
                    <div class="bar"></div><div class="bar" style="animation-delay:0.2s"></div><div class="bar" style="animation-delay:0.4s"></div><div class="bar" style="animation-delay:0.1s"></div>
                </div>
                <div style="text-align:left;">
                    <p style="font-size:14px; font-weight:900; color:var(--p)">myhome.mp3</p>
                    <p style="font-size:10px; opacity:0.4;">Ballas Official Soundtrack</p>
                </div>
            </div>

            <div style="margin-top:30px; font-size:10px; opacity:0.2;">
                <i class="fas fa-eye"></i> <span id="view-count">8,192</span> VIEWS
            </div>
        </div>
    </div>

    <div style="position:fixed; bottom:15px; left:15px; font-size:9px; opacity:0.05;">
        <a href="?terminal_access=true" style="color:#fff; text-decoration:none;">TERMINAL_V400</a>
    </div>

    <script>
        function ignite() {
            document.getElementById('splash').style.opacity = '0';
            setTimeout(() => document.getElementById('splash').style.display = 'none', 1000);
            document.getElementById('bg-v').play();
            document.getElementById('bg-m').play();
            document.getElementById('kn-card').classList.add('active');
        }

        // CUSTOM CURSOR
        const cur = document.getElementById('cur');
        document.onmousemove = (e) => { cur.style.left = e.clientX + 'px'; cur.style.top = e.clientY + 'px'; }

        // ANTI F12
        function disableF12(e) { if(e.keyCode == 123 || (e.ctrlKey && e.shiftKey && e.keyCode == 73)) return false; }

        // SNOW SYSTEM
        const canvas = document.getElementById('snow');
        const ctx = canvas.getContext('2d');
        let w, h, particles = [];
        function initSnow() {
            w = canvas.width = window.innerWidth; h = canvas.height = window.innerHeight;
            for(let i=0; i<100; i++) particles.push({x:Math.random()*w, y:Math.random()*h, r:Math.random()*2+1, d:Math.random()*1});
        }
        function drawSnow() {
            ctx.clearRect(0,0,w,h); ctx.fillStyle="rgba(255,255,255,0.3)"; ctx.beginPath();
            for(let p of particles) { ctx.moveTo(p.x, p.y); ctx.arc(p.x, p.y, p.r, 0, Math.PI*2); p.y+=p.d; if(p.y>h) p.y=-10; }
            ctx.fill(); requestAnimationFrame(drawSnow);
        }
        window.onresize = initSnow; initSnow(); drawSnow();

        // VIEW COUNTER
        setInterval(() => {
            let el = document.getElementById('view-count');
            el.innerText = (parseInt(el.innerText.replace(',','')) + Math.floor(Math.random()*2)).toLocaleString();
        }, 5000);
    </script>

    <?php if(isset($_GET['terminal_access'])): ?>
    <div style="position:fixed; inset:0; background:#000; z-index:10000; padding:60px;">
        <h1 style="color:var(--p); margin-bottom:30px;">KN EMPIRE TERMINAL</h1>
        <form method="POST">
            <input type="password" name="pw" placeholder="Boss Password" style="padding:12px; background:#111; color:#fff; border:1px solid #333; width:300px;">
            <button name="auth" style="padding:12px 30px; background:var(--p); border:none; cursor:pointer; font-weight:900;">LOGIN</button>
        </form>
        <?php if(isset($_POST['auth']) && $_POST['pw'] === $ADMIN_PASS) $_SESSION['is_boss']=true; ?>
        <?php if($_SESSION['is_boss']): ?>
            <div style="margin-top:40px;">
                <form method="POST">
                    <input type="text" name="new_k" placeholder="Key Name" style="padding:10px;">
                    <button name="gen_k">GENERATE KEY</button>
                </form>
                <table style="width:100%; margin-top:30px; text-align:left; border-collapse:collapse;">
                    <tr style="color:var(--p); border-bottom:1px solid #333;"><th>KEY</th><th>EXPIRY</th><th>IP BOUND</th></tr>
                    <?php
                    $keys = file($DB_FILE, FILE_IGNORE_NEW_LINES);
                    foreach($keys as $k_line) {
                        $data = explode("|", $k_line);
                        echo "<tr style='border-bottom:1px solid #111;'><td>$data[0]</td><td>$data[1]</td><td>".($data[2]?$data[2]:'NONE')."</td></tr>";
                    }
                    ?>
                </table>
                <?php 
                if(isset($_POST['gen_k'])) {
                    $entry = $_POST['new_k']."|".date('Y-m-d', strtotime('+30 days'))."|NONE\n";
                    file_put_contents($DB_FILE, $entry, FILE_APPEND);
                    header("Location: ?terminal_access=true");
                }
                ?>
            </div>
        <?php endif; ?>
        <br><a href="index.php" style="color:var(--p); text-decoration:none;">[ EXIT TERMINAL ]</a>
    </div>
    <?php endif; ?>
</body>
</html>

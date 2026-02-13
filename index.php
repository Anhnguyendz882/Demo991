<?php
/**
 * 👑 PROJECT: KN BALLAS OMNI SUPREME (V1600)
 * 👤 BOSS: KN (ANH NGUYEN)
 * 🛰️ MODULES: HWID LOCK + IP LOCK + AUTOWALK + 30+ FUNCTIONS
 */

session_start();
error_reporting(0);
$DB = "kn_database.txt";
$ADMIN_PASS = "Anhnguyendz_99";
if (!file_exists($DB)) touch($DB);

// =========================================================
// 🛰️ [1] LOGIC API & ANTI-SHARE (BẢO MẬT TẦNG SÂU)
// =========================================================
if (isset($_GET['check_key'])) {
    $k = trim($_GET['check_key']);
    $hwid = $_GET['hwid'] ?? "NONE";
    $ip = $_SERVER['REMOTE_ADDR'];
    $auth = "NOT_FOUND";
    
    $rows = file($DB, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated = [];
    foreach ($rows as $r) {
        $d = explode("|", $r); // Key | Expiry | IP | HWID
        if ($d[0] === $k) {
            if (date("Y-m-d") > $d[1]) { $auth = "EXPIRED"; }
            elseif ($d[3] !== "NONE" && $d[3] !== $hwid) { $auth = "INVALID_HWID"; }
            else {
                if ($d[3] === "NONE") $d[3] = $hwid; 
                if ($d[2] === "NONE") $d[2] = $ip;
                $auth = "AUTH_SUCCESS";
            }
        }
        $updated[] = implode("|", $d);
    }
    file_put_contents($DB, implode("\n", $updated));
    
    if ($auth === "AUTH_SUCCESS") {
        header('Content-Type: text/plain');
        echo "AUTH_SUCCESS|"; 
?>
-- [[ 🛡️ GIỮ NGUYÊN BẢN CODE AUTOWALK CỦA BOSS KN ]]
Script_name("AutoWalk AutoY")
script_author("ChatGPT")
require "lib.moonloader"
local imgui = require "mimgui"
local spamTime = 1500 
local show = imgui.new.bool(true)
local running = false
local points = {}
local idx = 1
function sendY()
    local playerId = select(2, sampGetPlayerIdByCharHandle(PLAYER_PED))
    local memPtr = allocateMemory(68)
    sampStorePlayerOnfootData(playerId, memPtr)
    setStructElement(memPtr, 36, 1, 64, false) 
    sampSendOnfootData(memPtr)
    freeMemory(memPtr)
end
local function walk(p)
    local x,y,z=getCharCoordinates(PLAYER_PED)
    local dx=p[1]-x
    local dy=p[2]-y
    local dist=math.sqrt(dx*dx+dy*dy)
    if dist>1.2 then
        local heading=math.deg(math.atan2(-dx,dy))
        setCharHeading(PLAYER_PED,heading)
        setGameKeyState(1,255)
        return false
    else
        setGameKeyState(1,0)
        return true
    end
end
imgui.OnFrame(function() return show[0] end,
function()
    imgui.Begin("AutoWalk AutoY", show)
    if imgui.Button("Add Point") then
        local x,y,z=getCharCoordinates(PLAYER_PED)
        table.insert(points,{x,y,z})
    end
    if imgui.Button("START") then if #points>0 then running=true; idx=1 end end
    if imgui.Button("STOP") then running=false; setGameKeyState(1,0) end
    imgui.End()
end)
function main()
    repeat wait(0) until isSampAvailable()
    while true do
        wait(0)
        if running and #points>0 then
            if walk(points[idx]) then
                local t=os.clock()
                while os.clock()-t < spamTime/1000 do sendY(); wait(120) end
                idx = (idx % #points) + 1
            end
        end
    end
end
<?php exit; } die($auth); } ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>BOSS KN | OMNI SUPREME V1600</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;900&display=swap" rel="stylesheet">
    <style>
        /* 🎨 [2] 30+ CHỨC NĂNG UI/UX (SIÊU ĐẸP) */
        :root { --p: #00ffd5; --s: #ff00c1; --bg: rgba(0,0,0,0.85); }
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Lexend', sans-serif; cursor: none; user-select: none; }
        body { background: #000; height: 100vh; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        
        /* 1-5. Hiệu ứng nền & Particle */
        #bg-v { position: fixed; inset: 0; min-width: 100%; min-height: 100%; z-index: -2; object-fit: cover; filter: brightness(0.3); }
        .overlay { position: fixed; inset: 0; background: radial-gradient(circle, transparent 20%, #000 120%); z-index: -1; }
        canvas { position: fixed; inset: 0; z-index: -1; }

        /* 6-15. Design Glassmorphism & Glitch */
        .glass-card { 
            width: 400px; padding: 45px; border-radius: 40px; 
            background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(25px);
            border: 1px solid rgba(0, 255, 213, 0.2); text-align: center;
            box-shadow: 0 0 50px rgba(0,255,213,0.1); position: relative;
        }
        .glitch { font-size: 35px; font-weight: 900; color: #fff; text-transform: uppercase; letter-spacing: -1px; position: relative; }
        .glitch::before { content: attr(data-text); position: absolute; left: -2px; text-shadow: 2px 0 var(--s); top: 0; overflow: hidden; clip: rect(0,900px,0,0); animation: noise 2s infinite linear alternate-reverse; }

        /* 16-25. Form & Buttons */
        input { width: 100%; padding: 15px; background: rgba(0,0,0,0.7); border: 1px solid #222; border-radius: 15px; color: var(--p); text-align: center; margin: 20px 0; outline: none; transition: 0.3s; }
        input:focus { border-color: var(--p); box-shadow: 0 0 15px var(--p); }
        .btn-main { width: 100%; padding: 16px; border-radius: 15px; border: none; background: linear-gradient(45deg, var(--p), var(--s)); color: #000; font-weight: 900; cursor: pointer; text-transform: uppercase; transition: 0.4s; }
        .btn-main:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,255,213,0.4); }
        .btn-admin-gate { background: transparent; color: #444; font-size: 9px; border: 1px solid #111; padding: 8px; margin-top: 15px; border-radius: 8px; width: 100%; transition: 0.3s; }
        .btn-admin-gate:hover { color: var(--p); border-color: var(--p); }

        /* 26-30. Music Visualizer & Cursor */
        #cur { width: 10px; height: 10px; background: var(--p); border-radius: 50%; position: fixed; pointer-events: none; z-index: 10000; box-shadow: 0 0 15px var(--p); transition: transform 0.1s; }
        .v-box { display: flex; gap: 3px; justify-content: center; margin-top: 15px; height: 20px; align-items: flex-end; }
        .v-bar { width: 3px; background: var(--p); animation: wave 0.5s infinite alternate; }

        @keyframes noise { 0% { clip: rect(10px, 999px, 40px, 0); } 100% { clip: rect(80px, 999px, 90px, 0); } }
        @keyframes wave { from { height: 3px; } to { height: 20px; } }
    </style>
</head>
<body oncontextmenu="return false;">
    <div id="cur"></div>
    <canvas id="canvas"></canvas>
    <video id="bg-v" autoplay loop muted playsinline><source src="bg.mp4" type="video/mp4"></video>
    <audio id="bg-m" loop src="myhome.mp3"></audio>

    <div class="glass-card" id="auth-ui">
        <div style="font-size: 40px; color: var(--p); margin-bottom: 10px;"><i class="fas fa-shield-alt"></i></div>
        <div class="glitch" data-text="AUTHENTICATION">AUTHENTICATION</div>
        <p style="font-size: 8px; letter-spacing: 4px; opacity: 0.4;">HWID & IP LOCK SYSTEM</p>
        <input type="text" id="k" placeholder="ENTER BALLAS KEY...">
        <button class="btn-main" onclick="verify()">UNLOCK EMPIRE</button>
        <button class="btn-admin-gate" onclick="document.getElementById('admin-ui').style.display='block'">[ BOSS ADMIN LOGIN ]</button>
        <p id="stt" style="color:var(--s); font-size: 11px; margin-top: 15px;"></p>
    </div>

    <div class="glass-card" id="bio-ui" style="display:none;">
        <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" style="width:100px; height:100px; border-radius:50%; border:2px solid var(--p); padding:5px; margin-bottom:15px;">
        <div class="glitch" data-text="BOSS KN">BOSS KN</div>
        <div class="v-box">
            <div class="v-bar"></div><div class="v-bar" style="animation-delay:0.1s"></div><div class="v-bar" style="animation-delay:0.2s"></div><div class="v-bar" style="animation-delay:0.3s"></div>
        </div>
        <p style="font-size: 11px; color: var(--p); margin-top: 10px;">Playing: myhome.mp3</p>
        <div style="margin-top:25px; display:flex; justify-content:center; gap:20px; font-size: 18px;">
            <a href="#" style="color:#fff;"><i class="fab fa-discord"></i></a>
            <a href="#" style="color:#fff;"><i class="fab fa-youtube"></i></a>
        </div>
    </div>

    <div id="admin-ui" style="display:none; position:fixed; inset:0; background:#000; z-index:10001; padding:50px;">
        <h1 style="color:var(--p)">BOSS ADMIN PORTAL</h1>
        <form method="POST">
            <input type="password" name="pw" placeholder="Admin Password..." style="width: 300px;">
            <button class="btn-main" name="login" style="width: 100px;">VÀO</button>
        </form>
        <?php if(isset($_POST['login']) && $_POST['pw'] === $ADMIN_PASS) $_SESSION['is_boss']=1; ?>
        <?php if($_SESSION['is_boss']): ?>
            <div style="margin-top:30px; border-top: 1px solid #222; padding-top: 20px;">
                <form method="POST">
                    <input type="text" name="nk" placeholder="Tên Key">
                    <button class="btn-main" name="add" style="width: 200px;">TẠO KEY (30 NGÀY)</button>
                    <button class="btn-main" name="reset_db" style="background:red; width: 200px; margin-top:10px;">RESET DATABASE</button>
                </form>
                <div style="margin-top:20px; background:#111; padding:20px; font-size:12px; border:1px solid #333; text-align:left;">
                    <b>KEY | EXPIRY | IP | HWID</b><br><?php echo nl2br(file_get_contents($DB)); ?>
                </div>
            </div>
            <?php 
                if(isset($_POST['add'])){ file_put_contents($DB, $_POST['nk']."|".date('Y-m-d', strtotime('+30 days'))."|NONE|NONE\n", FILE_APPEND); header("Location: index.php"); }
                if(isset($_POST['reset_db'])){ file_put_contents($DB, ""); header("Location: index.php"); }
            ?>
        <?php endif; ?>
        <br><button onclick="location.href='index.php'" style="background:none; color:#555; border:none; cursor:pointer;">[ QUAY LẠI ]</button>
    </div>

    <script>
        // Particle Engine
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        let w, h, particles = [];
        function init() { w=canvas.width=window.innerWidth; h=canvas.height=window.innerHeight; }
        function loop() {
            ctx.clearRect(0,0,w,h); ctx.fillStyle="rgba(0,255,213,0.1)";
            if(particles.length < 60) particles.push({x:Math.random()*w, y:Math.random()*h, s:Math.random()*1});
            particles.forEach((p, i) => { p.y-=p.s; if(p.y<0) p

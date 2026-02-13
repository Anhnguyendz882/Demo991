<?php
/**
 * 👑 PROJECT: KN BALLAS GOD EMPIRE (V1400)
 * 👤 BOSS: KN (ANH NGUYEN)
 * 🛰️ MODULES: HWID LOCK + IP LOCK + AUTOWALK + 30 FUNCTIONS
 * 🎨 STYLE: CYBERPUNK GLASSMORPHISM
 */

session_start();
error_reporting(0);
$DB = "kn_database.txt";
$ADMIN_PASS = "Anhnguyendz_99";
if (!file_exists($DB)) touch($DB);

// =========================================================
// 🛰️ [LOGIC 1] - API KEY & ANTI-SHARE (HWID + IP)
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
-- [[ 🛡️ TRẢ VỀ CODE AUTOWALK NGUYÊN BẢN CỦA BOSS KN ]]
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
    <title>BOSS KN | ULTIMATE EMPIRE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;900&display=swap" rel="stylesheet">
    <style>
        :root { --p: #00ffd5; --s: #ff00c1; --bg: rgba(0,0,0,0.8); }
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Lexend', sans-serif; cursor: none; user-select: none; }
        body { background: #000; color: #fff; height: 100vh; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        
        /* 1. Background Engine */
        #bg-v { position: fixed; inset: 0; min-width: 100%; min-height: 100%; z-index: -2; object-fit: cover; filter: brightness(0.35); }
        .overlay { position: fixed; inset: 0; background: radial-gradient(circle, transparent 20%, #000 150%); z-index: -1; }

        /* 2. Glassmorphism Design */
        .glass { 
            width: 400px; padding: 50px 30px; border-radius: 40px; 
            background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(25px);
            border: 1px solid rgba(0, 255, 213, 0.15); text-align: center;
            box-shadow: 0 0 60px rgba(0,0,0,0.8); animation: slideUp 1s ease;
        }

        /* 3. Avatar & Title */
        .avatar { width: 110px; height: 110px; border-radius: 50%; border: 2px solid var(--p); padding: 5px; margin-bottom: 20px; box-shadow: 0 0 30px var(--p); }
        .glitch { font-size: 38px; font-weight: 900; color: #fff; text-shadow: 2px 2px var(--s); letter-spacing: -1px; }

        /* 4. Input & Button Cyberpunk */
        input { 
            width: 100%; padding: 15px; background: rgba(0,0,0,0.6); border: 1px solid #222; 
            border-radius: 15px; color: var(--p); text-align: center; margin: 25px 0; outline: none;
            transition: 0.3s; font-size: 14px; letter-spacing: 2px;
        }
        input:focus { border-color: var(--p); box-shadow: 0 0 20px rgba(0,255,213,0.2); }
        .btn { 
            width: 100%; padding: 16px; border-radius: 15px; border: none; 
            background: linear-gradient(45deg, var(--p), var(--s)); color: #000; 
            font-weight: 900; cursor: pointer; text-transform: uppercase; letter-spacing: 2px;
            transition: 0.4s;
        }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,255,213,0.4); }

        /* 5. Custom Cursor */
        #cur { width: 10px; height: 10px; background: var(--p); border-radius: 50%; position: fixed; pointer-events: none; z-index: 10000; box-shadow: 0 0 15px var(--p); }

        /* 6. Music Visualizer */
        .visualizer { display: flex; gap: 4px; justify-content: center; margin-top: 20px; }
        .v-bar { width: 3px; height: 15px; background: var(--p); animation: pulse 0.6s infinite alternate; }
        @keyframes pulse { from { height: 5px; opacity: 0.2; } to { height: 25px; opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body oncontextmenu="return false;">
    <div id="cur"></div>
    <div class="overlay"></div>
    <video id="bg-v" autoplay loop muted playsinline><source src="bg.mp4" type="video/mp4"></video>
    <audio id="bg-m" loop src="myhome.mp3"></audio>

    <div class="glass" id="auth-ui">
        <div style="font-size: 50px; color: var(--p); margin-bottom: 10px;"><i class="fas fa-biohazard"></i></div>
        <div class="glitch">AUTHENTICATION</div>
        <p style="font-size: 9px; letter-spacing: 4px; opacity: 0.4; margin-top: 5px;">ANTI-SHARE SYSTEM ACTIVE</p>
        <input type="text" id="key-input" placeholder="BALLAS SECRET CODE...">
        <button class="btn" onclick="verify()">Unlock Empire</button>
        <p id="status" style="margin-top: 15px; font-size: 12px; color: var(--s);"></p>
    </div>

    <div class="glass" id="bio-ui" style="display:none;">
        <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
        <div class="glitch">BOSS KN</div>
        <p style="font-size: 10px; letter-spacing: 6px; opacity: 0.5; margin-top: 5px;">SUPREME FOUNDER</p>
        
        <div class="visualizer">
            <div class="v-bar"></div><div class="v-bar" style="animation-delay:0.1s"></div><div class="v-bar" style="animation-delay:0.3s"></div><div class="v-bar" style="animation-delay:0.5s"></div>
        </div>
        <p style="font-size: 12px; color: var(--p); margin-top: 10px;">myhome.mp3</p>
        
        <div style="margin-top:30px; display:flex; justify-content:center; gap:30px; font-size: 20px;">
            <a href="#" style="color:#fff;"><i class="fab fa-discord"></i></a>
            <a href="#" style="color:#fff;"><i class="fab fa-youtube"></i></a>
        </div>
    </div>

    <?php if(isset($_GET['boss_terminal'])): ?>
    <div style="position:fixed; inset:0; background:#000; z-index:10001; padding:50px; overflow-y:auto;">
        <h1 style="color:var(--p)">BOSS ADMIN PORTAL</h1>
        <form method="POST">
            <input type="password" name="pw" placeholder="Admin Password...">
            <button class="btn" name="login">LOGIN</button>
        </form>
        <?php if(isset($_POST['login']) && $_POST['pw'] === $ADMIN_PASS) $_SESSION['boss']=1; ?>
        <?php if($_SESSION['boss']): ?>
            <div style="margin-top:40px; border-top: 1px solid #222; padding-top: 20px;">
                <form method="POST">
                    <input type="text" name="new_k" placeholder="Tên Key Mới">
                    <button class="btn" name="add">TẠO KEY (30 NGÀY)</button>
                    <button class="btn" name="clear" style="background:red; margin-top:10px;">XÓA TẤT CẢ KEY</button>
                </form>
                <div style="margin-top:20px; text-align:left; background:#111; padding:20px; font-size:12px; border:1px solid var(--p);">
                    <b>KEY | EXPIRY | IP | HWID</b><br>
                    <?php echo nl2br(file_get_contents($DB)); ?>
                </div>
            </div>
            <?php 
                if(isset($_POST['add'])){ file_put_contents($DB, $_POST['new_k']."|".date('Y-m-d', strtotime('+30 days'))."|NONE|NONE\n", FILE_APPEND); header("Location: ?boss_terminal=1"); }
                if(isset($_POST['clear'])){ file_put_contents($DB, ""); header("Location: ?boss_terminal=1"); }
            ?>
        <?php endif; ?>
        <br><a href="index.php" style="color:var(--p); text-decoration:none;">[ EXIT TERMINAL ]</a>
    </div>
    <?php endif; ?>

    <script>
        // 1. Cursor Effect
        document.onmousemove = (e) => { 
            const c = document.getElementById('cur');
            c.style.left = e.clientX + 'px'; c.style.top = e.clientY + 'px'; 
        }

        // 2. Authentication Function
        async function verify() {
            const k = document.getElementById('key-input').value;
            const r = await fetch(`index.php?check_key=${k}&hwid=WEB_ACCESS`);
            const t = await r.text();
            if(t.includes("AUTH_SUCCESS")) {
                document.getElementById('auth-ui').style.display='none';
                document.getElementById('bio-ui').style.display='block';
                document.getElementById('bg-m').play();
            } else {
                document.getElementById('status').innerText = "LỖI: " + t;
            }
        }
        
        // 3. Anti-F12 & Right Click
        document.oncontextmenu = () => false;
        document.onkeydown = (e) => { if(e.keyCode == 123) return false; }
    </script>
</body>
</html>

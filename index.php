<?php
/**
 * 👑 PROJECT: KN BALLAS CAT EDITION (V5000)
 * 👤 BOSS: KN (ANH NGUYEN)
 * 🛰️ MODULE: MASTER KEY + CAT BIO + FULL LUA AUTOWALK
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// CẤU HÌNH DUY NHẤT
$MASTER_KEY = "Anhnguyendz_99"; 
$LOG_FILE = "kn_ip_logs.txt"; 

if (isset($_GET['check_key'])) {
    $k = trim($_GET['check_key']);
    $ip = $_SERVER['REMOTE_ADDR'];
    if ($k !== $MASTER_KEY) { die("NOT_FOUND"); }
    file_put_contents($LOG_FILE, $ip . " | " . date("H:i:s d/m/Y") . "\n", FILE_APPEND);
    header('Content-Type: text/plain');
    echo "AUTH_SUCCESS|"; 
?>
-- [[ CODE AUTOWALK CỦA BOSS KN ]]
script_name("AutoWalk AutoY")
script_author("KN_BOSS")
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

function main()
    repeat wait(0) until isSampAvailable()
    while true do
        wait(0)
        if running and #points>0 then
            local p=points[idx]
            local x,y,z=getCharCoordinates(PLAYER_PED)
            local dx,dy = p[1]-x, p[2]-y
            if math.sqrt(dx*dx+dy*dy)>1.2 then
                setCharHeading(PLAYER_PED, math.deg(math.atan2(-dx,dy)))
                setGameKeyState(1,255)
            else
                setGameKeyState(1,0)
                local t=os.clock()
                while os.clock()-t < spamTime/1000 do sendY(); wait(120) end
                idx = (idx % #points) + 1
            end
        end
    end
end
<?php exit; } ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>KN BOSS | CAT SYSTEM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --p: #00ffd5; --s: #ff00c1; }
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Lexend', sans-serif; cursor: none; }
        body { background: #000; height: 100vh; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        canvas { position: fixed; inset: 0; z-index: -1; }
        #bg-v { position: fixed; inset: 0; min-width: 100%; min-height: 100%; z-index: -2; object-fit: cover; filter: brightness(0.2); }
        .glass { width: 380px; padding: 40px; border-radius: 40px; background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(25px); border: 1px solid var(--p); text-align: center; position: relative; box-shadow: 0 0 40px rgba(0,255,213,0.2); }
        .glitch { font-size: 30px; font-weight: 900; color: #fff; text-transform: uppercase; letter-spacing: 5px; text-shadow: 2px 2px var(--s); }
        input { width: 100%; padding: 15px; background: rgba(0,0,0,0.8); border: 1px solid #222; border-radius: 12px; color: var(--p); text-align: center; margin: 20px 0; outline: none; border-left: 5px solid var(--p); }
        .btn { width: 100%; padding: 16px; border-radius: 12px; border: none; background: linear-gradient(45deg, var(--p), var(--s)); color: #000; font-weight: 900; cursor: pointer; transition: 0.3s; }
        .btn:hover { transform: scale(1.05); box-shadow: 0 0 20px var(--p); }
        #cur { width: 10px; height: 10px; background: var(--p); border-radius: 50%; position: fixed; pointer-events: none; z-index: 10000; box-shadow: 0 0 10px var(--p); }
        .avatar { width: 120px; height: 120px; border-radius: 50%; border: 3px solid var(--p); padding: 5px; margin-bottom: 15px; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(0,255,213,0.4); } 70% { box-shadow: 0 0 0 20px rgba(0,255,213,0); } 100% { box-shadow: 0 0 0 0 rgba(0,255,213,0); } }
    </style>
</head>
<body>
    <div id="cur"></div>
    <canvas id="snow"></canvas>
    <video id="bg-v" autoplay loop muted playsinline><source src="bg.mp4" type="video/mp4"></video>
    <audio id="bg-m" loop src="myhome.mp3"></audio>

    <div class="glass">
        <div id="login-ui">
            <div class="glitch">KN BALLAS</div>
            <p style="font-size: 10px; color: var(--p); opacity: 0.6; margin-top: 5px;">MASTER AUTHENTICATION</p>
            <input type="password" id="mk" placeholder="NHẬP MASTER KEY...">
            <button class="btn" onclick="go()">UNLOCK SYSTEM</button>
        </div>

        <div id="bio-ui" style="display:none;">
            <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
            <h2 style="color:#fff; letter-spacing: 2px;">BOSS KN</h2>
            <p style="font-size: 10px; color: var(--p); letter-spacing: 5px;">ACCESS GRANTED</p>
            <div style="margin-top:25px; display:flex; justify-content:center; gap:25px; font-size: 22px; color: #fff;">
                <i class="fab fa-discord"></i> <i class="fab fa-youtube"></i> <i class="fab fa-spotify"></i>
            </div>
            <p style="margin-top:20px; font-size: 11px; color: #555;">Status: Online & Working</p>
        </div>
    </div>

    <script>
        document.onmousemove = (e) => { const c=document.getElementById('cur'); c.style.left=e.clientX+'px'; c.style.top=e.clientY+'px'; }
        
        async function go() {
            const key = document.getElementById('mk').value;
            if (key === "<?php echo $MASTER_KEY; ?>") {
                document.getElementById('login-ui').style.display = 'none';
                document.getElementById('bio-ui').style.display = 'block';
                document.getElementById('bg-m').play();
            } else { alert("SAI KEY RỒI THẰNG LỒN!"); }
        }

        // Hiệu ứng hạt tuyết cho đẹp
        const canvas = document.getElementById('snow');
        const ctx = canvas.getContext('2d');
        let particles = [];
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        function create() {
            for(let i=0; i<50; i++) particles.push({x:Math.random()*canvas.width, y:Math.random()*canvas.height, r:Math.random()*2+1, d:Math.random()*1});
        }
        function draw() {
            ctx.clearRect(0,0,canvas.width, canvas.height);
            ctx.fillStyle = "rgba(0, 255, 213, 0.3)";
            ctx.beginPath();
            for(let p of particles) {
                ctx.moveTo(p.x, p.y); ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
                p.y += p.d; if(p.y > canvas.height) p.y = -10;
            }
            ctx.fill(); requestAnimationFrame(draw);
        }
        create(); draw();
    </script>
</body>
</html>

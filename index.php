<?php
session_start();
$admin_pass = "1912"; 
$db_file = "database.txt";

if (!file_exists($db_file)) { @file_put_contents($db_file, ""); }
@chmod($db_file, 0777);

// --- 1. CHỨC NĂNG API (TRẢ CODE CHO GAME) ---
if (isset($_GET['check_key'])) {
    $key_input = trim($_GET['check_key']);
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $new_data = []; $status = "NOT_FOUND";
    foreach ($data as $line) {
        $p = explode("|", $line);
        if (count($p) < 2) continue;
        if ($p[0] === $key_input) {
            if (date("Y-m-d") > $p[1]) { $status = "EXPIRED"; } 
            else {
                if (empty($p[2])) { $p[2] = $user_ip; $status = "AUTH_SUCCESS"; } 
                elseif ($p[2] === $user_ip) { $status = "AUTH_SUCCESS"; } 
                else { $status = "WRONG_IP"; }
            }
        }
        $new_data[] = implode("|", $p);
    }
    if ($status === "AUTH_SUCCESS") {
        file_put_contents($db_file, implode("\n", $new_data) . "\n");
        header('Content-Type: text/plain');
        echo "AUTH_SUCCESS|"; 
?>
-- [[ CODE LUA CHUẨN ]] --
local imgui = require 'mimgui'
local auto = imgui.new.bool(false)
imgui.OnFrame(function() return true end, function()
    imgui.Begin("BLACK CAT VIP")
    imgui.Checkbox("AutoWalk (W)", auto)
    imgui.End()
end)
function main() while true do wait(0) if auto[0] then setGameKeyState(1, 255) end end end
<?php
        exit;
    } die($status);
}

// --- 2. CHỨC NĂNG QUẢN LÝ (ADMIN PANEL) ---
if (isset($_POST['login']) && $_POST['pw'] === $admin_pass) $_SESSION['admin'] = true;
if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); exit; }
if (isset($_SESSION['admin'])) {
    if (isset($_POST['add_key'])) {
        $k = trim($_POST['k']); $d = $_POST['d'];
        if($k) file_put_contents($db_file, "$k|$d|\n", FILE_APPEND);
    }
    if (isset($_GET['del'])) {
        $data = file($db_file, FILE_IGNORE_NEW_LINES);
        unset($data[$_GET['del']]);
        file_put_contents($db_file, implode("\n", $data). (count($data)>0?"\n":""));
        header("Location: ?"); exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Black Cat - Full Option</title>
    <style>
        :root { --p: #00ffd5; }
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; background: #000; font-family: sans-serif; }
        
        /* 3. CHỨC NĂNG VIDEO BG */
        #video-bg {
            position: fixed; top: 50%; left: 50%;
            min-width: 100%; min-height: 100%;
            z-index: -1; transform: translate(-50%, -50%);
            object-fit: cover; filter: brightness(0.4);
        }

        .card { 
            position: relative; z-index: 10; background: rgba(0, 0, 0, 0.8); 
            padding: 30px; border-radius: 20px; border: 1px solid var(--p); 
            width: 320px; text-align: center; backdrop-filter: blur(10px);
            box-shadow: 0 0 30px rgba(0, 255, 213, 0.2);
        }
        
        .avatar { width: 80px; height: 80px; border-radius: 50%; border: 2px solid var(--p); margin-bottom: 10px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border-radius: 10px; border: 1px solid #333; background: #111; color: #fff; text-align: center; outline: none; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: var(--p); color: #000; font-weight: bold; border: none; border-radius: 10px; cursor: pointer; }
        
        /* 4. CHỨC NĂNG HẠT & 5. BÓNG NƯỚC (CANVAS) */
        canvas { position: fixed; top: 0; left: 0; pointer-events: none; z-index: 5; }
        .key-item { background: rgba(255,255,255,0.05); padding: 8px; margin-bottom: 5px; border-radius: 8px; display: flex; justify-content: space-between; font-size: 11px; }
    </style>
</head>
<body onclick="playAll(event)">

    <video autoplay muted loop playsinline id="video-bg"><source src="bg.mp4" type="video/mp4"></video>
    
    <audio id="bg-audio" loop><source src="song.mp3" type="audio/mpeg"></audio>

    <canvas id="c"></canvas>

    <div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
        <div class="card">
            <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
            <h2 style="color:var(--p); margin: 5px 0;">BLACK CAT</h2>
            
            <?php if (!isset($_SESSION['admin'])): ?>
                <form method="POST">
                    <input type="password" name="pw" placeholder="Mật khẩu Admin..." required>
                    <button type="submit" name="login">LOGIN</button>
                </form>
            <?php else: ?>
                <form method="POST">
                    <input type="text" name="k" placeholder="Key" required>
                    <input type="date" name="d" value="<?=date('Y-m-d', strtotime('+30 days'))?>">
                    <button type="submit" name="add_key">TẠO KEY</button>
                </form>
                <div style="max-height: 100px; overflow-y: auto; margin-top: 10px; text-align: left;">
                    <?php 
                    $data = file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($data as $i => $l) {
                        $p = explode("|", $l);
                        echo "<div class='key-item'><span>$p[0]</span> <a href='?del=$i' style='color:red; text-decoration:none;'>XÓA</a></div>";
                    }
                    ?>
                </div>
                <a href="?logout" style="color:#444; font-size: 10px; text-decoration: none; display: block; margin-top: 10px;">Đăng xuất</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const c = document.getElementById('c'), ctx = c.getContext('2d');
        c.width = window.innerWidth; c.height = window.innerHeight;
        let particles = [], ripples = [];

        function playAll(e) {
            document.getElementById('bg-audio').play();
            document.getElementById('video-bg').play();
            // TẠO HIỆU ỨNG BÓNG NƯỚC (RIPPLE)
            ripples.push({ x: e.clientX, y: e.clientY, r: 0, o: 0.6 });
        }

        window.onmousemove = (e) => {
            // TẠO HIỆU ỨNG HẠT (PARTICLES)
            for(let i=0; i<2; i++) particles.push({ x: e.clientX, y: e.clientY, s: Math.random()*2+1, vx: Math.random()*2-1, vy: Math.random()*2-1, o: 1 });
        };

        function draw() {
            ctx.clearRect(0,0,c.width,c.height);
            // Vẽ hạt
            particles.forEach((p,i)=>{ 
                p.x+=p.vx; p.y+=p.vy; p.o-=0.02; 
                if(p.o<=0) particles.splice(i,1);
                ctx.fillStyle=`rgba(0,255,213,${p.o})`; ctx.beginPath(); ctx.arc(p.x,p.y,p.s,0,Math.PI*2); ctx.fill(); 
            });
            // Vẽ bóng nước
            ripples.forEach((r,i)=>{ 
                r.r+=2; r.o-=0.01; 
                if(r.o<=0) ripples.splice(i,1);
                ctx.strokeStyle=`rgba(0,255,213,${r.o})`; ctx.lineWidth=2; ctx.beginPath(); ctx.arc(r.x,r.y,r.r,0,Math.PI*2); ctx.stroke();
            });
            requestAnimationFrame(draw);
        }
        draw();
        window.onresize = () => { c.width = window.innerWidth; c.height = window.innerHeight; };
    </script>
</body>
</html>

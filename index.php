<?php
session_start();

// --- CẤU HÌNH ---
$admin_pass = "123456"; 
$db_file = "database.txt";

if (!file_exists($db_file)) { @file_put_contents($db_file, ""); }
@chmod($db_file, 0777);

// --- 1. API TRẢ VỀ CODE LUA AUTOWALK CHO GAME ---
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
        @file_put_contents($db_file, implode("\n", $new_data) . "\n");
        header('Content-Type: text/plain');
        echo "AUTH_SUCCESS|"; 
?>
-- [[ SCRIPT LUA AUTOWALK CHUẨN MIMGUI ]] --
local imgui = require 'mimgui'
local encoding = require 'encoding'
encoding.default = 'CP1251'
local u8 = encoding.UTF8

local renderWin = imgui.new.bool(true)
local autoWalk = imgui.new.bool(false)

imgui.OnFrame(function() return renderWin[0] end, function()
    imgui.SetNextWindowSize(imgui.ImVec2(250, 150), imgui.Cond.FirstUseEver)
    imgui.Begin(u8"BLACK CAT - AUTO WALK", renderWin)
    if imgui.Checkbox(u8"Kích hoạt AutoWalk (W)", autoWalk) then
        sampAddChatMessage(autoWalk[0] and "{00ffd5}[BC]: {ffffff}ON" or "{00ffd5}[BC]: {ffffff}OFF", -1)
    end
    imgui.Text(u8"Key: Đang hoạt động")
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
    } die($status);
}

// --- 2. QUẢN LÝ ADMIN ---
if (isset($_POST['login']) && $_POST['pw'] === $admin_pass) $_SESSION['admin'] = true;
if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); exit; }

if (isset($_SESSION['admin'])) {
    if (isset($_POST['add_key'])) {
        $k = trim($_POST['k']); $d = $_POST['d'];
        if($k) @file_put_contents($db_file, "$k|$d|\n", FILE_APPEND);
    }
    if (isset($_GET['del'])) {
        $data = file($db_file, FILE_IGNORE_NEW_LINES);
        unset($data[$_GET['del']]);
        file_put_contents($db_file, (count($data)>0 ? implode("\n", $data)."\n" : ""));
        header("Location: ?"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Black Cat - Premium Panel</title>
    <style>
        :root { --p: #00ffd5; --bg: rgba(10, 10, 10, 0.85); }
        * { box-sizing: border-box; cursor: crosshair; }
        body, html { margin:0; padding:0; height:100%; overflow:hidden; font-family: 'Segoe UI', sans-serif; background:#000; color:#fff; }
        
        /* Video Nền */
        #bg-video { position:fixed; top:0; left:0; width:100%; height:100%; object-fit:cover; z-index:-1; filter: brightness(0.4); }
        
        canvas { position:fixed; top:0; left:0; pointer-events:none; z-index:5; }
        
        .card { 
            position:relative; z-index:10; background: var(--bg); padding:40px; 
            border-radius:30px; border:1px solid rgba(0,255,213,0.3); width:380px; 
            text-align:center; backdrop-filter:blur(15px); box-shadow: 0 0 40px rgba(0,0,0,0.8);
        }
        
        .avatar { width:100px; height:100px; border-radius:50%; border:3px solid var(--p); box-shadow:0 0 20px var(--p); margin-bottom:20px; }
        h2 { color:var(--p); letter-spacing:4px; text-transform:uppercase; margin:10px 0; }
        
        input { width:100%; background:rgba(0,0,0,0.6); border:1px solid #333; padding:15px; border-radius:12px; color:#fff; margin-bottom:12px; outline:none; text-align:center; }
        input:focus { border-color:var(--p); }
        button { width:100%; background:var(--p); color:#000; padding:15px; border-radius:12px; font-weight:900; border:none; transition:0.3s; cursor:pointer; }
        button:hover { transform:scale(1.05); box-shadow: 0 0 20px var(--p); }
        
        .key-list { margin-top:20px; max-height:200px; overflow-y:auto; text-align:left; border-top:1px solid #333; padding-top:15px; }
        .key-item { background:rgba(255,255,255,0.05); padding:12px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border:1px solid rgba(255,255,255,0.1); }
        .key-item b { color:var(--p); font-size:14px; }
        .key-item small { font-size:10px; color:#888; display:block; }
        .del-link { color:#ff4d4d; text-decoration:none; font-weight:bold; font-size:12px; }
        
        ::-webkit-scrollbar { width:4px; }
        ::-webkit-scrollbar-thumb { background:var(--p); border-radius:10px; }
    </style>
</head>
<body onclick="startAll()">
    <video autoplay muted loop playsinline id="bg-video"><source src="bg.mp4" type="video/mp4"></video>
    <audio id="m" loop><source src="https://files.catbox.moe/uclsqn.mp3" type="audio/mpeg"></audio>
    <canvas id="c"></canvas>

    <div style="display:flex; justify-content:center; align-items:center; height:100vh;">
        <div class="card">
            <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
            <h2>Black Cat VIP</h2>
            <p style="font-size:9px; color:var(--p); letter-spacing:2px;">AUTOWALK SYSTEM ACTIVE</p>

            <?php if (!isset($_SESSION['admin'])): ?>
                <form method="POST">
                    <input type="password" name="pw" placeholder="Mật khẩu: 123456" required>
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
                    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
                    foreach ($data as $i => $l) {
                        $p = explode("|", $l);
                        $stt = empty($p[2]) ? "Trống" : "Đã khóa IP";
                        echo "<div class='key-item'>
                                <div><b>$p[0]</b><small>Hết hạn: $p[1]</small><small style='color:var(--p)'>IP: $stt</small></div>
                                <a href='?del=$i' class='del-link'>XÓA</a>
                              </div>";
                    }
                    ?>
                </div>
                <a href="?logout" style="color:#444; text-decoration:none; font-size:10px; margin-top:15px; display:block;">ĐĂNG XUẤT</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function startAll() { document.getElementById('m').play().catch(()=>{}); }

        const c = document.getElementById('c'), ctx = c.getContext('2d');
        c.width = window.innerWidth; c.height = window.innerHeight;
        let ps = [], rs = [];

        window.onmousemove = (e) => {
            for(let i=0; i<2; i++) ps.push({x:e.x, y:e.y, s:Math.random()*3+1, vx:Math.random()*2-1, vy:Math.random()*2-1});
            if(Math.random()>0.9) rs.push({x:e.x, y:e.y, r:0, o:0.5});
        };

        function draw() {
            ctx.clearRect(0,0,c.width,c.height);
            ps.forEach((p,i)=>{ 
                p.x+=p.vx; p.y+=p.vy; p.s-=0.05; 
                if(p.s<=0) ps.splice(i,1);
                ctx.fillStyle='#00ffd5'; ctx.beginPath(); ctx.arc(p.x,p.y,p.s,0,Math.PI*2); ctx.fill();
            });
            rs.forEach((r,i)=>{ 
                r.r+=2; r.o-=0.01; 
                if(r.o<=0) rs.splice(i,1);
                ctx.strokeStyle=`rgba(0,255,213,${r.o})`; ctx.lineWidth=1.5; ctx.beginPath(); ctx.arc(r.x,r.y,r.r,0,Math.PI*2); ctx.stroke();
            });
            requestAnimationFrame(draw);
        }
        draw();
        window.onresize = () => { c.width = window.innerWidth; c.height = window.innerHeight; };
    </script>
</body>
</html>

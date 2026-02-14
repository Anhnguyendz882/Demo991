<?php
/**
 * 👑 PROJECT: KN BALLAS ULTIMATE (V8000)
 * 🛠️ CORE: GIỮ NGUYÊN AUTOWALK + SAVE/LOAD CONFIG + IP LOCK
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');
$DB_FILE = "kn_database.txt";
$ADMIN_PASS = "Anhnguyendz_99";
if (!file_exists($DB_FILE)) touch($DB_FILE);

// =========================================================
// 🛰️ [1] API TRẢ CODE (GIỮ NGUYÊN 100% CODE CỦA MÀY)
// =========================================================
if (isset($_GET['check_key'])) {
    $user_key = trim($_GET['check_key']);
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $auth_status = "NOT_FOUND";
    
    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $new_data = [];
    foreach ($rows as $row) {
        $data = explode("|", $row); 
        if ($data[0] === $user_key) {
            if (date("Y-m-d") > $data[1]) { $auth_status = "EXPIRED"; }
            elseif ($data[2] !== "NONE" && $data[2] !== $user_ip) { $auth_status = "IP_BLOCKED"; }
            else {
                if ($data[2] === "NONE") $data[2] = $user_ip;
                $auth_status = "SUCCESS";
            }
        }
        $new_data[] = implode("|", $data);
    }
    file_put_contents($DB_FILE, implode("\n", $new_data));
    header('Content-Type: text/plain');

    if ($auth_status === "SUCCESS") {
        echo "AUTH_SUCCESS|"; 
?>
-- [[ GIỮ NGUYÊN 100% CODE CỦA BOSS KN - CHỈ THÊM SAVE/LOAD ]]
script_name("AutoWalk AutoY")
script_author("ChatGPT")

require "lib.moonloader"
local imgui = require "mimgui"
local json = require "dkjson" -- Thư viện lưu config

-------------------------------------------------
-- SETTINGS & PATH
-------------------------------------------------
local config_path = getWorkingDirectory() .. "\\config\\AutoWalk_KN.json"
local spamTime = 1500 
local show = imgui.new.bool(true)
local running = false
local points = {}
local idx = 1

-------------------------------------------------
-- SAVE/LOAD LOGIC (CHỨC NĂNG THÊM THEO Ý MÀY)
-------------------------------------------------
function saveConfig()
    if not doesDirectoryExist(getWorkingDirectory() .. "\\config") then
        createDirectory(getWorkingDirectory() .. "\\config")
    end
    local f = io.open(config_path, "w")
    if f then
        f:write(json.encode(points))
        f:close()
        print("Đã lưu config points!")
    end
end

function loadConfig()
    local f = io.open(config_path, "r")
    if f then
        local content = f:read("*a")
        f:close()
        points = json.decode(content) or {}
        print("Đã tải config points!")
    end
end

-------------------------------------------------
-- KEY PACKET (Y) - GIỮ NGUYÊN
-------------------------------------------------
function sendY()
    local playerId = select(2, sampGetPlayerIdByCharHandle(PLAYER_PED))
    local memPtr = allocateMemory(68)
    sampStorePlayerOnfootData(playerId, memPtr)
    setStructElement(memPtr, 36, 1, 64, false) -- Y key
    sampSendOnfootData(memPtr)
    freeMemory(memPtr)
end

-------------------------------------------------
-- WALK - GIỮ NGUYÊN
-------------------------------------------------
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

-------------------------------------------------
-- UI - THÊM NÚT SAVE/LOAD
-------------------------------------------------
imgui.OnFrame(function() return show[0] end,
function()
    imgui.Begin("AutoWalk AutoY - KN BOSS", show)
    imgui.Text("Points: "..#points)
    imgui.Text("Current: "..idx)
    imgui.Text(running and "RUNNING" or "STOPPED")

    if imgui.Button("Add Point") then
        local x,y,z=getCharCoordinates(PLAYER_PED)
        table.insert(points,{x,y,z})
    end

    if imgui.Button("START") then
        if #points>0 then running=true; idx=1 end
    end

    if imgui.Button("STOP") then
        running=false; setGameKeyState(1,0)
    end

    if imgui.Button("CLEAR") then points={} end

    imgui.Separator() -- Ngăn cách phần lưu
    if imgui.Button("SAVE CONFIG") then saveConfig() end
    imgui.SameLine()
    if imgui.Button("LOAD CONFIG") then loadConfig() end

    imgui.End()
end)

function main()
    repeat wait(0) until isSampAvailable()
    loadConfig() -- Tự động tải config khi mở tool
    sampRegisterChatCommand("awui", function() show[0]=not show[0] end)

    while true do
        wait(0)
        if running and #points>0 then
            local p=points[idx]
            if walk(p) then
                local t=os.clock()
                while os.clock()-t < spamTime/1000 do
                    sendY()
                    wait(120)
                end
                idx = idx + 1
                if idx > #points then idx = 1 end
            end
        end
    end
end
<?php exit; } die("AUTH_ERR|".$auth_status); } ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>KN BALLAS | SYSTEM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --p: #00ffd5; --s: #ff00c1; }
        * { margin:0; padding:0; box-sizing:border-box; font-family: sans-serif; cursor: none; }
        body { background: #000; height: 100vh; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        #bg-v { position: fixed; inset: 0; min-width: 100%; min-height: 100%; z-index: -2; object-fit: cover; filter: brightness(0.2); }
        .glass { width: 380px; padding: 40px; border-radius: 40px; background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid var(--p); text-align: center; }
        input { width: 100%; padding: 15px; background: rgba(0,0,0,0.8); border: 1px solid #333; border-radius: 12px; color: var(--p); text-align: center; margin: 15px 0; outline: none; }
        .btn { width: 100%; padding: 16px; border-radius: 12px; border: none; background: linear-gradient(45deg, var(--p), var(--s)); color: #000; font-weight: 900; cursor: pointer; }
        .avatar { width: 100px; height: 100px; border-radius: 50%; border: 2px solid var(--p); margin-bottom: 15px; }
    </style>
</head>
<body>
    <video id="bg-v" autoplay loop muted playsinline><source src="bg.mp4" type="video/mp4"></video>
    <div class="glass">
        <div id="login-part">
            <h1 style="color:#fff; letter-spacing: 5px;">KN BALLAS</h1>
            <input type="text" id="input-key" placeholder="NHẬP KEY...">
            <button class="btn" onclick="check()">TRUY CẬP</button>
            <p id="msg" style="color:var(--s); font-size: 11px; margin-top: 10px;"></p>
        </div>
        <div id="bio-part" style="display:none;">
            <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
            <h2 style="color:#fff;">BOSS KN</h2>
            <p style="font-size: 10px; color: var(--p); letter-spacing: 5px;">AUTHORIZED</p>
            <div style="margin-top:20px; display:flex; justify-content:center; gap:20px; color:#fff; font-size:20px;">
                <i class="fab fa-discord"></i> <i class="fab fa-youtube"></i> <i class="fab fa-spotify"></i>
            </div>
            <button onclick="window.location='?admin'" style="background:none; border:none; color:#333; font-size:10px; margin-top:15px;">ADMIN PANEL</button>
        </div>
    </div>

    <?php if(isset($_GET['admin'])): ?>
    <div style="position:fixed; inset:0; background:#000; z-index:100; padding:20px; color:var(--p); font-family:monospace;">
        <h2>ADMIN MANAGER</h2>
        <form method="POST">
            <input type="password" name="apw" placeholder="MẬT KHẨU ADMIN" style="width:200px; padding:5px;">
            <input type="text" name="kn" placeholder="Tên Key" style="width:200px; padding:5px;">
            <input type="number" name="kd" placeholder="Số ngày" style="width:100px; padding:5px;">
            <button name="save_key" style="padding:5px 20px; background:var(--p);">TẠO/CỘNG KEY</button>
        </form>
        <hr>
        <table>
            <tr><th>KEY</th><th>HẠN</th><th>IP</th></tr>
            <?php foreach(file($DB_FILE) as $line) { $d=explode("|", $line); echo "<tr><td>$d[0]</td><td>$d[1]</td><td>$d[2]</td></tr>"; } ?>
        </table>
        <button onclick="window.location='index.php'" style="margin-top:20px;">QUAY LẠI</button>
    </div>
    <?php endif; ?>

    <script>
        async function check() {
            const k = document.getElementById('input-key').value;
            const r = await fetch(`index.php?check_key=${k}`);
            const t = await r.text();
            if(t.includes("AUTH_SUCCESS")) {
                document.getElementById('login-part').style.display = 'none';
                document.getElementById('bio-part').style.display = 'block';
            } else { document.getElementById('msg').innerText = t; }
        }
    </script>
    <?php
    if(isset($_POST['save_key']) && $_POST['apw'] === $ADMIN_PASS) {
        $n = $_POST['kn']; $d = $_POST['kd'];
        $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES); $up = []; $found = false;
        foreach($rows as $r) {
            $x = explode("|", $r);
            if($x[0] === $n) { $found = true; $x[1] = date('Y-m-d', strtotime($x[1] . " +$d days")); }
            $up[] = implode("|", $x);
        }
        if(!$found) $up[] = "$n|".date('Y-m-d', strtotime("+$d days"))."|NONE";
        file_put_contents($DB_FILE, implode("\n", $up));
        header("Location: ?admin");
    }
    ?>
</body>
</html>

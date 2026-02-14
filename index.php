<?php
/**
 * 👑 PROJECT: KN BALLAS - SUPREME FINAL (V15.000)
 * 🎨 THEME: PURPLE & PINK CHILL
 * 🛠️ CORE: GIỮ NGUYÊN AUTOWALK + AUTO-START MENU + SAVE/LOAD CONFIG
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');

$DB_FILE = "database.txt";
$ADMIN_PASS = "Anhnguyendz_99";

if (!file_exists($DB_FILE)) { file_put_contents($DB_FILE, ""); }

// =========================================================
// 🛰️ [1] API TRẢ CODE CHO GAME (FIX LỖI KHÔNG HIỆN MENU)
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
-- [[ 🛡️ TRỌN BỘ SCRIPT AUTOWALK CHUẨN BOSS KN ]]
script_name("AutoWalk AutoY")
script_author("KN_BOSS")

require "lib.moonloader"
local imgui = require "mimgui"
local json = require "dkjson"

local config_path = getWorkingDirectory() .. "\\config\\AutoWalk_KN.json"
local spamTime = 1500 
local show = imgui.new.bool(true)
local running = false
local points = {}
local idx = 1

function saveConfig()
    if not doesDirectoryExist(getWorkingDirectory() .. "\\config") then createDirectory(getWorkingDirectory() .. "\\config") end
    local f = io.open(config_path, "w")
    if f then f:write(json.encode(points)) f:close() sampAddChatMessage("{d946ef}[KN]: {ffffff}Da luu toa do!", -1) end
end

function loadConfig()
    local f = io.open(config_path, "r")
    if f then local c = f:read("*a") f:close() points = json.decode(c) or {} sampAddChatMessage("{d946ef}[KN]: {ffffff}Da tai toa do!", -1) end
end

function sendY()
    local pId = select(2, sampGetPlayerIdByCharHandle(PLAYER_PED))
    local m = allocateMemory(68)
    sampStorePlayerOnfootData(pId, m)
    setStructElement(m, 36, 1, 64, false)
    sampSendOnfootData(m)
    freeMemory(m)
end

local function walk(p)
    local x,y,z = getCharCoordinates(PLAYER_PED)
    local dx, dy = p[1]-x, p[2]-y
    local dist = math.sqrt(dx*dx+dy*dy)
    if dist > 1.2 then
        setCharHeading(PLAYER_PED, math.deg(math.atan2(-dx, dy)))
        setGameKeyState(1, 255)
        return false
    else
        setGameKeyState(1, 0)
        return true
    end
end

imgui.OnFrame(function() return show[0] end, function()
    imgui.Begin("AutoWalk AutoY - BOSS KN", show)
    imgui.Text("Points: "..#points)
    imgui.Text("Current: "..idx)
    imgui.Text(running and "STATUS: RUNNING" or "STATUS: STOPPED")
    if imgui.Button("Add Point") then table.insert(points,{getCharCoordinates(PLAYER_PED)}) end
    if imgui.Button("START") then if #points>0 then running, idx = true, 1 end end
    if imgui.Button("STOP") then running = false setGameKeyState(1,0) end
    if imgui.Button("CLEAR") then points = {} end
    imgui.Separator()
    if imgui.Button("SAVE CONFIG") then saveConfig() end
    imgui.SameLine()
    if imgui.Button("LOAD CONFIG") then loadConfig() end
    imgui.End()
end)

function main()
    repeat wait(0) until isSampAvailable()
    loadConfig()
    show[0] = true -- Ép hiện menu ngay khi login xong
    sampRegisterChatCommand("awui", function() show[0]=not show[0] end)
    while true do
        wait(0)
        if running and #points > 0 then
            if walk(points[idx]) then
                local t = os.clock()
                while os.clock()-t < spamTime/1000 do sendY() wait(120) end
                idx = (idx % #points) + 1
            end
        end
    end
end

-- KÍCH HOẠT SCRIPT
lua_thread.create(main)
<?php exit; } die("AUTH_ERR|".$auth_status); }

// =========================================================
// 🔐 [2] XỬ LÝ ADMIN PANEL (GIAO DIỆN TÍM HỒNG CHILL)
// =========================================================
if (isset($_POST['login_boss'])) {
    if ($_POST['boss_pw'] === $ADMIN_PASS) { $_SESSION['kn_boss'] = true; } 
    else { $error = "MẬT KHẨU SAI RỒI ĐMM!"; }
}
if (isset($_POST['create_key']) && $_SESSION['kn_boss']) {
    $name = trim($_POST['key_name']); $days = (int)$_POST['key_days'];
    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES); $up = []; $f = false;
    foreach($rows as $r) {
        $x = explode("|", $r);
        if($x[0] === $name) { $f = true; $x[1] = date('Y-m-d', strtotime(($x[1] > date("Y-m-d") ? $x[1] : date("Y-m-d"))." +$days days")); $x[2] = "NONE"; }
        $up[] = implode("|", $x);
    }
    if(!$f) $up[] = "$name|".date('Y-m-d', strtotime("+$days days"))."|NONE";
    file_put_contents($DB_FILE, implode("\n", $up));
}
if (isset($_GET['del_key']) && $_SESSION['kn_boss']) {
    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES); $up = [];
    foreach($rows as $r) { if(explode("|", $r)[0] !== $_GET['del_key']) $up[] = $r; }
    file_put_contents($DB_FILE, implode("\n", $up)); header("Location: /");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KN BOSS PANEL | CHILL</title>
    <style>
        :root { --p: #d946ef; --s: #f472b6; }
        * { margin:0; padding:0; box-sizing:border-box; font-family: sans-serif; }
        body { background: #0f051d; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        #bg-v { position: fixed; inset: 0; min-width: 100%; min-height: 100%; z-index: -1; object-fit: cover; filter: brightness(0.3) hue-rotate(40deg); }
        .box { width: 90%; max-width: 450px; padding: 40px; border-radius: 30px; background: rgba(20,5,30,0.8); backdrop-filter: blur(20px); border: 1px solid var(--p); text-align: center; box-shadow: 0 0 30px rgba(217, 70, 239, 0.2); }
        input { width: 100%; padding: 15px; margin: 10px 0; background: rgba(0,0,0,0.5); border: 1px solid #333; color: #fff; border-radius: 10px; text-align: center; outline: none; border-bottom: 2px solid var(--p); }
        .btn { width: 100%; padding: 16px; border: none; background: linear-gradient(135deg, var(--p), var(--s)); color: #fff; font-weight: bold; border-radius: 12px; cursor: pointer; text-transform: uppercase; margin-top: 10px; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; font-size: 12px; }
        th, td { padding: 12px; border: 1px solid rgba(255,255,255,0.1); text-align: left; }
        .avatar { width: 100px; height: 100px; border-radius: 50%; border: 3px solid var(--p); margin-bottom: 15px; box-shadow: 0 0 20px var(--p); }
    </style>
</head>
<body>
    <video id="bg-v" autoplay loop muted playsinline><source src="bg.mp4" type="video/mp4"></video>
    <div class="box">
        <?php if (!$_SESSION['kn_boss']): ?>
            <h1 style="color:var(--p); letter-spacing: 5px;">KN BALLAS</h1>
            <p style="font-size: 10px; margin-bottom: 20px; opacity: 0.6;">ADMIN CONTROL CENTER</p>
            <form method="POST">
                <input type="password" name="boss_pw" placeholder="ADMIN PASSWORD..." required>
                <button class="btn" name="login_boss">TRUY CẬP</button>
            </form>
            <?php if($error) echo "<p style='color:var(--s); margin-top:15px;'>$error</p>"; ?>
        <?php else: ?>
            <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
            <h2 style="color:var(--p);">BOSS PANEL</h2>
            <p style="font-size: 11px; margin: 10px 0;">
                <a href="/" style="color:#fff; text-decoration:none;">[ REFRESH ]</a> | 
                <a href="?logout" style="color:var(--s); text-decoration:none;">[ LOGOUT ]</a>
            </p>
            <form method="POST" style="margin-top:20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <input type="text" name="key_name" placeholder="Tên Key Khách" required>
                <input type="number" name="key_days" placeholder="Số ngày" required>
                <button class="btn" name="create_key">CẤP KEY MỚI</button>
            </form>
            <table>
                <tr><th>KEY</th><th>HẠN</th><th>X</th></tr>
                <?php 
                $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES);
                foreach($rows as $r): $d = explode("|", $r); ?>
                <tr>
                    <td><b style="color:var(--p);"><?php echo $d[0]; ?></b></td>
                    <td><?php echo $d[1]; ?></td>
                    <td><a href="?del_key=<?php echo $d[0]; ?>" style="color:var(--s); text-decoration:none; font-weight:bold;">✕</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    <?php if(isset($_GET['logout'])) { session_destroy(); header("Location: /"); } ?>
</body>
</html>

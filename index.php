<?php
/**
 * 👑 PROJECT: KN BALLAS - SUPREME FINAL (V18.000)
 * 🛡️ SECURITY FIX: CHỐNG LỘ CODE KHI TRUY CẬP TRỰC TIẾP
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');

$DB_FILE = "database.txt";
$ADMIN_PASS = "Anhnguyendz_99";

if (!file_exists($DB_FILE)) { file_put_contents($DB_FILE, ""); }

// =========================================================
// 🛰️ [1] API CHECK KEY (CHỈ TRẢ CODE KHI KEY ĐÚNG)
// =========================================================
if (isset($_GET['check_key']) && !empty($_GET['check_key'])) {
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

    // CHỈ XUẤT CODE NẾU AUTH_STATUS LÀ SUCCESS
    if ($auth_status === "SUCCESS") {
        $lua_code = '-- [[ 🛡️ SCRIPT AUTOWALK CHUẨN BOSS KN ]]
script_name("AutoWalk AutoY")
script_author("KN_BOSS")

require "lib.moonloader"
local imgui = require "mimgui"
local json = require "dkjson"

local config_path = getWorkingDirectory() .. "\\\\config\\\\AutoWalk_KN.json"
local spamTime = 1500 
local show = imgui.new.bool(true)
local running = false
local points = {}
local idx = 1

function saveConfig()
    if not doesDirectoryExist(getWorkingDirectory() .. "\\\\config") then createDirectory(getWorkingDirectory() .. "\\\\config") end
    local f = io.open(config_path, "w")
    if f then f:write(json.encode(points)) f:close() sampAddChatMessage("{d946ef}[KN]: {ffffff}Da luu toa do!", -1) end
end

function loadConfig()
    local f = io.open(config_path, "r")
    if f then local c = f:read("*a") f:close() points = json.decode(c) or {} end
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
    show[0] = true
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

lua_thread.create(main)';

        echo "AUTH_SUCCESS|" . $lua_code;
    } else {
        echo "AUTH_ERR|" . $auth_status;
    }
    exit;
}

// =========================================================
// 🔐 [2] QUẢN LÝ ADMIN PANEL (HIỆN GIAO DIỆN NẾU KHÔNG CHECK KEY)
// =========================================================
if (isset($_POST['login_boss'])) {
    if ($_POST['boss_pw'] === $ADMIN_PASS) { $_SESSION['kn_boss'] = true; } 
    else { $error = "MẬT KHẨU SAI!"; }
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
    file_put_contents($DB_FILE, implode("\n", $up)); header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>KN BOSS PANEL</title>
    <style>
        :root { --p: #d946ef; --s: #f472b6; }
        body { background: #0f051d; color: #fff; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { width: 400px; padding: 30px; border-radius: 20px; background: rgba(20,5,30,0.8); border: 1px solid var(--p); text-align: center; }
        input { width: 90%; padding: 12px; margin: 10px 0; background: #000; border: 1px solid #333; color: #fff; border-radius: 8px; text-align: center; outline: none; }
        .btn { width: 95%; padding: 15px; border: none; background: linear-gradient(45deg, var(--p), var(--s)); color: #fff; font-weight: bold; border-radius: 10px; cursor: pointer; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 10px; border: 1px solid #333; }
    </style>
</head>
<body>
    <div class="box">
        <?php if (!$_SESSION['kn_boss']): ?>
            <h1 style="color:var(--p)">KN BALLAS</h1>
            <form method="POST">
                <input type="password" name="boss_pw" placeholder="MẬT KHẨU ADMIN..." required>
                <button class="btn" name="login_boss">VÀO PANEL</button>
            </form>
            <?php if($error) echo "<p style='color:red'>$error</p>"; ?>
        <?php else: ?>
            <h2 style="color:var(--p)">BOSS PANEL</h2>
            <form method="POST">
                <input type="text" name="key_name" placeholder="Tên Key" required>
                <input type="number" name="key_days" placeholder="Số ngày" required>
                <button class="btn" name="create_key">TẠO KEY</button>
            </form>
            <table>
                <tr><th>KEY</th><th>HẠN</th><th>X</th></tr>
                <?php $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES); foreach($rows as $r): $d = explode("|", $r); if($d[0]): ?>
                <tr><td><?php echo $d[0]; ?></td><td><?php echo $d[1]; ?></td><td><a href="?del_key=<?php echo $d[0]; ?>" style="color:red; text-decoration:none;">X</a></td></tr>
                <?php endif; endforeach; ?>
            </table>
            <br><a href="?logout=true" style="color:#555">Đăng xuất</a>
        <?php endif; ?>
    </div>
</body>
</html>
<?php if(isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); } ?>

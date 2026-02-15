<?php
/**
 * 👑 PROJECT: KN BALLAS - SUPREME FINAL (V20.000)
 * 🛡️ SECURITY: CHỐNG LỘ CODE TRÊN TRÌNH DUYỆT (ANTI-VIEW SOURCE)
 * 🛠️ CORE: AUTOWALK + AUTO-START MENU
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');

$DB_FILE = "database.txt";
$ADMIN_PASS = "Anhnguyendz_99";

if (!file_exists($DB_FILE)) { file_put_contents($DB_FILE, ""); }

// =========================================================
// 🛰️ [1] XỬ LÝ TRẢ CODE (CHỈ CHẤP NHẬN POST TỪ TOOL)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_key'])) {
    $user_key = trim($_POST['check_key']);
    $auth_status = "NOT_FOUND";
    
    // Kiểm tra database
    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($rows as $row) {
        $data = explode("|", $row); 
        if ($data[0] === $user_key) {
            if (date("Y-m-d") > $data[1]) { $auth_status = "EXPIRED"; }
            else { $auth_status = "SUCCESS"; }
            break;
        }
    }

    header('Content-Type: text/plain');
    if ($auth_status === "SUCCESS") {
        // TOÀN BỘ CODE LUA ĐƯỢC GIẤU TRONG BIẾN NÀY
        $lua_script = '
script_name("AutoWalk AutoY")
script_author("KN_BOSS")
require "lib.moonloader"
local imgui = require "mimgui"
local json = require "dkjson"
local config_path = getWorkingDirectory() .. "\\\\config\\\\AutoWalk_KN.json"
local spamTime, show, running, points, idx = 1500, imgui.new.bool(true), false, {}, 1

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
    if imgui.Button("Add Point") then table.insert(points,{getCharCoordinates(PLAYER_PED)}) end
    if imgui.Button("START") then if #points>0 then running, idx = true, 1 end end
    if imgui.Button("STOP") then running = false setGameKeyState(1,0) end
    if imgui.Button("CLEAR") then points = {} end
    imgui.Separator()
    if imgui.Button("SAVE CONFIG") then saveConfig() end
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

        echo "AUTH_SUCCESS|" . $lua_script;
    } else {
        echo "AUTH_ERR|" . $auth_status;
    }
    exit;
}

// =========================================================
// 🔐 [2] XỬ LÝ ADMIN PANEL (CHỈ HIỆN KHI VÀO TRÌNH DUYỆT)
// =========================================================
if (isset($_POST['login_boss'])) {
    if ($_POST['boss_pw'] === $ADMIN_PASS) { $_SESSION['kn_boss'] = true; } 
    else { $error = "SAI MẬT KHẨU!"; }
}
if (isset($_POST['create_key']) && $_SESSION['kn_boss']) {
    $name = trim($_POST['key_name']); $days = (int)$_POST['key_days'];
    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES); $up = []; $f = false;
    foreach($rows as $r) {
        $x = explode("|", $r);
        if($x[0] === $name) { $f = true; $x[1] = date('Y-m-d', strtotime(($x[1] > date("Y-m-d") ? $x[1] : date("Y-m-d"))." +$days days")); }
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
        body { background: #0f051d; color: #fff; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .box { width: 380px; padding: 35px; border-radius: 20px; background: rgba(20,5,30,0.9); border: 1px solid var(--p); text-align: center; box-shadow: 0 0 20px rgba(217, 70, 239, 0.3); }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #000; border: 1px solid #333; color: #fff; border-radius: 8px; text-align: center; box-sizing: border-box; }
        .btn { width: 100%; padding: 15px; border: none; background: linear-gradient(45deg, var(--p), var(--s)); color: #fff; font-weight: bold; border-radius: 10px; cursor: pointer; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; font-size: 12px; }
        th, td { padding: 8px; border: 1px solid #333; }
    </style>
</head>
<body>
    <div class="box">
        <?php if (!$_SESSION['kn_boss']): ?>
            <h1 style="color:var(--p)">KN BALLAS</h1>
            <form method="POST">
                <input type="password" name="boss_pw" placeholder="ADMIN PASSWORD" required>
                <button class="btn" name="login_boss">VÀO HỆ THỐNG</button>
            </form>
            <?php if($error) echo "<p style='color:red; margin-top:10px;'>$error</p>"; ?>
        <?php else: ?>
            <h2 style="color:var(--p)">ADMIN PANEL</h2>
            <form method="POST">
                <input type="text" name="key_name" placeholder="Tên Key" required>
                <input type="number" name="key_days" placeholder="Số ngày" required>
                <button class="btn" name="create_key">CẤP KEY MỚI</button>
            </form>
            <table>
                <tr><th>KEY</th><th>HẠN</th><th>X</th></tr>
                <?php $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES); foreach($rows as $r): $d = explode("|", $r); if($d[0]): ?>
                <tr><td><?php echo $d[0]; ?></td><td><?php echo $d[1]; ?></td><td><a href="?del_key=<?php echo $d[0]; ?>" style="color:red; text-decoration:none;">✕</a></td></tr>
                <?php endif; endforeach; ?>
            </table>
            <br><a href="?logout=true" style="color:#555; text-decoration:none;">Đăng xuất</a>
        <?php endif; ?>
    </div>
</body>
</html>
<?php if(isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); } ?>

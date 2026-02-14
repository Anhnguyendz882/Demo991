<?php
/**
 * 👑 PROJECT: KN BALLAS - ADMIN DIRECT (V10.000)
 * 🛠️ LOGIC: TRANG CHỦ = BẢNG ADMIN
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');
$DB_FILE = "kn_database.txt";
$ADMIN_PASS = "Anhnguyendz_99"; // Pass duy nhất để mở hệ thống

if (!file_exists($DB_FILE)) { file_put_contents($DB_FILE, ""); }

// =========================================================
// 🛰️ [1] API CHO TOOL (GIỮ NGUYÊN ĐỂ TOOL CHẠY)
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
-- [[ CODE AUTOWALK + SAVE CONFIG CỦA MÀY ]]
script_name("AutoWalk AutoY")
require "lib.moonloader"
local imgui, json = require "mimgui", require "dkjson"
local config_path = getWorkingDirectory() .. "\\config\\AutoWalk_KN.json"
local spamTime, show, running, points, idx = 1500, imgui.new.bool(true), false, {}, 1

function saveConfig()
    if not doesDirectoryExist(getWorkingDirectory() .. "\\config") then createDirectory(getWorkingDirectory() .. "\\config") end
    local f = io.open(config_path, "w")
    if f then f:write(json.encode(points)) f:close() end
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
function main()
    repeat wait(0) until isSampAvailable()
    loadConfig()
    while true do
        wait(0)
        if running and #points > 0 then
            local p = points[idx]
            local x,y,z = getCharCoordinates(PLAYER_PED)
            if math.sqrt((p[1]-x)^2 + (p[2]-y)^2) > 1.2 then
                setCharHeading(PLAYER_PED, math.deg(math.atan2(-(p[1]-x), p[2]-y)))
                setGameKeyState(1, 255)
            else
                setGameKeyState(1, 0)
                local t = os.clock()
                while os.clock()-t < spamTime/1000 do sendY() wait(120) end
                idx = (idx % #points) + 1
            end
        end
    end
end
-- [[ UI IMGUE GIỮ NGUYÊN NHƯ CŨ ]]
<?php exit; } die("AUTH_ERR|".$auth_status); }

// =========================================================
// 🔐 [2] XỬ LÝ ĐĂNG NHẬP ADMIN & THAO TÁC
// =========================================================
if (isset($_POST['login_admin'])) {
    if ($_POST['admin_pw'] === $ADMIN_PASS) {
        $_SESSION['boss_active'] = true;
    } else { $err = "SAI PASS RỒI THẰNG LỒN!"; }
}
if (isset($_POST['add_key']) && $_SESSION['boss_active']) {
    $n = trim($_POST['kn']); $d = (int)$_POST['kd'];
    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES); $up = []; $f = false;
    foreach($rows as $r) {
        $x = explode("|", $r);
        if($x[0] === $n) { $f = true; $x[1] = date('Y-m-d', strtotime(($x[1] > date("Y-m-d") ? $x[1] : date("Y-m-d"))." +$d days")); $x[2] = "NONE"; }
        $up[] = implode("|", $x);
    }
    if(!$f) $up[] = "$n|".date('Y-m-d', strtotime("+$d days"))."|NONE";
    file_put_contents($DB_FILE, implode("\n", $up));
}
if (isset($_GET['del']) && $_SESSION['boss_active']) {
    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES); $up = [];
    foreach($rows as $r) { if(explode("|", $r)[0] !== $_GET['del']) $up[] = $r; }
    file_put_contents($DB_FILE, implode("\n", $up));
    header("Location: /");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>KN BOSS PANEL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --p: #00ffd5; --s: #ff00c1; }
        * { margin:0; padding:0; box-sizing:border-box; font-family: monospace; }
        body { background: #000; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow-x: hidden; }
        #bg-v { position: fixed; inset: 0; min-width: 100%; min-height: 100%; z-index: -2; object-fit: cover; filter: brightness(0.2); }
        .glass { width: 90%; max-width: 500px; padding: 30px; border-radius: 20px; background: rgba(255,255,255,0.02); backdrop-filter: blur(20px); border: 1px solid #222; box-shadow: 0 0 30px rgba(0,0,0,0.5); }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #111; border: 1px solid #333; color: var(--p); border-radius: 8px; outline: none; }
        .btn { width: 100%; padding: 15px; border: none; background: var(--p); color: #000; font-weight: bold; cursor: pointer; border-radius: 8px; text-transform: uppercase; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; font-size: 12px; }
        th, td { padding: 10px; border: 1px solid #222; text-align: left; }
        .status-ok { color: var(--p); }
        .status-err { color: var(--s); }
        .avatar { width: 80px; height: 80px; border-radius: 50%; border: 2px solid var(--p); margin-bottom: 10px; }
    </style>
</head>
<body>
    <video id="bg-v" autoplay loop muted playsinline><source src="bg.mp4" type="video/mp4"></video>

    <div class="glass">
        <?php if (!$_SESSION['boss_active']): ?>
            <div style="text-align:center;">
                <h1 style="color:var(--p); letter-spacing: 5px;">KN BALLAS</h1>
                <p style="font-size: 10px; opacity: 0.5;">ENTER ADMIN PASSWORD TO MANAGE</p>
                <form method="POST">
                    <input type="password" name="admin_pw" placeholder="MẬT KHẨU BOSS..." required>
                    <button class="btn" name="login_admin">MỞ HỆ THỐNG</button>
                </form>
                <?php if($err) echo "<p class='status-err' style='margin-top:10px;'>$err</p>"; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;">
                <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
                <h2 style="color:var(--p);">BOSS KN PANEL</h2>
                <div style="display:flex; justify-content:center; gap:15px; margin: 10px 0;">
                    <a href="https://demo991.onrender.com/" style="color:#555; text-decoration:none; font-size:10px;">[ REFRESH ]</a>
                    <a href="?logout" style="color:var(--s); text-decoration:none; font-size:10px;">[ THOÁT ]</a>
                </div>
            </div>

            <form method="POST" style="margin-top:20px; border-top: 1px solid #222; padding-top:20px;">
                <p style="font-size:11px; color:var(--p);">TẠO KEY MỚI HOẶC GIA HẠN:</p>
                <input type="text" name="kn" placeholder="Tên Key (VD: Huy99)" required>
                <input type="number" name="kd" placeholder="Số ngày sử dụng" required>
                <button class="btn" name="add_key">XÁC NHẬN TẠO</button>
            </form>

            <table>
                <tr><th>KEY</th><th>HẾT HẠN</th><th>IP LOCK</th><th>XÓA</th></tr>
                <?php 
                $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES);
                foreach($rows as $r): $d = explode("|", $r); ?>
                <tr>
                    <td><b><?php echo $d[0]; ?></b></td>
                    <td class="<?php echo (date("Y-m-d") > $d[1]) ? 'status-err' : 'status-ok'; ?>">
                        <?php echo $d[1]; ?>
                    </td>
                    <td style="font-size:10px;"><?php echo $d[2]; ?></td>
                    <td><a href="?del=<?php echo $d[0]; ?>" style="color:red; text-decoration:none;">[X]</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <?php if(isset($_GET['logout'])) { session_destroy(); header("Location: /"); } ?>
</body>
</html>

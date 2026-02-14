<?php
/**
 * 👑 PROJECT: KN BALLAS - CHILL EDITION (V12.000)
 * 🎨 THEME: PURPLE & PINK SYNTHWAVE
 * 🛠️ CORE: GIỮ NGUYÊN AUTOWALK + ADMIN DIRECT ACCESS
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');

$DB_FILE = "database.txt";
$ADMIN_PASS = "Anhnguyendz_99"; // PASS ADMIN

if (!file_exists($DB_FILE)) { file_put_contents($DB_FILE, ""); }

// =========================================================
// 🛰️ [1] API TOOL (KHÔNG ĐỤNG VÀO)
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
-- [[ 🛡️ AUTOWALK CỦA BOSS KN (GIỮ NGUYÊN 100%) ]]
script_name("AutoWalk AutoY")
script_author("KN_BOSS")
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
    sampRegisterChatCommand("awui", function() show[0]=not show[0] end)
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
-- [[ UI IMGUE CỦA MÀY NẰM TRONG TOOL RỒI ]]
<?php exit; } die("AUTH_ERR|".$auth_status); }

// =========================================================
// 🔐 [2] XỬ LÝ ADMIN (VÀO LINK LÀ GẶP)
// =========================================================
if (isset($_POST['login_boss'])) {
    if ($_POST['boss_pw'] === $ADMIN_PASS) { $_SESSION['kn_boss'] = true; } 
    else { $error = "MẬT KHẨU SAI RỒI ĐMM!"; }
}
if (isset($_POST['create_key']) && $_SESSION['kn_boss']) {
    $name = trim($_POST['key_name']); $days = (int)$_POST['key_days'];
    $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES); $up = []; $found = false;
    foreach($rows as $r) {
        $x = explode("|", $r);
        if($x[0] === $name) { $found = true; $x[1] = date('Y-m-d', strtotime(($x[1] > date("Y-m-d") ? $x[1] : date("Y-m-d"))." +$days days")); $x[2] = "NONE"; }
        $up[] = implode("|", $x);
    }
    if(!$found) $up[] = "$name|".date('Y-m-d', strtotime("+$days days"))."|NONE";
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        /* --- BẢNG MÀU TÍM HỒNG CHILL --- */
        :root {
            --p: #d946ef; /* Tím hồng sáng */
            --s: #f472b6; /* Hồng phấn */
            --bg-glass: rgba(20, 5, 30, 0.6); /* Nền kính tối màu tím */
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Montserrat', sans-serif; }
        body { background: #0f051d; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        /* Video nền sẽ bị ám màu tím để hợp theme */
        #bg-v { position: fixed; inset: 0; min-width: 100%; min-height: 100%; z-index: -1; object-fit: cover; filter: brightness(0.4) hue-rotate(40deg); }
        
        .box {
            width: 90%; max-width: 450px; padding: 40px;
            border-radius: 30px;
            background: var(--bg-glass);
            backdrop-filter: blur(30px); /* Blur mạnh hơn */
            border: 1px solid rgba(217, 70, 239, 0.3);
            box-shadow: 0 0 50px rgba(217, 70, 239, 0.15);
            text-align: center;
        }

        h1, h2 { text-transform: uppercase; letter-spacing: 2px; text-shadow: 0 0 15px var(--p); }

        input {
            width: 100%; padding: 15px; margin: 12px 0;
            background: rgba(0,0,0,0.4);
            border: 1px solid #333;
            border-bottom: 3px solid var(--p); /* Nhấn viền dưới */
            color: #fff; border-radius: 10px; text-align: center; outline: none; transition: 0.3s;
        }
        input:focus { border-color: var(--s); box-shadow: 0 5px 20px -10px var(--s); }

        .btn {
            width: 100%; padding: 16px; border: none;
            background: linear-gradient(135deg, var(--p), var(--s));
            color: #fff; font-weight: 900; border-radius: 12px; cursor: pointer;
            text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
            box-shadow: 0 5px 20px -5px rgba(217, 70, 239, 0.5);
        }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 10px 30px -5px rgba(217, 70, 239, 0.7); }

        table { width: 100%; margin-top: 25px; border-collapse: separate; border-spacing: 0 8px; font-size: 12px; }
        th { color: var(--s); padding: 10px; font-size: 10px; letter-spacing: 1px; }
        td { padding: 12px; background: rgba(255,255,255,0.03); }
        td:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; color: var(--p); font-weight: bold; }
        td:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

        .avatar { width: 100px; height: 100px; border-radius: 50%; border: 3px solid var(--p); margin-bottom: 15px; box-shadow: 0 0 30px var(--p); }
        .links a { color: rgba(255,255,255,0.6); text-decoration: none; font-size: 11px; margin: 0 10px; transition: 0.3s; }
        .links a:hover { color: var(--s); }
    </style>
</head>
<body>
    <video id="bg-v" autoplay loop muted playsinline><source src="bg.mp4" type="video/mp4"></video>

    <div class="box">
        <?php if (!$_SESSION['kn_boss']): ?>
            <h1 style="color:var(--p); margin-bottom: 10px;">KN BALLAS</h1>
            <p style="font-size: 10px; opacity: 0.7; margin-bottom: 30px;">MASTER CONTROL SYSTEM</p>
            <form method="POST">
                <input type="password" name="boss_pw" placeholder="ADMIN PASSWORD..." required>
                <button class="btn" name="login_boss">TRUY CẬP</button>
            </form>
            <?php if($error) echo "<p style='color:var(--s); margin-top:20px; font-weight:bold;'>$error</p>"; ?>
        <?php else: ?>
            <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
            <h2 style="color:var(--p);">BOSS PANEL</h2>
            <div class="links" style="margin: 15px 0;">
                <a href="/">[ LÀM MỚI ]</a> <a href="?logout" style="color:var(--s);">[ ĐĂNG XUẤT ]</a>
            </div>

            <form method="POST" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; margin-top: 20px;">
                <input type="text" name="key_name" placeholder="Tên Key Khách" required>
                <input type="number" name="key_days" placeholder="Số ngày cấp" required>
                <button class="btn" name="create_key">CẤP KEY / GIA HẠN</button>
            </form>

            <table>
                <tr><th>KEY</th><th>HẠN DÙNG</th><th>IP LOCK</th><th>XÓA</th></tr>
                <?php 
                $rows = file($DB_FILE, FILE_IGNORE_NEW_LINES);
                foreach($rows as $r): $d = explode("|", $r); ?>
                <tr>
                    <td><?php echo $d[0]; ?></td>
                    <td style="color:<?php echo (date("Y-m-d") > $d[1]) ? 'var(--s)' : '#fff'; ?>;"><?php echo $d[1]; ?></td>
                    <td style="font-size:9px; opacity:0.7;"><?php echo $d[2]; ?></td>
                    <td><a href="?del_key=<?php echo $d[0]; ?>" style="color:var(--s); text-decoration:none; font-weight:bold;">✕</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <?php if(isset($_GET['logout'])) { session_destroy(); header("Location: /"); } ?>
</body>
</html>

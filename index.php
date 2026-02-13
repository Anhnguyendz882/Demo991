<?php
session_start();

// --- CẤU HÌNH ---
$admin_pass = "123456"; 
$db_file = "database.txt";

// ÉP QUYỀN GHI FILE ĐỂ TRÁNH LỖI PERMISSION
if (!file_exists($db_file)) { @file_put_contents($db_file, ""); }
@chmod($db_file, 0777);

// --- PHẦN 1: API CHECK KEY & GỬI RUỘT SCRIPT ---
if (isset($_GET['check_key'])) {
    $key_input = trim($_GET['check_key']);
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $new_data = [];
    $found = false;

    foreach ($data as $line) {
        $p = explode("|", $line);
        if (count($p) < 2) continue;
        
        $s_key = $p[0]; 
        $expiry = $p[1];
        $l_ip = isset($p[2]) ? trim($p[2]) : "";

        if ($s_key === $key_input) {
            // Check hạn dùng
            if (date("Y-m-d") > $expiry) die("EXPIRED");
            
            // Check IP (Anti-Share)
            if ($l_ip === "") {
                $l_ip = $user_ip; // Khóa IP lần đầu
                $found = true;
            } elseif ($l_ip === $user_ip) {
                $found = true;
            } else {
                die("WRONG_IP");
            }
        }
        $new_data[] = "$s_key|$expiry|$l_ip";
    }

    if ($found) {
        @file_put_contents($db_file, implode("\n", $new_data) . "\n");
        header('Content-Type: text/plain');
        echo "AUTH_SUCCESS|";
?>
-- [[ RUỘT SCRIPT AUTOWALK + AUTO Y - LOAD TỪ SERVER ]] --
local imgui = require("mimgui")
local show = imgui.new.bool(true)
local running = false
local points = {}
local idx = 1
local delay = imgui.new.int(1500)
local waiting, reachTime = false, 0
local autoY = imgui.new.bool(false)
local yDelay = imgui.new.int(1500)
local lastY = 0

local function walkTo(p)
    local x,y,z = getCharCoordinates(PLAYER_PED)
    local dx, dy = p.x-x, p.y-y
    if math.sqrt(dx*dx+dy*dy) > 1.2 then
        setCharHeading(PLAYER_PED, math.deg(math.atan2(-dx,dy)))
        setGameKeyState(1,255)
        return false
    else
        setGameKeyState(1,0)
        return true
    end
end

local function pressY()
    local id = select(2, sampGetPlayerIdByCharHandle(PLAYER_PED))
    local mem = allocateMemory(68)
    sampStorePlayerOnfootData(id, mem)
    setStructElement(mem, 36, 1, 64, false)
    sampSendOnfootData(mem)
    freeMemory(mem)
end

lua_thread.create(function()
    while true do
        wait(0)
        if running and #points > 0 then
            local p = points[idx]
            if not waiting then
                if walkTo(p) then waiting = true reachTime = os.clock() end
            elseif os.clock()-reachTime >= delay[0]/1000 then
                pressY()
                idx = idx + 1
                if idx > #points then idx = 1 end
                waiting = false
            end
        end
        if autoY[0] and os.clock()-lastY >= yDelay[0]/1000 then
            pressY()
            lastY = os.clock()
        end
    end
end)

imgui.OnFrame(function() return show[0] end, function()
    imgui.SetNextWindowSize(imgui.ImVec2(280, 350), imgui.Cond.FirstUseEver)
    imgui.Begin("Black Cat VIP - AutoWalk", show)
    if imgui.Button("START", imgui.ImVec2(115, 40)) then running = true idx = 1 end
    imgui.SameLine()
    if imgui.Button("STOP", imgui.ImVec2(115, 40)) then running = false setGameKeyState(1,0) end
    if imgui.Button("ADD POINT", imgui.ImVec2(-1, 40)) then
        local x,y,z = getCharCoordinates(PLAYER_PED)
        table.insert(points, {x=x, y=y, z=z})
    end
    imgui.Text("Points: " .. #points)
    imgui.Checkbox("Auto Y (Independent)", autoY)
    imgui.SliderInt("Delay Y (ms)", yDelay, 100, 5000)
    imgui.End()
end)
sampAddChatMessage("{00FFD5}[Server]: {FFFFFF}Script Loaded! Nhan /nhapkey neu menu an.", -1)
<?php
        exit;
    } else { die("NOT_FOUND"); }
}

// --- PHẦN 2: QUẢN LÝ ADMIN PANEL ---
if (isset($_POST['login']) && $_POST['pw'] == $admin_pass) $_SESSION['admin'] = true;
if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); exit; }

if (isset($_POST['add_key']) && isset($_SESSION['admin'])) {
    $k = trim($_POST['k']); $d = $_POST['d'];
    if(!empty($k)) @file_put_contents($db_file, "$k|$d|\n", FILE_APPEND);
}

if (isset($_GET['del']) && isset($_SESSION['admin'])) {
    $data = file($db_file, FILE_IGNORE_NEW_LINES);
    unset($data[$_GET['del']]);
    file_put_contents($db_file, (count($data) > 0 ? implode("\n", $data)."\n" : ""));
    header("Location: ?"); exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Black Cat</title>
    <style>
        :root { --p: #00ffd5; }
        body { 
            margin: 0; 
            font-family: 'Segoe UI', sans-serif; 
            background: #000 url('bg.gif') no-repeat center center fixed; 
            background-size: cover; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            color: white; 
        }
        .card { 
            background: rgba(18, 18, 18, 0.85); 
            padding: 30px; 
            border-radius: 20px; 
            border: 1px solid var(--p); 
            text-align: center; 
            width: 350px; 
            box-shadow: 0 0 25px rgba(0, 255, 213, 0.4); 
            backdrop-filter: blur(8px);
        }
        .avatar { width: 90px; height: 90px; border-radius: 50%; border: 3px solid var(--p); margin-bottom: 15px; box-shadow: 0 0 15px var(--p); }
        input, button { width: 100%; padding: 12px; margin: 10px 0; border-radius: 10px; border: 1px solid #333; box-sizing: border-box; font-size: 14px; }
        input { background: rgba(0,0,0,0.5); color: #fff; outline: none; }
        input:focus { border-color: var(--p); }
        button { background: var(--p); color: #000; font-weight: bold; cursor: pointer; border: none; transition: 0.3s; }
        button:hover { background: #fff; box-shadow: 0 0 20px var(--p); transform: translateY(-2px); }
        .key-list { margin-top: 20px; text-align: left; max-height: 200px; overflow-y: auto; padding-right: 5px; }
        .item { padding: 12px; border-bottom: 1px solid #333; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.03); margin-bottom: 5px; border-radius: 5px; }
        .del-btn { color: #ff4d4d; text-decoration: none; font-weight: bold; font-size: 12px; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: var(--p); border-radius: 10px; }
    </style>
</head>
<body>
<div class="card">
    <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
    <h2 style="margin: 0; color: var(--p); letter-spacing: 1px;">BLACK CAT PANEL</h2>
    <p style="font-size: 10px; color: #888; margin-bottom: 20px;">SYSTEM SECURED BY AI</p>

    <?php if (!isset($_SESSION['admin'])): ?>
        <form method="POST">
            <input type="password" name="pw" placeholder="Mật khẩu hệ thống..." required>
            <button type="submit" name="login">XÁC THỰC</button>
        </form>
    <?php else: ?>
        <form method="POST">
            <input type="text" name="k" placeholder="Tên Key muốn tạo..." required>
            <input type="date" name="d" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
            <button type="submit" name="add_key">PHÁT HÀNH KEY</button>
        </form>
        <div class="key-list">
            <?php 
            if (file_exists($db_file)) {
                $data = file($db_file, FILE_IGNORE_NEW_LINES);
                foreach ($data as $idx => $line) {
                    $p = explode("|", $line); if(empty($p[0])) continue;
                    $ip_status = (empty($p[2])) ? "Trống" : "Đã khóa";
                    echo "<div class='item'>
                            <span><b style='color:var(--p)'>$p[0]</b><br><small style='font-size:10px; color:#aaa'>Hạn: $p[1] | IP: $ip_status</small></span>
                            <a href='?del=$idx' class='del-btn'>XÓA</a>
                          </div>";
                }
            }
            ?>
        </div>
        <a href="?logout" style="color:#444; text-decoration:none; font-size: 11px; margin-top:15px; display:block;">ĐĂNG XUẤT HỆ THỐNG</a>
    <?php endif; ?>
</div>
</body>
</html>

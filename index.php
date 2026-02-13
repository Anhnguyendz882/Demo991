<?php
session_start();

// --- CẤU HÌNH ---
$admin_pass = "123456"; 
$db_file = "database.txt";

// TỰ ĐỘNG FIX QUYỀN GHI
if (!file_exists($db_file)) { @file_put_contents($db_file, ""); }
@chmod($db_file, 0777);

// --- PHẦN 1: API CHECK KEY & GỬI CODE LUA ---
if (isset($_GET['check_key'])) {
    $key_input = $_GET['check_key'];
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $new_data = [];
    $found = false;
    $expiry_date = "";

    foreach ($data as $line) {
        $parts = explode("|", $line);
        if (count($parts) < 2) continue;
        $saved_key = $parts[0];
        $expiry = $parts[1];
        $locked_ip = isset($parts[2]) ? $parts[2] : "";

        if ($saved_key === $key_input) {
            if (date("Y-m-d") <= $expiry) {
                if ($locked_ip === "" || $locked_ip === $user_ip) {
                    $found = true;
                    $locked_ip = $user_ip;
                    $expiry_date = $expiry;
                }
            }
        }
        $new_data[] = "$saved_key|$expiry|$locked_ip";
    }

    if ($found) {
        @file_put_contents($db_file, implode("\n", $new_data) . "\n");
        // TRẢ VỀ CODE LUA AUTOWALK + AUTOY KHI KEY ĐÚNG
        header('Content-Type: text/plain');
        echo "AUTH_SUCCESS|";
?>
-- [[ ĐOẠN NÀY LÀ RUỘT SCRIPT CỦA MÀY ]] --
local imgui = require("mimgui")
local json = require("dkjson")
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
    imgui.Begin("AutoWalk VIP (Server Loaded)", show)
    if imgui.Button("START", imgui.ImVec2(100, 35)) then running = true idx = 1 end
    imgui.SameLine()
    if imgui.Button("STOP", imgui.ImVec2(100, 35)) then running = false setGameKeyState(1,0) end
    if imgui.Button("ADD POINT", imgui.ImVec2(-1, 35)) then
        local x,y,z = getCharCoordinates(PLAYER_PED)
        table.insert(points, {x=x, y=y, z=z})
    end
    imgui.Checkbox("Enable Auto Y", autoY)
    imgui.SliderInt("Y Delay", yDelay, 100, 5000)
    imgui.End()
end)
sampAddChatMessage("{00FFD5}[Server]: {FFFFFF}AutoWalk Script Loaded!", -1)
<?php
        exit;
    } else {
        die("FAIL");
    }
}

// --- PHẦN 2: LOGIC QUẢN LÝ (GIỮ NGUYÊN) ---
if (isset($_POST['login'])) { if ($_POST['pw'] == $admin_pass) $_SESSION['admin'] = true; }
if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); }
if (isset($_POST['add_key']) && isset($_SESSION['admin'])) {
    $new = trim($_POST['k']) . "|" . $_POST['d'] . "|\n";
    @file_put_contents($db_file, $new, FILE_APPEND);
}
if (isset($_GET['del']) && isset($_SESSION['admin'])) {
    $data = file($db_file, FILE_IGNORE_NEW_LINES);
    unset($data[$_GET['del']]);
    file_put_contents($db_file, (count($data) > 0 ? implode("\n", $data) . "\n" : ""));
    header("Location: ?");
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
        body { margin: 0; font-family: sans-serif; background: #0b0b0b; display: flex; justify-content: center; align-items: center; min-height: 100vh; color: white; }
        .card { background: #161616; padding: 25px; border-radius: 15px; border: 1px solid var(--p); text-align: center; width: 320px; box-shadow: 0 0 20px rgba(0,255,213,0.2); }
        .avatar { width: 80px; height: 80px; border-radius: 50%; border: 2px solid var(--p); margin-bottom: 10px; }
        input { width: 100%; padding: 10px; margin: 5px 0; border-radius: 8px; border: 1px solid #333; background: #222; color: #fff; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: var(--p); color: #000; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .key-list { margin-top: 15px; text-align: left; font-size: 13px; max-height: 150px; overflow-y: auto; }
        .item { padding: 8px; border-bottom: 1px solid #222; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
<div class="card">
    <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
    <h3>ADMIN PANEL</h3>
    <?php if (!isset($_SESSION['admin'])): ?>
        <form method="POST"><input type="password" name="pw" placeholder="Mật khẩu..."><button type="submit" name="login">ĐĂNG NHẬP</button></form>
    <?php else: ?>
        <form method="POST"><input type="text" name="k" placeholder="Nhập Key..." required><input type="date" name="d" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"><button type="submit" name="add_key">TẠO KEY</button></form>
        <div class="key-list">
            <?php if (file_exists($db_file)) {
                $data = file($db_file, FILE_IGNORE_NEW_LINES);
                foreach ($data as $idx => $line) {
                    $p = explode("|", $line); if(empty($p[0])) continue;
                    echo "<div class='item'><span>$p[0] ($p[1])</span><a href='?del=$idx' style='color:red;text-decoration:none'>XÓA</a></div>";
                }
            } ?>
        </div>
        <a href="?logout" style="color:#555; font-size: 11px; text-decoration:none; margin-top:10px; display:block;">ĐĂNG XUẤT</a>
    <?php endif; ?>
</div>
</body>
</html>

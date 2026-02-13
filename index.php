<?php
/**
 * 👑 PROJECT: KN BALLAS ULTIMATE EMPIRE
 * 👤 BOSS: KN (ANH NGUYEN)
 * 🛰️ MODULE: API SERVER + FULL LUA AUTOWALK + KEY MANAGER (IP LOCK)
 */

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');
$DB = "kn_database.txt";
$ADMIN_PASS = "Anhnguyendz_99";
if (!file_exists($DB)) touch($DB);

// =========================================================
// 🛰️ [1] API - TRẢ VỀ CODE LUA AUTOWALK KHI ĐÚNG KEY
// =========================================================
if (isset($_GET['check_key'])) {
    $k = trim($_GET['check_key']);
    $ip = $_SERVER['REMOTE_ADDR']; // Lấy IP người dùng
    $auth = "NOT_FOUND";
    
    $rows = file($DB, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated = [];
    foreach ($rows as $r) {
        $d = explode("|", $r); // 0:Key | 1:Expiry | 2:IP
        if ($d[0] === $k) {
            // Check thời hạn
            if (date("Y-m-d") > $d[1]) { 
                $auth = "EXPIRED"; 
            } 
            // Check IP (Anti-Share) - Nếu đã có IP mà không trùng với IP hiện tại
            elseif ($d[2] !== "NONE" && $d[2] !== $ip) { 
                $auth = "IP_BLOCKED (ANTI-SHARE)"; 
            } 
            else {
                if ($d[2] === "NONE") $d[2] = $ip; // Khóa IP lần đầu sử dụng
                $auth = "AUTH_SUCCESS";
            }
        }
        $updated[] = implode("|", $d);
    }
    file_put_contents($DB, implode("\n", $updated));

    header('Content-Type: text/plain');
    if ($auth === "AUTH_SUCCESS") {
        echo "AUTH_SUCCESS|"; 
        // --- ĐÂY LÀ CODE AUTOWALK CỦA MÀY ---
?>
script_name("AutoWalk AutoY")
script_author("KN_BOSS")
require "lib.moonloader"
local imgui = require "mimgui"
local spamTime = 1500 
local show = imgui.new.bool(true)
local running = false
local points = {}
local idx = 1

function sendY()
    local playerId = select(2, sampGetPlayerIdByCharHandle(PLAYER_PED))
    local memPtr = allocateMemory(68)
    sampStorePlayerOnfootData(playerId, memPtr)
    setStructElement(memPtr, 36, 1, 64, false)
    sampSendOnfootData(memPtr)
    freeMemory(memPtr)
end

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

imgui.OnFrame(function() return show[0] end,
function()
    imgui.Begin("AutoWalk AutoY", show)
    imgui.Text("Points: "..#points)
    if imgui.Button("Add Point") then
        local x,y,z=getCharCoordinates(PLAYER_PED)
        table.insert(points,{x,y,z})
    end
    if imgui.Button("START") then if #points>0 then running=true; idx=1 end end
    if imgui.Button("STOP") then running=false; setGameKeyState(1,0) end
    if imgui.Button("CLEAR") then points={} end
    imgui.End()
end)

function main()
    repeat wait(0) until isSampAvailable()
    while true do
        wait(0)
        if running and #points>0 then
            local p=points[idx]
            if walk(p) then
                local t=os.clock()
                while os.clock()-t < spamTime/1000 do sendY(); wait(120) end
                idx = idx + 1
                if idx > #points then idx = 1 end
            end
        end
    end
end
<?php
    } else {
        echo $auth;
    }
    exit;
}

// =========================================================
// 🎨 [2] GIAO DIỆN WEB & QUẢN LÝ (CHO BOSS KN)
// =========================================================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>KN BOSS | SYSTEM V2500</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --p: #00ffd5; --s: #ff00c1; }
        * { margin:0; padding:0; box-sizing:border-box; font-family: sans-serif; cursor: none; }
        body { background: #000; height: 100vh; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        #bg-v { position: fixed; inset: 0; min-width: 100%; min-height: 100%; z-index: -2; object-fit: cover; filter: brightness(0.2); }
        .glass { width: 450px; padding: 40px; border-radius: 40px; background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(30px); border: 1px solid var(--p); text-align: center; box-shadow: 0 0 30px rgba(0,255,213,0.2); position: relative; }
        input { width: 100%; padding: 15px; background: rgba(0,0,0,0.8); border: 1px solid #333; border-radius: 12px; color: var(--p); text-align: center; margin: 10px 0; outline: none; }
        .btn { width: 100%; padding: 16px; border-radius: 12px; border: none; background: linear-gradient(45deg, var(--p), var(--s)); color: #000; font-weight: 900; cursor: pointer; text-transform: uppercase; margin-top: 10px; }
        .btn-admin { position: absolute; bottom: 15px; right: 15px; color: #111; cursor: pointer; }
        table { width: 100%; font-size: 11px; margin-top: 20px; border-collapse: collapse; color: #fff; }
        th, td { border: 1px solid #222; padding: 8px; text-align: center; }
        #cur { width: 10px; height: 10px; background: var(--p); border-radius: 50%; position: fixed; pointer-events: none; z-index: 10000; box-shadow: 0 0 10px var(--p); }
    </style>
</head>
<body>
    <div id="cur"></div>
    <video id="bg-v" autoplay loop muted playsinline><source src="bg.mp4" type="video/mp4"></video>

    <div class="glass">
        <div id="login-ui">
            <h1 style="letter-spacing: 5px; color: #fff;">KN BALLAS</h1>
            <p style="font-size: 10px; color: var(--p); margin-bottom: 20px;">KEY AUTHENTICATION SYSTEM</p>
            <input type="text" id="k" placeholder="VUI LÒNG NHẬP KEY...">
            <button class="btn" onclick="verify()">TRUY CẬP</button>
            <p id="stt" style="color:var(--s); font-size: 12px; margin-top: 10px;"></p>
        </div>

        <div id="admin-ui" style="display:none;">
            <h2 style="color:var(--p)">QUẢN LÝ KEY</h2>
            <form method="POST">
                <input type="text" name="kn" placeholder="Tên Key" required>
                <input type="number" name="kd" placeholder="Số ngày (Cộng '+' / Trừ '-')" required>
                <button class="btn" name="set">CẬP NHẬT / TẠO KEY</button>
                <button class="btn" name="reset_ip" style="background: #333; color: #fff;">RESET IP TOÀN BỘ KEY</button>
            </form>
            <div style="max-height: 200px; overflow-y: auto; margin-top: 20px; background: #000; border: 1px solid #222;">
                <table>
                    <thead><tr><th>KEY</th><th>HẠN</th><th>IP GỐC</th></tr></thead>
                    <tbody>
                        <?php
                        $rows = file($DB, FILE_IGNORE_NEW_LINES);
                        foreach($rows as $r) {
                            $d = explode("|", $r);
                            $color = (date("Y-m-d") > $d[1]) ? "red" : "#00ffd5";
                            echo "<tr><td>$d[0]</td><td style='color:$color'>$d[1]</td><td>$d[2]</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <button class="btn" onclick="location.reload()" style="background:none; border: 1px solid #333; color:#555; margin-top:10px;">THOÁT ADMIN</button>
        </div>

        <div class="btn-admin" onclick="openAdmin()"><i class="fas fa-key"></i></div>
    </div>

    <script>
        document.onmousemove = (e) => { const c=document.getElementById('cur'); c.style.left=e.clientX+'px'; c.style.top=e.clientY+'px'; }
        
        function openAdmin() {
            let p = prompt("PASSWORD CHỦ (BOSS):");
            if(p === "<?php echo $ADMIN_PASS; ?>") {
                document.getElementById('login-ui').style.display='none';
                document.getElementById('admin-ui').style.display='block';
            }
        }

        async function verify() {
            const key = document.getElementById('k').value;
            const r = await fetch(`index.php?check_key=${key}`);
            const t = await r.text();
            if(t.includes("AUTH_SUCCESS")) alert("CHÚC MỪNG! KEY CHUẨN.");
            else document.getElementById('stt').innerText = "LỖI: " + t;
        }
    </script>

    <?php
    // LOGIC CỘNG/TRỪ HẠN & TẠO KEY
    if(isset($_POST['set'])) {
        $n = trim($_POST['kn']); $d = (int)$_POST['kd'];
        $rows = file($DB, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $up = []; $f = false;
        foreach($rows as $r) {
            $x = explode("|", $r);
            if($x[0] === $n) { 
                $f = true; 
                $base_date = (date("Y-m-d") > $x[1]) ? date("Y-m-d") : $x[1];
                $x[1] = date('Y-m-d', strtotime($base_date . " $d days")); 
            }
            $up[] = implode("|", $x);
        }
        if(!$f) $up[] = "$n|".date('Y-m-d', strtotime("+$d days"))."|NONE";
        file_put_contents($DB, implode("\n", $up));
        header("Location: index.php");
    }

    // LOGIC RESET IP (CHO PHÉP KHÁCH DÙNG IP MỚI)
    if(isset($_POST['reset_ip'])) {
        $rows = file($DB, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $up = array_map(function($r){
            $x = explode("|", $r); $x[2] = "NONE"; return implode("|", $x);
        }, $rows);
        file_put_contents($DB, implode("\n", $up));
        header("Location: index.php");
    }
    ?>
</body>
</html>

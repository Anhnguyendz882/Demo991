<?php
session_start();
date_default_timezone_set("Asia/Ho_Chi_Minh");

$ADMIN_PASS = "123456";
$DB_FILE = "database.txt";

if(!file_exists($DB_FILE)) file_put_contents($DB_FILE,"");



/* =========================
   API CHECK KEY + TRẢ SCRIPT
========================= */
if(isset($_GET['check_key'])){
    $key = trim($_GET['check_key']);
    $ip  = $_SERVER['REMOTE_ADDR'];

    $lines = file($DB_FILE, FILE_IGNORE_NEW_LINES);

    foreach($lines as $i=>$line){
        list($k,$created,$saved_ip) = array_pad(explode("|",$line),3,'');

        if($k === $key){

            if($saved_ip == '-'){
                $lines[$i] = "$k|$created|$ip";
                file_put_contents($DB_FILE, implode("\n",$lines));
            }

            header("Content-Type:text/plain");

            echo "AUTH_SUCCESS|\n";

?>
script_name("AutoWalk AutoY")
script_author("ChatGPT")

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
    imgui.Text("Current: "..idx)
    imgui.Text(running and "RUNNING" or "STOPPED")

    if imgui.Button("Add Point") then
        local x,y,z=getCharCoordinates(PLAYER_PED)
        table.insert(points,{x,y,z})
    end

    if imgui.Button("START") then
        if #points>0 then
            running=true
            idx=1
        end
    end

    if imgui.Button("STOP") then
        running=false
        setGameKeyState(1,0)
    end

    if imgui.Button("CLEAR") then
        points={}
    end

    imgui.End()
end)

function main()
    repeat wait(0) until isSampAvailable()

    sampRegisterChatCommand("awui", function()
        show[0]=not show[0]
    end)

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
<?php
            exit;
        }
    }

    echo "INVALID";
    exit;
}



/* =========================
   LOGIN ADMIN
========================= */
if(isset($_POST['login'])){
    if($_POST['pass'] === $ADMIN_PASS){
        $_SESSION['admin'] = true;
    }
}

if(isset($_GET['logout'])){
    session_destroy();
    header("Location:index.php");
    exit;
}



/* =========================
   TẠO KEY
========================= */
if(isset($_POST['create'])){
    $key = strtoupper(substr(md5(uniqid()),0,12));
    $created = date("d/m/Y H:i");

    file_put_contents($DB_FILE,"$key|$created|-\n", FILE_APPEND);
}



/* =========================
   XOÁ KEY
========================= */
if(isset($_GET['delete'])){
    $delete = $_GET['delete'];
    $lines = file($DB_FILE, FILE_IGNORE_NEW_LINES);
    $new = [];

    foreach($lines as $l){
        if(strpos($l,$delete) === false){
            $new[] = $l;
        }
    }

    file_put_contents($DB_FILE, implode("\n",$new));
    header("Location:index.php");
    exit;
}

$rows = file($DB_FILE, FILE_IGNORE_NEW_LINES);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>BLACK CAT PANEL</title>
<style>
body{margin:0;font-family:Segoe UI;background:#020617;color:#fff}
.card{width:380px;max-width:95%;margin:40px auto;padding:25px;border-radius:20px;background:#0f172a;text-align:center;box-shadow:0 0 40px rgba(0,255,255,.15)}
input,button{width:100%;padding:12px;border-radius:12px;border:none;margin-top:10px}
button{background:#06b6d4;color:#000;font-weight:bold;cursor:pointer}
table{width:100%;margin-top:15px;border-collapse:collapse}
td,th{padding:6px;border-bottom:1px solid #222;font-size:12px}
.key{color:#22d3ee;font-weight:bold}
.del{color:red;text-decoration:none}
</style>
</head>
<body>

<?php if(!isset($_SESSION['admin'])): ?>

<div class="card">
<h2>ADMIN LOGIN</h2>
<form method="post">
<input type="password" name="pass" placeholder="Password">
<button name="login">LOGIN</button>
</form>
</div>

<?php else: ?>

<div class="card">
<h2>KEY PANEL</h2>

<form method="post">
<button name="create">TẠO KEY</button>
</form>

<table>
<tr><th>KEY</th><th>TẠO LÚC</th><th>IP</th><th>XÓA</th></tr>

<?php foreach($rows as $r):
list($k,$c,$ip)=array_pad(explode("|",$r),3,'');
?>
<tr>
<td class="key"><?=$k?></td>
<td><?=$c?></td>
<td><?=$ip?></td>
<td><a class="del" href="?delete=<?=$k?>">X</a></td>
</tr>
<?php endforeach; ?>

</table>

<a href="?logout">LOGOUT</a>
</div>

<?php endif; ?>

</body>
</html>

<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$ADMIN_KEY = "admin123"; // key đăng nhập admin
$DB_FILE = "database.txt";

if(!file_exists($DB_FILE)) file_put_contents($DB_FILE,"");

function loadKeys(){
    global $DB_FILE;
    return file($DB_FILE, FILE_IGNORE_NEW_LINES);
}

function saveKeys($lines){
    global $DB_FILE;
    file_put_contents($DB_FILE, implode("\n",$lines)."\n");
}

function findKey($key){
    foreach(loadKeys() as $line){
        list($k,$exp,$ip) = explode("|",$line);
        if($k == $key) return [$k,$exp,$ip];
    }
    return false;
}

function createKey($days){
    $key = strtoupper(substr(md5(time().rand()),0,12));
    $expire = time()+($days*86400);
    file_put_contents("database.txt","$key|$expire|\n",FILE_APPEND);
}

function deleteKey($key){
    $new=[];
    foreach(loadKeys() as $line){
        if(strpos($line,$key)!==0) $new[]=$line;
    }
    saveKeys($new);
}

# ================= API CHECK =================
if(isset($_GET['check_key'])){
    $key = trim($_GET['check_key']);
    $ip = $_SERVER['REMOTE_ADDR'];
    $data = findKey($key);

    if(!$data) die("INVALID");

    list($k,$exp,$saved_ip) = $data;

    if(time() > $exp) die("EXPIRED");

    if($saved_ip=="" || $saved_ip==$ip){
        deleteKey($k);
        file_put_contents($DB_FILE,"$k|$exp|$ip\n",FILE_APPEND);
    } else die("IP_LOCKED");

    header("Content-Type:text/plain");

    echo "AUTH_SUCCESS|";
?>
script_name("AutoWalk AutoY")
script_author("KN")

require "lib.moonloader"
local imgui = require "mimgui"
local inicfg = require "inicfg"

local spamTime = 1500
local show = imgui.new.bool(true)
local running = false
local points = {}
local idx = 1
local cfgname = "autowalk_points"

local cfg = inicfg.load(nil, cfgname)

function savePoints()
    cfg={}
    cfg.points={}
    for i,p in ipairs(points) do
        cfg.points[i]={x=p[1],y=p[2],z=p[3]}
    end
    inicfg.save(cfg,cfgname)
    sampAddChatMessage("[AutoWalk] Saved!",-1)
end

function loadPoints()
    if cfg and cfg.points then
        for i,p in pairs(cfg.points) do
            table.insert(points,{p.x,p.y,p.z})
        end
    end
end

function sendY()
    local id=select(2,sampGetPlayerIdByCharHandle(PLAYER_PED))
    local mem=allocateMemory(68)
    sampStorePlayerOnfootData(id,mem)
    setStructElement(mem,36,1,64,false)
    sampSendOnfootData(mem)
    freeMemory(mem)
end

local function walk(p)
    local x,y,z=getCharCoordinates(PLAYER_PED)
    local dx=p[1]-x
    local dy=p[2]-y
    local dist=math.sqrt(dx*dx+dy*dy)
    if dist>1.2 then
        setCharHeading(PLAYER_PED,math.deg(math.atan2(-dx,dy)))
        setGameKeyState(1,255)
        return false
    else
        setGameKeyState(1,0)
        return true
    end
end

imgui.OnFrame(function() return show[0] end,function()
imgui.Begin("AutoWalk AutoY",show)
imgui.Text("Points: "..#points)
imgui.Text("Current: "..idx)
imgui.Text(running and "RUNNING" or "STOPPED")

if imgui.Button("Add Point") then
local x,y,z=getCharCoordinates(PLAYER_PED)
table.insert(points,{x,y,z})
end

if imgui.Button("SAVE CONFIG") then savePoints() end

if imgui.Button("START") then if #points>0 then running=true idx=1 end end
if imgui.Button("STOP") then running=false setGameKeyState(1,0) end
if imgui.Button("CLEAR") then points={} end

imgui.End()
end)

function main()
repeat wait(0) until isSampAvailable()
loadPoints()

sampRegisterChatCommand("awui",function() show[0]=not show[0] end)

while true do wait(0)
if running and #points>0 then
local p=points[idx]
if walk(p) then
local t=os.clock()
while os.clock()-t < spamTime/1000 do sendY() wait(120) end
idx = idx % #points + 1
end
end
end
end
<?php exit; }

# ================= ADMIN LOGIN =================
if(isset($_POST['admin_login'])){
    if($_POST['key']==$ADMIN_KEY){
        $_SESSION['admin']=true;
    }
}

if(isset($_GET['logout'])) unset($_SESSION['admin']);

if(isset($_POST['create']) && $_SESSION['admin']){
    createKey($_POST['days']);
}

if(isset($_GET['del']) && $_SESSION['admin']){
    deleteKey($_GET['del']);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>BLACK CAT SYSTEM</title>
<style>
body{margin:0;background:#000;color:#0ff;font-family:Arial;text-align:center}
.card{background:#111;padding:30px;border-radius:20px;box-shadow:0 0 30px #0ff;width:360px;margin:auto;margin-top:10vh}
input,button{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:none}
button{background:#0ff;font-weight:bold}
table{width:100%;margin-top:10px}
</style>
</head>
<body>

<div class="card">

<?php if(!isset($_SESSION['admin'])): ?>

<h2>ADMIN LOGIN</h2>
<form method="post">
<input name="key" placeholder="Admin key">
<button name="admin_login">LOGIN</button>
</form>

<?php else: ?>

<h2>BOSS KN</h2>
<p>Status: Online</p>

<form method="post">
<input type="number" name="days" placeholder="Days key">
<button name="create">CREATE KEY</button>
</form>

<table border="1">
<tr><th>KEY</th><th>Hạn</th><th>IP</th><th>Xoá</th></tr>
<?php foreach(loadKeys() as $l):
list($k,$e,$ip)=explode("|",$l); ?>
<tr>
<td><?=$k?></td>
<td><?=date("d/m/Y",$e)?></td>
<td><?=$ip?></td>
<td><a href="?del=<?=$k?>">X</a></td>
</tr>
<?php endforeach; ?>
</table>

<br><a href="?logout">LOGOUT</a>

<?php endif; ?>

</div>
</body>
</html>

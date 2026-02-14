<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$ADMIN_KEY = "admin123";   // key đăng nhập admin
$DB_FILE   = "database.txt";

if(!file_exists($DB_FILE)) file_put_contents($DB_FILE,"");

function loadKeys(){
    global $DB_FILE;
    return file($DB_FILE, FILE_IGNORE_NEW_LINES);
}

function saveKeys($arr){
    global $DB_FILE;
    file_put_contents($DB_FILE, implode("\n",$arr)."\n");
}

function createKey($days){
    global $DB_FILE;
    $key = strtoupper(substr(md5(time().rand()),0,12));
    $expire = time()+($days*86400);
    file_put_contents($DB_FILE,"$key|$expire|\n",FILE_APPEND);
}

function deleteKey($key){
    $new=[];
    foreach(loadKeys() as $line){
        if(strpos($line,$key)!==0) $new[]=$line;
    }
    saveKeys($new);
}

function findKey($key){
    foreach(loadKeys() as $line){
        list($k,$exp,$ip)=explode("|",$line);
        if($k==$key) return [$k,$exp,$ip];
    }
    return false;
}

# ================= API =================
if(isset($_GET['check_key'])){
    $key=trim($_GET['check_key']);
    $ip=$_SERVER['REMOTE_ADDR'];
    $data=findKey($key);

    if(!$data) die("INVALID");

    list($k,$exp,$saved_ip)=$data;

    if(time()>$exp) die("EXPIRED");

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

local spamTime=1500
local show=imgui.new.bool(true)
local running=false
local points={}
local idx=1
local cfgname="autowalk_points"

local lastX,lastY=0,0
local stuckTime=0

local cfg=inicfg.load(nil,cfgname)

function savePoints()
 cfg={} cfg.points={}
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
  local heading=math.deg(math.atan2(-dx,dy))
  setCharHeading(PLAYER_PED,heading)
  setGameKeyState(1,255)
  setGameKeyState(0,0)

  if math.abs(x-lastX)<0.02 and math.abs(y-lastY)<0.02 then
   stuckTime=stuckTime+1
  else
   stuckTime=0
  end

  if stuckTime>20 then
   setGameKeyState(16,255)
   wait(100)
   setGameKeyState(16,0)
   stuckTime=0
  end

  lastX,lastY=x,y
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

 while true do
  wait(10)
  if running and #points>0 then
   local p=points[idx]
   if walk(p) then
    local t=os.clock()
    while os.clock()-t < spamTime/1000 do
     sendY()
     wait(120)
    end
    idx = idx % #points + 1
   end
  end
 end
end
<?php exit; }

# ================= ADMIN =================
if(isset($_POST['login'])){
    if($_POST['key']==$ADMIN_KEY){
        $_SESSION['admin']=true;
    }
}

if(isset($_GET['logout'])) unset($_SESSION['admin']);

if(isset($_POST['create']) && isset($_SESSION['admin'])){
    createKey($_POST['days']);
}

if(isset($_GET['del']) && isset($_SESSION['admin'])){
    deleteKey($_GET['del']);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>BLACK CAT SYSTEM</title>
<style>
body{margin:0;height:100vh;background:#000;color:#fff;
display:flex;justify-content:center;align-items:center;font-family:Arial;overflow:hidden}
video{position:fixed;min-width:100%;min-height:100%;z-index:-2;object-fit:cover;filter:brightness(0.3)}
.glass{width:380px;padding:35px;border-radius:30px;
background:rgba(255,255,255,0.05);backdrop-filter:blur(20px);
border:1px solid rgba(0,255,255,0.3);
box-shadow:0 0 40px rgba(0,255,255,0.25);text-align:center}
.avatar{width:110px;border-radius:50%;border:3px solid cyan;box-shadow:0 0 25px cyan}
input,button{width:100%;padding:14px;margin-top:12px;border-radius:10px;border:none}
input{background:#000;color:#0ff;border-left:4px solid cyan}
button{background:linear-gradient(45deg,cyan,magenta);font-weight:bold;cursor:pointer}
table{width:100%;margin-top:15px;font-size:12px}
</style>
</head>
<body>

<video autoplay muted loop>
<source src="bg.mp4" type="video/mp4">
</video>

<div class="glass">

<?php if(!isset($_SESSION['admin'])): ?>

<img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
<h2>BLACK CAT SYSTEM</h2>
<p style="color:cyan">ADMIN LOGIN</p>

<form method="post">
<input name="key" placeholder="Master key">
<button name="login">LOGIN</button>
</form>

<?php else: ?>

<img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
<h2>BOSS KN</h2>
<p style="color:cyan">SYSTEM ONLINE</p>

<form method="post">
<input type="number" name="days" placeholder="Số ngày sử dụng">
<button name="create">TẠO KEY</button>
</form>

<table border="1">
<tr><th>KEY</th><th>HẾT HẠN</th><th>IP</th><th>XÓA</th></tr>
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

<br><a href="?logout" style="color:cyan">LOGOUT</a>

<?php endif; ?>

</div>
</body>
</html>

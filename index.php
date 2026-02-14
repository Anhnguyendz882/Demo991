<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$ADMIN_PASS="admin123";
define('DB','database.txt');

function loadKeys(){
 if(!file_exists(DB)) file_put_contents(DB,'');
 $lines=file(DB, FILE_IGNORE_NEW_LINES);
 $data=[];
 foreach($lines as $l){
  if(trim($l)=='') continue;
  list($k,$c,$e,$ip)=explode('|',$l);
  $data[$k]=['created'=>$c,'expire'=>$e,'ip'=>$ip];
 }
 return $data;
}

function saveKeys($data){
 $out='';
 foreach($data as $k=>$v){
  $out.="$k|{$v['created']}|{$v['expire']}|{$v['ip']}\n";
 }
 file_put_contents(DB,$out);
}

/* ========= API AUTH ========= */
if(isset($_GET['check_key'])){
 $key=strtoupper($_GET['check_key']);
 $ip=$_SERVER['REMOTE_ADDR'];
 $keys=loadKeys();

 if(!isset($keys[$key])) die("INVALID");
 if(time()>$keys[$key]['expire']) die("EXPIRED");

 if($keys[$key]['ip']==''){
  $keys[$key]['ip']=$ip;
  saveKeys($keys);
 }

 if($keys[$key]['ip']!==$ip) die("IP_LOCK");

 echo "AUTH_SUCCESS|";
 echo base64_encode(file_get_contents("AutoWalk.lua"));
 exit;
}

/* ========= LOGIN ========= */
if(isset($_POST['login'])){
 if($_POST['pass']==$ADMIN_PASS)
  $_SESSION['admin']=true;
}

if(isset($_GET['logout'])){
 session_destroy();
 header("Location: ?");
 exit;
}

/* ========= CREATE KEY ========= */
if(isset($_POST['create']) && isset($_SESSION['admin'])){
 $keys=loadKeys();
 $key=strtoupper(substr(md5(rand()),0,12));
 $days=intval($_POST['days']);
 $keys[$key]=[
  'created'=>time(),
  'expire'=>time()+($days*86400),
  'ip'=>''
 ];
 saveKeys($keys);
}

if(isset($_GET['del']) && isset($_SESSION['admin'])){
 $keys=loadKeys();
 unset($keys[$_GET['del']]);
 saveKeys($keys);
}

if(isset($_GET['resetip']) && isset($_SESSION['admin'])){
 $keys=loadKeys();
 $keys[$_GET['resetip']]['ip']='';
 saveKeys($keys);
}

$keys=loadKeys();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>BLACK CAT LOADER</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style>
body{background:#020617;color:white;font-family:Orbitron;text-align:center}
.box{background:#0f172a;padding:30px;border-radius:20px;width:420px;margin:auto;margin-top:60px;box-shadow:0 0 40px cyan}
button{padding:10px 18px;border:none;border-radius:8px;background:linear-gradient(45deg,#00ffff,#00ff9d);color:#000;font-weight:bold;cursor:pointer}
input{padding:10px;border-radius:8px;border:none;background:#020617;color:#0ff;text-align:center}
table{width:100%;margin-top:20px;font-size:13px}
td,th{padding:6px;border-bottom:1px solid #1e293b}
.avatar{width:90px;border-radius:50%;box-shadow:0 0 20px cyan}
</style>
</head>
<body>

<?php if(!isset($_SESSION['admin'])): ?>

<div class="box">
<h2>ADMIN LOGIN</h2>
<form method="post">
<input type="password" name="pass" placeholder="Master Key"><br><br>
<button name="login">ENTER</button>
</form>
</div>

<?php else: ?>

<div class="box">
<img src="https://i.imgur.com/3ZQ3Z9M.png" class="avatar">
<h2>BLACK CAT LOADER</h2>
<p>Premium SA-MP System</p>

<form method="post">
<br>Create Key (days):
<input type="number" name="days" value="30" style="width:70px">
<button name="create">CREATE</button>
</form>

<table>
<tr><th>KEY</th><th>EXPIRE</th><th>IP</th><th>ACTION</th></tr>
<?php foreach($keys as $k=>$v): ?>
<tr>
<td><?= $k ?></td>
<td><?= date("d/m/Y",$v['expire']) ?></td>
<td><?= $v['ip']?:'NEW' ?></td>
<td>
<a href="?resetip=<?= $k ?>">RESET</a> |
<a href="?del=<?= $k ?>" style="color:red">DELETE</a>
</td>
</tr>
<?php endforeach; ?>
</table>
<br>
<a href="?logout">LOGOUT</a>
</div>

<?php endif; ?>
</body>
</html>

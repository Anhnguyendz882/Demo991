<?php
session_start();

$ADMIN_PASS = "123456"; // mật khẩu admin
$DB = "database.txt";

if(!file_exists($DB)) file_put_contents($DB,"");

/* ================= API CHECK KEY ================= */
if(isset($_GET['check_key'])){
    $key = trim($_GET['check_key']);
    $ip = $_SERVER['REMOTE_ADDR'];
    $today = date("d/m/Y");

    $lines = file($DB, FILE_IGNORE_NEW_LINES);

    foreach($lines as $i=>$line){
        $p = explode("|",$line);
        $k = $p[0] ?? '';
        $exp = $p[1] ?? '';
        $saved_ip = $p[2] ?? '-';

        if($k === $key){

            if(strtotime(str_replace("/","-",$exp)) < strtotime($today)){
                echo "KEY_EXPIRED";
                exit;
            }

            if($saved_ip == '-' ){
                $lines[$i] = "$k|$exp|$ip";
                file_put_contents($DB, implode("\n",$lines));
            }

            echo "AUTH_SUCCESS";
            exit;
        }
    }

    echo "INVALID";
    exit;
}

/* ================= LOGIN ================= */
if(isset($_POST['login'])){
    if($_POST['pass'] === $ADMIN_PASS){
        $_SESSION['admin'] = true;
    }
}

if(isset($_GET['logout'])){
    session_destroy();
    header("Location: index.php");
    exit;
}

/* ================= CREATE KEY ================= */
if(isset($_POST['create'])){
    $days = intval($_POST['days']);
    if($days <= 0) $days = 30;

    $key = strtoupper(substr(md5(time().rand()),0,12));
    $expire = date("d/m/Y", strtotime("+$days days"));

    file_put_contents($DB,"$key|$expire|-\n", FILE_APPEND);
}

/* ================= DELETE ================= */
if(isset($_GET['del'])){
    $del = $_GET['del'];
    $lines = file($DB, FILE_IGNORE_NEW_LINES);
    $new=[];

    foreach($lines as $l){
        if(strpos($l,$del) === false) $new[]=$l;
    }

    file_put_contents($DB, implode("\n",$new));
    header("Location: index.php");
    exit;
}

$rows = file($DB, FILE_IGNORE_NEW_LINES);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>BLACK CAT KEY SYSTEM</title>

<style>
body{
margin:0;
font-family:Segoe UI;
background:radial-gradient(circle at top,#020617,#000);
color:white;
}

/* glass card */
.card{
width:380px;
max-width:95%;
margin:50px auto;
padding:28px;
border-radius:22px;
background:rgba(15,23,42,.75);
backdrop-filter: blur(14px);
box-shadow:0 0 50px rgba(0,255,255,.15);
border:1px solid rgba(255,255,255,.05);
text-align:center;
}

/* avatar */
.avatar{
width:90px;
height:90px;
border-radius:50%;
margin:auto;
background:url('https://i.imgur.com/8Km9tLL.png') center/cover;
box-shadow:0 0 25px #22d3ee;
}

h1{
margin:12px 0 0;
font-size:22px;
letter-spacing:2px;
}

.status{
color:#22d3ee;
font-size:13px;
margin-bottom:20px;
}

/* inputs */
input{
width:100%;
padding:13px;
border-radius:12px;
border:1px solid rgba(34,211,238,.25);
background:#020617;
color:white;
margin-bottom:14px;
font-size:15px;
outline:none;
}

/* button */
button{
width:100%;
padding:13px;
border-radius:14px;
border:none;
font-weight:bold;
font-size:15px;
background:linear-gradient(90deg,#06b6d4,#9333ea);
color:white;
cursor:pointer;
transition:.2s;
}

button:hover{
transform:scale(1.03);
box-shadow:0 0 18px rgba(147,51,234,.6);
}

/* table */
table{
width:100%;
border-collapse:collapse;
margin-top:18px;
}

th{
color:#22d3ee;
font-size:12px;
padding:8px;
border-bottom:1px solid rgba(34,211,238,.3);
}

td{
padding:8px;
font-size:12px;
border-bottom:1px solid rgba(255,255,255,.06);
}

.key{
color:#67e8f9;
font-weight:bold;
letter-spacing:1px;
}

.del{
color:#ef4444;
text-decoration:none;
font-weight:bold;
}

.logout{
display:inline-block;
margin-top:16px;
color:#22d3ee;
text-decoration:none;
font-size:13px;
}

.copy{
cursor:pointer;
}
</style>
</head>
<body>

<?php if(!isset($_SESSION['admin'])): ?>

<div class="card">
<div class="avatar"></div>
<h1>ADMIN PANEL</h1>
<div class="status">SECURE LOGIN</div>

<form method="post">
<input type="password" name="pass" placeholder="Password">
<button name="login">LOGIN</button>
</form>
</div>

<?php else: ?>

<div class="card">

<div class="avatar"></div>
<h1>BOSS KN</h1>
<div class="status">SYSTEM ONLINE</div>

<form method="post">
<input name="days" placeholder="Số ngày sử dụng">
<button name="create">TẠO KEY</button>
</form>

<table>
<tr>
<th>KEY</th>
<th>HẾT HẠN</th>
<th>IP</th>
<th>XÓA</th>
</tr>

<?php foreach($rows as $r):
$p=explode("|",$r);
$key=$p[0] ?? '';
$exp=$p[1] ?? '';
$ip=$p[2] ?? '-';
?>

<tr>
<td class="key copy" onclick="copyKey('<?=$key?>')"><?=$key?></td>
<td><?=$exp?></td>
<td><?=$ip?></td>
<td><a class="del" href="?del=<?=$key?>">✖</a></td>
</tr>

<?php endforeach; ?>
</table>

<a class="logout" href="?logout">LOGOUT</a>

</div>

<script>
function copyKey(k){
navigator.clipboard.writeText(k);
alert("Đã copy key!");
}
</script>

<?php endif; ?>

</body>
</html>

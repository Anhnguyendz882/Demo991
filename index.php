<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

/* ========= CONFIG ========= */

$MASTER_HASH = '$2y$10$R0knvZ8sE9KxC1p6Rz7lV.pW0vX8Y3v5QxV3FQJk2ZcZCzS3E6N3K'; 
$DB_FILE = "keys.json";

if(!file_exists($DB_FILE)){
 file_put_contents($DB_FILE,json_encode([],JSON_PRETTY_PRINT));
}

function loadKeys(){
 global $DB_FILE;
 return json_decode(file_get_contents($DB_FILE),true);
}

function saveKeys($k){
 global $DB_FILE;
 file_put_contents($DB_FILE,json_encode($k,JSON_PRETTY_PRINT));
}

/* ========= API CHECK ========= */

if(isset($_GET['api']) && $_GET['api']=="check"){
 $key=strtoupper(trim($_GET['key']??''));
 $ip=$_SERVER['REMOTE_ADDR'];
 $keys=loadKeys();

 if(!isset($keys[$key])) die("INVALID");

 if(time()>$keys[$key]['expire']) die("EXPIRED");

 if($keys[$key]['ip']==""){
  $keys[$key]['ip']=$ip;
  saveKeys($keys);
 }

 if($keys[$key]['ip']!==$ip) die("IP_LOCK");

 $left=$keys[$key]['expire']-time();
 die("VALID|$left");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>KN LOADER</title>

<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">

<style>
body{
 margin:0;
 background:#000;
 color:#00ffd5;
 font-family:Orbitron;
 overflow:hidden;
}

/* particles canvas */
canvas{
 position:fixed;
 inset:0;
 z-index:-1;
}

/* login */
#loginBox{
 position:absolute;
 top:50%;
 left:50%;
 transform:translate(-50%,-50%);
 text-align:center;
}

input{
 padding:14px;
 border-radius:8px;
 border:none;
 width:260px;
 text-align:center;
 background:#111;
 color:#00ffd5;
}

button{
 padding:12px 30px;
 border:none;
 border-radius:30px;
 background:#00ffd5;
 color:#000;
 font-weight:bold;
 cursor:pointer;
 margin-top:15px;
}

/* loader bio */

#bioLoader{
 display:none;
 text-align:center;
 animation:fade .6s ease;
}

@keyframes fade{from{opacity:0}to{opacity:1}}

.avatar{
 width:130px;
 height:130px;
 border-radius:50%;
 border:3px solid #00ffd5;
 box-shadow:0 0 35px #00ffd5;
 margin-top:80px;
}

.glitch{
 font-size:32px;
 margin-top:15px;
 position:relative;
}
.glitch:after{
 content:attr(data-text);
 position:absolute;
 left:2px;
 top:2px;
 color:#ff00c8;
 z-index:-1;
}

.btnBio{
 margin:8px;
 padding:12px 25px;
 border-radius:30px;
 border:1px solid #00ffd5;
 background:transparent;
 color:#00ffd5;
 cursor:pointer;
 transition:.2s;
}
.btnBio:hover{
 background:#00ffd5;
 color:#000;
 box-shadow:0 0 15px #00ffd5;
}

#timeLeft{
 margin-top:10px;
 opacity:.7;
}
</style>
</head>
<body>

<canvas id="particles"></canvas>

<div id="loginBox">
 <h2>ENTER ACCESS KEY</h2>
 <input id="keyInput" placeholder="ENTER KEY">
 <br>
 <button onclick="loginKey()">UNLOCK</button>
</div>

<div id="bioLoader">
 <img src="https://i.imgur.com/2yaf2wb.png" class="avatar">
 <div class="glitch" data-text="KN BOSS">KN BOSS</div>
 <p>Premium Loader Access</p>
 <div id="timeLeft"></div>

 <button class="btnBio" onclick="copyKey()">COPY KEY</button>
 <button class="btnBio" onclick="window.open('https://discord.com')">DISCORD</button>
 <button class="btnBio" onclick="window.open('https://youtube.com')">YOUTUBE</button>
</div>

<script>

/* LOGIN */

async function loginKey(){
 const key=document.getElementById("keyInput").value;

 const res=await fetch("?api=check&key="+key);
 const txt=await res.text();

 if(txt.startsWith("VALID")){
  document.getElementById("loginBox").style.display="none";
  document.getElementById("bioLoader").style.display="block";

  const sec=txt.split("|")[1];
  startTimer(sec);

  window.savedKey=key;
 }
 else if(txt==="IP_LOCK"){alert("Key locked IP");}
 else if(txt==="EXPIRED"){alert("Key expired");}
 else{alert("Invalid key");}
}

function copyKey(){
 navigator.clipboard.writeText(window.savedKey);
 alert("Copied!");
}

/* TIMER */

function startTimer(sec){
 setInterval(()=>{
  sec--;
  if(sec<=0) location.reload();
  let h=Math.floor(sec/3600);
  let m=Math.floor((sec%3600)/60);
  let s=sec%60;
  document.getElementById("timeLeft").innerText=
   "Expire in: "+h+"h "+m+"m "+s+"s";
 },1000);
}

/* PARTICLES BACKGROUND */

const canvas=document.getElementById("particles");
const ctx=canvas.getContext("2d");
canvas.width=innerWidth;
canvas.height=innerHeight;

let parts=[];
for(let i=0;i<80;i++){
 parts.push({
  x:Math.random()*canvas.width,
  y:Math.random()*canvas.height,
  r:Math.random()*2+1,
  d:Math.random()*1
 });
}

function draw(){
 ctx.clearRect(0,0,canvas.width,canvas.height);
 ctx.fillStyle="rgba(0,255,213,0.3)";
 ctx.beginPath();
 for(let p of parts){
  ctx.moveTo(p.x,p.y);
  ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
  p.y+=p.d;
  if(p.y>canvas.height)p.y=0;
 }
 ctx.fill();
 requestAnimationFrame(draw);
}
draw();

</script>
</body>
</html>

<?php
session_start();
$admin_pass = "123456"; 
$db_file = "database.txt";

if (!file_exists($db_file)) { @file_put_contents($db_file, ""); }
@chmod($db_file, 0777);

// --- PHẦN 1: API CHECK KEY ---
if (isset($_GET['check_key'])) {
    $key_input = trim($_GET['check_key']);
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $new_data = [];
    $found = false;
    $response = "NOT_FOUND";

    foreach ($data as $line) {
        $p = explode("|", $line);
        if (count($p) < 2) continue;
        $s_key = $p[0]; $expiry = $p[1];
        $l_ip = isset($p[2]) ? trim($p[2]) : "";

        if ($s_key === $key_input) {
            $found = true;
            if (date("Y-m-d") > $expiry) { $response = "EXPIRED"; } 
            else {
                if ($l_ip === "") { $l_ip = $user_ip; $response = "AUTH_SUCCESS"; } 
                elseif ($l_ip === $user_ip) { $response = "AUTH_SUCCESS"; } 
                else { $response = "WRONG_IP"; }
            }
        }
        $new_data[] = "$s_key|$expiry|$l_ip";
    }
    @file_put_contents($db_file, implode("\n", $new_data) . "\n");

    if ($response === "AUTH_SUCCESS") {
        header('Content-Type: text/plain');
        echo "AUTH_SUCCESS|";
?>
-- [[ CODE LUA CỦA MÀY ]] --
local imgui = require("mimgui")
local show = imgui.new.bool(true)
imgui.OnFrame(function() return show[0] end, function()
    imgui.Begin("Black Cat VIP", show)
    imgui.Text("Key Hop Le! Welcome Admin.")
    imgui.End()
end)
<?php
        exit;
    } else { die($response); }
}

// --- PHẦN 2: ADMIN PANEL ---
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
    <title>Black Cat Admin - Water Wave</title>
    <style>
        :root { --p: #00ffd5; }
        body { 
            margin: 0; padding: 0; background: #000 url('bg.gif') no-repeat center center fixed; 
            background-size: cover; height: 100vh; width: 100vw;
            display: flex; justify-content: center; align-items: center; 
            color: white; overflow: hidden; font-family: 'Segoe UI', sans-serif;
        }
        #water-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1; }
        .card { 
            background: rgba(10, 10, 10, 0.8); padding: 30px; border-radius: 20px; 
            border: 1px solid var(--p); text-align: center; width: 340px; 
            box-shadow: 0 0 30px rgba(0, 255, 213, 0.4); backdrop-filter: blur(10px);
            z-index: 10; position: relative;
        }
        .avatar { width: 90px; height: 90px; border-radius: 50%; border: 3px solid var(--p); margin-bottom: 15px; box-shadow: 0 0 15px var(--p); }
        input, button { width: 100%; padding: 12px; margin: 8px 0; border-radius: 12px; border: 1px solid #333; background: rgba(0,0,0,0.6); color: #fff; outline: none; box-sizing: border-box; }
        button { background: var(--p); color: #000; font-weight: bold; cursor: pointer; border: none; transition: 0.3s; }
        button:hover { background: #fff; transform: scale(1.02); }
        .list { margin-top: 15px; text-align: left; max-height: 200px; overflow-y: auto; }
        .item { padding: 10px; border-bottom: 1px solid #222; display: flex; justify-content: space-between; font-size: 13px; }
        .del { color: #ff4d4d; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <canvas id="water-canvas"></canvas>

    <div class="card">
        <img src="https://i.ibb.co/ynM5RCLc/avatar.jpg" class="avatar">
        <h2 style="color:var(--p); margin:0; letter-spacing: 2px;">BLACK CAT PANEL</h2>
        <?php if (!isset($_SESSION['admin'])): ?>
            <form method="POST"><input type="password" name="pw" placeholder="Mật khẩu Admin..."><button>ĐĂNG NHẬP</button></form>
        <?php else: ?>
            <form method="POST">
                <input type="text" name="k" placeholder="Tên Key..." required>
                <input type="date" name="d" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                <button name="add_key">TẠO KEY MỚI</button>
            </form>
            <div class="list">
                <?php 
                $data = file_exists($db_file) ? file($db_file, FILE_IGNORE_NEW_LINES) : [];
                foreach ($data as $idx => $line) {
                    $p = explode("|", $line); if(empty($p[0])) continue;
                    $status = (empty($p[2])) ? "Trống" : "Locked";
                    echo "<div class='item'><span><b>$p[0]</b> ($status)<br><small>$p[1]</small></span><a href='?del=$idx' class='del'>XÓA</a></div>";
                }
                ?>
            </div>
            <a href="?logout" style="color:#444; text-decoration:none; font-size:11px; display:block; margin-top:10px;">LOGOUT</a>
        <?php endif; ?>
    </div>

    <script>
        const canvas = document.getElementById('water-canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let ripples = [];

        class Ripple {
            constructor(x, y) {
                this.x = x; this.y = y;
                this.radius = 0;
                this.maxRadius = 100 + Math.random() * 50;
                this.opacity = 1;
                this.speed = 2 + Math.random() * 2;
            }
            update() {
                this.radius += this.speed;
                this.opacity -= 0.02;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.strokeStyle = `rgba(0, 255, 213, ${this.opacity})`;
                ctx.lineWidth = 2;
                ctx.stroke();
                ctx.closePath();
            }
        }

        // Hiệu ứng khi lướt chuột (Sóng biển liên tục)
        window.addEventListener('mousemove', (e) => {
            if (ripples.length < 20) { // Giới hạn để không lag
                ripples.push(new Ripple(e.clientX, e.clientY));
            }
        });

        // Hiệu ứng khi bấm chuột (Sóng mạnh hơn)
        window.addEventListener('mousedown', (e) => {
            for(let i=0; i<3; i++) {
                let r = new Ripple(e.clientX, e.clientY);
                r.speed = 5; // Bấm thì sóng loang nhanh hơn
                ripples.push(r);
            }
        });

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < ripples.length; i++) {
                ripples[i].update();
                ripples[i].draw();
                if (ripples[i].opacity <= 0) {
                    ripples.splice(i, 1);
                    i--;
                }
            }
            requestAnimationFrame(animate);
        }
        animate();

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    </script>
</body>
</html>

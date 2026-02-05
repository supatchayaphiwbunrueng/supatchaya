<?php 
// เช็คสถานะ Session ก่อนเริ่ม เพื่อป้องกัน Error "session already active"
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Winter Cool Shirt</title>

<style>
/* ===== RESET ===== */
*{
    box-sizing:border-box;
}

/* ===== BODY ===== */
body{
    font-family: "Segoe UI", Arial, sans-serif;
    background:#eef7ff;
    margin:0;
}

/* ===== HEADER ===== */
header{
    background:linear-gradient(90deg,#7cc8ff,#4aa3df);
    padding:15px 0;
    color:white;
    box-shadow:0 3px 8px rgba(0,0,0,0.1);
}

/* ===== HEADER INNER ===== */
.header-inner{
    width:90%;
    margin:auto;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

/* ===== LOGO AREA ===== */
.logo-area{
    display:flex;
    align-items:center;
}

.logo-area img{
    height:90px;
    margin-right:50px; 
}

.logo-text{
    font-size:24px;
    font-weight:bold;
    letter-spacing:1px;
}

/* ===== NAV ===== */
nav a{
    color:white;
    text-decoration:none;
    margin-left:33px;
    font-weight:600;
    padding:6px 12px;
    border-radius:6px;
    transition:0.3s;
}

nav a:hover{
    background:rgba(255,255,255,0.25);
}

/* ===== CONTAINER ===== */
.container{
    width:90%;
    margin:auto;
    background:white;
    padding:25px;
    margin-top:25px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    text-align: center;
}

/* ส่วนที่เหลือของ CSS คงเดิมตามที่คุณให้มา */
.card{ border:1px solid #cce5ff; padding:15px; border-radius:12px; margin:10px; display:inline-block; width:220px; vertical-align:top; text-align:center; transition:0.3s; }
.card:hover{ transform:translateY(-5px); box-shadow:0 6px 14px rgba(0,0,0,0.12); }
.card img{ max-width:100%; border-radius:10px; }
input, textarea, select{ width:100%; padding:10px; margin:8px 0; border:1px solid #bcdfff; border-radius:8px; font-size:14px; }
button{ background:#4aa3df; color:white; border:none; padding:10px 18px; border-radius:8px; cursor:pointer; font-size:14px; transition:0.3s; }
button:hover{ background:#2e86c1; }
footer{ text-align:center; padding:12px; margin-top:40px; background:#dbefff; color:#333; font-size:14px; }
@media(max-width:768px){ .header-inner{ flex-direction:column; text-align:center; } nav{ margin-top:10px; } nav a{ margin:5px; display:inline-block; } }
</style>

</head>
<body>

<header>
    <div class="header-inner">

        <div class="logo-area">
            <img src="logo/โลโก้.png" alt="Winter Cool Shirt Logo">
            <div class="logo-text">Winter Cool Shirt</div>
        </div>

        <nav>
            <a href="index.php">หน้าแรก</a>
            <a href="products.php">สินค้า</a>
            <a href="cart.php">ตะกร้า</a>
            <a href="orders.php">คำสั่งซื้อ</a>
            <a href="reviews.php">รีวิว</a> 
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php">เข้าสู่ระบบ</a>
                <a href="register.php">สมัครสมาชิก</a>
            <?php endif; ?>
        </nav>

    </div>
</header>
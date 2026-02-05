<?php
session_start();
include __DIR__ . "/../includes/config.php";

// ตรวจสอบสิทธิ์ผู้ดูแลระบบ
if (!isset($_SESSION["admin_id"])) {
    header("Location: admlogin.php");
    exit();
}

// ฟังก์ชันตรวจสอบผลลัพธ์ query ป้องกัน error
function safeQuery($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) {
        die("SQL Error: " . $conn->error . "<br>Query: " . $sql);
    }
    return $result->fetch_assoc();
}

// ดึงข้อมูลสรุปจากฐานข้อมูล
$total_orders    = safeQuery($conn, "SELECT COUNT(*) AS total FROM orders")["total"];
$total_users     = safeQuery($conn, "SELECT COUNT(*) AS total FROM users")["total"];

// ยอดขายรวม = SUM(total_amount) เฉพาะ order ที่ชำระแล้ว
// สถานะในฐานข้อมูลคือ: paid, shipped, completed
$total_sales     = safeQuery($conn,
    "SELECT SUM(total_amount) AS total 
     FROM orders 
     WHERE status='paid' OR status='shipped' OR status='completed'"
)["total"];

// ออเดอร์ที่รอตรวจสอบ = pending
$pending_orders  = safeQuery($conn,
    "SELECT COUNT(*) AS total 
     FROM orders 
     WHERE status='pending'"
)["total"];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดผู้ดูแลระบบ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f8f9fa;
            margin: 0;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 { margin: 0; }
        header a {
            color: white;
            text-decoration: none;
            background-color: #3498db;
            padding: 8px 15px;
            border-radius: 5px;
        }
        header a:hover { background-color: #2980b9; }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 30px auto;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 25px;
            text-align: center;
            transition: all 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card i {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .card h3 {
            margin: 10px 0 5px;
            color: #2c3e50;
        }
        .card p {
            font-size: 22px;
            font-weight: bold;
            color: #27ae60;
        }
        .links {
            margin-top: 40px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        .links a {
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .links a:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<header>
    <h1>แดชบอร์ดผู้ดูแลระบบ</h1>
    <a href="admin/admin_logout.php"><i class="fa fa-sign-out-alt"></i> ออกจากระบบ</a>
</header>

<div class="container">
    <div class="cards">
        <div class="card">
            <i class="fa fa-shopping-cart" style="color:#3498db;"></i>
            <h3>คำสั่งซื้อทั้งหมด</h3>
            <p><?= number_format($total_orders) ?></p>
        </div>
        <div class="card">
            <i class="fa fa-users" style="color:#9b59b6;"></i>
            <h3>จำนวนลูกค้า</h3>
            <p><?= number_format($total_users) ?></p>
        </div>
        <div class="card">
            <i class="fa fa-clock" style="color:#e67e22;"></i>
            <h3>รอตรวจสอบ</h3>
            <p><?= number_format($pending_orders) ?></p>
        </div>
        <div class="card">
            <i class="fa fa-money-bill-wave" style="color:#27ae60;"></i>
            <h3>ยอดขายรวม</h3>
            <p><?= number_format($total_sales, 2) ?> บาท</p>
        </div>
    </div>

    <div class="links">
        <a href="manage_orders.php"><i class="fa fa-list"></i> จัดการคำสั่งซื้อ</a>
        <a href="manage_products.php"><i class="fa fa-box"></i> จัดการสินค้า</a>
        <a href="manage_users.php"><i class="fa fa-user-cog"></i> จัดการผู้ใช้</a>
        <a href="manage_users.php"><i class="fa fa-user-cog"></i> ยอดขาย</a>
    </div>
</div>

</body>
</html>
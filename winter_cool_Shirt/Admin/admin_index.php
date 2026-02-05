<?php
session_start();
include __DIR__ . "/../includes/config.php";

// ✅ ตรวจสอบการ Login
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$display_name = $_SESSION["admin_name"] ?? "ผู้ดูแลระบบ";

try {
    $total_products = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0;
    $total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn() ?: 0;
    $total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
    $total_sales = $conn->query("SELECT SUM(total_amount) FROM orders")->fetchColumn() ?: 0;
    $total_reviews = $conn->query("SELECT COUNT(*) FROM reviews")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $total_products = $total_users = $total_orders = $total_sales = $total_reviews = 0;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #eefbff; 
            --bg-mid: #d1edff;
            --bg-end: #85c1e9;
            --primary-blue: #3498db;
            --dark-blue: #2c3e50;
            --card-white: rgba(255, 255, 255, 0.9);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, var(--bg-start) 0%, var(--bg-mid) 40%, var(--bg-end) 100%);
            background-attachment: fixed;
            margin: 0;
            color: var(--dark-blue);
            min-height: 100vh;
        }

        header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header h1 {
            margin: 0; font-size: 24px; font-weight: 600; color: var(--primary-blue);
        }

        nav { display: flex; gap: 10px; flex-wrap: wrap; }
        nav a {
            color: var(--dark-blue); text-decoration: none; background: rgba(255, 255, 255, 0.6);
            padding: 10px 18px; border-radius: 12px; transition: 0.3s all ease; font-size: 14px;
            border: 1px solid rgba(255,255,255,0.4);
        }
        nav a:hover { background: var(--primary-blue); color: white; transform: translateY(-2px); }
        nav a.logout { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }

        .container {
            width: 95%; /* ขยายความกว้างรวมเล็กน้อย */
            max-width: 1200px;
            margin: 40px auto;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .welcome-section {
            background: var(--card-white);
            padding: 30px; border-radius: 25px; margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        }

        /* ✅ ปรับปรุงส่วน Grid ให้ขนาดพอดี */
        .dashboard-grid {
            display: grid;
            /* ปรับ min-width ให้เล็กลงเล็กน้อย และใช้ 1fr เพื่อให้เฉลี่ยพื้นที่เท่ากัน */
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 20px;
            align-items: start; /* ให้การ์ดมีความสูงเริ่มต้นตามเนื้อหา */
        }

        .card {
            background: var(--card-white);
            padding: 30px 20px;
            border-radius: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            text-align: center;
            transition: all 0.4s ease;
            border: 1px solid rgba(255,255,255,0.6);
            backdrop-filter: blur(5px);
            /* ควบคุมความกว้างไม่ให้บานเกินไปในหน้าจอใหญ่ */
            max-width: 100%; 
            width: 100%;
            margin: 0 auto;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(52, 152, 219, 0.15);
            background: #ffffff;
        }

        .card .icon-circle {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px; font-size: 28px;
        }

        .card h3 {
            margin: 0; color: #95a5a6; font-size: 14px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        .card .value {
            font-size: 30px; font-weight: 600; color: var(--dark-blue); margin: 10px 0 20px;
        }

        .card a {
            display: inline-block; text-decoration: none; color: var(--primary-blue);
            background: #f0faff; padding: 10px 20px; border-radius: 12px;
            font-size: 13px; font-weight: 500; transition: 0.3s;
            border: 1px solid #d1edff; width: 100%; /* ให้ปุ่มกว้างเท่ากัน */
        }

        .card a:hover { background: var(--primary-blue); color: white; }

        @media (max-width: 768px) {
            header { flex-direction: column; padding: 20px; }
            nav { margin-top: 15px; }
            .dashboard-grid {
                grid-template-columns: repeat(auto-fit, minmax(100%, 1fr)); /* มือถือให้เต็มบรรทัด */
            }
        }
    </style>
</head>
<body>

<header>
    <h1>❄ Dashboard</h1>
    <nav>
        <a href="admin_index.php">หน้าแรก</a>
        <a href="manage_products.php">สินค้า</a>
        <a href="admin_user.php">สมาชิก</a>
        <a href="manage_orders.php">คำสั่งซื้อ</a>
        <a href="admin_sales_summary.php">ยอดขาย</a>
        <a href="admin_manage_reviews.php">รีวิว</a>
        
        <a href="../logout.php" class="logout">ออกจากระบบ</a>
    </nav>
</header>

<div class="container">
    <div class="welcome-section">
        <h2>สวัสดีตอนเช้า, <?php echo htmlspecialchars($display_name); ?> ✨</h2>
        <p>บทบาท: <strong style="color:var(--primary-blue)"><?= strtoupper($_SESSION['role'] ?? 'Admin') ?></strong></p>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <div class="icon-circle">📦</div>
            <h3>คลังสินค้าทั้งหมด</h3>
            <div class="value"><?= number_format($total_products); ?></div>
            <a href="manage_products.php">จัดการข้อมูล</a>
        </div>

        <div class="card">
            <div class="icon-circle">👥</div>
            <h3>สมาชิกทั้งหมด</h3>
            <div class="value"><?= number_format($total_users); ?></div>
            <a href="admin_user.php">ตรวจสอบสมาชิก</a>
        </div>

        <div class="card">
            <div class="icon-circle">📜</div>
            <h3>ออเดอร์ทั้งหมด</h3>
            <div class="value"><?= number_format($total_orders); ?></div>
            <a href="manage_orders.php">ดูรายละเอียด</a>
        </div>

        <div class="card">
            <div class="icon-circle">💰</div>
            <h3>ยอดขายสะสม</h3>
            <div class="value" style="color: #27ae60;">฿<?= number_format($total_sales, 2); ?></div>
            <a href="admin_sales_summary.php">รายงานการเงิน</a>
        </div>

        <div class="card">
            <div class="icon-circle">⭐</div>
            <h3>รีวิวจากลูกค้า</h3>
            <div class="value"><?= number_format($total_reviews); ?></div>
            <a href="admin_manage_reviews.php">อ่านความคิดเห็น</a>
        </div>
    </div>
</div>

</body>
</html>
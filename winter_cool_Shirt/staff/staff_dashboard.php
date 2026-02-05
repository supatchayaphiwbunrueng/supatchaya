<?php
session_start();
include __DIR__ . "/../includes/config.php";

// ตรวจสอบสิทธิ์ (เปลี่ยนจาก admin_id เป็น staff_id ตามระบบของพี่)
if (!isset($_SESSION["staff_id"])) {
    header("Location: staff_login.php");
    exit();
}

// ฟังก์ชันตรวจสอบผลลัพธ์ query (หนูใช้รูปแบบ PDO ตามที่ไฟล์ config ส่วนใหญ่ใช้กัน)
function safeQueryCount($conn, $sql) {
    try {
        $stmt = $conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['total'] : 0;
    } catch (Exception $e) {
        return 0;
    }
}

// ดึงข้อมูลสรุปสำหรับสตาฟ
// 1. สินค้าทั้งหมด
$total_products = safeQueryCount($conn, "SELECT COUNT(*) AS total FROM products");

// 2. สินค้าที่สต๊อกต่ำ (น้อยกว่า 20 ชิ้น) - เลียนแบบช่อง "รอตรวจสอบ" ของแอดมิน
$low_stock = safeQueryCount($conn, "SELECT COUNT(*) AS total FROM products WHERE stock < 20");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แดชบอร์ดพนักงาน (Staff)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: #f8f9fa;
            margin: 0;
        }
        header {
            background-color: #2c3e50; /* สีเข้มแบบ Admin */
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 { margin: 0; font-size: 24px; }
        header a {
            color: white;
            text-decoration: none;
            background-color: #e74c3c; /* สีแดงปุ่มออกจากระบบ */
            padding: 8px 15px;
            border-radius: 5px;
        }
        header a:hover { background-color: #c0392b; }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 30px auto;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 35px 25px;
            text-align: center;
            transition: all 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card i {
            font-size: 50px;
            margin-bottom: 15px;
        }
        .card h3 {
            margin: 10px 0 5px;
            color: #2c3e50;
        }
        .card p {
            font-size: 30px;
            font-weight: bold;
            color: #27ae60;
            margin: 0;
        }
        .links {
            margin-top: 40px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .links a {
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
        }
        .links a:hover {
            background-color: #2980b9;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<header>
    <h1><i class="fa fa-user-shield"></i> ระบบจัดการพนักงาน</h1>
    <a href="../logout.php"><i class="fa fa-sign-out-alt"></i> ออกจากระบบ</a>
</header>

<div class="container">
    <div class="cards">
        <div class="card">
            <i class="fa fa-box" style="color:#3498db;"></i>
            <h3>รายการสินค้าทั้งหมด</h3>
            <p><?= number_format($total_products) ?> รายการ</p>
        </div>

        <div class="card">
            <i class="fa fa-clock" style="color:#e67e22;"></i>
            <h3>สต๊อกต่ำกว่า 20 ชิ้น</h3>
            <p style="color:#e67e22;"><?= number_format($low_stock) ?> รายการ</p>
        </div>
    </div>

    <div class="links">
        <a href="manage_products.php"><i class="fa fa-edit"></i> จัดการรายการสินค้า</a>
        <a href="manage_stock.php"><i class="fa fa-cubes"></i> จัดการสต๊อกสินค้า</a>
        <a href="../index.php" style="background-color: #95a5a6;"><i class="fa fa-store"></i> ดูหน้าเว็บไซต์</a>
    </div>
</div>

</body>
</html>
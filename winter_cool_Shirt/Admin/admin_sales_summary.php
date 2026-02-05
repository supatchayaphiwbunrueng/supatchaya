<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include __DIR__ . "/../includes/config.php";

// ✅ 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('เฉพาะแอดมินเท่านั้นที่เข้าหน้านี้ได้'); window.location='admin_login.php';</script>";
    exit();
}

// ✅ 2. ดึงข้อมูลสรุปภาพรวม (Overview)
// ยอดขายรวมทั้งหมด (เฉพาะที่จัดส่งแล้วหรือสำเร็จ)
$stmt_total = $conn->query("SELECT SUM(total_price) as grand_total FROM orders WHERE status = 'shipped'");
$grand_total = $stmt_total->fetch(PDO::FETCH_ASSOC)['grand_total'] ?? 0;

// จำนวนออเดอร์แยกตามสถานะ
$stmt_stats = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$stats_data = $stmt_stats->fetchAll(PDO::FETCH_ASSOC);
$stats = ['pending' => 0, 'processing' => 0, 'shipped' => 0, 'cancelled' => 0];
foreach ($stats_data as $row) {
    $stats[$row['status']] = $row['count'];
}

// ✅ 3. ดึงข้อมูลยอดขายรายเดือน (สำหรับตาราง)
$monthly_query = "SELECT DATE_FORMAT(created_at, '%M %Y') as month_name, 
                         SUM(total_price) as monthly_total, 
                         COUNT(*) as order_count 
                  FROM orders 
                  WHERE status = 'shipped' 
                  GROUP BY month_name 
                  ORDER BY created_at DESC 
                  LIMIT 12";
$monthly_sales = $conn->query($monthly_query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปยอดขาย - Elite Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #f0f9ff; --bg-end: #cbebff;
            --primary-blue: #3498db; --dark-blue: #2c3e50;
            --success: #2ecc71; --danger: #e74c3c; --warning: #f39c12;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, var(--bg-start) 0%, var(--bg-end) 100%);
            margin: 0; min-height: 100vh; color: var(--dark-blue);
        }

        .top-nav {
            background: white; padding: 12px 5%;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .top-nav h1 { margin: 0; font-size: 20px; color: var(--primary-blue); display: flex; align-items: center; gap: 8px; }
        
        .back-home {
            text-decoration: none; background: #ebf5ff; color: var(--primary-blue);
            padding: 8px 18px; border-radius: 50px; font-size: 14px; font-weight: 500;
            transition: 0.3s; border: 1px solid #d1e9ff; display: flex; align-items: center; gap: 5px;
        }
        .back-home:hover { background: var(--primary-blue); color: white; }

        .container { width: 95%; max-width: 1200px; margin: 40px auto; }

        /* Dashboard Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-item {
            background: white; padding: 25px; border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            border: 1px solid rgba(255,255,255,0.8);
        }
        .stat-item h3 { margin: 0; font-size: 14px; color: #94a3b8; font-weight: 400; }
        .stat-item .value { font-size: 28px; font-weight: 600; margin: 10px 0; color: var(--dark-blue); }
        .stat-item .trend { font-size: 12px; font-weight: 500; }

        .admin-card {
            background: white; padding: 35px; border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.02);
        }

        .table-responsive { overflow-x: auto; margin-top: 25px; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 15px; text-align: left; border-bottom: 2px solid #f8fafc; color: #94a3b8; font-size: 13px; }
        td { padding: 20px 15px; border-bottom: 1px solid #f1f5f9; font-size: 15px; }
        
        .text-success { color: var(--success); font-weight: 600; }
    </style>
</head>
<body>

<div class="top-nav">
    <a href="admin_index.php" style="text-decoration:none;"><h1>📊 รายงานสรุปยอดขาย</h1></a>
    <a href="admin_index.php" class="back-home">🏠 กลับหน้าหลัก</a>
</div>

<div class="container">
    
    <div class="stats-grid">
        <div class="stat-item">
            <h3>💰 ยอดขายสุทธิ (จัดส่งแล้ว)</h3>
            <div class="value">฿<?= number_format($grand_total, 2) ?></div>
            <span class="trend" style="color: var(--success);">● รายได้จากการขายจริง</span>
        </div>
        <div class="stat-item">
            <h3>📦 รอชำระ/เตรียมส่ง</h3>
            <div class="value"><?= $stats['pending'] + $stats['processing'] ?></div>
            <span class="trend" style="color: var(--warning);">● รายการที่กำลังดำเนินการ</span>
        </div>
        <div class="stat-item">
            <h3>✅ ส่งสำเร็จแล้ว</h3>
            <div class="value"><?= $stats['shipped'] ?></div>
            <span class="trend" style="color: var(--primary-blue);">● ออเดอร์ที่ปิดการขาย</span>
        </div>
    </div>

    <div class="admin-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 10px;">
            <div>
                <h2 style="margin:0; color: var(--dark-blue);">📈 ยอดขายรายเดือน</h2>
                <p style="margin:5px 0 0; font-size: 13px; color: #94a3b8;">ตารางเปรียบเทียบยอดขายในแต่ละช่วงเวลา</p>
            </div>
        </div>

        <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 20px 0;">

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>เดือน/ปี</th>
                        <th>จำนวนออเดอร์</th>
                        <th>ยอดขายรวม (เฉพาะที่ส่งสำเร็จ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($monthly_sales)): ?>
                        <?php foreach ($monthly_sales as $ms): ?>
                        <tr>
                            <td style="font-weight: 500;"><?= $ms['month_name'] ?></td>
                            <td><?= $ms['order_count'] ?> รายการ</td>
                            <td class="text-success">฿<?= number_format($ms['monthly_total'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align:center; padding: 40px; color: #94a3b8;">📭 ยังไม่มีข้อมูลการขายที่สำเร็จ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
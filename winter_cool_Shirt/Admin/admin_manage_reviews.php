<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include __DIR__ . "/../includes/config.php";

// ✅ 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('เฉพาะแอดมินเท่านั้นที่เข้าหน้านี้ได้'); window.location='admin_login.php';</script>";
    exit();
}

// ✅ 2. Logic การลบรีวิว
if (isset($_GET['delete_review'])) {
    $review_id = (int)$_GET['delete_review'];
    try {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
        $stmt->execute([$review_id]);
        header("Location: admin_manage_reviews.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('ไม่สามารถลบได้: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// ✅ 3. ดึงข้อมูลรีวิวทั้งหมด พร้อม Join ตารางที่เกี่ยวข้อง
try {
    $query = "SELECT r.*, u.username, p.name as product_name 
              FROM reviews r
              JOIN users u ON r.user_id = u.user_id
              JOIN products p ON r.product_id = p.product_id
              ORDER BY r.created_at DESC";
    $reviews = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

    // คำนวณสถิติภาพรวม
    $total_reviews = count($reviews);
    $avg_rating = ($total_reviews > 0) ? array_sum(array_column($reviews, 'rating')) / $total_reviews : 0;
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการรีวิว - Elite Admin</title>
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
            background: white; padding: 15px 5%;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .top-nav h1 { margin: 0; font-size: 20px; color: var(--primary-blue); }
        .back-home {
            text-decoration: none; background: #ebf5ff; color: var(--primary-blue);
            padding: 8px 18px; border-radius: 50px; font-size: 14px; font-weight: 500;
            transition: 0.3s; border: 1px solid #d1e9ff;
        }
        .back-home:hover { background: var(--primary-blue); color: white; }
        .container { width: 95%; max-width: 1200px; margin: 40px auto; }

        /* Stats Section */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-item { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); }
        .stat-item h3 { margin: 0; font-size: 14px; color: #94a3b8; }
        .stat-item .value { font-size: 28px; font-weight: 600; margin: 10px 0; }

        .admin-card { background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .rating-star { color: #f1c40f; }
        .rating-empty { color: #d1d5db; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { padding: 15px; text-align: left; border-bottom: 2px solid #f8fafc; color: #94a3b8; font-size: 13px; }
        td { padding: 20px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        
        .btn-del { color: var(--danger); text-decoration: none; border: 1px solid #fee2e2; padding: 6px 15px; border-radius: 8px; font-size: 13px; transition: 0.2s; }
        .btn-del:hover { background: #fee2e2; }
        .alert { padding: 15px; background: #dcfce7; color: #15803d; border-radius: 12px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

<div class="top-nav">
    <h1>⭐ ระบบจัดการรีวิว Elite Admin</h1>
    <a href="admin_index.php" class="back-home">🏠 กลับหน้าหลัก</a>
</div>

<div class="container">
    <div class="stats-grid">
        <div class="stat-item">
            <h3>💬 จำนวนรีวิวทั้งหมด</h3>
            <div class="value"><?= $total_reviews ?> รายการ</div>
        </div>
        <div class="stat-item">
            <h3>🌟 คะแนนเฉลี่ยร้านค้า</h3>
            <div class="value"><?= number_format($avg_rating, 1) ?> / 5.0</div>
        </div>
    </div>

    <div class="admin-card">
        <h2>📋 รายการรีวิวจากลูกค้า</h2>
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert">✅ ลบรีวิวเรียบร้อยแล้ว</div>
        <?php endif; ?>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>วันที่ / ลูกค้า</th>
                        <th>สินค้า</th>
                        <th>คะแนน</th>
                        <th>ข้อความรีวิว</th>
                        <th style="text-align:center;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($total_reviews > 0): ?>
                        <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($r['username']) ?></strong><br>
                                <small style="color:#94a3b8;"><?= date('d/m/Y', strtotime($r['created_at'])) ?></small>
                            </td>
                            <td><span style="color:var(--primary-blue);"><?= htmlspecialchars($r['product_name']) ?></span></td>
                            <td>
                                <?php for($i=1; $i<=5; $i++) echo ($i <= $r['rating']) ? '<span class="rating-star">★</span>' : '<span class="rating-empty">★</span>'; ?>
                            </td>
                            <td><p style="color:#64748b; font-size:13px;"><?= nl2br(htmlspecialchars($r['comment'])) ?></p></td>
                            <td style="text-align:center;">
                                <a href="admin_manage_reviews.php?delete_review=<?= $r['review_id'] ?>" class="btn-del" onclick="return confirm('ยืนยันการลบรีวิวนี้?')">ลบรีวิว</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:50px; color:#94a3b8;">ยังไม่มีข้อมูลรีวิวในขณะนี้</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
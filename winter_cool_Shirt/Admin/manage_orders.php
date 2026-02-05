<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include __DIR__ . "/../includes/config.php";

// ✅ 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('เฉพาะแอดมินเท่านั้นที่เข้าหน้านี้ได้'); window.location='admin_login.php';</script>";
    exit();
}

// ✅ 2. Logic การอัปเดตสถานะคำสั่งซื้อ (เพิ่มการระบุตารางให้ชัดเจน)
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    try {
        // ระบุเป็น orders.status เพื่อป้องกันข้อผิดพลาด Unknown Column ในบาง Environment
        $stmt = $conn->prepare("UPDATE orders SET `status` = ? WHERE `order_id` = ?");
        $stmt->execute([$new_status, $order_id]);
        
        header("Location: manage_orders.php?msg=updated");
        exit();
    } catch (PDOException $e) {
        // ถ้ายัง Error ให้โชว์ Error Message แบบละเอียด
        echo "<script>alert('เกิดข้อผิดพลาดทางฐานข้อมูล: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// ✅ 3. Logic การลบคำสั่งซื้อ
if (isset($_GET['delete_order'])) {
    $order_id = (int)$_GET['delete_order'];
    try {
        $conn->beginTransaction();
        // ลบข้อมูลที่เกี่ยวข้องใน order_items ก่อน
        $conn->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$order_id]);
        $conn->prepare("DELETE FROM orders WHERE order_id = ?")->execute([$order_id]);
        $conn->commit();
        header("Location: manage_orders.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "<script>alert('ไม่สามารถลบได้: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// ✅ 4. ดึงข้อมูลคำสั่งซื้อทั้งหมด (ใช้ Alias 'o' กำกับทุกจุด)
$query = "SELECT o.*, u.username, u.email 
          FROM orders o 
          JOIN users u ON o.user_id = u.user_id 
          ORDER BY o.created_at DESC";
$orders = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำสั่งซื้อ - Elite Admin</title>
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

        .admin-card {
            background: white; padding: 35px; border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.02);
        }

        /* Status Badges */
        .status-badge { padding: 5px 15px; border-radius: 50px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-pending { background: #fef9c3; color: #a16207; }
        .status-processing { background: #e0f2fe; color: #0369a1; }
        .status-shipped { background: #dcfce7; color: #15803d; }
        .status-cancelled { background: #fee2e2; color: #ef4444; }

        .table-responsive { overflow-x: auto; margin-top: 25px; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { padding: 15px; text-align: left; border-bottom: 2px solid #f8fafc; color: #94a3b8; font-size: 13px; text-transform: uppercase; }
        td { padding: 20px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }

        select { padding: 8px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: 'Kanit'; outline: none; }
        .btn-save { background: var(--primary-blue); color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-weight: 500; }
        .btn-save:hover { background: #2980b9; transform: translateY(-1px); }
        
        .btn-del { color: var(--danger); text-decoration: none; padding: 7px 15px; border-radius: 8px; border: 1px solid #fee2e2; transition: 0.2s; }
        .btn-del:hover { background: var(--danger); color: white; }

        .alert { padding: 15px; background: #dcfce7; color: #15803d; border-radius: 12px; margin-bottom: 25px; text-align: center; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>

<div class="top-nav">
    <a href="admin_index.php" style="text-decoration:none;"><h1>📦 ระบบจัดการคำสั่งซื้อ</h1></a>
    <a href="admin_index.php" class="back-home">🏠 กลับหน้าหลัก</a>
</div>

<div class="container">
    <div class="admin-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 style="margin:0; color: var(--dark-blue);">🛒 รายการสั่งซื้อทั้งหมด</h2>
                <p style="margin:5px 0 0; font-size: 13px; color: #94a3b8;">จัดการสถานะการชำระเงินและการส่งสินค้า</p>
            </div>
            <div style="text-align: right; background: #f8fafc; padding: 10px 20px; border-radius: 15px;">
                <span style="font-size: 24px; font-weight: 600; color: var(--primary-blue);"><?= count($orders) ?></span>
                <span style="font-size: 12px; color: #94a3b8; display: block;">ออเดอร์ในระบบ</span>
            </div>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert">
                <b>สำเร็จ!</b> <?= $_GET['msg'] == 'updated' ? 'อัปเดตสถานะคำสั่งซื้อแล้ว' : 'ลบข้อมูลเรียบร้อย' ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>เลขที่สั่งซื้อ / วันที่</th>
                        <th>ข้อมูลลูกค้า</th>
                        <th>ยอดรวมสุทธิ</th>
                        <th>สถานะปัจจุบัน</th>
                        <th>เปลี่ยนสถานะ</th>
                        <th style="text-align:center;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($orders)): ?>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <b>#ORD-<?= $o['order_id'] ?></b><br>
                                <small style="color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></small>
                            </td>
                            <td>
                                <b><?= htmlspecialchars($o['username']) ?></b><br>
                                <small style="color:#94a3b8;"><?= htmlspecialchars($o['email']) ?></small>
                            </td>
                            <td>
                                <span style="font-weight:600; color: var(--primary-blue);">฿<?= number_format($o['total_price'], 2) ?></span>
                            </td>
                            <td>
                                <?php 
                                    $current_st = $o['status'] ?? 'pending';
                                    $status_labels = [
                                        'pending' => 'รอชำระเงิน',
                                        'processing' => 'กำลังเตรียมส่ง',
                                        'shipped' => 'จัดส่งแล้ว',
                                        'cancelled' => 'ยกเลิก'
                                    ];
                                ?>
                                <span class="status-badge status-<?= strtolower($current_st) ?>">
                                    <?= $status_labels[$current_st] ?? $current_st ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display:flex; gap:8px;">
                                    <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                    <select name="new_status">
                                        <?php foreach($status_labels as $val => $label): ?>
                                            <option value="<?= $val ?>" <?= $current_st == $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-save">อัปเดต</button>
                                </form>
                            </td>
                            <td style="text-align:center;">
                                <a href="manage_orders.php?delete_order=<?= $o['order_id'] ?>" 
                                   class="btn-del" 
                                   onclick="return confirm('ยืนยันการลบ? ข้อมูลออเดอร์และรายการสินค้าจะถูกลบถาวร')">ลบ</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 50px; color: #94a3b8;">
                                📭 ยังไม่มีประวัติการสั่งซื้อ
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
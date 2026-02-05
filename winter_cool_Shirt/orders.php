<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'includes/config.php';
include 'includes/header.php';

// 1. ตรวจสอบการ Login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('กรุณาเข้าสู่ระบบก่อน'); window.location.href='login.php';</script>";
    exit();
}
$user_id = $_SESSION['user_id'];

// --- ส่วนที่ 2: บันทึกข้อมูลใหม่ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['total_price'])) {
    try {
        // บันทึกข้อมูลพร้อมเบอร์โทรและสถานะเบื้องต้น
        $sql_insert = "INSERT INTO orders (user_id, customer_name, customer_phone, customer_address, payment_method, bank_name, total_price, order_status, shipping_status, created_at) 
                       VALUES (:uid, :cname, :cphone, :caddr, :pay, :bank, :total, 'ชำระเสร็จสิ้น', 'เตรียมจัดส่ง', NOW())";
        
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([
            ':uid'    => $user_id,
            ':cname'  => $_POST['customer_name'],
            ':cphone' => $_POST['customer_phone'],
            ':caddr'  => $_POST['customer_address'],
            ':pay'    => $_POST['payment_method'],
            ':bank'   => ($_POST['payment_method'] == 'โอนเงิน') ? ($_POST['bank_name'] ?? '-') : '-',
            ':total'  => (float)$_POST['total_price']
        ]);

        unset($_SESSION['cart']); 
        echo "<script>alert('สั่งซื้อและแจ้งชำระเงินเรียบร้อยแล้ว!'); window.location.href='orders.php';</script>";
        exit();
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>ไม่สามารถบันทึกคำสั่งซื้อได้: " . $e->getMessage() . "</div>";
    }
}

// --- ส่วนที่ 3: ดึงข้อมูลประวัติ ---
try {
    $sql_select = "SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql_select);
    $stmt->execute([':uid' => $user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
}

// --- ฟังก์ชันกำหนดสี Badge ---
function getStatusStyle($status) {
    switch ($status) {
        case 'ชำระเสร็จสิ้น': case 'จัดส่งสำเร็จ': return 'background-color: #2ecc71;'; // เขียว
        case 'เตรียมจัดส่ง': case 'กำลังจัดส่ง': return 'background-color: #3498db;'; // ฟ้า
        case 'รอชำระเงิน': return 'background-color: #f39c12;'; // ส้ม
        default: return 'background-color: #95a5a6;'; // เทา
    }
}
?>

<style>
    :root { --primary-blue: #3498db; --light-bg: #f0faff; }
    body { background-color: var(--light-bg); font-family: 'Kanit', sans-serif; }
    .history-container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
    .order-card { background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; border: 1px solid #b3e5fc; overflow: hidden; }
    .order-header { background: #e1f5fe; padding: 18px 25px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #b3e5fc; }
    
    .status-group { display: flex; gap: 8px; flex-wrap: wrap; }
    .status-badge { 
        padding: 4px 12px; 
        border-radius: 20px; 
        font-size: 0.75em; 
        font-weight: bold; 
        color: white; 
        display: inline-flex; 
        align-items: center; 
        gap: 5px; 
    }
    
    .order-body { padding: 25px; }
    .info-group { display: grid; grid-template-columns: 130px 1fr; margin-bottom: 8px; font-size: 0.95em; }
    .info-label { font-weight: 500; color: #7f8c8d; }
    .total-amount { font-size: 1.3em; color: #e74c3c; font-weight: bold; }
    .tracking-box { background: #f8f9fa; padding: 15px; border-radius: 10px; margin-top: 15px; border-left: 5px solid var(--primary-blue); }
</style>

<div class="history-container">
    <h2 style="text-align:center; color: var(--primary-blue); margin-bottom:35px;"><i class="fas fa-box-open"></i>ประวัติคำสั่งซื้อ</h2>

    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <strong style="color:#0277bd;">คำสั่งซื้อ #<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></strong><br>
                        <small style="color:#546e7a;"><i class="far fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
                    </div>
                    
                    <div class="status-group">
                        <span class="status-badge" style="<?= getStatusStyle($order['order_status']) ?>" title="สถานะชำระเงิน">
                            <i class="fas fa-wallet"></i> <?= htmlspecialchars($order['order_status']) ?>
                        </span>
                        <span class="status-badge" style="<?= getStatusStyle($order['shipping_status'] ?? 'เตรียมจัดส่ง') ?>" title="สถานะจัดส่ง">
                            <i class="fas fa-truck"></i> <?= !empty($order['shipping_status']) ? htmlspecialchars($order['shipping_status']) : 'เตรียมจัดส่ง' ?>
                        </span>
                    </div>
                </div>

                <div class="order-body">
                    <div class="info-group">
                        <span class="info-label">ผู้รับ:</span>
                        <span><?= htmlspecialchars($order['customer_name']) ?></span>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">เบอร์โทรศัพท์:</span>
                        <span><?= htmlspecialchars($order['customer_phone']) ?></span>
                    </div>

                    <div class="info-group">
                        <span class="info-label">ที่อยู่จัดส่ง:</span>
                        <span><?= htmlspecialchars($order['customer_address']) ?></span>
                    </div>

                    <div class="info-group">
                        <span class="info-label">การชำระเงิน:</span>
                        <span>
                            <?= htmlspecialchars($order['payment_method']) ?> 
                            <?= ($order['payment_method'] == 'โอนเงิน' && !empty($order['bank_name'])) ? "(".htmlspecialchars($order['bank_name']).")" : "" ?>
                        </span>
                    </div>
                    
                    <?php if(!empty($order['tracking_number'])): ?>
                    <div class="tracking-box">
                        <div style="font-weight: bold; color: #2c3e50; margin-bottom: 5px;"><i class="fas fa-shipping-fast"></i> ข้อมูลการจัดส่ง</div>
                        <div class="info-group" style="margin-bottom:0;">
                            <span class="info-label">เลขพัสดุ:</span>
                            <span style="color: var(--primary-blue); font-weight: bold; font-size: 1.1em;"><?= htmlspecialchars($order['tracking_number']) ?></span>
                        </div>
                    </div>
                    <?php else: ?>
                        <p style="font-size: 0.85em; color: #95a5a6; margin-top: 15px;"><i class="fas fa-sync-alt fa-spin"></i> ร้านค้ากำลังเตรียมสินค้าเพื่อจัดส่ง</p>
                    <?php endif; ?>

                    <div style="text-align: right; margin-top: 20px; border-top: 1px dashed #ddd; padding-top: 15px;">
                        <span style="color:#7f8c8d; margin-right: 10px;">ยอดชำระทั้งสิ้น:</span>
                        <span class="total-amount"><?= number_format($order['total_price'], 2) ?> ฿</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align:center; padding:80px; background:white; border-radius:15px; border: 2px dashed #b3e5fc;">
            <p style="color:#95a5a6;">ยังไม่มีประวัติการสั่งซื้อในขณะนี้</p>
            <a href="index.php" style="color: var(--primary-blue); text-decoration: none; font-weight: bold;">ไปเลือกชมสินค้ากันเลย!</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
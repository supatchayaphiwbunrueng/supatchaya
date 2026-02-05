<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/config.php';
include 'includes/header.php';

// ป้องกันการเข้าหน้าชำระเงินโดยไม่มีสินค้า
if (empty($_SESSION['cart'])) { 
    header("Location: products.php"); 
    exit(); 
}

$grand_total = 0;
foreach ($_SESSION['cart'] as $item) { 
    $grand_total += $item['price']; 
}
?>

<style>
    body { background-color: #f8f9fa; }
    .pay-container { max-width: 1000px; margin: 40px auto; font-family: 'Kanit', sans-serif; padding: 0 15px; }
    .pay-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 25px; }
    @media (max-width: 850px) { .pay-grid { grid-template-columns: 1fr; } }
    
    .pay-card { background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; }
    .section-title { font-size: 20px; font-weight: 600; color: #2c3e50; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    
    .input-group { margin-bottom: 18px; }
    .input-group label { display: block; margin-bottom: 8px; color: #666; font-size: 14px; }
    .form-control { width: 100%; padding: 12px 15px; border: 1px solid #e0e0e0; border-radius: 10px; font-family: 'Kanit'; transition: 0.3s; }
    .form-control:focus { border-color: #2e86c1; outline: none; box-shadow: 0 0 0 3px rgba(46,134,193,0.1); }
    
    .bank-selection { margin-top: 15px; display: none; background: #fdfdfd; border: 1px solid #edf2f7; padding: 20px; border-radius: 12px; }
    .bank-option { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 10px; cursor: pointer; transition: 0.2s; }
    .bank-option:hover { background: #f0f7ff; }
    
    .summary-item { display: flex; justify-content: space-between; margin-bottom: 12px; color: #555; font-size: 15px; }
    .total-price { font-size: 28px; font-weight: 700; color: #e74c3c; margin-top: 10px; }
    .confirm-btn { width: 100%; background: linear-gradient(135deg, #2e86c1, #21618c); color: white; border: none; padding: 18px; border-radius: 50px; font-size: 18px; font-weight: 600; cursor: pointer; margin-top: 25px; transition: 0.3s; box-shadow: 0 4px 15px rgba(46,134,193,0.3); }
    .confirm-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46,134,193,0.4); }
</style>

<div class="pay-container">
    <h2 style="text-align: center; margin-bottom: 35px;">📦ชำระเงิน</h2>
    
    <form action="orders.php" method="POST">
        <div class="pay-grid">
            <div class="pay-card">
                <div class="section-title">📍 ที่อยู่สำหรับจัดส่ง</div>
                <div class="input-group">
                    <label>ชื่อ-นามสกุล ผู้รับ</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="ระบุชื่อจริง-นามสกุล" required>
                </div>
                <div class="input-group">
                    <label>เบอร์โทรศัพท์ที่ติดต่อได้</label>
                    <input type="tel" name="customer_phone" class="form-control" placeholder="เช่น 0812345678" required>
                </div>
                <div class="input-group">
                    <label>ที่อยู่โดยละเอียด</label>
                    <textarea name="customer_address" class="form-control" rows="3" placeholder="บ้านเลขที่, ซอย, ถนน, ตำบล, อำเภอ, จังหวัด, รหัสไปรษณีย์" required></textarea>
                </div>

                <div class="section-title" style="margin-top: 35px;">💳 ช่องทางการชำระเงิน</div>
                <div class="input-group">
                    <select name="payment_method" id="pay_method" class="form-control" onchange="togglePaymentDetails()" required>
                        <option value="โมบายแบงก์กิ้ง">โมบายแบงก์กิ้ง (โอนผ่านแอปธนาคาร)</option>
                        <option value="ชำระเงินปลายทาง">เก็บเงินปลายทาง (COD)</option>
                    </select>
                </div>

                <div id="bank_selection" class="bank-selection">
                    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">เลือกธนาคารที่ต้องการโอน:</p>
                    
                    <div class="bank-option">
                        <input type="radio" name="bank_name" value="ธนาคารกสิกรไทย" id="kbank" checked>
                        <label for="kbank"><strong>ธนาคารกสิกรไทย:</strong> </label>
                    </div>

                    <div class="bank-option">
                        <input type="radio" name="bank_name" value="ธนาคารกรุงไทย" id="ktb">
                        <label for="ktb"><strong>ธนาคารกรุงไทย:</strong> </label>
                    </div>

                    <div class="bank-option">
                        <input type="radio" name="bank_name" value="ธนาคารออมสิน" id="gsb">
                        <label for="gsb"><strong>ธนาคารออมสิน:</strong> </label>
                    </div>
                    
                    <p style="font-size: 12px; color: #e67e22; margin-top: 10px;">* เมื่อโอนเงินแล้ว โปรดเก็บหลักฐานการโอนไว้เพื่อยืนยัน</p>
                </div>
            </div>

            <div class="pay-card" style="height: fit-content;">
                <div class="section-title">📑 สรุปคำสั่งซื้อ</div>
                <div style="max-height: 250px; overflow-y: auto; margin-bottom: 20px;">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="summary-item">
                            <span><?= htmlspecialchars($item['name']) ?> (<?= $item['size'] ?>)</span>
                            <span><?= number_format($item['price']) ?> ฿</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="border-top: 1px solid #eee; padding-top: 15px;">
                    <div class="summary-item">
                        <span>ค่าจัดส่ง</span>
                        <span style="color: #27ae60;">ฟรี</span>
                    </div>
                    <div class="summary-item" style="margin-top: 10px;">
                        <strong>ยอดชำระทั้งสิ้น</strong>
                    </div>
                    <div class="total-price"><?= number_format($grand_total, 2) ?> ฿</div>
                </div>

                <input type="hidden" name="total_price" value="<?= $grand_total ?>">
                <button type="submit" class="confirm-btn">สั่งซื้อสินค้าตอนนี้</button>
                <p style="text-align: center; font-size: 12px; color: #999; margin-top: 15px;">
                    การคลิกปุ่มเป็นการยอมรับเงื่อนไขการบริการ
                </p>
            </div>
        </div>
    </form>
</div>

<script>
function togglePaymentDetails() {
    var method = document.getElementById("pay_method").value;
    var bankDiv = document.getElementById("bank_selection");
    
    if (method === "โมบายแบงก์กิ้ง") {
        bankDiv.style.display = "block";
        // บังคับเลือกธนาคารตัวแรก
        document.getElementById("kbank").required = true;
    } else {
        bankDiv.style.display = "none";
        document.getElementById("kbank").required = false;
    }
}

// รันฟังก์ชันทันทีเมื่อโหลดหน้าเพื่อตั้งค่าเริ่มต้น
window.onload = togglePaymentDetails;
</script>

<?php include 'includes/footer.php'; ?>
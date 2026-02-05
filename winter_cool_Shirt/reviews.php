<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'includes/config.php';
include 'includes/header.php';

// --- 1. รายการสินค้า (ต้องตรงกับใน product_detail.php) ---
$all_products_list = [
    1 => "เสื้อยืดแขนยาวสีขาว", 2 => "เสื้อยืดแขนยาวดำ", 3 => "เสื้อยืดแขนยาวสีน้ำตาล",
    4 => "เสื้อคาร์ดิแกนสีขาว", 5 => "เสื้อคาร์ดิแกนสีเทา", 6 => "เสื้อคาร์ดิแกนสีแดง",
    7 => "เสื้อสเวตเตอร์สีขาว", 8 => "เสื้อสเวตเตอร์สีดำ", 9 => "เสื้อสเวตเตอร์สีชมพู",
    10 => "เสื้อฮู้ดสีขาว", 11 => "เสื้อฮู้ดสีดำ", 12 => "เสื้อฮู้ดสีฟ้า",
    13 => "แจ็คเก็ตยีนส์สีขาว", 14 => "แจ็คเก็ตยีนส์สีดำ", 15 => "แจ็คเก็ตยีนส์สีฟ้าอ่อน",
    16 => "เสื้อไหมพรมสีขาว", 17 => "เสื้อไหมพรมสีดำ", 18 => "เสื้อไหมพรมสีชมพู",
    19 => "เสื้อกันลมสีชมพูอ่อน", 20 => "เสื้อกันลมสีน้ำตาลแดง", 21 => "เสื้อกันลมสีดำ"
];

// --- 2. รับค่า PID และประเภท ---
$pid = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
$view_type = (isset($_GET['type']) && $_GET['type'] === 'shop') ? 'shop' : ($pid > 0 ? 'product' : 'shop');

$product_name = "ไม่พบข้อมูลสินค้า";
$product_exists = false;

// เช็คชื่อสินค้าจากรายการ Array ด้านบน
if ($pid > 0 && isset($all_products_list[$pid])) {
    $product_name = $all_products_list[$pid];
    $product_exists = true;
}

// --- 3. ระบบบันทึกรีวิว ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('กรุณาเข้าสู่ระบบก่อนรีวิวค่ะ'); window.location='login.php';</script>"; exit;
    }

    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    $form_pid = (int)$_POST['product_id'];
    $form_type = $_POST['view_type'];

    try {
        if ($form_type === 'product' && $form_pid > 0) {
            // บันทึกรีวิวสินค้า ( product_id มีค่า)
            $sql = "INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$form_pid, $user_id, $rating, $comment]);
        } else {
            // บันทึกรีวิวร้านค้า ( product_id เป็น NULL)
            $sql = "INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (NULL, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $rating, $comment]);
        }
        echo "<script>alert('ขอบคุณสำหรับรีวิวค่ะ!'); window.location='reviews.php?product_id=$form_pid&type=$form_type';</script>"; exit;
    } catch (PDOException $e) {
        echo "<script>alert('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// --- 4. ดึงข้อมูลรีวิวมาแสดง ---
if ($view_type === 'shop') {
    $sql = "SELECT r.*, u.username AS display_name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.product_id IS NULL ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($sql); $stmt->execute();
} else {
    $sql = "SELECT r.*, u.username AS display_name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($sql); $stmt->execute([$pid]);
}
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container" style="max-width: 800px; margin: 40px auto; font-family: 'Kanit', sans-serif;">
    <div style="text-align: center; margin-bottom: 30px;">
        <a href="reviews.php?product_id=<?=$pid?>&type=product" 
           style="padding: 12px 25px; text-decoration: none; border-radius: 50px; display:inline-block; margin: 5px; transition: 0.3s; <?=($view_type=='product'?'background:#3498db;color:#fff;':'background:#eee;color:#888;')?>">📦 รีวิวสินค้า</a>
        <a href="reviews.php?product_id=<?=$pid?>&type=shop" 
           style="padding: 12px 25px; text-decoration: none; border-radius: 50px; display:inline-block; margin: 5px; transition: 0.3s; <?=($view_type=='shop'?'background:#e67e22;color:#fff;':'background:#eee;color:#888;')?>">🏠 รีวิวร้านค้า</a>
    </div>

    <div style="background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-top: 5px solid <?=($view_type=='shop'?'#e67e22':'#3498db')?>;">
        <h2 style="text-align:center;">
            <?= ($view_type === 'shop') ? '🏬 รีวิวร้านค้า' : '📦 รีวิวสินค้า: '.htmlspecialchars($product_name) ?>
        </h2>

        <?php if ($view_type === 'product' && !$product_exists): ?>
            <div style="text-align:center; padding:20px;">
                <p style="color:red;">⚠️ ไม่พบรหัสสินค้าที่คุณเลือก</p>
                <a href="products.php" style="color:#3498db;">กลับไปเลือกสินค้าหน้าเว็บบอร์ด</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="product_id" value="<?=$pid?>">
                <input type="hidden" name="view_type" value="<?=$view_type?>">
                <select name="rating" style="width:100%; padding:12px; margin-bottom:20px; border-radius:10px; border:1px solid #ddd;">
                    <option value="5">⭐⭐⭐⭐⭐ ยอดเยี่ยม</option>
                    <option value="4">⭐⭐⭐⭐ ดีมาก</option>
                    <option value="3">⭐⭐⭐ ปานกลาง</option>
                    <option value="2">⭐⭐ พอใช้</option>
                    <option value="1">⭐ ปรับปรุง</option>
                </select>
                <textarea name="comment" required placeholder="เขียนความรู้สึกของคุณที่นี่..." style="width:100%; height:120px; padding:15px; border-radius:10px; border:1px solid #ddd; margin-bottom:20px; font-family:'Kanit'; box-sizing:border-box;"></textarea>
                <button type="submit" name="submit_review" style="width:100%; padding:15px; background:#27ae60; color:#fff; border:none; border-radius:10px; font-weight:bold; cursor:pointer;">ส่งรีวิว</button>
            </form>
        <?php endif; ?>
    </div>

    <div style="margin-top: 40px;">
        <h3 style="text-align:center;">💬 รีวิวทั้งหมด (<?=count($result)?>)</h3>
        <?php foreach ($result as $r): ?>
            <div style="background:#fff; padding:20px; margin-bottom:15px; border-radius:15px; border-left: 5px solid <?=($view_type=='shop'?'#e67e22':'#3498db')?>; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <div style="display:flex; justify-content:space-between;">
                    <strong>👤 <?= htmlspecialchars($r['display_name']) ?></strong>
                    <small style="color:#aaa;"><?= $r['created_at'] ?></small>
                </div>
                <div style="color:#f1c40f; margin:5px 0;"><?= str_repeat("⭐", $r['rating']) ?></div>
                <p style="margin:0;"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
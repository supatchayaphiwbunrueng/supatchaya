<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/config.php';
include 'includes/header.php';

// ฐานข้อมูลสินค้าจำลอง (ต้องตรงกับ products.php)
$all_products = [
    ["id" => 1, "name" => "เสื้อยืดแขนยาวสีขาว", "price" => 150],
    ["id" => 2, "name" => "เสื้อยืดแขนยาวดำ", "price" => 150],
    ["id" => 3, "name" => "เสื้อยืดแขนยาวสีน้ำตาล", "price" => 150],
    ["id" => 4, "name" => "เสื้อคาร์ดิแกนสีขาว", "price" => 225],
    ["id" => 5, "name" => "เสื้อคาร์ดิแกนสีเทา", "price" => 225],
    ["id" => 6, "name" => "เสื้อคาร์ดิแกนสีแดง", "price" => 225],
    ["id" => 7, "name" => "เสื้อสเวตเตอร์สีขาว", "price" => 220],
    ["id" => 8, "name" => "เสื้อสเวตเตอร์สีดำ", "price" => 220],
    ["id" => 9, "name" => "เสื้อสเวตเตอร์สีชมพู", "price" => 220],
    ["id" => 10, "name" => "เสื้อฮู้ดสีขาว", "price" => 160],
    ["id" => 11, "name" => "เสื้อฮู้ดสีดำ", "price" => 160],
    ["id" => 12, "name" => "เสื้อฮู้ดสีฟ้า", "price" => 160],
    ["id" => 13, "name" => "แจ็คเก็ตยีนส์สีขาว", "price" => 549],
    ["id" => 14, "name" => "แจ็คเก็ตยีนส์สีดำ", "price" => 549],
    ["id" => 15, "name" => "แจ็คเก็ตยีนส์สีฟ้าอ่อน", "price" => 549],
    ["id" => 16, "name" => "เสื้อไหมพรมสีขาว", "price" => 189],
    ["id" => 17, "name" => "เสื้อไหมพรมสีดำ", "price" => 189],
    ["id" => 18, "name" => "เสื้อไหมพรมสีชมพู", "price" => 189],
    ["id" => 19, "name" => "เสื้อกันลมสีชมพูอ่อน", "price" => 285],
    ["id" => 20, "name" => "เสื้อกันลมสีน้ำตาลแดง", "price" => 285],
    ["id" => 21, "name" => "เสื้อกันลมสีดำ", "price" => 285]
];

// Logic เพิ่มสินค้า
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $pid = (int)$_GET['product_id'];
    $found_item = null;
    foreach ($all_products as $p) { if ($p['id'] === $pid) { $found_item = $p; break; } }
    if ($found_item) {
        if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }
        $_SESSION['cart'][] = ["id" => $found_item['id'], "name" => $found_item['name'], "price" => $found_item['price'], "size" => "L"];
    }
    header("Location: cart.php"); exit();
}

// Logic อัปเดต Size
if (isset($_POST['update_size'])) {
    $index = (int)$_POST['item_index'];
    if (isset($_SESSION['cart'][$index])) { $_SESSION['cart'][$index]['size'] = $_POST['new_size']; }
    header("Location: cart.php"); exit();
}

// Logic ลบสินค้า
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    unset($_SESSION['cart'][(int)$_GET['id']]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php"); exit();
}
?>

<style>
    .cart-container { max-width: 900px; margin: 40px auto; padding: 25px; font-family: 'Kanit', sans-serif; background: #fff; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    .cart-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
    .cart-table th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; }
    .cart-table td { padding: 15px; border-bottom: 1px solid #eee; }
    .size-btn { padding: 5px 10px; border: 1px solid #cbd5e1; background: #fff; border-radius: 5px; cursor: pointer; font-size: 12px; }
    .size-btn.active { background: #3498db; color: white; border-color: #3498db; }
    .cart-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #f8f9fa; }
    .btn-checkout { background: #27ae60; color: white; padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 18px; transition: 0.3s; }
    .btn-checkout:hover { background: #219150; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(39,174,96,0.3); }
</style>

<div class="cart-container">
    <h2>🛒 ตะกร้าสินค้าของคุณ</h2>
    
    <?php if (!empty($_SESSION['cart'])): ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>สินค้า & ไซส์</th>
                    <th style="text-align: right;">ราคา</th>
                    <th style="text-align: center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php $grand_total = 0; foreach ($_SESSION['cart'] as $index => $item): $grand_total += $item['price']; ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($item['name']) ?></strong> (<?= $item['size'] ?>)<br>
                        <form action="cart.php" method="POST" style="display:flex; gap:5px; margin-top:5px;">
                            <input type="hidden" name="item_index" value="<?= $index ?>">
                            <input type="hidden" name="update_size" value="1">
                            <?php foreach (['S', 'M', 'L', 'XL', 'XXL'] as $s): ?>
                                <button type="submit" name="new_size" value="<?= $s ?>" class="size-btn <?= ($item['size'] == $s) ? 'active' : '' ?>"><?= $s ?></button>
                            <?php endforeach; ?>
                        </form>
                    </td>
                    <td style="text-align: right;"><?= number_format($item['price'], 2) ?> ฿</td>
                    <td style="text-align: center;"><a href="cart.php?action=remove&id=<?= $index ?>" style="color:red; text-decoration:none;">ลบออก</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-footer">
            <div>
                <a href="products.php" style="color: #666; text-decoration: none;">← เลือกซื้อสินค้าเพิ่ม</a>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0; color: #666;">ยอดรวมสุทธิ</p>
                <h3 style="margin: 0 0 15px 0; font-size: 32px; color: #e74c3c;"><?= number_format($grand_total, 2) ?> ฿</h3>
                <a href="payment.php" class="btn-checkout">ไปหน้าชำระเงิน →</a>
            </div>
        </div>

    <?php else: ?>
        <div style="text-align:center; padding: 50px 0;">
            <p>ตะกร้าว่างเปล่า</p>
            <a href="products.php">กลับไปเลือกซื้อสินค้า</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
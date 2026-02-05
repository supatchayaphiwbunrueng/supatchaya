<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/config.php';
include 'includes/header.php';

/* 1. รับค่าหมวดหมู่จาก URL */
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : "";

/* 2. รายชื่อหมวดหมู่สินค้า */
$categories = ["เสื้อยืดแขนยาว", "เสื้อคาร์ดิแกน", "เสื้อสเวตเตอร์", "เสื้อฮู้ด", "แจ็คเก็ตยีนส์", "เสื้อไหมพรม", "เสื้อกันลม"];

/* 3. ฐานข้อมูลสินค้าจำลอง */
$all_products = [
    ["id" => 1, "name" => "เสื้อยืดแขนยาวสีขาว", "price" => 150, "cat" => "เสื้อยืดแขนยาว", "img" => "uploads/เสื้อยืดแขนยาวสีขาว.jpg", "stock" => 50],
    ["id" => 2, "name" => "เสื้อยืดแขนยาวดำ", "price" => 150, "cat" => "เสื้อยืดแขนยาว", "img" => "uploads/เสื้อยืดแขนยาวดำ.jpg", "stock" => 50],
    ["id" => 3, "name" => "เสื้อยืดแขนยาวสีน้ำตาล", "price" => 150, "cat" => "เสื้อยืดแขนยาว", "img" => "uploads/เสื้อยืดแขนยาวสีน้ำตาล.jpg", "stock" => 50],
    ["id" => 4, "name" => "เสื้อคาร์ดิแกนสีขาว", "price" => 225, "cat" => "เสื้อคาร์ดิแกน", "img" => "uploads/เสื้อคาร์ดิแกนสีขาว.jpg", "stock" => 40],
    ["id" => 5, "name" => "เสื้อคาร์ดิแกนสีเทา", "price" => 225, "cat" => "เสื้อคาร์ดิแกน", "img" => "uploads/เสื้อคาร์ดิแกนสีเทา.jpg", "stock" => 40],
    ["id" => 6, "name" => "เสื้อคาร์ดิแกนสีแดง", "price" => 225, "cat" => "เสื้อคาร์ดิแกน", "img" => "uploads/เสื้อคาร์ดิแกนสีแดง.jpg", "stock" => 40],
    ["id" => 7, "name" => "เสื้อสเวตเตอร์สีขาว", "price" => 220, "cat" => "เสื้อสเวตเตอร์", "img" => "uploads/เสื้อสเวตเตอร์สีขาว.jpg", "stock" => 30],
    ["id" => 8, "name" => "เสื้อสเวตเตอร์สีดำ", "price" => 220, "cat" => "เสื้อสเวตเตอร์", "img" => "uploads/เสื้อสเวตเตอร์สีดำ.jpg", "stock" => 30],
    ["id" => 9, "name" => "เสื้อสเวตเตอร์สีชมพู", "price" => 220, "cat" => "เสื้อสเวตเตอร์", "img" => "uploads/เสื้อสเวตเตอร์สีชมพู.jpg", "stock" => 30],
    ["id" => 10, "name" => "เสื้อฮู้ดสีขาว", "price" => 160, "cat" => "เสื้อฮู้ด", "img" => "uploads/เสื้อฮู้ดสีขาว.jpg", "stock" => 25],
    ["id" => 11, "name" => "เสื้อฮู้ดสีดำ", "price" => 160, "cat" => "เสื้อฮู้ด", "img" => "uploads/เสื้อฮู้ดสีดำ.jpg", "stock" => 25],
    ["id" => 12, "name" => "เสื้อฮู้ดสีฟ้า", "price" => 160, "cat" => "เสื้อฮู้ด", "img" => "uploads/เสื้อฮู้ดสีฟ้า.jpg", "stock" => 25],
    ["id" => 13, "name" => "แจ็คเก็ตยีนส์สีขาว", "price" => 549, "cat" => "แจ็คเก็ตยีนส์", "img" => "uploads/แจ็คเก็ตยีนส์สีขาว.jpg", "stock" => 15],
    ["id" => 14, "name" => "แจ็คเก็ตยีนส์สีดำ", "price" => 549, "cat" => "แจ็คเก็ตยีนส์", "img" => "uploads/แจ็คเก็ตยีนส์สีดำ.jpg", "stock" => 15],
    ["id" => 15, "name" => "แจ็คเก็ตยีนส์สีฟ้าอ่อน", "price" => 549, "cat" => "แจ็คเก็ตยีนส์", "img" => "uploads/แจ็คเก็ตยีนส์สีฟ้าอ่อน.jpg", "stock" => 15],
    ["id" => 16, "name" => "เสื้อไหมพรมสีขาว", "price" => 189, "cat" => "เสื้อไหมพรม", "img" => "uploads/เสื้อไหมพรมสีขาว.jpg", "stock" => 20],
    ["id" => 17, "name" => "เสื้อไหมพรมสีดำ", "price" => 189, "cat" => "เสื้อไหมพรม", "img" => "uploads/เสื้อไหมพรมสีดำ.jpg", "stock" => 20],
    ["id" => 18, "name" => "เสื้อไหมพรมสีชมพู", "price" => 189, "cat" => "เสื้อไหมพรม", "img" => "uploads/เสื้อไหมพรมสีชมพู.jpg", "stock" => 20],
    ["id" => 19, "name" => "เสื้อกันลมสีชมพูอ่อน", "price" => 285, "cat" => "เสื้อกันลม", "img" => "uploads/เสื้อกันลมสีชมพูอ่อน.jpg", "stock" => 10],
    ["id" => 20, "name" => "เสื้อกันลมสีน้ำตาลแดง", "price" => 285, "cat" => "เสื้อกันลม", "img" => "uploads/เสื้อกันลมสีน้ำตาลแดง.jpg", "stock" => 10],
    ["id" => 21, "name" => "เสื้อกันลมสีดำ", "price" => 285, "cat" => "เสื้อกันลม", "img" => "uploads/เสื้อกันลมสีดำ.jpg", "stock" => 10]
];

/* 4. ตรรกะการกรองสินค้า */
$display_items = [];
foreach ($all_products as $p) {
    if ($category_filter == "" || $p['cat'] == $category_filter) {
        $display_items[] = $p;
    }
}
?>

<style>
.products-container { max-width: 1200px; margin: 40px auto; padding: 0 15px; font-family: 'Kanit', sans-serif; }
.products-container h2 { text-align: center; color: #2e86c1; margin-bottom: 25px; }
.category-menu { text-align: center; margin-bottom: 30px; }
.category-menu a { display: inline-block; margin: 6px; padding: 8px 20px; border-radius: 25px; text-decoration: none; color: #2e86c1; font-weight: bold; border: 1px solid #2e86c1; transition: 0.3s; }
.category-menu a:hover, .category-menu a.active { background: #2e86c1; color: white; }
.products-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 25px; }
.card { background: white; border-radius: 15px; padding: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: 0.3s; display: flex; flex-direction: column; width: 280px; }
.card:hover { transform: translateY(-8px); }
.card img { width: 100%; height: 230px; object-fit: cover; border-radius: 10px; background: #f9f9f9; }
.card h4 { margin: 15px 0 5px; font-size: 17px; color: #333; height: 40px; overflow: hidden; }
.price { color: #e74c3c; font-weight: bold; font-size: 19px; margin-bottom: 15px; }
.button-group { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: auto; }
.btn-detail, .btn-buy { padding: 10px; border-radius: 8px; cursor: pointer; font-weight: bold; text-decoration: none; font-size: 13px; text-align: center; transition: 0.3s; }
.btn-detail { background: #f1f1f1; color: #333; border: 1px solid #ddd; }
.btn-buy { background: #2e86c1; color: white; border: none; }
</style>

<div class="products-container">
    <h2>🧥 <?= empty($category_filter) ? "สินค้าทั้งหมด" : htmlspecialchars($category_filter) ?></h2>

    <div class="category-menu">
        <a href="products.php" class="<?= empty($category_filter) ? 'active' : '' ?>">ทั้งหมด</a>
        <?php foreach($categories as $cat): ?>
            <a href="products.php?category=<?= urlencode($cat); ?>" class="<?= ($category_filter === $cat) ? 'active' : '' ?>">
                <?= $cat; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="products-grid">
        <?php foreach($display_items as $item): ?>
            <div class="card">
                <img src="<?= $item['img']; ?>" onerror="this.src='https://via.placeholder.com/250x230?text=No+Image'" alt="<?= htmlspecialchars($item['name']); ?>">
                <h4><?= htmlspecialchars($item['name']); ?></h4>
                <p class="price"><?= number_format($item['price']); ?> บาท</p>
                
                <div class="button-group">
                    <a href="product_detail.php?product_id=<?= $item['id']; ?>" class="btn-detail">รายละเอียด</a>
                    <a href="cart.php?action=add&product_id=<?= $item['id']; ?>" class="btn-buy">🛒 สั่งซื้อ</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'includes/config.php';
include 'includes/header.php';

// ดึงรีวิวที่เป็นของร้านค้าเท่านั้น (product_id เป็น NULL)
$sql = "SELECT r.*, u.username AS display_name FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.product_id IS NULL 
        ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container" style="max-width: 800px; margin: 40px auto; font-family: 'Kanit', sans-serif;">
    <div style="text-align:center; margin-bottom:40px;">
        <h1 style="color:#e67e22;">🏬 รีวิวจากลูกค้าของเรา</h1>
        <p style="color:#777;">ความประทับใจจากการใช้บริการร้าน Winter Cool Shirt</p>
        <a href="reviews.php?type=shop" style="display:inline-block; background:#e67e22; color:#white; padding:10px 25px; text-decoration:none; border-radius:50px; font-weight:bold;">✍️ เขียนรีวิวให้ร้านค้า</a>
    </div>

    <div class="review-list">
        <?php if(count($reviews) > 0): ?>
            <?php foreach ($reviews as $rev): ?>
                <div style="background:#fff; padding:25px; border-radius:20px; margin-bottom:20px; box-shadow:0 10px 20px rgba(0,0,0,0.03); border:1px solid #f0f0f0;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <strong>👤 <?= htmlspecialchars($rev['display_name']) ?></strong>
                        <span style="color:#bbb; font-size:13px;"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                    </div>
                    <div style="color:#f1c40f; margin:10px 0; font-size:18px;"><?= str_repeat("⭐", $rev['rating']) ?></div>
                    <p style="color:#555; line-height:1.6;"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding:50px; color:#bbb;">ยังไม่มีรีวิวร้านค้าในขณะนี้</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
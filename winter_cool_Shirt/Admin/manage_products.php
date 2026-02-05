<?php
include '../includes/config.php'; 
session_start();

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$message_type = "info";

// 7 หมวดหมู่สินค้า
$categories = ["เสื้อยืดแขนยาว", "เสื้อคาร์ดิแกน", "เสื้อสเวตเตอร์", "เสื้อฮู้ด", "แจ็คเก็ตยีนส์", "เสื้อไหมพรม", "เสื้อกันลม"];

// --- 1. ส่วนของการเพิ่มสินค้า (Create) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_product"])) {
    $name = trim($_POST["product_name"]);
    $price = floatval($_POST["price"]);
    $category = $_POST["category"]; 
    $description = trim($_POST["description"]);
    $stock = intval($_POST["stock"]); 
    $is_featured = isset($_POST["is_featured"]) ? 1 : 0;
    $image_name = "";

    if (!empty($_FILES["image"]["name"])) {
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $image_name);
    }

    try {
        $sql = "INSERT INTO products (name, description, price, stock, image, category, is_featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$name, $description, $price, $stock, $image_name, $category, $is_featured])) {
            $message = "เพิ่มสินค้าสำเร็จ ✨";
            $message_type = "success";
        }
    } catch (PDOException $e) {
        $message = "ข้อผิดพลาด: " . $e->getMessage();
        $message_type = "error";
    }
}

// --- 2. ส่วนของการลบสินค้า (Delete) ---
if (isset($_GET["delete"])) {
    $product_id = intval($_GET["delete"]);
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        if ($stmt->execute([$product_id])) {
            $message = "ลบสินค้าเรียบร้อยแล้ว";
            $message_type = "success";
        }
    } catch (PDOException $e) {
        $message = "ลบไม่ได้: " . $e->getMessage();
        $message_type = "error";
    }
}

// --- 3. ส่วนการดึงข้อมูลมาโชว์ (Read) ---
$stmt = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า - Winter Cool</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #5dade2; --light: #ebf5fb; --text: #566573; }
        body { font-family: 'Kanit', sans-serif; background: #f4fbff; margin: 0; color: var(--text); }
        
        header { 
            background: white; 
            padding: 15px 5%; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }

        .back-home {
            text-decoration: none;
            background: #ebf5ff;
            color: var(--blue);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            border: 1px solid #d1e9ff;
        }
        .back-home:hover {
            background: var(--blue);
            color: white;
            box-shadow: 0 4px 10px rgba(93, 173, 226, 0.3);
        }

        .container { width: 90%; max-width: 1100px; margin: 30px auto; }
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        
        /* ✅ สไตล์สำหรับช่องกรอกราคาที่มีคำว่า "บาท" */
        .price-input-container {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .price-input-container input {
            margin-bottom: 0;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            flex: 1;
        }
        .currency-label {
            background: #f0f4f7;
            border: 1px solid #ddd;
            border-left: none;
            padding: 10px 15px;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            color: #7f8c8d;
            font-size: 14px;
            height: 42px; /* ให้สูงเท่ากับ input */
            display: flex;
            align-items: center;
            box-sizing: border-box;
        }

        input, select, textarea { width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box; }
        button { background: var(--blue); color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; cursor: pointer; font-size: 16px; margin-top: 10px; }
        
        table { width: 100%; background: white; border-radius: 15px; overflow: hidden; border-collapse: collapse; }
        th, td { padding: 15px; text-align: center; border-bottom: 1px solid #eee; }
        th { background: var(--light); color: var(--blue); }
        .badge { background: var(--light); color: var(--blue); padding: 4px 10px; border-radius: 5px; font-size: 12px; }
        .success { background: #d4edda; color: #155724; padding:15px; border-radius:10px; margin-bottom:20px; text-align:center; }
        .error { background: #f8d7da; color: #721c24; padding:15px; border-radius:10px; margin-bottom:20px; text-align:center; }
    </style>
</head>
<body>

<header>
    <h2 style="margin:0; color:var(--blue);">📦 ระบบจัดการสินค้า</h2>
    <a href="admin_index.php" class="back-home">
        <span>🏠</span> กลับหน้าหลัก
    </a>
</header>

<div class="container">
    <?php if ($message): ?>
        <div class="<?= $message_type == 'success' ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" enctype="multipart/form-data">
            <h3 style="margin-top:0;">✨ เพิ่มสินค้าใหม่</h3>
            
            <label>ชื่อสินค้า</label>
            <input type="text" name="product_name" placeholder="ระบุชื่อสินค้า..." required>
            
            <div style="display:flex; gap:10px;">
                <div style="flex:1;">
                    <label>หมวดหมู่</label>
                    <select name="category" required>
                        <option value="">-- เลือกหมวดหมู่ --</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat ?>"><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:1;">
                    <label>จำนวนสต็อก</label>
                    <input type="number" name="stock" placeholder="0" required>
                </div>
            </div>

            <label>ราคาขาย</label>
            <div class="price-input-container">
                <input type="number" name="price" step="0.01" placeholder="0.00" required>
                <span class="currency-label">บาท</span>
            </div>

            <label>รายละเอียดสินค้า</label>
            <textarea name="description" placeholder="รายละเอียดสินค้า..." rows="3"></textarea>
            
            <label style="display:block; margin-bottom:10px; cursor:pointer;">
                <input type="checkbox" name="is_featured" value="1"> สินค้าแนะนำ
            </label>
            
            <label>รูปภาพสินค้า</label>
            <input type="file" name="image">
            
            <button type="submit" name="add_product">🚀 บันทึกสินค้า</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>รูป</th>
                <th>ชื่อสินค้า</th>
                <th>หมวดหมู่</th>
                <th>ราคา</th>
                <th>สต็อก</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $row): ?>
            <tr>
                <td>
                    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" width="50" height="50" style="object-fit:cover; border-radius:5px;" onerror="this.src='https://via.placeholder.com/50'">
                </td>
                <td style="text-align:left;"><?= htmlspecialchars($row['name']) ?></td>
                <td><span class="badge"><?= htmlspecialchars($row['category'] ?? 'ทั่วไป') ?></span></td>
                
                <td style="font-weight:500;">
                    <?= number_format($row['price'], 2) ?> <span style="font-size:12px; color:#999;">บาท</span>
                </td>

                <td style="font-weight:bold; color:<?= $row['stock'] > 0 ? 'green' : 'red' ?>;">
                    <?= number_format($row['stock']) ?>
                </td>
                <td>
                    <a href="manage_products.php?delete=<?= $row['product_id'] ?>" onclick="return confirm('ลบสินค้านี้?')" style="color:red; text-decoration:none;">ลบ</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
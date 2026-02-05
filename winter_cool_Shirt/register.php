<?php
// 1. เริ่ม Session และเชื่อมต่อไฟล์ที่จำเป็น
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once 'includes/config.php'; 
include 'includes/header.php';

$message = ""; 

// 2. ตรวจสอบเมื่อมีการกดปุ่ม "สมัครสมาชิก"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($fullname) && !empty($email) && !empty($password)) {
        try {
            // ตรวจสอบก่อนว่าอีเมลนี้มีในระบบหรือยัง
            $check_email = "SELECT email FROM users WHERE email = :email";
            $stmt_check = $conn->prepare($check_email);
            $stmt_check->execute([':email' => $email]);

            if ($stmt_check->rowCount() > 0) {
                $message = "<div class='error-msg'>❌ อีเมลนี้ถูกใช้ไปแล้ว</div>";
            } else {
                // ✅ เข้ารหัสรหัสผ่าน
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // ✅ แก้ไข SQL: เปลี่ยนจาก 'name' เป็น 'username' (หรือชื่อคอลัมน์จริงใน DB ของพี่)
                // ผมใส่ Backticks ครอบชื่อคอลัมน์เพื่อป้องกัน Error กรณีชื่อไปซ้ำกับคำสงวนของ MySQL
                $sql = "INSERT INTO users (`username`, `email`, `password`, `role`) 
                        VALUES (:name, :email, :pass, 'user')";
                
                $stmt_insert = $conn->prepare($sql);
                $success = $stmt_insert->execute([
                    ':name'  => $fullname,
                    ':email' => $email,
                    ':pass'  => $hashed_password
                ]);
                
                if ($success) {
                    $message = "<div class='success-msg'>✅ สมัครสมาชิกสำเร็จ! <a href='login.php'>ไปหน้าเข้าสู่ระบบ</a></div>";
                }
            }
        } catch (PDOException $e) {
            // กรณีเกิด Error จะแจ้งรายละเอียดชัดเจน
            $message = "<div class='error-msg'>เกิดข้อผิดพลาดทางฐานข้อมูล: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $message = "<div class='error-msg'>กรุณากรอกข้อมูลให้ครบทุกช่อง</div>";
    }
}
?>

<style>
    .register-container {
        max-width: 450px; margin: 60px auto; padding: 35px;
        background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        font-family: 'Kanit', sans-serif;
    }
    .register-container h2 { text-align: center; color: #2e86c1; margin-bottom: 25px; font-weight: 600; }
    .register-container label { display: block; margin-bottom: 8px; font-size: 0.9rem; color: #555; }
    .register-container input {
        width: 100%; padding: 14px; margin-bottom: 20px;
        border: 1px solid #e1e8ed; border-radius: 12px; box-sizing: border-box;
        background: #f8fafd; font-size: 1rem; transition: 0.3s;
    }
    .register-container input:focus { border-color: #3498db; outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1); }
    .btn-register {
        width: 100%; padding: 14px; background: #2e86c1; color: white;
        border: none; border-radius: 12px; font-size: 1.1rem; font-weight: bold;
        cursor: pointer; transition: 0.3s;
    }
    .btn-register:hover { background: #21618c; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(46, 134, 193, 0.3); }
    .error-msg { color: #e74c3c; background: #fdf2f2; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; border: 1px solid #f9d6d6; }
    .success-msg { color: #27ae60; background: #eafaf1; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; border: 1px solid #d5f5e3; }
</style>

<div class="register-container">
    <h2>สมัครสมาชิกใหม่</h2>
    <?php echo $message; ?>
    
    <form method="post" action="">
        <label>ชื่อ-นามสกุล</label>
        <input type="text" name="fullname" placeholder="ตัวอย่าง: สมชาย ใจดี" required>
        
        <label>อีเมล</label>
        <input type="email" name="email" placeholder="example@mail.com" required>
        
        <label>รหัสผ่าน</label>
        <input type="password" name="password" placeholder="อย่างน้อย 6 ตัวอักษร" required>
        
        <button type="submit" class="btn-register">สร้างบัญชีผู้ใช้</button>
    </form>
    
    <p style="text-align: center; margin-top: 25px; color: #777; font-size: 0.95rem;">
        เป็นสมาชิกอยู่แล้ว? <a href="login.php" style="color: #2e86c1; text-decoration: none; font-weight: 600;">เข้าสู่ระบบที่นี่</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
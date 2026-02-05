<?php
session_start();
// 1. เชื่อมต่อฐานข้อมูล
include __DIR__ . "/../includes/config.php";

// 2. หากล็อคอินอยู่แล้ว ให้ไปหน้า Dashboard
if (isset($_SESSION["staff_id"])) {
    header("Location: staff_dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าและตัดช่องว่างที่อาจติดมาจากการพิมพ์
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            // 3. ดึงข้อมูลพนักงาน
            $stmt = $conn->prepare("SELECT * FROM staff WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $staff = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($staff) {
                // 4. ตรวจสอบรหัสผ่าน (ใช้ trim ครอบทั้งคู่เพื่อความแม่นยำสูงสุด)
                if (trim($password) === trim($staff['password'])) {
                    
                    $_SESSION["staff_id"] = $staff['id'];
                    $_SESSION["staff_name"] = $staff['name'];
                    
                    header("Location: staff_dashboard.php");
                    exit();
                } else {
                    $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
                }
            } else {
                $error = "ไม่พบชื่อผู้ใช้งานในระบบ";
            }
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    } else {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - ระบบจัดการพนักงาน</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #3498db; --dark-bg: #2c3e50; --text-muted: #7f8c8d; }
        body { font-family: 'Prompt', sans-serif; background-color: var(--dark-bg); height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }
        .login-container { background: white; padding: 45px 35px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); width: 100%; max-width: 380px; text-align: center; }
        .login-container h2 { margin: 10px 0 30px; color: var(--dark-bg); font-weight: 600; font-size: 24px; }
        .form-group { margin-bottom: 22px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 14px; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #bdc3c7; }
        .form-group input { width: 100%; padding: 14px 14px 14px 45px; border: 1px solid #e0e0e0; border-radius: 10px; box-sizing: border-box; font-size: 16px; transition: 0.3s; }
        .form-group input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 8px rgba(52, 152, 219, 0.2); }
        .btn-login { width: 100%; padding: 15px; background-color: var(--primary-color); color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-login:hover { background-color: #2980b9; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3); }
        .error-msg { background-color: #fff5f5; color: #e74c3c; padding: 12px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; border-left: 4px solid #e74c3c; text-align: center; }
        .back-home { margin-top: 25px; display: inline-block; color: var(--text-muted); text-decoration: none; font-size: 14px; }
        .back-home:hover { color: var(--primary-color); }
    </style>
</head>
<body>

<div class="login-container">
    <div style="background: #f0f3f5; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
        <i class="fa fa-user-shield" style="font-size: 40px; color: var(--primary-color);"></i>
    </div>
    <h2>Staff Login</h2>

    <?php if ($error): ?>
        <div class="error-msg">
            <i class="fa fa-circle-exclamation"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>ชื่อผู้ใช้งาน</label>
            <div class="input-wrapper">
                <i class="fa fa-user"></i>
                <input type="text" name="username" placeholder="Username" required autocomplete="off">
            </div>
        </div>
        <div class="form-group">
            <label>รหัสผ่าน</label>
            <div class="input-wrapper">
                <i class="fa fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
        </div>
        <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
    </form>
    <a href="../index.php" class="back-home"><i class="fa fa-arrow-left"></i> กลับไปหน้าหลักร้านค้า</a>
</div>

</body>
</html>
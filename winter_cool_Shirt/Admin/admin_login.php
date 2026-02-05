<?php
session_start();
include __DIR__ . "/../includes/config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM users WHERE username = :username AND (role = 'admin' OR role = 'staff') LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['admin_id']   = $user['user_id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['role']       = $user['role'];

            header("Location: admin_index.php");
            exit();
        } else {
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง หรือคุณไม่มีสิทธิ์เข้าถึง";
        }
    } catch (PDOException $e) {
        $error = "ข้อผิดพลาดระบบ: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin & Staff Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f0faff; font-family: 'Kanit', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { max-width: 400px; width: 90%; padding: 40px; background: white; border-radius: 20px; box-shadow: 0 10px 25px rgba(173, 216, 230, 0.4); border: 2px solid #e1f5fe; }
        .login-card h2 { color: #5dade2; text-align: center; margin-bottom: 30px; font-weight: 500; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; color: #7f8c8d; font-size: 14px; }
        .input-group input { width: 100%; padding: 12px; border: 2px solid #e1f5fe; border-radius: 12px; box-sizing: border-box; outline: none; transition: 0.3s; font-family: 'Kanit'; }
        .input-group input:focus { border-color: #aed6f1; box-shadow: 0 0 8px rgba(174, 214, 241, 0.5); }
        .btn-login { width: 100%; padding: 14px; background: #85c1e9; color: white; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 16px; font-family: 'Kanit'; margin-top: 10px; }
        .btn-login:hover { background: #5dade2; transform: translateY(-2px); }
        .btn-register-link { display: block; width: 100%; padding: 12px; background: #fff; color: #85c1e9; border: 2px solid #85c1e9; border-radius: 12px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 15px; font-family: 'Kanit'; text-align: center; text-decoration: none; margin-top: 15px; box-sizing: border-box; }
        .btn-register-link:hover { background: #f0faff; border-color: #5dade2; color: #5dade2; }
        .error-msg { color: #e74c3c; text-align: center; background: #fff5f5; padding: 10px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }
        .divider { text-align: center; margin: 20px 0; color: #bdc3c7; font-size: 14px; position: relative; }
        .divider::before { content: ""; position: absolute; left: 0; top: 50%; width: 40%; height: 1px; background: #eee; }
        .divider::after { content: ""; position: absolute; right: 0; top: 50%; width: 40%; height: 1px; background: #eee; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Admin & Staff</h2>
    
    <?php if(!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>ชื่อผู้ใช้งาน (Username)</label>
            <input type="text" name="username" placeholder="ระบุชื่อผู้ใช้งาน" required>
        </div>
        <div class="input-group">
            <label>รหัสผ่าน (Password)</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-login">เข้าสู่ระบบหลังบ้าน</button>
    </form>

    <div class="divider">หรือ</div>

    <a href="admin_register.php" class="btn-register-link">สมัครสมาชิกใหม่</a>

    <div style="text-align: center; margin-top: 25px;">
        <a href="../index.php" style="color: #bdc3c7; text-decoration: none; font-size: 13px;">← กลับสู่หน้าร้านค้า</a>
    </div>
</div>

</body>
</html>
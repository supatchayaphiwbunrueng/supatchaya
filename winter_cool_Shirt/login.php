<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once 'includes/config.php'; 
include 'includes/header.php';

$error = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password']; 

    if (!empty($email) && !empty($password)) {
        try {
            // ใช้ SELECT * เพื่อให้ดึงมาทุกคอลัมน์ (ทั้ง id, username, role)
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // ตรวจสอบรหัสผ่าน
                if (password_verify($password, $user['password']) || $password === $user['password']) {
                    
                    // ✅ 1. เก็บ ID ผู้ใช้ (รองรับทั้ง user_id หรือ id)
                    $_SESSION['user_id'] = $user['user_id'] ?? $user['id'];

                    // ✅ 2. เก็บชื่อผู้ใช้ (ให้ตรงกับหน้า Register ที่ใช้ username)
                    // ใช้ ?? เป็นระบบสำรอง ถ้าไม่มี username ให้ใช้อีเมลแทน จะได้ไม่ Error
                    $_SESSION['user_name'] = $user['username'] ?? $user['name'] ?? $user['email'];

                    // ✅ 3. เก็บสิทธิ์ผู้ใช้
                    $_SESSION['role'] = $user['role'] ?? 'user'; 

                    // 🔀 ส่งตัวไปตามหน้าที่เหมาะสม
                    if ($_SESSION['role'] === 'admin') {
                        header("Location: manage_orders.php"); 
                    } else {
                        header("Location: index.php"); 
                    }
                    exit();
                } else {
                    $error = "รหัสผ่านไม่ถูกต้อง";
                }
            } else {
                $error = "ไม่พบอีเมลนี้ในระบบ";
            }
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาดทางระบบ: " . $e->getMessage();
        }
    } else {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>

<style>
    :root { --primary-color: #3498db; --secondary-color: #2980b9; --bg-color: #f4f7f6; }
    body { background-color: var(--bg-color); font-family: 'Kanit', sans-serif; margin: 0; }
    .login-wrapper { display: flex; justify-content: center; align-items: center; min-height: 85vh; padding: 20px; }
    .login-box { width: 100%; max-width: 400px; background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    .login-box h2 { text-align: center; color: var(--secondary-color); margin-bottom: 30px; font-weight: 600; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; color: #555; font-size: 0.9rem; }
    .login-box input { width: 100%; padding: 12px; border: 1px solid #e1e8ed; border-radius: 10px; box-sizing: border-box; transition: 0.3s; background: #f8fafd; }
    .login-box input:focus { border-color: var(--primary-color); outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1); }
    .btn-login { width: 100%; padding: 14px; background: var(--primary-color); color: white; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 1rem; margin-top: 10px; }
    .btn-login:hover { background: var(--secondary-color); transform: translateY(-1px); box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3); }
    .error-alert { background: #fef2f2; color: #dc2626; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; border: 1px solid #fee2e2; font-size: 0.9rem; }
    .footer-links { text-align: center; margin-top: 25px; font-size: 0.9rem; color: #888; }
    .footer-links a { color: var(--primary-color); text-decoration: none; font-weight: 600; }
</style>

<div class="login-wrapper">
    <div class="login-box">
        <h2>เข้าสู่ระบบ</h2>

        <?php if ($error != ""): ?>
            <div class="error-alert">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>อีเมล</label>
                <input type="email" name="email" placeholder="กรอกอีเมลของคุณ" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
            </div>
            
            <div class="form-group">
                <label>รหัสผ่าน</label>
                <input type="password" name="password" placeholder="กรอกรหัสผ่าน" required>
            </div>

            <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
        </form>
        
        <div class="footer-links">
            ยังไม่มีบัญชีสมาชิก? <a href="register.php">สมัครสมาชิกที่นี่</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
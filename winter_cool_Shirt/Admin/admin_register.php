<?php
session_start();
include __DIR__ . "/../includes/config.php";

$message = "";
$message_type = "error";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // แก้ไขบรรทัดที่ 10-14: เพิ่มการเช็คค่าว่างป้องกัน Warning
    $username   = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $email      = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $password_raw = isset($_POST["password"]) ? $_POST["password"] : "";
    $full_name  = isset($_POST["full_name"]) ? trim($_POST["full_name"]) : "";
    
    // บรรทัดที่ 13 (จุดที่เกิดปัญหา): ใช้ ?? เพื่อบอกว่าถ้าไม่มีค่าให้เป็น 'staff' ไปก่อน
    $role       = $_POST["role"] ?? 'staff'; 

    if (!empty($username) && !empty($password_raw)) {
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        try {
            // 1. ตรวจสอบชื่อผู้ใช้หรืออีเมลซ้ำ
            $check_sql = "SELECT user_id FROM users WHERE username = :username OR email = :email";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([':username' => $username, ':email' => $email]);

            if ($check_stmt->rowCount() > 0) {
                $message = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้แล้ว";
            } else {
                // 2. เพิ่มข้อมูล (ใช้ชื่อคอลัมน์ตาม SQL ที่เราคุยกันไว้)
                $insert_sql = "INSERT INTO users (username, email, password, full_name, role) 
                               VALUES (:username, :email, :password, :full_name, :role)";
                $stmt = $conn->prepare($insert_sql);

                $result = $stmt->execute([
                    ':username'  => $username,
                    ':email'     => $email,
                    ':password'  => $password,
                    ':full_name' => $full_name,
                    ':role'      => $role
                ]);

                if ($result) {
                    $message = "สมัครสมาชิกสำเร็จ! <a href='admin_login.php' style='color: #2ecc71;'>เข้าสู่ระบบได้ที่นี่</a>";
                    $message_type = "success";
                }
            }
        } catch (PDOException $e) {
            $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    } else {
        $message = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก Admin & Staff</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background: #f0faff; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(173, 216, 230, 0.3); width: 400px; border: 2px solid #e1f5fe; }
        h2 { text-align: center; color: #5dade2; margin-bottom: 25px; }
        label { font-size: 14px; color: #7f8c8d; margin-left: 5px; }
        input, select { width: 100%; padding: 12px; margin: 8px 0 18px 0; border: 1px solid #e1f5fe; border-radius: 10px; box-sizing: border-box; outline: none; transition: 0.3s; }
        input:focus, select:focus { border-color: #85c1e9; box-shadow: 0 0 8px rgba(133, 193, 233, 0.3); }
        button { width: 100%; padding: 14px; background: #85c1e9; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: bold; font-size: 16px; transition: 0.3s; }
        button:hover { background: #5dade2; transform: translateY(-2px); }
        .msg { text-align: center; padding: 10px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }
        .error { background: #fff5f5; color: #e74c3c; }
        .success { background: #f0fff4; color: #2ecc71; }
    </style>
</head>
<body>
    <div class="card">
        <h2>สมัครสมาชิกหลังบ้าน</h2>
        
        <?php if($message): ?>
            <div class="msg <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>ชื่อผู้ใช้ (Username)</label>
            <input type="text" name="username" placeholder="ภาษาอังกฤษหรือตัวเลข" required>

            <label>อีเมล (Email)</label>
            <input type="email" name="email" placeholder="example@mail.com" required>

            <label>รหัสผ่าน (Password)</label>
            <input type="password" name="password" placeholder="ตั้งรหัสผ่าน 6 ตัวขึ้นไป" required>

            <label>ชื่อเล่น</label>
            <input type="text" name="full_name" placeholder="ชื่อจริงของคุณ" required>

            <label>ระดับสิทธิ์ (Role)</label>
            <select name="role" required>
                <option value="staff">พนักงาน (Staff)</option>
                <option value="admin">ผู้ดูแลระบบ (Admin)</option>
            </select>

            <button type="submit">ยืนยันการสมัคร</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="admin_login.php" style="color: #bdc3c7; text-decoration: none; font-size: 13px;">มีบัญชีอยู่แล้ว? เข้าสู่ระบบ</a>
        </div>
    </div>
</body>
</html>
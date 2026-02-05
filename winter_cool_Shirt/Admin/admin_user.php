<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include __DIR__ . "/../includes/config.php";

// ✅ 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('เฉพาะแอดมินเท่านั้นที่เข้าหน้านี้ได้'); window.location='admin_login.php';</script>";
    exit();
}

// ✅ 2. Logic การลบสมาชิก
if (isset($_GET['delete_id'])) {
    $uid = (int)$_GET['delete_id'];
    $current_admin_id = $_SESSION['user_id'] ?? 0;
    if ($uid === $current_admin_id) {
        echo "<script>alert('คุณไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้!'); window.location='admin_user.php';</script>";
        exit();
    }
    try {
        $conn->beginTransaction();
        $conn->prepare("DELETE FROM order_items WHERE order_id IN (SELECT order_id FROM orders WHERE user_id = ?)")->execute([$uid]);
        $conn->prepare("DELETE FROM orders WHERE user_id = ?")->execute([$uid]);
        $conn->prepare("DELETE FROM reviews WHERE user_id = ?")->execute([$uid]);
        $conn->prepare("DELETE FROM users WHERE user_id = ?")->execute([$uid]);
        $conn->commit();
        header("Location: admin_user.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "<script>alert('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "'); window.location='admin_user.php';</script>";
        exit();
    }
}

// ✅ 3. Logic เปลี่ยนสิทธิ์
if (isset($_POST['update_role'])) {
    $uid = (int)$_POST['user_id'];
    $new_role = $_POST['new_role'];
    $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?")->execute([$new_role, $uid]);
    header("Location: admin_user.php?msg=updated");
    exit();
}

$users = $conn->query("SELECT * FROM users ORDER BY role ASC, username ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสมาชิก - Elite Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-start: #f0f9ff; --bg-end: #cbebff;
            --primary-blue: #3498db; --dark-blue: #2c3e50;
            --success: #2ecc71; --danger: #e74c3c; --warning: #f39c12;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, var(--bg-start) 0%, var(--bg-end) 100%);
            margin: 0; min-height: 100vh; color: var(--dark-blue);
        }

        /* Top Navbar */
        .top-nav {
            background: white; padding: 12px 5%;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .top-nav h1 { margin: 0; font-size: 20px; color: var(--primary-blue); display: flex; align-items: center; gap: 8px; }
        .back-home {
            text-decoration: none; background: #ebf5ff; color: var(--primary-blue);
            padding: 8px 18px; border-radius: 50px; font-size: 14px; font-weight: 500;
            transition: 0.3s; border: 1px solid #d1e9ff;
        }
        .back-home:hover { background: var(--primary-blue); color: white; }

        .container { width: 90%; max-width: 1100px; margin: 40px auto; }

        /* Main Content Card */
        .admin-card {
            background: white; padding: 35px; border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.02);
        }

        /* Table Styles */
        .table-responsive { overflow-x: auto; margin-top: 25px; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 15px; text-align: left; border-bottom: 2px solid #f8fafc; color: #94a3b8; font-size: 13px; }
        td { padding: 20px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }

        .role-badge { padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .role-admin { background: #fee2e2; color: #ef4444; }
        .role-staff { background: #fef9c3; color: #a16207; }
        .role-user { background: #dcfce7; color: #15803d; }

        select { padding: 6px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: 'Kanit'; }
        .btn-save { background: var(--primary-blue); color: white; border: none; padding: 6px 10px; border-radius: 8px; cursor: pointer; }
        .btn-del { color: var(--danger); text-decoration: none; font-weight: 500; border: 1px solid #fee2e2; padding: 5px 12px; border-radius: 8px; transition: 0.2s; }
        .btn-del:hover { background: #fee2e2; }

        .alert { padding: 15px; background: #dcfce7; color: #15803d; border-radius: 12px; margin-bottom: 20px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>

<div class="top-nav">
    <a href="admin_index.php" style="text-decoration:none;"><h1>📦 ระบบจัดการร้านค้า</h1></a>
    <a href="admin_index.php" class="back-home">🏠 กลับหน้าหลัก</a>
</div>

<div class="container">
    <div class="admin-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 10px;">
            <div>
                <h2 style="margin:0; color: var(--dark-blue);">👥 รายชื่อสมาชิกทั้งหมด</h2>
                <p style="margin:5px 0 0; font-size: 13px; color: #94a3b8;">คุณสามารถปรับเปลี่ยนสิทธิ์หรือลบบัญชีผู้ใช้ได้ที่นี่</p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 24px; font-weight: 600; color: var(--primary-blue);"><?= count($users) ?></span>
                <span style="font-size: 12px; color: #94a3b8; display: block;">บัญชีในระบบ</span>
            </div>
        </div>

        <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 20px 0;">

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert">✨ ดำเนินการสำเร็จเรียบร้อยแล้ว</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>สมาชิก</th>
                        <th>สถานะปัจจุบัน</th>
                        <th>เปลี่ยนสิทธิ์</th>
                        <th style="text-align:center;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div style="display:flex; flex-direction:column;">
                                <span style="font-weight:600; color: var(--dark-blue);"><?= htmlspecialchars($u['username']) ?></span>
                                <span style="font-size:12px; color:#94a3b8;"><?= htmlspecialchars($u['email']) ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge role-<?= $u['role'] ?>"><?= strtoupper($u['role']) ?></span>
                        </td>
                        <td>
                            <form method="POST" style="display:flex; gap:5px; align-items:center;">
                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                <select name="new_role">
                                    <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
                                    <option value="staff" <?= $u['role']=='staff'?'selected':'' ?>>Staff</option>
                                    <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                </select>
                                <button type="submit" name="update_role" class="btn-save">บันทึก</button>
                            </form>
                        </td>
                        <td style="text-align:center;">
                            <?php if($u['user_id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                <a href="admin_user.php?delete_id=<?= $u['user_id'] ?>" class="btn-del" onclick="return confirm('❗ คำเตือน: ข้อมูลคำสั่งซื้อและรีวิวของสมาชิกรายนี้จะถูกลบถาวร ยืนยันหรือไม่?')">ลบสมาชิก</a>
                            <?php else: ?>
                                <span style="font-size:12px; color:#cbd5e1; font-style:italic;">บัญชีของคุณ</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
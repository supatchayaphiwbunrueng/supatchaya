<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


function allowRoles($roles = []) {
    if (!in_array($_SESSION['role'], $roles)) {
        die("คุณไม่มีสิทธิ์เข้าถึงหน้านี้");
    }
}
?>

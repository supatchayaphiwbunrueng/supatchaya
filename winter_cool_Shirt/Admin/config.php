<?php
$servername = "localhost";   // ชื่อโฮสต์ เช่น localhost
$username = "root";          // ชื่อผู้ใช้ฐานข้อมูล
$password = "";              // รหัสผ่านฐานข้อมูล
$dbname = "winter_cool_shirt";         // ชื่อฐานข้อมูลที่สร้างไว้

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// ตั้งค่าภาษาให้รองรับ UTF-8
$conn->set_charset("utf8");

// ถ้าเชื่อมต่อสำเร็จ
// echo "เชื่อมต่อฐานข้อมูลสำเร็จ";
?>
<?php
// config.example.php
$host = 'localhost';
$dbname = 'BoxerImgDb';
$username = 'root';
$password = 'root'; // Default for MAMP is 'root', XAMPP is ''

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
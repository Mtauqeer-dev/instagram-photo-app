<?php
$host = 'localhost';
$user = 'root';        // default XAMPP user
$password = '';        // default password is empty
$database = 'instagram_photo';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

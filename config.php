<?php
$host = "instamedia.mysql.database.azure.com";
$user = "mtau";        // default XAMPP user
$password = "@T@uqeer11@";        // default password is empty
$database = "instagram_photo";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'consumer') {
    $media_id = $_POST['media_id'];
    $comment = $_POST['comment'];
    $rating = $_POST['rating'];
    $user_id = $_SESSION['user_id'];

    // Insert comment
    $stmt = $conn->prepare("INSERT INTO comments (media_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $media_id, $user_id, $comment);
    $stmt->execute();

    // Insert rating
    $stmt2 = $conn->prepare("INSERT INTO ratings (media_id, user_id, rating) VALUES (?, ?, ?)");
    $stmt2->bind_param("iii", $media_id, $user_id, $rating);
    $stmt2->execute();

    header("Location: dashboard_consumer.php");
    exit();
} else {
    echo "Unauthorized access.";
}
?>

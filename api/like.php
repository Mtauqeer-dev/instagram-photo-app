<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$media_id = $_POST['media_id'] ?? 0;

if (!$media_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid media ID']);
    exit;
}

// Check if already liked
$stmt = $conn->prepare("SELECT id FROM likes WHERE media_id = ? AND user_id = ?");
$stmt->bind_param("ii", $media_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Unlike it
    $delete = $conn->prepare("DELETE FROM likes WHERE media_id = ? AND user_id = ?");
    $delete->bind_param("ii", $media_id, $user_id);
    $delete->execute();
    echo json_encode(['status' => 'unliked']);
} else {
    // Like it
    $insert = $conn->prepare("INSERT INTO likes (media_id, user_id) VALUES (?, ?)");
    $insert->bind_param("ii", $media_id, $user_id);
    $insert->execute();
    echo json_encode(['status' => 'liked']);
}
?>

<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

$media_id = $_GET['media_id'] ?? 0;

if (!$media_id) {
    echo json_encode(['count' => 0, 'liked' => false]);
    exit;
}

// Get total like count
$countStmt = $conn->prepare("SELECT COUNT(*) AS count FROM likes WHERE media_id = ?");
$countStmt->bind_param("i", $media_id);
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$count = $countResult['count'] ?? 0;

// Check if current user liked
$liked = false;
if (isset($_SESSION['user_id'])) {
    $checkStmt = $conn->prepare("SELECT id FROM likes WHERE media_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $media_id, $_SESSION['user_id']);
    $checkStmt->execute();
    $checkStmt->store_result();
    $liked = $checkStmt->num_rows > 0;
}

echo json_encode(['count' => $count, 'liked' => $liked]);

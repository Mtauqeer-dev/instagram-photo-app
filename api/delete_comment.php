<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$comment_id = $_POST['comment_id'] ?? 0;

if (!$comment_id) {
    echo json_encode(['status' => 'invalid']);
    exit;
}

// Check ownership
$stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['status' => 'not_found']);
    exit;
}

$stmt->bind_result($owner_id);
$stmt->fetch();

if ($owner_id != $_SESSION['user_id']) {
    echo json_encode(['status' => 'forbidden']);
    exit;
}

// Delete comment
$delete = $conn->prepare("DELETE FROM comments WHERE id = ?");
$delete->bind_param("i", $comment_id);
$delete->execute();

echo json_encode(['status' => 'deleted']);

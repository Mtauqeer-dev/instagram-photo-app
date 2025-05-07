<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$comment_id = $_POST['comment_id'] ?? 0;
$new_comment = trim($_POST['comment'] ?? '');

if (!$comment_id || $new_comment === '') {
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

// Update comment
$update = $conn->prepare("UPDATE comments SET comment = ? WHERE id = ?");
$update->bind_param("si", $new_comment, $comment_id);
$update->execute();

echo json_encode(['status' => 'success']);

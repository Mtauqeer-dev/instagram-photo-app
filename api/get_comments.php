<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

$media_id = $_GET['media_id'] ?? 0;

if (!$media_id) {
    echo json_encode([]);
    exit;
}

$current_user = $_SESSION['user_id'] ?? 0;

// Get comments + usernames + user_id
$sql = "SELECT c.id, c.comment, c.user_id, u.username
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.media_id = ?
        ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $media_id);
$stmt->execute();
$result = $stmt->get_result();
$comments = [];

while ($row = $result->fetch_assoc()) {
    $row['owned'] = ($row['user_id'] == $current_user);
    $comments[] = $row;
}

// Get average rating
$avgSql = "SELECT AVG(rating) as avg_rating FROM ratings WHERE media_id = ?";
$avgStmt = $conn->prepare($avgSql);
$avgStmt->bind_param("i", $media_id);
$avgStmt->execute();
$avgResult = $avgStmt->get_result()->fetch_assoc();

$avgRating = $avgResult['avg_rating'] ? round($avgResult['avg_rating'], 1) : null;

// Prepend average rating
array_unshift($comments, ['avg_rating' => $avgRating]);

echo json_encode($comments);

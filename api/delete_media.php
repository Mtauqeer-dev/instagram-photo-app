<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$id = $_POST['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch media info first
$stmt = $conn->prepare("SELECT filepath, thumbnail, creator_id FROM media WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$media = $result->fetch_assoc();

if (!$media || $media['creator_id'] != $user_id) {
    echo json_encode(['status' => 'forbidden']);
    exit;
}

// Delete media + thumbnail files if they exist
@unlink("../" . $media['filepath']);
if (!empty($media['thumbnail'])) {
    @unlink("../" . $media['thumbnail']);
}

// Delete likes
$delLikes = $conn->prepare("DELETE FROM likes WHERE media_id = ?");
$delLikes->bind_param("i", $id);
$delLikes->execute();

// Delete comments
$delComments = $conn->prepare("DELETE FROM comments WHERE media_id = ?");
$delComments->bind_param("i", $id);
$delComments->execute();

// Delete media
$delMedia = $conn->prepare("DELETE FROM media WHERE id = ?");
$delMedia->bind_param("i", $id);
$delMedia->execute();

echo json_encode(['status' => 'success']);

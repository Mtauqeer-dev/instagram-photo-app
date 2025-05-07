<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'creator') {
    $title = $_POST['title'];
    $caption = $_POST['caption'];
    $location = $_POST['location'];
    $people = $_POST['people'];
    $media = $_FILES['media'];
    $thumbnail = $_FILES['thumbnail'] ?? null;

    $uploadDir = '../uploads/';
    $fileName = basename($media['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $type = in_array($ext, ['mp4', 'mov', 'avi']) ? 'video' : 'image';
    $targetPath = $uploadDir . time() . '_' . $fileName;

    $thumbPath = null;

    // Save thumbnail if uploaded
    if ($thumbnail && $thumbnail['tmp_name']) {
        $thumbExt = strtolower(pathinfo($thumbnail['name'], PATHINFO_EXTENSION));
        $thumbName = 'thumb_' . time() . '.' . $thumbExt;
        $thumbTarget = $uploadDir . $thumbName;

        if (move_uploaded_file($thumbnail['tmp_name'], $thumbTarget)) {
            $thumbPath = 'uploads/' . $thumbName;
        }
    }

    if (move_uploaded_file($media['tmp_name'], $targetPath)) {
        $stmt = $conn->prepare("INSERT INTO media (creator_id, title, caption, location, people, filepath, type, thumbnail) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $filepath = 'uploads/' . basename($targetPath);
        $stmt->bind_param("isssssss", $_SESSION['user_id'], $title, $caption, $location, $people, $filepath, $type, $thumbPath);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => '✅ Media uploaded successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '❌ Upload failed. Check folder permissions.']);
    }
} else {
    echo json_encode(['status' => 'unauthorized']);
}

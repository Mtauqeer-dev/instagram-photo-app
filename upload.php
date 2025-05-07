<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] === 'creator') {
    $title = $_POST['title'];
    $caption = $_POST['caption'];
    $location = $_POST['location'];
    $people = $_POST['people'];
    $media = $_FILES['media'];

    $uploadDir = 'uploads/';
    $fileName = basename($media['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'webm'];

    // Determine file type
    $type = in_array($ext, $videoExtensions) ? 'video' : 'image';

    $targetPath = $uploadDir . time() . '_' . $fileName;

    if (move_uploaded_file($media['tmp_name'], $targetPath)) {
        $stmt = $conn->prepare("INSERT INTO media (creator_id, title, caption, location, people, filepath, type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $_SESSION['user_id'], $title, $caption, $location, $people, $targetPath, $type);
        $stmt->execute();

        echo "✅ Media uploaded successfully! <a href='dashboard_creator.php'>Go back</a>";
    } else {
        echo "❌ Upload failed. Check folder permissions.";
    }
} else {
    echo "Unauthorized access.";
}
?>

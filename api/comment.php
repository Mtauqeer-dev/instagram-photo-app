<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'consumer') {
    $media_id = $_POST['media_id'];
    $comment = trim($_POST['comment']);
    $rating = intval($_POST['rating']);
    $user_id = $_SESSION['user_id'];

    // ✅ Save comment if provided
    if ($comment !== '') {
        $stmt = $conn->prepare("INSERT INTO comments (media_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $media_id, $user_id, $comment);
        $stmt->execute();
    }

    // ✅ Save or update rating (if valid)
    if ($rating >= 1 && $rating <= 5) {
        $check = $conn->prepare("SELECT id FROM ratings WHERE media_id = ? AND user_id = ?");
        $check->bind_param("ii", $media_id, $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // Update rating
            $update = $conn->prepare("UPDATE ratings SET rating = ? WHERE media_id = ? AND user_id = ?");
            $update->bind_param("iii", $rating, $media_id, $user_id);
            $update->execute();
        } else {
            // Insert new rating
            $stmt = $conn->prepare("INSERT INTO ratings (media_id, user_id, rating) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $media_id, $user_id, $rating);
            $stmt->execute();
        }
    }

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'unauthorized']);
}
?>

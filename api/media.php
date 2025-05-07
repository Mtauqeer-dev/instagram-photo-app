<?php
session_start();
header('Content-Type: application/json');
include '../config.php';

// ✅ Block if not logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'unauthorized', 'message' => 'You must log in to view media.']);
    exit();
}

// ✅ Handle ?search= query from frontend
$search = $_GET['search'] ?? '';
$searchTerm = '%' . $conn->real_escape_string($search) . '%';

if ($search) {
    $sql = "SELECT id, title, caption, location, people, filepath, type, uploaded_at
            FROM media
            WHERE title LIKE ? OR location LIKE ? OR people LIKE ?
            ORDER BY uploaded_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT id, title, caption, location, people, filepath, type, uploaded_at
            FROM media
            ORDER BY uploaded_at DESC";
    $result = $conn->query($sql);
}

$media = [];
while ($row = $result->fetch_assoc()) {
    $media[] = $row;
}

echo json_encode($media);
?>

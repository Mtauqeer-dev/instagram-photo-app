<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header("Location: login.html");
    exit;
}

$id = $_GET['id'] ?? 0;

// Fetch media
$stmt = $conn->prepare("SELECT * FROM media WHERE id = ? AND creator_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$media = $result->fetch_assoc();

if (!$media) {
    echo "<p style='color:red;'>Media not found or unauthorized access.</p>";
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $caption = $_POST['caption'];
    $location = $_POST['location'];
    $people = $_POST['people'];
    $newThumb = $_FILES['thumbnail'] ?? null;
    $thumbPath = $media['thumbnail'];

    // If new thumbnail uploaded
    if ($newThumb && $newThumb['tmp_name']) {
        $thumbExt = pathinfo($newThumb['name'], PATHINFO_EXTENSION);
        $thumbName = 'thumb_' . time() . '.' . $thumbExt;
        $target = 'uploads/' . $thumbName;
        if (move_uploaded_file($newThumb['tmp_name'], $target)) {
            // Delete old thumbnail if exists
            if (!empty($thumbPath)) @unlink($thumbPath);
            $thumbPath = $target;
        }
    }

    // Update DB
    $update = $conn->prepare("UPDATE media SET title=?, caption=?, location=?, people=?, thumbnail=? WHERE id=? AND creator_id=?");
    $update->bind_param("ssssssi", $title, $caption, $location, $people, $thumbPath, $id, $_SESSION['user_id']);
    $update->execute();

    $success = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Media</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
      .form-container {
        max-width: 500px;
        margin: 50px auto;
        background: #1c1c1c;
        padding: 30px;
        border-radius: 12px;
        color: #fff;
      }
      input, textarea {
        width: 100%;
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 8px;
        background: #2a2a2a;
        border: 1px solid #444;
        color: #fff;
      }
      label {
        font-weight: bold;
        display: block;
        margin-top: 10px;
      }
    </style>
</head>
<body>

<nav class="navbar">
  <div class="nav-content">
    <span class="nav-logo">📸 Edit Media</span>
    <div class="nav-links">
      <a href="gallery.html">Gallery</a>
      <a href="upload.html">Upload</a>
      <a href="dashboard_creator.php">My Dashboard</a>
      <a href="#" onclick="logout()">Logout</a>
    </div>
  </div>
</nav>

<div class="form-container">
    <h2>Edit Media</h2>

    <?php if (!empty($success)): ?>
        <p style="color:lightgreen;">✅ Media updated successfully!</p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($media['title']) ?>" required>

        <label>Caption</label>
        <input type="text" name="caption" value="<?= htmlspecialchars($media['caption']) ?>" required>

        <label>Location</label>
        <input type="text" name="location" value="<?= htmlspecialchars($media['location']) ?>" required>

        <label>People</label>
        <input type="text" name="people" value="<?= htmlspecialchars($media['people']) ?>">

        <?php if ($media['type'] === 'video'): ?>
            <label>Replace Thumbnail (optional)</label>
            <input type="file" name="thumbnail" accept="image/*">
        <?php endif; ?>

        <button type="submit">💾 Save Changes</button>
    </form>
</div>

<script>
  function logout() {
    fetch("api/logout.php").then(() => window.location.href = "login.html");
  }
</script>

</body>
</html>

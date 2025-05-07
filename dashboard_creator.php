<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
  header("Location: login.html");
  exit;
}

$creator_id = $_SESSION['user_id'];

// Get all media with like/comment counts
$sql = "SELECT m.*, 
        (SELECT COUNT(*) FROM likes WHERE media_id = m.id) AS like_count,
        (SELECT COUNT(*) FROM comments WHERE media_id = m.id) AS comment_count
        FROM media m
        WHERE m.creator_id = ?
        ORDER BY m.uploaded_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $creator_id);
$stmt->execute();
$result = $stmt->get_result();
$media = $result->fetch_all(MYSQLI_ASSOC);

// Get overall stats
$statSql = "SELECT 
  COUNT(*) as total_uploads,
  (SELECT COUNT(*) FROM likes l JOIN media m ON l.media_id = m.id WHERE m.creator_id = ?) as total_likes,
  (SELECT COUNT(*) FROM comments c JOIN media m ON c.media_id = m.id WHERE m.creator_id = ?) as total_comments
  FROM media
  WHERE creator_id = ?";
$statStmt = $conn->prepare($statSql);
$statStmt->bind_param("iii", $creator_id, $creator_id, $creator_id);
$statStmt->execute();
$stats = $statStmt->get_result()->fetch_assoc();

// Top liked media (for single preview)
$topSql = "SELECT title, filepath, type, thumbnail, 
          (SELECT COUNT(*) FROM likes WHERE media_id = m.id) as likes 
          FROM media m 
          WHERE creator_id = ? 
          ORDER BY likes DESC LIMIT 1";
$topStmt = $conn->prepare($topSql);
$topStmt->bind_param("i", $creator_id);
$topStmt->execute();
$topMedia = $topStmt->get_result()->fetch_assoc();

// Top 3 leaderboard
$leaderboardSql = "SELECT id, title, filepath, type, thumbnail,
                   (SELECT COUNT(*) FROM likes WHERE media_id = m.id) as like_count
                   FROM media m
                   WHERE m.creator_id = ?
                   ORDER BY like_count DESC
                   LIMIT 3";
$leaderboardStmt = $conn->prepare($leaderboardSql);
$leaderboardStmt->bind_param("i", $creator_id);
$leaderboardStmt->execute();
$leaderboard = $leaderboardStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Your Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      padding: 20px;
    }
    .card {
      background: #1e1e1e;
      padding: 15px;
      border-radius: 12px;
      color: #eee;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.5);
      transition: transform 0.2s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: scale(1.03);
      box-shadow: 0 5px 18px rgba(0, 0, 0, 0.6);
    }
    .card video, .card img {
      width: 100%;
      max-height: 180px;
      border-radius: 8px;
      object-fit: cover;
      transition: opacity 0.3s ease;
    }
    .card h3 {
      margin-top: 10px;
      font-size: 16px;
      color: #fff;
    }
    .stats {
      margin-top: 10px;
    }
    .stats span {
      display: inline-block;
      background: #2b2b2b;
      color: #ccc;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      margin-right: 8px;
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="nav-content">
      <span class="nav-logo">📸 Dashboard</span>
      <div class="nav-links">
        <a href="gallery.html">Gallery</a>
        <a href="upload.html">Upload</a>
        <a href="dashboard_creator.php">My Dashboard</a>
        <a href="#" onclick="logout()">Logout</a>
      </div>
    </div>
  </nav>

  <div style="text-align:center; padding: 20px; color: #eee;">
    <h3>📊 Your Stats</h3>
    <p>Total Uploads: <?= $stats['total_uploads'] ?> &nbsp;|&nbsp;
       Total Likes: <?= $stats['total_likes'] ?> &nbsp;|&nbsp;
       Total Comments: <?= $stats['total_comments'] ?></p>
  </div>

  <?php if (count($leaderboard)): ?>
    <div style="padding: 20px; text-align: center;">
      <h3 style="color: gold;">🏆 Top 3 Most Liked Posts</h3>
      <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
        <?php foreach ($leaderboard as $item): ?>
          <div style="background:#1b1b1b;padding:10px;border-radius:10px;width:200px;color:#eee;">
            <?php if ($item['type'] === 'video'): ?>
              <video controls <?= $item['thumbnail'] ? 'poster="'.$item['thumbnail'].'"' : '' ?> style="width:100%;max-height:130px;">
                <source src="<?= $item['filepath'] ?>" type="video/mp4">
              </video>
            <?php else: ?>
              <img src="<?= $item['filepath'] ?>" style="width:100%;max-height:130px;object-fit:cover;">
            <?php endif; ?>
            <div style="margin-top:8px;">
              <strong><?= htmlspecialchars($item['title']) ?></strong><br>
              ❤️ <?= $item['like_count'] ?> likes
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($topMedia): ?>
    <div style="margin: 30px auto; max-width: 600px; text-align: center; color: #ccc;">
      <h3>🏅 Top Liked Media</h3>
      <?php if ($topMedia['type'] === 'video'): ?>
        <video controls <?= $topMedia['thumbnail'] ? 'poster="'.$topMedia['thumbnail'].'"' : '' ?> style="max-width:100%;border-radius:10px;">
          <source src="<?= $topMedia['filepath'] ?>" type="video/mp4">
        </video>
      <?php else: ?>
        <img src="<?= $topMedia['filepath'] ?>" style="max-width:100%;border-radius:10px;">
      <?php endif; ?>
      <p><strong><?= htmlspecialchars($topMedia['title']) ?></strong> – ❤️ <?= $topMedia['likes'] ?> likes</p>
    </div>
  <?php endif; ?>

  <h2 style="text-align:center;color:#ccc;">Your Uploaded Media</h2>

  <div class="grid">
    <?php foreach ($media as $item): ?>
      <div class="card">
        <?php if ($item['type'] === 'video'): ?>
          <video controls <?= $item['thumbnail'] ? 'poster="'.$item['thumbnail'].'"' : '' ?>>
            <source src="<?= $item['filepath'] ?>" type="video/mp4">
          </video>
        <?php else: ?>
          <img src="<?= $item['filepath'] ?>" alt="media">
        <?php endif; ?>
        <h3><?= htmlspecialchars($item['title']) ?></h3>
        <div class="stats">
          <span>❤️ <?= $item['like_count'] ?> Likes</span>
          <span>💬 <?= $item['comment_count'] ?> Comments</span>
        </div>
        <div style="margin-top:10px;">
          <a href="edit_media.php?id=<?= $item['id'] ?>" style="color:lightblue;">✏️ Edit</a> |
          <a href="#" onclick="deleteMedia(<?= $item['id'] ?>)" style="color:salmon;">🗑️ Delete</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <script>
    function logout() {
      fetch("api/logout.php").then(() => window.location.href = "login.html");
    }

    function deleteMedia(id) {
      if (confirm("Are you sure you want to delete this media?")) {
        fetch("api/delete_media.php", {
          method: "POST",
          body: new URLSearchParams({ id })
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === "success") {
            alert("Media deleted!");
            location.reload();
          } else {
            alert("Failed to delete.");
          }
        });
      }
    }
  </script>
</body>
</html>

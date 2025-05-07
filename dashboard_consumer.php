<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consumer') {
    header("Location: index.php");
    exit();
}

// Get all uploaded media
$sql = "SELECT * FROM media ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consumer Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo $_SESSION['username']; ?> (Consumer)</h2>
<p><a href="logout.php">Logout</a></p>
    <h3>Latest Photos</h3>
    <?php while ($row = $result->fetch_assoc()): ?>
<!-- Comment Form -->
<form action="comment.php" method="post">
    <input type="hidden" name="media_id" value="<?php echo $row['id']; ?>">
    <label>Comment:</label><br>
    <textarea name="comment" rows="2" cols="40" required></textarea><br>
    <label>Rating (1-5):</label>
    <input type="number" name="rating" min="1" max="5" required><br><br>
    <button type="submit">Submit</button>
</form>

<!-- Show past comments -->
<strong>Comments:</strong><br>
<?php
$mid = $row['id'];
$comment_sql = "SELECT c.comment, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.media_id = $mid";
$comment_result = $conn->query($comment_sql);
while ($c = $comment_result->fetch_assoc()) {
    echo "<b>" . htmlspecialchars($c['username']) . ":</b> " . htmlspecialchars($c['comment']) . "<br>";
}

// Show average rating
$rating_sql = "SELECT AVG(rating) as avg_rating FROM ratings WHERE media_id = $mid";
$rating_result = $conn->query($rating_sql);
$avg = $rating_result->fetch_assoc()['avg_rating'];
if ($avg) {
    echo "<strong>Avg. Rating:</strong> " . round($avg, 2) . "/5<br>";
} else {
    echo "<strong>No ratings yet.</strong><br>";
}
?>
<hr>

        <div style="margin-bottom: 20px;">
            <?php if ($row['type'] === 'image'): ?>
    <img src="<?php echo $row['filepath']; ?>" width="200"><br>
<?php else: ?>
    <video width="200" controls>
        <source src="<?php echo $row['filepath']; ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video><br>
<?php endif; ?>

            <strong>Title:</strong> <?php echo htmlspecialchars($row['title']); ?><br>
            <strong>Caption:</strong> <?php echo htmlspecialchars($row['caption']); ?><br>
            <strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?><br>
            <strong>People:</strong> <?php echo htmlspecialchars($row['people']); ?><br>
        </div>
    <?php endwhile; ?>
</body>
</html>

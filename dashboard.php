<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['role'] === 'creator') {
    header("Location: dashboard_creator.php");
} else {
    header("Location: dashboard_consumer.php");
}
exit();

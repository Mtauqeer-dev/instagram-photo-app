<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    echo "Username entered: $username<br>";
    echo "Password entered: $password<br>";

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    echo "Rows matched: " . $stmt->num_rows . "<br>";

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();

        echo "Password in DB (hashed): $hashed_password<br>";
        echo "Verifying password...<br>";

        if (password_verify($password, $hashed_password)) {
            echo "✅ Password matched!<br>";
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "❌ Password does not match.<br>";
        }
    } else {
        echo "❌ No user found with that username.<br>";
    }
}
?>

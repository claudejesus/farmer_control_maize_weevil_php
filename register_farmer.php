<?php
require 'auth.php';
require 'db.php';

// Ensure only admin
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: farmer.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'farmer')");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        header("Location: admin.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}

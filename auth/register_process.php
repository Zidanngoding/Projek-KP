<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once __DIR__ . '/../config/database.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? 'user';

if ($username === '' || $password === '' || $confirm_password === '') {
    header('Location: register.php?error=3');
    exit;
}

if ($password !== $confirm_password) {
    header('Location: register.php?error=2');
    exit;
}

// Cek apakah username sudah ada
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    header('Location: register.php?error=1');
    exit;
}

$stmt->close();

// Insert user baru
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $username, $hashed_password, $role);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: register.php?success=1');
    exit;
} else {
    $stmt->close();
    $conn->close();
    header('Location: register.php?error=4');
    exit;
}
?>

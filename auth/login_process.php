<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../config/database.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: login.php?error=1');
    exit;
}

$stmt = $conn->prepare('SELECT id, password, role FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($user_id, $password_hash, $role);
    $stmt->fetch();
    if (password_verify($password, $password_hash)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        header('Location: ../admin/dashboard.php');
        exit;
    }
}

$stmt->close();
$conn->close();

header('Location: login.php?error=1');
exit;
?>

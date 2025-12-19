<?php
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
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];
        header('Location: ../admin/dashboard.php');
        exit;
    }
}

$stmt->close();
$conn->close();

header('Location: login.php?error=1');
exit;
?>
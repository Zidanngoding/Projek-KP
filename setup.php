<?php
require_once 'config/database.php';

// Insert admin user
$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$role = 'admin';

$stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password)');
$stmt->bind_param('sss', $username, $password, $role);

if ($stmt->execute()) {
    echo 'User admin berhasil dibuat atau diperbarui. Username: admin, Password: admin123';
} else {
    echo 'Gagal membuat user: ' . $stmt->error;
}

$stmt->close();
$conn->close();
?>
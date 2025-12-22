<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    die('ID tidak valid.');
}

$stmt = $conn->prepare('DELETE FROM ktp_prr WHERE id = ?');
$stmt->bind_param('i', $id);

if (!$stmt->execute()) {
    die('Gagal menghapus data: ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: ../admin/ktp_masuk.php?deleted=1');
exit;

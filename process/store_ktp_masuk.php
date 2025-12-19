<?php
require_once __DIR__ . '/../config/database.php';

$nama = trim($_POST['nama_pemohon'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? 'Masuk PRR');
$status = $_POST['status'] ?? 'Diproses';

if ($nama === '' || $keterangan === '') {
    die('Nama pemohon dan keterangan wajib diisi.');
}

$stmt = $conn->prepare('INSERT INTO ktp_prr (nama_pemohon, keterangan, status) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $nama, $keterangan, $status);

if (!$stmt->execute()) {
    die('Gagal menyimpan data: ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: ../admin/ktp_masuk.php?success=1');
exit;

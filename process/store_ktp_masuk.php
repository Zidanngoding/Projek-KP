<?php
require_once __DIR__ . '/../config/database.php';

$nama = trim($_POST['nama_pemohon'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$status = 'Diproses';

if ($nama === '' || $kecamatan === '' || $keterangan === '') {
    die('Nama pemohon, kecamatan, dan keterangan wajib diisi.');
}

$stmt = $conn->prepare('INSERT INTO ktp_prr (nama_pemohon, kecamatan, keterangan, status) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $nama, $kecamatan, $keterangan, $status);

if (!$stmt->execute()) {
    die('Gagal menyimpan data: ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: ../admin/ktp_masuk.php?success=1');
exit;

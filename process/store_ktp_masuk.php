<?php
require_once __DIR__ . '/../config/database.php';

$nama = trim($_POST['nama_pemohon'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$keterangan_pengambilan = trim($_POST['keterangan_pengambilan'] ?? '');
$status = 'Diproses';

if ($nama === '' || $kecamatan === '' || $keterangan === '' || $keterangan_pengambilan === '') {
    die('Nama pemohon, kecamatan, keterangan, dan keterangan pengambilan wajib diisi.');
}

$stmt = $conn->prepare('INSERT INTO ktp_prr (nama_pemohon, kecamatan, keterangan, keterangan_pengambilan, status) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssss', $nama, $kecamatan, $keterangan, $keterangan_pengambilan, $status);

if (!$stmt->execute()) {
    die('Gagal menyimpan data: ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: ../admin/ktp_masuk.php?success=1');
exit;

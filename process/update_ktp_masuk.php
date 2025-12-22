<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_POST['id'] ?? 0);
$nama = trim($_POST['nama_pemohon'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');

if ($id <= 0 || $nama === '' || $kecamatan === '' || $keterangan === '') {
    die('Data tidak lengkap.');
}

$stmt = $conn->prepare('UPDATE ktp_prr SET nama_pemohon = ?, kecamatan = ?, keterangan = ? WHERE id = ?');
$stmt->bind_param('sssi', $nama, $kecamatan, $keterangan, $id);

if (!$stmt->execute()) {
    die('Gagal memperbarui data: ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: ../admin/ktp_masuk.php?updated=1');
exit;

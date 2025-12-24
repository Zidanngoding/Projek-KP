<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_POST['id'] ?? 0);
$nama = trim($_POST['nama_pemohon'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$keterangan_pengambilan = trim($_POST['keterangan_pengambilan'] ?? '');

if ($id <= 0 || $nama === '' || $kecamatan === '' || $keterangan === '' || $keterangan_pengambilan === '') {
    die('Data tidak lengkap.');
}

$stmt = $conn->prepare('UPDATE ktp_prr SET nama_pemohon = ?, kecamatan = ?, keterangan = ?, keterangan_pengambilan = ? WHERE id = ?');
$stmt->bind_param('ssssi', $nama, $kecamatan, $keterangan, $keterangan_pengambilan, $id);

if (!$stmt->execute()) {
    die('Gagal memperbarui data: ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: ../admin/ktp_masuk.php?updated=1');
exit;

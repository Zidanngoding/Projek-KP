<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_POST['id'] ?? 0);
$nama = trim($_POST['nama_pemohon'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$keterangan_pengambilan = trim($_POST['keterangan_pengambilan'] ?? '');
$nama_pengambil = trim($_POST['nama_pengambil'] ?? '');
$telp_pengambil = trim($_POST['telp_pengambil'] ?? '');
$telp_pemohon = trim($_POST['telp_pemohon'] ?? '');

if ($id <= 0 || $nama === '' || $kecamatan === '' || $keterangan === '') {
    die('Data tidak lengkap.');
}

$keterangan_pengambilan_text = $keterangan_pengambilan;
if ($keterangan_pengambilan === 'Diambil sendiri') {
    if ($telp_pemohon === '') {
        die('Nomor telepon pemohon wajib diisi.');
    }
    $keterangan_pengambilan_text = sprintf('Diambil sendiri (Telp: %s)', $telp_pemohon);
}
if ($keterangan_pengambilan === 'Diwakilkan') {
    if ($nama_pengambil === '' || $telp_pengambil === '') {
        die('Nama dan nomor telepon pengambil wajib diisi.');
    }
    $keterangan_pengambilan_text = sprintf('Diwakilkan (Nama: %s, Telp: %s)', $nama_pengambil, $telp_pengambil);
}

$stmt = $conn->prepare('UPDATE ktp_prr SET nama_pemohon = ?, kecamatan = ?, keterangan = ?, keterangan_pengambilan = ? WHERE id = ?');
$stmt->bind_param('ssssi', $nama, $kecamatan, $keterangan, $keterangan_pengambilan_text, $id);

if (!$stmt->execute()) {
    die('Gagal memperbarui data: ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: ../admin/ktp_masuk.php?updated=1');
exit;

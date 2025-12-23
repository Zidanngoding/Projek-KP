<?php
require_once __DIR__ . '/../config/database.php';

$nama = trim($_POST['nama_pemohon'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$keterangan_pengambilan = trim($_POST['keterangan_pengambilan'] ?? '');
$nama_pengambil = trim($_POST['nama_pengambil'] ?? '');
$telp_pengambil = trim($_POST['telp_pengambil'] ?? '');
$telp_pemohon = trim($_POST['telp_pemohon'] ?? '');
$status = 'Diproses';

if ($nama === '' || $kecamatan === '' || $keterangan === '') {
    die('Nama pemohon, kecamatan, dan keterangan wajib diisi.');
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

$stmt = $conn->prepare('INSERT INTO ktp_prr (nama_pemohon, kecamatan, keterangan, keterangan_pengambilan, status) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssss', $nama, $kecamatan, $keterangan, $keterangan_pengambilan_text, $status);

if (!$stmt->execute()) {
    die('Gagal menyimpan data: ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: ../admin/ktp_masuk.php?success=1');
exit;

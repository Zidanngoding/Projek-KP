<?php
require_once __DIR__ . '/../config/database.php';

$nama = trim($_POST['nama_pemohon'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$nama_pengambil = trim($_POST['nama_pengambil'] ?? '');
$telp_pengambil = trim($_POST['telp_pengambil'] ?? '');
$telp_pemohon = trim($_POST['telp_pemohon'] ?? '');
$status = 'Diproses';

if ($nama === '' || $kecamatan === '' || $keterangan === '') {
    die('Nama pemohon, kecamatan, dan keterangan wajib diisi.');
}

$keterangan_text = $keterangan;
if ($keterangan === 'Diambil sendiri') {
    if ($telp_pemohon === '') {
        die('Nomor telepon pemohon wajib diisi.');
    }
    $keterangan_text = sprintf('Diambil sendiri (Telp: %s)', $telp_pemohon);
}
if ($keterangan === 'Diwakilkan') {
    if ($nama_pengambil === '' || $telp_pengambil === '') {
        die('Nama dan nomor telepon pengambil wajib diisi.');
    }
    $keterangan_text = sprintf('Diwakilkan (Nama: %s, Telp: %s)', $nama_pengambil, $telp_pengambil);
}

$stmt = $conn->prepare('INSERT INTO ktp_prr (nama_pemohon, kecamatan, keterangan, status) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $nama, $kecamatan, $keterangan_text, $status);

if (!$stmt->execute()) {
    die('Gagal menyimpan data: ' . $stmt->error);
}

$stmt->close();
$conn->close();

header('Location: ../admin/ktp_masuk.php?success=1');
exit;

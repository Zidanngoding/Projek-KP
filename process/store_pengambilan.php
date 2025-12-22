<?php
require_once __DIR__ . '/../config/database.php';

$nama = trim($_POST['nama_pemohon'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$nama_pengambil = trim($_POST['nama_pengambil'] ?? '');
$telp_pengambil = trim($_POST['telp_pengambil'] ?? '');
$telp_pemohon = trim($_POST['telp_pemohon'] ?? '');

if ($nama === '' || $kecamatan === '' || $keterangan === '') {
    die('Semua field wajib diisi.');
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

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    die('Upload foto gagal.');
}

$allowedExt = ['jpg', 'jpeg', 'png'];
$originalName = $_FILES['foto']['name'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt, true)) {
    die('Format file harus JPG atau PNG.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['foto']['tmp_name']);
$allowedMime = ['image/jpeg', 'image/png'];
if (!in_array($mime, $allowedMime, true)) {
    die('File bukan gambar valid.');
}

$uploadDir = __DIR__ . '/../uploads/bukti_pengambilan/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = uniqid('bukti_', true) . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['foto']['tmp_name'], $targetPath)) {
    die('Gagal menyimpan file.');
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare('INSERT INTO ktp_pengambilan (nama_pemohon, kecamatan, foto_bukti, keterangan) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $nama, $kecamatan, $filename, $keterangan_text);

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan data pengambilan: ' . $stmt->error);
    }

    $stmt->close();

    $stmt = $conn->prepare("UPDATE ktp_prr SET status = 'Diambil' WHERE nama_pemohon = ?");
    $stmt->bind_param('s', $nama);

    if (!$stmt->execute()) {
        throw new Exception('Gagal update status: ' . $stmt->error);
    }

    $stmt->close();
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    die($e->getMessage());
}

$conn->close();

header('Location: ../admin/ktp_pengambilan.php?success=1');
exit;

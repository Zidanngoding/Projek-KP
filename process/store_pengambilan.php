<?php
require_once __DIR__ . '/../config/database.php';

$ktp_prr_id = (int)($_POST['ktp_prr_id'] ?? 0);

if ($ktp_prr_id <= 0) {
    die('Pemohon wajib dipilih.');
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
    $stmt = $conn->prepare("SELECT nama_pemohon, kecamatan, keterangan_pengambilan FROM ktp_prr WHERE id = ? AND status = 'Selesai'");
    $stmt->bind_param('i', $ktp_prr_id);

    if (!$stmt->execute()) {
        throw new Exception('Gagal mengambil data KTP: ' . $stmt->error);
    }

    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $stmt->close();
        throw new Exception('Data KTP tidak ditemukan atau belum siap diambil.');
    }

    $stmt->bind_result($nama_pemohon, $kecamatan, $keterangan_text);
    $stmt->fetch();
    $stmt->close();

    if ($keterangan_text === '') {
        throw new Exception('Keterangan pengambilan belum diisi.');
    }

    $stmt = $conn->prepare('INSERT INTO ktp_pengambilan (nama_pemohon, kecamatan, foto_bukti, keterangan) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $nama_pemohon, $kecamatan, $filename, $keterangan_text);

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan data pengambilan: ' . $stmt->error);
    }

    $stmt->close();

    $stmt = $conn->prepare("UPDATE ktp_prr SET status = 'Diambil' WHERE id = ?");
    $stmt->bind_param('i', $ktp_prr_id);

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

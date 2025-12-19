<?php
require_once __DIR__ . '/../config/database.php';

$nama = trim($_POST['nama_pemohon'] ?? '');
$kecamatan = trim($_POST['kecamatan'] ?? '');

if ($nama === '' || $kecamatan === '') {
    die('Nama pemohon dan kecamatan wajib diisi.');
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare('INSERT INTO ktp_selesai (nama_pemohon, kecamatan) VALUES (?, ?)');
    $stmt->bind_param('ss', $nama, $kecamatan);

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan data selesai: ' . $stmt->error);
    }

    $stmt->close();

    $stmt = $conn->prepare("UPDATE ktp_prr SET status = 'Selesai' WHERE nama_pemohon = ? AND status = 'Diproses'");
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

header('Location: ../admin/ktp_selesai.php?success=1');
exit;

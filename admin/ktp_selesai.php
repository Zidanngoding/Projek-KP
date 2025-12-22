<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$result_pengambilan = $conn->query('SELECT * FROM ktp_pengambilan ORDER BY tanggal_ambil DESC');
$ktp_pengambilan = $result_pengambilan ? $result_pengambilan->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KTP Selesai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="ktp_masuk.php">KTP PRR</a>
        <div class="navbar-nav flex-row align-items-center gap-1">
            <a class="nav-link" href="ktp_masuk.php">KTP Masuk</a>
            <a class="nav-link" href="ktp_pengambilan.php">Pengambilan</a>
            <a class="nav-link active" aria-current="page" href="ktp_selesai.php">KTP Selesai</a>
        </div>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-4">
    <div class="page-header">
        <h1 class="h3 mb-1">KTP Selesai Diambil</h1>
        <div class="text-muted">Data pengambilan KTP yang sudah selesai.</div>
    </div>
    <div class="row g-4">
        <div class="col-12">
            <div class="card card-shadow">
                <div class="card-body">
                    <h2 class="h5 mb-3">Data Pengambilan</h2>
                    <?php if (empty($ktp_pengambilan)): ?>
                        <div class="alert alert-info">Belum ada data pengambilan.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Pemohon</th>
                                        <th>Kecamatan</th>
                                        <th>Foto Bukti</th>
                                        <th>Keterangan</th>
                                        <th>Tanggal Ambil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ktp_pengambilan as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                            <td><?php echo htmlspecialchars($row['kecamatan']); ?></td>
                                            <td>
                                                <img src="../uploads/bukti_pengambilan/<?php echo htmlspecialchars($row['foto_bukti']); ?>" width="90" alt="Bukti">
                                            </td>
                                            <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['tanggal_ambil']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

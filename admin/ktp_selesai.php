<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$success = isset($_GET['success']);
$result_selesai = $conn->query('SELECT * FROM ktp_selesai ORDER BY tanggal_selesai DESC');
$ktp_selesai = $result_selesai ? $result_selesai->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KTP Selesai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">KTP PRR</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-4">
    <div class="d-flex gap-2 mb-3">
        <a class="btn btn-outline-success btn-sm" href="#form">Form Input</a>
        <a class="btn btn-outline-secondary btn-sm" href="#data">Data Selesai</a>
    </div>
    <div class="row g-4">
        <div class="col-lg-5" id="form">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h5 mb-3">Form KTP Selesai Dicetak</h1>
                    <?php if ($success): ?>
                        <div class="alert alert-success">Data berhasil disimpan dan status diperbarui.</div>
                    <?php endif; ?>
                    <form method="post" action="../process/store_ktp_selesai.php">
                        <div class="mb-3">
                            <label class="form-label">Nama Pemohon</label>
                            <input type="text" name="nama_pemohon" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" name="kecamatan" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success">Simpan</button>
                        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7" id="data">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Data KTP Selesai</h2>
                    <?php if (empty($ktp_selesai)): ?>
                        <div class="alert alert-info">Belum ada data KTP selesai.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <input class="form-check-input" type="checkbox" aria-label="Pilih semua">
                                        </th>
                                        <th>Nama Pemohon</th>
                                        <th>Kecamatan</th>
                                        <th>Tanggal Selesai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ktp_selesai as $row): ?>
                                        <tr>
                                            <td>
                                                <input class="form-check-input" type="checkbox" name="selected_selesai[]" value="<?php echo htmlspecialchars($row['id']); ?>" aria-label="Pilih data">
                                            </td>
                                            <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                            <td><?php echo htmlspecialchars($row['kecamatan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['tanggal_selesai']); ?></td>
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

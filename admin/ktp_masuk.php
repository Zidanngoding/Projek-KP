<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$success = isset($_GET['success']);
$result_prr = $conn->query('SELECT * FROM ktp_prr ORDER BY created_at DESC');
$ktp_prr = $result_prr ? $result_prr->fetch_all(MYSQLI_ASSOC) : [];
$result_selesai = $conn->query('SELECT nama_pemohon FROM ktp_selesai');
$selesai_names = $result_selesai ? $result_selesai->fetch_all(MYSQLI_ASSOC) : [];
$selesai_map = [];
foreach ($selesai_names as $row) {
    $selesai_map[$row['nama_pemohon']] = true;
}
$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KTP Masuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="ktp_masuk.php">KTP PRR</a>
        <div class="navbar-nav">
            <a class="nav-link active" aria-current="page" href="ktp_masuk.php">KTP Masuk</a>
            <a class="nav-link" href="ktp_selesai.php">KTP Selesai</a>
            <a class="nav-link" href="ktp_pengambilan.php">Pengambilan</a>
        </div>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-5" id="form">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h5 mb-3">Form KTP Masuk</h1>
                    <?php if ($success): ?>
                        <div class="alert alert-success">Data berhasil disimpan.</div>
                    <?php endif; ?>
                    <form method="post" action="../process/store_ktp_masuk.php">
                        <div class="mb-3">
                            <label class="form-label">Nama Pemohon</label>
                            <input type="text" name="nama_pemohon" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="Masuk PRR" required>
                        </div>
                        <input type="hidden" name="status" value="Diproses">
                        <div class="mb-3">
                            <span class="badge text-bg-secondary">Status: Diproses</span>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7" id="data">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Data KTP PRR</h2>
                    <?php if (empty($ktp_prr)): ?>
                        <div class="alert alert-info">Belum ada data KTP masuk.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <input class="form-check-input" type="checkbox" aria-label="Pilih semua">
                                        </th>
                                        <th>Nama Pemohon</th>
                                        <th>Keterangan</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ktp_prr as $row): ?>
                                        <tr>
                                            <td>
                                                <input class="form-check-input" type="checkbox" name="selected_prr[]" value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo isset($selesai_map[$row['nama_pemohon']]) ? 'checked' : ''; ?> aria-label="Pilih data">
                                            </td>
                                            <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                            <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
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

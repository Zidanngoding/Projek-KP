<?php
ob_start();
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$success = isset($_GET['success']);
$updated = isset($_GET['updated']);
$deleted = isset($_GET['deleted']);
$marked = isset($_GET['marked']);
$kecamatan_options = [
    'Bumi Waras',
    'Enggal',
    'Kedamaian',
    'Kedaton',
    'Kemiling',
    'Labuhan Ratu',
    'Langkapura',
    'Rajabasa',
    'Sukabumi',
    'Sukarame',
    'Tanjung Karang Barat',
    'Tanjung Karang Pusat',
    'Tanjung Karang Timur',
    'Tanjung Senang',
    'Teluk Betung Barat',
    'Teluk Betung Selatan',
    'Teluk Betung Timur',
    'Teluk Betung Utara',
    'Way Halim',
];
$result_prr = $conn->query('SELECT * FROM ktp_prr ORDER BY created_at DESC');
$ktp_prr = $result_prr ? $result_prr->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KTP Masuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="ktp_masuk.php">KTP PRR</a>
        <div class="navbar-nav flex-row align-items-center gap-1">
            <a class="nav-link active" aria-current="page" href="ktp_masuk.php">KTP Masuk</a>
            <a class="nav-link" href="ktp_pengambilan.php">Pengambilan</a>
            <a class="nav-link" href="ktp_selesai.php">KTP Selesai</a>
        </div>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-4">
    <div class="page-header">
        <h1 class="h3 mb-1">KTP PRR</h1>
        <div class="text-muted">Input data pemohon KTP.</div>
    </div>
    <div class="row g-4">
        <div class="col-lg-5" id="form">
            <div class="card card-shadow">
                <div class="card-body">
                    <h1 class="h5 mb-3">Form KTP</h1>
                    <?php if ($success): ?>
                        <div class="alert alert-success">Data berhasil disimpan.</div>
                    <?php endif; ?>
                    <?php if ($updated): ?>
                        <div class="alert alert-success">Data berhasil diperbarui.</div>
                    <?php endif; ?>
                    <?php if ($deleted): ?>
                        <div class="alert alert-success">Data berhasil dihapus.</div>
                    <?php endif; ?>
                    <?php if ($marked): ?>
                        <div class="alert alert-success">Status berhasil diperbarui.</div>
                    <?php endif; ?>
                    <form method="post" action="../process/store_ktp_masuk.php">
                        <div class="mb-3">
                            <label class="form-label">Nama Pemohon</label>
                            <input type="text" name="nama_pemohon" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kecamatan</label>
                            <select name="kecamatan" class="form-select" required>
                                <option value="" selected>Pilih kecamatan</option>
                                <?php foreach ($kecamatan_options as $kecamatan_option): ?>
                                    <option value="<?php echo htmlspecialchars($kecamatan_option); ?>">
                                        <?php echo htmlspecialchars($kecamatan_option); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7" id="data">
            <div class="card card-shadow">
                <div class="card-body">
                    <h2 class="h5 mb-3">Data KTP PRR</h2>
                    <?php if (empty($ktp_prr)): ?>
                        <div class="alert alert-info">Belum ada data KTP masuk.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle ktp-table">
                                <thead>
                                    <tr>
                                        <th>Nama Pemohon</th>
                                        <th>Kecamatan</th>
                                        <th>Keterangan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ktp_prr as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                            <td><?php echo htmlspecialchars($row['kecamatan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                                            <td>
                                                <div class="d-flex flex-column gap-2 ktp-actions">
                                                    <button type="button" class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#editModal-<?php echo htmlspecialchars($row['id']); ?>">
                                                        Edit
                                                    </button>
                                                    <?php if ($row['status'] !== 'Selesai' && $row['status'] !== 'Diambil'): ?>
                                                        <form method="post" action="../process/mark_ktp_selesai.php">
                                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-success w-100">Tandai Selesai</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form method="post" action="../process/delete_ktp_masuk.php" onsubmit="return confirm('Hapus data ini?');">
                                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Hapus</button>
                                                    </form>
                                                </div>
                                                <div class="modal fade" id="editModal-<?php echo htmlspecialchars($row['id']); ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="post" action="../process/update_ktp_masuk.php">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit Data KTP</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Nama Pemohon</label>
                                                                        <input type="text" name="nama_pemohon" class="form-control" value="<?php echo htmlspecialchars($row['nama_pemohon']); ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Kecamatan</label>
                                                                        <select name="kecamatan" class="form-select" required>
                                                                            <?php foreach ($kecamatan_options as $kecamatan_option): ?>
                                                                                <option value="<?php echo htmlspecialchars($kecamatan_option); ?>" <?php echo $row['kecamatan'] === $kecamatan_option ? 'selected' : ''; ?>>
                                                                                    <?php echo htmlspecialchars($kecamatan_option); ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Keterangan</label>
                                                                        <input type="text" name="keterangan" class="form-control" value="<?php echo htmlspecialchars($row['keterangan']); ?>" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (!isset($_SESSION['user_id'])) {
    if (!headers_sent()) {
        header('Location: ../auth/login.php');
        exit;
    }
    echo "<script>window.location.href='../auth/login.php';</script>";
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
$search = trim($_GET['search'] ?? '');
$filter_kecamatan = trim($_GET['kecamatan'] ?? '');
$tanggal = trim($_GET['tanggal'] ?? '');

$query = 'SELECT * FROM ktp_prr';
$conditions = [];
$params = [];
$types = '';

if ($search !== '') {
    $conditions[] = 'nama_pemohon LIKE ?';
    $params[] = '%' . $search . '%';
    $types .= 's';
}

if ($filter_kecamatan !== '') {
    $conditions[] = 'kecamatan = ?';
    $params[] = $filter_kecamatan;
    $types .= 's';
}

if ($tanggal !== '') {
    $conditions[] = 'DATE(created_at) = ?';
    $params[] = $tanggal;
    $types .= 's';
}

if ($conditions) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= ' ORDER BY created_at DESC';

$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Gagal memuat data: ' . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die('Gagal memuat data: ' . $stmt->error);
}

$result_prr = $stmt->get_result();
$ktp_prr = $result_prr ? $result_prr->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
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
                    <form method="post" action="../process/store_ktp_masuk.php" class="keterangan-form">
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
                            <select name="keterangan" class="form-select keterangan-select" required>
                                <option value="" selected>Pilih keterangan</option>
                                <option value="Diambil sendiri">Diambil sendiri</option>
                                <option value="Diwakilkan">Diwakilkan</option>
                            </select>
                        </div>
                        <div class="self-fields d-none">
                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon Pemohon</label>
                                <input type="text" name="telp_pemohon" class="form-control" placeholder="Contoh: 08xxxxxxxxxx">
                            </div>
                        </div>
                        <div class="wakil-fields d-none">
                            <div class="mb-3">
                                <label class="form-label">Nama Pengambil</label>
                                <input type="text" name="nama_pengambil" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon Pengambil</label>
                                <input type="text" name="telp_pengambil" class="form-control" placeholder="Contoh: 08xxxxxxxxxx">
                            </div>
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
                    <form method="get" class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Cari Nama Pemohon</label>
                            <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Contoh: Andi">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($tanggal); ?>">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">Daerah</label>
                            <select name="kecamatan" class="form-select">
                                <option value="">Semua</option>
                                <?php foreach ($kecamatan_options as $kecamatan_option): ?>
                                    <option value="<?php echo htmlspecialchars($kecamatan_option); ?>" <?php echo $filter_kecamatan === $kecamatan_option ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kecamatan_option); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary">Cari</button>
                            <a href="ktp_masuk.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
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
                                                <?php
                                                    $keterangan_value = $row['keterangan'];
                                                    $keterangan_type = '';
                                                    $telp_pemohon_value = '';
                                                    $nama_pengambil_value = '';
                                                    $telp_pengambil_value = '';

                                                    if (preg_match('/^Diambil sendiri(?: \(Telp: (.+)\))?$/', $keterangan_value, $matches)) {
                                                        $keterangan_type = 'Diambil sendiri';
                                                        $telp_pemohon_value = $matches[1] ?? '';
                                                    } elseif (preg_match('/^Diwakilkan(?: \(Nama: (.+), Telp: (.+)\))?$/', $keterangan_value, $matches)) {
                                                        $keterangan_type = 'Diwakilkan';
                                                        $nama_pengambil_value = $matches[1] ?? '';
                                                        $telp_pengambil_value = $matches[2] ?? '';
                                                    }
                                                ?>
                                                <div class="modal fade" id="editModal-<?php echo htmlspecialchars($row['id']); ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="post" action="../process/update_ktp_masuk.php" class="keterangan-form">
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
                                                                        <select name="keterangan" class="form-select keterangan-select" required>
                                                                            <option value="" <?php echo $keterangan_type === '' ? 'selected' : ''; ?>>Pilih keterangan</option>
                                                                            <option value="Diambil sendiri" <?php echo $keterangan_type === 'Diambil sendiri' ? 'selected' : ''; ?>>Diambil sendiri</option>
                                                                            <option value="Diwakilkan" <?php echo $keterangan_type === 'Diwakilkan' ? 'selected' : ''; ?>>Diwakilkan</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="self-fields d-none">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Nomor Telepon Pemohon</label>
                                                                            <input type="text" name="telp_pemohon" class="form-control" value="<?php echo htmlspecialchars($telp_pemohon_value); ?>" placeholder="Contoh: 08xxxxxxxxxx">
                                                                        </div>
                                                                    </div>
                                                                    <div class="wakil-fields d-none">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Nama Pengambil</label>
                                                                            <input type="text" name="nama_pengambil" class="form-control" value="<?php echo htmlspecialchars($nama_pengambil_value); ?>">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Nomor Telepon Pengambil</label>
                                                                            <input type="text" name="telp_pengambil" class="form-control" value="<?php echo htmlspecialchars($telp_pengambil_value); ?>" placeholder="Contoh: 08xxxxxxxxxx">
                                                                        </div>
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
<script>
    const keteranganForms = document.querySelectorAll('.keterangan-form');
    keteranganForms.forEach((form) => {
        const keteranganSelect = form.querySelector('.keterangan-select');
        const selfFields = form.querySelector('.self-fields');
        const wakilFields = form.querySelector('.wakil-fields');

        if (!keteranganSelect || !selfFields || !wakilFields) {
            return;
        }

        const toggleFields = () => {
            const isSelf = keteranganSelect.value === 'Diambil sendiri';
            const isWakil = keteranganSelect.value === 'Diwakilkan';

            selfFields.classList.toggle('d-none', !isSelf);
            wakilFields.classList.toggle('d-none', !isWakil);

            selfFields.querySelectorAll('input').forEach((input) => {
                input.required = isSelf;
            });

            wakilFields.querySelectorAll('input').forEach((input) => {
                input.required = isWakil;
            });
        };

        keteranganSelect.addEventListener('change', toggleFields);
        toggleFields();
    });
</script>
</body>
</html>

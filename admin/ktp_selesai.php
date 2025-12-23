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
$is_pdf = ($_GET['pdf'] ?? '') === '1';
$pdf_link = 'ktp_selesai.php?' . http_build_query([
    'search' => $search,
    'tanggal' => $tanggal,
    'kecamatan' => $filter_kecamatan,
    'pdf' => '1',
]);

$query = 'SELECT * FROM ktp_pengambilan';
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
    $conditions[] = 'DATE(tanggal_ambil) = ?';
    $params[] = $tanggal;
    $types .= 's';
}

if ($conditions) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= ' ORDER BY tanggal_ambil DESC';

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

$result_pengambilan = $stmt->get_result();
$ktp_pengambilan = $result_pengambilan ? $result_pengambilan->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
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
    <style>
        @media print {
            .navbar, .btn, .page-header {
                display: none !important;
            }
            .card {
                box-shadow: none;
                border: none;
            }
            body {
                background: #fff;
            }
        }
    </style>
</head>
<body>
<?php if (!$is_pdf): ?>
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
<?php endif; ?>
<div class="container py-4">
    <?php if (!$is_pdf): ?>
    <div class="page-header">
        <h1 class="h3 mb-1">KTP Selesai Diambil</h1>
        <div class="text-muted">Data pengambilan KTP yang sudah selesai.</div>
    </div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-12">
            <div class="card card-shadow">
                <div class="card-body">
                    <h2 class="h5 mb-3 d-flex justify-content-between align-items-center">
                        <span>Data Pengambilan</span>
                        <?php if (!$is_pdf): ?>
                            <a href="<?php echo htmlspecialchars($pdf_link); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">Unduh PDF</a>
                        <?php endif; ?>
                    </h2>
                    <?php if (!$is_pdf): ?>
                    <form method="get" class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Cari Nama Pemohon</label>
                            <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Contoh: Andi">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($tanggal); ?>">
                        </div>
                        <div class="col-6 col-md-3">
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
                            <a href="ktp_selesai.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                    <?php endif; ?>
                    <?php if (empty($ktp_pengambilan)): ?>
                        <div class="alert alert-info">Belum ada data pengambilan.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Pemohon</th>
                                        <th>Kecamatan</th>
                                        <?php if (!$is_pdf): ?>
                                            <th>Foto Bukti</th>
                                        <?php endif; ?>
                                        <th>Keterangan</th>
                                        <th>Tanggal Ambil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ktp_pengambilan as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                            <td><?php echo htmlspecialchars($row['kecamatan']); ?></td>
                                            <?php if (!$is_pdf): ?>
                                                <td>
                                                    <img src="../uploads/bukti_pengambilan/<?php echo htmlspecialchars($row['foto_bukti']); ?>" width="90" alt="Bukti">
                                                </td>
                                            <?php endif; ?>
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
<?php if ($is_pdf): ?>
<script>
    window.addEventListener('load', () => {
        window.print();
    });
</script>
<?php endif; ?>
</body>
</html>


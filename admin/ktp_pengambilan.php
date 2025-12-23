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

$query = "SELECT id, nama_pemohon, kecamatan, keterangan, created_at FROM ktp_prr WHERE status = 'Selesai'";
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
    $query .= ' AND ' . implode(' AND ', $conditions);
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

$result_siap = $stmt->get_result();
$ktp_siap = $result_siap ? $result_siap->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
$conn->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukti Pengambilan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="ktp_masuk.php">KTP PRR</a>
        <div class="navbar-nav flex-row align-items-center gap-1">
            <a class="nav-link" href="ktp_masuk.php">KTP Masuk</a>
            <a class="nav-link active" aria-current="page" href="ktp_pengambilan.php">Pengambilan</a>
            <a class="nav-link" href="ktp_selesai.php">KTP Selesai</a>
        </div>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-4">
    <div class="page-header">
        <h1 class="h3 mb-1">Bukti Pengambilan</h1>
        <div class="text-muted">Upload bukti pengambilan dan cek data.</div>
    </div>
    <div class="row g-4">
        <div class="col-lg-5" id="form">
            <div class="card card-shadow">
                <div class="card-body">
                    <h1 class="h5 mb-3">Form Pengambilan KTP</h1>
                    <?php if ($success): ?>
                        <div class="alert alert-success">Bukti pengambilan berhasil disimpan.</div>
                    <?php endif; ?>
                    <?php if (empty($ktp_siap)): ?>
                        <div class="alert alert-info">Belum ada KTP yang siap diambil.</div>
                    <?php endif; ?>
                    <form method="post" action="../process/store_pengambilan.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Nama Pemohon</label>
                            <select name="ktp_prr_id" id="ktp_prr_id" class="form-select" required <?php echo empty($ktp_siap) ? 'disabled' : ''; ?>>
                                <option value="" selected>Pilih pemohon</option>
                                <?php foreach ($ktp_siap as $row): ?>
                                    <option value="<?php echo htmlspecialchars($row['id']); ?>">
                                        <?php echo htmlspecialchars($row['nama_pemohon']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Bukti (JPG/PNG)</label>
                            <input type="file" name="foto" class="form-control" accept="image/*" capture="environment" required <?php echo empty($ktp_siap) ? 'disabled' : ''; ?>>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-primary w-100" id="openCameraBtn" <?php echo empty($ktp_siap) ? 'disabled' : ''; ?>>
                                Buka Kamera
                            </button>
                        </div>
                        <div id="cameraWrap" class="d-none">
                            <div class="mb-2">
                                <video id="cameraVideo" class="w-100 rounded" playsinline autoplay muted></video>
                                <canvas id="cameraCanvas" class="w-100 rounded d-none"></canvas>
                            </div>
                            <div class="d-flex gap-2 mb-3">
                                <button type="button" class="btn btn-success w-100" id="captureBtn">Ambil Foto</button>
                                <button type="button" class="btn btn-secondary w-100" id="closeCameraBtn">Tutup Kamera</button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" <?php echo empty($ktp_siap) ? 'disabled' : ''; ?>>Simpan</button>
                        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7" id="data">
            <div class="card card-shadow">
                <div class="card-body">
                    <h2 class="h5 mb-3">Data KTP Siap Diambil</h2>
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
                            <a href="ktp_pengambilan.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                    <?php if (empty($ktp_siap)): ?>
                        <div class="alert alert-info">Belum ada KTP siap diambil.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Pemohon</th>
                                        <th>Kecamatan</th>
                                        <th>Keterangan</th>
                                        <th>Tanggal Masuk</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ktp_siap as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                            <td><?php echo htmlspecialchars($row['kecamatan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
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
<script>
    const fileInput = document.querySelector('input[name="foto"]');
    const openCameraBtn = document.getElementById('openCameraBtn');
    const closeCameraBtn = document.getElementById('closeCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    const cameraWrap = document.getElementById('cameraWrap');
    const cameraVideo = document.getElementById('cameraVideo');
    const cameraCanvas = document.getElementById('cameraCanvas');
    let cameraStream = null;

    async function openCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Kamera tidak didukung di perangkat ini.');
            return;
        }

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            cameraVideo.srcObject = cameraStream;
            cameraWrap.classList.remove('d-none');
            cameraCanvas.classList.add('d-none');
        } catch (error) {
            alert('Tidak bisa mengakses kamera. Pastikan izin kamera diaktifkan.');
        }
    }

    function closeCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach((track) => track.stop());
            cameraStream = null;
        }
        cameraVideo.srcObject = null;
        cameraWrap.classList.add('d-none');
    }

    function capturePhoto() {
        if (!cameraStream) {
            return;
        }

        const videoWidth = cameraVideo.videoWidth || 1280;
        const videoHeight = cameraVideo.videoHeight || 720;
        cameraCanvas.width = videoWidth;
        cameraCanvas.height = videoHeight;

        const ctx = cameraCanvas.getContext('2d');
        ctx.drawImage(cameraVideo, 0, 0, videoWidth, videoHeight);

        cameraCanvas.classList.remove('d-none');

        cameraCanvas.toBlob((blob) => {
            if (!blob) {
                alert('Gagal mengambil foto.');
                return;
            }
            const file = new File([blob], 'bukti_pengambilan.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            closeCamera();
        }, 'image/jpeg', 0.9);
    }

    if (openCameraBtn) {
        openCameraBtn.addEventListener('click', openCamera);
    }
    if (closeCameraBtn) {
        closeCameraBtn.addEventListener('click', closeCamera);
    }
    if (captureBtn) {
        captureBtn.addEventListener('click', capturePhoto);
    }
</script>
</body>
</html>


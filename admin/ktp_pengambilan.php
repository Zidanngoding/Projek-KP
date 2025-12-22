<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$success = isset($_GET['success']);
$result_siap = $conn->query("SELECT id, nama_pemohon, kecamatan, keterangan, created_at FROM ktp_prr WHERE status = 'Selesai' ORDER BY created_at DESC");
$ktp_siap = $result_siap ? $result_siap->fetch_all(MYSQLI_ASSOC) : [];
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
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <select name="keterangan" id="keterangan" class="form-select" required <?php echo empty($ktp_siap) ? 'disabled' : ''; ?>>
                                <option value="" selected>Pilih keterangan</option>
                                <option value="Diambil sendiri">Diambil sendiri</option>
                                <option value="Diwakilkan">Diwakilkan</option>
                            </select>
                        </div>
                        <div id="selfFields" class="d-none">
                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon Pemohon</label>
                                <input type="text" name="telp_pemohon" class="form-control" placeholder="Contoh: 08xxxxxxxxxx">
                            </div>
                        </div>
                        <div id="wakilFields" class="d-none">
                            <div class="mb-3">
                                <label class="form-label">Nama Pengambil</label>
                                <input type="text" name="nama_pengambil" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon Pengambil</label>
                                <input type="text" name="telp_pengambil" class="form-control" placeholder="Contoh: 08xxxxxxxxxx">
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
    const ktpSelect = document.getElementById('ktp_prr_id');
    const keteranganSelect = document.getElementById('keterangan');
    const selfFields = document.getElementById('selfFields');
    const wakilFields = document.getElementById('wakilFields');
    const fileInput = document.querySelector('input[name="foto"]');
    const openCameraBtn = document.getElementById('openCameraBtn');
    const closeCameraBtn = document.getElementById('closeCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    const cameraWrap = document.getElementById('cameraWrap');
    const cameraVideo = document.getElementById('cameraVideo');
    const cameraCanvas = document.getElementById('cameraCanvas');
    let cameraStream = null;

    function toggleWakilFields() {
        const isSelf = keteranganSelect.value === 'Diambil sendiri';
        const isWakil = keteranganSelect.value === 'Diwakilkan';

        selfFields.classList.toggle('d-none', !isSelf);
        wakilFields.classList.toggle('d-none', !isWakil);

        const selfInputs = selfFields.querySelectorAll('input');
        selfInputs.forEach((input) => {
            input.required = isSelf;
        });

        const wakilInputs = wakilFields.querySelectorAll('input');
        wakilInputs.forEach((input) => {
            input.required = isWakil;
        });
    }

    keteranganSelect.addEventListener('change', toggleWakilFields);
    toggleWakilFields();

    if (ktpSelect) {
        ktpSelect.addEventListener('change', toggleWakilFields);
    }

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

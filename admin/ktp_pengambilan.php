<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$success = isset($_GET['success']);
$result_pengambilan = $conn->query('SELECT * FROM ktp_pengambilan ORDER BY tanggal_ambil DESC');
$ktp_pengambilan = $result_pengambilan ? $result_pengambilan->fetch_all(MYSQLI_ASSOC) : [];
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
                    <form method="post" action="../process/store_pengambilan.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Nama Pemohon</label>
                            <input type="text" name="nama_pemohon" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" name="kecamatan" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Bukti (JPG/PNG)</label>
                            <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <select name="keterangan" id="keterangan" class="form-select" required>
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
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7" id="data">
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
                                        <th scope="col">
                                            <input class="form-check-input" type="checkbox" aria-label="Pilih semua">
                                        </th>
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
                                            <td>
                                                <input class="form-check-input" type="checkbox" name="selected_pengambilan[]" value="<?php echo htmlspecialchars($row['id']); ?>" checked aria-label="Pilih data">
                                            </td>
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
<script>
    const keteranganSelect = document.getElementById('keterangan');
    const selfFields = document.getElementById('selfFields');
    const wakilFields = document.getElementById('wakilFields');

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
</script>
</body>
</html>

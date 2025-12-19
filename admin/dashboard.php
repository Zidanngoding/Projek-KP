<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Sistem KTP PRR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">KTP PRR</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="../auth/logout.php">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-5">
    <h1 class="mb-4">Dashboard Admin</h1>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">KTP Masuk PRR</h5>
                    <p class="card-text">Input data pemohon dan pantau status proses.</p>
                    <a href="ktp_masuk.php" class="btn btn-primary">Buka Menu</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">KTP Selesai Dicetak</h5>
                    <p class="card-text">Input data selesai dan update status.</p>
                    <a href="ktp_selesai.php" class="btn btn-success">Buka Menu</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Bukti Pengambilan</h5>
                    <p class="card-text">Upload bukti dan cek data pengambilan.</p>
                    <a href="ktp_pengambilan.php" class="btn btn-warning">Buka Menu</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

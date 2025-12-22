<?php
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

if (!$host || !$user || !$db || !$port) {
    die('Konfigurasi database Railway belum lengkap.');
}

$conn = mysqli_connect($host, $user, $pass, $db, (int)$port);
if (!$conn) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

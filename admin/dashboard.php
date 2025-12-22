<?php
if (!headers_sent()) {
    header('Location: ktp_masuk.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="0; url=ktp_masuk.php">
    <title>Redirecting...</title>
</head>
<body>
<script>
    window.location.href = 'ktp_masuk.php';
</script>
</body>
</html>

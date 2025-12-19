<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Sistem KTP PRR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3 text-center">Register</h1>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php if ($_GET['error'] == 1): ?>Username sudah ada.<?php endif; ?>
                            <?php if ($_GET['error'] == 2): ?>Password tidak cocok.<?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">Registrasi berhasil. <a href="login.php">Login</a></div>
                    <?php endif; ?>
                    <form method="post" action="register_process.php">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control" required>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                    <p class="mt-3 text-center"><a href="login.php">Sudah punya akun? Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
require_once 'config.php';
if (isLoggedIn()) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $no_hp = trim($_POST['no_hp'] ?? '');

    if (empty($nama) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, password, role, no_hp) VALUES (?, ?, ?, 'kasir', ?)");
            $stmt->execute([$nama, $email, $hashed, $no_hp]);
            flash('success', 'Registrasi berhasil! Silakan login.');
            redirect('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Toko Minuman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --primary: #7b2d26; --primary-dark: #5a1f1a; --secondary: #b8860b; --accent: #d4a843; }
        body {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0;
        }
        .register-card {
            background: white; border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4); max-width: 550px; width: 92%; padding: 3rem;
        }
        .register-header { text-align: center; margin-bottom: 2rem; }
        .register-header i { font-size: 4rem; color: var(--primary); }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 0.2rem rgba(212,168,67,0.25); }
        .btn-register {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none; padding: 0.7rem 2rem; font-weight: 600; border-radius: 10px; color: white;
        }
        .btn-register:hover { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); transform: translateY(-2px); color: white; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <i class="bi bi-cup-straw"></i>
            <h2 style="color: var(--primary);">Toko Minuman</h2>
            <p class="text-muted">Daftar Akun Baru</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap *</label>
                <input type="text" class="form-control" name="nama_lengkap" value="<?= sanitize($_POST['nama_lengkap'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" class="form-control" name="password" required placeholder="Min. 6 karakter">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Konfirmasi Password *</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">No. HP</label>
                <input type="text" class="form-control" name="no_hp" value="<?= sanitize($_POST['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx">
            </div>
            <button type="submit" class="btn btn-register w-100 mb-3"><i class="bi bi-person-plus"></i> Daftar</button>
        </form>
        <div class="text-center">
            <p class="mb-0">Sudah punya akun? <a href="index.php" style="color: var(--primary); font-weight: 600;">Masuk</a></p>
        </div>
    </div>
</body>
</html>

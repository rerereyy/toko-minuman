<?php
require_once 'config.php';

if (isLoggedIn()) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            redirect('dashboard.php');
        } else {
            $error = 'Email atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Minuman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --primary: #7b2d26; --primary-dark: #5a1f1a; --secondary: #b8860b; --accent: #d4a843; }
        body {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            background: white; border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4); overflow: hidden; max-width: 900px; width: 92%;
        }
        .login-left {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            color: white; padding: 3rem; display: flex; flex-direction: column;
            justify-content: center; align-items: center; text-align: center;
        }
        .login-left i { font-size: 5rem; margin-bottom: 1rem; color: var(--accent); }
        .login-right { padding: 3rem; }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 0.2rem rgba(212,168,67,0.25); }
        .btn-login {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none; padding: 0.75rem 2rem; font-weight: 600; border-radius: 10px; color: white;
        }
        .btn-login:hover { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); transform: translateY(-2px); color: white; }
        @media (max-width: 768px) { .login-left { padding: 2rem; } .login-left i { font-size: 3rem; } }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="row g-0">
            <div class="col-md-5 login-left">
                <i class="bi bi-cup-straw"></i>
                <h2>Toko Minuman</h2>
                <p class="mt-3 opacity-75">Sistem Manajemen Inventaris & Penjualan Minuman Beralkohol</p>
            </div>
            <div class="col-md-7 login-right">
                <h3 class="mb-4" style="color: var(--primary);">Selamat Datang</h3>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $error ?></div>
                <?php endif; ?>
                <?php $s = flash('success'); if ($s): ?>
                    <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $s ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required placeholder="Masukkan email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" name="password" required placeholder="Masukkan password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-login w-100 mb-3"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
                </form>
                <div class="text-center">
                    <p class="mb-0">Belum punya akun? <a href="register.php" style="color: var(--primary); font-weight: 600;">Daftar</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

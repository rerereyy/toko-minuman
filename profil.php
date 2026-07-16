<?php
require_once 'config.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    $current_pass = $_POST['current_password'] ?? '';

    if (!password_verify($current_pass, $user['password'])) {
        $error = 'Password saat ini salah!';
    } elseif (empty($nama)) {
        $error = 'Nama lengkap harus diisi!';
    } else {
        $sql = "UPDATE users SET nama_lengkap=?, no_hp=?";
        $params = [$nama, $no_hp];
        if (!empty($new_pass)) {
            if (strlen($new_pass) < 6) { $error = 'Password baru minimal 6 karakter!'; }
            elseif ($new_pass !== $confirm_pass) { $error = 'Konfirmasi password baru tidak cocok!'; }
            else { $sql .= ", password=?"; $params[] = password_hash($new_pass, PASSWORD_DEFAULT); }
        }
        if (empty($error)) {
            $sql .= " WHERE id=?";
            $params[] = $user_id;
            $pdo->prepare($sql)->execute($params);
            $_SESSION['nama_lengkap'] = $nama;
            $success = 'Profil berhasil diperbarui!';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-person-gear"></i> Profil Saya</h4>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil-square"></i> Edit Profil</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" name="nama_lengkap" value="<?= sanitize($user['nama_lengkap']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
                        <small class="text-muted">Email tidak dapat diubah.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" class="form-control" name="no_hp" value="<?= sanitize($user['no_hp'] ?? '') ?>">
                    </div>
                    <hr>
                    <h6>Ubah Password (opsional)</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="new_password" placeholder="Kosongkan jika tidak ubah">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="confirm_password">
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini *</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-circle"></i> Info Akun</div>
            <div class="card-body text-center">
                <i class="bi bi-person-circle" style="font-size:5rem;color:var(--primary)"></i>
                <h5 class="mt-2"><?= sanitize($user['nama_lengkap']) ?></h5>
                <p class="text-muted"><?= sanitize($user['email']) ?></p>
                <span class="badge bg-<?= $user['role']=='admin'?'primary':'secondary' ?>"><?= ucfirst($user['role']) ?></span>
                <hr>
                <p class="text-start"><i class="bi bi-phone"></i> <?= sanitize($user['no_hp'] ?? '-') ?></p>
                <p class="text-start"><i class="bi bi-calendar"></i> Bergabung: <?= date('d M Y', strtotime($user['created_at'])) ?></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

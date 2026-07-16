<?php
require_once 'config.php';

// Proses hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id == 1) { flash('error', 'Pelanggan default tidak bisa dihapus!'); }
    else {
        $pdo->prepare("DELETE FROM pelanggan WHERE id = ?")->execute([$id]);
        flash('success', 'Pelanggan berhasil dihapus!');
    }
    redirect('pelanggan.php');
}

// Proses tambah/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $nama = trim($_POST['nama_pelanggan'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $email_p = trim($_POST['email'] ?? '');

    if (empty($nama)) {
        flash('error', 'Nama pelanggan harus diisi!');
    } else {
        if ($action == 'edit' && isset($_POST['id'])) {
            $pdo->prepare("UPDATE pelanggan SET nama_pelanggan=?, no_hp=?, alamat=?, email=? WHERE id=?")->execute([$nama, $no_hp, $alamat, $email_p, (int)$_POST['id']]);
            flash('success', 'Pelanggan berhasil diperbarui!');
        } else {
            $pdo->prepare("INSERT INTO pelanggan (nama_pelanggan, no_hp, alamat, email) VALUES (?,?,?,?)")->execute([$nama, $no_hp, $alamat, $email_p]);
            flash('success', 'Pelanggan berhasil ditambahkan!');
        }
    }
    redirect('pelanggan.php');
}

// Filter search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM pelanggan WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (nama_pelanggan LIKE ? OR no_hp LIKE ? OR email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY nama_pelanggan";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pelanggan_list = $stmt->fetchAll();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt2 = $pdo->prepare("SELECT * FROM pelanggan WHERE id = ?");
    $stmt2->execute([(int)$_GET['edit']]);
    $edit_data = $stmt2->fetch();
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-people"></i> Data Pelanggan</h4>
</div>

<?php $msg = flash('success'); if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php $err = flash('error'); if ($err): ?><div class="alert alert-danger"><?= $err ?></div><?php endif; ?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-<?= $edit_data ? 'pencil' : 'person-plus' ?>"></i> <?= $edit_data ? 'Edit' : 'Tambah' ?> Pelanggan</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'tambah' ?>">
                    <?php if ($edit_data): ?><input type="hidden" name="id" value="<?= $edit_data['id'] ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Pelanggan *</label>
                        <input type="text" class="form-control" name="nama_pelanggan" value="<?= sanitize($edit_data['nama_pelanggan'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" class="form-control" name="no_hp" value="<?= sanitize($edit_data['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= sanitize($edit_data['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="alamat" rows="2"><?= sanitize($edit_data['alamat'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    <?php if ($edit_data): ?><a href="pelanggan.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-list-ul"></i> Daftar Pelanggan</span>
                <form method="GET" class="d-flex" style="max-width:250px">
                    <input type="text" class="form-control form-control-sm me-2" name="search" placeholder="Cari..." value="<?= sanitize($search) ?>">
                    <button type="submit" class="btn btn-sm btn-light"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>No</th><th>Nama</th><th>No. HP</th><th>Email</th><th>Alamat</th><th class="text-center">Aksi</th></tr></thead>
                        <tbody>
                            <?php foreach ($pelanggan_list as $i => $pl): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><strong><?= sanitize($pl['nama_pelanggan']) ?></strong></td>
                                <td><?= sanitize($pl['no_hp'] ?? '-') ?></td>
                                <td><?= sanitize($pl['email'] ?? '-') ?></td>
                                <td><?= sanitize($pl['alamat'] ?? '-') ?></td>
                                <td class="text-center">
                                    <a href="pelanggan.php?edit=<?= $pl['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <?php if ($pl['id'] != 1): ?>
                                        <a href="pelanggan.php?hapus=<?= $pl['id'] ?>" class="btn btn-sm btn-danger btn-hapus"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Menampilkan <?= count($pelanggan_list) ?> pelanggan</small>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

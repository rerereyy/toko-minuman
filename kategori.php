<?php
require_once 'config.php';

// Proses hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $cek = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE kategori_id = ?");
    $cek->execute([$id]);
    if ($cek->fetchColumn() > 0) {
        flash('error', 'Tidak bisa menghapus kategori yang masih memiliki produk!');
    } else {
        $pdo->prepare("DELETE FROM kategori_produk WHERE id = ?")->execute([$id]);
        flash('success', 'Kategori berhasil dihapus!');
    }
    redirect('kategori.php');
}

// Proses tambah/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $nama = trim($_POST['nama_kategori'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($nama)) {
        flash('error', 'Nama kategori harus diisi!');
    } else {
        if ($action == 'edit' && isset($_POST['id'])) {
            $pdo->prepare("UPDATE kategori_produk SET nama_kategori=?, deskripsi=? WHERE id=?")->execute([$nama, $deskripsi, (int)$_POST['id']]);
            flash('success', 'Kategori berhasil diperbarui!');
        } else {
            $pdo->prepare("INSERT INTO kategori_produk (nama_kategori, deskripsi) VALUES (?, ?)")->execute([$nama, $deskripsi]);
            flash('success', 'Kategori berhasil ditambahkan!');
        }
    }
    redirect('kategori.php');
}

$kategori_list = $pdo->query("SELECT k.*, (SELECT COUNT(*) FROM produk WHERE kategori_id = k.id) as jml_produk FROM kategori_produk k ORDER BY k.nama_kategori")->fetchAll();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM kategori_produk WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-tags"></i> Kategori Produk</h4>
</div>

<?php $msg = flash('success'); if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php $err = flash('error'); if ($err): ?><div class="alert alert-danger"><?= $err ?></div><?php endif; ?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-<?= $edit_data ? 'pencil' : 'plus-circle' ?>"></i> <?= $edit_data ? 'Edit' : 'Tambah' ?> Kategori</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_data ? 'edit' : 'tambah' ?>">
                    <?php if ($edit_data): ?><input type="hidden" name="id" value="<?= $edit_data['id'] ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori *</label>
                        <input type="text" class="form-control" name="nama_kategori" value="<?= sanitize($edit_data['nama_kategori'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"><?= sanitize($edit_data['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    <?php if ($edit_data): ?><a href="kategori.php" class="btn btn-secondary">Batal</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-list-ul"></i> Daftar Kategori</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>No</th><th>Nama Kategori</th><th>Deskripsi</th><th>Jumlah Produk</th><th class="text-center">Aksi</th></tr></thead>
                        <tbody>
                            <?php foreach ($kategori_list as $i => $k): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><strong><?= sanitize($k['nama_kategori']) ?></strong></td>
                                <td><?= sanitize($k['deskripsi'] ?? '-') ?></td>
                                <td><span class="badge bg-info"><?= $k['jml_produk'] ?> produk</span></td>
                                <td class="text-center">
                                    <a href="kategori.php?edit=<?= $k['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <a href="kategori.php?hapus=<?= $k['id'] ?>" class="btn btn-sm btn-danger btn-hapus"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

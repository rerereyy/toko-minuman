<?php
require_once 'config.php';

// Proses hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->execute([$id]);
    flash('success', 'Produk berhasil dihapus!');
    redirect('produk.php');
}

// Ambil data kategori
$kategori_list = $pdo->query("SELECT * FROM kategori_produk ORDER BY nama_kategori")->fetchAll();

// Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

$sql = "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori_produk k ON p.kategori_id = k.id WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (p.nama_produk LIKE ? OR p.kode_produk LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($filter_kategori) { $sql .= " AND p.kategori_id = ?"; $params[] = $filter_kategori; }
$sql .= " ORDER BY p.nama_produk";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produk_list = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-box-seam"></i> Data Produk</h4>
    <a href="produk.php?aksi=tambah" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Produk</a>
</div>

<?php $msg = flash('success'); if ($msg): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $msg ?></div>
<?php endif; ?>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" class="form-control" name="search" placeholder="Cari nama/kode produk..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-4">
                <select class="form-select" name="kategori">
                    <option value="0">Semua Kategori</option>
                    <?php foreach ($kategori_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $filter_kategori == $k['id'] ? 'selected' : '' ?>><?= sanitize($k['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button></div>
        </form>
    </div>
</div>

<!-- Form Tambah/Edit -->
<?php if (isset($_GET['aksi']) && in_array($_GET['aksi'], ['tambah', 'edit'])): ?>
<?php
    $edit_data = null;
    if ($_GET['aksi'] == 'edit' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
        $stmt->execute([(int)$_GET['id']]);
        $edit_data = $stmt->fetch();
    }
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $kode = trim($_POST['kode_produk'] ?? '');
        $nama = trim($_POST['nama_produk'] ?? '');
        $kategori_id = (int)($_POST['kategori_id'] ?? 0);
        $harga = (float)($_POST['harga_jual'] ?? 0);
        $stok = (int)($_POST['stok'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if (empty($kode) || empty($nama) || $kategori_id <= 0 || $harga <= 0) {
            $error = 'Semua field wajib diisi dengan benar!';
        } else {
            if ($edit_data) {
                $stmt = $pdo->prepare("UPDATE produk SET kode_produk=?, nama_produk=?, kategori_id=?, harga_jual=?, stok=?, deskripsi=? WHERE id=?");
                $stmt->execute([$kode, $nama, $kategori_id, $harga, $stok, $deskripsi, $edit_data['id']]);
                flash('success', 'Produk berhasil diperbarui!');
            } else {
                $stmt = $pdo->prepare("INSERT INTO produk (kode_produk, nama_produk, kategori_id, harga_jual, stok, deskripsi) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$kode, $nama, $kategori_id, $harga, $stok, $deskripsi]);
                flash('success', 'Produk berhasil ditambahkan!');
            }
            redirect('produk.php');
        }
    }
?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-<?= $edit_data ? 'pencil' : 'plus-circle' ?>"></i> <?= $edit_data ? 'Edit' : 'Tambah' ?> Produk</div>
    <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kode Produk *</label>
                    <input type="text" class="form-control" name="kode_produk" value="<?= sanitize($edit_data['kode_produk'] ?? ($_POST['kode_produk'] ?? '')) ?>" required placeholder="Contoh: BRW-001">
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Nama Produk *</label>
                    <input type="text" class="form-control" name="nama_produk" value="<?= sanitize($edit_data['nama_produk'] ?? ($_POST['nama_produk'] ?? '')) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kategori *</label>
                    <select class="form-select" name="kategori_id" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($kategori_list as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ($edit_data['kategori_id'] ?? ($_POST['kategori_id'] ?? '')) == $k['id'] ? 'selected' : '' ?>><?= sanitize($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Harga Jual (Rp) *</label>
                    <input type="number" class="form-control" name="harga_jual" step="100" min="0" value="<?= $edit_data['harga_jual'] ?? ($_POST['harga_jual'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Stok *</label>
                    <input type="number" class="form-control" name="stok" min="0" value="<?= $edit_data['stok'] ?? ($_POST['stok'] ?? '0') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" rows="1"><?= sanitize($edit_data['deskripsi'] ?? ($_POST['deskripsi'] ?? '')) ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            <a href="produk.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Produk -->
<div class="card">
    <div class="card-body">
        <?php if (empty($produk_list)): ?>
            <p class="text-muted text-center">Tidak ada data produk.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>Kode</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th class="text-center">Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produk_list as $p): ?>
                        <tr>
                            <td><strong><?= sanitize($p['kode_produk']) ?></strong></td>
                            <td><?= sanitize($p['nama_produk']) ?></td>
                            <td><span class="badge badge-kategori"><?= sanitize($p['nama_kategori']) ?></span></td>
                            <td><?= formatRupiah($p['harga_jual']) ?></td>
                            <td>
                                <?php if ($p['stok'] <= 5): ?>
                                    <span class="badge bg-danger"><?= $p['stok'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= $p['stok'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="produk.php?aksi=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <a href="produk.php?hapus=<?= $p['id'] ?>" class="btn btn-sm btn-danger btn-hapus"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted">Menampilkan <?= count($produk_list) ?> produk</small>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

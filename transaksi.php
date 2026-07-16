<?php
require_once 'config.php';
$user_id = $_SESSION['user_id'];

// Proses hapus transaksi
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Kembalikan stok
    $details = $pdo->prepare("SELECT produk_id, jumlah FROM detail_transaksi WHERE transaksi_id = ?");
    $details->execute([$id]);
    foreach ($details->fetchAll() as $d) {
        $pdo->prepare("UPDATE produk SET stok = stok + ? WHERE id = ?")->execute([$d['jumlah'], $d['produk_id']]);
    }
    $pdo->prepare("DELETE FROM detail_transaksi WHERE transaksi_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM transaksi WHERE id = ?")->execute([$id]);
    flash('success', 'Transaksi berhasil dihapus!');
    redirect('transaksi.php');
}

// Proses tambah transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'proses') {
    $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 1);
    $metode = $_POST['metode_pembayaran'] ?? 'cash';
    $items = $_POST['item'] ?? [];
    $jumlah = $_POST['jumlah'] ?? [];
    $diskon = (float)($_POST['diskon'] ?? 0);

    if (empty($items)) {
        flash('error', 'Pilih minimal satu produk!');
        redirect('transaksi.php?aksi=tambah');
    }

    $subtotal = 0;
    $detail_data = [];
    foreach ($items as $idx => $produk_id) {
        if ($produk_id && $jumlah[$idx] > 0) {
            $produk = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
            $produk->execute([$produk_id]);
            $p = $produk->fetch();
            if ($p && $p['stok'] >= $jumlah[$idx]) {
                $sub = $p['harga_jual'] * $jumlah[$idx];
                $subtotal += $sub;
                $detail_data[] = ['produk_id' => $produk_id, 'jumlah' => $jumlah[$idx], 'harga' => $p['harga_jual'], 'subtotal' => $sub];
            }
        }
    }

    if (empty($detail_data)) {
        flash('error', 'Tidak ada produk valid dipilih!');
        redirect('transaksi.php?aksi=tambah');
    }

    $total = $subtotal - $diskon;
    if ($total < 0) $total = 0;

    // Generate kode transaksi
    $kode = 'TRX-' . date('Ymd') . '-' . str_pad($pdo->query("SELECT MAX(id) FROM transaksi")->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);

    $pdo->prepare("INSERT INTO transaksi (kode_transaksi, user_id, pelanggan_id, subtotal, diskon, total_bayar, metode_pembayaran) VALUES (?,?,?,?,?,?,?)")
        ->execute([$kode, $user_id, $pelanggan_id, $subtotal, $diskon, $total, $metode]);
    $trx_id = $pdo->lastInsertId();

    foreach ($detail_data as $d) {
        $pdo->prepare("INSERT INTO detail_transaksi (transaksi_id, produk_id, jumlah, harga_satuan, subtotal) VALUES (?,?,?,?,?)")
            ->execute([$trx_id, $d['produk_id'], $d['jumlah'], $d['harga'], $d['subtotal']]);
        $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?")->execute([$d['jumlah'], $d['produk_id']]);
    }

    flash('success', "Transaksi $ kode berhasil disimpan!");
    redirect('transaksi.php');
}

// Ambil data
$produk_list = $pdo->query("SELECT * FROM produk WHERE stok > 0 ORDER BY nama_produk")->fetchAll();
$pelanggan_list = $pdo->query("SELECT * FROM pelanggan ORDER BY nama_pelanggan")->fetchAll();

// List transaksi
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT t.*, p.nama_pelanggan, u.nama_lengkap FROM transaksi t LEFT JOIN pelanggan p ON t.pelanggan_id=p.id JOIN users u ON t.user_id=u.id WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (t.kode_transaksi LIKE ? OR p.nama_pelanggan LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY t.tanggal_transaksi DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi_list = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-receipt"></i> Transaksi</h4>
    <?php if (!isset($_GET['aksi'])): ?>
        <a href="transaksi.php?aksi=tambah" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Transaksi Baru</a>
    <?php endif; ?>
</div>

<?php $msg = flash('success'); if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php $err = flash('error'); if ($err): ?><div class="alert alert-danger"><?= $err ?></div><?php endif; ?>

<?php if (isset($_GET['aksi']) && $_GET['aksi'] == 'tambah'): ?>
<!-- Form Transaksi Baru -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-cart-plus"></i> Transaksi Baru</div>
    <div class="card-body">
        <form method="POST" id="formTransaksi">
            <input type="hidden" name="aksi" value="proses">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Pelanggan</label>
                    <select class="form-select" name="pelanggan_id">
                        <?php foreach ($pelanggan_list as $pl): ?>
                            <option value="<?= $pl['id'] ?>"><?= sanitize($pl['nama_pelanggan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Metode Pembayaran</label>
                    <select class="form-select" name="metode_pembayaran">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="kartu">Kartu</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Diskon (Rp)</label>
                    <input type="number" class="form-control" name="diskon" value="0" min="0" id="diskon">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="itemsTable">
                    <thead class="table-dark">
                        <tr><th style="width:5%">No</th><th>Produk</th><th style="width:15%">Harga</th><th style="width:12%">Jumlah</th><th style="width:18%">Subtotal</th><th style="width:5%">Aksi</th></tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr>
                            <td>1</td>
                            <td>
                                <select class="form-select form-select-sm item-produk" required>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($produk_list as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-harga="<?= (int)$p['harga_jual'] ?>"><?= sanitize($p['nama_produk']) ?> (Stok: <?= $p['stok'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="item[]" class="item-id">
                            </td>
                            <td class="item-harga">Rp 0</td>
                            <td><input type="number" class="form-control form-control-sm item-jumlah" name="jumlah[]" value="1" min="1"></td>
                            <td class="item-subtotal"><strong>Rp 0</strong></td>
                            <td><button type="button" class="btn btn-sm btn-danger btn-remove-item"><i class="bi bi-x"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-success mb-3" id="addItem"><i class="bi bi-plus"></i> Tambah Baris</button>

            <div class="row justify-content-end">
                <div class="col-md-4">
                    <table class="table">
                        <tr><td>Subtotal</td><td class="text-end" id="subtotalDisplay">Rp 0</td></tr>
                        <tr><td>Diskon</td><td class="text-end" id="diskonDisplay">- Rp 0</td></tr>
                        <tr><td><strong>Total Bayar</strong></td><td class="text-end"><strong id="totalDisplay" style="color:var(--primary);font-size:1.3rem">Rp 0</strong></td></tr>
                    </table>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Proses Transaksi</button>
                <a href="transaksi.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<!-- List Transaksi -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" placeholder="Cari kode/pelanggan..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cari</button>
            </div>
        </form>
        <?php if (empty($transaksi_list)): ?>
            <p class="text-muted text-center">Belum ada transaksi.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Kode</th><th>Tanggal</th><th>Pelanggan</th><th>Kasir</th><th>Subtotal</th><th>Diskon</th><th>Total</th><th>Bayar</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($transaksi_list as $t): ?>
                        <tr>
                            <td><strong><?= sanitize($t['kode_transaksi']) ?></strong></td>
                            <td><small><?= date('d/m/Y H:i', strtotime($t['tanggal_transaksi'])) ?></small></td>
                            <td><?= sanitize($t['nama_pelanggan'] ?? 'Umum') ?></td>
                            <td><?= sanitize($t['nama_lengkap']) ?></td>
                            <td><?= formatRupiah($t['subtotal']) ?></td>
                            <td><?= $t['diskon'] > 0 ? '- '.formatRupiah($t['diskon']) : '-' ?></td>
                            <td><strong class="text-success"><?= formatRupiah($t['total_bayar']) ?></strong></td>
                            <td><span class="badge bg-<?= $t['metode_pembayaran']=='cash'?'success':($t['metode_pembayaran']=='transfer'?'primary':'warning') ?>"><?= ucfirst($t['metode_pembayaran']) ?></span></td>
                            <td><span class="badge bg-<?= $t['status']=='selesai'?'success':($t['status']=='pending'?'warning':'danger') ?>"><?= ucfirst($t['status']) ?></span></td>
                            <td class="text-center">
                                <a href="transaksi.php?detail=<?= $t['id'] ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a>
                                <a href="transaksi.php?hapus=<?= $t['id'] ?>" class="btn btn-sm btn-danger btn-hapus"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted">Menampilkan <?= count($transaksi_list) ?> transaksi</small>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Detail Modal -->
<?php if (isset($_GET['detail'])): ?>
<?php
    $trx = $pdo->prepare("SELECT t.*, p.nama_pelanggan, u.nama_lengkap FROM transaksi t LEFT JOIN pelanggan p ON t.pelanggan_id=p.id JOIN users u ON t.user_id=u.id WHERE t.id=?");
    $trx->execute([(int)$_GET['detail']]);
    $trx = $trx->fetch();
    $details = $pdo->prepare("SELECT dt.*, pr.nama_produk, pr.kode_produk FROM detail_transaksi dt JOIN produk pr ON dt.produk_id=pr.id WHERE dt.transaksi_id=?");
    $details->execute([(int)$_GET['detail']]);
    $details = $details->fetchAll();
?>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-receipt-cutoff"></i> Detail: <?= sanitize($trx['kode_transaksi']) ?></span>
        <a href="transaksi.php" class="btn btn-sm btn-light">Tutup</a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Pelanggan:</strong> <?= sanitize($trx['nama_pelanggan'] ?? 'Umum') ?></p>
                <p><strong>Kasir:</strong> <?= sanitize($trx['nama_lengkap']) ?></p>
                <p><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($trx['tanggal_transaksi'])) ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <p><strong>Subtotal:</strong> <?= formatRupiah($trx['subtotal']) ?></p>
                <p><strong>Diskon:</strong> <?= $trx['diskon'] > 0 ? '- '.formatRupiah($trx['diskon']) : 'Rp 0' ?></p>
                <p><strong style="font-size:1.2rem;">Total Bayar:</strong> <span style="color:var(--primary);font-size:1.3rem;"><?= formatRupiah($trx['total_bayar']) ?></span></p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>Kode</th><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
                <tbody>
                    <?php foreach ($details as $d): ?>
                    <tr>
                        <td><?= sanitize($d['kode_produk']) ?></td>
                        <td><?= sanitize($d['nama_produk']) ?></td>
                        <td><?= formatRupiah($d['harga_satuan']) ?></td>
                        <td><?= $d['jumlah'] ?></td>
                        <td><strong><?= formatRupiah($d['subtotal']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add item row
    document.getElementById('addItem')?.addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const row = tbody.querySelector('tr').cloneNode(true);
        tbody.appendChild(row);
        updateRowNumbers();
    });

    // Remove item row
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-item')) {
            const tbody = document.getElementById('itemsBody');
            if (tbody.querySelectorAll('tr').length > 1) {
                e.target.closest('tr').remove();
                updateRowNumbers();
                hitungTotal();
            }
        }
    });

    // Update on change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-produk')) {
            const row = e.target.closest('tr');
            const harga = Number(e.target.options[e.target.selectedIndex]?.dataset.harga) || 0;
            row.querySelector('.item-harga').textContent = formatRupiah(harga);
            row.querySelector('.item-id').value = e.target.value;
            hitungSubtotal(row);
            hitungTotal();
        }
    });
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-jumlah')) {
            hitungSubtotal(e.target.closest('tr'));
            hitungTotal();
        }
        if (e.target.id === 'diskon') hitungTotal();
    });

    function hitungSubtotal(row) {
        const harga = Number(row.querySelector('.item-produk').options[row.querySelector('.item-produk').selectedIndex]?.dataset.harga) || 0;
        const jumlah = parseInt(row.querySelector('.item-jumlah').value) || 0;
        row.querySelector('.item-harga').textContent = formatRupiah(harga);
        row.querySelector('.item-subtotal').innerHTML = '<strong>' + formatRupiah(harga * jumlah) + '</strong>';
    }

    function hitungTotal() {
        let subtotal = 0;
        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const select = row.querySelector('.item-produk');
            const harga = Number(select.options[select.selectedIndex]?.dataset.harga) || 0;
            const jumlah = parseInt(row.querySelector('.item-jumlah').value) || 0;
            subtotal += harga * jumlah;
        });
        const diskon = parseInt(document.getElementById('diskon')?.value) || 0;
        const total = Math.max(0, subtotal - diskon);
        document.getElementById('subtotalDisplay').textContent = formatRupiah(subtotal);
        document.getElementById('diskonDisplay').textContent = '- ' + formatRupiah(diskon);
        document.getElementById('totalDisplay').textContent = formatRupiah(total);
    }

    function updateRowNumbers() {
        document.querySelectorAll('#itemsBody tr').forEach((row, i) => {
            row.querySelector('td').textContent = i + 1;
        });
    }

    function formatRupiah(angka) {
        var num = Number(angka) || 0;
        var parts = num.toFixed(0).split('.');
        var intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'Rp ' + intPart;
    }
});
</script>

<?php include 'includes/footer.php'; ?>

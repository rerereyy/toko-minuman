<?php
require_once 'config.php';

// Filter tanggal
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$tgl_mulai = $bulan . '-01';
$tgl_akhir = date('Y-m-t', strtotime($tgl_mulai));

// Statistik bulanan
$stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total_bayar),0) as pendapatan, COALESCE(SUM(diskon),0) as total_diskon FROM transaksi WHERE DATE(tanggal_transaksi) BETWEEN ? AND ? AND status='selesai'");
$stmt->execute([$tgl_mulai, $tgl_akhir]);
$stat_bulanan = $stmt->fetch();

// Penjualan per kategori
$stmt = $pdo->prepare("
    SELECT k.nama_kategori, SUM(dt.jumlah) as total_qty, SUM(dt.subtotal) as total_omset
    FROM detail_transaksi dt
    JOIN produk pr ON dt.produk_id = pr.id
    JOIN kategori_produk k ON pr.kategori_id = k.id
    JOIN transaksi t ON dt.transaksi_id = t.id
    WHERE DATE(t.tanggal_transaksi) BETWEEN ? AND ? AND t.status='selesai'
    GROUP BY k.id ORDER BY total_omset DESC
");
$stmt->execute([$tgl_mulai, $tgl_akhir]);
$penjualan_kategori = $stmt->fetchAll();

// Penjualan per produk
$stmt = $pdo->prepare("
    SELECT pr.nama_produk, pr.kode_produk, SUM(dt.jumlah) as terjual, SUM(dt.subtotal) as omset
    FROM detail_transaksi dt
    JOIN produk pr ON dt.produk_id = pr.id
    JOIN transaksi t ON dt.transaksi_id = t.id
    WHERE DATE(t.tanggal_transaksi) BETWEEN ? AND ? AND t.status='selesai'
    GROUP BY pr.id ORDER BY terjual DESC LIMIT 10
");
$stmt->execute([$tgl_mulai, $tgl_akhir]);
$penjualan_produk = $stmt->fetchAll();

// Penjualan per metode pembayaran
$stmt = $pdo->prepare("
    SELECT metode_pembayaran, COUNT(*) as jml_trx, SUM(total_bayar) as total
    FROM transaksi WHERE DATE(tanggal_transaksi) BETWEEN ? AND ? AND status='selesai'
    GROUP BY metode_pembayaran
");
$stmt->execute([$tgl_mulai, $tgl_akhir]);
$metode_pembayaran = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-graph-up"></i> Laporan</h4>
</div>

<!-- Filter Bulan -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Bulan</label>
                <input type="month" class="form-control" name="buluan" value="<?= $bulan ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Statistik Bulanan -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-receipt stat-icon"></i>
                <h3 class="mt-2"><?= $stat_bulanan['total'] ?></h3>
                <p class="text-muted mb-0">Total Transaksi</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-cash-stack stat-icon"></i>
                <h3 class="mt-2" style="color:var(--primary)"><?= formatRupiah($stat_bulanan['pendapatan']) ?></h3>
                <p class="text-muted mb-0">Total Pendapatan</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-percent stat-icon"></i>
                <h3 class="mt-2"><?= formatRupiah($stat_bulanan['total_diskon']) ?></h3>
                <p class="text-muted mb-0">Total Diskon</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body text-center">
                <i class="bi bi-calculator stat-icon"></i>
                <h3 class="mt-2"><?= $stat_bulanan['total'] > 0 ? formatRupiah($stat_bulanan['pendapatan'] / $stat_bulanan['total']) : 'Rp 0' ?></h3>
                <p class="text-muted mb-0">Rata-rata/Transaksi</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Penjualan per Kategori -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart"></i> Penjualan per Kategori</div>
            <div class="card-body">
                <?php if (empty($penjualan_kategori)): ?>
                    <p class="text-muted text-center">Belum ada data.</p>
                <?php else: ?>
                    <?php foreach ($penjualan_kategori as $pk): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><span class="badge badge-kategori"><?= sanitize($pk['nama_kategori']) ?></span></span>
                            <span><strong><?= formatRupiah($pk['total_omset']) ?></strong> <small class="text-muted">(<?= $pk['total_qty'] ?> item)</small></span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar" style="width: <?= ($pk['total_omset']/$stat_bulanan['pendapatan']*100) ?:0 ?>%; background:var(--secondary)"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Metode Pembayaran -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-credit-card"></i> Metode Pembayaran</div>
            <div class="card-body">
                <?php if (empty($metode_pembayaran)): ?>
                    <p class="text-muted text-center">Belum ada data.</p>
                <?php else: ?>
                    <?php foreach ($metode_pembayaran as $mp): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded" style="background:#f8f4ef;">
                            <div>
                                <i class="bi bi-<?= $mp['metode_pembayaran']=='cash'?'cash-coin':($mp['metode_pembayaran']=='transfer'?'bank':'credit-card') ?>"></i>
                                <strong class="ms-2"><?= ucfirst($mp['metode_pembayaran']) ?></strong>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-secondary"><?= $mp['jml_trx'] ?> transaksi</span>
                                <strong class="ms-2" style="color:var(--primary)"><?= formatRupiah($mp['total']) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Produk Terlaris -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-trophy"></i> Top 10 Produk Terlaris - <?= date('M Y', strtotime($tgl_mulai)) ?></div>
    <div class="card-body">
        <?php if (empty($penjualan_produk)): ?>
            <p class="text-muted text-center">Belum ada data penjualan bulan ini.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Rank</th><th>Kode</th><th>Produk</th><th>Terjual</th><th>Omset</th></tr></thead>
                    <tbody>
                        <?php foreach ($penjualan_produk as $i => $pp): ?>
                        <tr>
                            <td><span class="badge bg-<?= $i<3?'warning':'secondary' ?>"><?= $i+1 ?></span></td>
                            <td><?= sanitize($pp['kode_produk']) ?></td>
                            <td><strong><?= sanitize($pp['nama_produk']) ?></strong></td>
                            <td><?= $pp['terjual'] ?> item</td>
                            <td><strong style="color:var(--primary)"><?= formatRupiah($pp['omset']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

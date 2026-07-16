<?php
require_once 'config.php';

// Statistik
$total_produk = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$total_pelanggan = $pdo->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn();
$total_transaksi = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status='selesai'")->fetchColumn();
$total_pendapatan = $pdo->query("SELECT COALESCE(SUM(total_bayar), 0) FROM transaksi WHERE status='selesai'")->fetchColumn();
$stok_habis = $pdo->query("SELECT COUNT(*) FROM produk WHERE stok <= 5")->fetchColumn();

// Transaksi terakhir
$recent = $pdo->query("
    SELECT t.*, p.nama_pelanggan, u.nama_lengkap
    FROM transaksi t
    LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
    JOIN users u ON t.user_id = u.id
    ORDER BY t.tanggal_transaksi DESC LIMIT 5
")->fetchAll();

// Produk terlaris
$best_seller = $pdo->query("
    SELECT pr.nama_produk, SUM(dt.jumlah) as total_terjual, SUM(dt.subtotal) as total_omset
    FROM detail_transaksi dt
    JOIN produk pr ON dt.produk_id = pr.id
    JOIN transaksi t ON dt.transaksi_id = t.id
    WHERE t.status = 'selesai'
    GROUP BY pr.id
    ORDER BY total_terjual DESC LIMIT 5
")->fetchAll();

include 'includes/header.php';
?>

<div class="hero-section">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1><i class="bi bi-cup-straw"></i> Selamat Datang, <?= sanitize($_SESSION['nama_lengkap']) ?>!</h1>
            <p class="mb-0">Kelola inventaris dan penjualan minuman dengan mudah.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="transaksi.php?aksi=tambah" class="btn btn-light btn-lg"><i class="bi bi-plus-circle"></i> Transaksi Baru</a>
        </div>
    </div>
</div>

<!-- Statistik -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Produk</h6>
                        <h3 class="mb-0"><?= $total_produk ?></h3>
                    </div>
                    <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Pelanggan</h6>
                        <h3 class="mb-0"><?= $total_pelanggan ?></h3>
                    </div>
                    <div class="stat-icon"><i class="bi bi-people"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Transaksi</h6>
                        <h3 class="mb-0"><?= $total_transaksi ?></h3>
                    </div>
                    <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Pendapatan</h6>
                        <h3 class="mb-0" style="font-size:1.1rem;"><?= formatRupiah($total_pendapatan) ?></h3>
                    </div>
                    <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($stok_habis > 0): ?>
<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> <strong>Perhatian:</strong> <?= $stok_habis ?> produk memiliki stok rendah (≤5).</div>
<?php endif; ?>

<div class="row">
    <!-- Transaksi Terakhir -->
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-clock-history"></i> Transaksi Terakhir</span>
                <a href="transaksi.php" class="btn btn-sm btn-light">Lihat Semua</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent)): ?>
                    <p class="text-muted text-center">Belum ada transaksi.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Kode</th><th>Pelanggan</th><th>Total</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($recent as $r): ?>
                                <tr>
                                    <td><strong><?= sanitize($r['kode_transaksi']) ?></strong></td>
                                    <td><?= sanitize($r['nama_pelanggan'] ?? 'Umum') ?></td>
                                    <td><strong class="text-success"><?= formatRupiah($r['total_bayar']) ?></strong></td>
                                    <td><span class="badge bg-<?= $r['status']=='selesai'?'success':($r['status']=='pending'?'warning':'danger') ?>"><?= ucfirst($r['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Produk Terlaris -->
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-trophy"></i> Produk Terlaris</div>
            <div class="card-body">
                <?php if (empty($best_seller)): ?>
                    <p class="text-muted text-center">Belum ada data penjualan.</p>
                <?php else: ?>
                    <?php foreach ($best_seller as $i => $bs): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="badge bg-secondary"><?= $i+1 ?></span>
                                <?= sanitize($bs['nama_produk']) ?>
                            </div>
                            <div class="text-end">
                                <small class="text-muted"><?= $bs['total_terjual'] ?> terjual</small><br>
                                <strong style="color: var(--primary);"><?= formatRupiah($bs['total_omset']) ?></strong>
                            </div>
                        </div>
                        <hr class="my-1">
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

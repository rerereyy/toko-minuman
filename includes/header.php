<?php
if (!isLoggedIn()) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Minuman - Sistem Manajemen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #7b2d26;
            --primary-dark: #5a1f1a;
            --secondary: #b8860b;
            --accent: #d4a843;
            --dark: #1a1a2e;
            --light-bg: #faf8f5;
            --card-bg: #ffffff;
        }
        * { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            background-color: var(--light-bg);
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark)) !important;
            box-shadow: 0 2px 15px rgba(0,0,0,0.2);
            padding: 0.8rem 1rem;
        }
        .navbar-brand {
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: 1px;
        }
        .navbar-brand i { color: var(--accent); }
        .sidebar {
            min-height: calc(100vh - 60px);
            background: var(--card-bg);
            border-right: 1px solid #e8e0d8;
        }
        .sidebar .nav-link {
            color: var(--dark);
            padding: 0.9rem 1.2rem;
            border-radius: 10px;
            margin: 4px 10px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover {
            background-color: #f5ebe0;
            color: var(--primary);
        }
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            font-weight: 600;
        }
        .sidebar .nav-link i { margin-right: 10px; font-size: 1.1rem; }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: transform 0.2s;
        }
        .card:hover { transform: translateY(-2px); }
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            padding: 1rem 1.25rem;
        }
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .btn-warning {
            background-color: var(--secondary);
            border-color: var(--secondary);
            color: white;
        }
        .btn-warning:hover {
            background-color: #9a7209;
            border-color: #9a7209;
            color: white;
        }
        .table thead th {
            background-color: var(--primary);
            color: white;
            border: none;
            font-weight: 600;
        }
        .stat-card {
            border-left: 4px solid var(--accent);
            border-radius: 15px;
        }
        .stat-card .stat-icon {
            font-size: 2.5rem;
            color: var(--accent);
        }
        .hero-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .badge-kategori {
            background-color: var(--secondary);
            color: white;
            font-weight: 500;
        }
        .text-primary-custom { color: var(--primary) !important; }
        @media (max-width: 768px) {
            .sidebar { min-height: auto; }
            .stat-card { margin-bottom: 1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-cup-straw"></i> TOKO MINUMAN
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="bi bi-person-circle"></i>
                            <?= sanitize($_SESSION['nama_lengkap'] ?? 'User') ?>
                            <span class="badge bg-warning ms-1"><?= sanitize($_SESSION['role'] ?? '') ?></span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar py-3">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'produk.php' ? 'active' : '' ?>" href="produk.php">
                                <i class="bi bi-box-seam"></i> Produk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'kategori.php' ? 'active' : '' ?>" href="kategori.php">
                                <i class="bi bi-tags"></i> Kategori
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'pelanggan.php' ? 'active' : '' ?>" href="pelanggan.php">
                                <i class="bi bi-people"></i> Pelanggan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'transaksi.php' ? 'active' : '' ?>" href="transaksi.php">
                                <i class="bi bi-receipt"></i> Transaksi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>" href="laporan.php">
                                <i class="bi bi-graph-up"></i> Laporan
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : '' ?>" href="profil.php">
                                <i class="bi bi-person-gear"></i> Profil
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

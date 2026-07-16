<?php
session_start();

// Konfigurasi Database
$host = 'localhost';
$dbname = 'toko_minuman_db';
$username = 'root';
$password_db = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi Helper
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = isset($_SESSION['flash'][$key]) ? $_SESSION['flash'][$key] : null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function generateKode($prefix, $table, $column) {
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM $table");
    $row = $stmt->fetch();
    $next = ($row['max_id'] ?? 0) + 1;
    return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
}
?>

<?php
session_start();
include 'conn.php';

// Ambil nama layanan dari POST
$nama = $_POST['nama'] ?? '';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    // Belum login → redirect ke login
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Jika user sudah login dan data ada, simpan lalu redirect ke WA
if (!empty($nama) && !empty($username)) {
    $stmt = $conn->prepare("INSERT INTO activity (nama, username) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("ss", $nama, $username);
        $stmt->execute();
        $stmt->close();
    }
    
    // Redirect ke WhatsApp
    $text = urlencode("Halo, saya ingin booking $nama. Apakah masih tersedia?");
    $url = "https://wa.me/6281353638858?text=$text";
    header("Location: $url");
    exit;
} else {
    // Jika data tidak valid, kembali ke harga
    header("Location: harga.php");
    exit;
}
?>

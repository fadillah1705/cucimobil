<?php
session_start();
include 'conn.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$layan = $_POST['layanan'] ?? '';

// Ambil nama lengkap user dari database
$stmt = $conn->prepare("SELECT nama_lengkap FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$nama = !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : $username;

// Validasi data
if (!empty($nama) && !empty($username) && !empty($layan)) {
    // Simpan ke activity
    $stmt = $conn->prepare("INSERT INTO activity (nama, layanan) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("ss", $nama, $layan);
        if (!$stmt->execute()) {
            die("Gagal menyimpan aktivitas: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Prepare gagal: " . $conn->error);
    }

    // Redirect ke WhatsApp
    $text = urlencode("Halo, saya ingin booking layanan $layan. Apakah masih tersedia?");
    header("Location: https://wa.me/6281353638858?text=$text");
    exit;
} else {
    header("Location: harga.php");
    exit;
}
?>

<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] === 'guest') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? '';
$layan = $_POST['layanan'] ?? '';
$nama = $username; // Default fallback

// Jika bukan tamu, ambil nama lengkap dari database
if ($role !== 'guest') {
    $stmt = $conn->prepare("SELECT nama_lengkap FROM mencuci WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $nama = !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : $username;
}

// Validasi data
if (!empty($nama) && !empty($username) && !empty($layan)) {
    // Simpan ke tabel booking
    $stmt = $conn->prepare("INSERT INTO booking (nama, layanan) VALUES (?, ?)");
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


<?php
ob_start();
session_start();
include 'conn.php';

if (!isset($_SESSION['user_id'])) {
    // Belum login, redirect ke halaman login
    header("Location: login.php");
    exit;
}

$nama = $_POST['nama'];
$username =$_POST['username'];
$url = "https://wa.me/6281353638858?text=Halo%2C%20saya%20ingin%20booking%20cuci%20interior.%20Apakah%20masih%20tersedia%3F";

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Insert hanya kolom yang diperlukan (jangan termasuk id!)
$stmt = $conn->prepare("INSERT INTO activity (nama, username) VALUES (?, ?)");
if (!$stmt) {
    die("Prepare gagal: " . $conn->error);
}
$stmt->bind_param("ss", $nama, $username);
$stmt->execute();



// Redirect ke WhatsApp
header("Location: $url");
exit;
ob_end_flush();
?>

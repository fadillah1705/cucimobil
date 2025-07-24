<?php
session_start();
include 'conn.php';

// "Jika user belum login ATAU dia login sebagai tamu, maka..."


if (!isset($_SESSION['username']) || $_SESSION['role'] === 'guest') {
    header("Location: login.php");
    exit;
}
// Kode ini digunakan untuk mengambil data dari sesi dan form POST 
$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? '';
$layan = $_POST['layanan'] ?? '';
$nama = $username; // Default fallback

// cek a[akah user bukan amu]
if ($role !== 'guest') {
    // mengambil data nama lengkap dari database berdasarkan username yang diberikan.
    $stmt = $conn->prepare("SELECT nama_lengkap FROM mencuci WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    // mengambil hasil dari query yang sudah dieksekusi dengan prepare + execute, khususnya saat kamu menggunakan SELECT.
    $result = $stmt->get_result();
    // mengambil satu baris data dari hasil query
    // Array asosiatif adalah array yang menggunakan key atau kunci sebagai penanda setiap nilainya, bukan angka berurutan seperti array biasa.
    $user = $result->fetch_assoc();


    //  Cek apakah nama_lengkap dari database kosong atau tidak
    // Jika tidak kosong → pakai nama_lengkap
    // Jika kosong → pakai username sebagai gantinya
    $nama = !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : $username;
}

// Validasi data
//  Kondisi ini akan dijalankan hanya jika ketiga variabel ($nama, $username, dan $layan) tidak kosong.
if (!empty($nama) && !empty($username) && !empty($layan)) {
    //  menambahkan data baru ke tabel booking, tepatnya ke kolom nama dan layanan.
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
    header("Location: https://wa.me/6281218352273?text=$text");
    exit;
} else {
    header("Location: harga.php");
    exit;
}
?>


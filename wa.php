<?php
session_start();
include 'conn.php'; // Pastikan file koneksi database sudah benar

// Redirect jika user belum login atau role adalah 'guest'
if (!isset($_SESSION['username']) || $_SESSION['role'] === 'guest') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? '';
$nama_layanan = $_POST['layanan'] ?? ''; // Mengambil nama layanan dari form POST
$waktu_booking = $_POST['waktu'] ?? date('H:i'); // Ambil waktu dari form, default waktu saat ini
$tanggal_booking = $_POST['tanggal'] ?? date('Y-m-d'); // Ambil tanggal dari form, default tanggal saat ini

$pelanggan_id = null;
$id_layanan = null;

// Ambil ID pelanggan dari tabel users
if ($role !== 'guest') {
    try {
        $stmt_user = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_user->execute([$username]);
        $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $pelanggan_id = $user_data['id'];
        } else {
            // Handle case where user is not found, though theoretically shouldn't happen if session is set
            echo "<script>alert('User tidak ditemukan di database.'); window.location.href='harga.php';</script>";
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error fetching user ID: " . $e->getMessage());
        echo "<script>alert('Terjadi kesalahan saat mengambil data pengguna.'); window.location.href='harga.php';</script>";
        exit;
    }
}

// Ambil ID layanan dari tabel layanan berdasarkan nama layanan
if (!empty($nama_layanan)) {
    try {
        $stmt_layanan = $pdo->prepare("SELECT id FROM layanan WHERE nama = ?");
        $stmt_layanan->execute([$nama_layanan]);
        $layanan_data = $stmt_layanan->fetch(PDO::FETCH_ASSOC);

        if ($layanan_data) {
            $id_layanan = $layanan_data['id'];
        } else {
            echo "<script>alert('Layanan tidak valid.'); window.location.href='harga.php';</script>";
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error fetching service ID: " . $e->getMessage());
        echo "<script>alert('Terjadi kesalahan saat mengambil data layanan.'); window.location.href='harga.php';</script>";
        exit;
    }
}

// Validasi data sebelum insert
if ($pelanggan_id !== null && $id_layanan !== null && !empty($waktu_booking) && !empty($tanggal_booking)) {
    try {
        // Masukkan data booking ke tabel booking
        // Kolom status akan otomatis default 'Menunggu'
        $stmt_booking = $pdo->prepare("INSERT INTO booking (pelanggan_id, id_layanan, waktu, tanggal, status) VALUES (?, ?, ?, ?, 'Menunggu')");
        
        if ($stmt_booking->execute([$pelanggan_id, $id_layanan, $waktu_booking, $tanggal_booking])) {
            // Redirect ke WhatsApp
            $text = urlencode("Halo, saya ingin booking layanan " . $nama_layanan . " untuk tanggal " . $tanggal_booking . " pukul " . $waktu_booking . ". Apakah masih tersedia?");
            header("Location: https://wa.me/6281218352273?text=$text");
            exit;
        } else {
            echo "<script>alert('Gagal membuat booking: " . $stmt_booking->errorInfo()[2] . "'); window.location.href='harga.php';</script>";
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error inserting booking: " . $e->getMessage());
        echo "<script>alert('Terjadi kesalahan sistem saat menyimpan booking.'); window.location.href='harga.php';</script>";
        exit;
    }
} else {
    // Jika ada data yang kurang
    echo "<script>alert('Data booking tidak lengkap. Mohon lengkapi semua informasi.'); window.location.href='harga.php';</script>";
    exit;
}
?>
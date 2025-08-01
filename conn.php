<?php
// conn.php - Menyediakan koneksi MySQLi ($conn) dan PDO ($pdo)

// --- Konfigurasi Database (Ganti sesuai lingkungan Anda) ---
$servername = "localhost";
$username_db = "root";
$password_db = "";

$dbname = "cucimobil";

// --- Koneksi MySQLi (untuk profil.php dan mungkin file lain yang menggunakannya) ---
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Cek koneksi MySQLi
if ($conn->connect_error) {
    // Lebih baik log error di lingkungan produksi daripada menampilkan pesan ke user
    error_log("Koneksi MySQLi gagal: " . $conn->connect_error);
    // Kita tidak akan die() di sini agar koneksi PDO tetap bisa dicoba
}

// --- Koneksi PDO (untuk tab_booking.php dan file lain yang menggunakannya) ---
$dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE             => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES    => false,
];

try {
    $pdo = new PDO($dsn, $username_db, $password_db, $options);
} catch (\PDOException $e) {
    // Log error PDO
    error_log("Koneksi PDO gagal: " . $e->getMessage());
    // Ubah baris di bawah ini untuk sementara waktu agar Anda bisa melihat pesan error spesifik
    // Setelah selesai diagnosis, ganti kembali ke pesan yang lebih umum
    die("Terjadi kesalahan koneksi database (PDO). Pesan Error: " . $e->getMessage());
}

?>
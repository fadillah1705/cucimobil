<?php
include 'koneksi.php';
// Memulai sesi PHP
session_start();
// Ini adalah kondisi pengecekan login dan role:
// !isset($_SESSION['username']) → jika user belum login
// $_SESSION['role'] !== 'admin' → jika user login tapi bukan admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  // Pengguna akan dialihkan ke halaman login
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Booking</title>
  <style>
    table { width: 100%; border-collapse: collapse; margin-top: 30px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
    th { background-color: #f4f4f4; }
  </style>
</head>
<body>
  <h2>Data Booking Pelanggan</h2>

  <table>
    <tr>
      <th>No</th>
      <th>Nama</th>
      <th>Layanan</th>
      <th>Waktu</th>
    </tr>

    <?php
    // mengambil/melihat semua data dari tabel booking
    $result = mysqli_query($conn, "SELECT * FROM booking ORDER BY waktu DESC");
    // menampilkan no urut, dari no 1 jadi nanti seterusnya akan manual 2,3,4,..
    $no = 1;
    // Memulai perulangan untuk mengambil baris demi baris dari hasil query ke database.
    // Setiap baris disimpan ke $row, dan bisa diakses dengan nama kolom seperti $row['nama'].
   // Perulangan akan berhenti otomatis saat data habis.
    while ($row = mysqli_fetch_assoc($result)) {
      // mengambil data dari database,sesuai yg di isi pengguna di tabel booking
      echo "<tr>
              <td>{$no}</td>
              <td>{$row['nama']}</td>
              <td>{$row['layanan']}</td>
              <td>{$row['waktu']}</td>
            </tr>";
      // Menambahkan nilai $no sebanyak 1 untuk baris berikutnya.
      $no++;
    }
    ?>
  </table>
</body>
</html>

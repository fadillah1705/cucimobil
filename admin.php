<?php
include 'koneksi.php';
// Memulai sesi PHP
session_start();

 $_SESSION['role'] !== 'admin' → jika user login tapi bukan admin


if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  // maka akan di arahkan ke halaman login
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin - Data Booking</title>
  <style>


    h2 {
    text-align: center;
    color: rgb(58, 159, 167);
    font-weight: bold;
    margin-bottom: 30px;
  }

  /* Tabel */
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    
  }

  th, td {
    padding: 14px 16px;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
    font-size: 15px;
  }

  th {
    background-color: rgb(58, 159, 167);
    color: white;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  tr:hover {
    background-color: #f1f1f1;
    transition: background-color 0.3s ease;
  }

  /* Tombol Logout */
  .logout-link {
    display: inline-block;
    margin-top: 25px;
    background-color: rgb(58, 159, 167);
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: background-color 0.3s ease;
<<<<<<< HEAD

=======
>>>>>>> ed0c355d6852244f1b215bb0dcc4b411ff745ff8
  }

  .logout-link:hover {
    background-color: rgb(45, 134, 140);
  }
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

<<<<<<< HEAD

$result = mysqli_query($conn, "SELECT * FROM booking ORDER BY waktu ASC")
          or die("Query Error: " . mysqli_error($conn));
    // mengambil/melihat semua data dari tabel booking
    $result = mysqli_query($conn, "SELECT * FROM booking ORDER BY waktu DESC");
    // menampilkan no urut, dari no 1 jadi nanti seterusnya akan manual 2,3,4,..

    $query = "SELECT * FROM booking ORDER BY waktu DESC";
    $result = mysqli_query($conn, $query) or die("Query Error: " . mysqli_error($conn));

=======
// Query: Ambil semua data dari tabel booking, diurutkan berdasarkan waktu dari terbaru ke terlama (DESC)
$query = "SELECT * FROM booking ORDER BY waktu DESC";
$result = mysqli_query($conn, $query) or die("Query Error: " . mysqli_error($conn));

// kamu memberikan nilai awal 1..
>>>>>>> ed0c355d6852244f1b215bb0dcc4b411ff745ff8
    $no = 1;
// Melakukan perulangan (while) selama masih ada baris data dalam hasil query ($result).
// while adalah  mengulang kode selama suatu kondisi bernilai true (benar).
    while ($row = mysqli_fetch_assoc($result)) {
      // menampilkan data dari database ke dalam tabel HTML
      echo "<tr>
              <td>{$no}</td>
              <td>{$row['nama']}</td>
              <td>{$row['layanan']}</td>
              <td>{$row['waktu']}</td>
            </tr>";
// operator increment.
// Artinya: tambahkan 1 ke nilai $no setelah baris ini dijalankan.
      $no++;
    }
    ?>
  </table>
<<<<<<< HEAD
=======

>>>>>>> ed0c355d6852244f1b215bb0dcc4b411ff745ff8

  <div class="logout-link ">
    <a href="logout.php">Logout</a>
  </div>
<<<<<<< HEAD
=======


>>>>>>> ed0c355d6852244f1b215bb0dcc4b411ff745ff8
</body>
</html>

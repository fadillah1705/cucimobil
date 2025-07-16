<?php
include 'koneksi.php';
// Memulai sesi PHP
session_start();
// Ini adalah kondisi pengecekan login dan role:
// !isset($_SESSION['username']) → jika user belum login
// $_SESSION['role'] !== 'admin' → jika user login tapi bukan admin

session_start();
include 'conn.php';

// Cek login dan role admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
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
  
    
>>>>>>> 4c2a7c7 (mengubah profil, dll)
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

$result = mysqli_query($conn, "SELECT * FROM activity ORDER BY waktu ASC")
          or die("Query Error: " . mysqli_error($conn));
    // mengambil/melihat semua data dari tabel booking
    $result = mysqli_query($conn, "SELECT * FROM activity ORDER BY waktu DESC");
    // menampilkan no urut, dari no 1 jadi nanti seterusnya akan manual 2,3,4,..
=======
    $query = "SELECT * FROM booking ORDER BY waktu DESC";
    $result = mysqli_query($conn, $query) or die("Query Error: " . mysqli_error($conn));
>>>>>>> 4c2a7c7 (mengubah profil, dll)
    $no = 1;

    while ($row = mysqli_fetch_assoc($result)) {
      echo "<tr>
              <td>{$no}</td>
              <td>{$row['nama']}</td>
              <td>{$row['layanan']}</td>
              <td>{$row['waktu']}</td>
            </tr>";
      $no++;
    }
    ?>
  </table>
<<<<<<< HEAD
     <li><a class="dropdown-item" href="logout.php">Logout</a></li>
=======

  <div class="logout-link ">
    <a href="logout.php">Logout</a>
  </div>

>>>>>>> 4c2a7c7 (mengubah profil, dll)
</body>
</html>

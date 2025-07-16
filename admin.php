<?php
include 'conn.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
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
$result = mysqli_query($conn, "SELECT * FROM activity ORDER BY waktu ASC")
          or die("Query Error: " . mysqli_error($conn));

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
      <li><a class="dropdown-item" href="logout.php">Logout</a></li>

</body>
</html>

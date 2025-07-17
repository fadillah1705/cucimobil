<?php


include 'conn.php';
// Memulai sesi PHP
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
  // Pengguna akan dialihkan ke halaman login
  
  header("Location: login.php");
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $nama = $_POST['nama_pelanggan'] ?? '';
    $layanan = $_POST['nama_layanan'] ?? '';
    $jam = $_POST['jam_layanan'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';

    if ($nama && $layanan && $jam && $tanggal) {
        $stmt = $conn->prepare("INSERT INTO booking (nama_pelanggan, nama_layanan, jam_layanan, tanggal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $layanan, $jam, $tanggal);

        if ($stmt->execute()) {
            echo json_encode(["status" => "sukses"]);
        } else {
            echo json_encode(["status" => "gagal", "error" => $stmt->error]);
        }
    } else {
        echo json_encode(["status" => "gagal", "error" => "Data tidak lengkap"]);
    }

    // ⛔ INI PENTING: HENTIKAN SCRIPT AGAR HTML TIDAK DIKIRIM KE CLIENT
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
$result = mysqli_query($conn, "SELECT * FROM activity ORDER BY waktu ASC")
          or die("Query Error: " . mysqli_error($conn));

    

 <?php

// Query: Ambil semua data dari tabel booking, diurutkan berdasarkan waktu dari terbaru ke terlama (DESC)
$query = "SELECT * FROM booking ORDER BY waktu DESC";
$result = mysqli_query($conn, $query) or die("Query Error: " . mysqli_error($conn));

// kamu memberikan nilai awal 1..

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

//     

echo "OK";
    ?>
  </table>
 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kalender Booking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <style>
    #calendar {
      max-width: 900px;
      margin: 40px auto;
    }
    #bookingModal {
      position: fixed;
      top: 30%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      border: 1px solid #ccc;
      padding: 20px;
      z-index: 1000;
      display: none;
    }
    #overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 999;
      display: none;
    }
  </style>
</head>
<body>
<h1>Kalender Booking Pelanggan</h1>
<div id='calendar'></div>

<div id="overlay"></div>

<!-- Modal Booking -->
<div id="bookingModal">
  <form id="bookingForm">
    <h3>Booking Layanan</h3>
    <p id="bookingDateText"></p>
    <input type="hidden" id="selectedDate">

    <label>Nama Pelanggan:</label><br>
    <input type="text" id="namaPelanggan" required><br><br>

    <label>Nama Layanan:</label><br>
    <input type="text" id="namaLayanan" required><br><br>

    <label>Jam Layanan:</label><br>
    <input type="time" id="jamLayanan" required><br><br>

    <button type="submit">Simpan</button>
    <button type="button" onclick="closeModal()">Batal</button>
  </form>
</div>
<h1>Tabel Booking Pelanggan</h1>
 <table>
    <tr>
      <th>No</th>
      <th>Nama</th>
      <th>Layanan</th>
      <th>Waktu</th>
    </tr>
    <?php
// Ambil data booking dari database
$events = [];
$result = mysqli_query($conn, "SELECT * FROM booking");
while ($row = mysqli_fetch_assoc($result)) {
    $events[] = [
        'title' => $row['nama_layanan'] . ' - ' . $row['jam_layanan'] . ' (' . $row['nama_pelanggan'] . ')',
        'start' => $row['tanggal']
    ];
}
?>

    <?php
    $result = mysqli_query($conn, "SELECT * FROM booking ORDER BY tanggal ASC")
              or die("Query Error: " . mysqli_error($conn));
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
      echo "<tr>
              <td>{$no}</td>
              <td>{$row['nama_pelanggan']}</td>
              <td>{$row['nama_layanan']}</td>
              <td>{$row['tanggal']} {$row['jam_layanan']}</td>
            </tr>";
      $no++;
    }
    ?>
  </table>

  <div id="eventPopup" style="display:none; position:fixed; top:20%; left:30%; background:#fff; border:1px solid #ccc; padding:20px; z-index:1000;">
  <h3>Detail Booking</h3>
  <p id="popupContent"></p>
  <button onclick="closePopup()">Tutup</button>
</div>
<div id="popupOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999;" onclick="closePopup()"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    selectable: true,
    events: <?= json_encode($events) ?>,
    dateClick: function(info) {
      document.getElementById('selectedDate').value = info.dateStr;
      document.getElementById('bookingDateText').textContent = "Tanggal: " + info.dateStr;
      openModal();
    },
    eventClick: function(info) {
    const title = info.event.title; // Misal: "exterior - 14:36 (riyad)"
    const date = info.event.startStr;
const content = `${title}\nTanggal: ${date}`;
  showPopup(content);
    }
  });

  

  calendar.render();

  document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const tanggal = document.getElementById('selectedDate').value;
    const namaPelanggan = document.getElementById('namaPelanggan').value;
    const namaLayanan = document.getElementById('namaLayanan').value;
    const jam = document.getElementById('jamLayanan').value;

    // Kirim data ke server
    fetch('admin.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        nama_pelanggan: namaPelanggan,
        nama_layanan: namaLayanan,
        jam_layanan: jam,
        tanggal: tanggal
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'sukses') {
        calendar.addEvent({
          title: `${namaLayanan} - ${jam} (${namaPelanggan})`,
          start: tanggal
        });
        closeModal();
        document.getElementById('bookingForm').reset();
      } else {
        alert("Gagal menyimpan data: " + data.error);
      }
    })
    .catch(error => {
      alert("Terjadi kesalahan: " + error);
    });
  });
});

function openModal() {
  document.getElementById('bookingModal').style.display = 'block';
  document.getElementById('overlay').style.display = 'block';
}

function closeModal() {
  document.getElementById('bookingModal').style.display = 'none';
  document.getElementById('overlay').style.display = 'none';
}
function showPopup(content) {
  document.getElementById('popupContent').innerText = content;
  document.getElementById('eventPopup').style.display = 'block';
  document.getElementById('popupOverlay').style.display = 'block';
}

function closePopup() {
  document.getElementById('eventPopup').style.display = 'none';
  document.getElementById('popupOverlay').style.display = 'none';
}

</script>


</body>
</html>

      <li><a class="dropdown-item" href="logout.php">Logout</a></li


  <div class="logout-link ">
    <a href="logout.php">Logout</a>
  </div>

</body>
</html>

<?php
session_start();
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = $_POST['nama_pelanggan'];
    $layanan = $_POST['nama_layanan'];
    $jam = $_POST['jam_layanan'];
    $tanggal = $_POST['tanggal'];

    $sql = "INSERT INTO emsit_cucimobil (nama_pelanggan, nama_layanan, jam_layanan, tanggal)
            VALUES ('$nama', '$layanan', '$jam', '$tanggal')";

    header('Content-Type: application/json'); // WAJIB supaya browser tahu ini JSON

    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "sukses"]);
    } else {
        echo json_encode(["status" => "gagal", "error" => mysqli_error($conn)]);
    }

    exit; // stop script agar tidak lanjut ke echo <tr> dan lainnya
}


// Mengecek apakah user belum login (tidak ada sesi username),$_SESSION['role'] !== 'admin'
// ➜ Mengecek apakah user yang login bukan admin
// Jika salah satu kondisi benar (belum login atau bukan admin), maka:
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  // maka akan di arahkan ke halaman login
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
    /* Kalender */
#calendar {
  max-width: 900px;
  margin: 40px auto;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Modal Booking */
#bookingModal {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #ffffff;
  border-radius: 12px;
  padding: 25px 30px;
  z-index: 1000;
  display: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 400px;
}

/* Overlay */
#overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.6);
  z-index: 999;
  display: none;
}

/* Form Input */
#bookingModal input,
#bookingModal select {
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
  box-sizing: border-box;
}

/* Tombol Submit */
#bookingModal button[type="submit"] {
  background-color: #007bff;
  color: white;
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  width: 100%;
  font-weight: bold;
  transition: background-color 0.3s;
}

#bookingModal button[type="submit"]:hover {
  background-color: #0056b3;
}

/* Popup Event Detail */
#eventPopup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  padding: 20px 25px;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  z-index: 1001;
  display: none;
  max-width: 400px;
}

#popupOverlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.6);
  z-index: 1000;
  display: none;
}

/* tombol batal */
#bookingModal button[type="button"] {
  background-color: #dc3545; /* merah lembut */
  color: white;
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  width: 100%;
  font-weight: bold;
  margin-top: 10px;
  transition: background-color 0.3s;
}

#bookingModal button[type="button"]:hover {
  background-color: #b52a38; /* lebih gelap saat hover */
}

/* Responsive Fix */
@media (max-width: 600px) {
  #bookingModal, #eventPopup {
    width: 90%;
  }
}

/* detail booking */
#eventPopup {
  display: none;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #ffffff;
  border: 1px solid #ccc;
  border-radius: 12px;
  padding: 24px;
  z-index: 1000;
  width: 380px;
  max-width: 90%;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  font-family: 'Segoe UI', sans-serif;
  text-align: center;
}

#eventPopup h3 {
  margin-top: 0;
  color: #333;
  font-size: 1.5rem;
}

#popupContent {
  margin: 16px 0;
  color: #555;
  font-size: 1.1rem;
  white-space: pre-line; /* agar bisa enter dari \n */
}

#eventPopup button {
  background-color: #007bff;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 10px 18px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

#eventPopup button:hover {
  background-color: #0056b3;
}

#popupOverlay {
  display: none;
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
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
$result = mysqli_query($conn, "SELECT * FROM emsit_cucimobil");
while ($row = mysqli_fetch_assoc($result)) {
    $events[] = [
        'title' => $row['nama_layanan'] . ' - ' . $row['jam_layanan'] . ' (' . $row['nama_pelanggan'] . ')',
        'start' => $row['tanggal']
    ];
}
?>

    <?php
    $result = mysqli_query($conn, "SELECT * FROM emsit_cucimobil ORDER BY tanggal ASC")
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


  <div class="logout-link ">
    <a href="logout.php">Logout</a>
  </div>


</body>
</html>

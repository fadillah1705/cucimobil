<?php
session_start();
include 'conn.php';


// ➜ Mengecek apakah user yang login bukan admin
// "Kalau user belum login, atau sudah login tapi bukan admin, maka :
// !isset($_SESSION['username'])	: Apakah user belum login? OR II
// $_SESSION['role'] !== 'admin'	Apakah user bukan admin (misalnya dia cuma user biasa)?
// ika salah satu kondisi benar (belum login atau bukan admin), maka:
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  // maka akan di arahkan ke halaman login
  header("Location: login.php");
  exit;
}


// bagian awal dari proses menangkap data dari form yang dikirim menggunakan metode POST.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // pengambilan data dari form HTML yang dikirim menggunakan metode POST
    $nama = $_POST['nama_pelanggan'];
    $layanan = $_POST['nama_layanan'];
    $jam = $_POST['jam_layanan'];
    $tanggal = $_POST['tanggal'];

    // Untuk menambahkan data booking ke dalam tabel emsit_cucimobil di database.
    $sql = "INSERT INTO emsit_cucimobil (nama_pelanggan, nama_layanan, jam_layanan, tanggal)
            VALUES ('$nama', '$layanan', '$jam', '$tanggal')";

// berfungsi untuk memberitahu browser atau client bahwa konten yang dikirim dari server adalah dalam format JSON (JavaScript Object Notation).
    header('Content-Type: application/json'); 

    // Untuk menjalankan perintah SQL yang ada di variabel $sql ke database menggunakan koneksi $conn, dan memeriksa apakah perintah tersebut berhasil dijalankan.
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "sukses"]);
    } else {
        echo json_encode(["status" => "gagal", "error" => mysqli_error($conn)]);
    }

    exit; // stop script agar tidak lanjut ke echo <tr> dan lainnya
}

// Ambil daftar layanan dari database
$layananOptions = [];
$layananQuery = mysqli_query($conn, "SELECT nama_layanan FROM layanan");
while ($layananRow = mysqli_fetch_assoc($layananQuery)) {
    $layananOptions[] = $layananRow['nama_layanan'];
}

// Ambil daftar nama pelanggan dari tabel booking
// DISTINCT artinya: ambil hanya nilai nama yang berbeda, jadi jika Emsit Patyradja muncul 3x di tabel, maka tetap cuma akan diambil 1 kali saja di dropdown.
$pelangganOptions = [];
$pelangganQuery = mysqli_query($conn, "SELECT DISTINCT nama FROM booking ORDER BY nama ASC");
while ($pelangganRow = mysqli_fetch_assoc($pelangganQuery)) {
    $pelangganOptions[] = $pelangganRow['nama'];
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


    <!-- UNTUK DATA BOOKING PELANGGAN 1 -->
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
<select id="namaPelanggan" required>
  <option value="">-- Pilih Nama Pelanggan --</option>
  <?php foreach ($pelangganOptions as $pelanggan): ?>
    <option value="<?= htmlspecialchars($pelanggan) ?>"><?= htmlspecialchars($pelanggan) ?></option>
  <?php endforeach; ?>
</select><br><br>


    <label>Nama Layanan:</label><br>
<select id="namaLayanan" required>
  <option value="">-- Pilih Layanan --</option>
  <?php foreach ($layananOptions as $layanan): ?>
    <option value="<?= htmlspecialchars($layanan) ?>"><?= htmlspecialchars($layanan) ?></option>
  <?php endforeach; ?>
</select><br><br>


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
// Membuat sebuah array kosong bernama $events yang nantinya akan digunakan untuk menyimpan kumpulan data, misalnya data booking atau event.
$events = [];
// Menjalankan perintah SQL SELECT untuk mengambil semua data (*) dari tabel emsit_cucimobil pada database yang sudah terkoneksi melalui variabel $conn.
$result = mysqli_query($conn, "SELECT * FROM emsit_cucimobil");
// Melakukan perulangan untuk mengambil satu per satu baris data dari hasil query $result, sampai tidak ada lagi data yang tersisa.
while ($row = mysqli_fetch_assoc($result)) {
  // Menambahkan elemen baru ke dalam array $events. Elemen ini adalah sebuah array asosiatif yang berisi informasi untuk event booking.
    $events[] = [
        'title' => $row['nama_layanan'] . ' - ' . $row['jam_layanan'] . ' (' . $row['nama_pelanggan'] . ')',
        'start' => $row['tanggal']
    ];
}
?>


<!-- DATA BOOKING PELANGGAN 2 -->
    <?php
// Baris ini berguna untuk mengambil seluruh data booking dari database secara urut berdasarkan tanggal, supaya bisa ditampilkan dengan rapi dan sesuai urutan waktu.
    $result = mysqli_query($conn, "SELECT * FROM emsit_cucimobil ORDER BY tanggal ASC")
              or die("Query Error: " . mysqli_error($conn));
//Variabel ini digunakan untuk menampilkan nomor baris secara berurutan 
    $no = 1;
    // while: adalah perulangan yang terus berjalan selama kondisinya benar.
    // Melakukan perulangan untuk membaca semua data hasil query dari database, baris demi baris, sampai tidak ada lagi data.
    while ($row = mysqli_fetch_assoc($result)) {
      echo "<tr>
              <td>{$no}</td>
              <td>{$row['nama_pelanggan']}</td>
              <td>{$row['nama_layanan']}</td>
              <td>{$row['tanggal']} {$row['jam_layanan']}</td>
            </tr>";
            
 // $no++ adalah increment operator dalam PHP.
// Artinya: naikkan nilai variabel $no sebanyak 1.
      $no++;
    }
    ?>
  </table>

<div id="eventPopup" style="display:none; position:fixed; top:20%; left:30%; background:#fff; border:1px solid #ccc; padding:20px; z-index:1000; width: 300px; box-shadow: 0 0 10px rgba(0,0,0,0.3); border-radius: 8px;">
  <h3>Detail Booking</h3>
  <p id="popupContent" style="white-space: pre-line;"></p>

  <!-- Tombol Aksi -->
  <div style="margin-top: 20px; display: flex; justify-content: space-between;">
    <button onclick="closePopup()" style="padding: 5px 15px; background-color: #aaa; border: none; border-radius: 5px; color: white;">Tutup</button>
    <button onclick="showEditPrompt()" style="padding: 5px 15px; background-color: #61d94fff; border: none; border-radius: 5px; color: white;">Edit</button>
    <button onclick="deleteEvent()" style="padding: 5px 15px; background-color: #d9534f; border: none; border-radius: 5px; color: white;">Hapus</button>
  </div>
</div>

<!-- Overlay -->
<div id="popupOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999;" onclick="closePopup()"></div>

<script>
let selectedEvent = null; // Menyimpan event yang diklik

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    selectable: true,
    events: function(fetchInfo, successCallback, failureCallback) {
      const deletedEvents = JSON.parse(localStorage.getItem('deletedEvents') || '[]');
      const allEvents = <?= json_encode($events) ?>;

      const filtered = allEvents.filter(e => {
        return !deletedEvents.some(d =>
          d.title === e.title && d.date === e.start
        );
      });

      successCallback(filtered);
    },

    dateClick: function(info) {
      document.getElementById('selectedDate').value = info.dateStr;
      document.getElementById('bookingDateText').textContent = "Tanggal: " + info.dateStr;
      openModal();
    },

    eventClick: function(info) {
      selectedEvent = info.event;
      const title = info.event.title;
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

        // Tambahan notifikasi sukses
        Swal.fire({
          title: "Selesai!",
          text: "Data booking berhasil disimpan!",
          icon: "success"
        });

      } else {
        Swal.fire({
          title: "Gagal!",
          text: "Gagal menyimpan data: " + data.error,
          icon: "error"
        });
      }
    });
  }); // <-- Penutup .addEventListener untuk bookingForm

  // Fungsi modal buka/tutup
  function openModal() {
    document.getElementById('bookingModal').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
  }

  window.closeModal = function () {
  document.getElementById('bookingModal').style.display = 'none';
  document.getElementById('overlay').style.display = 'none';
}

  function showPopup(content) {
    document.getElementById('popupContent').innerText = content;
    document.getElementById('eventPopup').style.display = 'block';
    document.getElementById('popupOverlay').style.display = 'block';
  }

window.closePopup = function () {
  document.getElementById('eventPopup').style.display = 'none';
  document.getElementById('popupOverlay').style.display = 'none';
  selectedEvent = null;
}

  // Fungsi untuk menghapus event
  window.deleteEvent = function () {
    if (selectedEvent) {
      const deletedEvents = JSON.parse(localStorage.getItem('deletedEvents') || '[]');

      deletedEvents.push({
        title: selectedEvent.title,
        date: selectedEvent.startStr
      });

      localStorage.setItem('deletedEvents', JSON.stringify(deletedEvents));

      selectedEvent.remove();
      closePopup();

      Swal.fire({
        title: "Selesai!",
        text: "Jadwal berhasil dihapus dari kalender!",
        icon: "success"
      });
    }
  }

  // Fungsi untuk mengedit jam layanan
  window.showEditPrompt = function () {
    if (!selectedEvent) return;

    Swal.fire({
      title: 'Edit Jam Layanan',
      input: 'time',
      inputLabel: 'Masukkan jam baru',
      inputValue: selectedEvent.startStr.slice(11, 16),
      showCancelButton: true,
      confirmButtonText: 'Update',
      preConfirm: (newTime) => {
        if (!newTime) {
          Swal.showValidationMessage('Jam tidak boleh kosong!');
          return false;
        }

        const title = selectedEvent.title;
        const tanggal = selectedEvent.startStr.split('T')[0];
        const nama_layanan = title.split(' - ')[0];
        const nama_pelanggan = title.match(/\((.*?)\)/)[1];

        return fetch('update_jam.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            nama_pelanggan,
            nama_layanan,
            tanggal,
            jam_layanan: newTime
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.status !== 'sukses') {
            throw new Error(data.error || 'Gagal update');
          }
          return newTime;
        })
        .catch(error => {
          Swal.showValidationMessage(`Gagal: ${error}`);
          return false;
        });
      }
    }).then((result) => {
      if (result.isConfirmed && result.value) {
        const newTitle = selectedEvent.title.replace(/\d{2}:\d{2}/, result.value);
        selectedEvent.setProp('title', newTitle);
        Swal.fire('Berhasil!', 'Jam layanan berhasil diubah.', 'success');
        closePopup();
      }
    });
  }
}); // <-- Penutup DOMContentLoaded
</script>




  <div class="logout-link ">
    <a href="logout.php">Logout</a>
  </div>


</body>
</html>
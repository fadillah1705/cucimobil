<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$db_cucimobil = "cucimobil";

// Koneksi ke database utama (cucimobil)
$conn = new mysqli($host, $user, $pass, $db_cucimobil);
if ($conn->connect_error) {
  die("Koneksi ke cucimobil gagal: " . $conn->connect_error);
}

// Proses tambah data dari kalender → simpan hanya ke tabel emsit_cucimobil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
  $nama     = $_POST['nama_pelanggan'];
  $layanan  = $_POST['nama_layanan'];
  $jam      = $_POST['jam_layanan'];
  $tanggal  = $_POST['tanggal'];

  $stmt = $conn->prepare("INSERT INTO emsit_cucimobil (nama_pelanggan, nama_layanan, jam_layanan, tanggal) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $nama, $layanan, $jam, $tanggal);
  $sukses = $stmt->execute();

  echo json_encode(['status' => $sukses ? 'sukses' : 'gagal']);
  exit;
}

// Proses hapus data dari kalender
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'hapus') {
  $id = $_POST['id'];
  $stmt = $conn->prepare("DELETE FROM emsit_cucimobil WHERE id = ?");
  $stmt->bind_param("i", $id);
  $sukses = $stmt->execute();

  echo json_encode(['status' => $sukses ? 'sukses' : 'gagal']);
  exit;
}

// Ambil data dari tabel ringkas (booking)
$bookingRingkas = [];
$result1 = $conn->query("SELECT nama, layanan, waktu FROM booking");
while ($row = $result1->fetch_assoc()) {
  $bookingRingkas[] = $row;
}

// Ambil data dari tabel detail (emsit_cucimobil)
$bookingDetail = [];
$result2 = $conn->query("SELECT * FROM emsit_cucimobil");
while ($row = $result2->fetch_assoc()) {
  $bookingDetail[] = $row;
}

// Format event untuk kalender
$events = [];
foreach ($bookingDetail as $row) {
  $events[] = [
    'id' => $row['id'],
    'title' => "{$row['nama_layanan']} - {$row['jam_layanan']} ({$row['nama_pelanggan']})",
    'start' => $row['tanggal']
  ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <style>
  body {
  background-color: #f0fdf4;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h2 {
  font-weight: bold;
  color: #2d3e2f;
}

table th {
  background-color: #79c2b0;
  color: white;
  text-align: center;
}

table td {
  vertical-align: middle;
}

.table {
  box-shadow: 0 2px 8px rgba(121, 194, 176, 0.3);
  background-color: white;
  border-radius: 10px;
}

#calendar {
  background-color: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(121, 194, 176, 0.3);
}

.modal-content {
  border-radius: 10px;
  box-shadow: 0 4px 16px rgba(121, 194, 176, 0.5);
}

.modal-header {
  background-color: #79c2b0;
  color: white;
  border-bottom: none;
}

.btn-primary {
  background-color: #79c2b0;
  border: none;
}

.btn-primary:hover {
  background-color: #68af9f;
}

.btn-secondary {
  background-color: #b3dcd2;
  border: none;
  color: #2d3e2f;
}

.logout-link {
  position: fixed;
  top: 10px;
  right: 20px;
}

.logout-link a {
  padding: 8px 16px;
  background-color: #e74c3c;
  color: white;
  text-decoration: none;
  font-weight: bold;
  border-radius: 6px;
  transition: background-color 0.3s ease;
}

.logout-link a:hover {
  background-color: #c0392b;
}
</style>
</head>
<body>
<div class="container my-4">
  <h2 class="text-center mb-4">Data Booking Ringkas</h2>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Layanan</th>
        <th>Waktu</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bookingRingkas as $index => $row): ?>
        <tr>
          <td><?= $index + 1 ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['layanan']) ?></td>
          <td><?= htmlspecialchars($row['waktu']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h2 class="text-center my-5">Kalender Booking</h2>
  <div id="calendar"></div>

  <h2 class="text-center my-5">Data Booking Detail</h2>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Layanan</th>
        <th>Jam</th>
        <th>Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bookingDetail as $row): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
          <td><?= htmlspecialchars($row['nama_layanan']) ?></td>
          <td><?= htmlspecialchars($row['jam_layanan']) ?></td>
          <td><?= htmlspecialchars($row['tanggal']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal untuk tambah data -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="bookingForm">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="tambah">
        <div class="mb-3">
          <label>Nama Pelanggan</label>
          <input type="text" name="nama_pelanggan" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Layanan</label>
          <input type="text" name="nama_layanan" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Jam Layanan</label>
          <input type="text" name="jam_layanan" class="form-control" placeholder="Contoh: 10.00-11.00" required>
        </div>
        <div class="mb-3">
          <label>Tanggal</label>
          <input type="date" name="tanggal" class="form-control" id="inputTanggal" readonly>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const modal = new bootstrap.Modal(document.getElementById('modalTambah'));
    const inputTanggal = document.getElementById('inputTanggal');
    const form = document.getElementById('bookingForm');

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      events: <?= json_encode($events) ?>,
      dateClick: function(info) {
        inputTanggal.value = info.dateStr;
        modal.show();
      },
      eventClick: function(info) {
        if (confirm('Hapus data ini?')) {
          fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'hapus', id: info.event.id })
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'sukses') {
              info.event.remove();
              alert('Berhasil dihapus');
            } else {
              alert('Gagal menghapus');
            }
          });
        }
      }
    });
    calendar.render();

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(form);
      fetch('', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'sukses') {
          calendar.addEvent({
            title: `${form.nama_layanan.value} - ${form.jam_layanan.value} (${form.nama_pelanggan.value})`,
            start: form.tanggal.value
          });
          modal.hide();
          form.reset();
        } else {
          alert('Gagal menyimpan');
        }
      });
    });
  });
</script>
 <div class="logout-link ">
    <a href="logout.php">Logout</a>
  </div>
</body>
</html>

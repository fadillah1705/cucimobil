<?php

// ✅ Tangani form booking dari AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'], $_POST['layanan'], $_POST['tanggal'], $_POST['waktu'])) {
    include 'conn.php';
    $nama = $_POST['nama'];
    $layanan = $_POST['layanan'];
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];

    // Simpan ke tabel booking
    $stmt = $conn->prepare("INSERT INTO booking (nama, layanan, tanggal, waktu) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $layanan, $tanggal, $waktu);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'sukses']);
    } else {
        echo json_encode(['status' => 'gagal', 'error' => $stmt->error]);
    }
    exit;
}

// ✅ Lanjutkan ke dashboard admin
session_start();
include "conn.php";

// Cek session login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}


 $query = "SELECT COUNT(*) AS total_booking FROM booking";
 $result = $conn->query($query);
 $data = $result->fetch_assoc();
 $totalBooking = $data['total_booking'];

>>>>>>> fcdb753adb7ac9d98cd4ff82d4ae0abaff01391f
 // Ambil jumlah total user
 $userQuery = "SELECT COUNT(*) AS total_user FROM mencuci where role='user'";
 $userResult = $conn->query($userQuery);
 $userData = $userResult->fetch_assoc();
 $totalUser = $userData['total_user'];


// Ambil jumlah layanan dari tabel layanan
$layananQuery = "SELECT COUNT(*) AS total_layanan FROM layanan";
$layananResult = $conn->query($layananQuery);
$layananData = $layananResult->fetch_assoc();
$totalLayanan = $layananData['total_layanan'];

// Menampilkan jumlah booking hari ini (tanggal = CURDATE() di SQL).
$todayQuery = "SELECT COUNT(*) AS booking_hari_ini FROM booking WHERE tanggal = CURDATE()";
$todayResult = $conn->query($todayQuery);
$todayData = $todayResult->fetch_assoc();
$bookingHariIni = $todayData['booking_hari_ini'];



// TABEL KALENDER
// Ambil daftar nama user (pelanggan) dari tabel mencuci
$namaQuery = "SELECT DISTINCT nama FROM booking ORDER BY nama ASC";
$namaResult = $conn->query($namaQuery);
$daftarNama = [];
while ($row = $namaResult->fetch_assoc()) {
    $daftarNama[] = $row['nama'];
}

// Ambil daftar nama layanan dari tabel layanan
$layananQuery = "SELECT nama FROM layanan ORDER BY nama ASC";
$layananResult = $conn->query($layananQuery);
$daftarLayanan = [];
while ($row = $layananResult->fetch_assoc()) {
    $daftarLayanan[] = $row['nama'];
}


 ?>



>>>>>>> fcdb753adb7ac9d98cd4ff82d4ae0abaff01391f
 <!DOCTYPE html>
 <html lang="en">
 <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Dashboard 2</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <link rel="stylesheet" href="AdminLTE-3.1.0/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <style>
  /* Tinggi tetap untuk kalender */
  #calendar {
    height: 900px;
    overflow: hidden;
  }
   
  /* Set tinggi semua baris tanggal menjadi 120px */
  .fc-daygrid-day-frame {
    height: 120px !important;
    min-height: 120px !important;
  }
   
  /* Area events dengan scroll */
  .fc-daygrid-day-events {
    overflow-y: auto;
    max-height: calc(120px - 30px); /* 30px untuk header tanggal */
    margin-right: 2px;
  }
   
  /* Header tanggal */
  .fc-daygrid-day-top {
    height: 30px;
  }
   
  /* Event item styling */
  .fc-event {
    font-size: 12px;
    padding: 2px 4px;
    margin-bottom: 2px;
    white-space: normal;
    word-break: break-word;
  }

   
  /* Warna layanan premium */
  .fc-event-premium {
    background-color: #ff9500;
    border-color: #e08600;
  }
   
  /* Warna layanan reguler */
  .fc-event-reguler {
    background-color: #4a90e2;
    border-color: #3a7bc8;
  }

   
  /* Hilangkan padding yang tidak perlu */
  .fc-daygrid-day {
    padding: 0 !important;
  }
   
  /* Pastikan sel tanggal memiliki tinggi yang konsisten */
  .fc-daygrid-day {
    height: 120px !important;
  }
 </style>

 </head>
 <body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
 <div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__wobble" src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div>

      <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index3.html" class="brand-link">
      <img src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">AdminGoWash</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="AdminLTE-3.1.0/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
          <div class="info">
  <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['username']) ?></a>
 </div>
      </div>

      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
              <li class="nav-item">
                <a href="admin.php" class="nav-link active">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard</p>
                </a>
              </li>
          </li>
           
          <li class="nav-item">
            <a href="AdminLTE-3.1.0/tab_booking.php" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>

                Widgets

              </p>
            </a>
          </li>
           
          <li class="nav-item">
            <a href="admin-harga.php" class="nav-link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>
                Charts

              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Logout</p>
            </a>
          </li>   
        </ul>
      </nav>
    </div>
    </aside>
  <div class="content-wrapper">

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
  <span class="info-box-icon bg-info elevation-1"><i class="fas fa-concierge-bell"></i></span>
  <div class="info-box-content">
    <span class="info-box-text">Total Layanan</span>
    <span class="info-box-number"><?= htmlspecialchars($totalLayanan) ?></span>
  </div>
</div>


            </div>
          <div class="col-12 col-sm-6 col-md-3">
           <div class="info-box mb-3">
  <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-calendar-day"></i></span>
  <div class="info-box-content">
    <span class="info-box-text">Booking Hari Ini</span>
    <span class="info-box-number"><?= htmlspecialchars($bookingHariIni) ?></span>
  </div>
</div>


            </div>
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Booking</span>
                <span class="info-box-number"><?= htmlspecialchars($totalBooking) ?></span>
              </div>
              </div>
            </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Users</span>
                <span class="info-box-number"><?= htmlspecialchars($totalUser) ?></span>
              </div>
              </div>
            </div>
          </div>
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Kalender Booking</h3>
              </div>
              <div class="card-body">
                <div id="calendar"></div>
              </div>
              </div>
            </div>
          </div>
        <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel">Detail Booking</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
      </div></section>
    </div>
  <aside class="control-sidebar control-sidebar-dark">
    </aside>
  <footer class="main-footer">
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.1.0
    </div>
  </footer>
 </div>
 <script src="AdminLTE-3.1.0/plugins/jquery/jquery.min.js"></script>
 <script src="AdminLTE-3.1.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
 <script src="AdminLTE-3.1.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
 <script src="AdminLTE-3.1.0/dist/js/adminlte.js"></script>

 <script src="AdminLTE-3.1.0/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
 <script src="AdminLTE-3.1.0/plugins/raphael/raphael.min.js"></script>
 <script src="AdminLTE-3.1.0/plugins/jquery-mapael/jquery.mapael.min.js"></script>
 <script src="AdminLTE-3.1.0/plugins/jquery-mapael/maps/usa_states.min.js"></script>
 <script src="AdminLTE-3.1.0/plugins/chart.js/Chart.min.js"></script>

 <script src="AdminLTE-3.1.0/dist/js/demo.js"></script>
 <script src="AdminLTE-3.1.0/dist/js/pages/dashboard2.js"></script>


 <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
 <script>
   const daftarNama = <?= json_encode($daftarNama) ?>;
   const daftarLayanan = <?= json_encode($daftarLayanan) ?>;


  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },

      // ✅ Tambahkan fitur klik tanggal
      dateClick: function(info) {
        const tanggal = info.dateStr;
        const formHtml = `
          <form id="formBooking">
            <div class="form-group">
             <label>Nama</label>
<select name="nama" class="form-control" required>
  <option value="">-- Pilih Nama --</option>
  ${daftarNama.map(n => `<option value="${n}">${n}</option>`).join('')}
</select>

            </div>
            <div class="form-group">
              <label>Layanan</label>
              <select name="layanan" class="form-control" required>
  <option value="">-- Pilih Layanan --</option>
  ${daftarLayanan.map(l => `<option value="${l}">${l}</option>`).join('')}
</select>


            </div>
            <div class="form-group">
              <label>Jam</label>
              <input type="time" name="waktu" class="form-control" required>
            </div>
            <input type="hidden" name="tanggal" value="${tanggal}">
            <button type="submit" class="btn btn-primary mt-2">Simpan</button>
          </form>
        `;
        $('#bookingModal .modal-body').html(formHtml);
        $('#bookingModal').modal('show');

        // Submit form
        $('#formBooking').on('submit', function(e) {
          e.preventDefault();
          const data = $(this).serialize();
          $.post('admin.php', data, function(response) {
            if (response.status === 'sukses') {
              $('#bookingModal').modal('hide');
              location.reload(); // atau gunakan calendar.refetchEvents()
            } else {
              alert("Gagal: " + response.error);
            }
          }, 'json');
        });
      },

      // ✅ Event dari database
      events: [
        <?php
        $sql = "SELECT layanan, waktu, tanggal FROM booking";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $layananClass = (strpos(strtolower($row['layanan']), 'premium') !== false) ? 'fc-event-premium' : 'fc-event-reguler';
            $start = $row['tanggal'] . 'T' . date('H:i:s', strtotime($row['waktu']));
            echo "{ title: '" . htmlspecialchars($row['layanan']) . "', start: '" . $start . "', className: '" . $layananClass . "' },";
          }
        }
        ?>

      ],

      // ✅ Klik event → buka modal detail
      eventClick: function(info) {
        $('#bookingModal .modal-body').html(
          '<strong>Layanan:</strong> ' + info.event.title +
          '<br><strong>Waktu:</strong> ' + info.event.start.toLocaleString()
        );
        $('#bookingModal').modal('show');
      }
    });

    calendar.render();
  });

</script>

 </body>
 </html>
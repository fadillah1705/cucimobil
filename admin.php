<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// total booking
include 'conn.php';

$query = "SELECT COUNT(*) AS total_booking FROM booking";
$result = $conn->query($query);
$data = $result->fetch_assoc();
$totalBooking = $data['total_booking'];

// Ambil jumlah total user
$userQuery = "SELECT COUNT(*) AS total_user FROM mencuci where role='user'";
$userResult = $conn->query($userQuery);
$userData = $userResult->fetch_assoc();
$totalUser = $userData['total_user'];
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Dashboard 2</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="AdminLTE-3.1.0/dist/css/adminlte.min.css">
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
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

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__wobble" src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div>

      <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="AdminLTE-3.1.0/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
         <div class="info">
  <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['username']) ?></a>
</div>
      </div>

      <!-- SidebarSearch Form -->
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

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item">
              <li class="nav-item">
                <a href="admin.php" class="nav-link">
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
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">CPU Traffic</span>
                <span class="info-box-number">
                  10
                  <small>%</small>
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-thumbs-up"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Likes</span>
                <span class="info-box-number">41,410</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Booking</span>
<span class="info-box-number"><?= $totalBooking ?></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Member</span>
<span class="info-box-number"><?= $totalUser ?></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
         <!-- Booking Calendar -->
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

<!-- Modal Form Booking -->
<div id="calendar"></div>

<!-- Modal Form Tambah / Ubah Booking -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="bookingForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bookingModalLabel">Form Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
  <input type="hidden" name="id" id="bookingId">
  <input type="hidden" name="tanggal" id="bookingTanggal"> <!-- ini akan diisi dari tanggal klik -->

  <div class="mb-3">
    <label for="bookingNama" class="form-label">Nama</label>
    <input type="text" class="form-control" name="nama" id="bookingNama" required>
  </div>

  <div class="form-group">
    <label for="bookingLayanan">Layanan</label>
    <select class="form-control" id="bookingLayanan" name="layanan" required>
      <option value="">-- Pilih Layanan --</option>
      <option value="Cuci Mobil Reguler">Cuci Mobil Reguler</option>
      <option value="Cuci Mobil Premium">Cuci Mobil Premium</option>
      <option value="Detailing">Detailing</option>
      <option value="Salon Mobil">Salon Mobil</option>
    </select>
  </div>

  <div class="mb-3">
    <label for="bookingJam" class="form-label">Jam</label>
    <input type="time" class="form-control" name="jam" id="bookingJam" required>
  </div>

</div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Detail Booking -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <p><strong>Nama:</strong> <span id="detailNama"></span></p>
        <p><strong>Layanan:</strong> <span id="detailLayanan"></span></p>
        <p><strong>Jam:</strong> <span id="detailJam"></span></p>
        <p><strong>Tanggal:</strong> <span id="detailTanggal"></span></p>
      </div>
      <div class="modal-footer">
        <button id="ubahBooking" class="btn btn-warning">Ubah</button>
        <button id="hapusBooking" class="btn btn-danger">Hapus</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Keluar</button>
      </div>
    </div>
  </div>
</div>

      </div>


  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.1.0
    </div>
  </footer>
</div>

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="AdminLTE-3.1.0/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="AdminLTE-3.1.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- overlayScrollbars -->
<script src="AdminLTE-3.1.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="AdminLTE-3.1.0/dist/js/adminlte.js"></script>

<!-- PAGE PLUGINS -->
<!-- jQuery Mapael -->
<script src="AdminLTE-3.1.0/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
<script src="AdminLTE-3.1.0/plugins/raphael/raphael.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/jquery-mapael/jquery.mapael.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/jquery-mapael/maps/usa_states.min.js"></script>
<!-- ChartJS -->
<script src="AdminLTE-3.1.0/plugins/chart.js/Chart.min.js"></script>

<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="AdminLTE-3.1.0/dist/js/pages/dashboard2.js"></script>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
      eventContent: function(info) {
  const nama = info.event.extendedProps.nama;
  const layanan = info.event.extendedProps.layanan;

  const waktu = new Date(info.event.start);
  const jam = waktu.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

  return {
    html: `${nama} - ${layanan} -${jam}`
  };
},

      initialView: 'dayGridMonth',
      selectable: true,
      events: 'AdminLTE-3.1.0/booking-handler.php?action=load',

      // Klik tanggal → buka form tambah
      
      select: function (info) {
        document.getElementById('bookingTanggal').value = info.startStr;
        document.getElementById('bookingId').value = ''; // reset ID untuk tambah baru
        $('#bookingModal').modal('show');
      },

      // Klik event → buka modal detail
      eventClick: function (info) {
        const event = info.event;
        const props = event.extendedProps;

        // Ambil jam dari waktu (format: YYYY-MM-DD HH:MM:SS)
        const jam = props.waktu.split(' ')[1].substring(0, 5); // ambil "HH:MM"

        document.getElementById('detailNama').textContent = props.nama;
        document.getElementById('detailLayanan').textContent = props.layanan;
        document.getElementById('detailJam').textContent = jam;
        document.getElementById('detailTanggal').textContent = event.startStr;
        document.getElementById('hapusBooking').dataset.id = event.id;
        document.getElementById('ubahBooking').dataset.id = event.id;

        $('#detailModal').modal('show');
      }
    });

    calendar.render();

    // Submit form booking
    document.getElementById('bookingForm').addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      const id = formData.get('id');
      formData.set('action', id ? 'update' : 'add'); // ubah jika ada id

       for (const [key, value] of formData.entries()) {
  console.log(key, value);
}
      fetch('AdminLTE-3.1.0/booking-handler.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        console.log('Server response:', data);
        if (data.success) {
          $('#bookingModal').modal('hide');
          calendar.refetchEvents();
        } else {
          alert(data.message || 'Gagal menyimpan booking');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan booking');
      });
    });

    // Hapus booking
   // Hapus booking hanya dari tampilan kalender
document.getElementById('hapusBooking').addEventListener('click', function () {
  const event = calendar.getEventById(this.dataset.id);
  
  if (event && confirm('Hapus booking ini dari kalender?')) {
    event.remove(); // hanya hapus dari kalender
    $('#detailModal').modal('hide');
  }
});


    // Ubah booking → isi ulang form
    document.getElementById('ubahBooking').addEventListener('click', function () {
      const id = this.dataset.id;

      document.getElementById('bookingId').value = id;
      document.getElementById('bookingNama').value = document.getElementById('detailNama').textContent;
      document.getElementById('bookingLayanan').value = document.getElementById('detailLayanan').textContent;
      document.getElementById('bookingTanggal').value = document.getElementById('detailTanggal').textContent;

      // Ambil jam dari tampilan "detailJam" dan set sebagai input time
      const jam = document.getElementById('detailJam').textContent;
      document.getElementById('bookingJam').value = jam;

      $('#detailModal').modal('hide');
      $('#bookingModal').modal('show');
    });
  });

 

</script>

</body>
</html>

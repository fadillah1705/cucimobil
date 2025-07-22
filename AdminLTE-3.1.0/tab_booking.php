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
$bookingDetail = [];
$result2 = $conn->query("SELECT * FROM booking");
while ($row = $result2->fetch_assoc()) {
  $bookingDetail[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'updateStatus') {
  header('Content-Type: application/json');

  $id = intval($_POST['id'] ?? 0);
  $new = intval($_POST['status'] ?? -1);
  if ($id > 0 && ($new === 0 || $new === 1)) {
    $stmt = $conn->prepare("UPDATE booking SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new, $id);
    echo json_encode(['success' => $stmt->execute()]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
  }
  exit;
}
$bookingQuery = "SELECT COUNT(*) AS total_menunggu FROM booking WHERE status = 0";
$bookingResult = $conn->query($bookingQuery);
$bookingData = $bookingResult->fetch_assoc();
$totalMenunggu = $bookingData['total_menunggu'];

// Hitung jumlah booking dengan status "Selesai"
$selesaiQuery = "SELECT COUNT(*) AS total_selesai FROM booking WHERE status = 1 ";
$selesaiResult = $conn->query($selesaiQuery);
$selesaiData = $selesaiResult->fetch_assoc();
$totalSelesai = $selesaiData['total_selesai'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Dashboard</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
<link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">

  <style>
  .status-select {
  font-weight: bold;
  color: #ffffff;
  background-color: #3a3f4b;
  border: 1px solid #666;
}

/* Status Selesai */
.status-selesai {
  color: #28d17c; /* hijau terang */
  background-color: #1e2f1e; /* hijau gelap */
  border-color: #28d17c;
}

/* Status Menunggu */
.status-menunggu {
  color: #f7c948; /* kuning terang */
  background-color: #3a2f1e; /* coklat gelap */
  border-color: #f7c948;
}


  /* Override tabel agar cocok dengan dark mode */
  body {
    background-color: #1e1e2f;
    color: #f1f1f1;
  }

  table.table {
    background-color: #2d2d3a;
    color: #ffffff;
    border-color: #444;
  }

  thead.table-secondary {
    background-color: #3a3a4a !important;
    color: #ffffff !important;
  }

  table tbody tr:nth-child(odd) {
    background-color: #2c2f38;
  }

  table tbody tr:nth-child(even) {
    background-color: #2a2d36;
  }

  table td, table th {
    border: 1px solid #555;
  }

  select.status-select {
    background-color: #3a3a4a;
    color: #fff;
    border: 1px solid #666;
    padding: 3px;
    border-radius: 4px;
  }

  .btn-danger {
    background-color: #e74c3c;
    border: none;
    color: #fff;
  }

  .btn-danger:hover {
    background-color: #c0392b;
  }

</style>

</head>
<body class="hold-transition sidebar-mini layout-fixed dark-mode">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div>

  <!-- Main Sidebar Container -->
   <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
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
                <a href="../admin.php" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard</p>
                </a>
              </li>
          </li>
          
          <li class="nav-item">
            <a href="tab_booking.php" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
              Widgets
            </p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="../admin-harga.php" class="nav-link">
            <i class="nav-icon fas fa-chart-pie"></i>
            <p>
              Charts
            </p>
          </a>
        </li>

        <li class="nav-item">
          <a href="../logout.php" class="nav-link">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>  
      </ul>
      </nav>
    </div>
  </aside>

<br>
<br>
<br>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>150</h3>

                <p>New Orders</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
               <h3><?= $totalMenunggu ?></h3>
<p>Booking Menunggu</p>

              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= $totalSelesai ?></h3>
<p>Booking Selesai</p>

              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>65</h3>

                <p>Unique Visitors</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        
        <div style="max-height: 500px; overflow-y: auto;">
  <table class="table table-bordered table-striped">
    <thead class="table-secondary" style="position: sticky; top: 0; z-index: 1;">
      <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Layanan</th>
        <th>Jam</th>
        <th>Tanggal</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $counter = 0;
      foreach ($bookingDetail as $row): 
        $counter++;
        if ($counter > 10) break; // Hanya tampilkan 10 baris pertama
      ?>
        <tr id="row-<?= $row['id'] ?>">
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['layanan']) ?></td>
          <td><?= date('H:i', strtotime($row['waktu'])) ?></td>
          <td><?= date('d-m-Y', strtotime(explode(' ', $row['waktu'])[0])) ?></td>
<td>
  <span class="badge status-toggle bg-<?= $row['status'] == 1 ? 'success' : 'danger' ?>"
        data-id="<?= $row['id'] ?>"
        data-status="<?= $row['status'] ?>"
        style="cursor:pointer;">
    <?= $row['status'] == 1 ? 'Selesai' : 'Menunggu' ?>
  </span>
</td>

          
          <td>
            <button class="btn btn-sm btn-danger" onclick="hapusBooking(<?= $row['id'] ?>)">Hapus</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Pagination -->




      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.1.0
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
<script>
function hapusBooking(id) {
  if (confirm("Yakin ingin menghapus data booking ini dari database?")) {
    fetch('booking-handler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'delete', id: id })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('row-' + id).remove();
      } else {
        alert('Gagal menghapus data booking.');
      }
    })
    .catch(err => {
      console.error('Error:', err);
      alert('Terjadi kesalahan saat menghapus.');
    });
  }
}
</script>
<script>
function hapusBooking(id) {
  if (confirm("Yakin ingin menghapus data booking ini dari database?")) {
    fetch('booking-handler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'delete', id: id })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('row-' + id).remove();
      } else {
        alert('Gagal menghapus data booking.');
      }
    })
    .catch(err => {
      console.error('Error:', err);
      alert('Terjadi kesalahan saat menghapus.');
    });
  }
}
</script>
<script>
  $(document).ready(function () {
    $(document).on('click', '.status-toggle', function () {
      const badge = $(this);
      const id = badge.data('id');
      const current = badge.data('status'); // 1 atau 0
      const next = current == 1 ? 0 : 1;

      $.ajax({
        url: 'tab_booking.php',
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'updateStatus',
          id: id,
          status: next
        },
        success: function (res) {
          if (res.success) {
            badge.data('status', next);
            badge
              .removeClass('bg-success bg-danger')
              .addClass(next == 1 ? 'bg-success' : 'bg-danger')
              .text(next == 1 ? 'Selesai' : 'Menunggu');
          } else {
            alert('Gagal: ' + (res.message || '...'));
          }
        },
        error: function (xhr, status, err) {
          console.error('AJAX Error:', status, err, xhr.responseText);
          alert('Terjadi kesalahan koneksi');
        }
      });
    });
  });
</script>

</body>
</html>

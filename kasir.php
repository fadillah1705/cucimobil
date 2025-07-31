<?php
session_start();

include "conn.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Dashboard</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/jqvmap/jqvmap.min.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/summernote/summernote-bs4.min.css">

    <style>
         .nota {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            background: #f8f9fa;}
    </style>

</head>
<body class="hold-transition sidebar-mini layout-fixed dark-mode">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
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
                                Booking
                            </p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="admin-harga.php" class="nav-link">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>
                                Layanan
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kasir.php" class="nav-link active">
                            <i class="nav-icon fas fa-desktop"></i>
                            <p>
                                Kasir
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

    <br>
    <br>
    <br>

    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <?php
// Hitung total booking yang statusnya 'menunggu'
$qMenunggu = $conn->query("SELECT COUNT(*) as total FROM booking WHERE status = 'menunggu'");
$totalMenunggu = $qMenunggu->fetch_assoc()['total'] ?? 0;

// Hitung total booking yang statusnya 'selesai'
$qSelesai = $conn->query("SELECT COUNT(*) as total FROM booking WHERE status = 'selesai'");
$totalSelesai = $qSelesai->fetch_assoc()['total'] ?? 0;

// Layanan terlaris (paling banyak dibooking)
$qTerlaris = $conn->query("
    SELECT layanan.nama, COUNT(*) as total 
    FROM booking 
    JOIN layanan ON booking.id_layanan = layanan.id 
    GROUP BY layanan.id 
    ORDER BY total DESC 
    LIMIT 1
");
$layananTerlarisData = $qTerlaris->fetch_assoc();
$layananTerlaris = $layananTerlarisData['nama'] ?? '-';
$totalLayananTerlaris = $layananTerlarisData['total'] ?? 0;

// Layanan paling jarang (paling sedikit dibooking)
$qKurang = $conn->query("
    SELECT layanan.nama, COUNT(*) as total 
    FROM booking 
    JOIN layanan ON booking.id_layanan = layanan.id 
    GROUP BY layanan.id 
    ORDER BY total ASC 
    LIMIT 1
");
$layananKurangData = $qKurang->fetch_assoc();
$layananKurang = $layananKurangData['nama'] ?? '-';
$totalLayananKurang = $layananKurangData['total'] ?? 0;
?>

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
                    <div class="col-lg-3 col-6">
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
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h6><?= htmlspecialchars($layananTerlaris) ?></h6>
                                <p>Layanan Terlaris (<?= $totalLayananTerlaris ?> pesanan)</p>
                            </div><br>
                            <div class="icon">
                                <i class="ion ion-star"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h6><?= htmlspecialchars($layananKurang) ?></h6>
                                <p>Layanan Kurang Diminati (<?= $totalLayananKurang ?> pesanan)</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-minus-circled"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

<?php
// DEBUG: tampilkan semua error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// koneksi ke database
include 'conn.php';
?>

<form method="GET" class="mb-4">
    <div class="mb-3">
        <label for="pelanggan_id" class="form-label">ID Pelanggan</label>
        <input type="number" class="form-control" id="pelanggan_id" name="pelanggan_id" placeholder="Masukkan ID pelanggan" value="<?= htmlspecialchars($_GET['pelanggan_id'] ?? '') ?>">
        <button type="submit" class="btn btn-primary mt-2">Cari Nota</button>
    </div>
</form>

<?php
if (isset($_GET['pelanggan_id']) && is_numeric($_GET['pelanggan_id'])) {
    $idPelanggan = $_GET['pelanggan_id'];

    $stmt = $conn->prepare("
        SELECT b.*, u.username, l.nama AS nama_layanan, l.price 
        FROM booking b
        JOIN users u ON b.pelanggan_id = u.id
        JOIN layanan l ON b.id_layanan = l.id
        WHERE b.pelanggan_id = ?
    ");

    if (!$stmt) {
        die("Query prepare gagal: " . $conn->error);
    }

    $stmt->bind_param("i", $idPelanggan);

    if (!$stmt->execute()) {
        die("Query gagal dijalankan: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $total = 0;
        echo "<h4>Nota Booking - ID Pelanggan: {$idPelanggan}</h4>";
        echo "<table class='table table-bordered'>
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Layanan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Harga</th>
                    </tr>
                </thead>
                <tbody>";

        while ($row = $result->fetch_assoc()) {
            $total += $row['price'];
            echo "<tr>
                    <td>" . htmlspecialchars($row['username']) . "</td>
                    <td>" . htmlspecialchars($row['nama_layanan']) . "</td>
                    <td>" . date('d-m-Y', strtotime($row['tanggal'])) . "</td>
                    <td>" . date('H:i', strtotime($row['waktu'])) . "</td>
                    <td>Rp " . number_format($row['price']) . "</td>
                </tr>";
        }

        echo "<tr>
        <td colspan='4'><strong>Total</strong></td>
        <td><strong>Rp " . number_format($total) . "</strong></td>
      </tr>";

        echo "</tbody></table>";
    } else {
        echo "<div class='alert alert-warning'>Data booking tidak ditemukan untuk ID pelanggan tersebut.</div>";
    }
}
?>
                
<script src="AdminLTE-3.1.0/plugins/jquery/jquery.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/jquery-ui/jquery-ui.min.js"></script>
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<script src="AdminLTE-3.1.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/chart.js/Chart.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/sparklines/sparkline.js"></script>
<script src="AdminLTE-3.1.0/plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<script src="AdminLTE-3.1.0/plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/moment/moment.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/daterangepicker/daterangepicker.js"></script>
<script src="AdminLTE-3.1.0/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/summernote/summernote-bs4.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="AdminLTE-3.1.0/dist/js/adminlte.js"></script>
<script src="AdminLTE-3.1.0/dist/js/demo.js"></script>
<script src="AdminLTE-3.1.0/dist/js/pages/dashboard.js"></script>


</body>
</html>
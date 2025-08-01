<?php
// Pastikan session dimulai di awal skrip
session_start();

// Inklusi file koneksi database
include "conn.php";

// Pengaturan Error Reporting:
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan koneksi database MySQLi berhasil
if ($conn->connect_error) {
    die("Koneksi database MySQLi gagal di kasir.php: " . $conn->connect_error);
}

// Cek apakah user sudah login. Jika belum, arahkan ke halaman login.
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Dashboard Kasir</title>

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
  
        body {
            font-family: 'Poppins', sans-serif !important;
        }
        /* CSS Tambahan untuk Merapikan Tampilan */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, .05);
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .card-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed dark-mode">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
    </div>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="admin.php" class="brand-link">
            <img src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">AdminGoWash</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="AdminLTE-3.1.0/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></a>
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
                        <a href="admin.php" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="AdminLTE-3.1.0/tab_booking.php" class="nav-link">
                            <i class="nav-icon fas fa-th"></i>
                            <p>Booking</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin-harga.php" class="nav-link">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>Layanan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kasir.php" class="nav-link active">
                            <i class="nav-icon fas fa-desktop"></i>
                            <p>Kasir</p>
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
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Kasir</h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-search"></i> Cari Nota Booking</h3>
                    </div>
                    <form method="GET">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="pelanggan_id">ID Pelanggan</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="pelanggan_id" name="pelanggan_id" placeholder="Masukkan ID pelanggan" value="<?= htmlspecialchars($_GET['pelanggan_id'] ?? '') ?>">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <?php
                // --- Proses Pencarian Nota ---
                if (isset($_GET['pelanggan_id']) && !empty($_GET['pelanggan_id'])) {
                    $idPelanggan = filter_var($_GET['pelanggan_id'], FILTER_VALIDATE_INT);

                    if ($idPelanggan === false) {
                        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                                <i class='icon fas fa-ban'></i> ID Pelanggan tidak valid. Harap masukkan angka.
                                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                    <span aria-hidden='true'>&times;</span>
                                </button>
                              </div>";
                    } else {
                        // Menggunakan prepared statement untuk mencegah SQL Injection
                        $stmt = $conn->prepare("
                            SELECT b.id as booking_id, b.tanggal, b.waktu, b.status,
                                   u.username,
                                   l.nama AS nama_layanan, l.price
                            FROM booking b
                            JOIN users u ON b.pelanggan_id = u.id
                            JOIN booking_layanan bl ON b.id = bl.booking_id
                            JOIN layanan l ON bl.layanan_id = l.id
                            WHERE b.pelanggan_id = ?
                            ORDER BY b.tanggal DESC, b.waktu DESC
                        ");

                        if ($stmt === false) {
                            die("Error preparing statement: " . $conn->error);
                        }

                        $stmt->bind_param("i", $idPelanggan);

                        if ($stmt->execute()) {
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                $bookings_data = [];
                                while ($row = $result->fetch_assoc()) {
                                    $booking_id = $row['booking_id'];
                                    if (!isset($bookings_data[$booking_id])) {
                                        $bookings_data[$booking_id] = [
                                            'username' => $row['username'],
                                            'tanggal' => $row['tanggal'],
                                            'waktu' => $row['waktu'],
                                            'status' => $row['status'],
                                            'layanan' => [],
                                            'total_harga' => 0
                                        ];
                                    }
                                    $bookings_data[$booking_id]['layanan'][] = [
                                        'nama' => $row['nama_layanan'],
                                        'price' => $row['price']
                                    ];
                                    $bookings_data[$booking_id]['total_harga'] += $row['price'];
                                }

                                // Menampilkan Nota Booking di dalam Card
                                echo '<div class="card card-success card-outline">';
                                echo '<div class="card-header">';
                                echo '<h3 class="card-title"><i class="fas fa-clipboard-list"></i> Nota Booking - ID Pelanggan: ' . htmlspecialchars($idPelanggan) . '</h3>';
                                echo '</div>'; // /.card-header
                                echo '<div class="card-body">';
                                echo "<div class='table-responsive'>";
                                echo "<table class='table table-bordered table-striped table-hover'>
                                        <thead>
                                            <tr>
                                                <th>Nama Pelanggan</th>
                                                <th>Tanggal</th>
                                                <th>Waktu</th>
                                                <th>Status</th>
                                                <th>Layanan</th>
                                                <th class='text-right'>Harga Layanan</th>
                                                <th class='text-right'>Total Booking</th>
                                            </tr>
                                        </thead>
                                        <tbody>";

                                $grand_total_semua_booking = 0;

                                foreach ($bookings_data as $booking_id => $data) {
                                    $layanan_count = count($data['layanan']);
                                    $rowspan_value = ($layanan_count > 0) ? $layanan_count : 1;

                                    // Menentukan badge color berdasarkan status
                                    $status_class = '';
                                    if ($data['status'] == 'selesai') {
                                        $status_class = 'badge-success';
                                    } else if ($data['status'] == 'proses') {
                                        $status_class = 'badge-warning';
                                    } else {
                                        $status_class = 'badge-info';
                                    }

                                    echo "<tr>
                                            <td rowspan='". $rowspan_value ."'>" . htmlspecialchars($data['username']) . "</td>
                                            <td rowspan='". $rowspan_value ."'>" . date('d-m-Y', strtotime($data['tanggal'])) . "</td>
                                            <td rowspan='". $rowspan_value ."'>" . date('H:i', strtotime($data['waktu'])) . "</td>
                                            <td rowspan='". $rowspan_value ."'><span class='badge {$status_class}'>" . htmlspecialchars(ucfirst($data['status'])) . "</span></td>";

                                    if ($layanan_count > 0) {
                                        echo "<td>" . htmlspecialchars($data['layanan'][0]['nama']) . "</td>";
                                        echo "<td class='text-right'>Rp " . number_format($data['layanan'][0]['price'], 0, ',', '.') . "</td>";
                                    } else {
                                        echo "<td>Tidak ada layanan</td><td class='text-right'>Rp 0</td>";
                                    }

                                    echo "<td rowspan='". $rowspan_value ."' class='text-right'><strong>Rp " . number_format($data['total_harga'], 0, ',', '.') . "</strong></td>
                                        </tr>";

                                    for ($i = 1; $i < $layanan_count; $i++) {
                                        echo "<tr>
                                                <td>" . htmlspecialchars($data['layanan'][$i]['nama']) . "</td>
                                                <td class='text-right'>Rp " . number_format($data['layanan'][$i]['price'], 0, ',', '.') . "</td>
                                              </tr>";
                                    }
                                    $grand_total_semua_booking += $data['total_harga'];
                                }

                                echo "</tbody>";
                                echo "<tfoot>
                                        <tr>
                                            <td colspan='6' class='text-right'><strong>GRAND TOTAL SEMUA BOOKING</strong></td>
                                            <td class='text-right'><strong>Rp " . number_format($grand_total_semua_booking, 0, ',', '.') . "</strong></td>
                                        </tr>
                                    </tfoot>";
                                echo "</table>";
                                echo "</div>"; // Tutup div responsif

                                echo '<button class="btn btn-info mt-3" onclick="window.print()"><i class="fas fa-print"></i> Cetak Nota</button>';
                                echo '</div>'; // /.card-body
                                echo '</div>'; // /.card

                            } else {
                                echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                                        <i class='icon fas fa-exclamation-triangle'></i> Data booking tidak ditemukan untuk ID pelanggan <strong>" . htmlspecialchars($idPelanggan) . "</strong>.
                                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                            <span aria-hidden='true'>&times;</span>
                                        </button>
                                      </div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                                    <i class='icon fas fa-ban'></i> Terjadi kesalahan saat mengambil data: " . htmlspecialchars($stmt->error) . "
                                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                        <span aria-hidden='true'>&times;</span>
                                    </button>
                                  </div>";
                        }
                        $stmt->close();
                    }
                } else if (isset($_GET['pelanggan_id']) && empty($_GET['pelanggan_id'])) {
                    echo "<div class='alert alert-info alert-dismissible fade show' role='alert'>
                            <i class='icon fas fa-info'></i> Silakan masukkan ID pelanggan untuk mencari nota.
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'>&times;</span>
                            </button>
                          </div>";
                }
                ?>
            </div>
        </section>
        </div>
    <footer class="main-footer">
        <strong>Copyright &copy; 2024 <a href="#">GoWash</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 3.1.0
        </div>
    </footer>

    <aside class="control-sidebar control-sidebar-dark">
    </aside>
    </div>
<script src="AdminLTE-3.1.0/plugins/jquery/jquery.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/jquery-ui/jquery-ui.min.js"></script>
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<script src="AdminLTE-3.1.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/moment/moment.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/daterangepicker/daterangepicker.js"></script>
<script src="AdminLTE-3.1.0/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/summernote/summernote-bs4.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="AdminLTE-3.1.0/dist/js/adminlte.js"></script>
</body>
</html>
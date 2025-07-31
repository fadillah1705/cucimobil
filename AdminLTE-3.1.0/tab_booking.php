<?php
session_start();

// Include the database connection file
// PASTIKAN PATH INI BENAR!
// Jika tab_booking.php ada di 'AdminLTE-3.1.0/' dan conn.php ada di folder 'cucimobil/',
// maka path '../conn.php' sudah benar.
// Jika conn.php berada di folder yang SAMA (misal keduanya di 'AdminLTE-3.1.0/'),
// maka ubah menjadi 'include 'conn.php';'
include '../conn.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// --- Logic for updating booking status and loyalty card (Adapt to PDO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'updateStatus') {
    header('Content-Type: application/json');

    $id = intval($_POST['id'] ?? 0);
    $new_status_int = intval($_POST['status'] ?? -1);

    // Pastikan status string adalah 'Selesai' atau 'Menunggu'
    $new_status_string = ($new_status_int === 1) ? 'Selesai' : 'Menunggu';

    if ($id > 0 && ($new_status_int === 0 || $new_status_int === 1)) {
        // 1. Get current status, pelanggan_id, and tanggal before updating
        // Pastikan $pdo sudah terdefinisi di sini
        if (!isset($pdo) || !$pdo instanceof PDO) {
            error_log("FATAL ERROR: \$pdo is not defined or not a PDO instance at updateStatus start. Ini menunjukkan masalah pada conn.php.");
            echo json_encode(['success' => false, 'message' => 'Kesalahan sistem: Koneksi database tidak tersedia. Mohon hubungi administrator.']);
            exit;
        }

        $stmt_get_current = $pdo->prepare("SELECT status, pelanggan_id, tanggal FROM booking WHERE id = ?");
        $stmt_get_current->execute([$id]);
        $current_booking_data = $stmt_get_current->fetch(PDO::FETCH_ASSOC);

        if (!$current_booking_data) {
            error_log("DEBUG: Booking dengan ID " . $id . " tidak ditemukan saat update status.");
            echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan.']);
            exit;
        }

        $old_status_string = $current_booking_data['status'];
        $pelanggan_id = $current_booking_data['pelanggan_id'];
        $booking_tanggal = $current_booking_data['tanggal']; // Ambil tanggal booking

        // Convert old status to int for comparison
        $old_status_int = ($old_status_string === 'Selesai') ? 1 : 0;

        // Update booking status in the 'booking' table
        $pdo->beginTransaction(); // Mulai transaksi
        try {
            $stmt = $pdo->prepare("UPDATE booking SET status = ? WHERE id = ?");
            if ($stmt->execute([$new_status_string, $id])) {
                error_log("DEBUG: Booking ID " . $id . " berhasil diperbarui status ke " . $new_status_string);

                // Loyalty card update logic based on status change
                if ($pelanggan_id !== NULL) {
                    if ($old_status_int === 0 && $new_status_int === 1) { // Status changed from Menunggu to Selesai
                        error_log("DEBUG: Status berubah dari Menunggu ke Selesai. Mencoba menambah loyalty card untuk pelanggan_id: " . $pelanggan_id . " pada tanggal: " . $booking_tanggal);
                        // Insert a new row for each completed wash (representing one stamp)
                        // Pastikan kolom 'poin' tidak disertakan jika memang sudah dihapus dari tabel
                        $stmt_insert_loyalty = $pdo->prepare("INSERT INTO loyalty_card (pelanggan_id, terakhir_cuci) VALUES (?, ?)");
                        if ($stmt_insert_loyalty->execute([$pelanggan_id, $booking_tanggal])) {
                            error_log("DEBUG: Berhasil menambah entri loyalty card untuk pelanggan ID " . $pelanggan_id . ".");
                        } else {
                            $errorInfo = $stmt_insert_loyalty->errorInfo();
                            error_log("DEBUG: GAGAL menambah entri loyalty card untuk pelanggan ID " . $pelanggan_id . ". Error: " . json_encode($errorInfo));
                            // Rollback jika penambahan loyalty card gagal
                            $pdo->rollBack();
                            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui loyalty card saat menambah: ' . $errorInfo[2]]);
                            exit;
                        }
                    } else if ($old_status_int === 1 && $new_status_int === 0) { // Status changed from Selesai to Menunggu
                        error_log("DEBUG: Status berubah dari Selesai ke Menunggu. Mencoba menghapus loyalty card untuk pelanggan_id: " . $pelanggan_id . " pada tanggal: " . $booking_tanggal);
                        // Untuk mengembalikan, hapus satu entri loyalty card yang paling baru DAN sesuai dengan tanggal booking
                        // Ini penting jika ada multiple booking di hari yang sama
                        $stmt_delete_loyalty = $pdo->prepare("DELETE FROM loyalty_card WHERE pelanggan_id = ? AND terakhir_cuci = ? ORDER BY id DESC LIMIT 1");
                        if ($stmt_delete_loyalty->execute([$pelanggan_id, $booking_tanggal])) {
                            error_log("DEBUG: Berhasil menghapus entri loyalty card untuk pelanggan ID " . $pelanggan_id . ".");
                        } else {
                            $errorInfo = $stmt_delete_loyalty->errorInfo();
                            error_log("DEBUG: GAGAL menghapus entri loyalty card untuk pelanggan ID " . $pelanggan_id . ". Error: " . json_encode($errorInfo));
                            // Ini tidak harus rollback seluruhnya karena mungkin bookingnya tetap mau dihapus
                            // Tapi penting untuk tahu ada masalah
                        }
                    } else {
                        error_log("DEBUG: Perubahan status tidak memicu update loyalty card. Status lama: " . $old_status_string . ", Status baru: " . $new_status_string);
                    }
                } else {
                    error_log("DEBUG: pelanggan_id NULL untuk booking ID " . $id . ", tidak memproses loyalty card.");
                }
                $pdo->commit(); // Commit transaksi jika semua berhasil
                echo json_encode(['success' => true]);
            } else {
                $errorInfo = $stmt->errorInfo();
                $pdo->rollBack(); // Rollback jika update booking gagal
                error_log("DEBUG: Gagal memperbarui status booking ID " . $id . ". Error: " . json_encode($errorInfo));
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status booking: ' . $errorInfo[2]]);
            }
        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback jika terjadi error
            error_log("DEBUG: Exception terjadi saat memperbarui loyalty card: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem saat memperbarui loyalty card: ' . $e->getMessage()]);
        }
    } else {
        error_log("DEBUG: Data tidak valid untuk updateStatus. ID: " . $id . ", Status: " . $new_status_int);
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    }
    exit;
}

// --- Logic for deleting a booking (Adapt to PDO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    header('Content-Type: application/json');

    $id = intval($_POST['id'] ?? 0);

    if ($id > 0) {
        // Pastikan $pdo sudah terdefinisi di sini
        if (!isset($pdo) || !$pdo instanceof PDO) {
            error_log("FATAL ERROR: \$pdo is not defined or not a PDO instance at delete start. Ini menunjukkan masalah pada conn.php.");
            echo json_encode(['success' => false, 'message' => 'Kesalahan sistem: Koneksi database tidak tersedia. Mohon hubungi administrator.']);
            exit;
        }

        // Get booking info to check status and pelanggan_id before deleting
        $stmt_get_booking_info = $pdo->prepare("SELECT status, pelanggan_id, tanggal FROM booking WHERE id = ?");
        $stmt_get_booking_info->execute([$id]);
        $booking_info = $stmt_get_booking_info->fetch(PDO::FETCH_ASSOC);

        if (!$booking_info) {
            error_log("DEBUG: Booking dengan ID " . $id . " tidak ditemukan saat menghapus.");
            echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan.']);
            exit;
        }

        $pdo->beginTransaction(); // Mulai transaksi
        try {
            // If the booking was 'Selesai', decrement loyalty points by removing one loyalty card entry
            if ($booking_info['status'] == 'Selesai' && $booking_info['pelanggan_id'] !== NULL) {
                $pelanggan_id_to_decrement = $booking_info['pelanggan_id'];
                $booking_tanggal_to_delete = $booking_info['tanggal']; // Ambil tanggal booking yang akan dihapus
                error_log("DEBUG: Booking ID " . $id . " berstatus Selesai. Mencoba menghapus entri loyalty card untuk pelanggan_id: " . $pelanggan_id_to_decrement . " pada tanggal: " . $booking_tanggal_to_delete);
                // Remove the most recent loyalty card entry for this pelanggan_id and date
                $stmt_delete_loyalty = $pdo->prepare("DELETE FROM loyalty_card WHERE pelanggan_id = ? AND terakhir_cuci = ? ORDER BY id DESC LIMIT 1");
                if ($stmt_delete_loyalty->execute([$pelanggan_id_to_decrement, $booking_tanggal_to_delete])) {
                    error_log("DEBUG: Berhasil menghapus entri loyalty card saat booking dihapus.");
                } else {
                    $errorInfo = $stmt_delete_loyalty->errorInfo();
                    error_log("DEBUG: GAGAL menghapus entri loyalty card saat booking dihapus. Error: " . json_encode($errorInfo));
                    // Ini tidak harus rollback seluruhnya karena mungkin bookingnya tetap mau dihapus
                    // Tapi penting untuk tahu ada masalah
                }
            }

            $stmt = $pdo->prepare("DELETE FROM booking WHERE id = ?");
            if ($stmt->execute([$id])) {
                $pdo->commit(); // Commit transaksi
                error_log("DEBUG: Booking ID " . $id . " berhasil dihapus.");
                echo json_encode(['success' => true]);
            } else {
                $errorInfo = $stmt->errorInfo();
                $pdo->rollBack(); // Rollback jika delete booking gagal
                error_log("DEBUG: Gagal menghapus booking ID " . $id . ". Error: " . json_encode($errorInfo));
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus data booking: ' . $errorInfo[2]]);
            }
        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback jika terjadi error
            error_log("DEBUG: Exception terjadi saat menghapus booking dan memperbarui loyalty card: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem saat menghapus booking: ' . $e->getMessage()]);
        }
    } else {
        error_log("DEBUG: ID booking tidak valid untuk delete. ID: " . $id);
        echo json_encode(['success' => false, 'message' => 'ID booking tidak valid.']);
    }
    exit;
}


// --- Data fetching for the dashboard (Updated for nama_lengkap) ---
// Pastikan $pdo sudah terdefinisi di sini
if (!isset($pdo) || !$pdo instanceof PDO) {
    error_log("FATAL ERROR: \$pdo is not defined or not a PDO instance at data fetching start. Displaying empty data.");
    $bookingDetail = [];
    $totalMenunggu = 0;
    $totalSelesai = 0;
    $layananTerlaris = '-';
    $totalLayananTerlaris = 0;
    $layananKurang = '-';
    $totalLayananKurang = 0;
} else {
    $bookingDetail = [];
    $sql_booking_detail = "
        SELECT
            b.id,
            b.pelanggan_id,
            u.username, -- Fetch the customer's username from 'users' table
            l.nama AS nama_layanan, -- Fetch the service name from 'layanan' table
            b.waktu,
            b.tanggal,
            b.status
        FROM
            booking b
        JOIN
            users u ON b.pelanggan_id = u.id
        JOIN
            layanan l ON b.id_layanan = l.id -- Join with 'layanan' table
        ORDER BY
            b.tanggal DESC, b.waktu DESC
    ";
    try {
        $stmt_booking_detail = $pdo->query($sql_booking_detail);
        $bookingDetail = $stmt_booking_detail->fetchAll();
    } catch (PDOException $e) {
        error_log("DEBUG: Gagal mengambil detail booking: " . $e->getMessage());
        $bookingDetail = []; // Set kosong agar tidak error di tampilan
    }


    // Calculate total pending bookings
    $bookingQuery = "SELECT COUNT(*) AS total_menunggu FROM booking WHERE status = 'Menunggu' OR status = '0'";
    try {
        $bookingResult = $pdo->query($bookingQuery);
        $totalMenunggu = $bookingResult->fetch(PDO::FETCH_ASSOC)['total_menunggu'];
    } catch (PDOException $e) {
        error_log("DEBUG: Gagal menghitung total menunggu: " . $e->getMessage());
        $totalMenunggu = 0;
    }


    // Calculate total completed bookings
    $selesaiQuery = "SELECT COUNT(*) AS total_selesai FROM booking WHERE status = 'Selesai' OR status = '1'";
    try {
        $selesaiResult = $pdo->query($selesaiQuery);
        $totalSelesai = $selesaiResult->fetch(PDO::FETCH_ASSOC)['total_selesai'];
    } catch (PDOException $e) {
        error_log("DEBUG: Gagal menghitung total selesai: " . $e->getMessage());
        $totalSelesai = 0;
    }


    // Ambil layanan terlaris
    // Corrected to join with 'layanan' table
    $layananQuery = "SELECT l.nama AS layanan_nama, COUNT(*) AS total_pesanan
                        FROM booking b
                        JOIN layanan l ON b.id_layanan = l.id
                        GROUP BY l.nama
                        ORDER BY total_pesanan DESC LIMIT 1";
    try {
        $layananResult = $pdo->query($layananQuery);
        $layananTerlarisData = $layananResult->fetch(PDO::FETCH_ASSOC);

        $layananTerlaris = $layananTerlarisData ? $layananTerlarisData['layanan_nama'] : '-';
        $totalLayananTerlaris = $layananTerlarisData ? $layananTerlarisData['total_pesanan'] : 0;
    } catch (PDOException $e) {
        error_log("DEBUG: Gagal mengambil layanan terlaris: " . $e->getMessage());
        $layananTerlaris = '-';
        $totalLayananTerlaris = 0;
    }


    // Ambil layanan kurang diminati
    // Corrected to join with 'layanan' table
    $layananKurangQuery = "SELECT l.nama AS layanan_nama, COUNT(*) AS total_pesanan
                            FROM booking b
                            JOIN layanan l ON b.id_layanan = l.id
                            GROUP BY l.nama
                            ORDER BY total_pesanan ASC LIMIT 1";
    try {
        $layananKurangResult = $pdo->query($layananKurangQuery);
        $layananKurangData = $layananKurangResult->fetch(PDO::FETCH_ASSOC);

        $layananKurang = $layananKurangData ? $layananKurangData['layanan_nama'] : '-';
        $totalLayananKurang = $layananKurangData ? $layananKurangData['total_pesanan'] : 0;
    } catch (PDOException $e) {
        error_log("DEBUG: Gagal mengambil layanan kurang diminati: " . $e->getMessage());
        $layananKurang = '-';
        $totalLayananKurang = 0;
    }
} // End if ($pdo) check

// No need to explicitly close PDO connection, it closes when script ends
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Dashboard</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">

    <style>
        /* Your existing CSS styles */
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

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
    </div>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index3.html" class="brand-link">
            <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">AdminGoWash</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
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
                            <a href="../admin.php" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                    </li>

                    <li class="nav-item">
                        <a href="tab_booking.php" class="nav-link active">
                            <i class="nav-icon fas fa-th"></i>
                            <p>
                                Booking
                            </p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="../admin-harga.php" class="nav-link">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>
                                Layanan
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

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Tabel Booking</h1>
                    </div></div></div></div>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $totalMenunggu ?></h3>
                                <p>Total Booking Menunggu</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-clock"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $totalSelesai ?></h3>
                                <p>Total Booking Selesai</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-checkmark"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h6><?= htmlspecialchars($layananTerlaris) ?></h6>
                                <p>Layanan Terlaris (<?= $totalLayananTerlaris ?> pesanan)</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-star"></i>
                            </div><br>
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
                                <i class="ion ion-arrow-down-c"></i>
                            </div>
                            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Data Booking Pelanggan</h3>
                            </div>
                            <div class="card-body">
                                <table id="example2" class="table table-bordered table-hover">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th>No.</th> <th>Nama Pelanggan</th>
                                            <th>Layanan</th>
                                            <th>Waktu</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; // Initialize counter for sequential numbering ?>
                                        <?php foreach ($bookingDetail as $booking): ?>
                                            <tr>
                                                <td><?= $i++ ?></td> <td><?= htmlspecialchars($booking['username']) ?></td>
                                                <td><?= htmlspecialchars($booking['nama_layanan']) ?></td>
                                                <td><?= htmlspecialchars($booking['waktu']) ?></td>
                                                <td><?= htmlspecialchars(date('d F Y', strtotime($booking['tanggal']))) ?></td>
                                                <td>
                                                    <select class="form-select status-select" data-id="<?= $booking['id'] ?>" onchange="updateStatus(this)">
                                                        <option value="0" <?= ($booking['status'] == 'Menunggu' || $booking['status'] == '0') ? 'selected' : '' ?> class="status-menunggu">Menunggu</option>
                                                        <option value="1" <?= ($booking['status'] == 'Selesai' || $booking['status'] == '1') ? 'selected' : '' ?> class="status-selesai">Selesai</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteBooking(<?= $booking['id'] ?>)">Hapus</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    <footer class="main-footer">
        <strong>Copyright &copy; 2024 GoWash.</strong>
        All rights reserved.
    </footer>

    <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/chart.js/Chart.min.js"></script>
<script src="plugins/sparklines/sparkline.js"></script>
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="dist/js/adminlte.js"></script>
<script src="dist/js/pages/dashboard.js"></script>
<script>
    function updateStatus(selectElement) {
        const bookingId = selectElement.dataset.id;
        const newStatus = selectElement.value; // 0 or 1

        fetch('tab_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'updateStatus',
                id: bookingId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status booking berhasil diperbarui!');
                // Opsional: perbarui tampilan baris tabel jika perlu, atau muat ulang halaman
                window.location.reload();
            } else {
                alert('Gagal memperbarui status booking: ' + (data.message || 'Terjadi kesalahan.'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat berkomunikasi dengan server.');
        });
    }

    function deleteBooking(bookingId) {
        if (confirm('Apakah Anda yakin ingin menghapus booking ini?')) {
            fetch('tab_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'delete',
                    id: bookingId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Booking berhasil dihapus!');
                    window.location.reload(); // Muat ulang halaman untuk melihat perubahan
                } else {
                    alert('Gagal menghapus booking: ' + (data.message || 'Terjadi kesalahan.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat berkomunikasi dengan server.');
            });
        }
    }
</script>
</body>
</html>
<?php
session_start();

// Database connection details
$host = "localhost";
$user = "root";
$pass = "";
$db_cucimobil = "cucimobil"; // Assuming your database name is 'cucimobil'

// Establish database connection
$conn = new mysqli($host, $user, $pass, $db_cucimobil);

// Check connection
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// --- Logic for updating booking status and loyalty card ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'updateStatus') {
    header('Content-Type: application/json');

    $id = intval($_POST['id'] ?? 0);
    $new_status_int = intval($_POST['status'] ?? -1); // 0 for Menunggu, 1 for Selesai
    
    // Convert integer status to string for DB consistency if your 'status' column is VARCHAR
    $new_status_string = ($new_status_int === 1) ? 'Selesai' : 'Menunggu';

    if ($id > 0 && ($new_status_int === 0 || $new_status_int === 1)) {
        // 1. Get current status and pelanggan_id before updating
        $stmt_get_current = $conn->prepare("SELECT status, pelanggan_id FROM booking WHERE id = ?");
        $stmt_get_current->bind_param("i", $id);
        $stmt_get_current->execute();
        $result_current = $stmt_get_current->get_result();
        $current_booking_data = $result_current->fetch_assoc();
        $stmt_get_current->close();

        $old_status_string = $current_booking_data['status'];
        $pelanggan_id = $current_booking_data['pelanggan_id'];

        // Convert old status string to integer for comparison if necessary (assuming 'Selesai' maps to 1, 'Menunggu' to 0)
        $old_status_int = ($old_status_string === 'Selesai' || $old_status_string === '1') ? 1 : 0;

        // Update booking status in the 'booking' table
        $stmt = $conn->prepare("UPDATE booking SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status_string, $id); // Bind new_status_string (e.g., 'Selesai' or 'Menunggu')

        if ($stmt->execute()) {
            // Loyalty card update logic based on status change
            if ($pelanggan_id !== NULL) {
                // Scenario 1: Status changed from Menunggu to Selesai
                if ($old_status_int === 0 && $new_status_int === 1) {
                    $points_per_wash = 10; // Define how many points are earned per wash
                    $stmt_update_loyalty = $conn->prepare("INSERT INTO loyalty_card (pelanggan_id, total_cuci, poin, terakhir_cuci) VALUES (?, 1, ?, CURDATE())
                                                            ON DUPLICATE KEY UPDATE total_cuci = total_cuci + 1, poin = poin + ?, terakhir_cuci = CURDATE()");
                    $stmt_update_loyalty->bind_param("iii", $pelanggan_id, $points_per_wash, $points_per_wash);
                    $stmt_update_loyalty->execute();
                    $stmt_update_loyalty->close();
                }
                // Scenario 2: Status changed from Selesai to Menunggu (correction)
                else if ($old_status_int === 1 && $new_status_int === 0) {
                    $points_per_wash = 10; // Points to decrement
                    // Ensure total_cuci and poin don't go below 0
                    $stmt_update_loyalty = $conn->prepare("UPDATE loyalty_card SET total_cuci = GREATEST(0, total_cuci - 1), poin = GREATEST(0, poin - ?), terakhir_cuci = CURDATE() WHERE pelanggan_id = ?");
                    $stmt_update_loyalty->bind_param("ii", $points_per_wash, $pelanggan_id);
                    $stmt_update_loyalty->execute();
                    $stmt_update_loyalty->close();
                }
                // If status didn't change (e.g., Selesai to Selesai or Menunggu to Menunggu), no loyalty update is needed.
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status booking: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    }
    exit; // Stop script execution after handling AJAX request
}

// --- Logic for deleting a booking ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    header('Content-Type: application/json');

    $id = intval($_POST['id'] ?? 0);

    if ($id > 0) {
        // Optional: If you want to decrement loyalty card when a 'Selesai' booking is deleted
        // First, get the status and pelanggan_id of the booking being deleted
        $stmt_get_booking_info = $conn->prepare("SELECT status, pelanggan_id FROM booking WHERE id = ?");
        $stmt_get_booking_info->bind_param("i", $id);
        $stmt_get_booking_info->execute();
        $result_booking_info = $stmt_get_booking_info->get_result();
        $booking_info = $result_booking_info->fetch_assoc();
        $stmt_get_booking_info->close();

        if ($booking_info && ($booking_info['status'] == 'Selesai' || $booking_info['status'] == 1) && $booking_info['pelanggan_id'] !== NULL) {
            $pelanggan_id_to_decrement = $booking_info['pelanggan_id'];
            $points_per_wash = 10;
            $stmt_decrement_loyalty = $conn->prepare("UPDATE loyalty_card SET total_cuci = GREATEST(0, total_cuci - 1), poin = GREATEST(0, poin - ?) WHERE pelanggan_id = ?");
            $stmt_decrement_loyalty->bind_param("ii", $points_per_wash, $pelanggan_id_to_decrement);
            $stmt_decrement_loyalty->execute();
            $stmt_decrement_loyalty->close();
        }

        $stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data booking.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'ID booking tidak valid.']);
    }
    exit; // Stop script execution after handling AJAX request
}


// --- Data fetching for the dashboard ---
$bookingDetail = [];
$result2 = $conn->query("SELECT * FROM booking ORDER BY id DESC"); // Order by ID to see most recent first
if ($result2) {
    while ($row = $result2->fetch_assoc()) {
        $bookingDetail[] = $row;
    }
} else {
    // Handle query error if any
    error_log("Error fetching booking details: " . $conn->error);
}


// Calculate total pending bookings
$bookingQuery = "SELECT COUNT(*) AS total_menunggu FROM booking WHERE status = 'Menunggu' OR status = 0"; // Use 'Menunggu' or 0 based on your current data
$bookingResult = $conn->query($bookingQuery);
$bookingData = $bookingResult->fetch_assoc();
$totalMenunggu = $bookingData['total_menunggu'];

// Calculate total completed bookings
$selesaiQuery = "SELECT COUNT(*) AS total_selesai FROM booking WHERE status = 'Selesai' OR status = 1"; // Use 'Selesai' or 1
$selesaiResult = $conn->query($selesaiQuery);
$selesaiData = $selesaiResult->fetch_assoc();
$totalSelesai = $selesaiData['total_selesai'];

// Ambil layanan terlaris
$layananQuery = "SELECT layanan, COUNT(*) AS total_pesanan FROM booking GROUP BY layanan ORDER BY total_pesanan DESC LIMIT 1";
$layananResult = $conn->query($layananQuery);
$layananTerlarisData = $layananResult->fetch_assoc();

$layananTerlaris = $layananTerlarisData ? $layananTerlarisData['layanan'] : '-';
$totalLayananTerlaris = $layananTerlarisData ? $layananTerlarisData['total_pesanan'] : 0;

// Ambil layanan kurang diminati
$layananKurangQuery = "SELECT layanan, COUNT(*) AS total_pesanan FROM booking GROUP BY layanan ORDER BY total_pesanan ASC LIMIT 1";
$layananKurangResult = $conn->query($layananKurangQuery);
$layananKurangData = $layananKurangResult->fetch_assoc();

$layananKurang = $layananKurangData ? $layananKurangData['layanan'] : '-';
$totalLayananKurang = $layananKurangData ? $layananKurangData['total_pesanan'] : 0;

$conn->close(); // Close the database connection after all operations
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

    <br>
    <br>
    <br>

    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-6">
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
                                if ($counter > 10) break; // Only display the first 10 rows
                            ?>
                                <tr id="row-<?= $row['id'] ?>">
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['layanan']) ?></td>
                                    <td><?= date('H:i', strtotime($row['waktu'])) ?></td>
                                    <td><?= date('d-m-Y', strtotime(explode(' ', $row['tanggal'])[0])) ?></td>
                                    <td>
                                        <span class="badge status-toggle bg-<?= ($row['status'] == 'Selesai' || $row['status'] == 1) ? 'success' : 'warning' ?>"
                                            data-id="<?= htmlspecialchars($row['id']) ?>"
                                            data-status="<?= ($row['status'] == 'Selesai' || $row['status'] == 1) ? 1 : 0 ?>"
                                            style="cursor:pointer;">
                                            <?= ($row['status'] == 'Selesai' || $row['status'] == 1) ? 'Selesai' : 'Menunggu' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="hapusBooking(<?= htmlspecialchars($row['id']) ?>)">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </div>
    <footer class="main-footer">
        <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 3.1.0
        </div>
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
<script src="dist/js/demo.js"></script>
<script src="dist/js/pages/dashboard.js"></script>

<script>
    // Function to handle booking deletion
    function hapusBooking(id) {
        if (confirm("Yakin ingin menghapus data booking ini dari database?")) {
            fetch('tab_booking.php', { // Send request to tab_booking.php itself
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'delete', id: id })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('row-' + id).remove();
                    // Reload for simplicity to update counts and dashboard stats
                    window.location.reload(); 
                } else {
                    alert('Gagal menghapus data booking: ' + (data.message || 'Unknown error.'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Terjadi kesalahan saat menghapus.');
            });
        }
    }

    // jQuery for updating status
    $(document).ready(function () {
        $(document).on('click', '.status-toggle', function () {
            const badge = $(this);
            const id = badge.data('id');
            const current_status_val = badge.data('status'); // 1 or 0
            const next_status_val = current_status_val == 1 ? 0 : 1; // Toggle status

            $.ajax({
                url: 'tab_booking.php', // Send request to tab_booking.php itself
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'updateStatus',
                    id: id,
                    status: next_status_val
                },
                success: function (res) {
                    if (res.success) {
                        // The PHP side will handle the loyalty card update/decrement
                        // We just need to reload the page to reflect all changes (dashboard counts, loyalty card on customer's profile)
                        window.location.reload(); // Reload for simplicity to update counts and dashboard stats
                    } else {
                        alert('Gagal: ' + (res.message || '...'));
                    }
                },
                error: function (xhr, status, err) {
                    console.error('AJAX Error:', status, err, xhr.responseText);
                    alert('Terjadi kesalahan koneksi saat memperbarui status.');
                }
            });
        });
    });
</script>

</body>
</html>
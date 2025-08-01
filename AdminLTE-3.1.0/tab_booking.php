<?php
session_start();
include "../conn.php"; // Pastikan path ini benar sesuai lokasi conn.php Anda

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); // Sesuaikan path jika perlu
    exit;
}

// --- Logic for updating booking status and loyalty card (PDO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'updateStatus') {
    header('Content-Type: application/json');

    $id = intval($_POST['id'] ?? 0);
    $new_status_int = intval($_POST['status'] ?? -1);

    // Map integer status to string status
    $new_status_string = ($new_status_int === 1) ? 'Selesai' : 'Menunggu';
    if ($new_status_int === 0) {
        $new_status_string = 'Menunggu';
    } elseif ($new_status_int === 1) {
        $new_status_string = 'Selesai';
    } else {
        echo json_encode(['success' => false, 'message' => 'Status tidak valid.']);
        exit;
    }

    if ($id > 0) {
        // 1. Get current status, pelanggan_id, and tanggal before updating
        $stmt_get_current = $pdo->prepare("SELECT status, pelanggan_id, tanggal FROM booking WHERE id = ?");
        $stmt_get_current->execute([$id]);
        $current_booking_data = $stmt_get_current->fetch(PDO::FETCH_ASSOC);

        if (!$current_booking_data) {
            echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan.']);
            exit;
        }

        $old_status_string = $current_booking_data['status'];
        $pelanggan_id = $current_booking_data['pelanggan_id'];
        $booking_tanggal = $current_booking_data['tanggal'];

        $pdo->beginTransaction(); // Start transaction
        try {
            // Update booking status in the 'booking' table
            $stmt = $pdo->prepare("UPDATE booking SET status = ? WHERE id = ?");
            if ($stmt->execute([$new_status_string, $id])) {
                // Loyalty card update logic based on status change
                if ($pelanggan_id !== NULL) {
                    if ($old_status_string === 'Menunggu' && $new_status_string === 'Selesai') {
                        // Status changed from Menunggu to Selesai: Add one entry to loyalty_card
                        $stmt_insert_loyalty = $pdo->prepare("INSERT INTO loyalty_card (pelanggan_id, terakhir_cuci) VALUES (?, ?)");
                        if (!$stmt_insert_loyalty->execute([$pelanggan_id, $booking_tanggal])) {
                            throw new Exception('Gagal menambah entri loyalty card: ' . json_encode($stmt_insert_loyalty->errorInfo()));
                        }
                    } else if ($old_status_string === 'Selesai' && $new_status_string === 'Menunggu') {
                        // Status changed from Selesai to Menunggu: Remove one entry from loyalty_card
                        // We remove the most recent one for this customer and date to reverse the action
                        $stmt_delete_loyalty = $pdo->prepare("DELETE FROM loyalty_card WHERE pelanggan_id = ? AND terakhir_cuci = ? ORDER BY id DESC LIMIT 1");
                        if (!$stmt_delete_loyalty->execute([$pelanggan_id, $booking_tanggal])) {
                            throw new Exception('Gagal menghapus entri loyalty card: ' . json_encode($stmt_delete_loyalty->errorInfo()));
                        }
                    }
                }
                $pdo->commit(); // Commit transaction if all successful
                echo json_encode(['success' => true]);
            } else {
                $errorInfo = $stmt->errorInfo();
                throw new Exception('Gagal memperbarui status booking: ' . $errorInfo[2]);
            }
        } catch (Exception $e) {
            $pdo->rollBack(); // Rollback if any error occurs
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID booking tidak valid.']);
    }
    exit;
}

// --- Logic for deleting a booking (PDO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    header('Content-Type: application/json');

    $id = intval($_POST['id'] ?? 0);

    if ($id > 0) {
        // Get booking info to check status and pelanggan_id before deleting
        $stmt_get_booking_info = $pdo->prepare("SELECT status, pelanggan_id, tanggal FROM booking WHERE id = ?");
        $stmt_get_booking_info->execute([$id]);
        $booking_info = $stmt_get_booking_info->fetch(PDO::FETCH_ASSOC);

        if (!$booking_info) {
            echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan.']);
            exit;
        }

        $pdo->beginTransaction(); // Start transaction
        try {
            // If the booking was 'Selesai', decrement loyalty points by removing one loyalty card entry
            if ($booking_info['status'] == 'Selesai' && $booking_info['pelanggan_id'] !== NULL) {
                $pelanggan_id_to_decrement = $booking_info['pelanggan_id'];
                $booking_tanggal_to_delete = $booking_info['tanggal'];

                // Remove the most recent loyalty card entry for this pelanggan_id and date
                $stmt_delete_loyalty = $pdo->prepare("DELETE FROM loyalty_card WHERE pelanggan_id = ? AND terakhir_cuci = ? ORDER BY id DESC LIMIT 1");
                if (!$stmt_delete_loyalty->execute([$pelanggan_id_to_decrement, $booking_tanggal_to_delete])) {
                    // Log the error but don't necessarily stop deletion of the booking itself
                    error_log("Error deleting loyalty card entry for pelanggan_id: " . $pelanggan_id_to_decrement . " on " . $booking_tanggal_to_delete . " - " . json_encode($stmt_delete_loyalty->errorInfo()));
                }
            }

            // Delete booking from 'booking' table
            // Due to ON DELETE CASCADE on 'booking_layanan' foreign key, related entries will be deleted automatically
            $stmt = $pdo->prepare("DELETE FROM booking WHERE id = ?");
            if ($stmt->execute([$id])) {
                $pdo->commit(); // Commit transaction
                echo json_encode(['success' => true]);
            } else {
                $errorInfo = $stmt->errorInfo();
                throw new Exception('Gagal menghapus data booking: ' . $errorInfo[2]);
            }
        } catch (Exception $e) {
            $pdo->rollBack(); // Rollback if any error occurs
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID booking tidak valid.']);
    }
    exit;
}

// --- Data fetching for the dashboard (PDO, including multi-service) ---

// Total Booking Menunggu (status 'Menunggu')
$totalMenunggu = 0;
$stmt_menunggu = $pdo->prepare("SELECT COUNT(*) AS total_menunggu FROM booking WHERE status = 'Menunggu'");
if ($stmt_menunggu->execute()) {
    $totalMenunggu = $stmt_menunggu->fetchColumn();
}

// Total Booking Selesai (status 'Selesai')
$totalSelesai = 0;
$stmt_selesai = $pdo->prepare("SELECT COUNT(*) AS total_selesai FROM booking WHERE status = 'Selesai'");
if ($stmt_selesai->execute()) {
    $totalSelesai = $stmt_selesai->fetchColumn();
}

// Layanan Terlaris (Top 1 Most Popular Service)
$layananTerlaris = '-';
$totalLayananTerlaris = 0;
$stmt_terlaris = $pdo->prepare("
    SELECT l.nama AS layanan_nama, COUNT(bl.layanan_id) AS total_pesanan
    FROM booking_layanan bl
    JOIN layanan l ON bl.layanan_id = l.id
    GROUP BY l.nama
    ORDER BY total_pesanan DESC
    LIMIT 1
");
if ($stmt_terlaris->execute()) {
    $data_terlaris = $stmt_terlaris->fetch(PDO::FETCH_ASSOC);
    if ($data_terlaris) {
        $layananTerlaris = $data_terlaris['layanan_nama'];
        $totalLayananTerlaris = $data_terlaris['total_pesanan'];
    }
}

// Layanan Kurang Diminati (Top 1 Least Popular Service)
$layananKurang = '-';
$totalLayananKurang = 0;
$stmt_kurang = $pdo->prepare("
    SELECT l.nama AS layanan_nama, COUNT(bl.layanan_id) AS total_pesanan
    FROM booking_layanan bl
    JOIN layanan l ON bl.layanan_id = l.id
    GROUP BY l.nama
    ORDER BY total_pesanan ASC
    LIMIT 1
");
if ($stmt_kurang->execute()) {
    $data_kurang = $stmt_kurang->fetch(PDO::FETCH_ASSOC);
    if ($data_kurang) {
        $layananKurang = $data_kurang['layanan_nama'];
        $totalLayananKurang = $data_kurang['total_pesanan'];
    }
}

// Get all booking details for the table (PDO, including multi-service)
$bookingDetail = [];
$sql_booking_detail = "
    SELECT
        b.id,
        u.username,
        GROUP_CONCAT(l.nama ORDER BY l.nama ASC SEPARATOR ' + ') AS nama_layanan_gabungan,
        b.waktu,
        b.tanggal,
        b.status
    FROM
        booking b
    JOIN
        users u ON b.pelanggan_id = u.id
    LEFT JOIN
        booking_layanan bl ON b.id = bl.booking_id
    LEFT JOIN
        layanan l ON bl.layanan_id = l.id
    GROUP BY
        b.id, u.username, b.waktu, b.tanggal, b.status
    ORDER BY
        b.tanggal DESC, b.waktu DESC
";
$stmt_booking_detail = $pdo->query($sql_booking_detail);
$bookingDetail = $stmt_booking_detail->fetchAll();

// No need to explicitly close PDO connection, it closes when script ends
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Tabel Booking</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="../AdminLTE-3.1.0/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="../AdminLTE-3.1.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../AdminLTE-3.1.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Custom styling for cards and forms */
        .card {
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            border: none;
        }
        .card-header {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            background-color: #3f474e;
            color: #fff;
            border-bottom: 1px solid #555;
        }
        .small-box {
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        /* Table styling */
        .table-responsive {
            border-radius: 12px;
            overflow: hidden; /* Ensures border-radius is applied to the table */
        }
        .table thead th {
            background-color: #495057; /* Darker header for better contrast */
            color: #fff;
            border-bottom: 2px solid #6c757d;
        }
        .table tbody tr {
            transition: background-color 0.2s ease;
        }
        .table tbody tr:hover {
            background-color: #3f474e;
        }
        .table td, .table th {
            border-top: 1px solid #555;
            vertical-align: middle;
        }
        
        /* Status Select Styling */
        .status-select {
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 8px;
            border: 1px solid;
            transition: background-color 0.2s, border-color 0.2s;
            appearance: none; /* Hide default dropdown arrow */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        .status-menunggu {
            color: #f7c948; /* kuning terang */
            background-color: #3a2f1e; /* coklat gelap */
            border-color: #f7c948;
        }

        .status-selesai {
            color: #28a745; /* hijau terang */
            background-color: #1e2f1e; /* hijau gelap */
            border-color: #28a745;
        }
        
        /* Delete Button */
        .btn-danger-custom {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
            transition: background-color 0.2s, border-color 0.2s;
        }
        .btn-danger-custom:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>

</head>
<body class="hold-transition sidebar-mini layout-fixed dark-mode">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="../AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
    </div>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="../admin.php" class="brand-link">
            <img src="../AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">AdminGoWash</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="../AdminLTE-3.1.0/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
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
                        <a href="../admin.php" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="tab_booking.php" class="nav-link active">
                            <i class="nav-icon fas fa-th"></i>
                            <p>Booking</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../admin-harga.php" class="nav-link">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>Layanan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../kasir.php" class="nav-link">
                            <i class="nav-icon fas fa-desktop"></i>
                            <p>Kasir</p>
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
                    </div>
                </div>
            </div>
        </div>
        
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
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover m-0">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Nama Pelanggan</th>
                                                <th>Layanan</th>
                                                <th>Waktu</th>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; ?>
                                            <?php foreach ($bookingDetail as $booking): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td><?= htmlspecialchars($booking['username']) ?></td>
                                                    <td><?= htmlspecialchars($booking['nama_layanan_gabungan'] ?: 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($booking['waktu']) ?></td>
                                                    <td><?= htmlspecialchars(date('d F Y', strtotime($booking['tanggal']))) ?></td>
                                                    <td>
                                                        <select class="form-select status-select <?= ($booking['status'] == 'Selesai') ? 'status-selesai' : 'status-menunggu' ?>" data-id="<?= $booking['id'] ?>" onchange="updateStatus(this)">
                                                            <option value="0" <?= ($booking['status'] == 'Menunggu') ? 'selected' : '' ?> class="status-menunggu">Menunggu</option>
                                                            <option value="1" <?= ($booking['status'] == 'Selesai') ? 'selected' : '' ?> class="status-selesai">Selesai</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-danger-custom" onclick="deleteBooking(<?= $booking['id'] ?>)">
                                                            <i class="bi bi-trash"></i> Hapus
                                                        </button>
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
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2024 <a href="#">GoWash</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>
</div>

<script src="../AdminLTE-3.1.0/plugins/jquery/jquery.min.js"></script>
<script src="../AdminLTE-3.1.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../AdminLTE-3.1.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="../AdminLTE-3.1.0/dist/js/adminlte.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function updateStatus(selectElement) {
        const bookingId = selectElement.dataset.id;
        const newStatus = selectElement.value; // 0 or 1
        const statusText = newStatus === '1' ? 'Selesai' : 'Menunggu';

        Swal.fire({
            title: 'Konfirmasi Perubahan Status?',
            text: `Anda yakin ingin mengubah status booking ini menjadi "${statusText}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Ubah!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
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
                        Swal.fire(
                            'Berhasil!',
                            'Status booking berhasil diperbarui.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Gagal!',
                            'Gagal memperbarui status: ' + (data.message || 'Terjadi kesalahan.'),
                            'error'
                        ).then(() => {
                            // Revert select option on failure
                            const originalStatus = (selectElement.classList.contains('status-selesai')) ? '1' : '0';
                            selectElement.value = originalStatus;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan saat berkomunikasi dengan server.',
                        'error'
                    );
                    // Revert select option on network error
                    const originalStatus = (selectElement.classList.contains('status-selesai')) ? '1' : '0';
                    selectElement.value = originalStatus;
                });
            } else {
                // If cancelled, revert the select option back to its original state
                const originalStatus = (selectElement.classList.contains('status-selesai')) ? '1' : '0';
                selectElement.value = originalStatus;
            }
        });
    }

    function deleteBooking(bookingId) {
        Swal.fire({
            title: 'Hapus Booking?',
            text: "Booking akan dihapus secara permanen. Tindakan ini tidak bisa dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
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
                        Swal.fire(
                            'Dihapus!',
                            'Booking berhasil dihapus.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Gagal!',
                            'Gagal menghapus booking: ' + (data.message || 'Terjadi kesalahan.'),
                            'error'
                        );
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan saat berkomunikasi dengan server.',
                        'error'
                    );
                });
            }
        });
    }
</script>
</body>
</html>
<?php
session_start();
include "conn.php"; // Pastikan file conn.php ada dan koneksi berhasil

// Cek session login admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// --- Logika untuk MENANGANI PENYIMPANAN BOOKING dari form modal (via AJAX POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'], $_POST['tanggal'], $_POST['waktu'], $_POST['service_ids'])) {
    header('Content-Type: application/json'); // Respon dalam format JSON

    $nama          = $conn->real_escape_string($_POST['nama']);
    $tanggal       = $conn->real_escape_string($_POST['tanggal']);
    $waktu         = $conn->real_escape_string($_POST['waktu']);
    $service_ids   = $_POST['service_ids']; // Ini akan menjadi array dari Select2

    // Validasi input layanan
    if (!is_array($service_ids) || empty($service_ids)) {
        echo json_encode(['status' => 'gagal', 'error' => 'Pilih setidaknya satu layanan.']);
        exit;
    }

    $pelanggan_id = NULL;

    // Ambil pelanggan_id berdasarkan nama (username)
    $stmt_get_pelanggan_id = $conn->prepare("SELECT id FROM users WHERE username = ?");
    if ($stmt_get_pelanggan_id) {
        $stmt_get_pelanggan_id->bind_param("s", $nama);
        $stmt_get_pelanggan_id->execute();
        $result_pelanggan = $stmt_get_pelanggan_id->get_result();
        if ($result_pelanggan->num_rows > 0) {
            $pelanggan_data = $result_pelanggan->fetch_assoc();
            $pelanggan_id = $pelanggan_data['id'];
        } else {
            echo json_encode(['status' => 'gagal', 'error' => 'Nama pelanggan tidak ditemukan.']);
            $stmt_get_pelanggan_id->close();
            exit;
        }
        $stmt_get_pelanggan_id->close();
    } else {
        echo json_encode(['status' => 'gagal', 'error' => 'Error preparing statement to get user ID: ' . $conn->error]);
        exit;
    }

    // Mulai transaksi database untuk memastikan integritas data
    $conn->begin_transaction();

    try {
        // 1. Simpan booking utama ke tabel 'booking'
        $sql_insert_booking = "INSERT INTO booking (pelanggan_id, waktu, tanggal, status) VALUES (?, ?, ?, 'Menunggu')";
        $stmt_booking = $conn->prepare($sql_insert_booking);
        if ($stmt_booking === false) {
            throw new Exception("Error preparing booking statement: " . $conn->error);
        }
        $stmt_booking->bind_param("iss", $pelanggan_id, $waktu, $tanggal);
        $stmt_booking->execute();
        $booking_id = $stmt_booking->insert_id;
        $stmt_booking->close();

        // 2. Simpan setiap layanan yang dipilih ke tabel 'booking_layanan'
        $sql_insert_booking_service = "INSERT INTO booking_layanan (booking_id, layanan_id) VALUES (?, ?)";
        $stmt_booking_service = $conn->prepare($sql_insert_booking_service);
        if ($stmt_booking_service === false) {
            throw new Exception("Error preparing booking_layanan statement: " . $conn->error);
        }

        foreach ($service_ids as $layanan_id) {
            $layanan_id = intval($layanan_id);

            $stmt_check_service = $conn->prepare("SELECT id FROM layanan WHERE id = ?");
            if ($stmt_check_service === false) {
                throw new Exception("Error preparing service check statement: " . $conn->error);
            }
            $stmt_check_service->bind_param("i", $layanan_id);
            $stmt_check_service->execute();
            $result_service = $stmt_check_service->get_result();
            if ($result_service->num_rows === 0) {
                throw new Exception("ID layanan tidak valid atau tidak ditemukan: " . $layanan_id);
            }
            $stmt_check_service->close();

            $stmt_booking_service->bind_param("ii", $booking_id, $layanan_id);
            $stmt_booking_service->execute();
        }
        $stmt_booking_service->close();

        $conn->commit();
        echo json_encode(['status' => 'sukses', 'message' => 'Booking dengan beberapa layanan berhasil disimpan!', 'booking_id' => $booking_id]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'gagal', 'error' => 'Gagal menyimpan booking: ' . $e->getMessage()]);
    }
    exit;
}

// --- Logika untuk MENGAMBIL DETAIL BOOKING untuk modal detail (via AJAX POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'getBookingDetails') {
    header('Content-Type: application/json');

    $booking_id = intval($_POST['id'] ?? 0);

    if ($booking_id > 0) {
        $sql_detail = "SELECT
                            b.id,
                            u.username AS nama_user,
                            GROUP_CONCAT(s.nama ORDER BY s.nama ASC SEPARATOR ' + ') AS nama_layanan_gabungan,
                            b.tanggal,
                            b.waktu,
                            b.status
                       FROM booking b
                       JOIN users u ON b.pelanggan_id = u.id
                       LEFT JOIN booking_layanan bl ON b.id = bl.booking_id
                       LEFT JOIN layanan s ON bl.layanan_id = s.id
                       WHERE b.id = ?
                       GROUP BY b.id, b.tanggal, b.waktu, u.username, b.status";

        $stmt_detail = $conn->prepare($sql_detail);
        if ($stmt_detail) {
            $stmt_detail->bind_param("i", $booking_id);
            $stmt_detail->execute();
            $result_detail = $stmt_detail->get_result();

            if ($result_detail->num_rows > 0) {
                $data = $result_detail->fetch_assoc();
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan.']);
            }
            $stmt_detail->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error preparing detail statement: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID booking tidak valid.']);
    }
    exit;
}

// --- LOGIKA PENGAMBILAN DATA UNTUK DASHBOARD ---
$query = "SELECT COUNT(*) AS total_booking FROM booking";
$result = $conn->query($query);
$totalBooking = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total_booking'] : 0;

$userQuery = "SELECT COUNT(*) AS total_user FROM users WHERE role='user'";
$userResult = $conn->query($userQuery);
$totalUser = ($userResult && $userResult->num_rows > 0) ? $userResult->fetch_assoc()['total_user'] : 0;

$layananQuery = "SELECT COUNT(*) AS total_layanan FROM layanan WHERE is_active = 1";
$layananResult = $conn->query($layananQuery);
$totalLayanan = ($layananResult && $layananResult->num_rows > 0) ? $layananResult->fetch_assoc()['total_layanan'] : 0;

$todayQuery = "SELECT COUNT(*) AS booking_hari_ini FROM booking WHERE tanggal = CURDATE()";
$todayResult = $conn->query($todayQuery);
$bookingHariIni = ($todayResult && $todayResult->num_rows > 0) ? $todayResult->fetch_assoc()['booking_hari_ini'] : 0;

$namaQuery = "SELECT id, username AS nama FROM users WHERE role = 'user' ORDER BY username ASC";
$namaResult = $conn->query($namaQuery);
$daftarNama = [];
if ($namaResult) {
    while ($row = $namaResult->fetch_assoc()) {
        $daftarNama[] = ['id' => $row['id'], 'nama' => $row['nama']];
    }
}

$layananListQuery = "SELECT id, nama FROM layanan WHERE is_active = 1 ORDER BY nama ASC";
$layananListResult = $conn->query($layananListQuery);
$daftarLayananUntukDropdown = [];
if ($layananListResult) {
    while ($row = $layananListResult->fetch_assoc()) {
        $daftarLayananUntukDropdown[] = ['id' => $row['id'], 'nama' => $row['nama']];
    }
}

$events = [];
$sql_events = "SELECT
                    b.id,
                    u.username AS nama_user,
                    GROUP_CONCAT(s.nama ORDER BY s.nama ASC SEPARATOR ' + ') AS nama_layanan_gabungan,
                    b.waktu,
                    b.tanggal,
                    b.status
                FROM booking b
                JOIN users u ON b.pelanggan_id = u.id
                LEFT JOIN booking_layanan bl ON b.id = bl.booking_id
                LEFT JOIN layanan s ON bl.layanan_id = s.id
                GROUP BY b.id, b.tanggal, b.waktu, u.username, b.status
                ORDER BY b.tanggal, b.waktu";

$result_events = $conn->query($sql_events);
if ($result_events) {
    while ($row = $result_events->fetch_assoc()) {
        $eventTitle = $row['nama_user'] . ' - ' . ($row['nama_layanan_gabungan'] ?: 'No Service') . ' (' . $row['status'] . ')';
        $eventColor = '';

        switch ($row['status']) {
            case 'Menunggu':
                $eventColor = '#ffc107'; // Yellow
                break;
            case 'Selesai':
                $eventColor = '#28a745'; // Green
                break;
            case 'Dibatalkan':
                $eventColor = '#dc3545'; // Red
                break;
            default:
                $eventColor = '#007bff'; // Blue (warna default lain)
                break;
        }

        $events[] = [
            'id'    => $row['id'],
            'title' => $eventTitle,
            'start' => $row['tanggal'] . 'T' . $row['waktu'],
            'color' => $eventColor,
        ];
    }
} else {
    error_log("Error fetching calendar events: " . $conn->error);
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" />

    <style>
        /* CSS yang lebih rapi dan terstruktur */
        body {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Override AdminLTE colors for a more modern look */
        .info-box-icon { border-radius: 8px; }
        .info-box { border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card { border-radius: 12px; box-shadow: 0 6px 12px rgba(0,0,0,0.15); }
        .card-header { border-top-left-radius: 12px; border-top-right-radius: 12px; border-bottom: 1px solid #dee2e6; }

        /* FullCalendar Styling */
        #calendar {
            height: 900px;
            font-family: 'Poppins', sans-serif;
        }
        .fc-col-header-cell-cushion,
        .fc-daygrid-day-number {
            color: white !important;
        }
        .fc-daygrid-day-frame {
            height: 120px !important;
            min-height: 120px !important;
        }
        .fc-daygrid-day-events {
            overflow-y: auto;
            max-height: calc(120px - 30px);
            margin-right: 2px;
            padding: 2px;
        }
        .fc-daygrid-day-top {
            height: 30px;
        }
        /* [PERUBAHAN 1] Mengubah ukuran font event kalender */
        .fc-event {
            font-size: 11px; /* Ukuran font dikecilkan dari 12px menjadi 11px */
            padding: 2px 4px;
            margin-bottom: 2px;
            white-space: normal;
            word-break: break-word;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .fc-event:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Modal Styling */
        .modal-header, .modal-footer { border-color: #343a40; }
        .modal-content { border-radius: 12px; background-color: #343a40; color: #f8f9fa; }
        .modal-title { color: #fff; }
        
        /* Form Styling */
        .form-control,
        .select2-container--bootstrap4 .select2-selection--multiple {
            background-color: #454d55;
            color: #f8f9fa;
            border: 1px solid #6c757d;
        }
        .form-control:focus,
        .select2-container--bootstrap4.select2-container--focus .select2-selection--multiple {
            background-color: #343a40;
            border-color: #17a2b8;
            box-shadow: 0 0 0 .2rem rgba(23,162,184,.25);
        }
        
        /* Select2 Specific Styling */
        /* [PERUBAHAN 2] Menengahkan placeholder "Pilih Layanan..." */
        .select2-container--bootstrap4 .select2-selection__placeholder {
            text-align: center;
            width: 100%;
        }
        /* Mengubah tampilan tag yang dipilih */
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border: 1px solid #0056b3;
            color: white;
            font-size: 14px;
            border-radius: 4px;
            padding: 2px 8px;
            margin-top: 5px;
            margin-right: 5px;
            transition: background-color 0.2s;
        }
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
            margin-left: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ffc107;
        }
        /* Tampilan dropdown */
        .select2-container--bootstrap4 .select2-dropdown {
            background-color: #454d55;
            border: 1px solid #6c757d;
            border-radius: 8px;
        }
        .select2-container--bootstrap4 .select2-results__option {
            color: #f8f9fa;
        }
        .select2-container--bootstrap4 .select2-results__option--highlighted {
            background-color: #17a2b8 !important;
            color: white;
        }
        .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
            background-color: #343a40;
            border: 1px solid #6c757d;
            color: #f8f9fa;
        }
    </style>

</head>
<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__wobble" src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
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
                        <a href="admin.php" class="nav-link active">
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
                        <a href="kasir.php" class="nav-link">
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
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-concierge-bell"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Layanan Aktif</span>
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
            </div>
            
            <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bookingModalLabel">Tambah Booking Baru</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="bookingForm">
                                <div class="form-group">
                                    <label for="modalNama">Nama Pelanggan:</label>
                                    <select class="form-control" id="modalNama" name="nama" required>
                                        <option value="">Pilih Pelanggan</option>
                                        <?php foreach ($daftarNama as $user): ?>
                                            <option value="<?= htmlspecialchars($user['nama']) ?>"><?= htmlspecialchars($user['nama']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="modalService">Layanan:</label>
                                    <select class="form-control" id="modalService" name="service_ids[]" multiple required>
                                        <option></option>
                                        <?php foreach ($daftarLayananUntukDropdown as $layanan): ?>
                                            <option value="<?= htmlspecialchars($layanan['id']) ?>"><?= htmlspecialchars($layanan['nama']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="modalTanggal">Tanggal:</label>
                                    <input type="date" class="form-control" id="modalTanggal" name="tanggal" required>
                                </div>
                                <div class="form-group">
                                    <label for="modalWaktu">Waktu:</label>
                                    <input type="time" class="form-control" id="modalWaktu" name="waktu" required>
                                </div>
                                <input type="hidden" id="bookingId" name="booking_id">
                                <input type="hidden" id="formAction" name="action" value="add">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary" id="saveBookingBtn">Simpan Booking</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="detailBookingModal" tabindex="-1" role="dialog" aria-labelledby="detailBookingModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detailBookingModalLabel">Detail Booking</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Nama Pelanggan:</strong> <span id="detailNama"></span></p>
                            <p><strong>Layanan:</strong> <span id="detailService"></span></p>
                            <p><strong>Tanggal:</strong> <span id="detailTanggal"></span></p>
                            <p><strong>Waktu:</strong> <span id="detailWaktu"></span></p>
                            <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-danger" id="deleteBookingFromDetailBtn">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <aside class="control-sidebar control-sidebar-dark">
    </aside>
    <footer class="main-footer">
        <strong>Copyright &copy; 2024 <a href="#">GoWash</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>
</div>

<script src="AdminLTE-3.1.0/plugins/jquery/jquery.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="AdminLTE-3.1.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="AdminLTE-3.1.0/dist/js/adminlte.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var bookingModal = $('#bookingModal');
        var detailBookingModal = $('#detailBookingModal');
        var currentEventId = null; // Variable untuk menyimpan ID event yang sedang dibuka

        // Inisialisasi Select2 pada dropdown layanan
        $('#modalService').select2({
            placeholder: "Pilih Layanan...",
            allowClear: true,
            theme: 'bootstrap4'
        });
        $('#modalNama').select2({
            placeholder: "Pilih Pelanggan...",
            allowClear: true,
            theme: 'bootstrap4'
        });

        // Pastikan Select2 dan form di-reset saat modal ditutup
        $('#bookingModal').on('hidden.bs.modal', function () {
            $('#modalService').val(null).trigger('change');
            $('#modalNama').val(null).trigger('change');
            $('#bookingForm')[0].reset();
        });

        // Inisialisasi FullCalendar
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            navLinks: true,
            editable: true,
            // Hapus opsi dayMaxEvents agar semua event ditampilkan
            events: <?php echo json_encode($events); ?>,
            dateClick: function(info) {
                $('#bookingModalLabel').text('Tambah Booking Baru');
                $('#formAction').val('add');
                $('#modalTanggal').val(info.dateStr);
                bookingModal.modal('show');
            },
            eventClick: function(info) {
                var bookingId = info.event.id;
                currentEventId = bookingId; // Simpan ID event
                $.ajax({
                    url: 'admin.php',
                    method: 'POST',
                    data: {
                        action: 'getBookingDetails',
                        id: bookingId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#detailNama').text(response.data.nama_user);
                            $('#detailService').text(response.data.nama_layanan_gabungan);
                            $('#detailTanggal').text(response.data.tanggal);
                            $('#detailWaktu').text(response.data.waktu);
                            $('#detailStatus').text(response.data.status);
                            detailBookingModal.modal('show');
                        } else {
                             Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Gagal mengambil detail booking: ' + response.message,
                                confirmButtonColor: '#343a40'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Error: ' + xhr.responseText,
                            confirmButtonColor: '#343a40'
                        });
                    }
                });
            },
            eventDrop: function(info) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Konfirmasi Perubahan!',
                    text: `Apakah Anda yakin ingin mengubah jadwal booking ini ke ${info.event.startStr}?`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Ubah Jadwal',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    customClass: {
                        popup: 'swal2-dark-mode'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Jadwal booking berhasil diubah.',
                            showConfirmButton: false,
                            timer: 1500,
                            customClass: {
                                popup: 'swal2-dark-mode'
                            }
                        });
                    } else {
                        info.revert();
                    }
                });
            }
        });

        calendar.render();

        // Logika saat tombol "Simpan Booking" diklik di modal
        $('#saveBookingBtn').on('click', function() {
            if ($('#bookingForm')[0].checkValidity()) {
                var formData = $('#bookingForm').serialize();
                $.ajax({
                    url: 'admin.php',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'sukses') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                confirmButtonColor: '#343a40'
                            });
                            bookingModal.modal('hide');
                            calendar.refetchEvents();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Gagal menyimpan: ' + response.error,
                                confirmButtonColor: '#343a40'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Error: ' + xhr.responseText,
                            confirmButtonColor: '#343a40'
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Mohon lengkapi semua field yang diperlukan.',
                    confirmButtonColor: '#343a40'
                });
            }
        });
        
        // Logika saat tombol "Hapus dari Kalender" diklik di modal detail
        $('#deleteBookingFromDetailBtn').on('click', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Konfirmasi Hapus!',
                html: 'Apakah Anda yakin ingin menghapus booking ini',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                customClass: {
                    popup: 'swal2-dark-mode'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (currentEventId !== null) {
                        let event = calendar.getEventById(currentEventId);
                        if (event) {
                            event.remove();
                            detailBookingModal.modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Booking berhasil dihapus.',
                                showConfirmButton: false,
                                timer: 1500,
                                customClass: {
                                    popup: 'swal2-dark-mode'
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Event tidak ditemukan di kalender.',
                                confirmButtonColor: '#343a40'
                            });
                        }
                        currentEventId = null; // Reset ID
                    }
                }
            });
        });

    });
</script>
</body>
</html>
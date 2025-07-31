<?php
session_start();
include "conn.php"; // Pastikan file conn.php ada dan koneksi berhasil

// Cek session login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// --- Logika untuk MENANGANI PENYIMPANAN BOOKING dari form modal (via AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'], $_POST['id_service'], $_POST['tanggal'], $_POST['waktu'])) {
    header('Content-Type: application/json');

    $nama       = $conn->real_escape_string($_POST['nama']);
    $id_service = intval($_POST['id_service']); // ← langsung ID, bukan nama layanan
    $tanggal    = $conn->real_escape_string($_POST['tanggal']);
    $waktu      = $conn->real_escape_string($_POST['waktu']);

    $pelanggan_id = NULL;

    // Ambil pelanggan_id berdasarkan nama (username)
    $stmt_get_pelanggan_id = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_get_pelanggan_id->bind_param("s", $nama);
    $stmt_get_pelanggan_id->execute();
    $result_pelanggan = $stmt_get_pelanggan_id->get_result();
    if ($result_pelanggan->num_rows > 0) {
        $pelanggan_data = $result_pelanggan->fetch_assoc();
        $pelanggan_id = $pelanggan_data['id'];
    }
    $stmt_get_pelanggan_id->close();

    // ✅ Validasi apakah ID layanan benar-benar ada
    $stmt_check_service = $conn->prepare("SELECT id FROM services WHERE id = ?");
    $stmt_check_service->bind_param("i", $id_service);
    $stmt_check_service->execute();
    $result_service = $stmt_check_service->get_result();

    if ($result_service->num_rows === 0) {
        echo json_encode(['status' => 'gagal', 'error' => 'ID layanan tidak valid atau tidak ditemukan.']);
        exit;
    }


    $stmt_check_service->close();

    // Simpan booking
    $sql_insert = "INSERT INTO booking (pelanggan_id, nama, id_service, tanggal, waktu, status) VALUES (?, ?, ?, ?, ?, 'Menunggu')";

    $stmt = $conn->prepare($sql_insert);

    if ($stmt === false) {
        echo json_encode(['status' => 'gagal', 'error' => 'Error preparing statement: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("isiss", $pelanggan_id, $nama, $id_service, $tanggal, $waktu);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'sukses', 'message' => 'Booking berhasil disimpan!']);
    } else {
        echo json_encode(['status' => 'gagal', 'error' => 'Gagal menyimpan booking: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}


// --- Logika untuk MENANGANI PENGHAPUSAN BOOKING dari modal (via AJAX) ---
// CATATAN: Logika ini TETAP ADA untuk kasus di mana Anda mungkin ingin menghapus dari database
// melalui endpoint ini di masa mendatang atau dari halaman lain.
// Namun, tombol "Hapus" di kalender di JavaScript TIDAK LAGI memanggil endpoint ini.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'deleteBooking') {
    header('Content-Type: application/json');

    $id = intval($_POST['id'] ?? 0);

    if ($id > 0) {
        // Optional: Decrement loyalty card if a 'Selesai' booking is deleted
        $stmt_get_booking_info = $conn->prepare("SELECT status, pelanggan_id FROM booking WHERE id = ?");
        $stmt_get_booking_info->bind_param("i", $id);
        $stmt_get_booking_info->execute();
        $result_booking_info = $stmt_get_booking_info->get_result();
        $booking_info = $result_booking_info->fetch_assoc();
        $stmt_get_booking_info->close();

        if ($booking_info && ($booking_info['status'] == 'Selesai' || $booking_info['status'] == 1) && $booking_info['pelanggan_id'] !== NULL) {
            $pelanggan_id_to_decrement = $booking_info['pelanggan_id'];
            $points_per_wash = 10; // Points to decrement
            $stmt_decrement_loyalty = $conn->prepare("UPDATE loyalty_card SET total_cuci = GREATEST(0, total_cuci - 1), poin = GREATEST(0, poin - ?) WHERE pelanggan_id = ?");
            $stmt_decrement_loyalty->bind_param("ii", $points_per_wash, $pelanggan_id_to_decrement);
            $stmt_decrement_loyalty->execute();
            $stmt_decrement_loyalty->close();
        }

        // Delete the booking from the 'booking' table
        $stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Booking berhasil dihapus.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus booking: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'ID booking tidak valid.']);
    }
    $conn->close(); // Close connection after AJAX response
    exit; // Stop script execution after handling AJAX request
}



// --- LOGIKA PENGAMBILAN DATA UNTUK DASHBOARD ---

// Ambil jumlah total booking
$query = "SELECT COUNT(*) AS total_booking FROM booking";
$result = $conn->query($query);
if ($result === false) {
    echo "Error dalam query total booking: " . $conn->error;
    exit();
}
$data = $result->fetch_assoc();
$totalBooking = $data['total_booking'];

// Ambil jumlah total user (role='user')
$userQuery = "SELECT COUNT(*) AS total_user FROM users WHERE role='user'";
$userResult = $conn->query($userQuery);
if ($userResult === false) {
    echo "Error dalam query total user: " . $conn->error;
    exit();
}
$userData = $userResult->fetch_assoc();
$totalUser = $userData['total_user'];

// Ambil jumlah layanan dari tabel layanan
$layananQuery = "SELECT COUNT(*) AS total_layanan FROM layanan";
$layananResult = $conn->query($layananQuery);
if ($layananResult === false) {
    echo "Error dalam query layanan: " . $conn->error;
    exit();
}
$layananData = $layananResult->fetch_assoc();
$totalLayanan = $layananData['total_layanan'];

// Menampilkan jumlah booking hari ini (tanggal = CURDATE() di SQL)
$todayQuery = "SELECT COUNT(*) AS booking_hari_ini FROM booking WHERE tanggal = CURDATE()";
$todayResult = $conn->query($todayQuery);
if ($todayResult === false) {
    echo "Error dalam query booking hari ini: " . $conn->error;
    exit();
}
$todayData = $todayResult->fetch_assoc();
$bookingHariIni = $todayData['booking_hari_ini'];

// TABEL KALENDER: Ambil daftar nama user (pelanggan) dari tabel mencuci
$namaQuery = "SELECT DISTINCT username AS nama FROM users ORDER BY username ASC"; // Menggunakan username sebagai nama
$namaResult = $conn->query($namaQuery);
if ($namaResult === false) {
    echo "Error dalam query daftar nama: " . $conn->error;
    exit();
}
$daftarNama = [];
while ($row = $namaResult->fetch_assoc()) {
    $daftarNama[] = $row['nama'];
}

// TABEL KALENDER: Ambil daftar nama layanan dari tabel layanan
$layananListQuery = "SELECT nama as name FROM layanan ORDER BY nama ASC"; // Beri nama berbeda agar tidak konflik dengan $layananQuery sebelumnya
$layananListResult = $conn->query($layananListQuery);
if ($layananListResult === false) {
    echo "Error dalam query daftar layanan: " . $conn->error;
    exit();
}
$daftarLayanan = [];
while ($row = $layananListResult->fetch_assoc()) {
    $daftarLayanan[] = $row['name'];
}


// Ambil semua event booking untuk kalender
$events = [];
$sql_events = "SELECT id, layanan, waktu, tanggal FROM booking";
$result_events = $conn->query($sql_events);
if ($result_events) {
    while ($row = $result_events->fetch_assoc()) {
        $events[] = $row;
    }
} else {
    error_log("Error fetching calendar events: " . $conn->error);
}

$conn->close(); // Close the database connection after all operations

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Dashboard</title>

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

        /* Warna layanan custom */
        .fc-event-cuci-eksterior { background-color: #214c4fff; border-color: #ffffff; color: #ffffff; }
        .fc-event-cuci-interior { background-color: #092022ff; border-color: #ffffff; color: #ffffff; }
        .fc-event-detailing { background-color: #bbfdffff; border-color: #ffffff; color: #343a40; }
        .fc-event-cuci-mobil { background-color: #538f94ff; border-color: #ffffff; color: #ffffff; }
        .fc-event-salon-mobil-kaca { background-color: #fdfeffff; border-color: #ffffff; color: #343a40; }
        .fc-event-perbaiki-mesin { background-color: #696969ff; border-color: #ffffff; color: #ffffff; }
        .fc-event-default { background-color: #cdcdcdff; border-color: #ffffff; color: #343a40; }

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
                </div><div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bookingModalLabel">Detail Booking</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Content will be loaded here by JS -->
                        </div>
                        <div class="modal-footer">
                            <!-- The delete button will be dynamically added/removed by JS based on context -->
                            <button type="button" class="btn btn-danger" id="hapusEventBtn">Hapus</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

        </section>
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
<?php
// Ambil data layanan dari database
$daftarLayanan = [];
$sqlLayanan = "SELECT id, nama FROM layanan";
$resultLayanan = $conn->query($sqlLayanan);
if ($resultLayanan->num_rows > 0) {
    while ($row = $resultLayanan->fetch_assoc()) {
        $daftarLayanan[] = $row;
    }
}

// Ambil username dari session
$username = $_SESSION['username'] ?? 'Guest';
?>

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    // Kirim data PHP ke JS
    const daftarLayanan = <?= json_encode($daftarLayanan) ?>;
    const username = <?= json_encode($username) ?>;

    // Pastikan variabel ini tersedia di JavaScript
    const daftarNama = <?= json_encode($daftarNama) ?>;
    const daftarLayanan = <?= json_encode($daftarLayanan) ?>; 

    let calendar; // agar bisa diakses global
    let selectedEvent; // simpan event yang diklik

    // Fungsi untuk mengelola event yang dihapus secara visual (disimpan di localStorage)
    function getHiddenEventIds() {
        return JSON.parse(localStorage.getItem('hiddenCalendarEvents') || '[]');
    }

    function addHiddenEventId(id) {
        const hiddenIds = getHiddenEventIds();
        if (!hiddenIds.includes(id)) {
            hiddenIds.push(id);
            localStorage.setItem('hiddenCalendarEvents', JSON.stringify(hiddenIds));
        }
    }


    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, { // Assign to global 'calendar' variable
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },

            // Klik tanggal → tampilkan form booking
            dateClick: function(info) {
                const tanggal = info.dateStr;

                const formHtml = `
                    <form id="formBooking">
                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" name="nama" class="form-control" value="${username}" required>
                        </div>
                        <div class="form-group">
                            <label>Layanan</label>
                            <select name="id_service" class="form-control" required>
                                <option value="">-- Pilih Layanan --</option>
                                ${daftarLayanan.map(l => `<option value="${l.id}">${l.name}</option>`).join('')}
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

                $('#bookingModalLabel').text('Tambah Booking Baru');

                $('#bookingModalLabel').text('Tambah Booking Baru'); // Update modal title
                $('#hapusEventBtn').hide(); // Hide delete button for new booking form

                $('#bookingModal').modal('show');

                $(document).off('submit', '#formBooking').on('submit', '#formBooking', function(e) {
                    e.preventDefault();
                    const data = $(this).serialize();


                    $.post('admin.php', data, function(response) {
                        if (response.status === 'sukses') {
                            alert(response.message);
                            $('#bookingModal').modal('hide');
                            calendar.refetchEvents();

                    $.post('admin.php', data, function (response) {
                        if (response.status === 'sukses') {
                            Swal.fire('Berhasil!', response.message, 'success').then(() => {
                                $('#bookingModal').modal('hide');
                                location.reload(); // Reload page to update calendar and stats
                            });

                        } else {
                            Swal.fire('Gagal!', "Gagal: " + response.error, 'error');
                        }
                    }, 'json').fail(function(xhr, status, error) {
                        Swal.fire('Error!', 'Terjadi kesalahan saat menyimpan booking: ' + error, 'error');
                    });
                });
            },

            events: [
                <?php

                $sql = "SELECT b.id, b.nama, b.id_service, s.name as nama_layanan, b.waktu, b.tanggal, b.status
                        FROM booking b
                        JOIN services s ON b.id_service = s.id";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $layanan = strtolower(trim($row['nama_layanan']));
                        $classMap = [
                            'detailing' => 'fc-event-detailing',
                            'cuci eksterior' => 'fc-event-cuci-eksterior',
                            'cuci interior' => 'fc-event-cuci-interior',
                            'cuci mobil' => 'fc-event-cuci-mobil',
                            'perbaiki mesin' => 'fc-event-perbaiki-mesin',
                            'salon mobil kaca' => 'fc-event-salon-mobil-kaca'
                        ];
                        $layananClass = $classMap[$layanan] ?? 'fc-event-default';
                        $start = $row['tanggal'] . 'T' . date('H:i:s', strtotime($row['waktu']));
                        echo "{ 
                            id: '{$row['id']}',
                            title: '" . htmlspecialchars($row['nama']) . " - " . htmlspecialchars($row['nama_layanan']) . "', 
                            start: '" . $start . "', 
                            className: '" . $layananClass . "' 
                        },";

                foreach ($events as $row) {
                    // Determine class based on service name (case-insensitive)
                    $serviceLower = strtolower($row['layanan']);
                    $class = 'fc-event-default'; // Default class

                    if (stripos($serviceLower, 'cuci eksterior') !== false) {
                        $class = 'fc-event-cuci-eksterior';
                    } elseif (stripos($serviceLower, 'cuci interior') !== false) {
                        $class = 'fc-event-cuci-interior';
                    } elseif (stripos($serviceLower, 'detailing') !== false) {
                        $class = 'fc-event-detailing';
                    } elseif (stripos($serviceLower, 'cuci mobil') !== false) { // General car wash, put after specific ones
                        $class = 'fc-event-cuci-mobil';
                    } elseif (stripos($serviceLower, 'salon mobil kaca') !== false) {
                        $class = 'fc-event-salon-mobil-kaca';
                    } elseif (stripos($serviceLower, 'perbaiki mesin') !== false) {
                        $class = 'fc-event-perbaiki-mesin';

                    }
                    
                    $start = $row['tanggal'] . 'T' . date('H:i:s', strtotime($row['waktu']));
                    echo "{ id: '{$row['id']}', title: '{$row['layanan']}', start: '{$start}', className: '{$class}' },";
                }
                ?>

            ],

            // Klik event untuk lihat detail
            eventClick: function(info) {
                $('#bookingModal .modal-body').html(
                    '<strong>Nama:</strong> ' + info.event.title.split(' - ')[0] +
                    '<br><strong>Layanan:</strong> ' + info.event.title.split(' - ')[1] +
                    '<br><strong>Tanggal:</strong> ' + info.event.start.toLocaleDateString() +
                    '<br><strong>Waktu:</strong> ' + info.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                );
                $('#bookingModalLabel').text('Detail Booking');

            ].filter(event => !getHiddenEventIds().includes(event.id)), // Filter events based on localStorage

            eventClick: function (info) {
                selectedEvent = info.event; // Store the clicked event globally

                $('#bookingModal .modal-body').html(`
                    <strong>Layanan:</strong> ${info.event.title}<br>
                    <strong>Waktu:</strong> ${info.event.start.toLocaleString()}
                `);
                $('#bookingModalLabel').text('Detail Booking'); // Update modal title
                $('#hapusEventBtn').show(); // Show delete button for existing event

                $('#bookingModal').modal('show');
            }
        });

        calendar.render();

        // Handle delete button click in the modal
        $('#hapusEventBtn').on('click', function () {
            if (!selectedEvent) return; // Ensure an event is selected

            Swal.fire({
                title: 'Yakin ingin menghapus booking ini dari kalender?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus dari kalender!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tambahkan ID event ke localStorage agar tidak muncul lagi setelah refresh
                    addHiddenEventId(selectedEvent.id);
                    // Hapus event dari tampilan kalender saja
                    selectedEvent.remove();
                    
                    Swal.fire(
                        'Dihapus!',
                        'Booking berhasil dihapus dari kalender.',
                        'success'
                    ).then(() => {
                        $('#bookingModal').modal('hide');
                        // Tidak perlu reload halaman karena perubahan sudah persisten di localStorage
                    });
                }
            });
        });
    });
</script>


</body>
</html>

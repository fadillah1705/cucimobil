<?php

session_start();
include "conn.php"; // Pastikan file conn.php ada dan koneksi berhasil

// Cek session login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// --- Logika untuk MENANGANI PENYIMPANAN BOOKING dari form modal (via AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'], $_POST['layanan'], $_POST['tanggal'], $_POST['waktu'])) {
    header('Content-Type: application/json'); // Penting untuk respons AJAX

    $nama = $conn->real_escape_string($_POST['nama']);
    $layanan = $conn->real_escape_string($_POST['layanan']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    $waktu = $conn->real_escape_string($_POST['waktu']);
    
    $pelanggan_id = NULL; // Default NULL

    // Opsional: Coba cari pelanggan_id berdasarkan username di tabel mencuci
    $sql_get_pelanggan_id = "SELECT id FROM mencuci WHERE username = ?";
    $stmt_get_pelanggan_id = $conn->prepare($sql_get_pelanggan_id);
    if ($stmt_get_pelanggan_id) {
        $stmt_get_pelanggan_id->bind_param("s", $nama); // Asumsi nama di form booking cocok dengan username di mencuci
        $stmt_get_pelanggan_id->execute();
        $result_pelanggan = $stmt_get_pelanggan_id->get_result();
        if ($result_pelanggan->num_rows > 0) {
            $pelanggan_data = $result_pelanggan->fetch_assoc();
            $pelanggan_id = $pelanggan_data['id'];
        }
        $stmt_get_pelanggan_id->close();
    }


    $sql_insert = "INSERT INTO booking (pelanggan_id, nama, layanan, tanggal, waktu) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);

    if ($stmt === false) {
        echo json_encode(['status' => 'gagal', 'error' => 'Error preparing statement: ' . $conn->error]);
        exit;
    }

    // Perhatikan urutan dan tipe data: "issss" -> i untuk int (pelanggan_id), s untuk string lainnya
    $stmt->bind_param("issss", $pelanggan_id, $nama, $layanan, $tanggal, $waktu); 

    if ($stmt->execute()) {
        echo json_encode(['status' => 'sukses', 'message' => 'Booking berhasil disimpan!']);
    } else {
        echo json_encode(['status' => 'gagal', 'error' => 'Gagal menyimpan booking: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit; // Penting untuk menghentikan eksekusi PHP setelah mengirim respons JSON
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
$userQuery = "SELECT COUNT(*) AS total_user FROM mencuci WHERE role='user'";
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
$namaQuery = "SELECT DISTINCT username AS nama FROM mencuci ORDER BY username ASC"; // Menggunakan username sebagai nama
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
$layananListQuery = "SELECT nama FROM layanan ORDER BY nama ASC"; // Beri nama berbeda agar tidak konflik dengan $layananQuery sebelumnya
$layananListResult = $conn->query($layananListQuery);
if ($layananListResult === false) {
    echo "Error dalam query daftar layanan: " . $conn->error;
    exit();
}
$daftarLayanan = [];
while ($row = $layananListResult->fetch_assoc()) {
    $daftarLayanan[] = $row['nama'];
}

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
        .fc-event-cuci-eksterior { background-color: #00ff88ff; border-color: #ffffffff; } /* Biru */
        .fc-event-cuci-interior { background-color: #f4ff2cff; border-color: #ffffffff; } /* Hijau */
        .fc-event-detailing { background-color: #d5beffff; border-color: #ffffffff; } /* Oranye */
        .fc-event-cuci-mobil { background-color: #ffffffff; border-color: #ffffffff; } /* Merah */
        .fc-event-salon-mobil-kaca { background-color: #ffb080ff; border-color: #ffffffff; } /* Ungu */
        .fc-event-perbaiki-mesin { background-color: #ffa6c2ff; border-color: #ffffffff; } /* Coklat */
        .fc-event-default { background-color: #ff7676ff; border-color: #ffffffff; } /* Abu-abu default jika tidak ada match */
        
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
                            </div>
                        <div class="modal-footer">
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
    // Pastikan variabel ini tersedia di JavaScript
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

            // Fitur klik tanggal untuk menambah booking
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
                $('#bookingModalLabel').text('Tambah Booking Baru'); // Update modal title
                $('#bookingModal').modal('show');

                // Submit form via AJAX
                $('#formBooking').on('submit', function(e) {
                    e.preventDefault();
                    const data = $(this).serialize();
                    $.post('admin.php', data, function(response) {
                        if (response.status === 'sukses') {
                            alert(response.message); // Tampilkan pesan sukses
                            $('#bookingModal').modal('hide');
                            calendar.refetchEvents(); // Refresh event di kalender
                        } else {
                            alert("Gagal: " + response.error);
                        }
                    }, 'json');
                });
            },

            // Event dari database
            events: [
                <?php
                $sql = "SELECT nama, layanan, waktu, tanggal FROM booking"; // Ambil nama juga
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $layanan = strtolower(trim($row['layanan']));
                        
                        // Map layanan ke class warna
                        $classMap = [
                            'cuci eksterior' => 'fc-event-cuci-eksterior',
                            'cuci interior' => 'fc-event-cuci-interior',
                            'detailing' => 'fc-event-detailing',
                            'cuci mobil' => 'fc-event-cuci-mobil',
                            'salon mobil kaca' => 'fc-event-salon-mobil-kaca',
                            'perbaiki mesin' => 'fc-event-perbaiki-mesin'
                        ];

                        // Gunakan class default jika tidak ada di map
                        $layananClass = isset($classMap[$layanan]) ? $classMap[$layanan] : 'fc-event-default';

                        $start = $row['tanggal'] . 'T' . date('H:i:s', strtotime($row['waktu']));
                        
                        // Menambahkan nama ke title event
                        echo "{ title: '" . htmlspecialchars($row['nama']) . " - " . htmlspecialchars($row['layanan']) . "', start: '" . $start . "', className: '" . $layananClass . "' },";
                    }
                }
                ?>
            ],

            // Klik event → buka modal detail
            eventClick: function(info) {
                $('#bookingModal .modal-body').html(
                    '<strong>Nama:</strong> ' + info.event.title.split(' - ')[0] + // Ambil nama dari title
                    '<br><strong>Layanan:</strong> ' + info.event.title.split(' - ')[1] + // Ambil layanan dari title
                    '<br><strong>Tanggal:</strong> ' + info.event.start.toLocaleDateString() +
                    '<br><strong>Waktu:</strong> ' + info.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                );
                $('#bookingModalLabel').text('Detail Booking'); // Update modal title
                $('#bookingModal').modal('show');
            }
        });

        calendar.render();
    });
</script>

</body>
</html>
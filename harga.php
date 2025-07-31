<?php
session_start();
// Koneksi database dan query untuk mendapatkan layanan
require_once 'conn.php';

// Pastikan $pdo sudah terdefinisi dari conn.php
// Jika conn.php Anda menggunakan mysqli, ubah bagian ini agar konsisten
if (!isset($pdo) || !$pdo instanceof PDO) {
    error_log("FATAL ERROR: \$pdo is not defined or not a PDO instance in harga.php. Ini menunjukkan masalah pada conn.php.");
    die("Kesalahan sistem: Koneksi database tidak tersedia. Mohon hubungi administrator.");
}

try {
    $stmt_layanan = $pdo->query("SELECT * FROM layanan WHERE is_active = 1 ORDER BY price");
    $layanan = $stmt_layanan->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching services: " . $e->getMessage());
    die("Terjadi kesalahan saat mengambil data layanan: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harga Layanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff; /* Pure white background */
        }

        /* --- Navbar Styles --- */
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .navbar-nav .nav-link {
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #3a9fa7 !important;
            font-weight: 600;
        }
        .navbar-brand img {
            height: 40px;
        }

        /* --- Section Title Styles --- */
        .section-title {
            padding: 60px 0 30px;
            background: linear-gradient(135deg, #f8fcfd, #e0f2f7); /* Very subtle, light gradient */
            margin-bottom: 40px;
            border-bottom: 1px solid rgba(0,0,0,0.03); /* Lighter border */
        }
        .section-title h3 {
            font-size: 2.2rem; /* Slightly smaller title */
            font-weight: 700;
            color: #212529;
            text-shadow: none; /* Remove text shadow for cleaner look */
        }
        .section-title .badge {
            background-color: #3a9fa7;
            padding: 8px 20px; /* Smaller padding for badge */
            border-radius: 50px;
            font-size: 1rem; /* Slightly smaller font for badge */
            letter-spacing: 0.5px; /* Reduced letter spacing */
            box-shadow: 0 3px 8px rgba(58, 159, 167, 0.2); /* Softer shadow */
        }
        .section-title .lead {
            font-size: 1rem; /* Smaller lead text */
            color: #666; /* Slightly darker gray for better readability on white */
        }

        /* --- Service Card Styles --- */
        .layan-card {
            border-radius: 12px; /* Slightly less rounded */
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); /* Softer, less prominent shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0; /* Subtle border for definition */
            background-color: #ffffff;
            overflow: hidden;
        }
        .layan-card:hover {
            transform: translateY(-5px); /* Less pronounced lift */
            box-shadow: 0 8px 20px rgba(0,0,0,0.12); /* Softer hover shadow */
        }
        .layan-card .card-body {
            padding: 25px; /* Slightly less padding */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            min-height: 280px; /* Adjusted min-height for smaller card */
        }
        .layan-card .card-title {
            font-size: 1.2rem; /* Smaller title font */
            font-weight: 600; /* Slightly less bold */
            color: #333;
            margin-bottom: 12px; /* Adjusted margin */
        }
        .layan-card img {
            width: 90px; /* Smaller image */
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f5f5f5; /* Lighter border around image */
            box-shadow: 0 1px 5px rgba(0,0,0,0.08); /* Softer image shadow */
            margin-bottom: 18px; /* Adjusted margin */
        }
        .layan-card p {
            color: #666; /* Slightly darker text for readability */
            font-size: 0.9rem; /* Smaller paragraph text */
            line-height: 1.5;
        }
        .layan-card .text-muted {
            font-size: 0.8rem; /* Smaller muted text */
            margin-top: 8px;
            margin-bottom: 3px;
        }
        .price-tag {
            font-size: 1.5rem; /* Smaller price font */
            font-weight: 700;
            color: #0d6efd;
            margin-top: 12px;
            margin-bottom: 15px; /* Adjusted margin */
            background: #eef7ff; /* Lighter background for price */
            padding: 6px 12px; /* Smaller padding */
            border-radius: 6px;
            display: inline-block;
        }
        .btn-booking {
            background-color: #3a9fa7;
            color: white;
            font-weight: 500; /* Slightly less bold */
            border: none;
            padding: 8px 25px; /* Smaller padding for button */
            border-radius: 40px; /* Slightly less pill-shaped */
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 3px 8px rgba(58, 159, 167, 0.25); /* Softer shadow */
            margin-top: auto;
            font-size: 0.95rem; /* Smaller font for button */
        }
        .btn-booking:hover {
            background-color: #2e8b94;
            transform: translateY(-1px); /* More subtle lift */
            color: white;
        }

        /* --- Modal Styles (Kept largely similar as they were good) --- */
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .modal-header {
            background-color: #3a9fa7;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 20px;
        }
        .modal-header .btn-close {
            filter: invert(1);
        }
        .modal-title {
            font-weight: 600;
        }
        .modal-body .form-group label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        .modal-body .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #dee2e6;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .modal-body .form-control:focus {
            border-color: #3a9fa7;
            box-shadow: 0 0 0 0.25rem rgba(58, 159, 167, 0.25);
        }
        .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 15px 20px;
        }
        .modal-footer .btn-primary {
            background-color: #3a9fa7;
            border-color: #3a9fa7;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 20px;
        }
        .modal-footer .btn-primary:hover {
            background-color: #2e8b94;
            border-color: #2e8b94;
        }

        /* --- Footer Styles (Kept good ones) --- */
        .footer {
            background-color: #3a9fa7;
            color: white;
            padding: 35px 0;
            margin-top: 60px;
        }
        .footer h6 {
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        .footer h6::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: #fff;
            border-radius: 2px;
        }
        .footer .list-unstyled li {
            margin-bottom: 10px;
        }
        .footer .list-unstyled a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer .list-unstyled a:hover {
            color: #e0e0e0;
        }
        .footer p {
            font-size: 0.95rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top shadow-sm">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="layanan.php">Layanan Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="harga.php">Harga</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="testimoni.php">Testimoni</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kontak.php">Kontak</a>
                    </li>

                    <?php if (isset($_SESSION['username']) && $_SESSION['role'] !== 'guest'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i>
                                Haii, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="profil.php">Lihat Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="section-title text-center">
        <div class="container">
            <h3 class="mb-4">
                <span class="badge text-white bg-custom-badge">Harga Layanan</span>
            </h3>
            <p class="lead text-muted">Temukan layanan pencucian mobil terbaik kami dengan harga transparan dan kompetitif.</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center g-4">
                <?php if (!empty($layanan)): ?>
                    <?php foreach ($layanan as $layan): ?>
                        <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                            <div class="card layan-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($layan['nama']) ?></h5>
                                    
                                    <img src="<?= htmlspecialchars($layan['image']) ?>" 
                                        alt="<?= htmlspecialchars($layan['nama']) ?>" 
                                        class="img-fluid mb-3">

                                    <p class="mb-1"><?= nl2br(htmlspecialchars($layan['description'])) ?></p>
                                    <p class="text-muted">Menggunakan: <strong><?= htmlspecialchars($layan['product_used']) ?></strong></p>

                                    <p class="text-muted mt-3">Harga Mulai Dari</p>
                                    <h5 class="price-tag">Rp<?= number_format($layan['price'], 0, ',', '.') ?></h5>

                                    <button type="button" class="btn btn-booking mt-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#bookingModal"
                                            data-layanan-nama="<?= htmlspecialchars($layan['nama']) ?>">
                                        Pesan Sekarang
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info" role="alert">
                            Tidak ada layanan yang tersedia saat ini. Silakan cek kembali nanti!
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">Konfirmasi Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="wa.php" method="POST"> 
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="modalLayananNama">Layanan yang Dipilih:</label>
                            <input type="text" class="form-control" id="modalLayananNama" name="layanan_display" readonly>
                            <input type="hidden" id="modalLayananHidden" name="layanan"> 
                        </div>
                        <div class="form-group mb-3">
                            <label for="tanggalBooking">Tanggal Booking:</label>
                            <input type="date" class="form-control" id="tanggalBooking" name="tanggal" required
                                   min="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label for="waktuBooking">Waktu Booking:</label>
                            <input type="time" class="form-control" id="waktuBooking" name="waktu" required
                                   min="07:00" max="20:00"> 
                        </div>
                        <?php if (!isset($_SESSION['username']) || $_SESSION['role'] === 'guest'): ?>
                            <div class="alert alert-warning mt-3" role="alert">
                                Anda harus <a href="login.php" class="alert-link">login</a> terlebih dahulu untuk melakukan booking.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"
                            <?php if (!isset($_SESSION['username']) || $_SESSION['role'] === 'guest') echo 'disabled'; ?>>
                            Konfirmasi Booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer"> 
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-4">
                    <h6 class="fw-bold">Layanan Kami</h6>
                    <ul class="list-unstyled">
                        <li>Cuci Mobil Interior</li>
                        <li>Cuci Mobil Eksterior</li>
                        <li>Cuci Mobil Detailing</li>
                        <li>Cuci Mobil</li>
                        <li>Salon Mobil Kaca</li>
                        <li>Perbaiki Mesin</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Informasi</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-decoration-none">Beranda</a></li>
                        <li><a href="layanan.php" class="text-decoration-none">Layanan Kami</a></li>
                        <li><a href="harga.php" class="text-decoration-none">Harga</a></li>
                        <li><a href="testimoni.php" class="text-decoration-none">Testimoni</a></li>
                        <li><a href="kontak.php" class="text-decoration-none">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Wilayah Operasional & Jam Kerja</h6>
                    <p class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Ende, Nusa Tenggara Timur</p>
                    <p class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Jogjakarta, DIY</p>
                    <p class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Surabaya, Jawa Timur</p>
                    <p class="mt-2"><i class="bi bi-clock-fill me-2"></i>Senin – Minggu (07.00–20.00 WIB)</p>
                    <p class="mt-3 opacity-75">© Go Wash 2020. All rights reserved</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var bookingModal = document.getElementById('bookingModal');
            bookingModal.addEventListener('show.bs.modal', function (event) {
                // Button that triggered the modal
                var button = event.relatedTarget;
                // Extract info from data-bs-* attributes
                var layananNama = button.getAttribute('data-layanan-nama');

                // Update the modal's content.
                var modalLayananNamaInput = bookingModal.querySelector('#modalLayananNama');
                var modalLayananHiddenInput = bookingModal.querySelector('#modalLayananHidden');
                
                modalLayananNamaInput.value = layananNama;
                modalLayananHiddenInput.value = layananNama; // Mengatur nilai input hidden untuk dikirim

                // Set default date to today
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = today.getFullYear();
                var todayFormatted = yyyy + '-' + mm + '-' + dd;
                bookingModal.querySelector('#tanggalBooking').value = todayFormatted;

                // Set default time to current hour + 1 hour, or 07:00 if before 07:00, or 20:00 if after 19:00
                var currentHour = today.getHours();
                var defaultHour = currentHour + 1;
                if (defaultHour < 7) {
                    defaultHour = 7;
                } else if (defaultHour > 20) {
                    defaultHour = 20; // Or adjust to the end of operational hours
                }
                var defaultTime = String(defaultHour).padStart(2, '0') + ':00';
                bookingModal.querySelector('#waktuBooking').value = defaultTime;
            });
        });
    </script>
</body>
</html>
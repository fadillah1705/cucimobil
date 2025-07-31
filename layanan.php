<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Kami</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff; /* Pure white background */
        }

        /* --- Navbar Styles (Consistent) --- */
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

        /* --- Section Title Styles (Consistent with harga.php) --- */
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
            text-shadow: none;
        }
        .section-title .badge {
            background-color: #3a9fa7;
            padding: 8px 20px; /* Smaller padding for badge */
            border-radius: 50px;
            font-size: 1rem; /* Slightly smaller font for badge */
            letter-spacing: 0.5px;
            box-shadow: 0 3px 8px rgba(58, 159, 167, 0.2);
        }
        .section-title .lead {
            font-size: 1rem;
            color: #666;
        }

        /* --- Service Card Styles (Consistent with harga.php) --- */
        .service-card {
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
            background-color: #ffffff;
            overflow: hidden;
            display: flex; /* Menggunakan flexbox untuk card */
            flex-direction: column; /* Konten diatur secara kolom */
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .service-card .card-img-top {
            width: 100%; /* Gambar mengisi lebar card */
            height: 180px; /* Tinggi gambar fixed untuk konsistensi */
            object-fit: cover; /* Memastikan gambar terisi penuh tanpa distorsi */
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            transition: transform 0.3s ease;
        }
        .service-card:hover .card-img-top {
            transform: scale(1.03); /* Efek zoom in pada gambar saat hover */
        }
        .service-card .card-body {
            padding: 20px; /* Sedikit lebih kecil dari harga.php karena gambar sudah besar */
            text-align: center; /* Pusat teks dalam card */
            flex-grow: 1; /* Memastikan card body mengisi sisa ruang */
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Untuk mendorong elemen ke bawah jika diperlukan */
        }
        .service-card .card-title {
            font-size: 1.25rem; /* Ukuran judul disesuaikan */
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .service-card .card-text {
            font-size: 0.9rem; /* Ukuran teks deskripsi */
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px; /* Jarak bawah deskripsi */
        }

        /* --- Tips Section Styles --- */
        .tips-section {
            padding-top: 60px;
            padding-bottom: 60px;
            background-color: #f8fcfd; /* Latar belakang konsisten */
            border-top: 1px solid rgba(0,0,0,0.03);
        }
        .tips-section h5 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 40px;
        }
        .tip-box {
            background-color: #ffffff; /* Kotak tips putih bersih */
            border: 1px solid #e0e0e0; /* Border tipis */
            border-radius: 10px !important; /* Rounded corners */
            box-shadow: 0 3px 10px rgba(0,0,0,0.05) !important; /* Soft shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 20px;
        }
        .tip-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.08) !important;
        }
        .tip-box .fw-bold {
            color: #3a9fa7; /* Warna judul tips sesuai brand */
            font-size: 1.05rem;
            margin-bottom: 8px !important;
        }
        .tip-box p {
            font-size: 0.9rem;
            color: #555;
            line-height: 1.6;
            margin-bottom: 0;
        }


        /* --- Footer Styles (Consistent) --- */
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
            color: white;
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
        .footer .list-unstyled a, .footer .list-unstyled li {
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
            color: white;
        }
        .footer .bi {
            margin-right: 8px;
            font-size: 1rem;
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
                        <a class="nav-link active" href="layanan.php">Layanan Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="harga.php">Harga</a>
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
                <span class="badge text-white bg-custom-badge">Layanan Kami</span>
            </h3>
            <p class="lead text-muted">Kami hadir dengan berbagai pilihan layanan pencucian dan perawatan mobil profesional untuk Anda.</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center g-4">
                <div class="col-sm-6 col-md-6 col-lg-4">
                    <div class="card service-card h-100">
                        <img src="Mobiklin_Exterior_Detailing.webp" class="card-img-top" alt="Cuci Eksterior">
                        <div class="card-body">
                            <h5 class="card-title">Cuci Eksterior</h5>
                            <p class="card-text">Membersihkan bagian luar mobil dengan semprotan tekanan tinggi dan sabun khusus berkualitas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-4">
                    <div class="card service-card h-100">
                        <img src="Mobiklin_Interior_Detailing.webp" class="card-img-top" alt="Cuci Interior">
                        <div class="card-body">
                            <h5 class="card-title">Cuci Interior</h5>
                            <p class="card-text">Vakum, pembersihan dashboard, jok, dan karpet mobil untuk kenyamanan maksimal.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-4">
                    <div class="card service-card h-100">
                        <img src="Mobiklin_Tire_Rims_Detailing.webp" class="card-img-top" alt="Detailing">
                        <div class="card-body">
                            <h5 class="card-title">Detailing</h5>
                            <p class="card-text">Paket premium untuk merawat dan menjaga kilau cat serta interior mobil Anda.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-4">
                    <div class="card service-card h-100">
                        <img src="Mobiklin_Cuci_Mobil.webp" class="card-img-top" alt="Cuci Mobil">
                        <div class="card-body">
                            <h5 class="card-title">Cuci Mobil</h5>
                            <p class="card-text">Pembersihan menyeluruh bagian luar dan dalam mobil menggunakan alat bertekanan tinggi untuk hasil maksimal.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-4">
                    <div class="card service-card h-100">
                        <img src="Mobiklin_Window_Detailing.webp" class="card-img-top" alt="Salon Mobil Kaca">
                        <div class="card-body">
                            <h5 class="card-title">Salon Mobil Kaca</h5>
                            <p class="card-text">Kaca depan dan samping mobil dibersihkan menyeluruh agar bebas buram dan kembali bening seperti baru.</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-4">
                    <div class="card service-card h-100">
                        <img src="Mobklin_Engine_Detailing.webp" class="card-img-top" alt="Perbaiki Mesin">
                        <div class="card-body">
                            <h5 class="card-title">Perbaiki Mesin</h5>
                            <p class="card-text">Periksa dan perbaiki mesin mobil Anda agar kembali bekerja dengan maksimal dan bebas gangguan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="tips-section">
        <div class="container text-center">
            <h5 class="fw-bold mb-5">Gimana sih, caranya menjaga mobil agar tetap bersih dan terawat?</h5>

            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5 mb-4">
                    <div class="tip-box p-4 h-100">
                        <p class="fw-bold mb-2">1. Cuci Mobil Secara Rutin</p>
                        <p class="mb-0">Mencuci mobil secara rutin minimal seminggu sekali menjaga tampilan tetap bersih dan bebas dari kotoran yang bisa merusak cat. Debu, lumpur, dan kotoran lain jika dibiarkan bisa menyebabkan karat dan mempercepat kerusakan pada permukaan mobil.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-5 mb-4">
                    <div class="tip-box p-4 h-100">
                        <p class="fw-bold mb-2">2. Gunakan Produk dan Alat yang Tepat</p>
                        <p class="mb-0">Hindari menggunakan sabun rumah tangga. Gunakan sampo mobil yang ramah cat dan lap microfiber agar tidak menggores. Air yang bersih dan alat yang tepat akan membantu hasil lebih maksimal tanpa merusak permukaan mobil.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-5 mb-4">
                    <div class="tip-box p-4 h-100">
                        <p class="fw-bold mb-2">3. Jaga Kebersihan Interior</p>
                        <p class="mb-0">Interior yang bersih meningkatkan kenyamanan berkendara. Bersihkan dashboard, kursi, dan karpet secara berkala. Gunakan vacuum cleaner dan pembersih interior khusus agar ruangan tetap segar dan bersih.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-5 mb-4">
                    <div class="tip-box p-4 h-100">
                        <p class="fw-bold mb-2">4. Hindari Parkir Langsung di Bawah Matahari</p>
                        <p class="mb-0">Sinar UV bisa merusak cat dan interior mobil. Jika harus parkir lama, gunakan pelindung seperti cover mobil atau cari tempat teduh. Hal ini juga membantu menjaga suhu kabin tetap stabil.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
</body>
</html>
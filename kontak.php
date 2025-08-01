<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - GoWash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        /* --- Contact Content Styles --- */
        .contact-content-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 40px;
        }

        /* Info Kontak Card */
        .info-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 25px; /* Slightly increased padding for content */
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .info-card h5 {
            font-weight: 600;
            color: #333;
            margin-bottom: 25px; /* Increased margin for heading */
            text-align: center;
            font-size: 1.4rem;
        }
        .info-card p {
            font-size: 0.95rem; /* Slightly larger text for readability */
            color: #555;
            margin-bottom: 15px; /* Consistent spacing between items */
            display: flex; /* Use flex for precise alignment */
            align-items: flex-start; /* Align icon and text to top */
            line-height: 1.4; /* Improve line spacing */
        }
        .info-card p:last-child {
            margin-bottom: 0; /* Remove bottom margin for the last paragraph */
        }
        .info-card p .fas, .info-card p .fab {
            color: #3a9fa7;
            margin-right: 12px;
            font-size: 1.1rem;
            width: 25px; /* Fixed width for consistent icon alignment */
            text-align: center;
            flex-shrink: 0; /* Prevent icon from shrinking */
            padding-top: 2px; /* Small adjustment for icon vertical alignment */
        }
        .info-card p strong {
            display: inline-block; /* Make strong a block-like element for width control */
            width: 90px; /* Fixed width for labels to align colons */
            flex-shrink: 0; /* Prevent from shrinking */
            text-align: left; /* Align label text to left */
            margin-right: 5px; /* Space between label and content */
        }
        .info-card p span {
            flex-grow: 1; /* Allow content to take remaining space */
        }
        .info-card a {
            color: #3a9fa7;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .info-card a:hover {
            color: #2e828a;
        }

        /* Contact Form Card */
        .form-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 25px;
            height: 100%;
        }
        .form-card h5 {
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.4rem;
        }
        .form-card .form-label {
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .form-card .form-control {
            border-radius: 0.4rem;
            border-color: #ced4da;
            font-size: 0.9rem;
            padding: 0.6rem 0.75rem;
        }
        .form-card .form-control:focus {
            border-color: #3a9fa7;
            box-shadow: 0 0 0 0.2rem rgba(58, 159, 167, 0.25);
        }
        .form-card .mb-3, .form-card .mb-4 {
            margin-bottom: 1.2rem !important;
        }
        .form-card .btn-primary {
            background-color: #3a9fa7;
            border-color: #3a9fa7;
            color: white;
            border-radius: 0.4rem;
            padding: 0.7rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .form-card .btn-primary:hover {
            background-color: #2e828a;
            border-color: #2e828a;
        }
        #form-status {
            font-size: 0.85rem;
            text-align: center;
            margin-top: 1rem;
        }

        /* Map Section */
        .map-section {
            padding-bottom: 40px;
        }
        .map-section h5 {
            font-weight: 700;
            color: #212529;
            margin-bottom: 20px;
            font-size: 1.6rem;
            text-align: center;
        }
        .map-section iframe {
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            height: 350px;
            width: 80%; /* Ensure it's responsive but limited */
            max-width: 800px; /* Add max-width for larger screens */
        }

        /* --- Footer Styles (EXACTLY MATCHING other pages) --- */
        .footer {
            background-color: #3a9fa7;
            color: white;
            padding: 30px 0;
            margin-top: auto;
            font-size: 0.9rem;
        }
        .footer h6 {
            font-weight: 600;
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 8px;
            color: white;
            font-size: 1rem;
        }
        .footer h6::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 2px;
            background-color: #fff;
            border-radius: 2px;
        }
        .footer .list-unstyled li {
            margin-bottom: 8px;
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
            line-height: 1.5;
            color: white;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        .footer .bi {
            margin-right: 6px;
            font-size: 0.9rem;
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .contact-content-container {
                padding: 20px;
            }
            .info-card, .form-card {
                margin-bottom: 20px;
            }
            .map-section iframe {
                height: 300px;
                width: 95%; /* Adjust width for smaller screens */
            }
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
                        <a class="nav-link" href="harga.php">Harga</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="testimoni.php">Testimoni</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="kontak.php">Kontak</a>
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

    <div id="main-content-section">
        <section class="section-title text-center">
            <div class="container">
                <h3 class="mb-4">
                    <span class="badge text-white" style="background-color: #3a9fa7;">Kontak Kami</span>
                </h3>
                <p class="lead text-muted">Hubungi kami untuk pertanyaan, reservasi, atau feedback.</p>
            </div>
        </section>

        <section id="contact-content" class="container py-4">
            <div class="contact-content-container">
                <div class="row g-4">
                    <div class="col-md-5 d-flex">
                        <div class="card info-card w-100">
                            <div class="card-body">
                                <h5><strong>Info Kontak</strong></h5>
                                <p><i class="fas fa-map-marker-alt"></i> Alamat :</strong> Gowok, Caturtunggal, Kec. Depok, Kabupaten Sleman, DIY</span></p><br>
                                <p><i class="fas fa-phone"></i> No Telepon :</strong> +628122676007</span></p><br>
                                <p><i class="fas fa-envelope"></i> Email :</strong> Gowash@gmail.com</span></p><br>
                                <p><i class="fas fa-globe"></i> Website :</strong> cucimobil.my.id</span></p><br>
                                <p class="mb-0"><i class="fab fa-instagram"></i>Instagram : </strong> 
                                    <a href="https://www.instagram.com/jogjaautowash/" target="_blank"> @jogjaautowash</a></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7 d-flex">
                        <div class="card form-card w-100">
                            <div class="card-body">
                                <h5><strong>Kirim Pesan Kepada Kami</strong></h5>
                                <form id="contact-form" action="https://api.web3forms.com/submit" method="POST">
                                    <input type="hidden" name="access_key" value="e9b273c3-47d2-4348-8cc0-d5802962b164">
                                    <input type="hidden" name="redirect" value="https://yourdomain.com/contact-success.html">
                                    <input type="hidden" name="subject" value="Pesan dari Form Kontak GoWash">

                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama</label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan Nama Anda" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan Email Anda" required>
                                    </div>

                                    <div class="mb-4">
                                        <label for="message" class="form-label">Pesan</label>
                                        <textarea class="form-control" id="message" name="message" rows="4" placeholder="Tulis pesan Anda di sini" required></textarea>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                                    </div>
                                </form>
                                <div id="form-status" class="text-success mt-3 fw-semibold"></div>
                            </div>
                        </div>
                    </div>
                </div> 
            </div> 
        </section>

        <section id="map-section" class="container map-section py-4">
            <h5 class="fw-bold text-center">Lokasi Kami</h5>
            <div class="d-flex justify-content-center mt-3">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.0599929148084!2d110.40883027485759!3d-7.783464277231517!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a59e57fbfe721%3A0x76b8788821eefda3!2sAutowash%20Yogyakarta%20Salon%26Cuci%20Mobil!5e0!3m2!1sid!2sid!4v1754014338186!5m2!1sid!2sid"
                    width="80%" height="350" style="border:0; border-radius: 12px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </section>
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
                        <li><a href="index.php" class="text-white text-decoration-none">Beranda</a></li>
                        <li><a href="layanan.php" class="text-white text-decoration-none">Layanan Kami</a></li>
                        <li><a href="harga.php" class="text-white text-decoration-none">Harga</a></li>
                        <li><a href="testimoni.php" class="text-white text-decoration-none">Testimoni</a></li>
                        <li><a href="kontak.php" class="text-white text-decoration-none">Kontak</a></li>
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
        // Optional: Script to handle form submission success/failure if not using Web3Forms redirect
        const contactForm = document.getElementById('contact-form');
        const formStatus = document.getElementById('form-status');

        contactForm.addEventListener('submit', async function(event) {
            // Prevent default redirect if Web3Forms redirect is not used
            // event.preventDefault(); 
            
            // If you want to handle success message on the same page,
            // you'd need to remove the "redirect" hidden input in the form
            // and use Fetch API to send the data.
            // Example:
            /*
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: this.method,
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (result.success) {
                formStatus.textContent = 'Pesan Anda berhasil terkirim! Terima kasih.';
                formStatus.className = 'text-success mt-3 fw-semibold';
                contactForm.reset();
            } else {
                formStatus.textContent = 'Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.';
                formStatus.className = 'text-danger mt-3 fw-semibold';
            }
            */
        });
    </script>
</body>
</html>
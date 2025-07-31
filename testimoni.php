<?php
session_start();
require_once 'conn.php';

$success_message = '';
$error_message = '';

// Check for messages stored in session from a previous redirect
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear it after displaying
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear it after displaying
}

// Handle testimonial submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_testimonial'])) {
    // Pastikan hanya user biasa yang bisa submit testimoni, bukan admin
    if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && (!isset($_SESSION['role']) || $_SESSION['role'] === 'user')) {
        $userId = $_SESSION['user_id'];
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
            try {
                $stmt = $conn->prepare("INSERT INTO testimonials (user_id, rating, comment) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $userId, $rating, $comment);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Terima kasih! Testimoni Anda berhasil ditambahkan.";
                } else {
                    $_SESSION['error_message'] = "Gagal menambahkan testimoni. Silakan coba lagi. Error: " . $stmt->error;
                }
                $stmt->close();
            } catch (mysqli_sql_exception $e) {
                $_SESSION['error_message'] = "Database error saat menyimpan testimoni: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = "Mohon lengkapi rating dan komentar Anda dengan benar.";
        }
    } else {
        // Pesan error jika admin atau guest mencoba submit
        $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengirim testimoni.";
    }

    // Always redirect after POST to prevent resubmission
    header("Location: testimoni.php");
    exit(); // Important to stop script execution after redirect
}

// Fetch existing testimonials from the database using a JOIN
$testimonials = [];
try {
    // JOIN with the users table to get the username
    $sql = "SELECT t.comment, t.rating, u.username
            FROM testimonials t
            JOIN users u ON t.user_id = u.id
            ORDER BY t.created_at DESC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $testimonials[] = $row;
        }
        $result->free();
    }
} catch (mysqli_sql_exception $e) {
    $error_message = "Gagal memuat testimoni: " . $e->getMessage();
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimoni - GoWash</title>
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

        /* --- Testimonial Card Styles (Harmonized with Service Card) --- */
        .testimonial-card {
            border: 1px solid #e0e0e0; /* Consistent border */
            border-radius: 12px; /* Consistent border-radius */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); /* Consistent shadow */
            padding: 20px;
            background-color: #fff;
            text-align: left;
            margin-bottom: 25px; /* Consistent margin-bottom */
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* Consistent transition */
        }
        .testimonial-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12); /* Consistent hover shadow */
            transform: translateY(-5px); /* Consistent hover transform */
        }
        .testimonial-card .card-body { /* Using card-body for inner structure */
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .testimonial-card .username {
            font-weight: 600;
            color: #3a9fa7;
            margin-bottom: 8px;
            font-size: 1.05rem;
        }
        .testimonial-card .rating {
            margin-bottom: 10px;
            font-size: 1.1rem;
            color: #ffc107;
        }
        .testimonial-card .comment {
            font-style: italic;
            color: #555;
            line-height: 1.6;
            font-size: 0.9rem;
        }
        .testimonial-form-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); /* Consistent shadow */
            padding: 30px;
            background-color: #fff;
            margin-top: 30px;
        }
        .form-select.rounded-pill,
        .form-control.rounded {
            border-radius: 0.5rem;
        }
        .btn-submit-testimonial {
            background-color: #3a9fa7;
            border-color: #3a9fa7;
            color: white;
            border-radius: 0.5rem;
            padding: 10px 25px;
            font-weight: 500;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .btn-submit-testimonial:hover {
            background-color: #2e828a;
            border-color: #2e828a;
        }
        /* --- Footer Styles (Consistent) --- */
        .footer {
            background-color: #3a9fa7;
            color: white;
            padding: 35px 0;
            margin-top: auto;
        }
        .footer h6 {
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
            color: white; /* Ensure text is white */
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
        .footer .list-unstyled a, .footer .list-unstyled li { /* Added li to selector */
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
            color: white; /* Ensure text is white */
            opacity: 0.9;
        }
        .footer .bi { /* Added styling for icons in footer */
            margin-right: 8px;
            font-size: 1rem;
        }
        .alert-info a.alert-link {
            font-weight: bold;
            color: #0c5460;
            text-decoration: underline;
        }
        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-dismissible .btn-close {
            position: absolute;
            top: 0;
            right: 0;
            padding: 0.75rem 1rem;
            color: inherit;
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M1.293 1.293a1 1 0 0 1 1.414 0L8 6.586l5.293-5.293a1 1 0 1 1 1.414 1.414L9.414 8l5.293 5.293a1 1 0 0 1-1.414 1.414L8 9.414l-5.293 5.293a1 1 0 0 1-1.414-1.414L6.586 8 1.293 2.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            border: 0;
            border-radius: 0.25rem;
            opacity: 0.5;
        }
        .alert-dismissible .btn-close:hover {
            opacity: 0.75;
        }
        /* Custom badge color for consistency */
        .bg-custom-badge {
            background-color: #3a9fa7 !important; /* Matches main theme color */
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
                        <a class="nav-link active" href="testimoni.php">Testimoni</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kontak.php">Kontak</a>
                    </li>
                    <?php if (isset($_SESSION['username']) && ($_SESSION['role'] !== 'guest' || !isset($_SESSION['role']))): ?>
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
                    <span class="badge text-white bg-custom-badge">Testimoni Pelanggan</span>
                </h3>
                <p class="lead text-muted">Testimoni dan ulasan pelanggan GoWash terhadap layanan kami</p>
            </div>
        </section>

        <section id="testimonials-content" class="container py-5">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['username']) && ($_SESSION['role'] === 'user' || !isset($_SESSION['role']))): ?>
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-8">
                        <div class="testimonial-form-card">
                            <h4 class="mb-4 text-center fw-bold text-dark">Berikan Testimoni Anda</h4>
                            <form action="testimoni.php" method="POST">
                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rating (1-5 Bintang)</label>
                                    <select class="form-select rounded" id="rating" name="rating" required>
                                        <option value="">Pilih Rating</option>
                                        <option value="5">5 Bintang - Sangat Baik</option>
                                        <option value="4">4 Bintang - Baik</option>
                                        <option value="3">3 Bintang - Cukup</option>
                                        <option value="2">2 Bintang - Buruk</option>
                                        <option value="1">1 Bintang - Sangat Buruk</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="comment" class="form-label">Komentar Anda</label>
                                    <textarea class="form-control rounded" id="comment" name="comment" rows="4" placeholder="Tulis komentar Anda di sini..." required></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="submit_testimonial" class="btn btn-submit-testimonial">Kirim Testimoni</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center" role="alert">
                    Anda harus <a href="login.php" class="alert-link">login</a> sebagai pengguna untuk bisa memberikan testimoni.
                </div>
            <?php endif; ?>

            <h4 class="text-center fw-bold mb-4">Apa Kata Mereka?</h4>
            <div class="row justify-content-center">
                <?php if (count($testimonials) > 0): ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="col-md-6 col-lg-4 d-flex">
                            <div class="card testimonial-card w-100">
                                <div class="card-body">
                                    <p class="username mb-0"><?php echo htmlspecialchars($testimonial['username']); ?></p>
                                    <div class="rating">
                                        <?php
                                        for ($i = 0; $i < 5; $i++) {
                                            if ($i < $testimonial['rating']) {
                                                echo '<i class="bi bi-star-fill"></i>';
                                            } else {
                                                echo '<i class="bi bi-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <p class="comment"><?php echo htmlspecialchars($testimonial['comment']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Belum ada testimoni saat ini. Jadilah yang pertama memberikan!</p>
                    </div>
                <?php endif; ?>
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
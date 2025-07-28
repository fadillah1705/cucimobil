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
    if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        $userId = $_SESSION['user_id'];
        $username = $_SESSION['username'];
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
            try {
                $stmt = $conn->prepare("INSERT INTO testimonials (user_id, username, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $userId, $username, $rating, $comment);
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
        $_SESSION['error_message'] = "Anda harus login untuk mengirim testimoni.";
    }

    // Always redirect after POST to prevent resubmission
    header("Location: testimoni.php");
    exit(); // Important to stop script execution after redirect
}

// Fetch existing testimonials from the database
$testimonials = [];
try {
    $result = $conn->query("SELECT username, rating, comment FROM testimonials ORDER BY created_at DESC");
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimoni - Go Wash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* CSS for sticky footer */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }
        #testimonial-section {
            flex: 1; /* Makes the content area grow */
        }
        .footer {
            color: #fff; /* White text for contrast */
            padding: 30px 0;
            margin-top: auto; /* Pushes the footer to the very bottom */
        }
        .footer ul {
            padding-left: 0;
        }
        .footer ul li {
            margin-bottom: 5px;
        }
        .footer a {
            color: #fff;
            text-decoration: none;
        }
        .footer a:hover {
            color: #dee2e6;
        }
        /* Custom styling for testimonial card */
        .testimonial-card {
            border-radius: 10px;
            overflow: hidden; /* Ensures rounded corners apply to content */
        }
        .testimonial-form-card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
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

    <section id="testimonial-section" class="my-5 pt-5">
        <div class="container text-center">
            <h3>
                <span class="badge fw-bold text-white p-3 mb-3" style="background-color: #3a9fa7;">Testimoni Pelanggan</span>
            </h3>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Display Existing Testimonials (Now at the top) -->
            <div class="row justify-content-center g-4 mb-5">
                <?php if (empty($testimonials)): ?>
                    <div class="col-12">
                        <p class="text-muted">Belum ada testimoni. Jadilah yang pertama memberikan testimoni!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100 border-0 testimonial-card">
                                <div class="card-body text-center">
                                    <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($testimonial['username']); ?></h6>
                                    <h6 class="fw-bold mb-0">
                                        <?php
                                        // Display stars based on rating
                                        for ($i = 0; $i < $testimonial['rating']; $i++) {
                                            echo '⭐';
                                        }
                                        ?>
                                    </h6>
                                    <p class="mt-3">"<?php echo htmlspecialchars($testimonial['comment']); ?>"</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Testimonial Submission Form (Now at the bottom, smaller) -->
            <?php if (isset($_SESSION['username']) && ($_SESSION['role'] !== 'guest' || !isset($_SESSION['role']))): ?>
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6"> <!-- Smaller column for the form -->
                        <div class="card shadow-sm mb-5 p-4 testimonial-form-card">
                            <h5 class="card-title fw-bold mb-3">Tulis Testimoni Anda</h5>
                            <form action="testimoni.php" method="POST">
                                <div class="mb-3 text-start">
                                    <label for="rating" class="form-label">Rating Bintang:</label>
                                    <select class="form-select rounded-pill" id="rating" name="rating" required>
                                        <option value="">Pilih Rating</option>
                                        <option value="5">⭐⭐⭐⭐⭐ (Sangat Baik)</option>
                                        <option value="4">⭐⭐⭐⭐ (Baik)</option>
                                        <option value="3">⭐⭐⭐ (Cukup)</option>
                                        <option value="2">⭐⭐ (Kurang)</option>
                                        <option value="1">⭐ (Buruk)</option>
                                    </select>
                                </div>
                                <div class="mb-3 text-start">
                                    <label for="comment" class="form-label">Komentar Anda:</label>
                                    <textarea class="form-control rounded" id="comment" name="comment" rows="4" placeholder="Bagaimana pengalaman Anda dengan layanan kami?" required></textarea>
                                </div>
                                <button type="submit" name="submit_testimonial" class="btn text-white rounded-pill" style="background-color: #3a9fa7;">Kirim Testimoni</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    Silakan <a href="login.php">login</a> untuk dapat memberikan testimoni Anda!
                </div>
            <?php endif; ?>

        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-4">
                    <h6 class="fw-bold">Layanan</h6>
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
                    <h6 class="fw-bold text-white">Informasi</h6>
                    <ul class="list-unstyled text-white">
                        <li><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="layanan.php" class="text-white text-decoration-none">Layanan Kami</a></li>
                        <li><a href="harga.php" class="text-white text-decoration-none">Harga</a></li>
                        <li><a href="testimoni.php" class="text-white text-decoration-none">Testimoni</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-white">Wilayah Operasional & Jam Kerja</h6>
                    <p class="mb-0">Ende, Nusa Tenggara Timur</p>
                    <p class="mb-0">Jogjakarta, DIY</p>
                    <p class="mb-0">Surabaya, Jawa Barat</p>
                    <p>Senin – Minggu (07.00–20.00 WIB)</p>
                    <p class="mt-3">© Go Wash 2020. All rights reserved</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>

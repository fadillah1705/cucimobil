<?php
session_start();
// Koneksi database dan query untuk mendapatkan layanan
require_once 'conn.php';
$services = $conn->query("SELECT * FROM services WHERE is_active = 1 ORDER BY price");
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
    <style>
        .service-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
            border: none;
            background-color: #f8f9fa;
        }
        .service-card:hover {
            transform: translateY(-5px);
        }
        .price-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .btn-booking {
            background-color: #3a9fa7;
            color: white;
            font-weight: bold;
            border: none;
        }
        .bg-custom-badge {
            background-color: #3a9fa7;
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

                    <!-- profile -->
                    <?php if (isset($_SESSION['username'])): ?>
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

    <section class="my-5 pt-5">
        <div class="container text-center">
            <h3>
                <span class="badge fw-bold text-white p-3 mb-3 bg-custom-badge">Harga Layanan</span>
            </h3>
            
            <div class="row justify-content-center g-4">
                <?php
                // Koneksi database dan query untuk mendapatkan layanan
                require_once 'conn.php';
                $services = $conn->query("SELECT * FROM services WHERE is_active = 1 ORDER BY price");
                
                foreach ($services as $service): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card service-card h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($service['name']) ?></h5>
                            <p class="mb-1"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                            <p class="text-muted">Menggunakan <?= htmlspecialchars($service['product_used']) ?></p>
                            
                            <p class="text-muted mt-3">Harga sekali cuci</p>
                            <h5 class="fw-bold">Rp<?= number_format($service['price'], 0, ',', '.') ?></h5>
                            
                            <form action="wa.php" method="POST">
                                <input type="hidden" name="layanan" value="<?= htmlspecialchars($service['name']) ?>">
                                <button type="submit" class="btn btn-booking mt-2">Booking</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
          </div>
        </div>
      </div>
    </div>
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
    <li><a href="#" class="text-white text-decoration-none">Home</a></li>
    <li><a href="#layanan" class="text-white text-decoration-none">Layanan Kami</a></li>
    <li><a href="#harga" class="text-white text-decoration-none">Harga</a></li>
    <li><a href="#testimoni" class="text-white text-decoration-none">Testimoni</a></li>
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
    </div>
  </footer>
</body>
</html>
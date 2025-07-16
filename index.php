<?php
session_start();


?> 
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GoWash - Cuci Mobil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

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
       
 <!-- profile -->
      <?php if (isset($_SESSION['username'])): ?>
  <!-- ✅ User sudah login -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-person-circle"></i> Profil
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
      <li><a class="dropdown-item" href="profil.php">Lihat Profil</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="logout.php">Logout</a></li>
    </ul>
  </li>
<?php else: ?>
  <!-- ❌ User belum login -->
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

  <section class="hero d-flex align-items-center" style="padding-top: 100px; " id="home">
    <div class="container ">
      <div class="row align-items-center">

        <div class="col-md-6">
         
          <h1 class="display-5 fw-bold">GoWash<br>Panggilan Profesional</h1>
          <p class="lead mt-3">Tunggu di rumah saja, jasa cuci dan salon mobil kami, siap membersihkan mobil anda kapanpun dibutuhkan. Ayo mulai berlangganan sekarang!</p>
            <a href="https://wa.me/6281353638585?text=Halo%20GoWash%2C%20saya%20tertarik%20dengan%20paket%20layanan%20cuci%20mobil " 
     target="_blank" 
    class="btn btn-success btn-sm fw-bold">
    Pesan Via WhatsApp
  </a>
 <div class="text-center mt-4">
  <a href="https://google.com" class="text-dark me-3" target="_blank">
    <i class="bi bi-google fs-2"></i>
  </a>
  <a href="https://tiktok.com" class="text-dark me-3" target="_blank">
    <i class="bi bi-tiktok fs-2"></i>
  </a>
  <a href="https://www.instagram.com/jogjaautowash/" class="text-dark" target="_blank">
    <i class="bi bi-instagram fs-2"></i>
  </a>
</div>
        </div>
        <div class="col-md-6 text-center">
          <img src="Biru Hitam Moderen Rental Mobil Postingan Facebook.png" class="img-fluid" alt="Cuci Mobil">
         
        </div>
       
      </div>
    </div>
  </section>
  <section class="py-5">
  <div class="container text-center">
    <h5 class="fw-bold mb-5">Kenapa Pilih GoWash?</h5>
    <div class="row justify-content-center g-4">

      <div class="col-6 col-md-2">
        <img src="security.webp" alt="Terpercaya" width="50" class="mb-3">
        <p class="mb-0 fw-medium">Terpercaya</p>
      </div>

      <div class="col-6 col-md-2">
        <img src="quality.webp" alt="Berkualitas" width="50" class="mb-3">
        <p class="mb-0 fw-medium">Berkualitas</p>
      </div>

      <div class="col-6 col-md-2">
        <img src="fast.webp" alt="Cepat" width="50" class="mb-3">
        <p class="mb-0 fw-medium">Cepat</p>
      </div>

      <div class="col-6 col-md-2">
        <img src="flexible.webp" alt="Fleksibel" width="50" class="mb-3">
        <p class="mb-0 fw-medium">Fleksibel</p>
      </div>

      <div class="col-6 col-md-2">
        <img src="professional.webp" alt="Professional" width="50" class="mb-3">
        <p class="mb-0 fw-medium">Professional</p>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

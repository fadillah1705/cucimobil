<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
         <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
     <i class="bi bi-person-circle"></i> Profil </a>
     <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
    <li><a class="dropdown-item" href="profil.php">Lihat Profil</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
  </ul>
</li>
      </ul>
    </div>
  </div>
</nav>
<section id="testimoni" class="my-5 pt-5">
  <div class="container text-center">
   <h3>
            <span class="badge fw-bold text-white p-3 mb-3" style="background-color: #3a9fa7;">Testemoni Pelangggan</span>
          </h3>
    <div class="row justify-content-center g-4">
  
      <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body text-center">
            <h6 class="fw-bold mb-0">Mahathir Mohammad</h6>
           <h6 class="fw-bold mb-0">⭐⭐⭐⭐⭐</h6>
            <p class="mt-3">"Layanan sangat memuaskan! Mobil saya seperti baru setiap selesai dicuci di sini."</p>
          </div>
        </div>
      </div>


      <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body text-center">
            <h6 class="fw-bold mb-0">Zahra Wahar</h6>
      <h6 class="fw-bold mb-0">⭐⭐⭐⭐</h6>
            <p class="mt-3">"Petugasnya ramah dan detail banget dalam membersihkan mobil. Recommended!"</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body text-center">
            <h6 class="fw-bold mb-0">Ummi Aminah</h6>
          <h6 class="fw-bold mb-0">⭐⭐⭐⭐</h6>
            <p class="mt-3">"Harga terjangkau, hasil bersih maksimal. Cocok buat kamu yang sibuk kerja!"</p>
          </div>
        </div>
      </div>
      
     <div class="col-md-4">
  <div class="card shadow-sm h-100 border-0">
    <div class="card-body text-center">
      <h6 class="fw-bold mb-0">Fahri Mandar</h6>
    <h6 class="fw-bold mb-0">⭐⭐⭐⭐⭐</h6>
      <p class="mt-3">"Pelayanannya cepat dan hasil cucinya bersih banget! Sekarang saya langganan tiap minggu."</p>
    </div>
  </div>
</div>

<div class="col-md-4">
  <div class="card shadow-sm h-100 border-0">
    <div class="card-body text-center">
      <h6 class="fw-bold mb-0">Fitriah Sukri</h6>
     <h6 class="fw-bold mb-0">⭐⭐⭐⭐⭐</h6>
      <p class="mt-3">"Awalnya coba-coba, tapi sekarang jadi langganan karena kualitas dan kebersihannya memuaskan."</p>
    </div>
  </div>
</div>

<div class="col-md-4">
  <div class="card shadow-sm h-100 border-0">
    <div class="card-body text-center">
      <h6 class="fw-bold mb-0">Nur Fadillah</h6>
         <h6 class="fw-bold mb-0">⭐⭐⭐⭐</h6>
      <p class="mt-3">"Petugasnya ramah dan mobil saya selalu kinclong tiap selesai dicuci. Mantap!"</p>
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
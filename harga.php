<?php
session_start();
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
          <a class="nav-link" href="testimoni.php">Testimoni</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="kontak.php">Kontak</a>
        </li>
         <!-- profile -->
          <!--  Cek apakah user sudah login -->
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

   <section class="my-5 pt-5">
  <div class="container text-center">
   <h3>
            <span class="badge fw-bold text-white p-3 mb-3" style="background-color: #3a9fa7;">Harga Layanan</span>
          </h3>
    <div class="row justify-content-center g-4 mb-4">
      <div class="col-md-4 col-lg-3">
        <div class="card bg-custom-blue shadow-sm rounded-4 h-100 border-0 bg-light">
          <div class="card-body">
            <h5 class="card-title fw-bold">Cuci Interior</h5>
            <p class="mb-1">Pembersihan dashboard, jok, dan karpet</p>
            <p class="mb-1">Menggunakan Meguiar’s Gold Class</p>
            <p class="text-muted mt-3">Harga sekali cuci</p>
            <h5 class="fw-bold">Rp30.000</h5>
           <form action="wa.php" method="POST">
  <input type="hidden" name="layanan" value="Cuci Interior">
  <button type="submit" class="btn btn-primary">Booking</button>
</form>

          </div>
        </div>
      </div>
<div class="col-md-4 col-lg-3">
  <div class="card bg-custom-blue shadow-sm rounded-4 h-100 border-0 bg-light">
    <div class="card-body">
      <h5 class="card-title fw-bold">Cuci Interior</h5>
      <p class="mb-1">Pembersihan dashboard, jok, dan karpet</p>
      <p class="mb-1">Menggunakan Meguiar’s Gold Class</p>
      <p class="text-muted mt-3">Harga sekali cuci</p>
      <h5 class="fw-bold">Rp30.000</h5>
    <form action="wa.php" method="POST">
  <input type="hidden" name="nama" value="Cuci Interior">
  <button type="submit" class="btn btn-primary">Booking</button>
</form>
    </div>
  </div>
</div>

      <div class="col-md-4 col-lg-3">
        <div class="card bg-custom-blue shadow-sm rounded-4 h-100 border-0 bg-light">
          <div class="card-body">
            <h5 class="card-title fw-bold">Cuci Mobil Eksterior</h5>
            <p class="mb-1">Pembersihan bagian luar kendaraan</p>
            <p class="mb-1">Menggunakan Meguiar’s Gold Class</p>
            <p class="text-muted mt-3">Harga sekali cuci</p>
            <h5 class="fw-bold">Rp45.000</h5>

           <form action="wa.php" method="POST">
  <input type="hidden" name="nama" value="Cuci Mobil Exterior">
  <button type="submit" class="btn btn-primary">Booking</button>
</form>

          

          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-3 ">
        <div class="card bg-custom-blue shadow-sm rounded-4 h-100 border-0 bg-light">
          <div class="card-body">
            <h5 class="card-title fw-bold">Detailing Mobil</h5>
            <p class="mb-1">Perawatan menyeluruh eksterior & interior</p>
            <p class="mb-1">Menggunakan Meguiar’s Gold Class</p>
            <p class="text-muted mt-3">Harga sekali cuci</p>
            <h5 class="fw-bold">Rp65.000</h5>

           <form action="wa.php" method="POST">
  <input type="hidden" name="nama" value="Detailing">
  <button type="submit" class="btn btn-primary">Booking</button>
</form>


          </div>
        </div>
      </div>
    </div>

   
    <div class="row justify-content-center g-4 ">
      <div class="col-md-4 col-lg-3">
        <div class="card bg-custom-blue shadow-sm rounded-4 h-100 border-0 bg-light">
          <div class="card-body">
            <h5 class="card-title fw-bold">Cuci Mobil Biasa</h5>
            <p class="mb-1">Pembersihan luar mobil dengan sabun khusus</p>
            <p class="mb-1">Menggunakan Meguiar’s Hyper Wash</p>
            <p class="text-muted mt-3">Harga sekali cuci</p>
            <h5 class="fw-bold">Rp40.000</h5>

            <form action="wa.php" method="POST">
  <input type="hidden" name="nama" value="Cuci Biasa">
  <button type="submit" class="btn btn-primary">Booking</button>
</form>

          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-3">
        <div class="card bg-custom-blue shadow-sm rounded-4 h-100 border-0 bg-light">
          <div class="card-body">
            <h5 class="card-title fw-bold">Salon Mobil Kaca</h5>
            <p class="mb-1">Pembersihan dan pemolesan kaca mobil menyeluruh</p>
            <p class="mb-1">Menggunakan Meguiar’s Hyper Wash</p>
            <p class="text-muted mt-3">Harga sekali Cuci</p>
            <h5 class="fw-bold">Rp1.500.000</h5>

            <form action="wa.php" method="POST">
  <input type="hidden" name="nama" value="Salon Mobil Kaca">
  <button type="submit" class="btn btn-primary">Booking</button>
</form>

          </div>
        </div>
      </div>

      <div class="col-md-4 col-lg-3">
        <div class="card bg-custom-blue shadow-sm rounded-4 h-100 border-0 bg-light">
          <div class="card-body">
            <h5 class="card-title fw-bold">Perbaiki Mobil</h5>
            <p class="mb-1">Pemeriksaan dan servis mesin mobil</p>
            <p class="mb-1">Menggunakan alat & formula profesional</p>
            <p class="text-muted mt-3">Harga sekali layanan</p>
            <h5 class="fw-bold">Rp50.000</h5>

            <form action="wa.php" method="POST">
  <input type="hidden" name="nama" value="Perbaikan">
  <button type="submit" class="btn btn-primary">Booking</button>
</form>

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
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
<!-- Mengecek apakah sesi username sudah ada (user sudah login). -->
 <?php if (isset($_SESSION['username']) && $_SESSION['role'] !== 'guest'): ?>
 <!--  tampilkan dropdown Profil + Logout.-->
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
<!-- User belum login -->
<!-- tampilkan tombol login -->
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
    <section id="contact" class="my-5 pt-5">
  <div class="container text-center">

  <h3>
            <span class="badge fw-bold text-white p-3 mb-3" style="background-color: #3a9fa7;">Kontak Kami</span>
          </h3>
    </div>

    <div class="card shadow-lg p-4 mx-auto" style="border-radius: 15px; background-color: #ffffff; max-width: 900px;">
      <div class="row">


        <div class="col-md-5 d-flex flex-column align-items-center">
          <h5>
            <span>Info Kontak</span>
          </h5>
          <div class="card p-4 shadow-sm w-90">
            <div class="card-body">
              <p class="mb-3"><i class="fas fa-map-marker-alt"></i> <strong>Alamat :</strong> Gowok, Caturtunggal, Kec. Depok, Kabupaten Sleman, DIY</p><br>
              <p class="mb-3"><i class="fas fa-phone"></i> <strong>No Telepon :</strong> +628122676007</p><br>
              <p class="mb-3"><i class="fas fa-envelope"></i> <strong>Email :</strong> nfaadillahh74@gmail.com</p><br>
              <p class="mb-3"><i class="fas fa-globe"></i> <strong>Website :</strong> cucimobil.my.id</p><br>
              <p class="mb-0"><i class="fab fa-instagram"></i> <strong>Instagram :</strong> 
                <a href="https://www.instagram.com/jogjaautowash/" target="_blank">@autowash</a></p>
            </div>
          </div>
        </div>

        <div class="col-md-7">
          <div class="card p-3 shadow-sm">
            <div class="card-body">
              <form id="contact-form" action="https://api.web3forms.com/submit" method="POST">
                <input type="hidden" name="access_key" value="e9b273c3-47d2-4348-8cc0-d5802962b164">

                <div class="mb-3">
                  <label for="name" class="form-label">Nama</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan Nama Anda" required>
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan Email Anda" required>
                </div>

                <div class="mb-3">
                  <label for="message" class="form-label">Pesan</label>
                  <textarea class="form-control" id="message" name="message" rows="5" placeholder="Tulis pesan Anda di sini" required></textarea>
                </div>

                <div class="d-grid">
                  <button type="submit" class="btn btn-primary">Kirim</button>
                </div>
              </form>
              <div id="form-status" class="text-success mt-3 fw-semibold"></div>
            </div>
          </div>
        </div>

      </div> 
    </div> 

  
    <div class="text-center mt-5">
      <h5 class="fw-bold">Alamat</h5>
      <div class="d-flex justify-content-center mt-4">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.0599929148084!2d110.40883027485759!3d-7.783464277231522!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a59e57fbfe721%3A0x76b8788821eefda3!2sAutowash%20Yogyakarta%20Salon%26Cuci%20Mobil!5e0!3m2!1sid!2sus!4v1752221022961!5m2!1sid!2sus"
          width="80%" height="350" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
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
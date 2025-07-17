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
      <?php if (isset($_SESSION['username'])): ?>
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
    <section class="my-5 pt-5">
    <div class="container my-4 text-center">
 <h3>
            <span class="badge fw-bold text-white p-3 mb-3" style="background-color: #3a9fa7;">Layanan Kami</span>
          </h3>
    <div class="container">
     
      <div class="row">
        <div class="col-md-4 mb-3">
          <div class="card h-100">
             <img src="Mobiklin_Exterior_Detailing.webp" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Cuci Eksterior</h5>
              <p class="card-text">Membersihkan bagian luar mobil dengan semprotan tekanan tinggi dan sabun khusus.</p>
           <div class="text-center mt-4">
</div>

            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card h-100">
             <img src="Mobiklin_Interior_Detailing.webp" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Cuci Interior</h5>
              <p class="card-text">Vakum, pembersihan dashboard, jok, dan karpet mobil untuk kenyamanan maksimal.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card h-100">
             <img src="Mobiklin_Tire_Rims_Detailing.webp" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Detailing</h5>
              <p class="card-text">Paket premium untuk merawat dan menjaga kilau cat serta interior mobil Anda.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card h-100">
             <img src="Mobiklin_Cuci_Mobil.webp" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Cuci Mobil</h5>
              <p class="card-text">Pembersihan menyeluruh bagian luar mobil menggunakan alat bertekanan tinggi untuk hasil maksimal</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card h-100">
             <img src="Mobiklin_Window_Detailing.webp" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Salon Mobil Kaca</h5>
              <p class="card-text">Kaca depan dan samping mobil dibersihkan menyeluruh agar bebas buram dan kembali bening seperti baru.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card h-100">
             <img src="Mobklin_Engine_Detailing.webp" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Perbaiki Mesin</h5>
              <p class="card-text">Periksa dan perbaiki mesin mobil anda agar kembali bekerja dengan maksimal dan bebas gangguan</p>
            </div>
          </div>
        </div>
      </div>
    </div>
     </div>
  </section>
  <section class="my-5 pt-5">
  <div class="container my-4">
    <h5 class="text-center mb-4 fw-bold">Gimana sih, caranya menjaga mobil agar tetap bersih dan terawat?</h5>

    <div class="tip-box mb-4 p-4 rounded-4 shadow-sm bg-light">
      <p class="fw-bold mb-2">1. Cuci Mobil Secara Rutin</p>
      <p class="mb-0">Mencuci mobil secara rutin minimal seminggu sekali menjaga tampilan tetap bersih dan bebas dari
        kotoran yang bisa merusak cat. Debu, lumpur, dan kotoran lain jika dibiarkan bisa menyebabkan karat dan
        mempercepat kerusakan pada permukaan mobil.</p>
    </div>

    <div class="tip-box mb-4 p-4 rounded-4 shadow-sm bg-light">
      <p class="fw-bold mb-2">2. Gunakan Produk dan Alat yang Tepat</p>
      <p class="mb-0">Hindari menggunakan sabun rumah tangga. Gunakan sampo mobil yang ramah cat dan lap microfiber
        agar tidak menggores. Air yang bersih dan alat yang tepat akan membantu hasil lebih maksimal tanpa merusak
        permukaan mobil.</p>
    </div>

    <div class="tip-box mb-4 p-4 rounded-4 shadow-sm bg-light">
      <p class="fw-bold mb-2">3. Jaga Kebersihan Interior</p>
      <p class="mb-0">Interior yang bersih meningkatkan kenyamanan berkendara. Bersihkan dashboard, kursi, dan karpet
        secara berkala. Gunakan vacuum cleaner dan pembersih interior khusus agar ruangan tetap segar dan bersih.</p>
    </div>

    <div class="tip-box mb-4 p-4 rounded-4 shadow-sm bg-light">
      <p class="fw-bold mb-2">4. Hindari Parkir Langsung di Bawah Matahari</p>
      <p class="mb-0">Sinar UV bisa merusak cat dan interior mobil. Jika harus parkir lama, gunakan pelindung seperti
        cover mobil atau cari tempat teduh. Hal ini juga membantu menjaga suhu kabin tetap stabil.</p>
    </div>
  </div>
</section>

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
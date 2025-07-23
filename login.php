<?php
include "conn.php";
session_start();


// Membuat variabel bernama $message dan mengisinya dengan string kosong.
$message = "";
$swalScript = ""; // Untuk menyimpan SweetAlert Script

// Proses login apapun
// $_SERVER["REQUEST_METHOD"] adalah variabel bawaan PHP yang menyimpan jenis, GETdan POST
// Cek apakah form dikirim dengan metode POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // ✅ Login sebagai tamu
  // Mengecek apakah user menekan tombol "Login Sebagai Tamu"
  if (isset($_POST["login_guest"])) {
    // Menyimpan data ke dalam session bahwa user yang sedang mengakses sistem adalah seorang Tamu
    $_SESSION["username"] = "Tamu";
    // menyimpan informasi ke dalam session bahwa peran (role) user saat ini adalah guest (tamu).
    $_SESSION["role"] = "guest";
    // Menampilkan popup alert ke pengguna, lalu mengalihkan halaman ke index.php.
  $swalScript = "
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Masuk sebagai tamu berhasil!',
        confirmButtonText: 'OK'
      }).then(() => {
        window.location.href = 'index.php';
      });
    </script>";
   echo $swalScript;
  }

  // ✅ Login sebagai user/admin
  // Ambil nilai dari input form username (metode POST), atau isi dengan string kosong ('') jika tidak ada.
  $username = $_POST["username"] ?? '';
  $password = $_POST["password"] ?? '';

  // Membuat query SQL untuk mengambil semua data dari tabel mencuci berdasarkan username tertentu.
  $sql = "SELECT * FROM mencuci WHERE username = ?";
  // Membuat statement yang sudah disiapkan (prepared statement) dari query SQL yang sebelumnya disimpan di variabel $sql.
  $stmt = $conn->prepare($sql);
  // Mengisi (bind) parameter ? dalam query SQL dengan nilai dari variabel $username, dan memberi tahu bahwa nilainya bertipe string (s).
  $stmt->bind_param("s", $username);
  // Menjalankan (mengeksekusi) perintah SQL yang sudah disiapkan 
  $stmt->execute();
  // Mengambil hasil query yang sudah dieksekusi menggunakan $stmt->execute() dan menyimpannya dalam variabel $result.
  $result = $stmt->get_result();

  // Cek apakah jumlah baris hasil query adalah tepat satu (1 baris data). Biasanya dipakai saat kamu ingin memastikan bahwa data ditemukan dan hanya satu yang cocok.
  if ($result->num_rows === 1) {
    // Mengambil 1 baris data dari hasil query dan menyimpannya ke variabel $user dalam bentuk array asosiatif.
    // Array asosiatif adalah jenis array di PHP yang menggunakan nama (key) sebagai indeks, bukan angka.
    $user = $result->fetch_assoc();


    // Memeriksa apakah password yang dimasukkan oleh pengguna ($password) cocok dengan password yang tersimpan di database ($user["password"]), menggunakan fungsi password_verify().
    if (password_verify($password, $user["password"])) {
      // Menyimpan username pengguna yang login ke dalam session, agar bisa dipakai di halaman lain.
      $_SESSION["username"] = $user["username"];
      // Menyimpan peran pengguna (misalnya: admin, user, guest) ke dalam session, setelah login berhasil.
      $_SESSION["role"] = $user["role"];


      // Mengecek apakah tombol form dengan name="login_admin" ditekan atau dikirim melalui metode POST.
      if (isset($_POST["login_admin"])) {
        // kalau login nya sebagai admin
        if ($user["role"] === "admin") {
        $swalScript = "
          <script>
            Swal.fire({
              icon: 'success',
              title: 'Login sebagai admin berhasil!',
              confirmButtonText: 'OK'
            }).then(() => {
              window.location.href = 'admin.php';
            });
          </script>";
        } else {
          $swalScript = "
          <script>
            Swal.fire({
              icon: 'error',
              title: 'Akses ditolak anda bukan admin!',
              confirmButtonText: 'OK'
            }).then(() => {
              window.location.href = 'login.php';
            });
          </script>";
        }
      } else {
        $swalScript = "
        <script>
          Swal.fire({
            icon: 'success',
            title: 'Login berhasil!',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = 'index.php';
          });
        </script>";
      }
      echo $swalScript;

    } else {
      $message = "Password salah.";
    }
  } else {
    $message = "Username tidak ditemukan.";
  }

  $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow p-4">
          <h3 class="text-center mb-4">Login</h3>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php endif; ?>

           

       <!-- FORM LOGIN USER / ADMIN -->
<form method="POST">
  <div class="mb-3">
    <label class="form-label">Username</label>
    <input type="text" class="form-control" name="username" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" class="form-control" name="password" required>
  </div>

  <!-- Tombol Login User -->
  <button type="submit" name="login_user" class="btn btn-success w-100 mb-2">Login User</button>

  <!-- Tombol Login Admin -->
  <button type="submit" name="login_admin" class="btn btn-primary w-100 mb-2">Login Admin</button>
</form>

<!-- FORM MASUK TANPA AKUN (TERPISAH) -->
<form method="POST">
  <input type="hidden" name="login_guest" value="1">
  <button type="submit" class="btn btn-outline-secondary w-100 mb-3">Kembali ke Beranda</button>
</form>

<!-- Pesan Error -->
<?php if (!empty($message)): ?>
  <div class="alert alert-danger text-center">
    <?= $message ?>
  </div>
<?php endif; ?>

<!-- Link Daftar -->
<div class="text-center mt-3">
  Belum punya akun? <a href="regis.php">Daftar</a>
</div>


        </div>
      </div>
    </div>
  </div>
   <?= $swalScript ?>
</body>
</html>
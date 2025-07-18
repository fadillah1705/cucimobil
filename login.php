<?php
// Menghubungkan ke file koneksi database
include "conn.php";

// Memulai session untuk menyimpan informasi user
session_start();
// Variabel untuk menyimpan pesan error (jika ada)
$message = "";

// Jika form login dikirim (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Login sebagai TAMU (tidak perlu input)
  if (isset($_POST["login"]) && $_POST["login"] === "guest") {
    $_SESSION["username"] = "Tamu";   // Simpan username sebagai "Tamu"
    $_SESSION["role"] = "guest";      // Role juga diset sebagai "guest"
    echo "<script>
      alert('Masuk sebagai tamu berhasil!');
      window.location.href = 'index.php'; // Redirect ke halaman utama
    </script>";
    exit;
  }

  // Login sebagai user/admin (butuh username & password)
  $username = $_POST["username"] ?? ''; // Ambil username dari input
  $password = $_POST["password"] ?? ''; // Ambil password dari input
  $loginType = $_POST["login"] ?? 'user'; // Cek tipe login (user/admin)

  // Ambil data dari database berdasarkan username
  $sql = "SELECT * FROM mencuci WHERE username = ?";
  $stmt = $conn->prepare($sql);                 // Persiapkan query
  $stmt->bind_param("s", $username);            // Ikat parameter (string)
  $stmt->execute();                             // Jalankan query
  $result = $stmt->get_result();                // Ambil hasilnya

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();             // Ambil baris user

    // Cek password cocok dengan yang di-hash di database
    if (password_verify($password, $user["password"])) {
      $_SESSION["username"] = $user["username"];
      $_SESSION["role"] = $user["role"];

      // Jika login sebagai admin
      if ($loginType === "admin") {
        if ($user["role"] === "admin") {
          echo "<script>
            alert('Login sebagai admin berhasil!');
            window.location.href = 'admin.php'; // Arahkan ke halaman admin
          </script>";
        } else {
          echo "<script>
            alert('Akses ditolak. Bukan akun admin.');
            window.location.href = 'login.php'; // Kembali ke login
          </script>";
        }
      } else {
        // Jika login sebagai user biasa
        echo "<script>
          alert('Login berhasil!');
          window.location.href = 'index.php'; // Redirect ke halaman utama
        </script>";
      }
      exit(); // Hentikan eksekusi script
    } else {
      $message = "Password salah."; // Jika password tidak cocok
    }
  } else {
    $message = "Username tidak ditemukan."; // Jika username tidak ada
  }

  $stmt->close(); // Tutup statement
}
?>

<!-- Bagian HTML -->
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <!-- Impor Bootstrap dari CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <!-- Kartu untuk form login -->
        <div class="card shadow p-4">
          <h3 class="text-center mb-4">Login</h3>

          <!-- FORM LOGIN -->
          <form method="POST">
            <!-- Input Username -->
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" name="username" required>
            </div>

            <!-- Input Password -->
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>

            <!-- Tombol Login sebagai User -->
            <button type="submit" name="login" value="user" class="btn btn-success w-100 mb-2">
              Login User
            </button>

            <!-- Tombol Login sebagai Admin -->
            <button type="submit" name="login" value="admin" class="btn btn-primary w-100 mb-2">
              Login Admin
            </button>

            <!-- Tombol Login sebagai Tamu -->
            <button type="submit" name="login" value="guest" class="btn btn-outline-secondary w-100 mb-3">
              Masuk tanpa akun
            </button>
          </form>

          <!-- Tampilkan Pesan Error Jika Ada -->
          <?php if (!empty($message)): ?>
            <div class="alert alert-danger text-center">
              <?= $message ?>
            </div>
          <?php endif; ?>

          <!-- Link ke halaman registrasi -->
          <div class="text-center mt-3">
            Belum punya akun? <a href="regis.php">Daftar</a>
          </div>

        </div> <!-- End Card -->
      </div>
    </div>
  </div>
</body>
</html>

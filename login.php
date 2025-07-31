<?php
// PHP menggunakan session untuk menyimpan data yang tetap tersimpan antar halaman, misalnya data login pengguna.
// Tanpa session_start();, kamu tidak bisa menggunakan atau menyimpan $_SESSION.
session_start(); // Pastikan ini adalah baris pertama di file PHP!

include "conn.php"; // Pastikan file koneksi database Anda bernama 'conn.php'

$swalScript = ""; // Variabel untuk menampung script SweetAlert2

// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {


  // ✅ Login sebagai tamu
  // if (isset($_POST["login"]) && $_POST["login"] === "guest") {
  //   $_SESSION["username"] = "Tamu";
  //   $_SESSION["role"] = "guest";
  //   $swalScript = "
  //     <script>
  //       Swal.fire({
  //         icon: 'success',
  //         title: 'Masuk sebagai tamu berhasil!',
  //         confirmButtonText: 'OK'
  //       }).then(() => {
  //         window.location.href = 'index.php';
  //       });
  //     </script>";
  // }

  // ✅ Login sebagai user / admin
  if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $loginType = $_POST["login"] ?? 'user'; // default ke user

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();

      if (password_verify($password, $user["password"])) {
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        if ($loginType === "admin") {
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
                  title: 'Akses ditolak! Anda bukan admin.',
                  confirmButtonText: 'OK'
                }).then(() => {
                  window.location.href = 'login.php';
                });
              </script>";
          }

    // Login sebagai user / admin
    if (isset($_POST["username"]) && isset($_POST["password"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $loginType = $_POST["login"] ?? 'user'; // default ke user

        // Mengambil id, username, password, dan role dari tabel 'mencuci'
        $sql = "SELECT id, username, password, role FROM mencuci WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password yang di-hash
            if (password_verify($password, $user["password"])) {
                // Login berhasil, set variabel session
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                // Redirect berdasarkan tipe login dan peran
                if ($loginType === "admin") {
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
                        // Jika mencoba login sebagai admin tapi bukan admin
                        session_destroy(); // Hancurkan session yang mungkin sudah dibuat
                        $swalScript = "
                            <script>
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Akses ditolak!',
                                    text: 'Anda tidak memiliki izin admin.',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.href = 'login.php';
                                });
                            </script>";
                    }
                } else { // Login sebagai user biasa
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

            } else {
                // Password salah
                $swalScript = "
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Gagal!',
                            text: 'Password salah.',
                            confirmButtonText: 'OK'
                        });
                    </script>";
            }

        } else {
            // Username tidak ditemukan
            $swalScript = "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal!',
                        text: 'Username tidak ditemukan.',
                        confirmButtonText: 'OK'
                    });
                </script>";
        }

        $stmt->close();
    }
}
// Tutup koneksi database setelah semua operasi selesai
if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa; /* Light background */
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .btn-success {
            background-color: #3a9fa7;
            border-color: #3a9fa7;
        }
        .btn-success:hover {
            background-color: #2e8b92;
            border-color: #2e8b92;
        }
        /* Style untuk tombol Login Admin agar berbeda dari Login User */
        .btn-primary {
            background-color: #007bff; /* Default Bootstrap primary blue */
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow p-4">
                    <h3 class="text-center mb-4 fw-bold" style="color: #3a9fa7;">Login Go Wash</h3>

                    <!-- FORM LOGIN -->
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control rounded-pill" id="username" name="username" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control rounded-pill" id="password" name="password" required>
                        </div>

                        <button type="submit" name="login" value="user" class="btn btn-success w-100 mb-2 rounded-pill">Login User</button>
                        <button type="submit" name="login" value="admin" class="btn btn-primary w-100 mb-3 rounded-pill">Login Admin</button>
                        <a href="index.php" class="btn btn-outline-secondary w-100 rounded-pill">Kembali ke beranda</a>
                    </form>

                    <!-- Pesan Error (akan digantikan oleh SweetAlert2) -->
                    <?php /* if (!empty($message)): ?>
                        <div class="alert alert-danger text-center mt-3 rounded-pill">
                            <?= $message ?>
                        </div>
                    <?php endif; */ ?>

                    <!-- Link Daftar -->
                    <div class="text-center mt-4">
                        Belum punya akun? <a href="regis.php" class="text-decoration-none fw-bold" style="color: #3a9fa7;">Daftar di sini</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert Script -->
    <?= $swalScript ?>
</body>
</html>

<?php
include "conn.php";
session_start();

$message = "";
$swalScript = "";

// ✅ Proses login
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

      } else {
        $message = "Password salah.";
      }
    } else {
      $message = "Username tidak ditemukan.";
    }

    $stmt->close();
  }
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

          <!-- FORM LOGIN -->
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" name="username" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>

            <button type="submit" name="login" value="user" class="btn btn-success w-100 mb-2">Login User</button>
            <button type="submit" name="login" value="admin" class="btn btn-primary w-100 mb-2">Login Admin</button>
           <a href="index.php" class="btn btn-outline-secondary w-100 mb-3">Kembali ke beranda</a>

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

  <!-- SweetAlert Script -->
  <?= $swalScript ?>
</body>
</html>

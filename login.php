<?php
include "conn.php";
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Login tamu
  if (isset($_POST["login"]) && $_POST["login"] === "guest") {
    $_SESSION["username"] = "Tamu";
    $_SESSION["role"] = "guest";
    echo "<script>
      alert('Masuk sebagai tamu berhasil!');
      window.location.href = 'index.php';
    </script>";
    exit;
  }

  // Login user/admin
  $username = $_POST["username"] ?? '';
  $password = $_POST["password"] ?? '';
  $loginType = $_POST["login"] ?? 'user'; // default user

  $sql = "SELECT * FROM mencuci WHERE username = ?";
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
          echo "<script>
            alert('Login sebagai admin berhasil!');
            window.location.href = 'admin.php';
          </script>";
        } else {
          echo "<script>
            alert('Akses ditolak. Bukan akun admin.');
            window.location.href = 'login.php';
          </script>";
        }
      } else {
        echo "<script>
          alert('Login berhasil!');
          window.location.href = 'index.php';
        </script>";
      }
      exit();
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
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow p-4">
          <h3 class="text-center mb-4">Login</h3>

          <!-- FORM SATU -->
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
           <a href="index.php" class="btn btn-outline-secondary w-100 mb-3">Kembali Ke Beranda</a>
          </form>

          <!-- Pesan Error -->
          <?php if (!empty($message)): ?>
            <div class="alert alert-danger text-center"><?= $message ?></div>
          <?php endif; ?>

          <!-- Link Daftar -->
          <div class="text-center mt-3">
            Belum punya akun? <a href="regis.php">Daftar</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>
</html> 
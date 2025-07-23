<?php
session_start();
include "conn.php";

// Aktifkan error reporting (debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $loginType = $_POST["login"] ?? "";

  // ✅ Login sebagai TAMU
  if ($loginType === "guest") {
    $_SESSION["username"] = "Tamu";
    $_SESSION["role"] = "guest";
    echo "<script>
      alert('Login sebagai tamu berhasil!');
      window.location.href = 'index.php';
    </script>";
    exit;
  }

  // ✅ Login sebagai USER / ADMIN
  $username = trim($_POST["username"] ?? '');
  $password = $_POST["password"] ?? '';

  if (empty($username) || empty($password)) {
    $message = "Username dan Password wajib diisi.";
  } else {
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

        // Arahkan sesuai role
        if ($user["role"] === "admin") {
          header("Location: admin.php");
        } else {
          header("Location: index.php");
        }
        exit;
      } else {
        $message = "Password salah.";
      }
    } else {
      $message = "Akun tidak ditemukan.";
    }
  }
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

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" name="username">
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password">
            </div>

            <button type="submit" name="login" value="user" class="btn btn-success w-100 mb-2">Login</button>

            <hr class="my-4">

            <button type="submit" name="login" value="guest" class="btn btn-secondary w-100">Kembali ke beranda</button>
          </form>

          <?php if (!empty($message)): ?>
            <div class="alert alert-danger mt-3 text-center"><?= htmlspecialchars($message) ?></div>
          <?php endif; ?>

          <div class="text-center mt-3">
            Belum punya akun? <a href="regis.php">Daftar di sini</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

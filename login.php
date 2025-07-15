<?php
include "conn.php"; // ganti dengan file koneksi database-mu
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST["username"];
  $password = $_POST["password"];

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

    echo "<script>
      alert('Login berhasil!');
      window.location.href = 'index.php';
    </script>";
    exit();
} else {
    $message = "Password salah.";
}



  $stmt->close();
}}
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

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php endif; ?>

         <form method="POST">
  <div class="mb-3">
    <label class="form-label">Username</label>
    <input type="text" class="form-control" name="username" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" class="form-control" name="password" required>
  </div>
  <button type="submit" class="btn btn-success w-100">Login</button>
  <br>
<button type="button" class="btn btn-success w-100" onclick="window.location.href='index.php'">
  Masuk tanpa akun
</button>

  <?php if (!empty($message)): ?>
    <div class="alert alert-danger text-center mt-3">
      <?= $message ?>
    </div>
  <?php endif; ?>
</form>

              Belum punya akun? <a href="regis.php">Daftar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
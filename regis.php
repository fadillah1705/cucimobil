<?php
include "conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $rawPassword = $_POST["password"];
  // $inputRole = $_POST["role"] ?? '';

  if (empty($username) || empty($rawPassword)) {
    $message = "Semua field harus diisi.";
  } else {
    // Enkripsi password
    $password = password_hash($rawPassword, PASSWORD_DEFAULT);


    // Cek apakah username sudah ada
    $check = $conn->prepare("SELECT id FROM mencuci WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

  $sql = "INSERT INTO users (username, password) VALUES (?, ? )";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $username, $password);


    if ($check->num_rows > 0) {
      $message = "Username sudah digunakan. Silakan pilih yang lain.";
    } else {
      // Simpan ke database

      $sql = "INSERT INTO users (username, password ) VALUES (?, ?)";

      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ss", $username, $password);

      if ($stmt->execute()) {
        echo "<script>
          alert('Registrasi berhasil!');
          window.location.href = 'login.php';
        </script>";
        exit();
      } else {
        $message = "Registrasi gagal: " . $stmt->error;
      }

      $stmt->close();
    }

    $check->close();
  }
}
?>


      



<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Registrasi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow p-4">
          <h3 class="text-center mb-4">Registrasi Akun</h3>

          <?php if (!empty($message)): ?>
            <div class="alert alert-danger"><?= $message ?></div>
          <?php endif; ?>
          
          <form method="POST">
          <!-- <div class="mb-3">
    <label class="form-label">Role</label>
    <select class="form-select" name="role" required>
      <option value="">Pilih Role</option>
      <option value="admin">Admin</option>
      <option value="user">User</option>
    </select>
  </div> -->

            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Daftar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
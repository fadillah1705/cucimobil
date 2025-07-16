<?php
include "conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
 $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

  // $role = $_POST["role"];

  $sql = "INSERT INTO mencuci (username, password) VALUES (?, ? )";
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
            <div class="alert alert-info"><?= $message ?></div>
          <?php endif; ?>

          <form method="POST">
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
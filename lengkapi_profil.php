<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include 'conn.php';

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaLengkap = $_POST['nama_lengkap'];
    $gender = $_POST['gender'];
    $foto = $_FILES['foto'];
    $fotoName = null;

    if ($foto['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $fotoName = $username . "_" . time() . "." . $ext;
        move_uploaded_file($foto['tmp_name'], "uploads/$fotoName");

        // SQL jika ada foto
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap = ?, gender = ?, foto = ? WHERE username = ?");
        $stmt->bind_param("ssss", $namaLengkap, $gender, $fotoName, $username);
    } else {
        // SQL jika tidak ada foto
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap = ?, gender = ? WHERE username = ?");
        $stmt->bind_param("sss", $namaLengkap, $gender, $username);
    }

    $stmt->execute();
    header("Location: profil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Lengkapi Profil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card mx-auto p-4 shadow-sm" style="max-width: 500px;">
    <h4 class="mb-4 text-center">Lengkapi Profil</h4>

    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
      </div>
       <div class="mb-3">
        <label for="gender" class="form-label">Jenis Kelamin</label>
        <select class="form-select" name="gender" id="gender" required>
          <option value="" disabled selected>-- Pilih --</option>
          <option value="Pria">Pria</option>
          <option value="Wanita">Wanita</option>
        </select>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="profil.php" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>

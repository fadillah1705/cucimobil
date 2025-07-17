<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include 'conn.php';

$oldUsername = $_SESSION['username'];
$namaLengkap = '';
$gender = '';

// AMBIL DATA DARI DATABASE (supaya muncul di form)
$stmt = $conn->prepare("SELECT nama_lengkap, gender FROM users WHERE username = ?");
$stmt->bind_param("s", $oldUsername);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
    $namaLengkap = $userData['nama_lengkap'];
    $gender = $userData['gender'];
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaLengkap = $_POST['nama_lengkap'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $newUsername = $_POST['username'] ?? $oldUsername;
    $foto = $_FILES['foto'] ?? null;

    $fotoName = null;

    // Proses upload foto jika ada
    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $fotoName = $newUsername . "_" . time() . "." . $ext;
        move_uploaded_file($foto['tmp_name'], "uploads/$fotoName");
    }

    // Bangun SQL sesuai kondisi
    $sql = "UPDATE users SET nama_lengkap = ?, gender = ?, username = ?" . 
           (!empty($fotoName) ? ", foto = ?" : "") . 
           " WHERE username = ?";

    // Bangun parameter binding
    $types = "sss"; // nama_lengkap, gender, username
    $params = [$namaLengkap, $gender, $newUsername];

    if (!empty($fotoName)) {
        $types .= "s";
        $params[] = $fotoName;
    }

    $types .= "s"; // old username
    $params[] = $oldUsername;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();

    $_SESSION['username'] = $newUsername;

    header("Location: profil.php");
    exit;
}
?>





<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Lengkapi Profil</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <style>
/* ---------- Container & Card ---------- */
.container {
  animation: fadeIn 0.8s ease-in-out;
}

.card {
  border-radius: 20px;
  border: none;
  background: linear-gradient(145deg, #ffffff, #c7ffffff);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  padding: 30px 25px;
}

/* ---------- Judul ---------- */
.card h4 {
  font-weight: bold;
  color: rgb(58, 159, 167);
  font-family: 'Poppins', sans-serif;
}

/* ---------- Label & Input ---------- */
.form-label {
  font-weight: 500;
  color: #495057;
}

.form-control,
.form-select {
  border-radius: 12px;
  border: 1px solid #c3e3e5;
  background-color: #ffffff;
  transition: 0.3s ease;
}

.form-control:focus,
.form-select:focus {
  box-shadow: 0 0 6px rgba(58, 159, 167, 0.4);
  border-color: rgb(58, 159, 167);
}

/* ---------- Button ---------- */
.btn {
  border-radius: 15px;
  font-weight: 500;
  padding: 10px;
  font-family: 'Poppins', sans-serif;
  transition: 0.3s ease;
}

.btn[type="submit"] {
  background-color: rgb(58, 159, 167);
  color: white;
  border: none;
}

.btn[type="submit"]:hover {
  background-color: rgb(45, 134, 140);
}

.btn[href] {
  background-color: #adb5bd;
  color: white;
  border: none;
}

.btn[href]:hover {
  background-color: #8e959b;
}

/* ---------- Animasi Fade ---------- */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
  </style>

<div class="container mt-5">
  <div class="card mx-auto p-4 shadow-sm" style="max-width: 500px;">
    <h4 class="mb-4 text-center">Lengkapi Profil</h4>

    <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
  <label for="username" class="form-label">Username Baru</label>
  <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($oldUsername) ?>" required>
</div>

      <div class="mb-3">
  <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
  <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
         value="<?= htmlspecialchars($namaLengkap) ?>" required>
</div>

<div class="mb-3">
  <label for="gender" class="form-label">Jenis Kelamin</label>
  <select class="form-select" name="gender" id="gender" required>
    <option value="" disabled <?= $gender == '' ? 'selected' : '' ?>>-- Pilih --</option>
    <option value="Pria" <?= $gender == 'Pria' ? 'selected' : '' ?>>Pria</option>
    <option value="Wanita" <?= $gender == 'Wanita' ? 'selected' : '' ?>>Wanita</option>
  </select>
</div>


      <div class="d-grid gap-2">
        <a href="pw.php" class="btn">Ubah Password</a>
        <a href="profil.php" class="btn">Batal</a>
        <button type="submit" class="btn">Simpan</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>

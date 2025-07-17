<?php
include 'conn.php';
session_start();


$username = $_SESSION['username'] ?? '';

// Pertama, pastikan user sudah login dulu
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// ✅ Kedua, larang akses tamu (role = guest)
if ($_SESSION['role'] === 'guest') {
    header("Location: index.php");
    exit;
}


$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? '';


// Ambil data user dari database
$stmt = $conn->prepare("SELECT nama_lengkap, foto, gender FROM mencuci WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$namaLengkap = $data['nama_lengkap'] ?? '';
$foto = $data['foto'] ?? '';
$gender = $data['gender'] ?? '';

// Tentukan gambar profil yang akan ditampilkan
if (!empty($foto) && file_exists("uploads/$foto")) {
    $fotoProfil = "uploads/" . htmlspecialchars($foto);
} else {
    if ($gender === "Pria") {
        $fotoProfil = "uploads/download.png";
    } elseif ($gender === "Wanita") {
        $fotoProfil = "uploads/wn.png";
    } else {
        $fotoProfil = "uploads/avatar.webp";
    }
}

// === Hapus Foto ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!empty($foto) && file_exists("uploads/$foto")) {
        unlink("uploads/$foto");
    }
    $stmt = $conn->prepare("UPDATE mencuci SET foto = NULL WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    header("Location: profil.php");
    exit;
}

// === Upload Foto Baru ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $namaBaru = uniqid() . '.' . $ext;

        // Hapus foto lama jika ada
        if (!empty($foto) && file_exists("uploads/$foto")) {
            unlink("uploads/$foto");
        }

        move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/$namaBaru");

        $stmt = $conn->prepare("UPDATE mencuci SET foto = ? WHERE username = ?");
        $stmt->bind_param("ss", $namaBaru, $username);
        $stmt->execute();

        header("Location: profil.php");
        exit;
    } else {
        echo "<script>alert('Gagal mengunggah file.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Pengguna</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .profile-img {
      width: 130px;
      height: 130px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 10px;
      border: 3px solid #ccc;
    }
  </style>
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card mx-auto p-4 shadow-sm" style="max-width: 500px;">
    <h4 class="text-center mb-4">Profil Pengguna</h4>

    <div class="text-center">
<img src="<?= $fotoProfil ?>" class="profile-img" alt="Foto Profil">

      <!-- Tombol untuk buka modal -->
       <br>
      <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#editFotoModal">
        Edit Foto Profil
      </button>
    </div>

    <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
    <p><strong>Nama Lengkap:</strong> <?= !empty($namaLengkap) ? htmlspecialchars($namaLengkap) : '<em>Belum diisi</em>' ?></p>
    <p><strong>Gender:</strong> <?= !empty($gender) ? htmlspecialchars($gender) : '<em>Belum diisi</em>' ?></p>

    <div class="d-grid gap-2 mt-4">
      <a href="index.php" class="btn btn-secondary">Kembali</a>
      <a href="lengkapi_profil.php" class="btn btn-primary">Lengkapi Profil</a>
    </div>
  </div>
</div>

<!-- Modal Ganti/Hapus Foto -->
<div class="modal fade" id="editFotoModal" tabindex="-1" aria-labelledby="editFotoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editFotoModalLabel">Edit Foto Profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="foto" class="form-label">Ganti Foto</label>
          <input class="form-control" type="file" id="foto" name="foto" accept="image/*">
        </div>
        <p class="text-muted">Atau klik tombol hapus jika ingin menghapus foto profil.</p>
      </div>
      <div class="modal-footer">
        <button type="submit" name="action" value="delete" class="btn btn-danger">Hapus Foto</button>
        <button type="submit" name="action" value="upload" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>

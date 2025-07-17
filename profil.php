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
/* ---------- PROFILE IMAGE ---------- */
.profile-img {
  width: 130px;
  height: 130px;
  object-fit: cover;
  border-radius: 50%;
  margin-bottom: 15px;
  border: 4px solid rgb(58, 159, 167);
  box-shadow: 0 6px 12px rgba(58, 159, 167, 0.3);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.profile-img:hover {
  transform: scale(1.08);
  box-shadow: 0 10px 25px rgba(58, 159, 167, 0.5);
}

/* ---------- CARD / PROFILE BOX ---------- */
.card {
  border-radius: 20px;
  border: none;
  background: linear-gradient(145deg, #ffffff, #c7ffffff);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
  padding: 20px;
}

/* Judul Form */
.card h4 {
  font-weight: bold;
  color: rgb(58, 159, 167);
  text-align: center;
  font-family: 'Poppins', sans-serif;
}

/* ---------- FORM ---------- */
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

/* ---------- BUTTONS ---------- */
.btn {
  border-radius: 15px;
  font-weight: 500;
  padding: 8px 18px;
  font-family: 'Poppins', sans-serif;
}

.btn-primary {
  background-color: rgb(58, 159, 167);
  border: none;
  color: white;
  transition: background-color 0.3s ease;
}

.btn-primary:hover {
  background-color: rgb(45, 134, 140);
}

.btn-secondary {
  background-color: #adb5bd;
  border: none;
  color: white;
}

.btn-secondary:hover {
  background-color: #8e959b;
}

/* ---------- MODAL ---------- */
.modal-content {
  border-radius: 20px;
  border: none;
  box-shadow: 0 15px 30px rgba(58, 159, 167, 0.2);
}

.modal-title {
  color: rgb(58, 159, 167);
  font-weight: 600;
}

/* ---------- TEXT ---------- */
.text-muted {
  font-size: 0.9rem;
  color: #6c757d !important;
}

a {
  text-decoration: none;
  color: rgb(58, 159, 167);
}

a:hover {
  text-decoration: underline;
  color: rgb(45, 134, 140);
}

/* ---------- FADE IN ANIMATION ---------- */
.container {
  animation: fadeIn 0.8s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}


  </style>
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card mx-auto p-4 shadow-sm" style="max-width: 500px;">
   <h4 class="text-center mb-4">
  <?php
  // Pastikan session sudah dimulai di atas
  echo "Haii, " . htmlspecialchars($_SESSION['username']) . "!";
  ?>
</h4>


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

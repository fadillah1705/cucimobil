<?php
include 'conn.php';
session_start();

// "Ambil username dari session kalau ada, kalau tidak ada, kasih string kosong sebagai gantinya."
$username = $_SESSION['username'] ?? '';

// Mengecek apakah pengguna belum login,pastikan user sudah login dulu,
// Artinya: user belum login,kalo sudah di arahkan ke halaman login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// berarti menolak akses untuk pengguna yang berperan sebagai "guest" (tamu),Pengguna langsung dialihkan ke index.php.
// Mengecek apakah role (peran) dari user yang sedang login adalah 'guest'.
if ($_SESSION['role'] === 'guest') {
  // Arahkan tamu ke halaman utama
    header("Location: index.php");
    exit;
}

// berfungsi untuk mengambil data sesi login pengguna, yaitu username dan role-nya
$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? '';


// digunakan untuk mengambil data profil pengguna dari database berdasarkan username yang sedang login.
// $stmt menyimpan perintah SQL yang sudah dipersiapkan dan siap dijalankan.
// 1. $conn->prepare(...):Fungsi ini digunakan untuk mempersiapkan perintah SQL
// Ambil (SELECT) 3 kolom dari tabel mencuci:
// Placeholder (?) :agar query lebih aman dan terhindar dari SQL Injection (data yang di bocorkan oleh manusia nakal,jadi harus pakai ?)
$stmt = $conn->prepare("SELECT nama_lengkap, foto, gender FROM mencuci WHERE username = ?");
// bind_param() digunakan untuk mengikat nilai variabel ke query SQL yang menggunakan tanda ? (placeholder).
// "s" menunjukkan bahwa nilai yang diikat adalah bertipe string.
// Isi tanda ? dalam query dengan nilai dari $username, dan anggap itu string.
// mengikat nilai variabel $username ke pernyataan SQL yang menggunakan prepared statement.
// Tujuannya agar query aman dari serangan SQL Injection.
$stmt->bind_param("s", $username);
//  Menjalankan query SQL yang sudah dipersiapkan tadi.
$stmt->execute();
// Ambil hasil dari query yang barusan dijalankan pakai $stmt->execute() tadi.
$result = $stmt->get_result();
// Karena kamu pakai fetch_assoc() tanpa perulangan. Artinya kamu hanya ambil baris pertama saja.
// Ambil 1 baris data dari hasil query tadi, dan ubah menjadi array asosiasi (pakai nama kolom sebagai key).
$data = $result->fetch_assoc();

// ?? '' itu apa : Itu namanya Null Coalescing Operator di PHP
// artinya Kalau ['key'] itu ada dan tidak null, pakai nilainya,Tapi kalau tidak ada atau null, maka pakai nilai default, yaitu string kosong ''.
//Ambil nama_lengkap,foto dan gender dari array $data.
// Kalau tidak ada, kasih nilai kosong (''),supaya tidak error
$namaLengkap = $data['nama_lengkap'] ?? '';
$foto = $data['foto'] ?? '';
$gender = $data['gender'] ?? '';

// Cek apakah variabel $foto tidak kosong dan file-nya ada di folder uploads/.
if (!empty($foto) && file_exists("uploads/$foto")) {
//  Kalau ada, gunakan foto milik user sebagai profil.
// htmlspecialchars() dipakai untuk mencegah XSS (keamanan) kalau ada karakter aneh di nama file.
    $fotoProfil = "uploads/" . htmlspecialchars($foto);

} else {
// Kalau user belum upload foto:
// Kalau gender-nya Pria, pakai download.png (gambar default pria).
    if ($gender === "Pria") {
        $fotoProfil = "uploads/download.png";
// Kalau Wanita, pakai wn.png (gambar default wanita).
    } elseif ($gender === "Wanita") {
        $fotoProfil = "uploads/wn.png";
// Kalau gender belum diisi atau bukan "Pria/Wanita", pakai avatar.webp (gambar umum/default).
    } else {
        $fotoProfil = "uploads/avatar.webp";
    }
}

// === Hapus Foto ===
// Dan terdapat input action yang nilainya "delete".
// 📝 Artinya: user mengirim permintaan untuk menghapus foto profil.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
// ➡️ Cek apakah:
// Variabel $foto tidak kosong, artinya user memang punya foto profil.
// File dengan nama itu benar-benar ada di folder uploads/.
    if (!empty($foto) && file_exists("uploads/$foto")) {
// ➡️ Fungsi unlink() digunakan untuk menghapus file dari server.
// ✅ Jadi, jika semua kondisi di atas terpenuhi, maka foto profil milik user akan dihapus dari folder uploads/.
        unlink("uploads/$foto");
    }
// Ini membuat prepared statement untuk mengubah kolom foto di tabel mencuci menjadi NULL, hanya untuk user yang login (username = ?).
// Artinya: di database, informasi tentang foto profil dikosongkan.
    $stmt = $conn->prepare("UPDATE mencuci SET foto = NULL WHERE username = ?");
// Mengikat nilai $username (tipe string, makanya "s") ke tanda ? tadi di query.
// Jadi perubahan ini hanya untuk user yang sedang login.
    $stmt->bind_param("s", $username);
  //  Menjalankan perintah UPDATE tadi: menghapus data foto dari user tersebut.
    $stmt->execute();
// Setelah update berhasil, user akan dialihkan kembali ke halaman profil.php.
    header("Location: profil.php");
    exit;
}

// === Upload Foto Baru ===
// Artinya: ini blok kode hanya dijalankan jika user memang menekan tombol "Upload Foto".
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
  //Mengecek apakah file input foto tersedia dan tidak ada error saat di-upload (error === 0 berarti sukses).
// Jadi: hanya lanjut kalau benar-benar ada file foto yang diunggah dan tidak rusak.
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
// Mengambil ekstensi file yang diunggah, misalnya: jpg, png, webp, dll.      // 
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
// uniqid() akan membuat ID acak, misalnya 64cfe4f2c9c2b.
// Kemudian ditambah . dan ekstensi tadi, jadi hasilnya misalnya 64cfe4f2c9c2b.jpg.
// Tujuan: hindari nama file tabrakan dengan file orang lain.
        $namaBaru = uniqid() . '.' . $ext;

// !empty($foto): mengecek apakah variabel $foto tidak kosong (artinya, user sudah punya foto profil sebelumnya).
// file_exists("uploads/$foto"): mengecek apakah file foto tersebut benar-benar ada di folder uploads/.
        if (!empty($foto) && file_exists("uploads/$foto")) {
          // Fungsi unlink() digunakan untuk menghapus file dari server.
            unlink("uploads/$foto");
        }

 // Fungsi move_uploaded_file() digunakan untuk memindahkan file yang baru saja di-upload oleh user dari lokasi sementara ke folder tujuan di server.
//  👉 Ini adalah lokasi sementara (temporary) file yang di-upload sebelum disimpan ke server.
// "uploads/$namaBaru"
// 👉 Ini adalah lokasi tujuan penyimpanan file di server.
        move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/$namaBaru");


// menyimpan nama file foto yang baru diupload ke database, agar nanti bisa ditampilkan di profil pengguna.
// 👉 Membuat prepared statement untuk mengupdate kolom foto di tabel mencuci, berdasarkan username tertentu.
// Tanda ? disebut placeholder – ini tempat data akan di-bind untuk mencegah SQL Injection.
        $stmt = $conn->prepare("UPDATE mencuci SET foto = ? WHERE username = ?");
// 👉 Mengikat dua nilai ke placeholder tadi:
// "ss" artinya: parameter pertama dan kedua bertipe string
// $namaBaru adalah nama file baru yang di-upload
// $username adalah username pengguna yang sedang login
        $stmt->bind_param("ss", $namaBaru, $username);
 // Menjalankan perintah SQL-nya. Setelah ini, kolom foto di database akan berisi nama file yang baru diupload.
        $stmt->execute();
// SETELAH ITU AKAN DI ARAHKAN KE HALAMANA PROFIL
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

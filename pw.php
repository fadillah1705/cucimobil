<?php
session_start();
include 'conn.php';

// Ambil data username dari session, tapi kalau belum ada (belum login atau session belum dibuat), maka isi dengan string kosong ('').
$username = $_SESSION['username'] ?? '';

//"Kalau halaman ini menerima permintaan dari form (metode POST), dan ada tombol atau data bernama ganti_password yang dikirim, maka jalankan kode di dalam blok if ini."
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
  // Mengambil nilai password lama dan baru dari input form:
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];

 // Membuat prepared statement untuk mengambil password dari tabel mencuci, berdasarkan username yang dicari.
    $stmt = $conn->prepare("SELECT password FROM mencuci WHERE username = ?");
    // Mengisi placeholder ? di query SQL dengan nilai dari variabel $username, dan menyatakan bahwa nilai itu bertipe string ("s").
    // Placeholder dalam pemrograman (terutama SQL) adalah simbol pengganti sementara yang digunakan untuk menandai bagian yang akan diisi nanti dengan data sebenarnya.
    $stmt->bind_param("s", $username);
    $stmt->execute();
    // Baris ini mengikat hasil dari query SQL ke variabel $password_hash_db.
    $stmt->bind_result($password_hash_db);
    // Digunakan untuk mengambil hasil dari query SQL yang sudah di-bind ke variabel dengan bind_result().
    $stmt->fetch();
    $stmt->close();

    // Digunakan untuk memverifikasi apakah password yang dimasukkan oleh user ($password_lama) cocok dengan hash password yang tersimpan di database ($password_hash_db).
    if (password_verify($password_lama, $password_hash_db)) {
        // Ini digunakan untuk mengenkripsi (hash) password baru sebelum disimpan ke database.
        $hashed_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        // Kode ini menyiapkan query SQL untuk mengubah (update) password seorang pengguna dalam tabel mencuci.
        $stmt = $conn->prepare("UPDATE mencuci SET password = ? WHERE username = ?");
        // Bagian ini mengikat (bind) dua nilai ke placeholder (?) di query SQL yang sudah disiapkan sebelumnya. Tujuannya agar data bisa diproses dengan aman dan benar saat query dieksekusi.
        $stmt->bind_param("ss", $hashed_baru, $username);
        $stmt->execute();
        $stmt->close();

        echo "<script>
          alert('Password berhasil diubah');
          window.location.href = 'lengkapi_profil.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('Gagal: Password lama salah!');</script>";
    }
}
?>
<!-- TAMPILAN -->
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Ganti Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .card-custom {
      background-color: #fffbe6;
      border-radius: 16px;
      border: none;
      box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    }
    .btn-primary {
      background-color: #4d94ff;
      border: none;
    }
    .btn-primary:hover {
      background-color: #1a75ff;
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <div class="card card-custom mx-auto p-4" style="max-width: 500px;">
    <h4 class="text-center mb-4">Akun Anda</h4>

    <!-- Tombol Ganti Password -->
    <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#gantiPasswordModal">
      🔒 Ganti Password
    </button>
<br>
    <!-- Tombol Kembali -->
<a href="lengkapi_profil.php" class="btn btn-secondary w-100">
  Kembali ke Profil
</a>
  </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="gantiPasswordModal" tabindex="-1" aria-labelledby="gantiPasswordLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content p-3 rounded-4 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="gantiPasswordLabel">🔐 Ganti Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST">
        <input type="hidden" name="ganti_password" value="1">
        <div class="modal-body">
          <div class="mb-3">
            <label for="password_lama" class="form-label">Password Lama</label>
            <input type="password" class="form-control" name="password_lama" required>
          </div>
          <div class="mb-3">
            <label for="password_baru" class="form-label">Password Baru</label>
            <input type="password" class="form-control" name="password_baru" required>
          </div>
        </div>


        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="window.location.href='lengkapi_profil.php'"></a>Batal</button>

        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



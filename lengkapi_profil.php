<?php
session_start();
// mengecek apakah user sudah login
if (!isset($_SESSION['username'])) {
//kalo belum akan di arahkab ke halaman login 
    header("Location: login.php");
    exit;
}
include 'conn.php';

// Mengambil username pengguna yang sedang login dari data sesi.
// Mengambil username dari session dan menyimpannya di $oldUsername.
$oldUsername = $_SESSION['username'];
// membuat sebuah variabel bernama $namaLengkap dan $gender dan memberikan nilai awal berupa string kosong ('').
$namaLengkap = '';
$gender = '';

//  mengambil data nama_lengkap dan gender dari tabel mencuci berdasarkan username.
$stmt = $conn->prepare("SELECT nama_lengkap, gender FROM mencuci WHERE username = ?");
// Kamu sedang mengisi tanda tanya ? dalam query SQL dengan nilai dari variabel $oldUsername.
$stmt->bind_param("s", $oldUsername);
// Menjalankan perintah SQL yang sudah dipersiapkan
$stmt->execute();
// Mengambil hasil dari query yang sudah dieksekusi sebelumnya, dan menyimpannya ke dalam variabel $result.
$result = $stmt->get_result();
// Mengecek apakah hasil query dari database berisi data atau tidak.
// > 0
// Kita cek: apakah jumlah datanya lebih dari nol?
// berarti ada data yang ditemukan.
if ($result->num_rows > 0) {
// Mengambil satu baris data hasil query dari database, dan menyimpannya dalam bentuk array asosiatif
// Array asosiatif adalah array yang menggunakan nama (key) untuk mengakses nilainya, bukan angka indeks seperti array biasa.s
    $userData = $result->fetch_assoc();
// Mengambil data dari array asosiatif di $userData (yang berasal dari database) dan menyimpannya ke dalam variabel biasa ($namaLengkap dan $gender) untuk digunakan lebih mudah di tempat lain.
// mengambil data dari array $userData, lalu menyimpannya ke dalam dua variabel: $namalengkap dan $ gender
    $namaLengkap = $userData['nama_lengkap'];
    $gender = $userData['gender'];
}
$stmt->close();


// $_SERVER['REQUEST_METHOD'] adalah variabel superglobal yang menyimpan jenis request HTTP dari browser.
// === digunakan untuk membandingkan secara identik, memastikan nilainya benar-benar "POST" (string).
// Mengecek apakah halaman saat ini dipanggil melalui permintaan POST, biasanya saat form disubmit.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//Mengambil nilai dari input form bernama nama_lengkap dan gender, jika ada, dan kalau tidak ada (belum diisi atau error), maka nilainya jadi string kosong (''). 
    $namaLengkap = $_POST['nama_lengkap'] ?? '';
    $gender = $_POST['gender'] ?? '';

// kalau tidak ada, pakai username lama ($oldUsername). 
    $newUsername = $_POST['username'] ?? $oldUsername;
// mengambil file gambar yang diunggah lewat form, dan menyimpannya ke variabel $foto
    $foto = $_FILES['foto'] ?? null;
// Jika file tidak diunggah atau input tidak ada, maka variabel $foto akan bernilai null.
    $fotoName = null;

// Proses upload foto jika ada
// Baris ini mengecek apakah ada file yang diunggah dan apakah upload-nya berhasil tanpa error.
    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
//Mengambil ekstensi file dari nama file yang diupload dan simpan di variabel $ext. CONTOH emsit.jpg, emsit.png
        $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
// Membuat nama file foto yang unik berdasarkan:
// Username baru ($newUsername)
// Timestamp saat ini (time())
// Ekstensi file ($ext)
//Tujuannya : Menghindari nama file kembar jika user upload file dengan nama yang sama (misalnya foto.jpg)
        $fotoName = $newUsername . "_" . time() . "." . $ext;
// Memindahkan file sementara hasil upload ke folder permanen (uploads/) dengan nama yang sudah ditentukan.
        move_uploaded_file($foto['tmp_name'], "uploads/$fotoName");
    }

    // Query ini akan mengubah data di tabel mencuci, tepatnya: nama lengkap,gender dan username
    $sql = "UPDATE mencuci SET nama_lengkap = ?, gender = ?, username = ?" . 
//  !empty($fotoName) :Mengecek apakah variabel $fotoName tidak kosong.
// Kalau $fotoName tidak kosong, tambahkan , foto = ?
// Kalau kosong, tidak usah menambahkan apapun (hasilnya "")
           (!empty($fotoName) ? ", foto = ?" : "") . 
//  Menentukan baris mana yang ingin diupdate berdasarkan username, hanya username saja ynag di ubah MENGGUNAKAN WHERA AGAR YANG LAIN TIDAK IKUT KE UBAH
           " WHERE username = ?";

    // Bangun parameter binding
    //Artinya kamu akan mengirim 3 parameter ke query,masing masing bertipe string 
    $types = "sss";
    $params = [$namaLengkap, $gender, $newUsername];

    // Untuk menambahkan parameter fotoName ke dalam query SQL jika file foto memang di-upload.
    // Mengecek apakah variabel $fotoName tidak kosong (berarti ada file yang di-upload).
    if (!empty($fotoName)) {
      // Menambahkan satu huruf "s" ke variabel $types.,pertama kan cuman 3 sekarang tambah 1 lagi
        $types .= "s";
 // Menambahkan nilai $fotoName ke akhir array $params.
//  $params adalah array berisi data yang akan di-bind (ikat) ke query SQL.
        $params[] = $fotoName;
    }


    // Menambahkan huruf "s" ke akhir string $types.
    // .= adalah operator penggabung string
    $types .= "s"; // old username
    //Menambahkan nilai dari $oldUsername ke akhir array $params. 
    $params[] = $oldUsername;

    // Eksekusi query
    // prepare() digunakan untuk mencegah SQL Injection dan membuat query jadi lebih aman.
// fungsi untuk menyiapkan perintah SQL sebelum dijalankan. 
    $stmt = $conn->prepare($sql);
    // Mengikat (bind) nilai-nilai parameter ke query SQL yang sudah disiapkan sebelumnya menggunakan prepare().
    // Operator ... disebut spread operator. Ini artinya elemen-elemen dalam array $params akan "dipecah satu per satu".
    $stmt->bind_param($types, ...$params);
    // Menjalankan query SQL 
    $stmt->execute();
    $stmt->close();

    // Update session username jika berhasil
    // Menyimpan username baru ke dalam session
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
        html {
        background-color: rgba(241, 245, 254, 1); /* Ini warna abu-abu kebiruan terang seperti di gambar profil sebelumnya */
        /* Atau jika Anda ingin warna yang sama dengan yang Anda sebutkan di chat: */
    }

    body {
        background-color: transparent; /* Pastikan body transparan agar warna html terlihat */
        /* Atau jika Anda ingin body memiliki warna sendiri, Anda bisa tetap menentukannya di sini */
        /* background-color: white; */ /* Contoh: untuk membuat area kartu putih tetap menonjol */
        min-height: 100vh; /* Penting agar html mengisi seluruh viewport jika konten pendek */
        display: flex; /* Menggunakan flexbox untuk memposisikan kartu di tengah */
        justify-content: center;
        align-items: center;
        padding: 20px; /* Padding agar kartu tidak terlalu mepet ke tepi */
    }
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

<?php
session_start();
include 'conn.php';

$username = $_SESSION['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];

    // Ambil password saat ini dari DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($password_hash_db);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($password_lama, $password_hash_db)) {
        // Password lama cocok, update
        $hashed_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
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
<div class="container mt-5">
  <div class="card mx-auto p-4 shadow-sm" style="max-width: 500px;">

<!-- Tombol Ganti Password -->
<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#gantiPasswordModal">Ganti Password</button>

<!-- Modal Bootstrap -->
<div class="form-label" id="gantiPasswordModal" tabindex="-1" aria-labelledby="gantiPasswordLabel" aria-hidden="true">
  <div class="from-control">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <!-- <h5 class="modal-title" >Ganti Password</h5> -->
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="ganti_password" value="1">
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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </div>
    </form>
  </div>
</div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


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
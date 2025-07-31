<?php
session_start(); // Pastikan ini adalah baris pertama di file PHP!
include "conn.php"; // Pastikan file koneksi database Anda bernama 'conn.php'

$message = "";
$swalScript = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil input dari form, menghapus spasi di awal dan akhir nilai tersebut.
    $username = trim($_POST['username'] ?? '');
    $rawPassword = $_POST["password"] ?? '';
    $inputRole = $_POST["role"] ?? 'user'; // Default role ke 'user' jika tidak ada input

    if (empty($username) || empty($rawPassword) || empty($inputRole)) {
        $message = "Semua field harus diisi.";
    } else {
        // Enkripsi password
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);

        // Cek apakah username sudah ada
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Username sudah digunakan. Silakan pilih yang lain.";
        } else {
            // Menambahkan data pengguna baru ke dalam tabel users
            $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $password, $inputRole);

            // Cek apakah eksekusinya berhasil atau tidak.
            if ($stmt->execute()) {
                $swalScript = "
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Registrasi berhasil!',
                            text: 'Anda sekarang bisa login.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = 'login.php';
                        });
                    </script>";
            } else {
                $message = "Registrasi gagal: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}

// Tutup koneksi database setelah semua operasi selesai
if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa; /* Light background */
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .btn-primary {
            background-color: #3a9fa7; /* Menggunakan warna yang sama dengan login.php */
            border-color: #3a9fa7;
        }
        .btn-primary:hover {
            background-color: #2e8b92;
            border-color: #2e8b92;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow p-4">
                    <h3 class="text-center mb-4 fw-bold" style="color: #3a9fa7;">Registrasi Akun</h3>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-danger text-center mt-3 rounded-pill">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select rounded-pill" id="role" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control rounded-pill" id="username" name="username" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control rounded-pill" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill mb-3">Daftar</button>
                        <a href="index.php" class="btn btn-outline-secondary w-100 rounded-pill">Kembali ke beranda</a>
                    </form>

                    <div class="text-center mt-4">
                        Sudah punya akun? <a href="login.php" class="text-decoration-none fw-bold" style="color: #3a9fa7;">Login di sini</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert Script -->
    <?= $swalScript ?>
</body>
</html>

<?php
session_start();
include 'conn.php';

$username = $_SESSION['username'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];

    // Periksa tabel yang benar, asumsikan ini 'users' berdasarkan file lain.
    // Jika tabel yang benar adalah 'mencuci', biarkan seperti semula.
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?"); // Mengubah 'mencuci' menjadi 'users'
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($password_hash_db);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($password_lama, $password_hash_db)) {
        $hashed_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        // Periksa tabel yang benar, asumsikan ini 'users' berdasarkan file lain.
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?"); // Mengubah 'mencuci' menjadi 'users'
        $stmt->bind_param("ss", $hashed_baru, $username);
        $stmt->execute();
        $stmt->close();

        echo "<script>
            alert('Password berhasil diubah!');
            window.location.href = 'profil.php'; // Kembali ke profil setelah berhasil
        </script>";
        exit;
    } else {
        echo "<script>alert('Gagal: Password lama salah!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ganti Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #3A9FA7; 
            --dark-blue: #1e1e4c; 
            --light-blue: #70D8DF; 
            --orange: #FFC107; 
            --green: #28a745; 
            --red: #dc3545; 
            --white: #ffffff;
            --light-gray: #f2f7f9; 
            --medium-gray: #6c757d;
            --text-dark: #343a40;
            --text-light: #fefefe;
            --bg-gradient-start: #e0f2f7; 
            --bg-gradient-end: #cbedf6; 
            --card-bg-light: #fefeff; 
            --profile-label-color: #555; 
        }

        html { 
            background: linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            min-height: 100vh;
        }
        body { 
            background-color: transparent; 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 20px; 
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
        }
        .card { 
            border-radius: 25px; 
            background: var(--card-bg-light); 
            padding: 35px; 
            box-shadow: 0 18px 50px rgba(0,0,0,0.15); 
            border: none;
            overflow: hidden; 
            position: relative;
            z-index: 1;
        }
        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at top left, rgba(58, 159, 167, 0.04) 10%, transparent 40%),
                        radial-gradient(circle at bottom right, rgba(200, 230, 240, 0.06) 10%, transparent 40%);
            transform: rotate(15deg);
            z-index: -1;
            opacity: 0.8;
        }

        /* Judul */
        .card h4 {
            font-weight: 800;
            color: var(--primary-blue);
            text-align: center;
            margin-bottom: 2rem;
        }

        /* Buttons */
        .btn {
            border-radius: 12px; 
            font-weight: 700; 
            padding: 14px 28px; 
            transition: all 0.3s ease-in-out;
            letter-spacing: 0.7px; 
            text-transform: uppercase;
        }
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            box-shadow: 0 6px 15px rgba(58, 159, 167, 0.4); 
        }
        .btn-primary:hover {
            background-color: #318a91;
            border-color: #318a91;
            transform: translateY(-5px) scale(1.02); 
            box-shadow: 0 10px 20px rgba(58, 159, 167, 0.5);
        }
        .btn-secondary {
            background-color: var(--medium-gray);
            border-color: var(--medium-gray);
            box-shadow: 0 6px 15px rgba(108, 117, 125, 0.3);
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 20px rgba(108, 117, 125, 0.4);
        }

        /* Modal specific styling */
        .modal-content {
            border-radius: 20px; /* Sedikit lebih kecil dari card utama */
            padding: 20px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.2);
            border: none;
            background: var(--card-bg-light);
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
        }
        .modal-header {
            border-bottom: none;
            padding-bottom: 0;
        }
        .modal-title {
            font-weight: 700;
            color: var(--primary-blue);
            font-size: 1.5rem;
        }
        .modal-body {
            padding-top: 0;
            padding-bottom: 1rem;
        }
        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 12px;
            border: 1px solid #c3e3e5;
            background-color: var(--white);
            transition: all 0.3s ease;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(58, 159, 167, 0.25);
            border-color: var(--primary-blue);
            outline: 0;
        }
        .modal-footer {
            border-top: none;
            padding-top: 0;
            justify-content: center; /* Rata tengah tombol di footer */
            gap: 15px; /* Jarak antar tombol */
        }
        /* Style untuk tombol Simpan dan Batal di modal */
        .modal-footer .btn {
            padding: 10px 25px; /* Padding lebih kecil untuk tombol modal */
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="card mx-auto p-4" style="max-width: 500px;">
        <h4 class="mb-4">Akun Anda</h4>

        <button class="btn btn-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#gantiPasswordModal">
            🔒 Ganti Password
        </button>
        
        <a href="profil.php" class="btn btn-secondary w-100">
            Kembali ke Profil
        </a>
    </div>
</div>

<div class="modal fade" id="gantiPasswordModal" tabindex="-1" aria-labelledby="gantiPasswordLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gantiPasswordLabel">🔐 Ganti Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST">
                <input type="hidden" name="ganti_password" value="1">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="password_lama" class="form-label">Password Lama</label>
                        <input type="password" class="form-control" name="password_lama" id="password_lama" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_baru" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" name="password_baru" id="password_baru" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
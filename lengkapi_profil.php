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
    $foto = $_FILES['foto'] ?? null; // Keep this, even if not used in this form, it's safer for future expansion
    $fotoName = null;

    // This section for foto upload logic is present but commented out/not used in the HTML form.
    // If you plan to add foto upload to this page, uncomment and adjust the HTML.
    /*
    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $fotoName = $newUsername . "_" . time() . "." . $ext;
        move_uploaded_file($foto['tmp_name'], "uploads/$fotoName");
    }
    */

    $sql = "UPDATE users SET nama_lengkap = ?, gender = ?, username = ?" .
           (!empty($fotoName) ? ", foto = ?" : "") . // This part will always be false unless foto upload is enabled
           " WHERE username = ?";

    $types = "sss";
    $params = [$namaLengkap, $gender, $newUsername];

    if (!empty($fotoName)) { // This condition will only be true if a file was uploaded and processed
        $types .= "s";
        $params[] = $fotoName;
    }

    $types .= "s";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
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
            font-weight: 800; /* Lebih tebal dari bold */
            color: var(--primary-blue);
            font-family: 'Poppins', sans-serif;
            text-align: center; /* Pastikan judul di tengah */
            margin-bottom: 2rem; /* Tambah jarak bawah */
        }

        /* Label & Input */
        .form-label {
            font-weight: 600; /* Sedikit lebih tebal */
            color: var(--text-dark); /* Mengikuti warna teks utama */
            font-family: 'Poppins', sans-serif;
            margin-bottom: 0.5rem; /* Jarak antara label dan input */
        }

        .form-control,
        .form-select {
            border-radius: 12px; /* Disesuaikan dengan gaya umum */
            border: 1px solid #c3e3e5;
            background-color: var(--white);
            transition: all 0.3s ease; /* Transisi lebih halus */
            font-family: 'Poppins', sans-serif;
            padding: 0.75rem 1rem; /* Padding yang lebih baik */
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(58, 159, 167, 0.25); /* Gaya focus Bootstrap 5 */
            border-color: var(--primary-blue);
            outline: 0; /* Hapus outline default */
        }

        /* Buttons */
        .btn {
            border-radius: 12px; /* Disesuaikan dengan profil.php */
            font-weight: 700; /* Disesuaikan dengan profil.php */
            padding: 14px 28px; /* Disesuaikan dengan profil.php */
            transition: all 0.3s ease-in-out;
            letter-spacing: 0.7px; 
            text-transform: uppercase;
        }

        .btn-primary { /* untuk tombol Simpan */
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

        .btn-secondary { /* untuk tombol Batal */
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

        /* Animasi Fade */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

<div class="container mt-5">
    <div class="card mx-auto p-4" style="max-width: 500px;">
        <h4 class="mb-4">Lengkapi Profil</h4>

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


            <div class="d-grid gap-2 mt-4"> <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="profil.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
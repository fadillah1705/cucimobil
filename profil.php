<?php
include 'conn.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? '';

$stmt = $conn->prepare("SELECT id, nama_lengkap, foto, gender FROM mencuci WHERE username = ?");
if ($stmt === false) {
    die("Error preparing user data statement: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "User tidak ditemukan.";
    exit;
}

$userId = $data['id'];
$namaLengkap = $data['nama_lengkap'] ?? '';
$foto = $data['foto'] ?? '';
$gender = $data['gender'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Handle Delete Foto Profil
        if ($_POST['action'] === 'delete') {
            if (!empty($foto) && file_exists("uploads/$foto")) {
                unlink("uploads/$foto");
            }
            $stmt = $conn->prepare("UPDATE mencuci SET foto = NULL WHERE username = ?");
            if ($stmt === false) {
                die("Error preparing delete foto statement: " . $conn->error);
            }
            $stmt->bind_param("s", $username);
            $stmt->execute();
            header("Location: profil.php");
            exit;
        }

        // Handle Upload Foto Profil Baru
        if ($_POST['action'] === 'upload') {
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $namaBaru = uniqid() . '.' . $ext;

                if (!empty($foto) && file_exists("uploads/$foto")) {
                    unlink("uploads/$foto");
                }

                move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/$namaBaru");

                $stmt = $conn->prepare("UPDATE mencuci SET foto = ? WHERE username = ?");
                if ($stmt === false) {
                    die("Error preparing upload foto statement: " . $conn->error);
                }
                $stmt->bind_param("ss", $namaBaru, $username);
                $stmt->execute();

                header("Location: profil.php");
                exit;
            } else {
                echo "<script>alert('Gagal mengunggah file.');</script>";
            }
        }

        // Handle Klaim Reward (hanya mencatat klaim, tidak mereset loyalty card di sini)
        if ($_POST['action'] === 'claim_reward') {
            // Re-fetch totalCuci to ensure it's up-to-date before allowing claim
            $stmt_recheck = $conn->prepare("SELECT total_cuci FROM loyalty_card WHERE pelanggan_id = ?");
            if ($stmt_recheck === false) {
                die("Error preparing loyalty recheck statement: " . $conn->error);
            }
            $stmt_recheck->bind_param("i", $userId);
            $stmt_recheck->execute();
            $result_recheck = $stmt_recheck->get_result();
            $loyalty_recheck = $result_recheck->fetch_assoc();
            $currentTotalCuci = $loyalty_recheck['total_cuci'] ?? 0;

            // Cek apakah ada klaim reward yang masih pending untuk user ini
            $stmt_check_pending_claim = $conn->prepare("SELECT COUNT(*) AS total_pending FROM reward_claims WHERE pelanggan_id = ? AND status = 'Pending'");
            if ($stmt_check_pending_claim === false) {
                die("Error preparing pending claim check statement: " . $conn->error);
            }
            $stmt_check_pending_claim->bind_param("i", $userId);
            $stmt_check_pending_claim->execute();
            $result_pending_claim = $stmt_check_pending_claim->get_result();
            $pending_claim_count = $result_pending_claim->fetch_assoc()['total_pending'];

            if ($userId && $currentTotalCuci >= 9 && $pending_claim_count == 0) { // Minimal 9 stamp untuk klaim FREE ke-10 DAN tidak ada klaim pending
                // HANYA CATAT KLAIM REWARD KE TABEL reward_claims DENGAN STATUS 'Pending'
                // Logika reset loyalty_card dan penghapusan booking 'Selesai'
                // AKAN DITANGANI OLEH ADMIN DI admin.php SAAT KLAIM DISETUJUI.
                $klaimTanggal = date('Y-m-d H:i:s'); // Menggunakan datetime untuk presisi
                $stmt_claim = $conn->prepare("INSERT INTO reward_claims (pelanggan_id, klaim_tanggal, status) VALUES (?, ?, 'Pending')");
                if ($stmt_claim === false) {
                    die("Error preparing insert claim statement: " . $conn->error);
                }
                $stmt_claim->bind_param("is", $userId, $klaimTanggal);
                
                if ($stmt_claim->execute()) {
                    echo "<script>alert('Reward berhasil diajukan! Silakan tunggu konfirmasi dari admin. Poin Anda belum direset.'); window.location.href='profil.php';</script>";
                    exit;
                } else {
                    echo "<script>alert('Gagal mengajukan klaim reward: " . $stmt_claim->error . "'); window.location.href='profil.php';</script>";
                    exit;
                }
            } else {
                echo "<script>alert('Anda belum memenuhi syarat untuk mengklaim reward atau sudah ada klaim yang menunggu konfirmasi.'); window.location.href='profil.php';</script>";
                exit;
            }
        }
    }
}

// Ambil data loyalty card sesuai user_id (setelah potensi reset)
$stmt = $conn->prepare("SELECT total_cuci, poin, terakhir_cuci FROM loyalty_card WHERE pelanggan_id = ?");
if ($stmt === false) {
    die("Error preparing loyalty card statement: " . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$loyalty = $result->fetch_assoc();

$totalCuci = $loyalty['total_cuci'] ?? 0;
$poin = $loyalty['poin'] ?? 0;
// $terakhirCuci = $loyalty['terakhir_cuci'] ?? '-'; // This line is now effectively superseded for display

// Ambil semua tanggal booking yang sudah selesai
$bookingDates = [];
// Order by tanggal DESC to easily get the latest if needed, though ASC is fine for the loop
$stmtDates = $conn->prepare("SELECT tanggal FROM booking WHERE pelanggan_id = ? AND status = 'Selesai' ORDER BY tanggal ASC");
if ($stmtDates === false) {
    die("Error preparing booking dates statement: " . $conn->error);
}
$stmtDates->bind_param("i", $userId);
$stmtDates->execute();
$resultDates = $stmtDates->get_result();
while ($row = $resultDates->fetch_assoc()) {
    $bookingDates[] = $row['tanggal'];
}

// Determine the latest wash date for display, prioritizing actual completed bookings
$displayTerakhirCuci = '-';
if (!empty($bookingDates)) {
    // Since bookingDates is ordered ASC, the last element is the latest.
    $displayTerakhirCuci = end($bookingDates);
}


// Tentukan gambar profil yang akan ditampilkan
if (!empty($foto) && file_exists("uploads/$foto")) {
    $fotoProfil = "uploads/" . htmlspecialchars($foto);
} else {
    if ($gender === "Pria") {
        $fotoProfil = "uploads/download.png"; // Default for male
    } elseif ($gender === "Wanita") {
        $fotoProfil = "uploads/wn.png"; // Default for female
    } else {
        $fotoProfil = "uploads/avatar.webp"; // Default fallback
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Profil Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        html {
            background-color: rgba(241, 245, 254, 1);
        }

        body {
            background-color: transparent;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
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
        .card {
            border-radius: 20px;
            border: none;
            background: linear-gradient(145deg, #ffffff, #c7ffffff);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }
        .card h4 {
            font-weight: bold;
            color: rgb(58, 159, 167);
            text-align: center;
            font-family: 'Poppins', sans-serif;
        }
        .btn-primary {
            background-color: rgb(58, 159, 167);
            border: none;
            color: white;
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

        .loyalty-card-container {
            background: #1e1e4cff;
            padding: 15px;
            border-radius: 10px;
            color: white;
        }
        .loyalty-card-container h4 {
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        .loyalty-stamp-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 2px solid white;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 5px;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            color: #fff;
            flex-shrink: 0;
        }
        .loyalty-stamp-circle.stamped-1-5 {
            background-color: rgba(112, 216, 223, 1);
        }
        .loyalty-stamp-circle.stamped-6-9 {
            background-color: #4CAF50;
        }
        .loyalty-stamp-circle.free-stamp {
            border: 2px dashed yellow;
            background-color: #FFC107;
            color: #000;
        }
        .loyalty-stamp-circle.not-stamped {
            background-color: transparent;
            color: #fff;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="card mx-auto p-4 shadow-sm" style="max-width: 500px;">
        <h4 class="text-center mb-4">Haii, <?= htmlspecialchars($username) ?>!</h4>
        <div class="text-center">
            <img src="<?= $fotoProfil ?>" class="profile-img" alt="Foto Profil" />
            <br />
            <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#editFotoModal">Edit Foto Profil</button>
        </div>
        <p><strong>Username :</strong> <?= htmlspecialchars($username) ?></p>
        <p><strong>Nama Lengkap :</strong> <?= !empty($namaLengkap) ? htmlspecialchars($namaLengkap) : '<em>~Belum diisi</em>~' ?></p>
        <p><strong>Gender :</strong> <?= !empty($gender) ? htmlspecialchars($gender) : '<em>~Belum diisi~</em>' ?></p>

        <div class="mt-4 p-3 rounded text-white loyalty-card-container">
            <h4 class="text-center text-white"><strong>LOYALTY CARD</strong></h4>
            <p><strong>Total Cuci :</strong> <?= (int)$totalCuci ?> kali</p>
            <p><strong>Poin :</strong> <span class="badge bg-warning text-dark"><?= (int)$poin ?> Poin</span></p>
            <p><strong>Terakhir Cuci :</strong>
                <?php
                if ($displayTerakhirCuci !== '-') { // Use the new variable for display
                    echo htmlspecialchars(date('d F Y', strtotime($displayTerakhirCuci)));
                } else {
                    echo '<em>~Belum ada cuci terakhir~</em>';
                }
                ?>
            </p>

            <div class="d-flex flex-wrap justify-content-center mt-3">
                <?php for ($i = 0; $i < 5; $i++):
                    $isStamped = isset($bookingDates[$i]);
                    $displayValue = $isStamped ? date('d/m', strtotime($bookingDates[$i])) : ($i + 1);
                    $circleClass = $isStamped ? 'stamped-1-5' : 'not-stamped';
                ?>
                    <div class="loyalty-stamp-circle <?= $circleClass ?>"><?= $displayValue ?></div>
                <?php endfor; ?>
            </div>

            <div class="d-flex flex-wrap justify-content-center mt-3">
                <?php for ($i = 5; $i < 9; $i++):
                    $isStamped = isset($bookingDates[$i]);
                    $displayValue = $isStamped ? date('d/m', strtotime($bookingDates[$i])) : ($i + 1);
                    $circleClass = $isStamped ? 'stamped-6-9' : 'not-stamped';
                ?>
                    <div class="loyalty-stamp-circle <?= $circleClass ?>"><?= $displayValue ?></div>
                <?php endfor; ?>
                <?php
                    $isFreeStamped = ($totalCuci >= 9);
                    $freeBgClass = $isFreeStamped ? 'free-stamp' : 'not-stamped';
                ?>
                <div class="loyalty-stamp-circle <?= $freeBgClass ?>">FREE</div>
            </div>
        </div>

        <?php
        // Cek apakah ada klaim reward yang masih pending untuk user ini
        $stmt_check_pending_claim = $conn->prepare("SELECT COUNT(*) AS total_pending FROM reward_claims WHERE pelanggan_id = ? AND status = 'Pending'");
        if ($stmt_check_pending_claim === false) {
            die("Error preparing pending claim check statement: " . $conn->error);
        }
        $stmt_check_pending_claim->bind_param("i", $userId);
        $stmt_check_pending_claim->execute();
        $result_pending_claim = $stmt_check_pending_claim->get_result();
        $pending_claim_count = $result_pending_claim->fetch_assoc()['total_pending'];

        // Tombol klaim hanya muncul jika total cuci >= 9 DAN tidak ada klaim pending
        if ($totalCuci >= 9 && $pending_claim_count == 0):
        ?>
            <div class="text-center mt-3">
                <form method="POST" action="profil.php">
                    <input type="hidden" name="action" value="claim_reward">
                    <button type="submit" class="btn btn-success btn-lg">Klaim Reward!</button>
                </form>
            </div>
        <?php elseif ($pending_claim_count > 0): ?>
            <div class="text-center mt-3 alert alert-info" role="alert">
                Anda memiliki klaim reward yang sedang menunggu konfirmasi admin.
            </div>
        <?php endif; ?>

        <div class="d-grid gap-2 mt-4">
            <a href="index.php" class="btn btn-secondary">Kembali</a>
            <a href="lengkapi_profil.php" class="btn btn-primary">Lengkapi Profil</a>
        </div>
    </div>
</div>

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
                    <input class="form-control" type="file" id="foto" name="foto" accept="image/*" />
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

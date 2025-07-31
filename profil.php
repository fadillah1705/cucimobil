<?php
include 'conn.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? ''; // Pastikan 'role' diambil dari session

// Ambil data user
$stmt = $conn->prepare("SELECT id, nama_lengkap, foto, gender FROM users WHERE username = ?");
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

// === AKSI KLAIM REWARD (Hanya untuk non-admin) ===
if ($role !== 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'klaim_reward') {
        // 1. Simpan reward claim
        $stmt = $conn->prepare("INSERT INTO reward_claims (loyalty_card_id) VALUES (?)");
        $dummyLoyaltyCardId = 1; // ← ubah jika ada struktur loyalty_card
        $stmt->bind_param("i", $dummyLoyaltyCardId);
        $stmt->execute();

        // 2. Hapus semua booking status 'Selesai' - This resets the loyalty card for the new cycle.
        $stmt = $conn->prepare("DELETE FROM booking WHERE pelanggan_id = ? AND status = 'Selesai'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // 3. Tambahkan voucher
        $stmtCheckActiveVoucher = $conn->prepare("SELECT id FROM voucher WHERE user_id = ? AND status = 'Aktif' LIMIT 1");
        $stmtCheckActiveVoucher->bind_param("i", $userId);
        $stmtCheckActiveVoucher->execute();
        $resultActiveVoucher = $stmtCheckActiveVoucher->get_result();
        if ($existingActiveVoucher = $resultActiveVoucher->fetch_assoc()) {
            $stmtUpdatePrevVoucher = $conn->prepare("UPDATE voucher SET status = 'Kadaluarsa' WHERE id = ?");
            $stmtUpdatePrevVoucher->bind_param("i", $existingActiveVoucher['id']);
            $stmtUpdatePrevVoucher->execute();
        }

        $kodeVoucher = strtoupper(uniqid("VCR"));
        $stmt = $conn->prepare("INSERT INTO voucher (user_id, kode_voucher, status) VALUES (?, ?, 'Aktif')");
        $stmt->bind_param("is", $userId, $kodeVoucher);
        $stmt->execute();

        echo "<script>alert('Reward berhasil diklaim! Voucher ditambahkan.'); window.location='profil.php';</script>";
        exit;
    }
}

// === Aksi upload / delete foto profil ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'delete') {
        if (!empty($foto) && file_exists("uploads/$foto")) unlink("uploads/$foto");
        $stmt = $conn->prepare("UPDATE users SET foto = NULL WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        header("Location: profil.php");
        exit;
    }

    if ($_POST['action'] === 'upload') {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $namaBaru = uniqid() . '.' . $ext;
            if (!empty($foto) && file_exists("uploads/$foto")) unlink("uploads/$namaBaru"); 
            move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/$namaBaru");
            $stmt = $conn->prepare("UPDATE users SET foto = ? WHERE username = ?");
            $stmt->bind_param("ss", $namaBaru, $username);
            $stmt->execute();
            header("Location: profil.php");
            exit;
        } else {
            echo "<script>alert('Gagal mengunggah file.');</script>";
        }
    }
}


// Ambil tanggal cuci selesai (Hanya untuk non-admin)
$bookingDates = [];
if ($role !== 'admin') {
    $stmtDates = $conn->prepare("SELECT tanggal FROM booking WHERE pelanggan_id = ? AND status = 'Selesai' ORDER BY tanggal ASC");
    $stmtDates->bind_param("i", $userId);
    $stmtDates->execute();
    $resultDates = $stmtDates->get_result();
    while ($row = $resultDates->fetch_assoc()) {
        $bookingDates[] = $row['tanggal'];
    }
}


$displayTerakhirCuci = !empty($bookingDates) ? end($bookingDates) : '-';

// Foto profil fallback
if (!empty($foto) && file_exists("uploads/$foto")) {
    $fotoProfil = "uploads/" . htmlspecialchars($foto);
} else {
    $fotoProfil = ($gender === "Pria") ? "uploads/download.png" :
                  (($gender === "Wanita") ? "uploads/wn.png" : "uploads/avatar.webp");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Profil Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        /* Profile Image */
        .profile-img-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 25px; 
            border-radius: 50%;
            overflow: hidden;
            background: var(--white); 
            box-shadow: 0 0 0 6px var(--primary-blue), 
                        0 0 0 10px rgba(255,255,255,0.7), 
                        0 8px 20px rgba(58, 159, 167, 0.4); 
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease; 
        }
        .profile-img-container:hover {
            box-shadow: 0 0 0 6px var(--primary-blue), 
                        0 0 0 12px rgba(255,255,255,0.8), 
                        0 10px 25px rgba(58, 159, 167, 0.6); 
            transform: scale(1.02); 
        }
        .profile-img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            border-radius: 50%; 
            display: block;
        }

        /* Profile Details Section - REVISED FOR CLOSER SPACING */
        .profile-details-section {
            background-color: var(--light-gray); 
            border-radius: 18px; 
            padding: 25px 30px; 
            margin-top: 40px; 
            box-shadow: inset 0 3px 10px rgba(0,0,0,0.07); 
            position: relative;
            overflow: hidden;
        }
        .profile-details-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0.0) 0%, rgba(255,255,255,0.05) 100%);
            z-index: 0;
            pointer-events: none;
        }

        .profile-details-item {
            display: flex; 
            align-items: baseline; /* Align text baselines for better look */
            margin-bottom: 18px; 
            padding-bottom: 18px; 
            border-bottom: 1px dashed rgba(0,0,0,0.15); 
            font-size: 1em; 
            color: var(--text-dark);
            position: relative;
            z-index: 1; 
        }
        .profile-details-item:last-child {
            border-bottom: none; 
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .profile-details-item i {
            color: var(--primary-blue); 
            font-size: 1.4em; 
            width: 30px; 
            text-align: center;
            margin-right: 10px; /* Reduced space after icon */
            flex-shrink: 0; 
        }
        .detail-label { /* New class for the label */
            color: var(--profile-label-color); 
            font-weight: 600; 
            flex-shrink: 0; 
            margin-right: 5px; /* Small space between label and value */
            text-transform: capitalize;
        }
        .detail-value { /* New class for the value */
            font-weight: 500;
            color: var(--text-dark); 
            /* Perubahan di sini: Hapus flex-grow dan text-align: right */
            word-break: break-word; 
            /* Ini akan membuat nilai mengalir tepat setelah label dengan jarak yang pas */
        }
        .profile-details-item em {
            color: var(--medium-gray); 
            font-style: italic;
            font-size: 0.9em; 
        }

        /* Loyalty Card */
        .loyalty-card-container { 
            background: linear-gradient(135deg, var(--dark-blue) 0%, #0c0c2eff 100%); 
            padding: 30px; 
            border-radius: 20px; 
            color: var(--text-light); 
            box-shadow: 0 12px 35px rgba(0,0,0,0.4); 
            margin-top: 40px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            transform: perspective(1000px) rotateX(0deg);
            transition: transform 0.4s ease-in-out;
        }
        .loyalty-card-container:hover {
            transform: perspective(1000px) rotateX(1.5deg) scale(1.005);
        }
        .loyalty-card-container::before, .loyalty-card-container::after {
            content: '';
            position: absolute;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
            z-index: 0;
            filter: blur(2px);
        }
        .loyalty-card-container::before {
            top: -20px;
            left: -20px;
            width: 120px;
            height: 120px;
            transform: rotate(45deg);
        }
        .loyalty-card-container::after {
            bottom: -30px;
            right: -30px;
            width: 180px;
            height: 180px;
            transform: rotate(-30deg);
        }
        .loyalty-card-container h4 {
            color: var(--white);
            margin-bottom: 25px;
            font-weight: 800; 
            text-shadow: 0 3px 6px rgba(0,0,0,0.4);
            letter-spacing: 1.5px;
        }
        .loyalty-card-container p {
            margin-bottom: 8px;
            font-size: 1em;
            opacity: 0.95;
        }
        .loyalty-stamp-circle { 
            width: 65px; 
            height: 65px; 
            border-radius: 50%; 
            border: 3px solid var(--white); 
            display: flex; 
            flex-direction: column;
            justify-content: center; 
            align-items: center; 
            margin: 6px; 
            font-weight: 600; 
            font-size: 13px; 
            text-align: center; 
            box-shadow: 0 5px 12px rgba(0,0,0,0.3);
            transition: all 0.3s ease-in-out;
            position: relative;
            overflow: hidden;
            cursor: default;
            flex-shrink: 0; 
        }
        .loyalty-stamp-circle::before { 
            content: '';
            position: absolute;
            top: 5px;
            left: 5px;
            right: 5px;
            bottom: 5px;
            border-radius: 50%;
            border: 1px dashed rgba(255,255,255,0.3);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            z-index: 1; 
        }
        .loyalty-stamp-circle:hover::before {
            opacity: 1;
        }
        .loyalty-stamp-circle.stamped-1-5 { background: linear-gradient(135deg, var(--light-blue), #5bc0de); color: var(--dark-blue); }
        .loyalty-stamp-circle.stamped-6-9 { background: linear-gradient(135deg, var(--green), #218838); color: var(--white); }
        .loyalty-stamp-circle.free-stamp { 
            border: 3px dashed var(--orange); 
            background: linear-gradient(135deg, #FFC107, #ffab00); 
            color: var(--text-dark); 
            font-weight: 700; 
            animation: pulseYellow 1.5s infinite alternate; 
        }
        @keyframes pulseYellow {
            0% { transform: scale(1); box-shadow: 0 0 0px rgba(255,193,7,0.7); }
            100% { transform: scale(1.05); box-shadow: 0 0 18px rgba(255,193,7,0.8); } 
        }
        .loyalty-stamp-circle.not-stamped { 
            background-color: rgba(255,255,255,0.08); 
            color: rgba(255,255,255,0.6); 
            border-style: dashed;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.2);
        }
        .loyalty-stamp-circle .stamp-number { 
            font-size: 1.6em; 
            font-weight: 700;
            line-height: 1;
            z-index: 2; 
        }
        .loyalty-stamp-circle .stamp-date { 
            font-size: 0.75em; 
            font-weight: 400;
            opacity: 0.9;
            z-index: 2;
        }
        .loyalty-stamp-circle:not(.not-stamped):hover {
            transform: translateY(-8px) scale(1.1); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.5); 
        }
        .loyalty-stamp-circle.not-stamped:hover {
             transform: translateY(-3px);
             box-shadow: inset 0 0 10px rgba(0,0,0,0.4);
        }

        /* Voucher Card */
        .voucher-card {
            background: linear-gradient(135deg, #007bff, #0056b3); 
            border-radius: 20px; 
            color: var(--white);
            position: relative;
            padding: 30px; 
            box-shadow: 0 12px 35px rgba(0,0,0,0.4); 
            overflow: hidden;
            margin-top: 30px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: transform 0.4s ease-in-out, box-shadow 0.4s ease-in-out;
            cursor: pointer;
        }
        .voucher-card:hover {
            transform: translateY(-8px) scale(1.015); 
            box-shadow: 0 18px 50px rgba(0,0,0,0.5);
        }
        .voucher-card::before, .voucher-card::after {
            content: '';
            position: absolute;
            width: 25px; 
            height: 25px;
            background: var(--card-bg-light); 
            border-radius: 50%;
            z-index: 2; 
            filter: blur(0.5px); 
        }
        .voucher-card::before { 
            top: 50%;
            left: -12.5px; 
            transform: translateY(-50%);
        }
        .voucher-card::after { 
            top: 50%;
            right: -12.5px; 
            transform: translateY(-50%);
        }
        
        .voucher-card .decoration-circle-top-right {
            content: '';
            position: absolute;
            top: -50px; 
            right: -50px;
            width: 120px; 
            height: 120px;
            background: rgba(255, 255, 255, 0.18); 
            border-radius: 50%;
            z-index: 0;
            filter: blur(3px); 
        }
        .voucher-card .decoration-icon-bottom-left {
            font-family: 'Font Awesome 5 Free'; 
            content: '\f06b'; 
            font-weight: 900; 
            font-size: 110px; 
            position: absolute;
            bottom: -35px; 
            left: -25px;
            opacity: 0.12; 
            z-index: 0;
            transform: rotate(-20deg); 
            color: var(--white);
            text-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        .voucher-card .content-wrapper {
            position: relative;
            z-index: 1; 
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .voucher-card h6 {
            color: rgba(255,255,255,0.95); 
            margin-bottom: 5px;
            font-size: 0.9em;
            font-weight: 600; 
        }
        .voucher-card h4 {
            font-size: 2.3em; 
            font-weight: 800; 
            margin-bottom: 15px;
            letter-spacing: 2px; 
            text-shadow: 0 4px 8px rgba(0,0,0,0.3); 
        }
        .voucher-card .badge {
            font-size: 0.88em; 
            padding: 7px 14px; 
            border-radius: 20px; 
            font-weight: 700;
        }
        .voucher-card img {
            width: 75px; 
            filter: drop-shadow(0 0 10px rgba(0,0,0,0.5)); 
            animation: floatUpDown 2.5s infinite ease-in-out; 
        }
        @keyframes floatUpDown {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-7px) rotate(2deg); } 
        }
        .voucher-card .text-white-50 {
            font-size: 0.85em; 
            opacity: 0.95;
            font-weight: 400;
            margin-top: 5px;
        }

        /* Info/Warning messages */
        .alert {
            border-radius: 15px; 
            margin-top: 30px; 
            font-size: 1em; 
            padding: 20px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
            border: none;
            font-weight: 500;
            animation: fadeIn 0.5s ease-out; 
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alert-warning {
            background-color: #fff8e1; 
            color: #7b5b00;
            border-left: 5px solid var(--orange); 
        }
        .alert-info {
            background-color: #e0f7fa; 
            color: #006064;
            border-left: 5px solid var(--light-blue); 
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
        .btn-success {
            background-color: var(--green);
            border-color: var(--green);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #218838;
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.5);
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
        .btn-outline-primary {
            color: var(--primary-blue);
            border-color: var(--primary-blue);
            box-shadow: 0 3px 8px rgba(58, 159, 167, 0.3);
        }
        .btn-outline-primary:hover {
            background-color: var(--primary-blue);
            color: var(--white);
            box-shadow: 0 6px 12px rgba(58, 159, 167, 0.4);
        }
        .btn-danger {
            background-color: var(--red);
            border-color: var(--red);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.3);
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.4);
        }
        .btn-info { 
            background-color: #17a2b8; 
            border-color: #17a2b8;
            box-shadow: 0 6px 15px rgba(23, 162, 184, 0.4);
            transition: all 0.3s ease-in-out; 
        }
        .btn-info:hover {
            background-color: #138496; 
            border-color: #138496;
            transform: translateY(-5px) scale(1.02); 
            box-shadow: 0 10px 20px rgba(23, 162, 184, 0.5); 
        }

        /* Smaller buttons at the bottom */
        .bottom-buttons .btn {
            padding: 10px 20px; 
            font-size: 0.9em; 
            border-radius: 10px; 
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card mx-auto p-4" style="max-width: 550px;">
        <h4 class="text-center mb-4">Halo, <span style="color: var(--primary-blue);"><?= htmlspecialchars($username) ?></span>!</h4>
        <div class="text-center">
            <div class="profile-img-container"> 
                <img src="<?= $fotoProfil ?>" class="profile-img" alt="Foto Profil" />
            </div>
            <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#editFotoModal">Edit Foto Profil</button>
        </div>
        
        <div class="profile-details-section"> 
            <div class="profile-details-item">
                <i class="fas fa-user"></i>
                <span class="detail-label">Username :</span> 
                <span class="detail-value"><?= htmlspecialchars($username) ?></span>
            </div>
            <div class="profile-details-item">
                <i class="fas fa-id-card"></i>
                <span class="detail-label">Nama Lengkap :</span> 
                <span class="detail-value"><?= !empty($namaLengkap) ? htmlspecialchars($namaLengkap) : '<em>~Belum diisi~</em>' ?></span>
            </div>
            <div class="profile-details-item">
                <?php if ($gender === "Pria"): ?>
                    <i class="fas fa-male"></i>
                <?php elseif ($gender === "Wanita"): ?>
                    <i class="fas fa-female"></i>
                <?php else: ?>
                    <i class="fas fa-genderless"></i>
                <?php endif; ?>
                <span class="detail-label">Gender :</span> 
                <span class="detail-value"><?= !empty($gender) ? htmlspecialchars($gender) : '<em>~Belum diisi~</em>' ?></span>
            </div>
            <?php if ($role === 'admin'): ?>
                <div class="profile-details-item">
                    <i class="fas fa-shield-alt"></i>
                    <span class="detail-label">Role :</span> 
                    <span class="detail-value badge bg-success text-white rounded-pill px-3 py-1">Admin</span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($role !== 'admin'): // Tampilkan Loyalty Card & Voucher hanya jika bukan admin ?>
        <div class="loyalty-card-container">
            <h4 class="text-center"><strong>LOYALTY CARD</strong></h4>
            <div class="row text-center mb-3">
                <div class="col">
                    <p class="mb-0">Total Cuci:</p>
                    <h5 class="fw-bold"><?= count($bookingDates) ?> kali</h5>
                </div>
                <div class="col">
                    <p class="mb-0">Poin:</p>
                    <h5 class="fw-bold"><span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><?= count($bookingDates) * 10 ?> Poin</span></h5>
                </div>
            </div>
            <p class="text-center"><strong>Terakhir Cuci :</strong>
                <?= $displayTerakhirCuci !== '-' ? htmlspecialchars(date('d F Y', strtotime($displayTerakhirCuci))) : '<em>~Belum ada cuci terakhir~</em>' ?>
            </p>
            <div class="d-flex flex-wrap justify-content-center mt-4">
                <?php
                for ($i = 0; $i < 9; $i++) {
                    $isStamped = isset($bookingDates[$i]);
                    $displayValue = $isStamped ? "<span class='stamp-number'>" . ($i + 1) . "</span><span class='stamp-date'>" . date('d/m', strtotime($bookingDates[$i])) . "</span>" : "<span class='stamp-number'>" . ($i + 1) . "</span>";
                    $circleClass = $isStamped ? ($i < 5 ? 'stamped-1-5' : 'stamped-6-9') : 'not-stamped';
                    echo "<div class='loyalty-stamp-circle $circleClass'>$displayValue</div>";
                }
                echo "<div class='loyalty-stamp-circle free-stamp'><span class='stamp-number'>FREE</span><span class='stamp-date'>Voucher!</span></div>"; 
                ?>
            </div>

            <?php if (count($bookingDates) >= 9): ?>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="action" value="klaim_reward">
                    <button type="submit" class="btn btn-success w-100 btn-lg">Klaim Reward Sekarang!</button>
                </form>
            <?php else: ?>
                <p class="text-center text-muted mt-3 mb-0">Anda butuh <strong><?= 9 - count($bookingDates) ?></strong> cuci lagi untuk mendapatkan reward!</p>
            <?php endif; ?>
        </div>

        <div class="mt-5">
            <h5 class="mb-3">Voucher Anda:</h5>
            <?php
            // Updated logic to check loyalty_card_id in reward_claims
            $stmtLastClaim = $conn->prepare("SELECT loyalty_card_id FROM reward_claims ORDER BY claimed_at DESC LIMIT 1");
            $stmtLastClaim->execute();
            $resultLastClaim = $stmtLastClaim->get_result();
            $lastClaim = $resultLastClaim->fetch_assoc();
            $lastClaimedLoyaltyCardId = $lastClaim ? $lastClaim['loyalty_card_id'] : 0;

            // Fetch the last active voucher for the current user
            $stmt = $conn->prepare("SELECT id, kode_voucher, status, dibuat_pada FROM voucher WHERE user_id = ? AND status = 'Aktif' ORDER BY dibuat_pada DESC LIMIT 1");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $activeVoucher = $result->fetch_assoc();

            // Logic to set voucher status to 'Kadaluarsa' (Expired) if conditions are met
            // This now checks if there's a new 'Selesai' booking AFTER the last voucher was issued,
            // or if a reward was claimed with an associated loyalty_card_id.
            $shouldExpireVoucher = false;

            if ($activeVoucher) {
                // Get the latest 'Selesai' booking date for the user
                $stmtLatestBookingDate = $conn->prepare("SELECT MAX(tanggal) AS latest_date FROM booking WHERE pelanggan_id = ? AND status = 'Selesai'");
                $stmtLatestBookingDate->bind_param("i", $userId);
                $stmtLatestBookingDate->execute();
                $resultLatestBookingDate = $stmtLatestBookingDate->get_result();
                $latestBooking = $resultLatestBookingDate->fetch_assoc();
                $latestBookingDate = $latestBooking['latest_date'] ?? null;

                // Compare voucher creation date with the latest booking completion date
                $voucherDibuatPada = new DateTime($activeVoucher['dibuat_pada']);
                
                if ($latestBookingDate) {
                    $latestBookingDateTime = new DateTime($latestBookingDate);
                    // If a booking was completed AFTER the voucher was created, expire the voucher
                    if ($latestBookingDateTime > $voucherDibuatPada) {
                        $shouldExpireVoucher = true;
                    }
                }
                
                // Also, check if a reward claim was made using the latest loyalty card that *should* be associated with this user.
                // This part needs a proper loyalty_card_id associated with the user for accuracy.
                // For now, using the $lastClaimedLoyaltyCardId from reward_claims as a general trigger.
                // A more robust solution would track loyalty_card_id per user.
                if ($lastClaimedLoyaltyCardId > 0 && count($bookingDates) == 0) { // If a reward was claimed AND bookings were reset (0 bookings)
                    $shouldExpireVoucher = true;
                }
            }

            if ($shouldExpireVoucher && $activeVoucher) {
                $stmtUpdateVoucher = $conn->prepare("UPDATE voucher SET status = 'Kadaluarsa' WHERE id = ?");
                $stmtUpdateVoucher->bind_param("i", $activeVoucher['id']);
                $stmtUpdateVoucher->execute();
                $activeVoucher = null; // Set to null as it's now expired
            }

            // Refetch active voucher after potential update
            $stmt = $conn->prepare("SELECT id, kode_voucher, status, dibuat_pada FROM voucher WHERE user_id = ? AND status = 'Aktif' ORDER BY dibuat_pada DESC LIMIT 1");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $activeVoucher = $result->fetch_assoc();

            if ($activeVoucher):
                $voucher = $activeVoucher['kode_voucher'];
                $status = $activeVoucher['status']; 
                $tanggalVoucher = date('d M Y', strtotime($activeVoucher['dibuat_pada']));
            ?>
            <div class="voucher-card">
                <div class="decoration-circle-top-right"></div>
                <div class="decoration-icon-bottom-left"></div>
                <div class="content-wrapper">
                    <div>
                        <h6>KODE VOUCHER ANDA</h6>
                        <h4><?= htmlspecialchars($voucher) ?></h4>
                        <span class="badge bg-warning text-dark mt-1">Status: <?= $status ?></span>
                    </div>
                    <div class="text-end">
                        <img src="https://cdn-icons-png.flaticon.com/512/709/709790.png" alt="voucher icon" />
                        <div class="text-white-50 small mt-1">Dibuat: <?= $tanggalVoucher ?></div>
                    </div>
                </div>
            </div>
            <?php else:
                // Fetch the most recent voucher (active or not) to display status if no active one
                $stmt = $conn->prepare("SELECT kode_voucher, status, dibuat_pada FROM voucher WHERE user_id = ? ORDER BY dibuat_pada DESC LIMIT 1");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()):
                    $voucher = $row['kode_voucher'];
                    $tanggalVoucher = date('d M Y', strtotime($row['dibuat_pada']));
                    $statusLastVoucher = $row['status'];
            ?>
                <?php if ($statusLastVoucher === 'Kadaluarsa'): ?>
                    <div class="alert alert-warning mt-2">
                        Voucher <strong><?= htmlspecialchars($voucher) ?></strong> Anda sudah tidak berlaku lagi sejak <strong><?= $tanggalVoucher ?></strong>. Silakan kumpulkan stempel lagi!
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mt-2">Belum ada voucher aktif yang dapat ditampilkan.</div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info mt-2">Belum ada voucher yang pernah diklaim. Kumpulkan 9 stempel untuk mendapatkan voucher gratis cuci!</div>
            <?php endif; endif; ?>
        </div>
        <?php endif; // Akhir dari conditional untuk non-admin ?>

        <div class="d-grid gap-2 mt-5 bottom-buttons"> 
            <a href="lengkapi_profil.php" class="btn btn-primary">Lengkapi / Edit Detail Profil</a>
            <a href="pw.php" class="btn btn-primary">Ubah Password</a>
            <a href="index.php" class="btn btn-secondary">Kembali ke Beranda</a>
        </div>
    </div>
</div>

<div class="modal fade" id="editFotoModal" tabindex="-1" aria-labelledby="editFotoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="foto" class="form-label">Ganti Foto</label>
                    <input class="form-control" type="file" id="foto" name="foto" accept="image/*" />
                </div>
                <p class="text-muted">Klik hapus jika ingin menghapus foto profil.</p>
            </div>
            <div class="modal-footer">
                <button type="submit" name="action" value="delete" class="btn btn-danger">Hapus Foto</button>
                <button type="submit" name="action" value="upload" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
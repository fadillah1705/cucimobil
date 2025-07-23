<?php
session_start();
include "conn.php"; // Pastikan file conn.php ada dan koneksi berhasil

// Cek session login (sesuaikan sesuai sistem autentikasi Anda)
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil booking dengan limit 8 untuk tampil di tabel
$bookingData = $conn->query("SELECT * FROM emsit_cucimobil ORDER BY tanggal DESC, jam_layanan DESC LIMIT 8");
if ($bookingData === false) {
    die("Error fetching booking data: " . $conn->error);
}

// Ambil log booking juga dengan limit 8
// Baris ini masih mengambil data dari tabel 'booking' untuk ditampilkan di "Data Booking Awal"
// Jika Anda ingin menghilangkan tabel ini sepenuhnya, Anda bisa menghapus blok ini juga.
$bookingLog = $conn->query("SELECT * FROM booking ORDER BY waktu DESC LIMIT 8");
if ($bookingLog === false) {
    die("Error fetching booking log: " . $conn->error);
}

// Simpan data dari kalender (tambah booking baru)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "add_booking") {
    if (!empty($_POST["username"]) && !empty($_POST["layanan"]) && !empty($_POST["jam"]) && !empty($_POST["tanggal"])) {
        $username = $conn->real_escape_string($_POST["username"]);
        $layanan = $conn->real_escape_string($_POST["layanan"]);
        $jam = $conn->real_escape_string($_POST["jam"]);
        $tanggal = $conn->real_escape_string($_POST["tanggal"]);

        // Cari ID pengguna dan nama lengkap dari tabel mencuci berdasarkan username yang diinput
        $sql_user = "SELECT id, nama_lengkap FROM mencuci WHERE username = ?";
        $stmt_user = $conn->prepare($sql_user);
        if ($stmt_user === false) {
            die("Error preparing user lookup statement: " . $conn->error . " SQL: " . $sql_user);
        }
        $stmt_user->bind_param("s", $username);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        $user = $result_user->fetch_assoc();
        $user_id = $user['id'] ?? NULL;
        $nama = $user['nama_lengkap'] ?? $username; // Fallback ke username jika nama lengkap tidak ada

        if ($user_id === NULL) {
            echo json_encode(["status" => "error", "message" => "Username pelanggan tidak ditemukan. Pastikan pelanggan sudah mendaftar."]);
            exit;
        }

        // Insert ke emsit_cucimobil dengan ID pengguna yang ditemukan
        $sql_booking = "INSERT INTO emsit_cucimobil (pelanggan_id, nama_pelanggan, nama_layanan, jam_layanan, tanggal, status)
                                             VALUES (?, ?, ?, ?, ?, 'Menunggu')";
        $stmt_booking = $conn->prepare($sql_booking);
        if ($stmt_booking === false) {
            die("Error preparing booking insert statement: " . $conn->error . " SQL: " . $sql_booking);
        }
        $stmt_booking->bind_param("issss", $user_id, $nama, $layanan, $jam, $tanggal);
        
        if ($stmt_booking->execute()) {
            // --- HAPUS BARIS INI JIKA ANDA TIDAK INGIN DATA MASUK KE TABEL 'booking' LAGI ---
            // $waktu = "$tanggal $jam";
            // $conn->query("INSERT INTO booking (nama, layanan, waktu) VALUES ('$nama', '$layanan', '$waktu')");
            // ----------------------------------------------------------------------------------
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menambahkan booking ke database: " . $stmt_booking->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Data booking belum lengkap"]);
    }
    exit;
}

// Update status dan loyalty card
if (isset($_POST["update_status"])) {
    $id = (int)$_POST["id"];
    $statusBaru = $conn->real_escape_string($_POST["status"]);

    // --- AMBIL DATA LAMA SEBELUM UPDATE STATUS DI EMSIT_CUCIMOBIL ---
    $sql_get_old_status = "SELECT status, pelanggan_id, tanggal FROM emsit_cucimobil WHERE id = ?";
    $getDataOldStatus = $conn->prepare($sql_get_old_status);
    if ($getDataOldStatus === false) {
        die("Error preparing old status lookup statement: " . $conn->error . " SQL: " . $sql_get_old_status);
    }
    $getDataOldStatus->bind_param("i", $id);
    $getDataOldStatus->execute();
    $resultOldStatus = $getDataOldStatus->get_result();
    $dataOld = $resultOldStatus->fetch_assoc();

    $statusLama = $dataOld['status'] ?? ''; // Status sebelum diubah
    $pelanggan_id = $dataOld['pelanggan_id'] ?? NULL;
    $tanggalCuci = $dataOld['tanggal'] ?? NULL;
    // -----------------------------------------------------------------

    // Update status di emsit_cucimobil
    $sql_update_emsit = "UPDATE emsit_cucimobil SET status = ? WHERE id = ?";
    $stmt_update_emsit = $conn->prepare($sql_update_emsit);
    if ($stmt_update_emsit === false) {
        die("Error preparing emsit_cucimobil update statement: " . $conn->error . " SQL: " . $sql_update_emsit);
    }
    $stmt_update_emsit->bind_param("si", $statusBaru, $id);
    $stmt_update_emsit->execute();

    // Logika untuk penambahan loyalty card (HANYA BERDASARKAN STATUS BOOKING "Selesai")
    if ($pelanggan_id != NULL) { // Pastikan ada pelanggan_id yang valid
        // Case 1: Status berubah dari non-'Selesai' menjadi 'Selesai' (PENAMBAHAN STAMP/POIN)
        if ($statusBaru == 'Selesai' && $statusLama != 'Selesai') {
            $sql_cek_loyalty = "SELECT * FROM loyalty_card WHERE pelanggan_id = ?";
            $cek = $conn->prepare($sql_cek_loyalty);
            if ($cek === false) {
                die("Error preparing loyalty check statement: " . $conn->error . " SQL: " . $sql_cek_loyalty);
            }
            $cek->bind_param("i", $pelanggan_id);
            $cek->execute();
            $resultLoyalty = $cek->get_result();
            
            $tanggalBaruLoyalty = $tanggalCuci; // Tanggal dari booking yang baru saja selesai

            if ($resultLoyalty->num_rows > 0) {
                // Jika sudah ada, update total_cuci, poin, dan terakhir_cuci
                $sql_loyalty_update = "UPDATE loyalty_card SET
                    total_cuci = total_cuci + 1,
                    poin = poin + 10,
                    terakhir_cuci = ?
                    WHERE pelanggan_id = ?";
                $stmt_loyalty_update = $conn->prepare($sql_loyalty_update);
                if ($stmt_loyalty_update === false) {
                    die("Error preparing loyalty update statement: " . $conn->error . " SQL: " . $sql_loyalty_update);
                }
                $stmt_loyalty_update->bind_param("si", $tanggalBaruLoyalty, $pelanggan_id);
                $stmt_loyalty_update->execute();
            } else {
                // Jika belum ada, insert baru
                $sql_loyalty_insert = "INSERT INTO loyalty_card (pelanggan_id, total_cuci, poin, terakhir_cuci)
                    VALUES (?, 1, 10, ?)";
                $stmt_loyalty_insert = $conn->prepare($sql_loyalty_insert);
                if ($stmt_loyalty_insert === false) {
                    die("Error preparing loyalty insert statement: " . $conn->error . " SQL: " . $sql_loyalty_insert);
                }
                $stmt_loyalty_insert->bind_param("is", $pelanggan_id, $tanggalBaruLoyalty);
                $stmt_loyalty_insert->execute();
            }
        }
        // Case 2: Status berubah dari 'Selesai' kembali ke non-'Selesai' (PENGURANGAN / ROLLBACK)
        // Ini adalah kebalikan dari penambahan poin.
        // HANYA LAKUKAN INI JIKA BOOKING 'Selesai' dibatalkan/diubah kembali.
        else if ($statusBaru != 'Selesai' && $statusLama == 'Selesai') {
            $sql_cek_loyalty_rollback = "SELECT total_cuci, poin FROM loyalty_card WHERE pelanggan_id = ?";
            $cek = $conn->prepare($sql_cek_loyalty_rollback);
            if ($cek === false) {
                die("Error preparing loyalty rollback check statement: " . $conn->error . " SQL: " . $sql_cek_loyalty_rollback);
            }
            $cek->bind_param("i", $pelanggan_id);
            $cek->execute();
            $resultLoyalty = $cek->get_result();
            $loyaltyData = $resultLoyalty->fetch_assoc();

            if ($loyaltyData) { // Jika ada data loyalty_card untuk pelanggan ini
                $currentTotalCuci = $loyaltyData['total_cuci'];
                $currentPoin = $loyaltyData['poin'];

                // Hanya kurangi jika total_cuci > 0
                if ($currentTotalCuci > 0) {
                    $newTotalCuci = $currentTotalCuci - 1;
                    $newPoin = max(0, $currentPoin - 10); // Pastikan poin tidak negatif

                    $sql_loyalty_rollback_update = "UPDATE loyalty_card SET
                        total_cuci = ?,
                        poin = ?
                        WHERE pelanggan_id = ?";
                    $stmt_loyalty_rollback = $conn->prepare($sql_loyalty_rollback_update);
                    if ($stmt_loyalty_rollback === false) {
                        die("Error preparing loyalty rollback update statement: " . $conn->error . " SQL: " . $sql_loyalty_rollback_update);
                    }
                    $stmt_loyalty_rollback->bind_param("iii", $newTotalCuci, $newPoin, $pelanggan_id);
                    $stmt_loyalty_rollback->execute();
                }
            }
        }
    }
    
    header("Location: admin.php");
    exit;
}

// BARU: Update Status Klaim Reward oleh Admin (TERMASUK LOGIKA RESET POIN DAN HAPUS BOOKING SAAT KLAIM DITERIMA)
if (isset($_POST["action"]) && $_POST["action"] == "update_claim_status") {
    $claimId = (int)$_POST["claim_id"];
    $newStatus = $conn->real_escape_string($_POST["new_status"]);

    // Get current claim status and pelanggan_id
    $sql_get_claim_info = "SELECT status, pelanggan_id FROM reward_claims WHERE id = ?";
    $stmt_get_claim_info = $conn->prepare($sql_get_claim_info);
    if ($stmt_get_claim_info === false) {
        die("Error preparing get claim info statement: " . $conn->error . " SQL: " . $sql_get_claim_info);
    }
    $stmt_get_claim_info->bind_param("i", $claimId);
    $stmt_get_claim_info->execute();
    $result_claim_info = $stmt_get_claim_info->get_result();
    $claimInfo = $result_claim_info->fetch_assoc();

    $oldStatus = $claimInfo['status'] ?? '';
    $pelanggan_id_claim = $claimInfo['pelanggan_id'] ?? NULL;

    // Update status in reward_claims table
    $sql_update_claim = "UPDATE reward_claims SET status = ? WHERE id = ?";
    $stmt_update_claim = $conn->prepare($sql_update_claim);
    if ($stmt_update_claim === false) {
        die("Error preparing update claim status statement: " . $conn->error . " SQL: " . $sql_update_claim);
    }
    $stmt_update_claim->bind_param("si", $newStatus, $claimId);
    $stmt_update_claim->execute();

    // Logika untuk reset loyalty card dan hapus booking yang sudah selesai (saat disetujui admin)
    if ($pelanggan_id_claim != NULL) {
        // Jika status berubah menjadi 'Klaim Diterima' DAN sebelumnya 'Pending'
        if ($newStatus == 'Klaim Diterima' && $oldStatus == 'Pending') {
            $conn->begin_transaction(); // Mulai transaksi
            try {
                // 1. Reset total_cuci dan poin di loyalty_card
                $sql_reset_loyalty = "UPDATE loyalty_card SET total_cuci = 0, poin = 0, terakhir_cuci = NULL WHERE pelanggan_id = ?";
                $stmt_reset_loyalty = $conn->prepare($sql_reset_loyalty);
                if ($stmt_reset_loyalty === false) {
                    throw new Exception("Error preparing reset loyalty statement: " . $conn->error . " SQL: " . $sql_reset_loyalty);
                }
                $stmt_reset_loyalty->bind_param("i", $pelanggan_id_claim);
                $stmt_reset_loyalty->execute();

                // 2. Hapus entri booking yang sudah 'Selesai' untuk user ini dari emsit_cucimobil
                // Ini penting agar hitungan stempel di profil user reset visualnya.
                $sql_delete_bookings = "DELETE FROM emsit_cucimobil WHERE pelanggan_id = ? AND status = 'Selesai'";
                $stmt_delete_bookings = $conn->prepare($sql_delete_bookings);
                if ($stmt_delete_bookings === false) {
                    throw new Exception("Error preparing delete bookings statement: " . $conn->error . " SQL: " . $sql_delete_bookings);
                }
                $stmt_delete_bookings->bind_param("i", $pelanggan_id_claim);
                $stmt_delete_bookings->execute();

                $conn->commit(); // Commit transaksi jika semua berhasil
            } catch (Exception $e) {
                $conn->rollback(); // Rollback jika ada error
                error_log("Failed to process reward claim for user ID: $pelanggan_id_claim. Error: " . $e->getMessage());
            }
        }
        // Jika status berubah dari 'Klaim Diterima' kembali ke 'Pending' atau 'Klaim Ditolak' (rollback)
        // Logika ini opsional. Jika Anda ingin mengembalikan poin, ini akan menjadi kompleks
        // karena Anda perlu melacak poin yang sudah direset. Untuk saat ini, asumsikan
        // jika sudah diterima, tidak ada rollback otomatis poin. Admin bisa koreksi manual.
    }

    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Admin Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f3f4f6; padding: 20px; }
    h2 { text-align: center; margin-top: 40px; }
    #calendar { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; background: white; margin-top: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    th, td { padding: 12px; border: 1px solid #e5e7eb; text-align: center; }
    th { background: #f9fafb; }
    tr:hover { background: #f1f5f9; }
    select, input, button { padding: 6px 10px; }
    .table-container { max-height: 400px; overflow-y: auto; margin-top: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); background: white; }
    form.logout-form { text-align: center; margin-top: 50px; }
    form.logout-form button { padding: 10px 20px; background: #e53e3e; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem; transition: background-color 0.3s ease; }
    form.logout-form button:hover { background: #c53030; }

    /* Gaya untuk modal dan overlay */
    #overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
    }
    #modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        display: none;
        width: 90%;
        max-width: 400px;
    }
    #modal label {
        display: block;
        margin-bottom: 10px;
        font-weight: bold;
    }
    #modal input[type="text"],
    #modal input[type="time"],
    #modal input[type="date"] {
        width: calc(100% - 20px);
        padding: 8px 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    #modal button {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-right: 10px;
    }
    #modal button[type="submit"] {
        background-color: #4CAF50;
        color: white;
    }
    #modal button[type="button"] {
        background-color: #f44336;
        color: white;
    }
  </style>
</head>
<body>

<h2>Kalender Booking</h2>
<div id="calendar"></div>

<div id="overlay" style="display:none;"></div>
<div id="modal" style="display:none;">
  <form id="formBooking">
    <input type="hidden" name="action" value="add_booking" />
    <label>Username Pelanggan: <input type="text" name="username" required /></label><br />
    <label>Layanan: <input type="text" name="layanan" required /></label><br />
    <label>Jam: <input type="time" name="jam" required /></label><br />
    <label>Tanggal: <input type="date" name="tanggal" id="tanggalInput" required /></label><br />
    <button type="submit">Simpan</button>
    <button type="button" onclick="closeModal()">Batal</button>
  </form>
</div>

<h2>Data Booking Fiks</h2>
<div class="table-container">
<table>
  <tr>
    <th>Nama Pelanggan</th>
    <th>Layanan</th>
    <th>Jam</th>
    <th>Tanggal</th>
    <th>Status</th>
    <th>Total Cuci</th>
    <th>Poin</th>
    <th>Terakhir Cuci</th>
  </tr>
  <?php
  // Reset pointer bookingData untuk loop ini
  $bookingData->data_seek(0);
  while ($row = $bookingData->fetch_assoc()):
    $loyal = ['total_cuci' => 0, 'poin' => 0, 'terakhir_cuci' => null];
    if ($row['pelanggan_id'] != NULL) {
        // Gunakan prepared statement untuk mengambil data loyalty_card
        $sql_loyalty_display = "SELECT total_cuci, poin, terakhir_cuci FROM loyalty_card WHERE pelanggan_id = ?";
        $stmt_loyalty_display = $conn->prepare($sql_loyalty_display);
        if ($stmt_loyalty_display === false) {
            die("Error preparing loyalty display statement: " . $conn->error . " SQL: " . $sql_loyalty_display);
        }
        $stmt_loyalty_display->bind_param("i", $row['pelanggan_id']);
        $stmt_loyalty_display->execute();
        $resultLoyaltyDisplay = $stmt_loyalty_display->get_result();
        $loyal = $resultLoyaltyDisplay->fetch_assoc() ?? $loyal;
    }
  ?>
  <tr>
    <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
    <td><?= htmlspecialchars($row['nama_layanan']) ?></td>
    <td><?= htmlspecialchars($row['jam_layanan']) ?></td>
    <td><?= htmlspecialchars($row['tanggal']) ?></td>
    <td>
      <form method="post" style="margin:0;">
        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>" />
        <select name="status" onchange="this.form.submit()">
          <?php foreach (['Menunggu', 'Diproses', 'Selesai'] as $s): ?>
            <option value="<?= $s ?>" <?= $row['status'] == $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
        <input type="hidden" name="update_status" value="1" />
      </form>
    </td>
    <td><?= (int)($loyal['total_cuci'] ?? 0) ?></td>
    <td><?= (int)($loyal['poin'] ?? 0) ?></td>
    <td><?= htmlspecialchars($loyal['terakhir_cuci'] ?? '-') ?></td>
  </tr>
  <?php endwhile; ?>
</table>
</div>

<h2>Klaim Reward</h2>
<div class="table-container">
    <table>
        <tr>
            <th>ID Klaim</th>
            <th>Nama Pelanggan</th>
            <th>Tanggal Klaim</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php
        $sql_claims_data = "
            SELECT rc.id as claim_id, m.nama_lengkap, rc.klaim_tanggal, rc.status, rc.pelanggan_id
            FROM reward_claims rc
            JOIN mencuci m ON rc.pelanggan_id = m.id
            ORDER BY rc.klaim_tanggal DESC
        ";
        $claimsData = $conn->query($sql_claims_data);
        if ($claimsData === false) {
            die("Error fetching claims data: " . $conn->error . " SQL: " . $sql_claims_data);
        }
        while ($claim = $claimsData->fetch_assoc()):
        ?>
        <tr>
            <td><?= htmlspecialchars($claim['claim_id']) ?></td>
            <td><?= htmlspecialchars($claim['nama_lengkap']) ?></td>
            <td><?= htmlspecialchars($claim['klaim_tanggal']) ?></td>
            <td><?= htmlspecialchars($claim['status']) ?></td>
            <td>
                <form method="post" style="margin:0;">
                    <input type="hidden" name="claim_id" value="<?= (int)$claim['claim_id'] ?>" />
                    <input type="hidden" name="action" value="update_claim_status" />
                    <select name="new_status" onchange="this.form.submit()">
                        <?php
                        $claim_statuses = ['Pending', 'Klaim Diterima', 'Klaim Ditolak'];
                        foreach ($claim_statuses as $status_option) {
                            $selected = ($claim['status'] == $status_option) ? 'selected' : '';
                            echo "<option value=\"{$status_option}\" {$selected}>{$status_option}</option>";
                        }
                        ?>
                    </select>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<h2>Data Booking Awal</h2>
<div class="table-container">
<table>
  <tr>
    <th>Nama</th>
    <th>Layanan</th>
    <th>Waktu</th>
  </tr>
  <?php while ($b = $bookingLog->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($b['nama']) ?></td>
      <td><?= htmlspecialchars($b['layanan']) ?></td>
      <td><?= htmlspecialchars($b['waktu']) ?></td>
    </tr>
  <?php endwhile; ?>
</table>
</div>

<form method="post" action="logout.php" class="logout-form">
  <button type="submit">Logout</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  const events = [
    <?php
    $bookingData2 = $conn->query("SELECT * FROM emsit_cucimobil");
    while ($event = $bookingData2->fetch_assoc()) {
      $title = htmlspecialchars($event['nama_layanan'] . " (" . $event['nama_pelanggan'] . ")");
      $start = $event['tanggal'] . "T" . date('H:i:s', strtotime($event['jam_layanan']));
      echo "{ id: {$event['id']}, title: '{$title}', start: '{$start}' },";
    }
    ?>
  ];

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    selectable: true,
    events: events,
    select: function(info) {
      document.getElementById("tanggalInput").value = info.startStr;
      document.getElementById("modal").style.display = "block";
      document.getElementById("overlay").style.display = "block";
    },
    eventClick: function(info) {
      if (confirm("Hapus booking ini dari kalender?")) {
        // Ini hanya menghapus dari tampilan kalender, bukan dari database.
        // Untuk menghapus dari database, Anda perlu AJAX request ke server.
        info.event.remove();
        alert("Booking berhasil dihapus dari tampilan kalender. Data di database tetap aman. Untuk menghapus permanen, Anda perlu menambahkan fitur hapus di server.");
      }
    },
  });

  calendar.render();

  window.closeModal = function () {
    document.getElementById("modal").style.display = "none";
    document.getElementById("overlay").style.display = "none";
  };

  document.getElementById("formBooking").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch("admin.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        alert("Booking berhasil ditambahkan!");
        location.reload(); // Reload halaman untuk menampilkan data terbaru
      } else {
        alert(data.message || "Gagal menambahkan booking.");
      }
    })
    .catch((error) => {
      console.error('Error:', error);
      alert("Terjadi kesalahan saat mengirim data.");
    });
  });
});
</script>

</body>
</html>
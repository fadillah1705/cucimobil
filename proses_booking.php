<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "cucimobil");
if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'pesan' => 'Gagal koneksi']);
  exit;
}

// Tangani form booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = $_POST['nama'];
  $layanan = $_POST['layanan'];
  $tanggal = $_POST['tanggal'];
  $waktu = $_POST['waktu'];
  $email = $_POST['email'];

  // Cek duplikasi booking
  $cek = $conn->prepare("SELECT * FROM booking WHERE tanggal = ? AND waktu = ?");
  $cek->bind_param("ss", $tanggal, $waktu);
  $cek->execute();
  $hasil = $cek->get_result();

  if ($hasil->num_rows > 0) {
    echo json_encode(['status' => 'terduplikasi']);
    exit;
  }

  // Simpan ke database
  $stmt = $conn->prepare("INSERT INTO booking (nama, layanan, tanggal, waktu, email) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $nama, $layanan, $tanggal, $waktu, $email);

  if ($stmt->execute()) {
    // Kirim Email
    $mail = new PHPMailer(true);
    try {
      // Konfigurasi SMTP
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'emsitpatraaa@gmail.com'; // GANTI DENGAN EMAILMU
      $mail->Password   = 'wzui gezq wscy gjyb';      // GANTI DENGAN APP PASSWORD
      $mail->SMTPSecure = 'tls';
      $mail->Port       = 587;

      // Penerima dan isi email
      $mail->setFrom('emsitpatraaa@gmail.com', 'CuciMobil');
      $mail->addAddress($email, $nama);

      $mail->isHTML(true);
      $mail->Subject = 'Konfirmasi Booking Cuci Mobil';
      $mail->Body    = "
        <h3>Halo $nama,</h3>
        <p>Terima kasih telah melakukan booking layanan <strong>$layanan</strong> pada:</p>
        <ul>
          <li>Tanggal: <strong>$tanggal</strong></li>
          <li>Waktu: <strong>$waktu</strong></li>
        </ul>
        <p>Kami tunggu kehadiran Anda di tempat kami. Terima kasih!</p>
        <br><small>Email ini dikirim otomatis.</small>
      ";

      $mail->send();
      echo json_encode(['status' => 'sukses']);
    } catch (Exception $e) {
      echo json_encode(['status' => 'sukses', 'pesan' => 'Booking berhasil, email gagal dikirim: ' . $mail->ErrorInfo]);
    }
  } else {
    echo json_encode(['status' => 'gagal']);
  }

  $stmt->close();
}
$conn->close();

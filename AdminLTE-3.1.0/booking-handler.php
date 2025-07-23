<?php
header('Content-Type: application/json');
include '../conn.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die(json_encode(['success' => false, 'message' => 'Koneksi gagal']));
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

  case 'load':
    $result = $conn->query("SELECT id, nama, layanan, waktu, tanggal, status FROM booking");
    $events = [];

    while ($row = $result->fetch_assoc()) {
      $events[] = [
        'id' => $row['id'],
        'start' => $row['tanggal'],
        'extendedProps' => [
          'nama' => $row['nama'],
          'layanan' => $row['layanan'],
          'waktu' => $row['waktu'],
          'status' => $row['status']
        ]
      ];
    }

    echo json_encode($events);
    break;

  case 'add':
    $nama    = $_POST['nama'] ?? '';
    $layanan = $_POST['layanan'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $jam     = $_POST['jam'] ?? '';

    if (!$nama || !$layanan || !$tanggal || !$jam) {
      echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
      exit;
    }

    $waktu = $tanggal . ' ' . $jam . ':00';
    $status = 'menunggu';

    $stmt = $conn->prepare("INSERT INTO booking (nama, layanan, waktu, tanggal, status) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
      echo json_encode(["success" => false, "message" => "Prepare gagal: " . $conn->error]);
      exit;
    }

    $stmt->bind_param("sssss", $nama, $layanan, $waktu, $tanggal, $status);
    $success = $stmt->execute();

    echo json_encode(["success" => $success]);
    break;

  case 'update':
    $id      = $_POST['id'] ?? '';
    $nama    = $_POST['nama'] ?? '';
    $layanan = $_POST['layanan'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $jam     = $_POST['jam'] ?? '';

    if (!$id || !$nama || !$layanan || !$tanggal || !$jam) {
      echo json_encode(["success" => false, "message" => "Data tidak lengkap"]);
      exit;
    }

    $waktu = $tanggal . ' ' . $jam . ':00';

    $stmt = $conn->prepare("UPDATE booking SET nama = ?, layanan = ?, waktu = ?, tanggal = ? WHERE id = ?");
    if (!$stmt) {
      echo json_encode(["success" => false, "message" => "Prepare gagal: " . $conn->error]);
      exit;
    }

    $stmt->bind_param("ssssi", $nama, $layanan, $waktu, $tanggal, $id);
    $success = $stmt->execute();

    echo json_encode(["success" => $success]);
    break;

  case 'delete':
    $id = $_POST['id'] ?? 0;

    if ($id) {
      $stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
      $stmt->bind_param("i", $id);
      $success = $stmt->execute();
      echo json_encode(["success" => $success]);
    } else {
      echo json_encode(["success" => false, "message" => "ID tidak valid"]);
    }
    break;

  case 'status':
    $id     = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (!$id || !$status) {
      echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
      exit;
    }

    $stmt = $conn->prepare("UPDATE booking SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $success = $stmt->execute();

    echo json_encode(["success" => $success]);
    break;

  default:
    echo json_encode(["success" => false, "message" => "Aksi tidak dikenali"]);
    break;
}
$conn->close();

?>

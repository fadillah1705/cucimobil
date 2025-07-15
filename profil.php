<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// Ambil data user dari database
$username = $_SESSION['username'];
$query = "SELECT * FROM mencuci WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Profil Pengguna</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="card shadow p-4">
      <h3 class="text-center mb-4">Profil Pengguna</h3>
      <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
      <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
      <a href="index.php" class="btn btn-secondary">Kembali</a>
    </div>
  </div>
</body>
</html>

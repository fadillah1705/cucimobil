<?php
session_start();
require_once 'conn.php';

// Cek apakah user adalah admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Ambil data layanan yang akan diedit jika ada parameter ?edit
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);

    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editService = $result->fetch_assoc();
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        // Tambah layanan baru
        $stmt = $conn->prepare("INSERT INTO services (name, description, product_used, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", 
            $_POST['name'],
            $_POST['description'],
            $_POST['product_used'],
            $_POST['price']
        );
        $stmt->execute();

    } elseif (isset($_POST['update_service'])) {
        // Update layanan
        $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, product_used = ?, price = ? WHERE id = ?");
        $stmt->bind_param("sssdi", 
            $_POST['name'], 
            $_POST['description'], 
            $_POST['product_used'], 
            $_POST['price'], 
            $_POST['id']
        );
        
        if ($stmt->execute()) {
            echo "Layanan berhasil diupdate!";
        } else {
            echo "Gagal update layanan: " . $stmt->error;
        }

    } elseif (isset($_POST['update_status'])) {
        // Handle status update via AJAX
        $id = $_POST['id'];
        $newStatus = $_POST['status'];

        $stmt = $conn->prepare("UPDATE services SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $newStatus, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        exit;
    }
}

// Handle delete/activate
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Pertama set is_active jadi 0 (tidak aktif)
    $stmt = $conn->prepare("UPDATE services SET is_active = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Kemudian benar-benar hapus
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin-harga.php?success=deleted");
        exit();
    } else {
        echo "<script>alert('Gagal menghapus layanan');</script>";
    }

    $stmt->close();
} elseif (isset($_GET['activate'])) {
    $stmt = $conn->prepare("UPDATE services SET is_active = 1 WHERE id = ?");
    $stmt->bind_param("i", $_GET['activate']);
    $stmt->execute();
}

// Ambil semua layanan
$services = $conn->query("SELECT * FROM services ORDER BY is_active DESC, name");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Dashboard 2</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="AdminLTE-3.1.0/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
    .status-badge {
      cursor: pointer;
      transition: all 0.3s;
    }
    .status-badge:hover {
      opacity: 0.8;
      transform: scale(1.05);
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed dark-mode">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__wobble" src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div>

  <!-- Main Sidebar Container -->
        <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">AdminGoWash</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="AdminLTE-3.1.0/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
         <div class="info">
  <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['username']) ?></a>
</div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item">
              <li class="nav-item">
                <a href="admin.php" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard</p>
                </a>
              </li>
          </li>
          
          <li class="nav-item">
            <a href="AdminLTE-3.1.0/tab_booking.php" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
              Booking
            </p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="admin-harga.php" class="nav-link active">
            <i class="nav-icon fas fa-chart-pie"></i>
            <p>
              Layanan
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="logout.php" class="nav-link ">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>    
      </ul>
    </nav>
  </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="container py-3">
        <h1 class="text-center mb-5 ">Kelola Layanan</h1>
        
        <!-- Form Tambah/Edit Layanan -->
        <div class="card mb-5">
            <div class="card-header">
                <h5><?= isset($_GET['edit']) ? 'Edit' : 'Tambah' ?> Layanan</h5>
            </div>
            <div class="card-body">
               <?php 
$editService = null;
if (isset($_GET['edit'])): 
    $editId = intval($_GET['edit']);
    $editStmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $editStmt->bind_param("i", $editId);
    $editStmt->execute();
    $result = $editStmt->get_result();
    $editService = $result->fetch_assoc(); // <== ini hasilnya array atau false
endif;

$editMode = is_array($editService);
?>

<form method="POST" >
    <?php if ($editMode): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($editService['id']) ?>">
    <?php endif; ?>
    
    <div class="mb-3">
        <label for="name" class="form-label">Nama Layanan</label>
        <input type="text" class="form-control" id="name" name="name" 
               value="<?= $editMode ? htmlspecialchars($editService['name']) : '' ?>" required>
    </div>
    
    <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea class="form-control" id="description" name="description" rows="3" required><?= 
            $editMode ? htmlspecialchars($editService['description']) : '' ?></textarea>
    </div>
    
    <div class="mb-3">
        <label for="product_used" class="form-label">Produk Digunakan</label>
        <input type="text" class="form-control" id="product_used" name="product_used" 
               value="<?= $editMode ? htmlspecialchars($editService['product_used']) : '' ?>" required>
    </div>
    
    
                    <div class="mb-3">
                        <label for="price" class="form-label">Harga (Rp)</label>
                        <input type="number" class="form-control" id="price" name="price" 
                               value="<?= $editMode ? htmlspecialchars($editService['price']) : '' ?>" required>
                    </div>
    <!-- Tombol Submit -->
    <button type="submit" name="<?= $editMode ? 'update_service' : 'add_service' ?>" class="btn btn-primary">
        <?= $editMode ? 'Update' : 'Tambah' ?> Layanan
    </button>
</form>

                    
                    
                    
                    <?php if (isset($_GET['edit'])): ?>
                        <a href="admin-harga.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Daftar Layanan -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama Layanan</th>
                        <th>Deskripsi</th>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= htmlspecialchars($service['name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($service['description'])) ?></td>
                        <td><?= htmlspecialchars($service['product_used']) ?></td>
                        <td>Rp<?= number_format($service['price'], 0, ',', '.') ?></td>
                        <td>
                            <span class="badge status-badge bg-<?= $service['is_active'] ? 'success' : 'danger' ?>" 
                                  data-id="<?= $service['id'] ?>">
                                <?= $service['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td>
                            <a href="admin-harga.php?edit=<?= $service['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($service['is_active']): ?>
                                <a href="admin-harga.php?delete=<?= $service['id'] ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Yakin ingin menghapus layanan ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php else: ?>
                                <a href="admin-harga.php?activate=<?= $service['id'] ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-circle"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
  </div>

  <!-- REQUIRED SCRIPTS -->
  <script src="AdminLTE-3.1.0/plugins/jquery/jquery.min.js"></script>
  <script src="AdminLTE-3.1.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="AdminLTE-3.1.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <script src="AdminLTE-3.1.0/dist/js/adminlte.js"></script>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Tangani klik pada badge status
    document.querySelectorAll('.status-badge').forEach(badge => {
      badge.addEventListener('click', function(e) {
        e.preventDefault();
        
        const serviceId = this.getAttribute('data-id');
        const isCurrentlyActive = this.classList.contains('bg-success');
        const newStatus = isCurrentlyActive ? 0 : 1;
        
        if(confirm(`Anda yakin ingin ${isCurrentlyActive ? 'menonaktifkan' : 'mengaktifkan'} layanan ini?`)) {
          const formData = new FormData();
          formData.append('update_status', true);
          formData.append('id', serviceId);
          formData.append('status', newStatus);

          fetch(window.location.href, {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if(data.success) {
              // Update tampilan
              this.classList.toggle('bg-success');
              this.classList.toggle('bg-danger');
              this.textContent = newStatus ? 'Aktif' : 'Nonaktif';
              
              // Show notification
              alert('Status berhasil diubah');
            } else {
              alert('Gagal mengubah status');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
          });
        }
      });
    });
  });
  </script>
</body>
</html>
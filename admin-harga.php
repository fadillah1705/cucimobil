<?php
session_start();
require_once 'conn.php'; // Pastikan file koneksi database Anda benar

// Cek apakah user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Ambil data layanan untuk edit
$editlayan = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM layanan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editlayan = $result->fetch_assoc();
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lokasi penyimpanan gambar
    $uploadDir = 'img/layanan/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Buat folder jika belum ada
    }

    // ================================
    // === TAMBAH LAYANAN BARU
    // ================================
    if (isset($_POST['add_layan'])) {
        $uploadedImagePath = '';

        // Upload gambar
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpnama = $_FILES['image']['tmp_name']; // Menggunakan 'tmp_name' bukan 'tmp_nama'
            $originalnama = basename($_FILES['image']['name']); // Menggunakan 'name' bukan 'nama'
            $extension = strtolower(pathinfo($originalnama, PATHINFO_EXTENSION));
            $newnama = uniqid('layan_', true) . '.' . $extension;
            $destination = $uploadDir . $newnama;

            // Validasi ekstensi
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($extension, $allowedExt)) {
                echo "<script>Swal.fire('Gagal!', 'Format gambar tidak didukung!', 'error');</script>";
                exit;
            }

            // Pindahkan file
            if (move_uploaded_file($tmpnama, $destination)) {
                $uploadedImagePath = $destination;
            } else {
                echo "<script>Swal.fire('Gagal!', 'Gagal mengupload gambar.', 'error');</script>";
                exit;
            }
        } else {
            echo "<script>Swal.fire('Gagal!', 'Gambar tidak ditemukan atau terjadi error upload.', 'error');</script>";
            exit;
        }

        // Insert ke database
        $stmt = $conn->prepare("INSERT INTO layanan (nama, image, description, product_used, price) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo "<script>Swal.fire('Error!', 'Gagal prepare statement: " . $conn->error . "', 'error');</script>";
            exit;
        }

        $stmt->bind_param("ssssd",
            $_POST['nama'],
            $uploadedImagePath,
            $_POST['description'],
            $_POST['product_used'],
            $_POST['price']
        );

        if ($stmt->execute()) {
            header("Location: admin-harga.php?success=added");
            exit;
        } else {
            echo "<script>Swal.fire('Gagal!', 'Gagal menambahkan layanan: " . $stmt->error . "', 'error');</script>";
        }

    // ================================
    // === UPDATE LAYANAN
    // ================================
    } elseif (isset($_POST['update_layan'])) {
        $id = $_POST['id'];
        $uploadedImagePath = $_POST['current_image_path'] ?? ''; // Default: pakai gambar lama dari hidden input

        // Jika gambar baru diupload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $_FILES['image']['size'] > 0) {
            $tmpnama = $_FILES['image']['tmp_name']; // Menggunakan 'tmp_name'
            $originalnama = basename($_FILES['image']['name']); // Menggunakan 'name'
            $extension = strtolower(pathinfo($originalnama, PATHINFO_EXTENSION));
            $newnama = uniqid('layan_', true) . '.' . $extension;
            $destination = $uploadDir . $newnama;

            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($extension, $allowedExt)) {
                echo "<script>Swal.fire('Gagal!', 'Format gambar tidak didukung!', 'error');</script>";
                exit;
            }

            if (move_uploaded_file($tmpnama, $destination)) {
                // Hapus gambar lama jika ada dan berbeda dengan yang baru
                if (!empty($uploadedImagePath) && file_exists($uploadedImagePath) && $uploadedImagePath !== $destination) {
                    unlink($uploadedImagePath);
                }
                $uploadedImagePath = $destination;
            } else {
                echo "<script>Swal.fire('Gagal!', 'Gagal mengupload gambar baru.', 'error');</script>";
                exit;
            }
        }

        $stmt = $conn->prepare("UPDATE layanan SET nama = ?, image = ?, description = ?, product_used = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ssssdi",
            $_POST['nama'],
            $uploadedImagePath,
            $_POST['description'],
            $_POST['product_used'],
            $_POST['price'],
            $id
        );

        if ($stmt->execute()) {
            header("Location: admin-harga.php?success=updated");
            exit;
        } else {
            echo "<script>Swal.fire('Gagal!', 'Gagal update layanan: " . $stmt->error . "', 'error');</script>";
        }

    // ================================
    // === UPDATE STATUS LAYANAN
    // ================================
    } elseif (isset($_POST['update_status'])) {
        $id = $_POST['id'];
        $newStatus = $_POST['status'];

        $stmt = $conn->prepare("UPDATE layanan SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $newStatus, $id);

        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }
}

// ================================
// === DELETE / ACTIVATE
// ================================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Ambil path gambar sebelum menghapus record
    $stmt_get_image = $conn->prepare("SELECT image FROM layanan WHERE id = ?");
    $stmt_get_image->bind_param("i", $id);
    $stmt_get_image->execute();
    $result_image = $stmt_get_image->get_result();
    $image_path_to_delete = $result_image->fetch_assoc()['image'] ?? null;
    $stmt_get_image->close();

    $stmt = $conn->prepare("DELETE FROM layanan WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Hapus file gambar dari server
        if (!empty($image_path_to_delete) && file_exists($image_path_to_delete)) {
            unlink($image_path_to_delete);
        }
        header("Location: admin-harga.php?success=deleted");
        exit;
    } else {
        echo "<script>Swal.fire('Gagal!', 'Gagal menghapus layanan: " . $stmt->error . "', 'error');</script>";
    }
} elseif (isset($_GET['activate'])) {
    $stmt = $conn->prepare("UPDATE layanan SET is_active = 1 WHERE id = ?");
    $stmt->bind_param("i", $_GET['activate']);
    $stmt->execute();
    header("Location: admin-harga.php?success=activated");
    exit;
}

// Ambil semua data layanan
$layanan_result = $conn->query("SELECT * FROM layanan ORDER BY is_active DESC, nama");
if (!$layanan_result) {
    // Query failed, output the MySQL error and stop execution
    die("Error fetching layanan data: " . $conn->error);
}
$layanan = $layanan_result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as an associative array
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | Kelola Layanan</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="AdminLTE-3.1.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Custom CSS untuk menyesuaikan warna dan tampilan */
        .status-badge {
            cursor: pointer;
            transition: all 0.3s;
        }
        .status-badge:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }
        .layan-table-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        /* ==== Kustomisasi Warna Tambahan ==== */
        /* Contoh: Mengubah warna tombol update/tambah menjadi biru langit */
        .btn-primary-custom {
            background-color: #00BFFF; /* Deep Sky Blue */
            border-color: #00BFFF;
            color: white;
        }
        .btn-primary-custom:hover {
            background-color: #009ACD; /* Slightly darker */
            border-color: #009ACD;
        }

        /* Contoh: Mengubah warna tombol edit menjadi ungu */
        .btn-warning-custom {
            background-color: #8A2BE2; /* Blue Violet */
            border-color: #8A2BE2;
            color: white;
        }
        .btn-warning-custom:hover {
            background-color: #6A1BA8; /* Darker Blue Violet */
            border-color: #6A1BA8;
        }

        /* Contoh: Mengubah warna tombol delete menjadi merah tua */
        .btn-danger-custom {
            background-color: #DC143C; /* Crimson */
            border-color: #DC143C;
            color: white;
        }
        .btn-danger-custom:hover {
            background-color: #B20C2D; /* Darker Crimson */
            border-color: #B20C2D;
        }

        /* Contoh: Mengubah warna tombol activate menjadi hijau gelap */
        .btn-success-custom {
            background-color: #228B22; /* Forest Green */
            border-color: #228B22;
            color: white;
        }
        .btn-success-custom:hover {
            background-color: #156615; /* Darker Forest Green */
            border-color: #156615;
        }

        /* Contoh: Mengubah warna badge aktif menjadi hijau kebiruan */
        .badge-success-custom {
            background-color: #20B2AA !important; /* Light Sea Green */
        }

        /* Contoh: Mengubah warna badge nonaktif menjadi abu-abu gelap */
        .badge-danger-custom {
            background-color: #696969 !important; /* Dim Gray */
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed dark-mode">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__wobble" src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
    </div>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="admin.php" class="brand-link">
            <img src="AdminLTE-3.1.0/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">AdminGoWash</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="AdminLTE-3.1.0/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['username']) ?></a>
                </div>
            </div>

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

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="admin.php" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Dashboard</p>
                        </a>
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

    <div class="content-wrapper">
        <div class="container py-3">
            <h1 class="text-center mb-5 ">Kelola Layanan</h1>

            <div class="card mb-5">
                <div class="card-header">
                    <h5><?= isset($_GET['edit']) ? 'Edit' : 'Tambah' ?> Layanan</h5>
                </div>
                <div class="card-body">
                    <?php
$editMode = is_array($editlayan);
?>

<form method="POST" enctype="multipart/form-data">
    <?php if ($editMode): ?>
        <input type="hidden" name="id" value="<?= htmlspecialchars($editlayan['id']) ?>">
        <input type="hidden" name="current_image_path" value="<?= htmlspecialchars($editlayan['image']) ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="nama" class="form-label">Nama Layanan</label>
        <input type="text" class="form-control" id="nama" name="nama"
                value="<?= $editMode ? htmlspecialchars($editlayan['nama']) : '' ?>" required>
    </div>

    <div class="mb-3">
        <label for="image" class="form-label">Foto Layanan</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*" <?= $editMode ? '' : 'required' ?>>
        <?php if ($editMode && !empty($editlayan['image'])): ?>
            <div class="mt-2">
                <p class="mb-1">Gambar saat ini:</p>
                <img src="<?= htmlspecialchars($editlayan['image']) ?>" alt="Preview" class="layan-table-img">
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea class="form-control" id="description" name="description" rows="3" required><?=
            $editMode ? htmlspecialchars($editlayan['description']) : '' ?></textarea>
    </div>

    <div class="mb-3">
        <label for="product_used" class="form-label">Produk Digunakan</label>
        <input type="text" class="form-control" id="product_used" name="product_used"
                value="<?= $editMode ? htmlspecialchars($editlayan['product_used']) : '' ?>" required>
    </div>

    <div class="mb-3">
        <label for="price" class="form-label">Harga</label> <input type="number" class="form-control" id="price" name="price" step="0.01"
                value="<?= $editMode ? htmlspecialchars($editlayan['price']) : '' ?>" required>
    </div>

    <button type="submit" name="<?= $editMode ? 'update_layan' : 'add_layan' ?>" class="btn btn-primary-custom">
        <?= $editMode ? 'Update' : 'Tambah' ?> Layanan
    </button>
    <?php if ($editMode): ?>
        <a href="admin-harga.php" class="btn btn-secondary">Batal</a>
    <?php endif; ?>
</form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Layanan</th>
                            <th>Foto</th>
                            <th>Deskripsi</th>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($layanan as $layan): ?>
                        <tr>
                            <td><?= htmlspecialchars($layan['nama']) ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($layan['image']) ?>" alt="Gambar Layanan" class="layan-table-img">
                            </td>
                            <td><?= nl2br(htmlspecialchars($layan['description'])) ?></td>
                            <td><?= htmlspecialchars($layan['product_used']) ?></td>
                            <td>Rp<?= number_format($layan['price'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge status-badge <?= $layan['is_active'] ? 'badge-success-custom' : 'badge-danger-custom' ?>"
                                        data-id="<?= $layan['id'] ?>">
                                    <?= $layan['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <a href="admin-harga.php?edit=<?= $layan['id'] ?>" class="btn btn-sm btn-warning-custom">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($layan['is_active']): ?>
                                    <a href="#" class="btn btn-sm btn-danger-custom" onclick="confirmDelete(<?= $layan['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="admin-harga.php?activate=<?= $layan['id'] ?>" class="btn btn-sm btn-success-custom">
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

    <script src="AdminLTE-3.1.0/plugins/jquery/jquery.min.js"></script>
    <script src="AdminLTE-3.1.0/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="AdminLTE-3.1.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="AdminLTE-3.1.0/dist/js/adminlte.js"></script>

    <script>
        function confirmDelete(layanId) {
            Swal.fire({
                title: 'Hapus Layanan?',
                text: 'Layanan akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `admin-harga.php?delete=${layanId}`;
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Tangani klik pada badge status
            document.querySelectorAll('.status-badge').forEach(badge => {
                badge.addEventListener('click', function(e) {
                    e.preventDefault();

                    const layanId = this.getAttribute('data-id');
                    const isCurrentlyActive = this.classList.contains('badge-success-custom'); // Sesuaikan dengan kelas kustom Anda
                    const newStatus = isCurrentlyActive ? 0 : 1;
                    const badgeElement = this;

                    // Gunakan SweetAlert untuk konfirmasi
                    Swal.fire({
                        title: 'Konfirmasi',
                        text: `Anda yakin ingin ${isCurrentlyActive ? 'menonaktifkan' : 'mengaktifkan'} layanan ini?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = new FormData();
                            formData.append('update_status', true);
                            formData.append('id', layanId);
                            formData.append('status', newStatus);

                            fetch(window.location.href, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if(data.success) {
                                    // Update tampilan
                                    badgeElement.classList.toggle('badge-success-custom'); // Sesuaikan kelas kustom
                                    badgeElement.classList.toggle('badge-danger-custom'); // Sesuaikan kelas kustom
                                    badgeElement.textContent = newStatus ? 'Aktif' : 'Nonaktif';

                                    Swal.fire('Berhasil', 'Status berhasil diubah', 'success');
                                } else {
                                    Swal.fire('Gagal', 'Gagal mengubah status', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error', 'Terjadi kesalahan saat mengubah status', 'error');
                            });
                        }
                    });
                });
            });

            // Tampilkan SweetAlert untuk pesan sukses dari redirect
            const urlParams = new URLSearchParams(window.location.search);
            const successType = urlParams.get('success');
            if (successType) {
                let message = '';
                let title = '';
                switch (successType) {
                    case 'added':
                        title = 'Layanan Ditambahkan!';
                        message = 'Layanan baru berhasil ditambahkan.';
                        break;
                    case 'updated':
                        title = 'Layanan Diperbarui!';
                        message = 'Informasi layanan berhasil diperbarui.';
                        break;
                    case 'deleted':
                        title = 'Layanan Dihapus!';
                        message = 'Layanan berhasil dihapus secara permanen.';
                        break;
                    case 'activated':
                        title = 'Layanan Diaktifkan!';
                        message = 'Layanan berhasil diaktifkan kembali.';
                        break;
                }
                if (message) {
                    Swal.fire(title, message, 'success');
                    // Hapus parameter 'success' dari URL agar SweetAlert tidak muncul lagi saat refresh
                    history.replaceState({}, document.title, window.location.pathname);
                }
            }
        });
    </script>

</body>
</html>
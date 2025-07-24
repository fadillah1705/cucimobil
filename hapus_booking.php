<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $id = intval($_POST['id']);

        $query = "DELETE FROM emsit_cucimobil WHERE id = $id";

        if (mysqli_query($conn, $query)) {
            header("Location: admin.php?success=deleted");
            exit();
        } else {
            echo "Gagal hapus: " . mysqli_error($conn);
        }
    } else {
        echo "ID tidak valid.";
    }
} else {
    echo "Akses tidak sah.";
}

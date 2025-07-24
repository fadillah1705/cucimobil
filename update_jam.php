<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_pelanggan'];
    $layanan = $_POST['nama_layanan'];
    $tanggal = $_POST['tanggal'];
    $jamBaru = $_POST['jam_layanan'];

    $sql = "UPDATE emsit_cucimobil 
            SET jam_layanan = '$jamBaru' 
            WHERE nama_pelanggan = '$nama' 
              AND nama_layanan = '$layanan' 
              AND tanggal = '$tanggal'";

    header('Content-Type: application/json');

    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "sukses"]);
    } else {
        echo json_encode(["status" => "gagal", "error" => mysqli_error($conn)]);
    }
}
?>

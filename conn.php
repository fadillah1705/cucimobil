<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "cucimobil";

new mysqli("localhost", "root", "", "cucimobil");


if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>

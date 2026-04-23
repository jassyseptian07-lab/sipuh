<?php
$conn = mysqli_connect("localhost", "root", "MyPass@123", "sipuh");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
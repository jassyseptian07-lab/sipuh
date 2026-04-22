<?php
include 'koneksi.php';

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM pelaku_usaha WHERE id='$id'");

header("Location: data.php");
?>
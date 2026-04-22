<?php
session_start();
include 'koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query);

if($user && password_verify($password, $user['password'])){
    $_SESSION['login'] = true;
    header("Location: index.php");
} else {
    header("Location: login.php?error=1");
}
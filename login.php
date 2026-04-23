<?php
include 'koneksi.php';
session_start();

$error = "";

/* ================= LOGIN ================= */
if(isset($_POST['login'])){

$username = mysqli_real_escape_string($conn,$_POST['username']);
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username='$username'";

$q = mysqli_query($conn,$sql);

/* 🔥 CEK ERROR QUERY (WAJIB) */
if(!$q){
    die("SQL ERROR: ".mysqli_error($conn));
}

$user = mysqli_fetch_assoc($q);

/* LOGIN SUCCESS */
if($user && password_verify($password, $user['password'])){
    $_SESSION['admin'] = true;
    $_SESSION['username'] = $username;

    header("location:index.php");
    exit;
}else{
    $error = "Username / Password salah!";
}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login Admin SIPUH</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
background:#eef1f5;
font-family:'Segoe UI';
}

.card{
border-radius:15px;
box-shadow:0 3px 10px rgba(0,0,0,0.1);
}
</style>

</head>

<body>

<div class="container mt-5" style="max-width:420px;">

<div class="card p-4">

<h4 class="text-center">🔐 Login Admin SIPUH</h4>
<p class="text-center text-muted">Silakan login untuk akses admin</p>

<?php if($error!=""){ ?>
<div class="alert alert-danger">
<?= $error ?>
</div>
<?php } ?>

<form method="POST">

<label>Username</label>
<input type="text" name="username" class="form-control mb-2" required>

<label>Password</label>
<input type="password" name="password" class="form-control mb-3" required>

<button class="btn btn-primary w-100" name="login">
Login
</button>

</form>

</div>

</div>

</body>
</html>
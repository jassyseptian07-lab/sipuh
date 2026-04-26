<?php
include 'koneksi.php';

$msg = "";
$msg_type = "";

if(isset($_POST['buat'])){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($password)){
        $msg = "Username dan password tidak boleh kosong.";
        $msg_type = "danger";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed);

        if($stmt->execute()){
            $msg = "User <strong>$username</strong> berhasil dibuat. Silakan <a href='login.php'>login</a>.";
            $msg_type = "success";
        } else {
            $msg = "Gagal: " . $stmt->error;
            $msg_type = "danger";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buat Akun Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:400px;">
    <div class="card p-4">
        <h5 class="mb-3">Buat Akun Admin</h5>

        <?php if($msg): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" class="form-control mb-2" required>
            <label>Password</label>
            <input type="password" name="password" class="form-control mb-3" required>
            <button name="buat" class="btn btn-primary w-100">Buat Akun</button>
        </form>
    </div>
</div>
</body>
</html>

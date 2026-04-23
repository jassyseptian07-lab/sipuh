<?php 
include 'koneksi.php';

// =====================
// PROSES UPDATE (AMAN)
// =====================
if(isset($_POST['update'])){
    $id         = (int) $_POST['id'];
    $nama       = $_POST['nama'];
    $alamat     = $_POST['alamat'];
    $komoditas  = $_POST['komoditas'];
    $produksi   = $_POST['produksi'];
    $pemasaran  = $_POST['tujuan_pemasaran'];
    $teknik     = $_POST['teknik_pemasaran'];
    $sertifikasi= $_POST['sertifikasi'];
    $kendala    = $_POST['kendala'];
    $no_hp      = $_POST['no_hp'];
    $kabupaten  = $_POST['kabupaten'];

    // VALIDASI SEDERHANA
    if(empty($nama) || empty($komoditas)){
        echo "<div class='alert alert-danger'>Data wajib diisi!</div>";
    } else {

        $stmt = $conn->prepare("UPDATE pelaku_usaha SET
            nama_pelaku=?,
            alamat=?,
            komoditas=?,
            produksi=?,
            tujuan_pemasaran=?,
            teknik_pemasaran=?,
            sertifikasi=?,
            kendala=?,
            no_hp=?,
            kabupaten=?
        WHERE id=?");

        $stmt->bind_param(
            "ssssssssssi",
            $nama,
            $alamat,
            $komoditas,
            $produksi,
            $pemasaran,
            $teknik,
            $sertifikasi,
            $kendala,
            $no_hp,
            $kabupaten,
            $id
        );

        if($stmt->execute()){
            header("Location: data.php?status=sukses");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Error: ".$stmt->error."</div>";
        }

        $stmt->close();
    }
}

// =====================
// AMBIL DATA EDIT
// =====================
$edit = null;
if(isset($_GET['edit'])){
    $id = (int) $_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM pelaku_usaha WHERE id='$id'");
    $edit = mysqli_fetch_assoc($q);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data SIPUH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-4">

<h2>📋 Data Pelaku Usaha</h2>

<!-- NOTIFIKASI -->
<?php if(isset($_GET['status']) && $_GET['status']=='sukses'){ ?>
<div class="alert alert-success">✅ Data berhasil diupdate</div>
<?php } ?>

<?php if(isset($_GET['status']) && $_GET['status']=='import'){ ?>
<div class="alert alert-success">
    ✅ Import selesai: <strong><?= (int)($_GET['ok']??0) ?></strong> data berhasil dimasukkan,
    <?= (int)($_GET['skip']??0) ?> baris di-skip,
    <?= (int)($_GET['fail']??0) ?> gagal.
</div>
<?php } ?>

<!-- ===================== -->
<!-- FORM EDIT -->
<!-- ===================== -->
<?php if($edit){ ?>
<div class="card p-3 mb-3">
    <b>Edit Data</b>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit['id'] ?>">

        <div class="row">
            <div class="col-md-3 mb-2"><input name="nama" class="form-control" placeholder="Nama" value="<?= $edit['nama_pelaku'] ?? '' ?>"></div>
            <div class="col-md-3 mb-2"><input name="alamat" class="form-control" placeholder="Alamat" value="<?= $edit['alamat'] ?? '' ?>"></div>
            <div class="col-md-3 mb-2"><input name="komoditas" class="form-control" placeholder="Komoditas" value="<?= $edit['komoditas'] ?? '' ?>"></div>
            <div class="col-md-3 mb-2"><input name="produksi" class="form-control" placeholder="Produksi" value="<?= $edit['produksi'] ?? '' ?>"></div>

            <div class="col-md-3 mb-2"><input name="tujuan_pemasaran" class="form-control" placeholder="Tujuan Pemasaran" value="<?= $edit['tujuan_pemasaran'] ?? '' ?>"></div>
            <div class="col-md-3 mb-2"><input name="teknik_pemasaran" class="form-control" placeholder="Teknik Pemasaran" value="<?= $edit['teknik_pemasaran'] ?? '' ?>"></div>

            <div class="col-md-3 mb-2"><input name="sertifikasi" class="form-control" placeholder="Sertifikasi" value="<?= $edit['sertifikasi'] ?? '' ?>"></div>
            <div class="col-md-3 mb-2"><input name="kendala" class="form-control" placeholder="Kendala" value="<?= $edit['kendala'] ?? '' ?>"></div>

            <div class="col-md-3 mb-2"><input name="no_hp" class="form-control" placeholder="No HP" value="<?= $edit['no_hp'] ?? '' ?>"></div>
            <div class="col-md-3 mb-2"><input name="kabupaten" class="form-control" placeholder="Kabupaten" value="<?= $edit['kabupaten'] ?? '' ?>"></div>
        </div>

        <button name="update" class="btn btn-primary">Update</button>
        <a href="data.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?php } ?>

<!-- ===================== -->
<!-- TABEL DATA -->
<!-- ===================== -->
<table class="table table-bordered">
<tr>
<th>No</th>
<th>Nama</th>
<th>Alamat</th>
<th>Komoditas</th>
<th>Produksi</th>
<th>Pemasaran</th>
<th>Teknik</th>
<th>Sertifikasi</th>
<th>Kendala</th>
<th>Kontak</th>
<th>Kabupaten</th>
<th>Aksi</th>
</tr>

<?php include 'auth.php'; ?>
<?php
$query = mysqli_query($conn, "SELECT * FROM pelaku_usaha");
$no = 1;

while($row = mysqli_fetch_assoc($query)){
?>

<tr>
<td><?= $no++; ?></td>
<td><?= $row['nama_pelaku']; ?></td>
<td><?= $row['alamat']; ?></td>
<td><?= $row['komoditas']; ?></td>
<td><?= $row['produksi']; ?></td>
<td><?= $row['tujuan_pemasaran']; ?></td>
<td><?= $row['teknik_pemasaran']; ?></td>
<td><?= $row['sertifikasi']; ?></td>
<td><?= $row['kendala']; ?></td>
<td><?= $row['no_hp']; ?></td>
<td><?= $row['kabupaten']; ?></td>
<td>
    <a href="data.php?edit=<?= $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
    <a href="hapus.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data?')">Hapus</a>
</td>
</tr>

<?php } ?>
</table>

<a href="index.php" class="btn btn-secondary">⬅ Kembali</a>

</div>
</body>
</html>
<?php 
include 'auth.php';
include 'koneksi.php';

// =====================
// DATA KABUPATEN MANUAL
// =====================
$kabupaten_manual = [
"Bangkalan","Banyuwangi","Blitar","Bojonegoro","Bondowoso","Gresik",
"Jember","Jombang","Kediri","Lamongan","Lumajang","Madiun","Magetan",
"Malang","Mojokerto","Nganjuk","Ngawi","Pacitan","Pamekasan",
"Pasuruan","Ponorogo","Probolinggo","Sampang","Sidoarjo","Situbondo",
"Sumenep","Trenggalek","Tuban","Tulungagung",
"Kota Surabaya","Kota Malang","Kota Kediri","Kota Blitar",
"Kota Probolinggo","Kota Pasuruan","Kota Madiun",
"Kota Mojokerto","Kota Batu"
];

// =====================
// PROSES SIMPAN
// =====================
if(isset($_POST['submit'])){
    $nama       = trim($_POST['nama']);
    $alamat     = trim($_POST['alamat']);
    $komoditas  = trim($_POST['komoditas']);
    $produksi   = trim($_POST['produksi']);
    $tujuan     = trim($_POST['tujuan_pemasaran']);
    $teknik     = trim($_POST['teknik_pemasaran']);
    $sertifikasi= trim($_POST['sertifikasi']);
    $kendala    = trim($_POST['kendala']);
    $no_hp      = trim($_POST['no_hp']);
    $kabupaten  = trim($_POST['kabupaten']);

    // =====================
    // NORMALISASI NO HP
    // =====================
    $no_hp = str_replace([' ', '-', '+'], '', $no_hp);

    // =====================
    // VALIDASI
    // =====================
    if(empty($nama) || empty($komoditas) || empty($kabupaten)){
        echo "<div class='alert alert-danger'>Nama, Komoditas, dan Kabupaten wajib diisi!</div>";
    }
    elseif(!empty($no_hp) && !ctype_digit($no_hp)){
        echo "<div class='alert alert-danger'>No HP harus berupa angka!</div>";
    }
    elseif(!empty($no_hp) && (strlen($no_hp) < 10 || strlen($no_hp) > 13)){
        echo "<div class='alert alert-danger'>No HP harus 10-13 digit!</div>";
    }
    else {

        $stmt = $conn->prepare("INSERT INTO pelaku_usaha 
        (nama_pelaku, alamat, komoditas, produksi, tujuan_pemasaran, teknik_pemasaran, sertifikasi, kendala, no_hp, kabupaten)
        VALUES (?,?,?,?,?,?,?,?,?,?)");

        $stmt->bind_param(
            "ssssssssss",
            $nama,$alamat,$komoditas,$produksi,$tujuan,
            $teknik,$sertifikasi,$kendala,$no_hp,$kabupaten
        );

        if($stmt->execute()){
            header("Location: data.php?status=tambah");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Error: ".$stmt->error."</div>";
        }

        $stmt->close();
    }
}

// untuk selected dropdown
$selected_kabupaten = $_POST['kabupaten'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Data SIPUH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script>
function validasiForm(){
    let nama = document.forms["formku"]["nama"].value;
    let komoditas = document.forms["formku"]["komoditas"].value;
    let nohp = document.forms["formku"]["no_hp"].value;

    if(nama == "" || komoditas == ""){
        alert("Nama dan Komoditas wajib diisi!");
        return false;
    }

    if(nohp != "" && isNaN(nohp)){
        alert("No HP harus angka!");
        return false;
    }

    return true;
}
</script>
</head>

<body>
<div class="container mt-4">
    <h2>➕ Tambah Data SIPUH</h2>

    <form method="POST" name="formku" onsubmit="return validasiForm()">

        <input type="text" name="nama" class="form-control mb-2" placeholder="Nama" required>

        <input type="text" name="alamat" class="form-control mb-2" placeholder="Alamat">

        <textarea name="komoditas" class="form-control mb-2" placeholder="Komoditas" required></textarea>

        <input type="text" name="produksi" class="form-control mb-2" placeholder="Produksi">

        <input type="text" name="tujuan_pemasaran" class="form-control mb-2" placeholder="Tujuan Pemasaran">

        <input type="text" name="teknik_pemasaran" class="form-control mb-2" placeholder="Teknik Pemasaran">

        <input type="text" name="sertifikasi" class="form-control mb-2" placeholder="Sertifikasi">

        <input type="text" name="kendala" class="form-control mb-2" placeholder="Kendala">

        <input type="text" name="no_hp" class="form-control mb-2" placeholder="No HP">

        <!-- DROPDOWN KABUPATEN -->
        <select name="kabupaten" class="form-control mb-3" required>
            <option value="">-- Pilih Kabupaten --</option>
            <?php foreach($kabupaten_manual as $k){ ?>
                <option value="<?= $k; ?>" <?= ($selected_kabupaten == $k ? 'selected' : '') ?>>
                    <?= $k; ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit" name="submit" class="btn btn-success">Simpan</button>
        <a class="btn btn-secondary">Kembali</a>

    </form>
</div>
</body>
</html>
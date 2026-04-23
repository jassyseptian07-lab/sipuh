<?php
include 'auth.php';
include 'koneksi.php';
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

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

function matchKabupaten(string $raw, array $list): ?string {
    $norm = ucwords(strtolower(trim($raw)));
    foreach ($list as $k) {
        if (strcasecmp($norm, $k) === 0) return $k;
    }
    return null;
}

$alertMsg  = '';
$alertType = '';

if (isset($_POST['submit'])) {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $alertMsg  = 'File gagal diupload atau tidak dipilih.';
        $alertType = 'danger';
    } else {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'], true)) {
            $alertMsg  = 'Format file harus .xlsx atau .xls!';
            $alertType = 'danger';
        } elseif ($_FILES['file']['size'] > 5 * 1024 * 1024) {
            $alertMsg  = 'Ukuran file maksimal 5 MB!';
            $alertType = 'danger';
        } else {
            try {
                $spreadsheet = IOFactory::load($_FILES['file']['tmp_name']);
                $sheet       = $spreadsheet->getActiveSheet();
                $maxRow      = $sheet->getHighestRow();

                // Baca kabupaten dari B3
                $kabRaw = trim((string) $sheet->getCell('B3')->getValue());
                $kabupaten = matchKabupaten($kabRaw, $kabupaten_manual);

                if ($kabupaten === null) {
                    $alertMsg  = "Kabupaten '<b>" . htmlspecialchars($kabRaw) . "</b>' di sel B3 tidak dikenali. Periksa format file Excel.";
                    $alertType = 'danger';
                } else {
                    // Cari baris header (kolom A berisi "No")
                    $headerRow = null;
                    for ($r = 1; $r <= $maxRow; $r++) {
                        $val = trim((string) $sheet->getCell("A$r")->getValue());
                        if (strtolower($val) === 'no') {
                            $headerRow = $r;
                            break;
                        }
                    }

                    if ($headerRow === null) {
                        $alertMsg  = "Header 'No' tidak ditemukan di kolom A. Periksa format file Excel.";
                        $alertType = 'danger';
                    } else {
                        $dataStartRow = $headerRow + 2;
                        $inserted = 0;
                        $skipped  = 0;
                        $failed   = 0;

                        $stmt = $conn->prepare(
                            "INSERT INTO pelaku_usaha
                            (nama_pelaku, alamat, komoditas, produksi, tujuan_pemasaran,
                             teknik_pemasaran, sertifikasi, kendala, no_hp, kabupaten)
                            VALUES (?,?,?,?,?,?,?,?,?,?)"
                        );

                        for ($r = $dataStartRow; $r <= $maxRow; $r++) {
                            $nama      = trim((string) $sheet->getCell("B$r")->getValue());
                            $komoditas = trim((string) $sheet->getCell("D$r")->getValue());

                            if ($nama === '' || $komoditas === '') {
                                $skipped++;
                                continue;
                            }

                            $alamat   = trim((string) $sheet->getCell("C$r")->getValue());
                            $produksi = trim((string) $sheet->getCell("E$r")->getValue());
                            $tujuan   = trim((string) $sheet->getCell("F$r")->getValue());
                            $teknik   = trim((string) $sheet->getCell("G$r")->getValue());
                            $sertif   = trim((string) $sheet->getCell("H$r")->getValue());
                            $kendala  = trim((string) $sheet->getCell("I$r")->getValue());
                            $no_hp    = preg_replace('/[\s\-\+\*]/', '', (string) $sheet->getCell("J$r")->getValue());

                            $stmt->bind_param(
                                "ssssssssss",
                                $nama, $alamat, $komoditas, $produksi, $tujuan,
                                $teknik, $sertif, $kendala, $no_hp, $kabupaten
                            );

                            if ($stmt->execute()) {
                                $inserted++;
                            } else {
                                $failed++;
                            }
                        }

                        $stmt->close();
                        header("Location: data.php?status=import&ok=$inserted&skip=$skipped&fail=$failed");
                        exit;
                    }
                }
            } catch (\Exception $e) {
                $alertMsg  = 'Gagal membaca file: ' . htmlspecialchars($e->getMessage());
                $alertType = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Data SIPUH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4" style="max-width:480px;">
    <h2>Import Data SIPUH</h2>
    <p class="text-muted">Upload file Excel — kabupaten dibaca otomatis dari sel B3.</p>

    <?php if ($alertMsg): ?>
    <div class="alert alert-<?= $alertType ?>"><?= $alertMsg ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" name="formku" onsubmit="return validasiForm()">
        <label class="form-label fw-semibold">File Excel (.xlsx / .xls)</label>
        <input type="file" name="file" class="form-control mb-4" accept=".xlsx,.xls" required>

        <button type="submit" name="submit" class="btn btn-success">Import</button>
        <a href="index.php" class="btn btn-secondary ms-2">Kembali</a>
    </form>
</div>

<script>
function validasiForm() {
    if (document.forms["formku"]["file"].value === "") {
        alert("Pilih file Excel terlebih dahulu!");
        return false;
    }
    return true;
}
</script>
</body>
</html>

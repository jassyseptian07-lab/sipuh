<?php
include 'koneksi.php';
header('Content-Type: application/json');

$kab=$_GET['kabupaten']??'';
$kom=$_GET['komoditas']??'';

$where="WHERE 1=1";

if($kab!=''){
$kab=mysqli_real_escape_string($conn,$kab);
$where.=" AND kabupaten='$kab'";
}

if($kom!=''){
$kom=mysqli_real_escape_string($conn,$kom);
$where.=" AND komoditas='$kom'";
}

/* KPI */
$total=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) jml FROM pelaku_usaha $where
"))['jml']??0;

$kab_total=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(DISTINCT kabupaten) jml FROM pelaku_usaha $where
"))['jml']??0;

/* FIX SERTIFIKASI */
$sert=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) jml FROM pelaku_usaha $where
AND sertifikasi IS NOT NULL
AND sertifikasi!=''
AND LOWER(sertifikasi)!='tidak ada'
"))['jml']??0;

/* PIE */
$pie=[];
$q=mysqli_query($conn,"
SELECT komoditas,COUNT(*) jml FROM pelaku_usaha $where GROUP BY komoditas
");
while($r=mysqli_fetch_assoc($q))$pie[]=$r;

/* BAR */
$bar=[];
$q=mysqli_query($conn,"
SELECT kabupaten,COUNT(*) jml FROM pelaku_usaha $where GROUP BY kabupaten
");
while($r=mysqli_fetch_assoc($q))$bar[]=$r;

/* TOP5 */
$top5=[];
$q=mysqli_query($conn,"
SELECT komoditas,SUM(produksi) jml FROM pelaku_usaha $where
GROUP BY komoditas ORDER BY jml DESC LIMIT 5
");
while($r=mysqli_fetch_assoc($q))$top5[]=$r;

/* TABLE */
$table=[];
$q=mysqli_query($conn,"SELECT * FROM pelaku_usaha $where ORDER BY id DESC");
while($r=mysqli_fetch_assoc($q))$table[]=$r;

echo json_encode([
"total"=>$total,
"kabupaten"=>$kab_total,
"sertifikasi"=>$sert,
"pie"=>$pie,
"bar"=>$bar,
"top5"=>$top5,
"table"=>$table
]);
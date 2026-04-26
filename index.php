<?php
include 'koneksi.php';
session_start();

$isAdmin = isset($_SESSION['admin']);

/* ================= KPI ================= */
$total=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) jml FROM pelaku_usaha"))['jml']??0;

$kab=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT kabupaten) jml FROM pelaku_usaha"))['jml']??0;

$sert=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) jml FROM pelaku_usaha
WHERE LOWER(TRIM(sertifikasi)) = 'ada'
"))['jml']??0;

/* ================= DATA ================= */
$dataTable = mysqli_fetch_all(
mysqli_query($conn,"SELECT * FROM pelaku_usaha ORDER BY id DESC"),
MYSQLI_ASSOC
);
?>

<!DOCTYPE html>
<html>
<head>
<title>SIPUH Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{background:#eef1f5;font-family:'Segoe UI';}

.sidebar{
width:230px;height:100vh;position:fixed;
background:#1f3c88;color:#fff;padding:20px;
}

.sidebar a{
display:block;color:#fff;text-decoration:none;
padding:10px;border-radius:8px;
}

.sidebar a:hover{background:#3b5bdb;}

.content{margin-left:240px;padding:20px;}

.card{border-radius:15px;box-shadow:0 3px 8px rgba(0,0,0,0.1);}

.kpi{font-size:28px;font-weight:bold;}
</style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

<h4>📊 SIPUH</h4>
<hr>

<a href="index.php">🏠 Dashboard</a>

<?php if(!$isAdmin){ ?>
<a href="login.php">🔐 Login Admin</a>
<?php } ?>

<?php if($isAdmin){ ?>
<a href="data.php">📋 Data</a>
<a href="tambah.php">➕ Tambah</a>
<a href="index.php?logout=1">🚪 Logout</a>
<?php } ?>

</div>

<!-- CONTENT -->
<div class="content">

<h4>Dashboard SIPUH</h4>

<!-- KPI -->
<div class="row">

<div class="col-md-4"><div class="card p-3">Total <div class="kpi"><?= $total ?></div></div></div>
<div class="col-md-4"><div class="card p-3">Kabupaten <div class="kpi"><?= $kab ?></div></div></div>
<div class="col-md-4"><div class="card p-3">Sertifikasi <div class="kpi"><?= $sert ?></div></div></div>

</div>

<!-- ================= FILTER (PINDAH KE ATAS GRAFIK) ================= -->
<div class="card p-3 mt-4">
<h6>🔎 Filter Data Kabupaten & Komoditas</h6>

<div class="row">

<div class="col-md-6">
<select id="filterKabupaten" class="form-select">
<option value="">Semua Kabupaten</option>
<?php
$k=mysqli_query($conn,"SELECT DISTINCT kabupaten FROM pelaku_usaha ORDER BY kabupaten");
while($r=mysqli_fetch_assoc($k)){
echo "<option value='{$r['kabupaten']}'>{$r['kabupaten']}</option>";
}
?>
</select>
</div>

<div class="col-md-6">
<select id="filterKomoditas" class="form-select">
<option value="">Semua Komoditas</option>
</select>
</div>

</div>
</div>

<!-- ================= GRAFIK ================= -->
<div class="row mt-3">

<div class="col-md-6 card p-3">
<h6>Komoditas</h6>
<canvas id="pie"></canvas>
</div>

<div class="col-md-6 card p-3">
<h6>Kabupaten</h6>
<canvas id="bar"></canvas>
</div>

</div>

<div class="row mt-3">

<div class="col-md-12 card p-3">
<h6>Top 5 Produksi</h6>
<canvas id="top5"></canvas>
</div>

</div>

<!-- ================= TABLE ================= -->
<div class="card p-3 mt-4">
<h6>Data Pelaku Usaha</h6>

<div class="table-responsive">
<table class="table table-bordered table-striped">

<thead>
<tr style="background:#1f3c88;color:#fff;">
<th>No</th>
<th>Nama</th>
<th>Kabupaten</th>
<th>Alamat</th>
<th>Komoditas</th>
<th>Produksi</th>
<th>Tujuan</th>
<th>Teknik</th>
<th>Sertifikasi</th>
<th>No HP</th>
</tr>
</thead>

<tbody id="tabelData"></tbody>

</table>
</div>

</div>

</div>

<!-- ================= SCRIPT ================= -->
<script>

function colors(n){
let c=[];
for(let i=0;i<n;i++){
c.push(`hsl(${i*(360/n)},70%,55%)`);
}
return c;
}

function normalizeKomoditas(k){
var v=(k||'').trim().toLowerCase().replace(/\s+/g,' ');
var title=function(s){return s.replace(/\b\w/g,function(c){return c.toUpperCase();});};
var mc=v.match(/^ca[bh]e?a?i?\s*(.*)$/);
if(mc) return ('Cabai'+(mc[1]?' '+title(mc[1]):'')).trim();
var mb=v.match(/^bawang\s*(.*)$/);
if(mb) return ('Bawang'+(mb[1]?' '+title(mb[1]):'')).trim();
var mf=v.match(/^buah\s+(.+)$/);
if(mf) return 'Buah '+title(mf[1]);
return title(v);
}

document.addEventListener("DOMContentLoaded", function(){

let dataAsli = <?= json_encode($dataTable) ?>;

let komSorted=Array.from(new Set(dataAsli.map(d=>normalizeKomoditas(d.komoditas)))).filter(Boolean).sort();
let selKom=document.getElementById("filterKomoditas");
komSorted.forEach(function(k){let o=document.createElement("option");o.value=k;o.textContent=k;selKom.appendChild(o);});

let pieChart, barChart, topChart;

/* ================= TABLE ================= */
function renderTable(data){
let html="";
let no=1;

if(data.length===0){
document.getElementById("tabelData").innerHTML=`<tr><td colspan="10" class="text-center">Data tidak ditemukan</td></tr>`;
return;
}

data.forEach(d=>{
html+=`
<tr>
<td>${no++}</td>
<td>${d.nama_pelaku}</td>
<td>${d.kabupaten}</td>
<td>${d.alamat}</td>
<td>${d.komoditas}</td>
<td>${d.produksi}</td>
<td>${d.tujuan_pemasaran}</td>
<td>${d.teknik_pemasaran}</td>
<td>${d.sertifikasi}</td>
<td>${d.no_hp}</td>
</tr>`;
});

document.getElementById("tabelData").innerHTML=html;
}

/* ================= CHART ================= */
const KOMODITAS_WHITELIST=['Cabai Besar','Cabai Rawit','Bawang Merah','Buah Pisang','Buah Jeruk','Buah Durian'];

function buildChart(data){

let chartData=data.filter(d=>KOMODITAS_WHITELIST.indexOf(normalizeKomoditas(d.komoditas))!==-1);

let kom={}, kab={}, top={};

chartData.forEach(d=>{
var normKom=normalizeKomoditas(d.komoditas);
kom[normKom]=(kom[normKom]||0)+1;
kab[d.kabupaten]=(kab[d.kabupaten]||0)+1;
top[normKom]=(top[normKom]||0)+parseInt(d.produksi||0);
});

/* PIE */
if(pieChart) pieChart.destroy();
pieChart=new Chart(document.getElementById('pie'),{
type:'pie',
data:{
labels:Object.keys(kom),
datasets:[{
data:Object.values(kom),
backgroundColor:colors(Object.keys(kom).length)
}]
}
});

/* BAR */
if(barChart) barChart.destroy();
barChart=new Chart(document.getElementById('bar'),{
type:'bar',
data:{
labels:Object.keys(kab),
datasets:[{
data:Object.values(kab),
backgroundColor:colors(Object.keys(kab).length)
}]
},
options:{plugins:{legend:{display:false}}}
});

/* TOP 5 */
let topArr=Object.entries(top).sort((a,b)=>b[1]-a[1]).slice(0,5);

if(topChart) topChart.destroy();
topChart=new Chart(document.getElementById('top5'),{
type:'bar',
data:{
labels:topArr.map(x=>x[0]),
datasets:[{
data:topArr.map(x=>x[1]),
backgroundColor:colors(topArr.length)
}]
},
options:{plugins:{legend:{display:false}}}
});

}

/* ================= FILTER ================= */
function filterData(){

let kab=document.getElementById("filterKabupaten").value;
let kom=document.getElementById("filterKomoditas").value;

let hasil=dataAsli.filter(d=>{
return (kab==""||d.kabupaten==kab) &&
(kom==""||normalizeKomoditas(d.komoditas)==kom);
});

renderTable(hasil);
buildChart(hasil);
}

document.getElementById("filterKabupaten").addEventListener("change",filterData);
document.getElementById("filterKomoditas").addEventListener("change",filterData);

/* INIT */
renderTable(dataAsli);
buildChart(dataAsli);

});

</script>

</body>
</html>
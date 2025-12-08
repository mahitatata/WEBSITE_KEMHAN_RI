<<<<<<< HEAD
<?php
include "koneksi.php";
session_start();

if (!isset($_SESSION['pegawai_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: dashboardpegawai.php");
    exit;
}


include 'koneksi.php';

$backUrl = 'dashboardpegawai.php';

if (isset($_GET['from'])) {
    if ($_GET['from'] === 'lihat artikel') {
        $backUrl = 'lihatartikel.php';
    } elseif ($_GET['from'] === 'artikel') {
        $backUrl = 'artikel.php';
    } elseif ($_GET['from'] === 'review') {
        $backUrl = 'review.php';
    }
}

$id = intval($_GET['id']);
$pegawai_id = $_SESSION['pegawai_id'];

$stmt = $conn->prepare("SELECT * FROM artikel WHERE id = ? AND pegawai_id = ?");
$stmt->bind_param("ii", $id, $pegawai_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<h3>Artikel tidak ditemukan</h3>";
    exit;
}

$artikel = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($artikel['judul']) ?></title>
<link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
  font-family: 'Inter', sans-serif;
  background: #f5f6fa;
  color: #333;
  margin: 0;
}
header {
  background-color: #8B0000;
  color: white;
  padding: 16px 32px;
  font-size: 20px;
  font-weight: bold;
}
.container {
  max-width: 800px;
  margin: 50px auto;
  background: white;
  border-radius: 15px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  padding: 40px 50px;
}
h1 {
  color: #8B0000;
  font-size: 28px;
  margin-bottom: 10px;
}
.meta {
  font-size: 14px;
  color: #666;
  margin-bottom: 20px;
}
.konten {
  font-size: 16px;
  line-height: 1.7;
}
.judul-bar{
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: -60px; 
    margin-left: -8px;  
    margin-bottom: 40px; 
}

.btn-back-shopee {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 44px;
    height: 44px;
    background: #ffffff;
    border-radius: 50%;
    border: 1px solid #e5e5e5;
    text-decoration: none;
    cursor: pointer;
    transition: 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    position: relative;
    width: 40px;
    height: 40px;
}

.btn-back-shopee::before {
    content: "";
    position: absolute;
    width: 58px;     
    height: 58px;
    border-radius: 10%;
    background: rgba(0,0,0,0.05);  
    z-index: -1;    
}

.btn-back-shopee:hover {
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.4);
    transform: scale(1.05);
}

.arrow-shopee {
    width: 22px;
    stroke: #333;
    stroke-width: 3.2; 
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.gambar-artikel {
  text-align: center;
  margin-bottom: 25px;
}

.gambar-artikel img {
  width: 100%;
  max-height: 380px; 
  object-fit: cover;
  border-radius: 0;
  margin:20px 0;
  border:3px solid #7c0000ff;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<header>📄 Lihat Artikel</header>
<div class="container">
<div class="judul-bar">
    <a href="<?= $backUrl ?>" class="btn-back-shopee">
        <svg class="arrow-shopee" viewBox="0 0 24 24">
            <path d="M15 6l-6 6 6 6" />
        </svg>
    </a>
</div>

  <h1><?= htmlspecialchars($artikel['judul']) ?></h1>
  <div class="meta">
    <strong>Kategori:</strong> <?= htmlspecialchars($artikel['kategori']) ?> |
    <strong>Tipe:</strong> <?= htmlspecialchars(ucfirst($artikel['tipe'])) ?> |
    <strong>Status:</strong> <?= htmlspecialchars($artikel['status']) ?> |
    <strong>Tanggal:</strong> <?= htmlspecialchars($artikel['created_at']) ?>
  </div>

  <?php if (!empty($artikel['gambar'])): ?>
    <div class="gambar-artikel">
      <img src="uploads/<?= htmlspecialchars($artikel['gambar']) ?>" alt="Gambar Artikel" class="gambar-preview">
    </div>
  <?php endif; ?>

 <?php if (!empty($artikel['pdf'])): ?>
    <div style="margin: 20px 0;">
        <strong>📎 Lampiran PDF:</strong><br><br>
        <a href="lihatpdf.php?file=<?= urlencode($artikel['pdf']) ?>"
           style="display:inline-block; background:#8B0000; color:white; padding:10px 18px; border-radius:8px; text-decoration:none; font-weight:600;">
            📄 Buka File PDF
        </a>
    </div>
<?php endif; ?>

  <div class="konten">
    <?= nl2br(htmlspecialchars($artikel['isi_artikel'])) ?>
  </div>
</div>

</body>
</html>
=======
<?php
include "koneksi.php";
session_start();

if (!isset($_SESSION['pegawai_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: dashboardpegawai.php");
    exit;
}


include 'koneksi.php';

$backUrl = 'dashboardpegawai.php';

if (isset($_GET['from'])) {
    if ($_GET['from'] === 'lihat artikel') {
        $backUrl = 'lihatartikel.php';
    } elseif ($_GET['from'] === 'artikel') {
        $backUrl = 'artikel.php';
    } elseif ($_GET['from'] === 'review') {
        $backUrl = 'review.php';
    }
}

$id = intval($_GET['id']);
$pegawai_id = $_SESSION['pegawai_id'];

$stmt = $conn->prepare("SELECT * FROM artikel WHERE id = ? AND pegawai_id = ?");
$stmt->bind_param("ii", $id, $pegawai_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<h3>Artikel tidak ditemukan</h3>";
    exit;
}

$artikel = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($artikel['judul']) ?></title>
<link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
  font-family: 'Inter', sans-serif;
  background: #f5f6fa;
  color: #333;
  margin: 0;
}
header {
  background-color: #8B0000;
  color: white;
  padding: 16px 32px;
  font-size: 20px;
  font-weight: bold;
}
.container {
  max-width: 800px;
  margin: 50px auto;
  background: white;
  border-radius: 15px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  padding: 40px 50px;
}
h1 {
  color: #8B0000;
  font-size: 28px;
  margin-bottom: 10px;
}
.meta {
  font-size: 14px;
  color: #666;
  margin-bottom: 20px;
}
.konten {
  font-size: 16px;
  line-height: 1.7;
}
.judul-bar{
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: -60px; 
    margin-left: -8px;  
    margin-bottom: 40px; 
}

.btn-back-shopee {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 44px;
    height: 44px;
    background: #ffffff;
    border-radius: 50%;
    border: 1px solid #e5e5e5;
    text-decoration: none;
    cursor: pointer;
    transition: 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    position: relative;
    width: 40px;
    height: 40px;
}

.btn-back-shopee::before {
    content: "";
    position: absolute;
    width: 58px;     
    height: 58px;
    border-radius: 10%;
    background: rgba(0,0,0,0.05);  
    z-index: -1;    
}

.btn-back-shopee:hover {
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.4);
    transform: scale(1.05);
}

.arrow-shopee {
    width: 22px;
    stroke: #333;
    stroke-width: 3.2; 
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.gambar-artikel {
  text-align: center;
  margin-bottom: 25px;
}

.gambar-artikel img {
  width: 100%;
  max-height: 380px; 
  object-fit: cover;
  border-radius: 0;
  margin:20px 0;
  border:3px solid #7c0000ff;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<header>📄 Lihat Artikel</header>
<div class="container">
<div class="judul-bar">
    <a href="<?= $backUrl ?>" class="btn-back-shopee">
        <svg class="arrow-shopee" viewBox="0 0 24 24">
            <path d="M15 6l-6 6 6 6" />
        </svg>
    </a>
</div>

  <h1><?= htmlspecialchars($artikel['judul']) ?></h1>
  <div class="meta">
    <strong>Kategori:</strong> <?= htmlspecialchars($artikel['kategori']) ?> |
    <strong>Tipe:</strong> <?= htmlspecialchars(ucfirst($artikel['tipe'])) ?> |
    <strong>Status:</strong> <?= htmlspecialchars($artikel['status']) ?> |
    <strong>Tanggal:</strong> <?= htmlspecialchars($artikel['created_at']) ?>
  </div>

  <?php if (!empty($artikel['gambar'])): ?>
    <div class="gambar-artikel">
      <img src="uploads/<?= htmlspecialchars($artikel['gambar']) ?>" alt="Gambar Artikel" class="gambar-preview">
    </div>
  <?php endif; ?>

 <?php if (!empty($artikel['pdf'])): ?>
    <div style="margin: 20px 0;">
        <strong>📎 Lampiran PDF:</strong><br><br>
        <a href="lihatpdf.php?file=<?= urlencode($artikel['pdf']) ?>"
           style="display:inline-block; background:#8B0000; color:white; padding:10px 18px; border-radius:8px; text-decoration:none; font-weight:600;">
            📄 Buka File PDF
        </a>
    </div>
<?php endif; ?>

  <div class="konten">
    <?= nl2br(htmlspecialchars($artikel['isi_artikel'])) ?>
  </div>
</div>

</body>
</html>
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

<<<<<<< HEAD
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "koneksi.php";

// Ambil data pegawai dari session (misal username atau id)
$pegawai_id = $_SESSION['pegawai_id'] ?? null;
$pegawai_nama = "Pegawai";
if ($pegawai_id) {
    $result = $conn->query("SELECT nama FROM regsitrasi WHERE id = $pegawai_id LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pegawai_nama = htmlspecialchars($row['nama']);
    }
}
// Ambil kategori & search keyword dari URL
$kategori = $_GET['kategori'] ?? 'SEMUA';
$keyword = trim($_GET['search'] ?? '');

// Base SQL
$sql = "SELECT a.*, a.gambar, r.nama AS penulis, COUNT(k.id) AS komentar
        FROM artikel a
        LEFT JOIN regsitrasi r ON a.pegawai_id = r.id
        LEFT JOIN komentar k ON k.artikel_id = a.id
        WHERE a.status='publish' AND a.arsip = 0";

$params = [];
$types = "";

// Filter kategori
if ($kategori !== "SEMUA") {
    $sql .= " AND a.kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

// Filter search keyword
if (!empty($keyword)) {
    $sql .= " AND (a.judul LIKE ? OR a.isi_artikel LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
    $types .= "ss";
}

$sql .= " GROUP BY a.id ORDER BY komentar DESC, a.created_at DESC";

$stmt = $conn->prepare($sql);

// Bind param kalau ada
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Artikel Pegawai</title>
  <link rel="icon" type="image/png" href="logo kemhan 1.png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    /* RESET DASAR */
* {
  margin: 0;
  box-sizing: border-box;
}

html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

body {
    font-family: 'Inter', sans-serif;
}

main {
  flex: 1 0 auto;
}

/* Container untuk konten utama */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* No Article */
.no-article {
  grid-column: 1 / -1;
  display: flex;
  text-align: center;       
  font-size: 1.2rem;       
  color: #666;             
  margin: 60px 0;           
  display: flex;            
  justify-content: center;  
  align-items: center;      
  min-height: 200px;  
  font-weight: 500;     
}

/* Article Grid */
    .articles {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      padding: 20px 40px;
      align-items: start;
      margin-bottom: 80px;
    }

.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 40px;
    margin-bottom: 100px;
}

   .article-card {
      background: #fff;
      border: 2px solid #cfcfcf; /* outline lebih tebal */
      border-radius: 10px;
      padding: 16px;
      box-sizing: border-box;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      height: 100%;
      overflow: hidden;
      min-height: 280px;
      position: relative;
    }

    .article-card .bookmark-internal {
    position: absolute;
    top: 10px;       /* Sedikit di bawah atas kartu */
    left: 50%;       /* Center horizontal */
    transform: translateX(-50%); /* Center presisi */
    background-color: #d40000;
    color: white;
    font-weight: 700;
    font-size: 12px;
    padding: 6px 20px; /* Lebar memanjang */
    border-radius: 4px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    z-index: 10;
    letter-spacing: 0.5px;
    text-align: center;
    white-space: nowrap;
    border: 2px solid yellow;
}

.article-card:hover {
  transform: translateY(-4px);
  border-color: #a30202;
  box-shadow: 0 6px 16px rgba(163, 2, 2, 0.15);
  background-color: #fff5f5; /* sedikit merah muda */
}

.article-card h3,
.article-title {
  font-size: 18px;
  font-weight: 700;
  color: #a30202;
  margin: 0 0 8px 0;
  line-height: 1.3;
  transition: color 0.3s ease;
}

.article-card {
  background: #fff;
  border: 2px solid #cfcfcf;
  border-radius: 10px;
  padding: 16px;
  box-sizing: border-box;
  transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  height: 100%;
  overflow: hidden;
  min-height: 280px;
}

.article-card p {
  font-size: 14px;
  color: #333;
  line-height: 1.5;
  flex-grow: 1;
}

.article-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 12px;
}

.article-tags .tag {
  display: inline-block;
  background: #f4f4f4;
  color: #a30202;
  border-radius: 8px;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: bold;
  text-decoration: none;
}

.article-tags .tag:hover {
  background: #a30202;
  color: white;
}

.article-comments .comment-link {
    text-decoration: none;
    color: #555;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    transition: color 0.3s ease; 
}

.article-comments .comment-link:hover {
    color: #a30202;
    transform: none;
}

.article-comments .fa-comment {
    margin-right: 5px;
    font-size: 1rem; 
    transition: color 0.3s ease;
}

.author-credit {
  font-size: 13px;
  color: #a30202;
  margin-top: 8px;
}
.article-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  color: #a30202;
  margin-top: 10px;
}

/* Nama penulis merah */
.article-meta .author {
  color: #a30202;
  font-weight: 600;
}

.author-credit strong,
.author-credit .author,
.article-meta .author {
  color: #a30202 !important;
  font-weight: bold;
}

.tag {
  background: #f2f2f2;
  color: #555;
  font-size: 13px;
  padding: 4px 8px;
  border-radius: 6px;
}

/* Koemntar */
.article-comments {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #555;
  cursor: pointer;
  transition: color 0.3s ease;
}

.article-comments i {
  color: inherit; 
  transition: color 0.3s ease;
}

.article-comments:hover {
  color: #a30202; /* jadi merah pas hover */
}

.article-comments:hover i {
  transform: scale(1.2);
}
  
/* ARTIKEL DESKRIPSI */
.artikel-deskripsi {
  text-align: center;
  font-size: 16px;
  margin-top: -10px;
  margin-bottom: 30px;
  color: #555;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
}

/* Search Section */
.search-section {
    background-color: #a30202;
    color: white;
    padding: 50px 20px;
    text-align: center;
    margin-top: 100px;
    width: 100%;
    border-radius: 0;
    box-sizing: border-box;
}

.search-title {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

/* Container search */
.search-input-container {
    margin: 20px auto;
    display: flex;
    align-items: center;
    max-width: 900px;
    border-radius: 40px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    border: 2px solid #a30202;
    overflow: hidden; /* biar rapi */
    height: 60px;
}

/* Input box */
.search-input-container input[type="text"] {
    flex: 1;
    border: none;
    padding: 18px 20px;
    font-size: 16px;
    outline: none;
    background: #fff;
}

/* Button search */
.search-input-container button {
    background: transparent; /* biar menyatu */
    border: none;
    padding: 0 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Icon default */
.search-input-container button i {
    font-size: 18px;
    color: #a30202; 
    transition: color 0.3s ease;
}

/* Hover effect tetap merah (atau lebih terang) */
.search-input-container button:hover i {
    color: #424242; /* merah lebih terang saat hover */
}

/* Categories */
.categories {
    margin-top: 25px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
}

.category-item {
    color: white;
    text-decoration: none;
    font-weight: 600;
    margin: 0 15px;
    padding: 5px 0;
    position: relative;
    transition: color 0.3s ease;
}

.category-item.active {
    border-bottom: 2px solid white;
}

.category-item:hover {
    opacity: 0.8;
}

/* No Article */
.no-article {
  grid-column: 1 / -1;
  display: flex;
  text-align: center;       
  font-size: 1.2rem;       
  color: #666;             
  margin: 60px 0;           
  display: flex;            
  justify-content: center;  
  align-items: center;      
  min-height: 200px;  
  font-weight: 500;     
}

/* ===== FOOTER STYLE ===== */
footer {
  background-color: #a30202;
  color: #fff;
  font-family: 'Inter', sans-serif;
  padding-top: 40px;
  margin-top: auto;
  width: 100%;
  box-sizing: border-box;
  overflow: hidden;
  flex-shrink: 0;
}

/* --- Container utama --- */
.footer-container {
  max-width: 1300px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  padding: 0 60px 40px;
}

/* --- Section umum --- */
.footer-section {
  flex: 1;
  min-width: 280px;
  margin-bottom: 30px;
}

/* --- Judul section --- */
.footer-section h4 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 10px;
  position: relative;
  display: inline-block;
}

/* Garis putih di bawah judul */
.footer-section h4::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: -4px;
  display: block;
  width: 100%;
  height: 2px;
  background-color: #fff;
  margin-top: 5px;
  border-radius: 2px;
}

/* --- Logo & Instansi --- */
.footer-logo {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.footer-logo img {
  width: 65px;
  height: 65px;
  margin-right: 12px;
}

.footer-instansi h2 {
  margin: 2px 0 0;
  font-size: 0.9rem;
  font-weight: 400;
  color: #fff;
}

/* --- Deskripsi --- */
.footer-desc {
  font-size: 14px;
  line-height: 1.6;
  color: #f0f0f0;
  margin-top: 10px;
  max-width: 360px;
}

/* --- List Umum --- */
.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section ul li {
  margin: 10px 0;
  display: flex;
  align-items: center;
  color: #fff;
}

.footer-section i {
  margin-right: 10px;
  width: 20px;
  text-align: center;
  color: #ffffff;
}

.footer-section a {
  color: #ffffff;
  text-decoration: none;
  transition: opacity 0.3s;
}

.footer-section a:hover {
  opacity: 0.8;
}

.footer-section.sosmed {
  text-align: center;
}

.footer-section.sosmed h4 {
  text-align: center;
  margin: 0 auto 10px auto;
}

.footer-section h4 {
  font-size: 1.3rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 10px;
  position: relative;
  display: inline-block;
}

.footer-section h4::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: -4px;
  width: 100%;
  height: 2px;
  background-color: #fff;
  border-radius: 2px;
}

.footer-section.sosmed .social-icons {
  display: flex;
  font-size: 1.5rem;
  justify-content: center;
  gap: 15px;
  margin-top: 10px;
}

/* --- Footer bawah --- */
.footer-bottom {
  background-color: #7d0000;
  text-align: center;
  padding: 12px 0;
  font-size: 0.9rem;
  color: #fff;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  width: 100%;
  position: relative;
  bottom: 0;
  left: 0;
}

/* close button di hamburger */
.close-btn {
  font-size: 30px;
  color: white;
  cursor: pointer;
  position: absolute;
  top: 15px;
  right: 20px;
  display: none; /* default hidden, muncul kalau nav aktif */
}

.nav-right.active .close-btn {
  display: block;
}

.copyright {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.2); /* Opsional: Tambahkan garis di atas teks */
    margin-top: 20px;
}

.copyright p {
    margin: 0;
    font-size: 0.9rem;
    color: white; /* Sesuaikan warna jika perlu */
}

/* Popup modern */
.popup-login {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(3px);
}
.popup-login .popup-content {
  background: white;
  padding: 40px 30px;
  border-radius: 12px;
  width: 340px;
  text-align: center;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
  animation: fadeIn 0.25s ease;
}
.popup-login h2 {
  font-size: 24px;
  font-weight: 700;
  color: #111;
  margin-bottom: 12px;
}
.popup-login p {
  color: #555;
  font-size: 15px;
  line-height: 1.5;
  margin-bottom: 25px;
}
.popup-buttons {
  display: flex;
  justify-content: center;
  gap: 12px;
}
.popup-btn-login {
   background: #a30202;
  color: white;
  text-decoration: none;
  padding: 10px 26px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 15px;
  transition: all 0.3s ease;
}
.popup-btn-login:hover {
  background: #8b0202;
  transform: scale(1.05);
}
.popup-btn-cancel {
  background: #e0e0e0;
  border: none;
  color: #333;
  padding: 10px 26px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 15px;
  transition: all 0.3s ease;
}
.popup-btn-cancel:hover {
  background: #cfcfcf;
  transform: scale(1.05);
}

/* RESPONSIVE */
@media (max-width: 768px) {
  body {
    overflow-x: hidden;
  }

  .faq h3 {
    font-size: 22px;
  }

  .search-form input[type="text"] {
    font-size: 14px;
  }

  .search-form button {
    padding: 12px 20px;
  }

  /* Hamburger muncul */
  .hamburger {
    display: block;
    cursor: pointer;
    font-size: 26px;
    color: white;
    background: none;
    border: none;
  }

  /* Navbar disembunyikan */
  .nav-right {
    position: fixed;
    top: 0;
    right: -250px; 
    height: 100%;
    width: 250px;
    background: #a30202c7;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 60px 20px;
    transition: right 0.4s ease-in-out;
    z-index: 999;
  }

  /* Navbar muncul */
  .nav-right.active {
    right: 0;
  }

  /* Link menu */
  .nav-right a {
    margin: 15px 0;
    font-size: 18px;
    color: white;
    text-decoration: none;
    width: 100%;
  }

  .nav-right a:hover {
    opacity: 0.85;
  }

  /* Tombol Masuk */
  .nav-right .btn-login {
    background: white;
    color: #a30202;
    padding: 10px;
    border-radius: 5px;
    font-size: 16px;
    text-align: center;
    width: 100%;
  }

  /* Tombol Daftar */
  .nav-right .btn-login:last-child {
    background: #ffffff;
    color: rgb(205, 30, 30);
  }

  .article-container {
    flex-direction: column;   /* Ubah ke vertikal di layar kecil */
    align-items: center;
  }

  .article-item {
    flex: 1 1 100%;
    max-width: 90%;
  }

  /* Footer */
    .footer-container {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 25px;
    padding: 10px 15px 25px;
  }

  .footer-desc {
    max-width: 100%;
  }

  .footer-section {
    width: 100%;
    margin-bottom: 25px;
    text-align: center;
  }

  .footer-logo {
    justify-content: center;
  }

  .footer-section h4::after {
    left: 50%;
    margin: 0 auto;
    transform: translateX(-50%);
  }
}

@keyframes fadeIn {
  from {opacity:0; transform:scale(0.9);}
  to {opacity:1; transform:scale(1);}
}

    /* Kotak Buat Artikel */
.create-article-box {
  max-width: 600px;
  margin: 30px auto;
  background: #fff;
  border: 2px solid #a30202;
  border-radius: 12px;
  padding: 25px;
  text-align: center;
  box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}
.create-article-box h3 {
  margin: 0 0 10px;
  color: #a30202;
}
.create-article-box p {
  margin: 0 0 20px;
  font-size: 14px;
  color: #555;
}

    .btn-create {
    display: inline-block;
    background: #a30202;
    color: #fff;
    padding: 12px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: background 0.3s;
  }
  .btn-create:hover {
    background: #7a0101;
  }

  /* Animasi tangan melambai */
.wave {
  display: inline-block;
  animation: waveAnim 2s infinite;
  transform-origin: 70% 70%;
}

.article-card.no-image .thumb-img {
  display: none !important;
}

.article-card.no-image {
  padding-top: 0 !important;
  margin-top: 0 !important;
}

.article-card {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.article-container {
  display: flex;
  flex-wrap: wrap;            
  justify-content: space-between;
  gap: 20px;                  
}

.article-item {
  flex: 1 1 45%;             
  box-sizing: border-box;
}

/* === FIX STRUCTUR GRID ARTIKEL === */

/* Container utama artikel */
.articles {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 24px;
  padding: 40px 60px;
  align-items: stretch;
  margin-bottom: 100px;
  box-sizing: border-box;
}

/* Kartu artikel */
.article-card {
  display: flex;
  flex-direction: column;
  background: #fff;
  border: 2px solid #cfcfcf;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  transition: all 0.3s ease;
  height: 100%;
}

.article-card:hover {
  transform: translateY(-4px);
  border-color: #a30202;
  box-shadow: 0 6px 18px rgba(163, 2, 2, 0.15);
  background-color: #fff8f8;
}

/* Gambar artikel */
.article-card img,
.article-card .thumb-img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-bottom: 2px solid #eee;
}

/* Konten teks */
.article-card-content {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  padding: 16px 18px;
  justify-content: space-between;
}

/* Judul artikel */
.article-card h3,
.article-title {
  font-size: 18px;
  font-weight: 700;
  color: #a30202;
  margin-bottom: 10px;
  line-height: 1.4;
}

/* Deskripsi artikel */
.article-card p {
  flex-grow: 1;
  font-size: 14px;
  color: #333;
  line-height: 1.6;
  margin-bottom: 12px;
}

/* Meta info (penulis, komentar, tag, dll) */
.article-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  color: #777;
  margin-top: 10px;
  flex-wrap: wrap;
  gap: 6px;
}

.article-meta .author {
  color: #a30202;
  font-weight: 600;
}

.article-comments {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #555;
  transition: color 0.3s ease;
}

.article-comments:hover {
  color: #a30202;
}

.article-tags {
  margin-top: 8px;
}

.article-tags .tag {
  display: inline-block;
  background: #f5f5f5;
  color: #a30202;
  font-size: 12px;
  font-weight: bold;
  border-radius: 8px;
  padding: 4px 10px;
  text-decoration: none;
  transition: background 0.3s, color 0.3s;
}

.article-tags .tag:hover {
  background: #a30202;
  color: white;
}

/* Responsif biar tetap sejajar */
@media (max-width: 768px) {
  .articles {
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    padding: 20px;
    gap: 16px;
  }

  .article-card img {
    height: 180px;
  }
}

@keyframes waveAnim {
  0% { transform: rotate(0deg); }
  10% { transform: rotate(14deg); }
  20% { transform: rotate(-8deg); }
  30% { transform: rotate(14deg); }
  40% { transform: rotate(-4deg); }
  50% { transform: rotate(10deg); }
  60%,100% { transform: rotate(0deg); }
}

/* === MODAL GAMBAR DENGAN BACKGROUND BLUR === */
#imageModal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100vw;
  height: 100vh;
  backdrop-filter: blur(10px);
  background: rgba(0, 0, 0, 0.4);
  justify-content: center;
  align-items: center;
  overflow: hidden;
  animation: fadeIn 0.25s ease;
}

#imageModal img {
  max-width: 85%;
  max-height: 85%;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  transition: transform 0.3s ease;
}

#imageModal img:hover {
  transform: scale(1.02);
}

#closeModal {
  position: fixed;
  top: 25px;
  right: 35px;
  font-size: 32px;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: 0.2s;
  z-index: 10000;
}

#closeModal:hover {
  color: #ff6b6b;
}

/* Animasi masuk */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
.thumb-img {
    pointer-events: none;
}
  </style>

</head>
<body>

<?php include "header.php"; ?>

<!-- Section sapaan -->
<section class="search-section">
  <h1 class="search-title">
    Halo, <?= $pegawai_nama; ?> 
    <span class="wave">👋</span>
  </h1>
  <h2 class="search-title">Apa Yang Anda Cari?</h2>

  <form method="GET" action="pegawai.php" class="search-input-container">
    <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
    <input type="text" name="search" placeholder="ketik kata kunci pencarian"
           value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>

  <div class="categories">
    <a href="pegawai.php?kategori=SEMUA" class="category-item <?= $kategori=='SEMUA'?'active':'' ?>">SEMUA</a>
    <a href="pegawai.php?kategori=UMUM" class="category-item <?= $kategori=='UMUM'?'active':'' ?>">UMUM</a>
    <a href="pegawai.php?kategori=SPBE" class="category-item <?= $kategori=='SPBE'?'active':'' ?>">SPBE</a>
    <a href="pegawai.php?kategori=IPOLEKSOSBUDHANKAM" class="category-item <?= $kategori=='IPOLEKSOSBUDHANKAM'?'active':'' ?>">IPOLEKSOSBUDHANKAM</a>
  </div>
</section>

<!-- Kotak Buat Artikel -->
<div class="create-article-box">
  <h3><i class="fas fa-pen-nib"></i> Tulis Artikel Baru</h3>
  <p>Berkontribusilah dengan menulis artikel untuk dibagikan.</p>
  <a href="madeartikel.html" class="btn-create">
    <i class="fas fa-plus"></i> Buat Artikel
  </a>
</div>

<main class="container">
  <div id="imageModal">
  <span id="closeModal">&times;</span>
  <img id="modalImg" src="">
</div>
  <section class="articles-grid">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <?php
          $artikel_id = (int)$row['id'];
          $qKomentar = $conn->query("SELECT COUNT(*) AS total FROM komentar WHERE artikel_id = $artikel_id");
          $jmlKomentar = ($qKomentar && $qKomentar->num_rows > 0) ? $qKomentar->fetch_assoc()['total'] : 0;
        ?>
        <?php 
$gambar = trim($row['gambar'] ?? '');
$hasImage = ($gambar !== '' && $gambar !== null && file_exists("uploads/" . $gambar));
?>

<div class="article-card <?= $hasImage ? 'has-image' : 'no-image'; ?>" 
     data-id="<?= $row['id']; ?>" data-loggedin="true">

     <?php if($row['kategori'] === 'INTERNAL'): ?>
    <div class="bookmark-internal">ARTIKEL INTERNAL</div>
<?php endif; ?>

  <?php if($hasImage): ?>
    <img src="uploads/<?= htmlspecialchars($row['gambar']); ?>" 
         class="thumb-img"
         alt="Gambar Artikel">
<?php endif; ?>

  <h3><?= htmlspecialchars($row['judul']); ?></h3>
  <p><?= substr(htmlspecialchars($row['isi_artikel']), 0, 150) . "..."; ?></p>

  <?php if (!empty($row['penulis'])): ?>
    <p class="author-credit">✍️ Ditulis oleh <strong><?= htmlspecialchars($row['penulis']); ?></strong></p>
  <?php endif; ?>

  <div class="article-meta">
    <div class="article-tags">
      <a href="pegawai.php?kategori=<?= urlencode($row['kategori']); ?>" class="tag">
        #<?= htmlspecialchars($row['kategori']); ?>
      </a>
    </div>
    <div class="article-comments">
      <a href="komentar.php?id=<?= $row['id']; ?>" class="comment-link" data-artikel-id="<?= $row['id']; ?>">
        <i class="fas fa-comment"></i> 
        <span id="count-<?= $row['id']; ?>"><?= $jmlKomentar; ?></span>
      </a>
    </div>
  </div>
</div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="no-article">Tidak ada artikel yang cocok dengan pencarian.</div>
    <?php endif; ?>
  </section>
</main>

<footer>
  <div class="footer-container">
    <section class="footer-section about">

    <!-- Logo -->
      <div class="footer-logo">
        <img src="logo kemhan 1.png" alt="Logo Kemhan">
        <div class="footer-instansi">
          <h4>Pusat Data dan Informasi</h4>
          <p>Kementerian Pertahanan Republik Indonesia</p>
        </div>
      </div>
      <p class="footer-desc">
        HanZone merupakan portal informasi dan layanan digital yang dikembangkan oleh
        Pusat Data dan Informasi Kemhan untuk mendukung transformasi digital pertahanan nasional.
      </p>
    </section>

    <!-- Social Media -->
    <div class="footer-section sosmed">
      <h4>Social Media</h4>
      <div class="social-icons">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

    <!-- Kontak -->
    <section class="footer-section contact">
      <h4>Informasi Kontak</h4>
      <ul>
        <li><i class="fas fa-map-marker-alt"></i> Jl. RS Fatmawati No.1, Jakarta Selatan</li>
        <li><i class="fas fa-phone-alt"></i> Telp: 021-7690009</li>
        <li><i class="fas fa-envelope"></i> 
          <a href="mailto:pusdatin@kemhan.go.id">pusdatin@kemhan.go.id</a>
        </li>
      </ul>
    </section>

  </div>

  <div class="footer-bottom">
    <p style="margin:0;">© <span id="year"></span> Pusdatin Kemhan. All rights reserved.</p>
  </div>
</footer>

<script>
// Auto update jumlah komentar tanpa reload
document.addEventListener('DOMContentLoaded', () => {
  function updateKomentarCount() {
    document.querySelectorAll('[data-artikel-id]').forEach(link => {
      const id = link.getAttribute('data-artikel-id');
      fetch(`get_comment_count.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          const el = document.getElementById(`count-${id}`);
          if (el && data.count !== undefined) {
            el.textContent = data.count;
          }
        })
        .catch(err => console.error('Error:', err));
    });
  }

  // Modal gambar
// Modal gambar dengan background blur
document.querySelectorAll('.thumb-img').forEach(img => {
  img.addEventListener('click', function(e) {
    e.stopPropagation();

    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');

    modal.style.display = 'flex';
    modalImg.src = this.dataset.full;

    // Tambah blur ke body (efek lembut)
    document.body.style.overflow = 'hidden';
    document.querySelector('main').style.filter = 'blur(6px)';
    document.querySelector('header').style.filter = 'blur(6px)';
    document.querySelector('footer').style.filter = 'blur(6px)';
  });
});

// Tutup modal
document.getElementById('closeModal').addEventListener('click', function() {
  const modal = document.getElementById('imageModal');
  modal.style.display = 'none';
  document.body.style.overflow = 'auto';
  document.querySelector('main').style.filter = 'none';
  document.querySelector('header').style.filter = 'none';
  document.querySelector('footer').style.filter = 'none';
});

// Tutup kalau klik di luar gambar
document.getElementById('imageModal').addEventListener('click', function(e) {
  if (e.target === this) {
    this.style.display = 'none';
    document.body.style.overflow = 'auto';
    document.querySelector('main').style.filter = 'none';
    document.querySelector('header').style.filter = 'none';
    document.querySelector('footer').style.filter = 'none';
  }
});

  // Update tiap 2 detik
  setInterval(updateKomentarCount, 2000);
  updateKomentarCount();
});

  document.getElementById("year").textContent = new Date().getFullYear();
  document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const nav = document.getElementById('navMenu');
    const closeBtn = document.getElementById('closeBtn');

    hamburger.addEventListener('click', function() {
        nav.classList.add('active');
    });

    closeBtn.addEventListener('click', function() {
        nav.classList.remove('active');
    });
  });

  document.addEventListener('DOMContentLoaded', () => {

  // Klik seluruh card untuk buka artikel detail
  document.querySelectorAll('.article-card').forEach(card => {
    card.addEventListener('click', function(e) {
      // Jangan redirect kalau klik di <a> (tag, komentar)
      if (e.target.closest('a')) return;

      const articleId = this.dataset.id;
      window.location.href = "komentar.php?id=" + articleId + "&from=pegawai";
    });
  });

  // Klik komentar tetap ke komentar.php
  document.querySelectorAll('.comment-link').forEach(link => {
    link.addEventListener('click', function(e) {
      // mencegah event bubble ke card
      e.stopPropagation();
    });
  });

  // Update jumlah komentar real-time
  function updateKomentarCount() {
    document.querySelectorAll('[data-artikel-id]').forEach(link => {
      const id = link.getAttribute('data-artikel-id');
      fetch(`get_comment_count.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          const el = document.getElementById(`count-${id}`);
          if (el && data.count !== undefined) {
            el.textContent = data.count;
          }
        })
        .catch(err => console.error('Error:', err));
    });
  }
  setInterval(updateKomentarCount, 2000);
  updateKomentarCount();
});


  document.addEventListener('DOMContentLoaded', () => {
    const profileToggle = document.querySelector('.profile-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    profileToggle.addEventListener('click', () => {
      dropdownMenu.style.display =
        dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    // Klik di luar dropdown -> tutup
    document.addEventListener('click', (e) => {
      if (!profileToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.style.display = 'none';
      }
    });
  });
</script>
</body>
=======
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "koneksi.php";

// Ambil data pegawai dari session (misal username atau id)
$pegawai_id = $_SESSION['pegawai_id'] ?? null;
$pegawai_nama = "Pegawai";
if ($pegawai_id) {
    $result = $conn->query("SELECT nama FROM regsitrasi WHERE id = $pegawai_id LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pegawai_nama = htmlspecialchars($row['nama']);
    }
}
// Ambil kategori & search keyword dari URL
$kategori = $_GET['kategori'] ?? 'SEMUA';
$keyword = trim($_GET['search'] ?? '');

// Base SQL
$sql = "SELECT a.*, a.gambar, r.nama AS penulis, COUNT(k.id) AS komentar
        FROM artikel a
        LEFT JOIN regsitrasi r ON a.pegawai_id = r.id
        LEFT JOIN komentar k ON k.artikel_id = a.id
        WHERE a.status='publish' AND a.arsip = 0";

$params = [];
$types = "";

// Filter kategori
if ($kategori !== "SEMUA") {
    $sql .= " AND a.kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

// Filter search keyword
if (!empty($keyword)) {
    $sql .= " AND (a.judul LIKE ? OR a.isi_artikel LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
    $types .= "ss";
}

$sql .= " GROUP BY a.id ORDER BY komentar DESC, a.created_at DESC";

$stmt = $conn->prepare($sql);

// Bind param kalau ada
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Artikel Pegawai</title>
  <link rel="icon" type="image/png" href="logo kemhan 1.png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    /* RESET DASAR */
* {
  margin: 0;
  box-sizing: border-box;
}

html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

body {
    font-family: 'Inter', sans-serif;
}

main {
  flex: 1 0 auto;
}

/* Container untuk konten utama */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* No Article */
.no-article {
  grid-column: 1 / -1;
  display: flex;
  text-align: center;       
  font-size: 1.2rem;       
  color: #666;             
  margin: 60px 0;           
  display: flex;            
  justify-content: center;  
  align-items: center;      
  min-height: 200px;  
  font-weight: 500;     
}

/* Article Grid */
    .articles {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      padding: 20px 40px;
      align-items: start;
      margin-bottom: 80px;
    }

.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 40px;
    margin-bottom: 100px;
}

   .article-card {
      background: #fff;
      border: 2px solid #cfcfcf; /* outline lebih tebal */
      border-radius: 10px;
      padding: 16px;
      box-sizing: border-box;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      height: 100%;
      overflow: hidden;
      min-height: 280px;
      position: relative;
    }

    .article-card .bookmark-internal {
    position: absolute;
    top: 10px;       /* Sedikit di bawah atas kartu */
    left: 50%;       /* Center horizontal */
    transform: translateX(-50%); /* Center presisi */
    background-color: #d40000;
    color: white;
    font-weight: 700;
    font-size: 12px;
    padding: 6px 20px; /* Lebar memanjang */
    border-radius: 4px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    z-index: 10;
    letter-spacing: 0.5px;
    text-align: center;
    white-space: nowrap;
    border: 2px solid yellow;
}

.article-card:hover {
  transform: translateY(-4px);
  border-color: #a30202;
  box-shadow: 0 6px 16px rgba(163, 2, 2, 0.15);
  background-color: #fff5f5; /* sedikit merah muda */
}

.article-card h3,
.article-title {
  font-size: 18px;
  font-weight: 700;
  color: #a30202;
  margin: 0 0 8px 0;
  line-height: 1.3;
  transition: color 0.3s ease;
}

.article-card {
  background: #fff;
  border: 2px solid #cfcfcf;
  border-radius: 10px;
  padding: 16px;
  box-sizing: border-box;
  transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  height: 100%;
  overflow: hidden;
  min-height: 280px;
}

.article-card p {
  font-size: 14px;
  color: #333;
  line-height: 1.5;
  flex-grow: 1;
}

.article-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 12px;
}

.article-tags .tag {
  display: inline-block;
  background: #f4f4f4;
  color: #a30202;
  border-radius: 8px;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: bold;
  text-decoration: none;
}

.article-tags .tag:hover {
  background: #a30202;
  color: white;
}

.article-comments .comment-link {
    text-decoration: none;
    color: #555;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    transition: color 0.3s ease; 
}

.article-comments .comment-link:hover {
    color: #a30202;
    transform: none;
}

.article-comments .fa-comment {
    margin-right: 5px;
    font-size: 1rem; 
    transition: color 0.3s ease;
}

.author-credit {
  font-size: 13px;
  color: #a30202;
  margin-top: 8px;
}
.article-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  color: #a30202;
  margin-top: 10px;
}

/* Nama penulis merah */
.article-meta .author {
  color: #a30202;
  font-weight: 600;
}

.author-credit strong,
.author-credit .author,
.article-meta .author {
  color: #a30202 !important;
  font-weight: bold;
}

.tag {
  background: #f2f2f2;
  color: #555;
  font-size: 13px;
  padding: 4px 8px;
  border-radius: 6px;
}

/* Koemntar */
.article-comments {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #555;
  cursor: pointer;
  transition: color 0.3s ease;
}

.article-comments i {
  color: inherit; 
  transition: color 0.3s ease;
}

.article-comments:hover {
  color: #a30202; /* jadi merah pas hover */
}

.article-comments:hover i {
  transform: scale(1.2);
}
  
/* ARTIKEL DESKRIPSI */
.artikel-deskripsi {
  text-align: center;
  font-size: 16px;
  margin-top: -10px;
  margin-bottom: 30px;
  color: #555;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
}

/* Search Section */
.search-section {
    background-color: #a30202;
    color: white;
    padding: 50px 20px;
    text-align: center;
    margin-top: 100px;
    width: 100%;
    border-radius: 0;
    box-sizing: border-box;
}

.search-title {
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

/* Container search */
.search-input-container {
    margin: 20px auto;
    display: flex;
    align-items: center;
    max-width: 900px;
    border-radius: 40px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    border: 2px solid #a30202;
    overflow: hidden; /* biar rapi */
    height: 60px;
}

/* Input box */
.search-input-container input[type="text"] {
    flex: 1;
    border: none;
    padding: 18px 20px;
    font-size: 16px;
    outline: none;
    background: #fff;
}

/* Button search */
.search-input-container button {
    background: transparent; /* biar menyatu */
    border: none;
    padding: 0 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Icon default */
.search-input-container button i {
    font-size: 18px;
    color: #a30202; 
    transition: color 0.3s ease;
}

/* Hover effect tetap merah (atau lebih terang) */
.search-input-container button:hover i {
    color: #424242; /* merah lebih terang saat hover */
}

/* Categories */
.categories {
    margin-top: 25px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
}

.category-item {
    color: white;
    text-decoration: none;
    font-weight: 600;
    margin: 0 15px;
    padding: 5px 0;
    position: relative;
    transition: color 0.3s ease;
}

.category-item.active {
    border-bottom: 2px solid white;
}

.category-item:hover {
    opacity: 0.8;
}

/* No Article */
.no-article {
  grid-column: 1 / -1;
  display: flex;
  text-align: center;       
  font-size: 1.2rem;       
  color: #666;             
  margin: 60px 0;           
  display: flex;            
  justify-content: center;  
  align-items: center;      
  min-height: 200px;  
  font-weight: 500;     
}

/* ===== FOOTER STYLE ===== */
footer {
  background-color: #a30202;
  color: #fff;
  font-family: 'Inter', sans-serif;
  padding-top: 40px;
  margin-top: auto;
  width: 100%;
  box-sizing: border-box;
  overflow: hidden;
  flex-shrink: 0;
}

/* --- Container utama --- */
.footer-container {
  max-width: 1300px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  padding: 0 60px 40px;
}

/* --- Section umum --- */
.footer-section {
  flex: 1;
  min-width: 280px;
  margin-bottom: 30px;
}

/* --- Judul section --- */
.footer-section h4 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 10px;
  position: relative;
  display: inline-block;
}

/* Garis putih di bawah judul */
.footer-section h4::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: -4px;
  display: block;
  width: 100%;
  height: 2px;
  background-color: #fff;
  margin-top: 5px;
  border-radius: 2px;
}

/* --- Logo & Instansi --- */
.footer-logo {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.footer-logo img {
  width: 65px;
  height: 65px;
  margin-right: 12px;
}

.footer-instansi h2 {
  margin: 2px 0 0;
  font-size: 0.9rem;
  font-weight: 400;
  color: #fff;
}

/* --- Deskripsi --- */
.footer-desc {
  font-size: 14px;
  line-height: 1.6;
  color: #f0f0f0;
  margin-top: 10px;
  max-width: 360px;
}

/* --- List Umum --- */
.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section ul li {
  margin: 10px 0;
  display: flex;
  align-items: center;
  color: #fff;
}

.footer-section i {
  margin-right: 10px;
  width: 20px;
  text-align: center;
  color: #ffffff;
}

.footer-section a {
  color: #ffffff;
  text-decoration: none;
  transition: opacity 0.3s;
}

.footer-section a:hover {
  opacity: 0.8;
}

.footer-section.sosmed {
  text-align: center;
}

.footer-section.sosmed h4 {
  text-align: center;
  margin: 0 auto 10px auto;
}

.footer-section h4 {
  font-size: 1.3rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 10px;
  position: relative;
  display: inline-block;
}

.footer-section h4::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: -4px;
  width: 100%;
  height: 2px;
  background-color: #fff;
  border-radius: 2px;
}

.footer-section.sosmed .social-icons {
  display: flex;
  font-size: 1.5rem;
  justify-content: center;
  gap: 15px;
  margin-top: 10px;
}

/* --- Footer bawah --- */
.footer-bottom {
  background-color: #7d0000;
  text-align: center;
  padding: 12px 0;
  font-size: 0.9rem;
  color: #fff;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  width: 100%;
  position: relative;
  bottom: 0;
  left: 0;
}

/* close button di hamburger */
.close-btn {
  font-size: 30px;
  color: white;
  cursor: pointer;
  position: absolute;
  top: 15px;
  right: 20px;
  display: none; /* default hidden, muncul kalau nav aktif */
}

.nav-right.active .close-btn {
  display: block;
}

.copyright {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.2); /* Opsional: Tambahkan garis di atas teks */
    margin-top: 20px;
}

.copyright p {
    margin: 0;
    font-size: 0.9rem;
    color: white; /* Sesuaikan warna jika perlu */
}

/* Popup modern */
.popup-login {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(3px);
}
.popup-login .popup-content {
  background: white;
  padding: 40px 30px;
  border-radius: 12px;
  width: 340px;
  text-align: center;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
  animation: fadeIn 0.25s ease;
}
.popup-login h2 {
  font-size: 24px;
  font-weight: 700;
  color: #111;
  margin-bottom: 12px;
}
.popup-login p {
  color: #555;
  font-size: 15px;
  line-height: 1.5;
  margin-bottom: 25px;
}
.popup-buttons {
  display: flex;
  justify-content: center;
  gap: 12px;
}
.popup-btn-login {
   background: #a30202;
  color: white;
  text-decoration: none;
  padding: 10px 26px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 15px;
  transition: all 0.3s ease;
}
.popup-btn-login:hover {
  background: #8b0202;
  transform: scale(1.05);
}
.popup-btn-cancel {
  background: #e0e0e0;
  border: none;
  color: #333;
  padding: 10px 26px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 15px;
  transition: all 0.3s ease;
}
.popup-btn-cancel:hover {
  background: #cfcfcf;
  transform: scale(1.05);
}

/* RESPONSIVE */
@media (max-width: 768px) {
  body {
    overflow-x: hidden;
  }

  .faq h3 {
    font-size: 22px;
  }

  .search-form input[type="text"] {
    font-size: 14px;
  }

  .search-form button {
    padding: 12px 20px;
  }

  /* Hamburger muncul */
  .hamburger {
    display: block;
    cursor: pointer;
    font-size: 26px;
    color: white;
    background: none;
    border: none;
  }

  /* Navbar disembunyikan */
  .nav-right {
    position: fixed;
    top: 0;
    right: -250px; 
    height: 100%;
    width: 250px;
    background: #a30202c7;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 60px 20px;
    transition: right 0.4s ease-in-out;
    z-index: 999;
  }

  /* Navbar muncul */
  .nav-right.active {
    right: 0;
  }

  /* Link menu */
  .nav-right a {
    margin: 15px 0;
    font-size: 18px;
    color: white;
    text-decoration: none;
    width: 100%;
  }

  .nav-right a:hover {
    opacity: 0.85;
  }

  /* Tombol Masuk */
  .nav-right .btn-login {
    background: white;
    color: #a30202;
    padding: 10px;
    border-radius: 5px;
    font-size: 16px;
    text-align: center;
    width: 100%;
  }

  /* Tombol Daftar */
  .nav-right .btn-login:last-child {
    background: #ffffff;
    color: rgb(205, 30, 30);
  }

  .article-container {
    flex-direction: column;   /* Ubah ke vertikal di layar kecil */
    align-items: center;
  }

  .article-item {
    flex: 1 1 100%;
    max-width: 90%;
  }

  /* Footer */
    .footer-container {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 25px;
    padding: 10px 15px 25px;
  }

  .footer-desc {
    max-width: 100%;
  }

  .footer-section {
    width: 100%;
    margin-bottom: 25px;
    text-align: center;
  }

  .footer-logo {
    justify-content: center;
  }

  .footer-section h4::after {
    left: 50%;
    margin: 0 auto;
    transform: translateX(-50%);
  }
}

@keyframes fadeIn {
  from {opacity:0; transform:scale(0.9);}
  to {opacity:1; transform:scale(1);}
}

    /* Kotak Buat Artikel */
.create-article-box {
  max-width: 600px;
  margin: 30px auto;
  background: #fff;
  border: 2px solid #a30202;
  border-radius: 12px;
  padding: 25px;
  text-align: center;
  box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}
.create-article-box h3 {
  margin: 0 0 10px;
  color: #a30202;
}
.create-article-box p {
  margin: 0 0 20px;
  font-size: 14px;
  color: #555;
}

    .btn-create {
    display: inline-block;
    background: #a30202;
    color: #fff;
    padding: 12px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: background 0.3s;
  }
  .btn-create:hover {
    background: #7a0101;
  }

  /* Animasi tangan melambai */
.wave {
  display: inline-block;
  animation: waveAnim 2s infinite;
  transform-origin: 70% 70%;
}

.article-card.no-image .thumb-img {
  display: none !important;
}

.article-card.no-image {
  padding-top: 0 !important;
  margin-top: 0 !important;
}

.article-card {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.article-container {
  display: flex;
  flex-wrap: wrap;            
  justify-content: space-between;
  gap: 20px;                  
}

.article-item {
  flex: 1 1 45%;             
  box-sizing: border-box;
}

/* === FIX STRUCTUR GRID ARTIKEL === */

/* Container utama artikel */
.articles {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 24px;
  padding: 40px 60px;
  align-items: stretch;
  margin-bottom: 100px;
  box-sizing: border-box;
}

/* Kartu artikel */
.article-card {
  display: flex;
  flex-direction: column;
  background: #fff;
  border: 2px solid #cfcfcf;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  transition: all 0.3s ease;
  height: 100%;
}

.article-card:hover {
  transform: translateY(-4px);
  border-color: #a30202;
  box-shadow: 0 6px 18px rgba(163, 2, 2, 0.15);
  background-color: #fff8f8;
}

/* Gambar artikel */
.article-card img,
.article-card .thumb-img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-bottom: 2px solid #eee;
}

/* Konten teks */
.article-card-content {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  padding: 16px 18px;
  justify-content: space-between;
}

/* Judul artikel */
.article-card h3,
.article-title {
  font-size: 18px;
  font-weight: 700;
  color: #a30202;
  margin-bottom: 10px;
  line-height: 1.4;
}

/* Deskripsi artikel */
.article-card p {
  flex-grow: 1;
  font-size: 14px;
  color: #333;
  line-height: 1.6;
  margin-bottom: 12px;
}

/* Meta info (penulis, komentar, tag, dll) */
.article-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  color: #777;
  margin-top: 10px;
  flex-wrap: wrap;
  gap: 6px;
}

.article-meta .author {
  color: #a30202;
  font-weight: 600;
}

.article-comments {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #555;
  transition: color 0.3s ease;
}

.article-comments:hover {
  color: #a30202;
}

.article-tags {
  margin-top: 8px;
}

.article-tags .tag {
  display: inline-block;
  background: #f5f5f5;
  color: #a30202;
  font-size: 12px;
  font-weight: bold;
  border-radius: 8px;
  padding: 4px 10px;
  text-decoration: none;
  transition: background 0.3s, color 0.3s;
}

.article-tags .tag:hover {
  background: #a30202;
  color: white;
}

/* Responsif biar tetap sejajar */
@media (max-width: 768px) {
  .articles {
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    padding: 20px;
    gap: 16px;
  }

  .article-card img {
    height: 180px;
  }
}

@keyframes waveAnim {
  0% { transform: rotate(0deg); }
  10% { transform: rotate(14deg); }
  20% { transform: rotate(-8deg); }
  30% { transform: rotate(14deg); }
  40% { transform: rotate(-4deg); }
  50% { transform: rotate(10deg); }
  60%,100% { transform: rotate(0deg); }
}

/* === MODAL GAMBAR DENGAN BACKGROUND BLUR === */
#imageModal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100vw;
  height: 100vh;
  backdrop-filter: blur(10px);
  background: rgba(0, 0, 0, 0.4);
  justify-content: center;
  align-items: center;
  overflow: hidden;
  animation: fadeIn 0.25s ease;
}

#imageModal img {
  max-width: 85%;
  max-height: 85%;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  transition: transform 0.3s ease;
}

#imageModal img:hover {
  transform: scale(1.02);
}

#closeModal {
  position: fixed;
  top: 25px;
  right: 35px;
  font-size: 32px;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: 0.2s;
  z-index: 10000;
}

#closeModal:hover {
  color: #ff6b6b;
}

/* Animasi masuk */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
.thumb-img {
    pointer-events: none;
}
  </style>

</head>
<body>

<?php include "header.php"; ?>

<!-- Section sapaan -->
<section class="search-section">
  <h1 class="search-title">
    Halo, <?= $pegawai_nama; ?> 
    <span class="wave">👋</span>
  </h1>
  <h2 class="search-title">Apa Yang Anda Cari?</h2>

  <form method="GET" action="pegawai.php" class="search-input-container">
    <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
    <input type="text" name="search" placeholder="ketik kata kunci pencarian"
           value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>

  <div class="categories">
    <a href="pegawai.php?kategori=SEMUA" class="category-item <?= $kategori=='SEMUA'?'active':'' ?>">SEMUA</a>
    <a href="pegawai.php?kategori=UMUM" class="category-item <?= $kategori=='UMUM'?'active':'' ?>">UMUM</a>
    <a href="pegawai.php?kategori=SPBE" class="category-item <?= $kategori=='SPBE'?'active':'' ?>">SPBE</a>
    <a href="pegawai.php?kategori=IPOLEKSOSBUDHANKAM" class="category-item <?= $kategori=='IPOLEKSOSBUDHANKAM'?'active':'' ?>">IPOLEKSOSBUDHANKAM</a>
  </div>
</section>

<!-- Kotak Buat Artikel -->
<div class="create-article-box">
  <h3><i class="fas fa-pen-nib"></i> Tulis Artikel Baru</h3>
  <p>Berkontribusilah dengan menulis artikel untuk dibagikan.</p>
  <a href="madeartikel.html" class="btn-create">
    <i class="fas fa-plus"></i> Buat Artikel
  </a>
</div>

<main class="container">
  <div id="imageModal">
  <span id="closeModal">&times;</span>
  <img id="modalImg" src="">
</div>
  <section class="articles-grid">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <?php
          $artikel_id = (int)$row['id'];
          $qKomentar = $conn->query("SELECT COUNT(*) AS total FROM komentar WHERE artikel_id = $artikel_id");
          $jmlKomentar = ($qKomentar && $qKomentar->num_rows > 0) ? $qKomentar->fetch_assoc()['total'] : 0;
        ?>
        <?php 
$gambar = trim($row['gambar'] ?? '');
$hasImage = ($gambar !== '' && $gambar !== null && file_exists("uploads/" . $gambar));
?>

<div class="article-card <?= $hasImage ? 'has-image' : 'no-image'; ?>" 
     data-id="<?= $row['id']; ?>" data-loggedin="true">

     <?php if($row['kategori'] === 'INTERNAL'): ?>
    <div class="bookmark-internal">ARTIKEL INTERNAL</div>
<?php endif; ?>

  <?php if($hasImage): ?>
    <img src="uploads/<?= htmlspecialchars($row['gambar']); ?>" 
         class="thumb-img"
         alt="Gambar Artikel">
<?php endif; ?>

  <h3><?= htmlspecialchars($row['judul']); ?></h3>
  <p><?= substr(htmlspecialchars($row['isi_artikel']), 0, 150) . "..."; ?></p>

  <?php if (!empty($row['penulis'])): ?>
    <p class="author-credit">✍️ Ditulis oleh <strong><?= htmlspecialchars($row['penulis']); ?></strong></p>
  <?php endif; ?>

  <div class="article-meta">
    <div class="article-tags">
      <a href="pegawai.php?kategori=<?= urlencode($row['kategori']); ?>" class="tag">
        #<?= htmlspecialchars($row['kategori']); ?>
      </a>
    </div>
    <div class="article-comments">
      <a href="komentar.php?id=<?= $row['id']; ?>" class="comment-link" data-artikel-id="<?= $row['id']; ?>">
        <i class="fas fa-comment"></i> 
        <span id="count-<?= $row['id']; ?>"><?= $jmlKomentar; ?></span>
      </a>
    </div>
  </div>
</div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="no-article">Tidak ada artikel yang cocok dengan pencarian.</div>
    <?php endif; ?>
  </section>
</main>

<footer>
  <div class="footer-container">
    <section class="footer-section about">

    <!-- Logo -->
      <div class="footer-logo">
        <img src="logo kemhan 1.png" alt="Logo Kemhan">
        <div class="footer-instansi">
          <h4>Pusat Data dan Informasi</h4>
          <p>Kementerian Pertahanan Republik Indonesia</p>
        </div>
      </div>
      <p class="footer-desc">
        HanZone merupakan portal informasi dan layanan digital yang dikembangkan oleh
        Pusat Data dan Informasi Kemhan untuk mendukung transformasi digital pertahanan nasional.
      </p>
    </section>

    <!-- Social Media -->
    <div class="footer-section sosmed">
      <h4>Social Media</h4>
      <div class="footer-section sosmed">
      <h4>Social Media</h4>
      <div class="social-icons">
        <a href="https://www.facebook.com/KementerianPertahananRI/" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <a href="https://x.com/Kemhan_RI" target="_blank"><i class="fab fa-twitter"></i></a>
        <a href="https://www.instagram.com/kemhanri/" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://www.youtube.com/@kemhan" target="_blank"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

    <!-- Kontak -->
    <section class="footer-section contact">
      <h4>Informasi Kontak</h4>
      <ul>
        <li><i class="fas fa-map-marker-alt"></i> Jl. RS Fatmawati No.1, Jakarta Selatan</li>
        <li><i class="fas fa-phone-alt"></i> Telp: 021-7690009</li>
        <li><i class="fas fa-envelope"></i> 
          <a href="mailto:pusdatin@kemhan.go.id">pusdatin@kemhan.go.id</a>
        </li>
      </ul>
    </section>

  </div>

  <div class="footer-bottom">
    <p style="margin:0;">© <span id="year"></span> Pusdatin Kemhan. All rights reserved.</p>
  </div>
</footer>

<script>
// Auto update jumlah komentar tanpa reload
document.addEventListener('DOMContentLoaded', () => {
  function updateKomentarCount() {
    document.querySelectorAll('[data-artikel-id]').forEach(link => {
      const id = link.getAttribute('data-artikel-id');
      fetch(`get_comment_count.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          const el = document.getElementById(`count-${id}`);
          if (el && data.count !== undefined) {
            el.textContent = data.count;
          }
        })
        .catch(err => console.error('Error:', err));
    });
  }

  // Modal gambar
// Modal gambar dengan background blur
document.querySelectorAll('.thumb-img').forEach(img => {
  img.addEventListener('click', function(e) {
    e.stopPropagation();

    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');

    modal.style.display = 'flex';
    modalImg.src = this.dataset.full;

    // Tambah blur ke body (efek lembut)
    document.body.style.overflow = 'hidden';
    document.querySelector('main').style.filter = 'blur(6px)';
    document.querySelector('header').style.filter = 'blur(6px)';
    document.querySelector('footer').style.filter = 'blur(6px)';
  });
});

// Tutup modal
document.getElementById('closeModal').addEventListener('click', function() {
  const modal = document.getElementById('imageModal');
  modal.style.display = 'none';
  document.body.style.overflow = 'auto';
  document.querySelector('main').style.filter = 'none';
  document.querySelector('header').style.filter = 'none';
  document.querySelector('footer').style.filter = 'none';
});

// Tutup kalau klik di luar gambar
document.getElementById('imageModal').addEventListener('click', function(e) {
  if (e.target === this) {
    this.style.display = 'none';
    document.body.style.overflow = 'auto';
    document.querySelector('main').style.filter = 'none';
    document.querySelector('header').style.filter = 'none';
    document.querySelector('footer').style.filter = 'none';
  }
});

  // Update tiap 2 detik
  setInterval(updateKomentarCount, 2000);
  updateKomentarCount();
});

  document.getElementById("year").textContent = new Date().getFullYear();
  document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const nav = document.getElementById('navMenu');
    const closeBtn = document.getElementById('closeBtn');

    hamburger.addEventListener('click', function() {
        nav.classList.add('active');
    });

    closeBtn.addEventListener('click', function() {
        nav.classList.remove('active');
    });
  });

  document.addEventListener('DOMContentLoaded', () => {

  // Klik seluruh card untuk buka artikel detail
  document.querySelectorAll('.article-card').forEach(card => {
    card.addEventListener('click', function(e) {
      // Jangan redirect kalau klik di <a> (tag, komentar)
      if (e.target.closest('a')) return;

      const articleId = this.dataset.id;
      window.location.href = "komentar.php?id=" + articleId + "&from=pegawai";
    });
  });

  // Klik komentar tetap ke komentar.php
  document.querySelectorAll('.comment-link').forEach(link => {
    link.addEventListener('click', function(e) {
      // mencegah event bubble ke card
      e.stopPropagation();
    });
  });

  // Update jumlah komentar real-time
  function updateKomentarCount() {
    document.querySelectorAll('[data-artikel-id]').forEach(link => {
      const id = link.getAttribute('data-artikel-id');
      fetch(`get_comment_count.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          const el = document.getElementById(`count-${id}`);
          if (el && data.count !== undefined) {
            el.textContent = data.count;
          }
        })
        .catch(err => console.error('Error:', err));
    });
  }
  setInterval(updateKomentarCount, 2000);
  updateKomentarCount();
});


  document.addEventListener('DOMContentLoaded', () => {
    const profileToggle = document.querySelector('.profile-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    profileToggle.addEventListener('click', () => {
      dropdownMenu.style.display =
        dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    // Klik di luar dropdown -> tutup
    document.addEventListener('click', (e) => {
      if (!profileToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.style.display = 'none';
      }
    });
  });
</script>
</body>
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268
</html>
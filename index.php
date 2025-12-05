<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda</title>
  <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
  <link href="style.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

  <?php include "header.php"; ?>

  <main>
  <section class="hero-wrapper">
    <div class="hero-content">
       <img src="huft kemhan2.jpg"alt="Lambang"class="full-banner">
    </div>
  </section>

  <section class="search-section">
  <form class="search-form" action="index.php" method="get">
  <input type="text" placeholder="Cari pengetahuan..." name="search" />
  <button type="submit">
    <i class="fas fa-search"></i>
  </button>
</form>
  </section>

  <section class="section">
    <h2>Artikel</h2>
    <p class="artikel-deskripsi">
      Berbagai Pengetahuan dan Mencari Informasi Terbaru Mengenai Manajemen Pengetahuan Melalui Artikel
    </p>
  <section class="articles-grid">
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "koneksi.php";

$isLoggedIn = isset($_SESSION['email']); // ✅ deteksi login

// Query artikel + jumlah komentar + penulis
$sql = "
SELECT 
    a.id,
    a.judul,
    a.isi_artikel,
    a.kategori,
    a.gambar,
    a.created_at,  
    p.nama AS nama_penulis,
    COUNT(k.id) AS total_komentar
FROM artikel a
LEFT JOIN komentar k ON k.artikel_id = a.id
LEFT JOIN regsitrasi p ON a.pegawai_id = p.id
WHERE a.status = 'publish' 
  AND a.tipe = 'publik'
  AND a.arsip = 0   -- <== WAJIB
GROUP BY a.id
ORDER BY 
    CASE WHEN COUNT(k.id) = 0 THEN 0 ELSE 1 END DESC, -- prioritaskan artikel dengan komentar
    COUNT(k.id) DESC,                                -- komentar terbanyak
    a.created_at DESC                                -- fallback ke terbaru
LIMIT 6
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0):
  while ($row = $result->fetch_assoc()):
?>
  <div class="article-card" 
       data-id="<?= $row['id']; ?>" 
       data-loggedin="<?= $isLoggedIn ? 'true' : 'false'; ?>" 
       style="cursor:pointer;">

    <!-- Gambar -->
    <?php if (!empty($row['gambar'])): ?>
      <img src="uploads/<?= htmlspecialchars($row['gambar']); ?>" alt="<?= htmlspecialchars($row['judul']); ?>" class="article-image">
    <?php else: ?>
      <img src="default.jpg" alt="Tidak ada gambar" class="article-image">
    <?php endif; ?>

    <!-- Judul -->
    <h3 class="article-title"><?= htmlspecialchars($row['judul']); ?></h3>

    <!-- Isi singkat -->
    <p><?= substr(strip_tags($row['isi_artikel']), 0, 150) . "..."; ?></p>

    <!-- Penulis -->
    <?php if (!empty($row['nama_penulis'])): ?>
      <p class="author-credit">✍️ Ditulis oleh <strong><?= htmlspecialchars($row['nama_penulis']); ?></strong></p>
    <?php endif; ?>

    <!-- Metadata -->
    <div class="article-meta">
      <div class="article-tags">
        <span class="tag">#<?= htmlspecialchars($row['kategori']); ?></span>
      </div>
      <div class="article-comments">
        <i class="fas fa-comment"></i> <span><?= $row['total_komentar']; ?></span>
      </div>
    </div>

  </div>
<?php 
  endwhile;
else:
  echo "<p style='text-align:center; color:#666;'>Belum ada artikel tersedia.</p>";
endif;

// --- SEARCH MODE ---
$search = isset($_GET['search']) ? $_GET['search'] : '';  // ← FIX WAJIB
$searchEscaped = $conn->real_escape_string($search);

$sql = "
SELECT 
    a.id,
    a.judul,
    a.isi_artikel,
    a.kategori,
    a.gambar,
    a.created_at,
    p.nama AS nama,
    COUNT(k.id) AS total_komentar
FROM artikel a
LEFT JOIN komentar k ON k.artikel_id = a.id
LEFT JOIN regsitrasi p ON a.pegawai_id = p.id
WHERE a.status = 'publish'
  AND a.tipe = 'publik'
  AND a.arsip = 0
  AND a.judul LIKE '%$searchEscaped%'
GROUP BY a.id
ORDER BY a.created_at DESC
";

?>
</section>

<!-- ✅ Popup Login -->
<div id="loginPopup" class="popup-login">
  <div class="popup-content">
    <h2>Anda belum login</h2>
    <p>Silakan login terlebih dahulu untuk membaca artikel ini.</p>
    <div class="popup-buttons">
      <a href="login.php" class="popup-btn-login">Login</a>
      <button onclick="closePopup()" class="popup-btn-cancel">Batal</button>
    </div>
  </div>
</div>

<section class="faq">
    <h2 class="faq-title">FAQs</h2>
    <div class="faq-wrapper">
    <div class="faq-container">

    <?php  
    $q = $conn->query("SELECT * FROM faq WHERE featured = 1 ORDER BY id ASC LIMIT 3");

    while ($row = $q->fetch_assoc()): ?>
        <div class="faq-item">

            <div class="faq-header">
                <div class="faq-question">
                    <?= htmlspecialchars($row['question']) ?>
                </div>
                <i class="fas fa-chevron-down faq-toggle"></i>
            </div>

            <div class="faq-answer">
                <?= nl2br(htmlspecialchars($row['answer'])) ?>
            </div>

        </div>
    <?php endwhile; ?>

    </div>
    </div>
</section>
</main>

<script src="hi.js"></script>
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
    <p>&copy; 2025 HanZone | Pusat Data dan Informasi Kementerian Pertahanan</p>
  </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {

  const popup = document.getElementById("loginPopup");
  const cancelBtn = document.querySelector(".popup-btn-cancel");

  // === Fungsi Tampilkan Popup Login ===
  function showLoginPopup(message = null) {
    const title = popup.querySelector("h2");
    const text  = popup.querySelector("p");

    if (message) {
        title.textContent = "Pemberitahuan";
        text.textContent  = message;
    } else {
        title.textContent = "Anda belum login";
        text.textContent  = "Silakan login terlebih dahulu untuk membaca artikel ini.";
    }

    popup.style.display = "flex";
    document.body.classList.add("popup-active");
  }

  // === Fungsi Tutup Popup ===
  function closePopup() {
    popup.style.display = "none";
    document.body.classList.remove("popup-active");
  }

  cancelBtn?.addEventListener("click", closePopup);

  // === KLIK ARTIKEL ===
document.querySelectorAll('.article-card').forEach(card => {
    card.addEventListener('click', function() {
        const articleId = this.dataset.id;
        window.location.href = "komentar.php?id=" + articleId + "&from=beranda";
    });
});

  // === SEARCH BAR ===
  document.querySelector(".search-form").addEventListener("submit", function(e) {
    e.preventDefault();

    let q = document.querySelector("input[name='search']").value.trim().toLowerCase();
    if (q === "") return;

    // keyword spesial
    if (q === "artikel") {
        window.location.href = "artikel.php";
        return;
    }
    if (q === "forum") {
        window.location.href = "forum.php";
        return;
    }

    // Search DB melalui redirect.php
    fetch("redirect.php?q=" + encodeURIComponent(q))
      .then(res => res.json())
      .then(data => {
        if (data.status === "found") {
            window.location.href = data.redirect;
        } else {
            showLoginPopup("Tidak ditemukan. Coba kata kunci lain.");
        }
      });
  });

});
</script>
</body>
</html>


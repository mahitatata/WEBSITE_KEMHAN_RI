<?php
include "koneksi.php";
session_start();

// Ambil semua forum dari DB, urutkan dari terbaru
$query = "
    SELECT f.id, f.judul, f.penulis_nama, f.tanggal,
           COUNT(k.id) AS jumlah_balasan
    FROM forum f
    LEFT JOIN komentar_forum k ON k.forum_id = f.id
    GROUP BY f.id
    ORDER BY f.tanggal DESC
";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forum</title>
    <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
    <link rel="stylesheet" href="forum.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  </head>
  <body>

    <?php include "header.php"; ?>
    
    <main class="container">
      <section class="forum-section">
        <div class="forum-header">
          <h1>Diskusi dan Berbagi Pengetahuan</h1>
          <p>Temukan topik menarik, tanyakan, dan berikan jawaban.</p>
          <a href="madeforum.php" class="btn-buat-topik">+ Buat Topik Baru</a>
        </div>

          <ul class="topic-list">
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <li class="topic-item" data-id="<?= $row['id'] ?>">
                  <div class="topic-info">
                    <h4>
                      <a href="lihatforum.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['judul']) ?></a>
                    </h4>
                    <div class="topic-meta">
                      <span>Oleh: <?= htmlspecialchars($row['penulis_nama']) ?></span>
                      <span><?= date('d M Y, H:i', strtotime($row['tanggal'])) ?></span>
                    </div>
                  </div>
                  <div class="topic-stats">
                    <span><?= $row['jumlah_balasan'] ?></span>
                    <small>Balasan</small>
                  </div>
                  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <button class="btn-hapus-forum" data-id="<?= $row['id'] ?>">Hapus</button>
<?php endif; ?>
                </li>
              <?php endwhile; ?>
            <?php else: ?>
              <p style="text-align:center; color:#777;">Belum ada topik yang dibuat.</p>
            <?php endif; ?>
          </ul>
      </section>
    </main>
     
     <footer>
  <div class="footer-container">
    <section class="footer-section about">
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

    <div class="popup-login">
      <div class="popup-content">
        <h2>Anda belum login</h2>
        <p>Silakan login terlebih dahulu untuk membuat/melihat topik.</p>
        <div class="popup-buttons">
          <button class="popup-btn-cancel">Batal</button>
          <a href="login.php" class="popup-btn-login">Login</a>
        </div>
      </div>
    </div>

    <div id="popup-hapus" class="popup-login" style="display:none;">
  <div class="popup-content">
    <h2>Hapus Forum?</h2>
    <p>Yakin ingin menghapus forum ini? Tindakan ini tidak dapat dibatalkan.</p>
    <div class="popup-buttons">
      <button class="popup-btn-cancel">Batal</button>
      <a id="btn-hapus-confirm" href="#" class="popup-btn-login">Hapus</a>
    </div>
  </div>
</div>

    <div class="footer-section sosmed">
      <h4>Social Media</h4>
      <div class="social-icons">
        <a href="https://www.facebook.com/KementerianPertahananRI/" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <a href="https://x.com/Kemhan_RI" target="_blank"><i class="fab fa-twitter"></i></a>
        <a href="https://www.instagram.com/kemhanri/" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://www.youtube.com/@kemhan" target="_blank"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

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
<script src="artikel.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const popup = document.querySelector(".popup-login");
  const cancelBtn = document.querySelector(".popup-btn-cancel");
  const btnBuat = document.querySelector(".btn-buat-topik");

  function showLoginPopup() {
    popup.style.display = "flex";
    document.body.classList.add("popup-active");
  }

  cancelBtn?.addEventListener("click", () => {
    popup.style.display = "none";
    document.body.classList.remove("popup-active");
  });

  document.querySelectorAll(".topic-item").forEach(item => {
  item.addEventListener("click", function(e) {
    const loggedIn = <?= isset($_SESSION['email']) ? 'true' : 'false' ?>;

    if (!loggedIn) {
      e.preventDefault();
      showLoginPopup();
      return;
    }

    const link = item.querySelector("a");
    if (link) {
      window.location.href = link.href;
    }
  });
});

  btnBuat.addEventListener("click", function(e) {
    const loggedIn = <?= isset($_SESSION['email']) ? 'true' : 'false' ?>;

    if (!loggedIn) {
      e.preventDefault();
      showLoginPopup();
    } 
    // kalau sudah login → biarkan lanjut ke madeforum.php
  });
});

// Tombol hapus forum
document.querySelectorAll(".btn-hapus-forum").forEach(btn => {
  btn.addEventListener("click", function(e) {
    e.stopPropagation(); // biar tidak buka halaman forum
    const id = btn.dataset.id;

    const popup = document.getElementById("popup-hapus");
    const cancel = popup.querySelector(".popup-btn-cancel");
    const confirmBtn = document.getElementById("btn-hapus-confirm");

    popup.style.display = "flex";

    confirmBtn.href = "hapusforum.php?id=" + id;

    cancel.onclick = () => popup.style.display = "none";
  });
});
</script>
</body>
</html>

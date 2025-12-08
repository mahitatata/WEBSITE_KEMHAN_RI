<<<<<<< HEAD
<?php
include "koneksi.php";
session_start();

// Ambil kategori & search keyword dari URL
$kategori = $_GET['kategori'] ?? 'SEMUA';
$keyword  = trim($_GET['search'] ?? '');

// FIX UTAMA — Normalisasi role
$role     = strtolower($_SESSION['role'] ?? 'user'); 

// Base query + join ke komentar
$sql = "SELECT a.*, p.nama AS nama_penulis, COUNT(k.id) AS jumlah_komentar 
        FROM artikel a 
        LEFT JOIN komentar k ON a.id = k.artikel_id 
        LEFT JOIN regsitrasi p ON a.pegawai_id = p.id 
        WHERE a.status='publish'";
$params = [];
$types  = "";

// Filter kategori
if ($kategori !== "SEMUA") {
    $sql .= " AND a.kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

// FIX ADMIN — logika dibenarkan
if ($role === 'admin') {

    // admin default tidak lihat arsip kecuali "show_arsip"
    if (!isset($_GET['show_arsip'])) {
        $sql .= " AND a.arsip = 0";
    }

    // admin tidak dibatasi tipe artikel

} elseif ($role === 'pegawai') {

    // pegawai bisa lihat publik + internal
    $sql .= " AND (a.tipe='publik' OR a.tipe='internal')";
    $sql .= " AND a.arsip = 0";
 
} else {

    // user publik hanya boleh lihat publik
    $sql .= " AND a.tipe='publik'";
    $sql .= " AND a.arsip = 0";
}

// Filter search
if (!empty($keyword)) {
    $sql .= " AND (a.judul LIKE ? OR a.isi_artikel LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
    $types .= "ss";
}

$sql .= "
GROUP BY a.id 
ORDER BY 
    CASE WHEN COUNT(k.id) = 0 THEN 0 ELSE 1 END DESC,
    COUNT(k.id) DESC,
    a.created_at DESC
";

// API kecil: jumlah komentar real-time
if (isset($_GET['get_comments_count'])) {
    $id = intval($_GET['get_comments_count']);
    $stmt = $conn->prepare("SELECT COUNT(*) AS jml FROM komentar WHERE artikel_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    echo $res['jml'];
    exit;
}

// Eksekusi query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$isLoggedIn = isset($_SESSION['email']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel</title>
    <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
    <link rel="stylesheet" href="artikel.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<div id="confirmModal" class="modal">
  <div class="modal-content">
    <h3 id="modal-title">Konfirmasi</h3>
    <p id="modal-desc"></p>
    <div class="modal-actions">
        <button id="modal-cancel">Batal</button>
      <button id="modal-ok">Ya</button>
    </div>
  </div>
</div>

<main>
<section class="search-section">
    <h1 class="search-title">Apa Yang Anda Cari?</h1>
    <form method="GET" action="artikel.php" class="search-input-container">
        <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
        <input type="text" name="search" placeholder="ketik kata kunci pencarian"
               value="<?= htmlspecialchars($keyword) ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <div class="categories">
        <a href="artikel.php?kategori=SEMUA" class="category-item <?= $kategori=='SEMUA'?'active':'' ?>">SEMUA</a>
        <a href="artikel.php?kategori=UMUM" class="category-item <?= $kategori=='UMUM'?'active':'' ?>">UMUM</a>
        <a href="artikel.php?kategori=SPBE" class="category-item <?= $kategori=='SPBE'?'active':'' ?>">SPBE</a>
        <a href="artikel.php?kategori=IPOLEKSOSBUDHANKAM" class="category-item <?= $kategori=='IPOLEKSOSBUDHANKAM'?'active':'' ?>">IPOLEKSOSBUDHANKAM</a>
    </div>
</section>

<div class="container">
<!-- Bagian artikel -->
 <?php if ($role === 'admin'): ?>
    <?php if (isset($_GET['show_arsip'])): ?>
        <a href="artikel.php" class="btn-arsip-link">
            <i class="fas fa-eye-slash"></i> Sembunyikan Arsip
        </a>
    <?php else: ?>
        <a href="artikel.php?show_arsip=1" class="btn-arsip-link">
            <i class="fas fa-folder-open"></i> Lihat Arsip
        </a>
    <?php endif; ?>
<?php endif; ?>

<section class="articles-grid">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="article-card" data-id="<?= $row['id']; ?>" data-loggedin="<?= $isLoggedIn ? 'true' : 'false'; ?>">
 
                <!-- ✅ tambahan: tampilkan gambar artikel -->
                <?php if (!empty($row['gambar'])): ?>
                    <img src="uploads/<?= htmlspecialchars($row['gambar']); ?>" alt="<?= htmlspecialchars($row['judul']); ?>" class="article-image">
                <?php else: ?>
                    <img src="placeholder.jpg" alt="Tidak ada gambar" class="article-image">
                <?php endif; ?>
              
                <h3 class="article-title"><?= htmlspecialchars($row['judul']); ?></h3>
                <p><?= substr(htmlspecialchars($row['isi_artikel']), 0, 150) . "..."; ?></p>
                <?php if (!empty($row['nama_penulis'])): ?>
                    <p class="author-credit">✍️ Ditulis oleh <strong><?= htmlspecialchars($row['nama_penulis']); ?></strong></p>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
    <?php if ($row['arsip'] == 0): ?>
        <button class="arsip-btn" data-id="<?= $row['id']; ?>">
    <i class="fas fa-archive"></i>
</button>
    <?php else: ?>
        <button class="unarsip-btn" data-id="<?= $row['id']; ?>">
    <i class="fas fa-undo"></i> Kembalikan
</button>
    <?php endif; ?>
<?php endif; ?>
                <div class="article-meta">
                    <div class="article-tags">
                        <a href="artikel.php?kategori=<?= urlencode($row['kategori']); ?>" class="tag">
                            #<?= htmlspecialchars($row['kategori']); ?>
                        </a>
                    </div>
                    <div class="article-comments">
                        <div class="comment-link" data-id="<?= $row['id']; ?>" data-loggedin="<?= $isLoggedIn ? 'true' : 'false'; ?>">
                            <i class="fas fa-comment"></i> 
                            <span><?= $row['jumlah_komentar']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-article">Tidak ada artikel yang cocok dengan pencarian.</div>
    <?php endif; ?>
</section>
</main>
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
    <p>&copy; 2025 HanZone | Pusat Data dan Informasi Kementerian Pertahanan</p>
  </div>
</footer>
<script src="artikel.js"></script>
<script>
// Status login dari PHP
let loggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

// --- Update komentar real-time ---
function updateCommentCounts() {
  document.querySelectorAll('.comment-link').forEach(el => {
    const id = el.dataset.id;
    fetch(`artikel.php?get_comments_count=${id}`)
      .then(res => res.text())
      .then(count => {
        const span = el.querySelector('span');
        if (span && span.textContent !== count) {
          span.textContent = count;
        }
      });
  });
}
setInterval(updateCommentCounts, 2000);
updateCommentCounts();

// === Klik artikel card (langsung buka artikel) ===
document.querySelectorAll(".article-card").forEach(card => {
    card.addEventListener("click", function(e) {
        // Jangan redirect kalau klik kategori tag
        if (e.target.closest('.tag')) {
            return;
        }
        
        let articleId = this.getAttribute("data-id");
        window.location.href = "komentar.php?id=" + articleId;
    });
});

// --- Arsip ---
document.querySelectorAll('.arsip-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        let id = btn.dataset.id;
        openConfirmModal(id, "arsip");
    });
});

document.querySelectorAll('.unarsip-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        let id = btn.dataset.id;
        openConfirmModal(id, "unarsip");
    });
});

//confirm modal//
function openConfirmModal(id, action){
    const modal = document.getElementById("confirmModal");
    const desc = document.getElementById("modal-desc");
    const btnOk = document.getElementById("modal-ok");

    desc.innerText = action==="arsip" 
        ? "Yakin arsipkan artikel ini?"
        : "Yakin kembalikan artikel ini?";

    modal.style.display="flex";

    btnOk.onclick = function(){
        fetch("arsip_artikel.php", {
            method: "POST",
            body: new URLSearchParams({ id: id, action: action })
        })
        .then(r=>r.text())
        .then(t=>{
            if(t=="ok") location.reload();
        });
        modal.style.display="none";
    };

    document.getElementById("modal-cancel").onclick = function(){
        modal.style.display="none";
    };
}

</script>
</body>
</html>
=======
<?php
include "koneksi.php";
session_start();

// Ambil kategori & search keyword dari URL
$kategori = $_GET['kategori'] ?? 'SEMUA';
$keyword  = trim($_GET['search'] ?? '');

// FIX UTAMA — Normalisasi role
$role     = strtolower($_SESSION['role'] ?? 'user'); 

// Base query + join ke komentar
$sql = "SELECT a.*, p.nama AS nama_penulis, COUNT(k.id) AS jumlah_komentar 
        FROM artikel a 
        LEFT JOIN komentar k ON a.id = k.artikel_id 
        LEFT JOIN regsitrasi p ON a.pegawai_id = p.id 
        WHERE a.status='publish'";
$params = [];
$types  = "";

// Filter kategori
if ($kategori !== "SEMUA") {
    $sql .= " AND a.kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

// FIX ADMIN — logika dibenarkan
if ($role === 'admin') {

    // admin default tidak lihat arsip kecuali "show_arsip"
    if (!isset($_GET['show_arsip'])) {
        $sql .= " AND a.arsip = 0";
    }

    // admin tidak dibatasi tipe artikel

} elseif ($role === 'pegawai') {

    // pegawai bisa lihat publik + internal
    $sql .= " AND (a.tipe='publik' OR a.tipe='internal')";
    $sql .= " AND a.arsip = 0";
 
} else {

    // user publik hanya boleh lihat publik
    $sql .= " AND a.tipe='publik'";
    $sql .= " AND a.arsip = 0";
}

// Filter search
if (!empty($keyword)) {
    $sql .= " AND (a.judul LIKE ? OR a.isi_artikel LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
    $types .= "ss";
}

$sql .= "
GROUP BY a.id 
ORDER BY 
    CASE WHEN COUNT(k.id) = 0 THEN 0 ELSE 1 END DESC,
    COUNT(k.id) DESC,
    a.created_at DESC
";

// API kecil: jumlah komentar real-time
if (isset($_GET['get_comments_count'])) {
    $id = intval($_GET['get_comments_count']);
    $stmt = $conn->prepare("SELECT COUNT(*) AS jml FROM komentar WHERE artikel_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    echo $res['jml'];
    exit;
}

// Eksekusi query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$isLoggedIn = isset($_SESSION['email']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel</title>
    <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
    <link rel="stylesheet" href="artikel.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php include "header.php"; ?>

<div id="confirmModal" class="modal">
  <div class="modal-content">
    <h3 id="modal-title">Konfirmasi</h3>
    <p id="modal-desc"></p>
    <div class="modal-actions">
        <button id="modal-cancel">Batal</button>
      <button id="modal-ok">Ya</button>
    </div>
  </div>
</div>

<main>
<section class="search-section">
    <h1 class="search-title">Apa Yang Anda Cari?</h1>
    <form method="GET" action="artikel.php" class="search-input-container">
        <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
        <input type="text" name="search" placeholder="ketik kata kunci pencarian"
               value="<?= htmlspecialchars($keyword) ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <div class="categories">
        <a href="artikel.php?kategori=SEMUA" class="category-item <?= $kategori=='SEMUA'?'active':'' ?>">SEMUA</a>
        <a href="artikel.php?kategori=UMUM" class="category-item <?= $kategori=='UMUM'?'active':'' ?>">UMUM</a>
        <a href="artikel.php?kategori=SPBE" class="category-item <?= $kategori=='SPBE'?'active':'' ?>">SPBE</a>
        <a href="artikel.php?kategori=IPOLEKSOSBUDHANKAM" class="category-item <?= $kategori=='IPOLEKSOSBUDHANKAM'?'active':'' ?>">IPOLEKSOSBUDHANKAM</a>
    </div>
</section>

<div class="container">
<!-- Bagian artikel -->
 <?php if ($role === 'admin'): ?>
    <?php if (isset($_GET['show_arsip'])): ?>
        <a href="artikel.php" class="btn-arsip-link">
            <i class="fas fa-eye-slash"></i> Sembunyikan Arsip
        </a>
    <?php else: ?>
        <a href="artikel.php?show_arsip=1" class="btn-arsip-link">
            <i class="fas fa-folder-open"></i> Lihat Arsip
        </a>
    <?php endif; ?>
<?php endif; ?>

<section class="articles-grid">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="article-card" data-id="<?= $row['id']; ?>" data-loggedin="<?= $isLoggedIn ? 'true' : 'false'; ?>">
 
                <!-- ✅ tambahan: tampilkan gambar artikel -->
                <?php if (!empty($row['gambar'])): ?>
                    <img src="uploads/<?= htmlspecialchars($row['gambar']); ?>" alt="<?= htmlspecialchars($row['judul']); ?>" class="article-image">
                <?php else: ?>
                    <img src="placeholder.jpg" alt="Tidak ada gambar" class="article-image">
                <?php endif; ?>
              
                <h3 class="article-title"><?= htmlspecialchars($row['judul']); ?></h3>
                <p><?= substr(htmlspecialchars($row['isi_artikel']), 0, 150) . "..."; ?></p>
                <?php if (!empty($row['nama_penulis'])): ?>
                    <p class="author-credit">✍️ Ditulis oleh <strong><?= htmlspecialchars($row['nama_penulis']); ?></strong></p>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
    <?php if ($row['arsip'] == 0): ?>
        <button class="arsip-btn" data-id="<?= $row['id']; ?>">
    <i class="fas fa-archive"></i>
</button>
    <?php else: ?>
        <button class="unarsip-btn" data-id="<?= $row['id']; ?>">
    <i class="fas fa-undo"></i> Kembalikan
</button>
    <?php endif; ?>
<?php endif; ?>
                <div class="article-meta">
                    <div class="article-tags">
                        <a href="artikel.php?kategori=<?= urlencode($row['kategori']); ?>" class="tag">
                            #<?= htmlspecialchars($row['kategori']); ?>
                        </a>
                    </div>
                    <div class="article-comments">
                        <div class="comment-link" data-id="<?= $row['id']; ?>" data-loggedin="<?= $isLoggedIn ? 'true' : 'false'; ?>">
                            <i class="fas fa-comment"></i> 
                            <span><?= $row['jumlah_komentar']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-article">Tidak ada artikel yang cocok dengan pencarian.</div>
    <?php endif; ?>
</section>
</main>
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
    <p>&copy; 2025 HanZone | Pusat Data dan Informasi Kementerian Pertahanan</p>
  </div>
</footer>
<script src="artikel.js"></script>
<script>
// Status login dari PHP
let loggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

// --- Update komentar real-time ---
function updateCommentCounts() {
  document.querySelectorAll('.comment-link').forEach(el => {
    const id = el.dataset.id;
    fetch(`artikel.php?get_comments_count=${id}`)
      .then(res => res.text())
      .then(count => {
        const span = el.querySelector('span');
        if (span && span.textContent !== count) {
          span.textContent = count;
        }
      });
  });
}
setInterval(updateCommentCounts, 2000);
updateCommentCounts();

// === Klik artikel card (langsung buka artikel) ===
document.querySelectorAll(".article-card").forEach(card => {
    card.addEventListener("click", function(e) {
        // Jangan redirect kalau klik kategori tag
        if (e.target.closest('.tag')) {
            return;
        }
        
        let articleId = this.getAttribute("data-id");
        window.location.href = "komentar.php?id=" + articleId;
    });
});

// --- Arsip ---
document.querySelectorAll('.arsip-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        let id = btn.dataset.id;
        openConfirmModal(id, "arsip");
    });
});

document.querySelectorAll('.unarsip-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        let id = btn.dataset.id;
        openConfirmModal(id, "unarsip");
    });
});

//confirm modal//
function openConfirmModal(id, action){
    const modal = document.getElementById("confirmModal");
    const desc = document.getElementById("modal-desc");
    const btnOk = document.getElementById("modal-ok");

    desc.innerText = action==="arsip" 
        ? "Yakin arsipkan artikel ini?"
        : "Yakin kembalikan artikel ini?";

    modal.style.display="flex";

    btnOk.onclick = function(){
        fetch("arsip_artikel.php", {
            method: "POST",
            body: new URLSearchParams({ id: id, action: action })
        })
        .then(r=>r.text())
        .then(t=>{
            if(t=="ok") location.reload();
        });
        modal.style.display="none";
    };

    document.getElementById("modal-cancel").onclick = function(){
        modal.style.display="none";
    };
}

</script>
</body>
</html>
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

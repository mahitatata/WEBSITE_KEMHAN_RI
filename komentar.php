<?php
session_start();
include "koneksi.php";

$backUrl = $_SERVER['HTTP_REFERER'] ?? 'artikel.php';

$role = $_SESSION['role'] ?? 'user';

// ========================
// 1. Cek apakah ada ID
// ========================
if (!isset($_GET['id'])) {
    die("Artikel tidak ditemukan.");
}

$artikel_id = intval($_GET['id']);

// ========================
// 2. Ambil artikel dulu
// ========================
$stmt_artikel = $conn->prepare("SELECT id, judul, isi_artikel, penulis, pegawai_id, created_at, gambar, pdf, tipe, arsip FROM artikel WHERE id = ? LIMIT 1");
$stmt_artikel->bind_param("i", $artikel_id);
$stmt_artikel->execute();
$result_artikel = $stmt_artikel->get_result();

if ($result_artikel->num_rows == 0) die("Artikel tidak ditemukan.");
$artikel = $result_artikel->fetch_assoc();

// ========================
// 3. Baru CEK AKSES internal
// ========================
if ($artikel['tipe'] === 'internal') {
    if (!in_array($role, ['pegawai', 'admin'])) {
        echo "<h2 style='color:red;'>Akses Ditolak</h2>";
        echo "<p>Artikel ini hanya boleh dilihat oleh pegawai dan admin.</p>";
        exit;
    }
}

// Kalau ada parameter 'from' di URL, pakai itu
if (isset($_GET['from'])) {
    $routes = [
        'beranda' => 'index.php',
        'artikel' => 'artikel.php',
        'pegawai' => 'pegawai.php'
    ];

    if (isset($routes[$_GET['from']])) {
        $backUrl = $routes[$_GET['from']];
    }
}

$isLoggedIn = isset($_SESSION['email']);
$nama_user = $_SESSION['nama'] ?? $_SESSION['email'] ?? "Anonim";
$pegawai_id = $_SESSION['pegawai_id'] ?? null;
$role = $_SESSION['role'] ?? 'user';

// Insert komentar
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['komentar']) && $isLoggedIn) {
    $isi = trim($_POST['isi']);
    if (!empty($isi)) {
        $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $stmt_insert = $conn->prepare("INSERT INTO komentar (artikel_id, parent_id, Nama, Isi_Text, pegawai_id) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("iissi", $artikel_id, $parent_id, $nama_user, $isi, $pegawai_id);
        if ($stmt_insert->execute()) exit("success"); else exit("error");
    }
}

// Fungsi tampilkan komentar modern dengan toggle dan reply kecil
function tampilkanKomentarModern($conn, $artikel_id, $parent_id = null, $id_pembuat = null) {
    $sql = "SELECT * FROM komentar WHERE artikel_id = ? AND " . ($parent_id === null ? "parent_id IS NULL" : "parent_id = ?") . " ORDER BY Tanggal ASC";
    $stmt = $conn->prepare($sql);
    if ($parent_id === null) $stmt->bind_param("i", $artikel_id);
    else $stmt->bind_param("ii", $artikel_id, $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $isAuthor = ($id_pembuat && $row['pegawai_id'] == $id_pembuat);
            $isOwnComment = ($row['pegawai_id'] && $row['pegawai_id'] == ($_SESSION['pegawai_id'] ?? 0));
            $nestedClass = $parent_id ? "nested" : "";

            // Nama user yang dibalas (jika reply)
            $replyTo = "";
            if ($parent_id) {
                $stmtParent = $conn->prepare("SELECT Nama FROM komentar WHERE id = ?");
                $stmtParent->bind_param("i", $parent_id);
                $stmtParent->execute();
                $resParent = $stmtParent->get_result();
                if($resParent->num_rows>0){
                    $rowParent = $resParent->fetch_assoc();
                    $replyTo = "Membalas @" . htmlspecialchars($rowParent['Nama']);
                }
            }

            // Cek apakah komentar ini punya reply
            $stmtCheck = $conn->prepare("SELECT COUNT(*) as total FROM komentar WHERE parent_id = ?");
            $stmtCheck->bind_param("i", $row['id']);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();
            $hasReplies = $resCheck->fetch_assoc()['total'] > 0;

            echo "<div class='komentar-item {$nestedClass}' data-id='{$row['id']}'>";
            echo "<strong>" . htmlspecialchars($row['Nama']) . "</strong>";
            if ($isAuthor) echo " <span class='badge-penulis'>Penulis</span>";
            echo "<span class='waktu'>" . date("d M Y H:i", strtotime($row['Tanggal'])) . "</span>";
            
            if($replyTo) echo "<p class='reply-to'>$replyTo</p>";
            echo "<p>" . nl2br(htmlspecialchars($row['Isi_Text'])) . "</p>";

            // HAPUS KOMENTAR ADMIN/user
            echo "<div class='actions'>";

if ((isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || $isOwnComment) {
    echo "
    <button class='delete-btn' data-id='{$row['id']}' title='Hapus komentar'>
        <svg width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='currentColor'
             stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
            <polyline points='3 6 5 6 21 6'></polyline>
            <path d='M19 6l-1 14H6L5 6'></path>
            <path d='M10 11v6'></path>
            <path d='M14 11v6'></path>
            <path d='M9 6V4h6v2'></path>
        </svg>
    </button>";
}

if (!$isOwnComment) {
    echo "<button class='reply-btn' data-parent='{$row['id']}'>Balas</button>";
}

if ($hasReplies) {
    echo "<button class='toggle-replies' data-id='{$row['id']}'>Tampilkan Balasan</button>";
}

echo "</div>";

            // Container reply
            echo "<div class='reply-container' data-parent='{$row['id']}' style='display:none;'>";
            tampilkanKomentarModern($conn, $artikel_id, $row['id'], $id_pembuat);
            echo "</div>";

            echo "</div>";
        }
    }
}

$id_pembuat = $artikel['pegawai_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="logo kemhan 1.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Artikel</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
body{font-family:'Inter',sans-serif;margin:0;background:#f9fafb;color:#333;}
.container{max-width:800px;margin:2rem auto;padding:0 1rem;}
.artikel-box{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 12px rgba(154, 154, 154, 0.08);margin-bottom:2rem;}
.artikel-box h1{margin-top:0;font-size:1.8rem;}
.artikel-date{font-size:0.9rem;color:#888;margin-bottom:1rem;}
.artikel-image img{width:100%;max-height:380px;border-radius:0;border:none;object-fit:cover;box-shadow:0 4px 10px rgba(22, 22, 22, 0.1);border:3px solid #7c0000ff;margin:20px 0;}
.label-internal {display: inline-block;background: #830000;color: white;font-size: 0.75rem;font-weight: 700;padding: 4px 10px;border-radius: 6px;margin-left: 10px;text-transform: uppercase;letter-spacing: 1px;}
.artikel-box.internal {border: 2px solid #830000;background: #fff7f7;}

.komentar-section,.komentar-list{background:#fff;padding:1.5rem;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:2rem;}
.komentar-form textarea{width:100%;padding:0.8rem;border:1px solid #ddd;border-radius:8px;font-family:inherit;margin-bottom:1rem;transition:border 0.2s;}
.komentar-form textarea:focus{border-color:#a30202;outline:none;}
.komentar-form button{background:#a30202;color:#fff;padding:0.8rem 1.5rem;border-radius:8px;cursor:pointer;font-weight:600;transition:background 0.2s;}
.komentar-form button:hover{background:#7a0202;} 

.komentar-item strong{color:#a30202;}
.komentar-item .waktu{font-size:0.8rem;color:#888;margin-left:0.5rem;}
.badge-penulis {
    background: #a30202;
    color: #fff;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 6px;
    margin-left: 6px;
    vertical-align: middle;   /* bikin rata tengah */
    display: inline-block;    /* biar posisinya stabil */
}
.reply-btn,.toggle-replies{background:none;color:#a30202;border:none;cursor:pointer;font-size:0.75rem;font-weight:600;margin-top:5px;}
.reply-btn:hover,.toggle-replies:hover{text-decoration:underline;}

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
}

.btn-back-shopee::before {
    content: "";
    color: #7a0202;
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

/* Tombol Batal */
#batalReply {
    background:#ccc;      
    color:#333;
    margin-left:8px;
    padding:0.8rem 1.5rem; /* sama dengan tombol kirim */
    border-radius:8px;
    border:none;
    cursor:pointer;
    font-weight:600;       /* sama dengan tombol kirim */
    transition:background 0.2s;
}
#batalReply:hover {
    background:#bbb;
}

.reply-btn, .toggle-replies {
    background:none;
    color:#a30202;
    border:none;
    cursor:pointer;
    font-size:0.85rem;  
    font-weight:600;
    margin-top:5px;
    margin-right:10px; /* jarak horizontal antar tombol */
    padding:0;           
}

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

/* --- KOMENTAR AREA (style match komentar.php) --- */
    .komentar-list { margin-top:18px; }
    .komentar-section { margin-top:28px; }

    /* Comment item */
    .komentar-item { margin: 18px 0; padding: 0; }
    .komentar-item.nested { margin-left: 26px; border-left:1px solid #c74d4dff; padding-left:12px; }

    .komentar-item .meta-line { display:flex; align-items:center; gap:10px; margin-bottom:6px; }
    .komentar-item strong { color:#a30202; font-weight:700; display:block; }
    .waktu { font-size:12px; color:#777; }

    .reply-to { font-size:13px; color:#555; margin-bottom:6px; }

    .komentar-item p { margin:4px 0 8px 0; line-height:1.45; white-space:pre-wrap; }

    .actions { display:flex; gap:10px; align-items:center; margin-top:6px; }
    .reply-btn, .toggle-replies { background:none; border:none; color:#a30202; font-weight:700; cursor:pointer; font-size:13px; padding:0; }
    .reply-btn:hover, .toggle-replies:hover { text-decoration:underline; }

    .reply-container { margin-top:8px; }

    /* komentar main form (top) */
    .komentar-form { margin-top:18px; }
    .komentar-form textarea { width:100%; padding:12px; border-radius:8px; border:1px solid #901616ff; resize:vertical; min-height:90px; font-family:inherit; }
    .komentar-form .btns { margin-top:10px; display:flex; gap:8px; align-items:center; }
    .komentar-form button.primary { background:#a30202; color:#fff; border:none; padding:10px 16px; border-radius:8px; font-weight:700; cursor:pointer; }
    .komentar-form button.cancel { background:#ccc; color:#333; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; }

    /* reply small form style (same as komentar.php) */
    .small-reply-form textarea { width:100%; padding:10px; border-radius:6px; border:1px solid #ddd; min-height:70px; resize:vertical; }
    .small-reply-form .btns { margin-top:8px; display:flex; gap:8px; }

    /* responsive */
    @media (max-width:600px) {
      .container { margin:18px; padding:20px; }
      .komentar-item.nested { margin-left:14px; padding-left:8px; }
    }

     /* delete button komentar */
.actions {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 6px;
}

.delete-btn {
    background: #f8d7da;      /* merah muda sebagai background */
    border: 1px solid #f5c2c7; /* border tipis agar terlihat */
    border-radius: 6px;
    cursor: pointer;
    padding: 4px;
    color: #a30202;           /* icon jadi merah tua */
    transition: all 0.2s;
}

.delete-btn svg {
    vertical-align: middle;
}

.delete-btn:hover {
    background: #a30202;      /* background merah tua */
    color: #fff;              /* icon jadi putih */
    transform: scale(1.2);
}

@media(max-width:600px){.artikel-box,.komentar-section,.komentar-list{padding:1rem;}.artikel-box h1{font-size:1.5rem;}}
</style>
</head>
<body>
<main class="container">
<a href="<?= $backUrl ?>" class="btn-back-shopee">
    <svg class="arrow-shopee" viewBox="0 0 24 24">
        <path d="M15 6l-6 6 6 6" />
    </svg>
</a>
<article class="artikel-box">
<h1>
    <?= htmlspecialchars($artikel['judul']); ?>

    <?php if ($artikel['tipe'] === 'internal'): ?>
        <span class="label-internal">Internal</span>
    <?php endif; ?>
</h1>
<p class="artikel-date"><?= date("d M Y H:i", strtotime($artikel['created_at'])); ?></p>
<?php if(!empty($artikel['gambar'])): ?>
<div class="artikel-image"><img src="uploads/<?= htmlspecialchars($artikel['gambar']); ?>" alt="Gambar Artikel"></div>
<?php endif; ?>
<?php if (!empty($artikel['pdf'])): ?>
    <div style="margin: 20px 0;">
        <strong>📎 Lampiran PDF:</strong><br><br>
        <button id="pdfBtn"
           style="display:inline-block; background:#8B0000; color:white; padding:10px 18px; border-radius:8px; font-weight:600; border:none; cursor:pointer;">
            📄 Buka File PDF
        </button>
    </div>
<?php endif; ?>
<div class="artikel-content"><?= nl2br(htmlspecialchars($artikel['isi_artikel'])); ?></div>
</article>

<section class="komentar-section">
<h2>Tinggalkan Komentar</h2>
<?php if($isLoggedIn): ?>
<form id="formKomentar" class="komentar-form">
<p><strong><?= htmlspecialchars($nama_user); ?></strong></p>
<textarea name="isi" id="isi" placeholder="Tulis komentar Anda..." required></textarea>
<button type="submit" name="komentar">Kirim</button>
</form>
<?php else: ?>
<button id="loginForComment" 
style="background:#a30202;color:white;border:none;padding:0.8rem 1.5rem;border-radius:8px;cursor:pointer;">
    Login untuk berkomentar
</button>
<?php endif; ?>
</section>

<section class="komentar-list" id="daftarKomentar">
<h2>Komentar</h2>
<?php tampilkanKomentarModern($conn, $artikel_id, null, $id_pembuat); ?>
</section>
<!-- POPUP LOGIN -->
<div class="popup-login" id="loginPopup">
  <div class="popup-content">
    <h2>Anda belum login</h2>
    <p>Silakan login terlebih dahulu untuk berkomentar.</p>
    <div class="popup-buttons">
      <button class="popup-btn-cancel" id="cancelPopup">Batal</button>
      <a href="login.php" class="popup-btn-login">Login</a>
    </div>
  </div>
</div>

<!-- POPUP KONFIRMASI HAPUS -->
<div class="popup-login" id="deletePopup">
  <div class="popup-content">
    <h2>Konfirmasi Hapus</h2>
    <p>Apakah kamu yakin ingin menghapus komentar ini?</p>
    <div class="popup-buttons">
      <button class="popup-btn-login" id="confirmDeleteBtn">Hapus</button>
      <button class="popup-btn-cancel" id="cancelDeleteBtn">Batal</button>
    </div>
  </div>
</div>

<!-- POPUP LOGIN PDF -->
<div class="popup-login" id="pdfPopup">
  <div class="popup-content">
    <h2>Anda belum login</h2>
    <p>Silakan login terlebih dahulu untuk membuka file PDF ini.</p>
    <div class="popup-buttons">
      <a href="login.php" class="popup-btn-login">Login</a>
      <button class="popup-btn-cancel" id="cancelPdfPopup">Batal</button>
    </div>
  </div>
</div>

</main>

<script>
// pop up login 
function showLoginPopup() {
    document.getElementById("loginPopup").style.display = "flex";
}

function closeLoginPopup() {
    document.getElementById("loginPopup").style.display = "none";
}

document.getElementById("cancelPopup")?.addEventListener("click", () => {
    closeLoginPopup();
});

// === TRIGGER POPUP LOGIN SAAT KLIK TOMBOL KOMENTAR ===
const loginCommentBtn = document.getElementById("loginForComment");
if (loginCommentBtn) {
    loginCommentBtn.addEventListener("click", function() {
        if (typeof showLoginPopup === "function") {
            showLoginPopup(); 
        } else {
            alert("Silakan login terlebih dahulu.");
        }
    });
}

// Popup PDF
function showPdfPopup() {
    document.getElementById("pdfPopup").style.display = "flex";
}

function closePdfPopup() {
    document.getElementById("pdfPopup").style.display = "none";
}

document.getElementById("cancelPdfPopup")?.addEventListener("click", closePdfPopup);

document.getElementById("pdfBtn")?.addEventListener("click", function() {
    <?php if (!$isLoggedIn): ?>
        showPdfPopup();
    <?php else: ?>
        // Kalau sudah login, langsung buka PDF
        window.location.href = "lihatpdf.php?file=<?= urlencode($artikel['pdf']) ?>";
    <?php endif; ?>
});

document.addEventListener("DOMContentLoaded",()=>{
  const form=document.getElementById("formKomentar");
  const isi=document.getElementById("isi");

  if(form){
    form.addEventListener("submit",async(e)=>{
      e.preventDefault();
      const text=isi.value.trim();
      if(!text) return;
      const res=await fetch("komentar.php?id=<?= $artikel_id ?>",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:new URLSearchParams({komentar:1,isi:text})});
      const r=await res.text();
      if(r==="success") location.reload();
    });
  }
  
// Tombol Hapus Komentar
  let deleteCommentId = null;

document.addEventListener("click", function(e) {
    const btn = e.target.closest(".delete-btn");
    if(btn){
        deleteCommentId = btn.dataset.id;
        document.getElementById("deletePopup").style.display = "flex";
    }
});

// Tombol batal
document.getElementById("cancelDeleteBtn")?.addEventListener("click", () => {
    deleteCommentId = null;
    document.getElementById("deletePopup").style.display = "none";
});

// Tombol konfirmasi hapus
document.getElementById("confirmDeleteBtn")?.addEventListener("click", async () => {
    if (!deleteCommentId) return;

    const res = await fetch("hapuskomentar.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ id: deleteCommentId })
    });

    const r = await res.text();
    if (r === "success") {
        location.reload();
    } else {
        alert("Gagal menghapus komentar!");
    }
});

  // Balas & Batal
  document.addEventListener("click",(e)=>{
    if (e.target.classList.contains("reply-btn")) {

    // Jika belum login → munculkan popup
    <?php if (!$isLoggedIn): ?>
        if (typeof showLoginPopup === "function") {
            showLoginPopup();
        } else {
            alert("Silakan login untuk membalas komentar.");
        }
        return;
    <?php endif; ?>

      const parentId=e.target.dataset.parent;
      const parentComment=e.target.closest(".komentar-item");

      // hapus form lama
      const existingForm=document.getElementById("replyForm");
      if(existingForm) existingForm.remove();

      // buat form baru
      const replyForm=document.createElement("form");
      replyForm.id="replyForm";
      replyForm.className="komentar-form";
      replyForm.innerHTML=`
        <textarea name="isi" placeholder="Tulis balasan..." required></textarea>
        <button type="submit">Kirim Balasan</button>
        <button type="button" id="batalReply">Batal</button>
      `;
      parentComment.appendChild(replyForm);

      // Batal
      replyForm.querySelector("#batalReply").addEventListener("click",()=>replyForm.remove());

      // Submit reply
      replyForm.addEventListener("submit",async(ev)=>{
        ev.preventDefault();
        const isiText=replyForm.querySelector("textarea").value.trim();
        if(!isiText) return;
        const res=await fetch("komentar.php?id=<?= $artikel_id ?>",{method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},body:new URLSearchParams({komentar:1,isi:isiText,parent_id:parentId})});
        const r=await res.text();
        if(r==="success") location.reload();
      });
    }

     // Hapus Komentar Admin
    document.addEventListener("click",function(e){
  if(e.target.classList.contains("hapus-btn")){
    if(!confirm("Yakin hapus komentar ini?")){
      e.preventDefault();
    }
  }
});

    // Toggle balasan
    if(e.target.classList.contains("toggle-replies")){
      const parentId=e.target.dataset.id;
      const container=document.querySelector(`.reply-container[data-parent='${parentId}']`);
      if(container.style.display==="none"){ container.style.display="block"; e.target.textContent="Sembunyikan Balasan";}
      else{ container.style.display="none"; e.target.textContent="Tampilkan Balasan";}
    }
  });
});
</script>
</body>
</html>

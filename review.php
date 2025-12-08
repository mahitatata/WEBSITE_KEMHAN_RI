<<<<<<< HEAD
<?php
include "koneksi.php";
session_start();

 // Default fallback
$backUrl = 'dashboard.php';

// Kalau ada parameter 'from' di URL, pakai itu
if (isset($_GET['from'])) {
    if ($_GET['from'] === 'beranda') {
        $backUrl = 'index.php';
    } elseif ($_GET['from'] === 'artikel') {
        $backUrl = 'artikel.php';
    } elseif ($_GET['from'] === 'review') {
        $backUrl = 'review.php';
    }
}

// ==== PROSES APPROVE / REJECT ====
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action'], $_POST['id'])) {
    $id = intval($_POST['id']);

    if ($_POST['action'] === "approve") {
        $stmt = $conn->prepare("UPDATE artikel SET status=? WHERE id=?");
        $status = "publish";
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        header("Location: review.php?msg=approved");
        exit;
    }

    if ($_POST['action'] === "reject") {
        $stmt = $conn->prepare("UPDATE artikel SET status=?, balasan=? WHERE id=?");
        $status = "rejected";
        $feedback = trim($_POST['balasan']) ?: 'Artikel ditolak tanpa keterangan';
        $stmt->bind_param("ssi", $status, $feedback, $id);
        $stmt->execute();
        header("Location: review.php?msg=rejected");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Review Artikel Pending</title>
  <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* ============ GLOBAL ============ */
    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      background: #f8f9fa;
      color: #333;
    }
    header {
      background-color: #8B0000;
      color: white;
      padding: 18px 32px;
      font-size: 20px;
      font-weight: bold;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .container {
      max-width: 1000px;
      margin: 1rem auto;
      background: white;
      border-radius: 14px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 40px 50px;
    }
    h2 {
      font-size: 26px;
      color: #8B0000;
      text-align: center;
      margin-bottom: 20px;
      position: relative;
      font-weight: 700;
    }
    h2::after {
      content: "";
      display: block;
      width: 180px;
      height: 3px;
      background-color: #8B0000;
      margin: 10px auto 0;
      border-radius: 2px;
    }

    /* ============ TABLE ============ */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      padding: 10px 12px;
      text-align: center;
      font-size: 14px;
    }
    th {
      background: #f2f2f2;
      font-weight: 600;
      color: #444;
    }
    tr:hover { background: #fff7f7; }

    .status {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
    }
    .status.pending {
      background: #fde8e8;
      color: #b91c1c;
      border: 1px solid #fca5a5;
    }
    /* Perbaikan tombol Review */
    .btn-small {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      background-color: #007bff;
      color: #fff;
      padding: 6px 12px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      border: none;
      transition: all 0.2s ease-in-out;
      white-space: nowrap;
    }

    .btn-small i {
      margin-right: 4px;
    }

    .btn-small:hover {
      background-color: #0056b3;
      transform: translateY(-1px);
    }

/* Responsif: agar tombol tidak rusak di layar kecil */
    @media (max-width: 600px) {
      .btn-small {
        font-size: 13px;
        padding: 6px 10px;
      }
    }

    /* ============ BUTTON GROUP ============ */
    .button-group {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin-top: 25px;
    }
    .btn {
      border: none;
      padding: 12px 28px;
      border-radius: 10px;
      cursor: pointer;
      font-size: 15px;
      font-weight: 600;
      color: white;
      transition: all 0.3s ease;
      box-shadow: 0 3px 10px rgba(0,0,0,0.15);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-approve { background: linear-gradient(135deg, #196c23ff, #196c23ff); }
    .btn-approve:hover { background: linear-gradient(135deg, #36B93A, #25A32C); transform: translateY(-2px); }
    .btn-reject { background: linear-gradient(135deg, #a00000, #a00000); }
    .btn-reject:hover { background: linear-gradient(135deg, #E23C3C, #C53030); transform: translateY(-2px); }

    .empty-message {
      text-align: center;
      font-size: 16px;
      color: #444;
      margin-top: 20px;
    }

   .back-btn-wrapper {
    max-width: 1000px;
    margin: 20px auto 0;
    padding: 0 50px;
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

    /* ============ IMAGE PREVIEW MODAL ============ */
  .review-image {
    display: block;
    margin: 20px auto; /* tengah secara horizontal */
    width: 400px; /* atur ukuran sesuai keinginan */
    max-width: 100%; /* biar tetap responsif */
    border-radius: 10px;
    cursor: pointer;
  }

    .image-modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.85);
      justify-content: center;
      align-items: center;
      z-index: 5000;
      backdrop-filter: blur(6px);
      transition: all 0.3s ease-in-out;
    }
    .image-modal.show { display: flex; }
    .image-modal img {
      max-width: 95vw;
      max-height: 90vh;
      border-radius: 20px;
      box-shadow: 0 0 40px rgba(0,0,0,0.6);
      animation: zoomImage 0.25s ease;
      object-fit: contain;
    }
    @keyframes zoomImage {
      from { transform: scale(0.7); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    .close-btn {
      position: absolute;
      top: 25px;
      right: 35px;
      background: rgba(255,255,255,0.2);
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 50%;
      width: 45px;
      height: 45px;
      color: white;
      font-size: 26px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(6px);
    }
    .close-btn:hover {
      background: rgba(255,255,255,0.4);
      transform: scale(1.15);
    }

    /* ==== MODAL PENOLAKAN (BARU, SAMA DENGAN LOGIN) ==== */
.modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(10px);
  justify-content: center;
  align-items: center;
  z-index: 4000;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; } 
  to { opacity: 1; }
}

.modal.show {
  display: flex;
}

.modal-content {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  padding: 28px 32px;
  width: 90%;
  max-width: 420px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
  animation: scaleUp 0.3s ease;
}

@keyframes scaleUp {
  from { opacity: 0; transform: scale(0.8); }
  to { opacity: 1; transform: scale(1); }
}

.modal-content h3 {
  color: #111;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 18px;
}

.modal-content textarea {
  width: 100%;
  height: 120px;
  border-radius: 10px;
  border: 1px solid #ccc;
  padding: 12px;
  font-size: 14px;
  resize: none;
  transition: 0.3s ease;
  margin-bottom: 24px;
}

.modal-content textarea:focus {
  outline: none;
  border-color: #8B0000;
  box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.15);
}

/* Tombol di bawah popup */
.modal-buttons {
  display: flex;
  justify-content: center;
  gap: 14px;
}

.modal-buttons button {
  flex: 1;
  padding: 12px 0;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.25s ease;
}

/* Tombol merah seperti login */
.modal-buttons .btn-reject {
  background: #8B0000;
  color: white;
}

.modal-buttons .btn-reject:hover {
  background: #a00000;
  transform: translateY(-1px);
}

/* Tombol batal abu seperti login */
.modal-buttons .btn-back {
  background: #d9d9d9;
  color: #111;
}

.modal-buttons .btn-back:hover {
  background: #c7c7c7;
  transform: translateY(-1px);
}

/* Teks kecil info di bawah textarea */
.info-optional {
  font-size: 13px;
  color: #555;
  margin-top: -10px;
  margin-bottom: 20px;
  font-style: italic;
}
/* Background hitam transparan */
.pdf-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    padding-top: 40px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
}

/* Box putih isi PDF */
.pdf-modal-content {
    margin: auto;
    background: white;
    width: 80%;
    height: 85%;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

/* Iframe PDF */
.pdf-frame {
    width: 100%;
    height: 100%;
    border: none;
}

/* Tombol */
.open-pdf-btn {
    padding: 10px 18px;
    background: #8B0000;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
}
.open-pdf-btn:hover {
    background: #5c0606ff;
}
  </style>
</head>
<body>
<header>📜 Review Artikel Pending</header>
<!-- Modal Penolakan -->
<div class="modal" id="rejectModal">
  <div class="modal-content">
    <h3>Masukkan Alasan Penolakan (Opsional)</h3>
    <textarea id="rejectReason" placeholder="Tulis alasan penolakan di sini..."></textarea>
    <p class="info-optional">Jika tidak diisi, artikel tetap akan ditolak tanpa alasan tambahan.</p>
    <div class="modal-buttons">
      <button class="btn-reject" onclick="submitReject()">Kirim</button>
      <button class="btn-back" onclick="closeModal()">Batal</button>
    </div>
  </div>
</div>

  <div class="back-btn-wrapper">
    <a href="<?= $backUrl ?>" class="btn-back-shopee">
        <svg class="arrow-shopee" viewBox="0 0 24 24">
            <path d="M15 6l-6 6 6 6" />
        </svg>
    </a>
</div>

<!-- Modal Gambar -->
<div class="image-modal" id="imageModal">
  <button class="close-btn" onclick="closeImageModal()">&times;</button>
  <img id="modalImg" src="" alt="Preview">
</div>

<div class="container">
<?php
// ====== TAMPILKAN ARTIKEL DETAIL ======
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT a.*, r.nama AS penulis FROM artikel a LEFT JOIN regsitrasi r ON a.pegawai_id = r.id WHERE a.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $artikel = $result->fetch_assoc();

    if (!$artikel) { 
        echo "<h2>Artikel tidak ditemukan</h2>";
    } else { ?>
        <h2>Review Artikel</h2>
        <p><strong>Judul:</strong> <?= htmlspecialchars($artikel['judul']) ?></p>
        <p><strong>Penulis:</strong> <?= htmlspecialchars($artikel['penulis'] ?? 'Pegawai') ?></p>
        <p><strong>Kategori:</strong> <?= htmlspecialchars($artikel['kategori']) ?></p>
        <p><strong>Tipe:</strong> <?= htmlspecialchars(ucfirst($artikel['tipe'])) ?></p>
        <p><strong>Tanggal:</strong> <?= htmlspecialchars($artikel['created_at']) ?></p>

        <?php if (!empty($artikel['gambar'])): ?>
          <img src="uploads/<?= htmlspecialchars($artikel['gambar']) ?>" alt="Gambar Artikel" class="review-image" style="max-width:300px; border-radius:10px; cursor:pointer;">
        <?php endif; ?>

        <div class="content-box" style="margin-top:20px; line-height:1.7;"><?= nl2br(htmlspecialchars($artikel['isi_artikel'])) ?></div>
         
        <?php if (!empty($artikel['pdf'])): ?>
<div class="lampiran-box" style="margin-top: 20px;">
    <button class="open-pdf-btn">Lihat Lampiran</button>
</div>

<div class="pdf-modal" id="pdfModal">
    <div class="pdf-modal-content">
        <span class="pdf-close">&times;</span>
        <iframe 
            src="uploads/pdf/<?= htmlspecialchars($artikel['pdf']) ?>" 
            class="pdf-frame">
        </iframe>
    </div>
</div>
<?php endif; ?>

        <form id="reviewForm" method="POST">
          <input type="hidden" name="id" value="<?= intval($artikel['id']) ?>">
          <input type="hidden" name="balasan" id="hiddenBalasan">
          <div class="button-group">
            <button type="submit" name="action" value="approve" class="btn btn-approve"><i class="fa fa-check"></i> Setujui</button>
            <button type="button" class="btn btn-reject" onclick="openModal(<?= intval($artikel['id']) ?>)"><i class="fa fa-times"></i> Tolak</button>
          </div>
        </form>
<?php }
} else {
    $sql = "SELECT a.id, a.judul, r.nama AS penulis, a.kategori, a.tipe, a.created_at, a.status 
            FROM artikel a
            LEFT JOIN regsitrasi r ON a.pegawai_id = r.id
            WHERE a.status = 'pending'
            ORDER BY a.created_at DESC";
    $res = $conn->query($sql); ?>
    <h2>Daftar Artikel Pending</h2>
    <?php if ($res && $res->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Kategori</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Tipe</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php $no=1; while($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['judul']) ?></td>
            <td><?= htmlspecialchars($row['penulis'] ?? 'Pegawai') ?></td>
            <td><?= htmlspecialchars(ucfirst($row['kategori'])) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td><span class="status pending"><?= htmlspecialchars($row['status']) ?></span></td>
            <td><?= htmlspecialchars(ucfirst($row['tipe'])) ?></td>
            <td><a href="review.php?id=<?= intval($row['id']) ?>" class="btn-small"><i class="fa fa-eye"></i> Review</a></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="empty-message">Tidak ada artikel yang menunggu review ✅</div>
    <?php endif;
}
?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  let rejectId = null;
  const rejectModal = document.getElementById("rejectModal");
  const imageModal = document.getElementById("imageModal");
  const modalImg = document.getElementById("modalImg");

  // === Modal Tolak ===
  window.openModal = function(id) {
    rejectId = id;
    rejectModal.classList.add("show");
  };

  window.closeModal = function() {
    rejectModal.classList.remove("show");
  };

  window.submitReject = function() {
    const form = document.getElementById("reviewForm");
    const reason = document.getElementById("rejectReason").value.trim();
    document.getElementById("hiddenBalasan").value = reason || "Artikel ditolak tanpa keterangan";

    const actionInput = document.createElement("input");
    actionInput.type = "hidden";
    actionInput.name = "action";
    actionInput.value = "reject";
    form.appendChild(actionInput);

    rejectModal.classList.remove("show");
    form.submit();
  };

  // === Modal PDF ===
  document.querySelector(".open-pdf-btn").addEventListener("click", function () {
    document.getElementById("pdfModal").style.display = "block";
});

document.querySelector(".pdf-close").addEventListener("click", function () {
    document.getElementById("pdfModal").style.display = "none";
});

window.onclick = function(event) {
    const modal = document.getElementById("pdfModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

  // === Modal Gambar ===
  document.querySelectorAll(".review-image").forEach(img => {
    img.addEventListener("click", () => {
      modalImg.src = img.src;
      imageModal.classList.add("show");
    });
  });

  window.closeImageModal = function() {
    imageModal.classList.remove("show");
  };

  imageModal.addEventListener("click", (e) => {
    if (e.target === imageModal) imageModal.classList.remove("show");
  });

  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      rejectModal.classList.remove("show");
      imageModal.classList.remove("show");
    }
  });
});
</script>
</body>
</html>
=======
<?php
include "koneksi.php";
session_start();

 // Default fallback
$backUrl = 'dashboard.php';

// Kalau ada parameter 'from' di URL, pakai itu
if (isset($_GET['from'])) {
    if ($_GET['from'] === 'beranda') {
        $backUrl = 'index.php';
    } elseif ($_GET['from'] === 'artikel') {
        $backUrl = 'artikel.php';
    } elseif ($_GET['from'] === 'review') {
        $backUrl = 'review.php';
    }
}

// ==== PROSES APPROVE / REJECT ====
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action'], $_POST['id'])) {
    $id = intval($_POST['id']);

    if ($_POST['action'] === "approve") {
        $stmt = $conn->prepare("UPDATE artikel SET status=? WHERE id=?");
        $status = "publish";
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        header("Location: review.php?msg=approved");
        exit;
    }

    if ($_POST['action'] === "reject") {
        $stmt = $conn->prepare("UPDATE artikel SET status=?, balasan=? WHERE id=?");
        $status = "rejected";
        $feedback = trim($_POST['balasan']) ?: 'Artikel ditolak tanpa keterangan';
        $stmt->bind_param("ssi", $status, $feedback, $id);
        $stmt->execute();
        header("Location: review.php?msg=rejected");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Review Artikel Pending</title>
  <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* ============ GLOBAL ============ */
    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      background: #f8f9fa;
      color: #333;
    }
    header {
      background-color: #8B0000;
      color: white;
      padding: 18px 32px;
      font-size: 20px;
      font-weight: bold;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .container {
      max-width: 1000px;
      margin: 1rem auto;
      background: white;
      border-radius: 14px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 40px 50px;
    }
    h2 {
      font-size: 26px;
      color: #8B0000;
      text-align: center;
      margin-bottom: 20px;
      position: relative;
      font-weight: 700;
    }
    h2::after {
      content: "";
      display: block;
      width: 180px;
      height: 3px;
      background-color: #8B0000;
      margin: 10px auto 0;
      border-radius: 2px;
    }

    /* ============ TABLE ============ */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      padding: 10px 12px;
      text-align: center;
      font-size: 14px;
    }
    th {
      background: #f2f2f2;
      font-weight: 600;
      color: #444;
    }
    tr:hover { background: #fff7f7; }

    .status {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
    }
    .status.pending {
      background: #fde8e8;
      color: #b91c1c;
      border: 1px solid #fca5a5;
    }
    /* Perbaikan tombol Review */
    .btn-small {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      background-color: #007bff;
      color: #fff;
      padding: 6px 12px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      border: none;
      transition: all 0.2s ease-in-out;
      white-space: nowrap;
    }

    .btn-small i {
      margin-right: 4px;
    }

    .btn-small:hover {
      background-color: #0056b3;
      transform: translateY(-1px);
    }

/* Responsif: agar tombol tidak rusak di layar kecil */
    @media (max-width: 600px) {
      .btn-small {
        font-size: 13px;
        padding: 6px 10px;
      }
    }

    /* ============ BUTTON GROUP ============ */
    .button-group {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin-top: 25px;
    }
    .btn {
      border: none;
      padding: 12px 28px;
      border-radius: 10px;
      cursor: pointer;
      font-size: 15px;
      font-weight: 600;
      color: white;
      transition: all 0.3s ease;
      box-shadow: 0 3px 10px rgba(0,0,0,0.15);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-approve { background: linear-gradient(135deg, #196c23ff, #196c23ff); }
    .btn-approve:hover { background: linear-gradient(135deg, #36B93A, #25A32C); transform: translateY(-2px); }
    .btn-reject { background: linear-gradient(135deg, #a00000, #a00000); }
    .btn-reject:hover { background: linear-gradient(135deg, #E23C3C, #C53030); transform: translateY(-2px); }

    .empty-message {
      text-align: center;
      font-size: 16px;
      color: #444;
      margin-top: 20px;
    }

   .back-btn-wrapper {
    max-width: 1000px;
    margin: 20px auto 0;
    padding: 0 50px;
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

    /* ============ IMAGE PREVIEW MODAL ============ */
  .review-image {
    display: block;
    margin: 20px auto; /* tengah secara horizontal */
    width: 400px; /* atur ukuran sesuai keinginan */
    max-width: 100%; /* biar tetap responsif */
    border-radius: 10px;
    cursor: pointer;
  }

    .image-modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.85);
      justify-content: center;
      align-items: center;
      z-index: 5000;
      backdrop-filter: blur(6px);
      transition: all 0.3s ease-in-out;
    }
    .image-modal.show { display: flex; }
    .image-modal img {
      max-width: 95vw;
      max-height: 90vh;
      border-radius: 20px;
      box-shadow: 0 0 40px rgba(0,0,0,0.6);
      animation: zoomImage 0.25s ease;
      object-fit: contain;
    }
    @keyframes zoomImage {
      from { transform: scale(0.7); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    .close-btn {
      position: absolute;
      top: 25px;
      right: 35px;
      background: rgba(255,255,255,0.2);
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 50%;
      width: 45px;
      height: 45px;
      color: white;
      font-size: 26px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(6px);
    }
    .close-btn:hover {
      background: rgba(255,255,255,0.4);
      transform: scale(1.15);
    }

    /* ==== MODAL PENOLAKAN (BARU, SAMA DENGAN LOGIN) ==== */
.modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(10px);
  justify-content: center;
  align-items: center;
  z-index: 4000;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; } 
  to { opacity: 1; }
}

.modal.show {
  display: flex;
}

.modal-content {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  padding: 28px 32px;
  width: 90%;
  max-width: 420px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
  animation: scaleUp 0.3s ease;
}

@keyframes scaleUp {
  from { opacity: 0; transform: scale(0.8); }
  to { opacity: 1; transform: scale(1); }
}

.modal-content h3 {
  color: #111;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 18px;
}

.modal-content textarea {
  width: 100%;
  height: 120px;
  border-radius: 10px;
  border: 1px solid #ccc;
  padding: 12px;
  font-size: 14px;
  resize: none;
  transition: 0.3s ease;
  margin-bottom: 24px;
}

.modal-content textarea:focus {
  outline: none;
  border-color: #8B0000;
  box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.15);
}

/* Tombol di bawah popup */
.modal-buttons {
  display: flex;
  justify-content: center;
  gap: 14px;
}

.modal-buttons button {
  flex: 1;
  padding: 12px 0;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.25s ease;
}

/* Tombol merah seperti login */
.modal-buttons .btn-reject {
  background: #8B0000;
  color: white;
}

.modal-buttons .btn-reject:hover {
  background: #a00000;
  transform: translateY(-1px);
}

/* Tombol batal abu seperti login */
.modal-buttons .btn-back {
  background: #d9d9d9;
  color: #111;
}

.modal-buttons .btn-back:hover {
  background: #c7c7c7;
  transform: translateY(-1px);
}

/* Teks kecil info di bawah textarea */
.info-optional {
  font-size: 13px;
  color: #555;
  margin-top: -10px;
  margin-bottom: 20px;
  font-style: italic;
}
/* Background hitam transparan */
.pdf-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    padding-top: 40px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
}

/* Box putih isi PDF */
.pdf-modal-content {
    margin: auto;
    background: white;
    width: 80%;
    height: 85%;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

/* Iframe PDF */
.pdf-frame {
    width: 100%;
    height: 100%;
    border: none;
}

/* Tombol */
.open-pdf-btn {
    padding: 10px 18px;
    background: #8B0000;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
}
.open-pdf-btn:hover {
    background: #5c0606ff;
}
  </style>
</head>
<body>
<header>📜 Review Artikel Pending</header>
<!-- Modal Penolakan -->
<div class="modal" id="rejectModal">
  <div class="modal-content">
    <h3>Masukkan Alasan Penolakan (Opsional)</h3>
    <textarea id="rejectReason" placeholder="Tulis alasan penolakan di sini..."></textarea>
    <p class="info-optional">Jika tidak diisi, artikel tetap akan ditolak tanpa alasan tambahan.</p>
    <div class="modal-buttons">
      <button class="btn-reject" onclick="submitReject()">Kirim</button>
      <button class="btn-back" onclick="closeModal()">Batal</button>
    </div>
  </div>
</div>

  <div class="back-btn-wrapper">
    <a href="<?= $backUrl ?>" class="btn-back-shopee">
        <svg class="arrow-shopee" viewBox="0 0 24 24">
            <path d="M15 6l-6 6 6 6" />
        </svg>
    </a>
</div>

<!-- Modal Gambar -->
<div class="image-modal" id="imageModal">
  <button class="close-btn" onclick="closeImageModal()">&times;</button>
  <img id="modalImg" src="" alt="Preview">
</div>

<div class="container">
<?php
// ====== TAMPILKAN ARTIKEL DETAIL ======
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT a.*, r.nama AS penulis FROM artikel a LEFT JOIN regsitrasi r ON a.pegawai_id = r.id WHERE a.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $artikel = $result->fetch_assoc();

    if (!$artikel) { 
        echo "<h2>Artikel tidak ditemukan</h2>";
    } else { ?>
        <h2>Review Artikel</h2>
        <p><strong>Judul:</strong> <?= htmlspecialchars($artikel['judul']) ?></p>
        <p><strong>Penulis:</strong> <?= htmlspecialchars($artikel['penulis'] ?? 'Pegawai') ?></p>
        <p><strong>Kategori:</strong> <?= htmlspecialchars($artikel['kategori']) ?></p>
        <p><strong>Tipe:</strong> <?= htmlspecialchars(ucfirst($artikel['tipe'])) ?></p>
        <p><strong>Tanggal:</strong> <?= htmlspecialchars($artikel['created_at']) ?></p>

        <?php if (!empty($artikel['gambar'])): ?>
          <img src="uploads/<?= htmlspecialchars($artikel['gambar']) ?>" alt="Gambar Artikel" class="review-image" style="max-width:300px; border-radius:10px; cursor:pointer;">
        <?php endif; ?>

        <div class="content-box" style="margin-top:20px; line-height:1.7;"><?= nl2br(htmlspecialchars($artikel['isi_artikel'])) ?></div>
         
        <?php if (!empty($artikel['pdf'])): ?>
<div class="lampiran-box" style="margin-top: 20px;">
    <button class="open-pdf-btn">Lihat Lampiran</button>
</div>

<div class="pdf-modal" id="pdfModal">
    <div class="pdf-modal-content">
        <span class="pdf-close">&times;</span>
        <iframe 
            src="uploads/pdf/<?= htmlspecialchars($artikel['pdf']) ?>" 
            class="pdf-frame">
        </iframe>
    </div>
</div>
<?php endif; ?>

        <form id="reviewForm" method="POST">
          <input type="hidden" name="id" value="<?= intval($artikel['id']) ?>">
          <input type="hidden" name="balasan" id="hiddenBalasan">
          <div class="button-group">
            <button type="submit" name="action" value="approve" class="btn btn-approve"><i class="fa fa-check"></i> Setujui</button>
            <button type="button" class="btn btn-reject" onclick="openModal(<?= intval($artikel['id']) ?>)"><i class="fa fa-times"></i> Tolak</button>
          </div>
        </form>
<?php }
} else {
    $sql = "SELECT a.id, a.judul, r.nama AS penulis, a.kategori, a.tipe, a.created_at, a.status 
            FROM artikel a
            LEFT JOIN regsitrasi r ON a.pegawai_id = r.id
            WHERE a.status = 'pending'
            ORDER BY a.created_at DESC";
    $res = $conn->query($sql); ?>
    <h2>Daftar Artikel Pending</h2>
    <?php if ($res && $res->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Kategori</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Tipe</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php $no=1; while($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['judul']) ?></td>
            <td><?= htmlspecialchars($row['penulis'] ?? 'Pegawai') ?></td>
            <td><?= htmlspecialchars(ucfirst($row['kategori'])) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
            <td><span class="status pending"><?= htmlspecialchars($row['status']) ?></span></td>
            <td><?= htmlspecialchars(ucfirst($row['tipe'])) ?></td>
            <td><a href="review.php?id=<?= intval($row['id']) ?>" class="btn-small"><i class="fa fa-eye"></i> Review</a></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="empty-message">Tidak ada artikel yang menunggu review ✅</div>
    <?php endif;
}
?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  let rejectId = null;
  const rejectModal = document.getElementById("rejectModal");
  const imageModal = document.getElementById("imageModal");
  const modalImg = document.getElementById("modalImg");

  // === Modal Tolak ===
  window.openModal = function(id) {
    rejectId = id;
    rejectModal.classList.add("show");
  };

  window.closeModal = function() {
    rejectModal.classList.remove("show");
  };

  window.submitReject = function() {
    const form = document.getElementById("reviewForm");
    const reason = document.getElementById("rejectReason").value.trim();
    document.getElementById("hiddenBalasan").value = reason || "Artikel ditolak tanpa keterangan";

    const actionInput = document.createElement("input");
    actionInput.type = "hidden";
    actionInput.name = "action";
    actionInput.value = "reject";
    form.appendChild(actionInput);

    rejectModal.classList.remove("show");
    form.submit();
  };

  // === Modal PDF ===
  document.querySelector(".open-pdf-btn").addEventListener("click", function () {
    document.getElementById("pdfModal").style.display = "block";
});

document.querySelector(".pdf-close").addEventListener("click", function () {
    document.getElementById("pdfModal").style.display = "none";
});

window.onclick = function(event) {
    const modal = document.getElementById("pdfModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

  // === Modal Gambar ===
  document.querySelectorAll(".review-image").forEach(img => {
    img.addEventListener("click", () => {
      modalImg.src = img.src;
      imageModal.classList.add("show");
    });
  });

  window.closeImageModal = function() {
    imageModal.classList.remove("show");
  };

  imageModal.addEventListener("click", (e) => {
    if (e.target === imageModal) imageModal.classList.remove("show");
  });

  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      rejectModal.classList.remove("show");
      imageModal.classList.remove("show");
    }
  });
});
</script>
</body>
</html>
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

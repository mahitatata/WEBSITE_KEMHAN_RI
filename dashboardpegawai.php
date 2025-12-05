<?php
include "koneksi.php";
session_start();

 // Default fallback
$backUrl = 'pegawai.php';

// Kalau ada parameter 'from' di URL, pakai itu
if (isset($_GET['from'])) {
    if ($_GET['from'] === 'dashboardpegawai') {
        $backUrl = 'dashboardpegawai.php';
}
}

if (!isset($_SESSION['pegawai_id'])) {
    header("Location: login.php");
    exit;
}

$pegawai_id = $_SESSION['pegawai_id'];

// Filter & search
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query dasar
$sql = "SELECT * FROM artikel WHERE pegawai_id = ?";
$params = [$pegawai_id];
$types = "i";

// Filter status
if ($filter_status !== '') {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

// Search judul
if ($search !== '') {
    $sql .= " AND judul LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// Hapus artikel
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM artikel WHERE id = ? AND pegawai_id = ?");
    $stmt->bind_param("ii", $id, $pegawai_id);
    $stmt->execute();
    header("Location: dashboardpegawai.php?msg=deleted");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Pegawai</title>
<link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body {
    font-family: 'Inter', sans-serif;
    margin: 0;
    background: #f5f6fa;
    color: #333;
  }

  header {
    background-color: #8B0000;
    color: white;
    padding: 16px 32px;
    font-size: 20px;
    font-weight: bold;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  }

  .container {
    max-width: 1100px;
    margin: 50px auto;
    background: white;
    border-radius: 15px;
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
    padding-bottom: 10px;
    letter-spacing: 0.5px;
  }

  h2::after {
    content: "";
    display: block;
    width: 200px;
    height: 3px;
    background-color: #8B0000;
    margin: 10px auto 0;
    border-radius: 2px;
  }

  .filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 10px;
  }

  select, input[type="text"] {
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 20px;
    font-size: 14px;
  }

  input[type="text"]:focus, select:focus {
    border-color: #8B0000;
    outline: none;
  }

  .filter-bar button {
    padding: 8px 16px;
    background: #8B0000;
    color: white;
    border: none;
    border-radius: 20px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
  }
  .filter-bar button:hover { background: #a00000; }

  .alert {
    text-align: center;
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-weight: 600;
  }
  .alert.success { background: #e6ffed; color: #218838; border: 1px solid #b5f5c2; }

  .tambah-btn {
    display: inline-block;
    background: #8B0000;
    color: white;
    padding: 10px 18px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 20px;
    transition: all 0.3s ease;
  }

  .tambah-btn:hover {
    transform: scale(1.05);
    background: #a00000;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  th, td {
    padding: 12px;
    text-align: center;
    font-size: 14px;
  }

  th {
    background: #ffb8b8ff;
    font-weight: 600;
    color: #363636ff;
  }

  tr:hover {
    background: #fff7f7;
  }

  .status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-transform: capitalize;
  }

  .status.pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
  .status.publish { background: #e6ffe6; color: #1a7f37; border: 1px solid #b6f0b6; }
  .status.rejected { background: #fde8e8; color: #b91c1c; border: 1px solid #fca5a5; }

  .btn-small {
    display: inline-block;
    color: white;
    padding: 8px 14px;
    border-radius: 25px;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.3s ease;
    margin: 2px;
  }

  .btn-delete { background: linear-gradient(135deg, #dc3545, #ff4d4d); }
  .btn-view { background: linear-gradient(135deg, #007bff, #3399ff); }
  .btn-small:hover { transform: scale(1.05); opacity: 0.9; }

  .empty-message {
    text-align: center;
    font-size: 16px;
    color: #444;
    margin-top: 20px;
  }

  /* Modal */
 .modal-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(5px);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* Box */
.modal-box {
  background: #fff;
  border-radius: 18px;
  padding: 35px 45px;
  text-align: center;
  max-width: 420px;
  width: 90%;
  box-shadow: 0 8px 25px rgba(0,0,0,0.25);
  animation: popupFade 0.25s ease-out;
}

@keyframes popupFade {
  from { transform: scale(0.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

/* Title & Text */
.modal-box h3 {
  color: #000;  /* warna judul jadi hitam */
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 10px;
}

.modal-box p {
  color: #444;
  font-size: 15px;
  margin-bottom: 25px;
}

/* Button group */
.modal-buttons {
  display: flex;
  justify-content: center;
  gap: 15px;
}

/* Tombol sama gaya dengan popup login.php */
.btn-primary, .btn-secondary {
  padding: 10px 24px;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: 0.3s ease;
  text-decoration: none;
}

/* Hapus = warna merah tua (utama) */
.btn-primary {
  background-color: #8B0000;
  color: white;
}
.btn-primary:hover {
  background-color: #a00000;
}

/* Batal = abu muda */
.btn-secondary {
  background-color: #d3d3d3;
  color: #000;
}
.btn-secondary:hover {
  background-color: #bfbfbf;
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
    margin-top: -55px;
    margin-bottom: 10px;
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

.badge-internal {
    display: inline-block;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 700;
    background: #8B0000;
    color: white;
    border-radius: 12px;
    letter-spacing: 0.3px;
    margin-right: 6px;
}

.no-internal-badge {
    position: relative;
    font-weight: 700;
    color: #8B0000;
    z-index: 1;
}

.no-internal-badge::before {
    content: "";
    position: absolute;
    width: 26px;
    height: 26px;
    background: rgba(139,0,0,0.15); /* merah soft */
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: -1;
}

</style>
</head>
<body>

<header>📊 Artikel Saya</header>

<div class="container">

  <a href="<?= $backUrl ?>" class="btn-back-shopee">
        <svg class="arrow-shopee" viewBox="0 0 24 24">
            <path d="M15 6l-6 6 6 6" />
        </svg>
    </a>

  <h2>Daftar Artikel Anda</h2>

  <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <div class="alert success">Artikel berhasil dihapus 🗑️</div>
  <?php endif; ?>

  <div class="filter-bar">
    <form method="GET" style="display:flex;gap:10px;align-items:center;">
      <select name="status">
        <option value="">Semua Status</option>
        <option value="publish" <?= $filter_status=='publish'?'selected':'' ?>>Publish</option>
        <option value="pending" <?= $filter_status=='pending'?'selected':'' ?>>Pending</option>
        <option value="rejected" <?= $filter_status=='rejected'?'selected':'' ?>>Rejected</option>
        <option value="draft" <?= $filter_status=='draft'?'selected':'' ?>>Draft</option>
      </select>
      <input type="text" name="search" placeholder="Cari judul..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit"><i class="fa fa-search"></i> Cari</button>
    </form>

    <a href="madeartikel.html" class="tambah-btn"><i class="fa fa-plus"></i> Tambah Artikel Baru</a>
  </div>

  <?php if ($res && $res->num_rows > 0): ?>
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Judul</th>
        <th>Kategori</th>
        <th>Tanggal</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while($row = $res->fetch_assoc()): ?>
      <tr>
    <td class="<?= $row['tipe']=='internal' ? 'no-internal-badge' : '' ?>">
        <?= $no++; ?>
    </td>

    <td>
        <div class="judul-wrapper">
            <span><?= htmlspecialchars($row['judul']) ?></span>
        </div>
    </td>

    <td><?= htmlspecialchars(ucfirst($row['kategori'])) ?></td>
    <td><?= htmlspecialchars($row['created_at']) ?></td>
    <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>

    <td>
        <a href="lihatartikel.php?id=<?= $row['id'] ?>" class="btn-small btn-view"><i class="fa fa-eye"></i></a>
        <?php if ($row['status'] == 'pending' || $row['status'] == 'rejected' || $row['status'] == 'draft'): ?>
        <a href="#" class="btn-small btn-delete" onclick="showDeletePopup(<?= $row['id'] ?>)"><i class="fa fa-trash"></i></a>
        <?php endif; ?>
    </td>
</tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty-message">Belum ada artikel yang Anda tulis 📝</div>
  <?php endif; ?>
</div>

<!-- Modal Konfirmasi -->
<div id="deletePopup" class="modal-overlay">
  <div class="modal-box">
    <h3>Yakin ingin menghapus?</h3>
    <p>Artikel ini akan dihapus secara permanen.</p>
    <div class="modal-buttons">
      <button id="cancelBtn" class="btn-secondary">Batal</button>
      <a id="confirmDelete" href="#" class="btn-primary">Hapus</a>
    </div>
  </div>
</div>

<script>
function showDeletePopup(id) {
  const popup = document.getElementById('deletePopup');
  const link = document.getElementById('confirmDelete');
  popup.style.display = 'flex';
  link.href = '?hapus=' + id;
}
document.getElementById('cancelBtn').onclick = () => {
  document.getElementById('deletePopup').style.display = 'none';
};
</script>

</body>
</html>

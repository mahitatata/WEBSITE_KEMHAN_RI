<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
include "koneksi.php";

// Hitung jumlah artikel pending
// Hitung total artikel yang pending
$resArtikel = $conn->query("SELECT COUNT(*) AS total FROM artikel WHERE status = 'pending'");
$artikelCount = $resArtikel->fetch_assoc()['total'];

// Hitung jumlah pengguna
$resUser = $conn->query("SELECT COUNT(*) AS total FROM regsitrasi"); // pastikan nama tabel sesuai
$userCount = $resUser->fetch_assoc()['total'];

// Hitung jumlah riwayat
// hitung artikel publish
$res = $conn->query("SELECT COUNT(*) AS total FROM artikel WHERE status = 'publish'");
$published = $res ? (int)$res->fetch_assoc()['total'] : 0;

// hitung artikel rejected
$res = $conn->query("SELECT COUNT(*) AS total FROM artikel WHERE status = 'rejected'");
$rejected = $res ? (int)$res->fetch_assoc()['total'] : 0;

// hitung blacklist
$res = $conn->query("SELECT COUNT(*) AS total FROM blacklist");
$blacklist = $res ? (int)$res->fetch_assoc()['total'] : 0;

// total riwayat = published + rejected + blacklist
$riwayatCount = $published + $rejected + $blacklist;

// Hitung artikel pending (buat notifikasi bell)
$resPending = $conn->query("SELECT COUNT(*) AS total FROM artikel WHERE status='pending'");
$pendingCount = $resPending->fetch_assoc()['total'];

// ======== QUERY PEGAWAI ========
// Ambil data pegawai + waktu terakhir aktif berdasarkan Email
$query = "
SELECT 
    r.nama AS nama,
    r.email AS email,
    r.satker AS satker,
    p.last_active AS last_active
FROM regsitrasi r
LEFT JOIN pegawai p ON r.Email = p.Email
WHERE r.role = 'pegawai'
ORDER BY p.last_active DESC
";
$resPegawai = $conn->query($query);

if (!$resPegawai) {
    die('Query pegawai gagal: ' . $conn->error);
}

// Pastikan session email tersimpan
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $conn->query("UPDATE pegawai SET last_active = NOW() WHERE Email = '$email'");
}

// ======== FUNGSI STATUS AKTIF ========
function waktuAktif($lastActive) {
    if (!$lastActive) return "Belum pernah aktif";

    date_default_timezone_set('Asia/Jakarta'); // pastikan timezone
    $now = new DateTime();
    $last = new DateTime($lastActive);
    $diff = $now->getTimestamp() - $last->getTimestamp(); // selisih detik

    if ($diff < 60) return "Online"; // <1 menit
    if ($diff < 3600) return "Terakhir aktif " . floor($diff/60) . " menit lalu";
    if ($diff < 86400) return "Terakhir aktif " . floor($diff/3600) . " jam lalu";
    return "Terakhir aktif " . floor($diff/86400) . " hari lalu";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // simpan session
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            header("Location: dashboard.php");
            exit;
        } else {
            echo "Password salah!";
        }
    } else {
        echo "User tidak ditemukan!";
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'fetch_pegawai') {
    $query = "
        SELECT r.nama AS nama, r.satker AS satker, p.last_active AS last_active
        FROM regsitrasi r
        LEFT JOIN pegawai p ON r.email = p.Email
        WHERE r.role = 'pegawai'
        ORDER BY p.last_active DESC
    ";
    $resPegawai = $conn->query($query);

    while ($row = $resPegawai->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
        echo "<td>" . htmlspecialchars($row['satker']) . "</td>";

        $last = $row['last_active'];
        if (!$last) {
            echo "<td>Belum pernah aktif</td>";
        } else {
            echo "<td>" . date("Y-m-d H:i:s", strtotime($last)) . "</td>";
        }

        echo "</tr>";
    }
    exit; // penting supaya tidak mengeksekusi HTML setelah fetch
}
?>

<!doctype html>
<html lang="id">
<meta http-equiv="refresh" content="15">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Dashboard Admin</title>
    <link rel="icon" type="image/png" href="logo kemhan 1.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
      :root{
        --maroon:#8B0000;
        --dark-maroon:#7a0000;
        --bg:#f6f7f8;
        --card-bg:#ffffff;
        --muted:#666666;
        --accent:#a30202;
      }

      *{box-sizing:border-box}
      body{
        font-family: "Inter", Arial, sans-serif;
        margin:0;
        min-height:100vh;
        background: #efefef;
        color:#222;
      }

      /* Layout */
      .app {
        display: flex;
        min-height:100vh;
      }

/* === TOPBAR STYLE === */
.topbar {
  height: 64px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 28px;

  /* shadow biar jelas */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);

  position: fixed;
  top: 0;
  left: 240px;
  right: 0;
  z-index: 1000; /* pastikan selalu di atas konten */
  font-weight: bold;
}

.topbar-right {
  display:flex;
  align-items:center;
  gap:20px;
  margin-left: auto;
}

/* Bell Icon */
.icon-bell {
  position: relative;
  background:#fff;
  border:2px solid #999;
  border-radius:50%;
  padding:8px;
  font-size:20px;
  cursor:pointer;
  box-shadow:0 3px 6px rgba(0,0,0,0.15);
  transition: all 0.2s ease;
}

.icon-bell .badge {
  position: absolute;
  top: -5px;
  right: -8px;
  background: red;
  color: #fff;
  border-radius: 50%;
  padding: 3px 6px;
  font-size: 10px;
}

.icon-bell:hover {
  transform: scale(1.1);
  box-shadow:0 5px 10px rgba(0,0,0,0.25);
}

/* Profile pill */
.profile-pill {
  display:flex;
  align-items:center;
  gap:10px;
  background:#fff;
  border:2px solid var(--maroon);
  border-radius:50px;
  padding:6px 14px;
  font-weight:600;
  color:var(--maroon);
  box-shadow:0 3px 6px rgba(0,0,0,0.15);
  cursor:pointer;
  transition: all 0.2s ease;
}
.profile-pill:hover {
  background:var(--maroon);
  color:#fff;
  transform: translateY(-2px);
}

/* Avatar bulat */
.profile-pill .avatar {
  width:28px;
  height:28px;
  border-radius:50%;
  background:#a40000;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#fff;
  font-weight:bold;
  font-size:14px;
}

/* === PROFILE DROPDOWN === */
.profile-wrapper {
  position: relative;
  display: inline-block;
}

.profile-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: none;
  cursor: pointer;
  color: inherit;
  font: inherit;
}

.profile-dropdown {
  position: absolute;
  right: 0;
  top: 48px;
  background: #fff;
  border: 1px solid #7b7b7bff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  min-width: 140px;
  display: none;
  flex-direction: column;
  z-index: 1000;
}

.profile-dropdown.show {
  display: flex;
}

.profile-dropdown a {
  display: block;
  padding: 10px 14px;
  color: #333;
  text-decoration: none;
  font-size: 14px;
  transition: background 0.2s;
}

.profile-dropdown a:hover {
  background: var(--maroon);
  color: #fff;
  border-radius: 8px;
}

/* Dropdown notifikasi */
.notif-dropdown {
  display: none;
  position: absolute;
  right: 0;
  top: 30px;
  width: 250px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  padding: 10px;
}

.icon-bell:hover .notif-dropdown {
  display: block;
}

/* === TABLE STYLE === */
.table-wrapper {
  margin-top:20px;
  background:#fff;
  border:2px solid #999;
  border-radius:12px;
  overflow:hidden;
  box-shadow:0 6px 12px rgba(0,0,0,0.15);
}

.table-wrapper table {
  width:100%;
  border-collapse:collapse;
}

.table-wrapper th {
  background:#f1f1f1;
  padding:12px;
  font-weight:700;
  border-bottom:2px solid #ccc;
}

.table-wrapper td {
  padding:12px;
  border-bottom:1px solid #eee;
}

.table-wrapper tr:hover {
  background:#fafafa;
}

      /* =============== SIDEBAR =============== */
.sidebar {
  width: 240px;
  background: linear-gradient(180deg, var(--maroon), var(--dark-maroon));
  color: #fff;
  padding: 16px 20px;
  border-right: 1px solid rgba(0, 0, 0, 0.08);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
}

/* --- Dropdown Container --- */
.dropdown-menu {
  display: none;              /* awalnya hidden */
  flex-direction: column;
  margin: 0;
  padding-left: 12px;
  border-left: 2px solid rgba(255,255,255,0.15);
  gap: 10px;
}

/* dropdown aktif */
.dropdown-menu.active {
  display: flex;              /* muncul pas aktif */
}

.nav-item.has-dropdown {
  flex-direction: row;   /* teks + panah sejajar */
  align-items: flex-start;
  flex-wrap: wrap;       /* anak berikutnya boleh turun ke bawah */
}

/* dropdown ikut flow normal */
.nav-item.has-dropdown .dropdown-menu {
  position: relative;  /* bukan absolute lagi */
  width: 100%;
  margin-top: 5px;
  display: none;
  flex-direction: column;
  border-radius: none;
  padding: 0;          /* hapus background & padding */
  background: none;    /* ga ada background */
}

.nav-item.has-dropdown .dropdown-menu.active {
  display: flex;
}

/* --- Dropdown Item --- */
.dropdown-item {
  font-size: 14px;
  color: rgba(255,255,255,0.9);
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: all 0.25s ease;
  padding: 8px 0;

  text-decoration: none;  /* ⬅️ hilangkan garis bawah */
}

.dropdown-item:visited,
.dropdown-item:active,
.dropdown-item:focus {
  text-decoration: none;  /* ⬅️ juga untuk state lain */
  color: rgba(255,255,255,0.9);
}

/* bullet kecil */
.dropdown-item::before {
  content: "•";   
  font-size: 16px;
  line-height: 1;
  color: #ffc107;
}

/* hover effect */
.dropdown-item:hover {
  color: #fff;
  transform: translateX(5px);
}

/* dropdown aktif */
.dropdown-menu.active {
  max-height: 500px;
  opacity: 1;
}


/* Logo/Brand */
.brand {
  display: flex;
  align-items: center;
  font-weight: 700;
  font-size: 22px;
  letter-spacing: 0.3px;
  margin-bottom: 40px;
  color: #fff;
}

.brand img {
  width: 36px;
  height: 36px;
  margin-right: 12px;
  border-radius: 6px;
}

/* Sidebar Nav */
.sidebar nav {
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 8px;
  margin-bottom: 8px;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.95);
  font-weight: 500;
  font-size: 15px;
  transition: all 0.3s ease;
}

.nav-item:hover,
.nav-item.active {
  background: rgba(255, 255, 255, 0.15);
  padding-left: 20px; /* efek geser halus */
}

.nav-item .chev {
  margin-left: auto;
  opacity: 0.7;
  font-size: 12px;
}

/* =============== MAIN AREA =============== */
.main {
  flex: 1;
  margin-left: 240px; /* kasih ruang buat sidebar */
  padding-top: 64px;
  background: #f6f6f6;
  min-height: 100vh;
}

   /* =============== STATUS HEADER =============== */
.status-header {
  display: flex;
  justify-content: center;
  align-items: center;  
  margin: 15px 0 20px;
  position: relative;
}

.status-header h2 {
  position: absolute;
  left: 50%;
  transform: translate(-50%);
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: var(--maroon);
  line-height: 1.2;
}

  /* CONTENT */
  .content {
  padding: 28px;
  background: #f6f7f8; /* abu-abu muda, biar shadow kontras */
  flex: 1;
}

.section {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 28px; /* jarak antar blok */
  box-shadow: 0 6px 14px rgba(0,0,0,0.15); /* shadow lebih tegas */
  border: 1px solid rgba(0,0,0,0.08);
}

      .cards{
        display:flex;
        gap:18px;
        align-items:stretch;
        margin-bottom:28px;
        flex-wrap:wrap;
      }
      .card{
        background:var(--card-bg);
        padding:20px;
        border-radius:12px;
        box-shadow: 0 6px 18px rgba(20,20,20,0.04);
        min-width:210px;
        flex:1 1 220px;
        border:1px solid rgba(0,0,0,0.06);
        display:flex;
        justify-content:space-between;
        align-items:center;
      }
      .card .count{ font-size:28px; font-weight:700; color:var(--maroon); }
      .card .label{ font-size:13px; color:var(--muted); margin-top:6px; }

      .center-controls{ display:flex; gap:12px; align-items:center; margin: 6px 0 28px; }
      .btn{
        background:var(--maroon);
        color:#fff;
        padding:10px 18px;
        border-radius:8px;
        border:none;
        cursor:pointer;
        font-weight:700;
      }
      .btn.ghost{
        background:transparent;
        color:var(--maroon);
        border:1px solid rgba(0,0,0,0.08);
      }
    
      /* Section title + small action on right */
      .section {
        background: #fff;
        padding:18px;
        border-radius:10px;
        border:1px solid rgba(0,0,0,0.04);
      }
      .section-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:12px;
      }
      .section-head h3{ margin:0; color:var(--maroon); }
      .see-all{
        background:var(--maroon);
        color:#fff;
        padding:8px 12px;
        border-radius:999px;
        font-weight:700;
        text-decoration:none;
      }

      /* Table */
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  border: 2px solid #444;   /* ✅ Outer border */
  border-radius: 8px;       /* ✅ Sudut melengkung */
  overflow: hidden;         /* biar border radius rapi */
}

thead th {
  text-align: left;
  padding: 12px 10px;
  color: #333;
  border-bottom: 2px solid rgba(0, 0, 0, 0.06);
  font-weight: 700;
  background: #f5f5f5;      /* ✅ biar header keliatan beda */
}

tbody td {
  padding: 12px 10px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  color: #444;
}

tbody tr:last-child td {
  border-bottom: none;      /* ✅ hapus border terakhir biar clean */
}

.small-muted {
  font-size: 13px;
  color: #777;
}

/* Table Pegawai */
.table-pegawai {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
.table-pegawai th, .table-pegawai td {
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid #acacacff;
}
.table-pegawai th {
  background: #df6d6daa;  
  color: #000000ff;           
  font-weight: 600;
  text-align: center;
}
.table-pegawai td {
  color: #444;   
  text-align: center;       
}

/* Hover effect */
tbody tr:hover {
  background: #fafafa;      /* ✅ kasih highlight pas hover */
}

  .cards {
  display: flex;
  gap: 20px;
  margin: 20px 0;
}

/* Bell Icon - modern & kecil */
.icon-bell {
  background: #fff;
  border: none;                /* tanpa border biar clean */
  border-radius: 50%;
  width: 34px;                 /* kecil & proporsional */
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;             /* lebih kecil */
  cursor: pointer;
  color: #555;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  transition: all 0.2s ease;
}
.icon-bell:hover {
  background: var(--maroon);
  color: #fff;
  transform: scale(1.05);
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.icon-bell {
  position: relative;
}

.icon-bell .badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: red;
  color: #fff;
  font-size: 12px;
  font-weight: bold;
  border-radius: 50%;
  padding: 2px 6px;
}

/* Profile pill - modern compact */
.profile-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff;
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 40px;
  padding: 5px 12px;
  font-weight: 600;
  font-size: 14px;             /* lebih kecil */
  color: #333;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: all 0.2s ease;
}
.profile-pill:hover {
  background: var(--maroon);
  color: #fff;
  transform: translateY(-2px);
}

/* Avatar bulat - kecil & clean */
.profile-pill .avatar {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: var(--maroon);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 600;
  font-size: 13px;
}

.card {
  flex: 1;
  background: #fdfdfd; /* bukan putih polos, lebih kontras */
  border: 2px solid #999; /* border lebih tebal dan lebih gelap */
  border-radius: 12px;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 6px 12px rgba(0,0,0,0.2); /* shadow lebih pekat */
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translateY(-6px); 
  box-shadow: 0 8px 16px rgba(0,0,0,0.25); /* makin tegas saat hover */
}

.count {
  font-size: 30px;
  font-weight: bold;
  color: #a40000; /* merah tetap dominan */
}

.label {
  font-size: 15px;
  font-weight: 700;
  color: #222; /* lebih gelap dari sebelumnya */
}

.divider-strong {
  border: none;
  border-top: 3px solid rgba(0,0,0,0.15); /* garis lebih tebal dan kontras */
  margin: 30px 0; 
}

     /* Responsive */
@media (max-width:900px){
  .sidebar {
    width:200px;
    padding:22px;
  }
  .topbar {
    left:200px;          /* ✅ biar sejajar sidebar yang mengecil */
    padding:0 16px;
  }
  .main {
    margin-left:200px;   /* ✅ sesuaikan dengan lebar sidebar */
    padding-top:64px;    /* ✅ jarak biar ga ketiban header */
  }
  .cards {
    flex-direction:column;
  }
  .card {
    min-width:unset;
    width:100%;
  }
}

@media (max-width:600px){
  .sidebar {
    display:none;
  }
  .topbar {
    left:0;              
    padding:0 12px;
  }
  .main {
    margin-left:0;       
    padding-top:64px;    
  }
  .content {
    padding:16px;
  }
}

.cards a.card {
  text-decoration: none;  /* biar ga ada underline */
  color: inherit;         /* biar teks ga jadi biru */
}

.icon-bell {
  position: relative;
}

.notif-dropdown {
  position: absolute;
  top: 40px;
  right: 0;
  width: 250px;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  display: none;
  z-index: 1000;
  padding: 10px;
}

.notif-dropdown h4 {
  margin: 0 0 10px;
  font-size: 14px;
  color: #8B0000;
}

.notif-dropdown ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.notif-dropdown ul li {
  padding: 6px 0;
  border-bottom: 1px solid #eee;
}

.notif-dropdown ul li a {
  color: #333;
  text-decoration: none;
  font-size: 14px;
}

.notif-dropdown ul li a:hover {
  color: #8B0000;
}

.notif-dropdown .see-all {
  display: block;
  margin-top: 8px;
  text-align: center;
  background: #8B0000;
  color: #fff;
  padding: 6px;
  border-radius: 5px;
  text-decoration: none;
  font-size: 13px;
}

.icon-bell {
  position: relative;
}

.notif-dropdown {
  position: absolute;
  top: 40px;
  right: 0;
  width: 250px;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  display: none;
  z-index: 1000;
  padding: 10px;
}

.notif-dropdown h4 {
  margin: 0 0 10px;
  font-size: 14px;
  color: #8B0000;
}

.notif-dropdown ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.notif-dropdown ul li {
  padding: 6px 0;
  border-bottom: 1px solid #eee;
}

.notif-dropdown ul li a {
  color: #333;
  text-decoration: none;
  font-size: 14px;
}

.notif-dropdown ul li a:hover {
  color: #8B0000;
}

.notif-dropdown .see-all {
  display: block;
  margin-top: 8px;
  text-align: center;
  background: #8B0000;
  color: #fff;
  padding: 6px;
  border-radius: 5px;
  text-decoration: none;
  font-size: 13px;
}

    </style>
</head>
<body>
<div class="app">
  <aside class="sidebar" aria-label="sidebar">
    <!-- Logo + teks HanZone -->
    <div class="brand">
      <img src="logo kemhan 1.png" alt="Logo Kemhan">
      HanZone
    </div>

    <nav>
      <div class="nav-item">Dashboard</div>
<div class="nav-item has-dropdown" id="manajemenKonten">Manajemen Konten <span class="chev">▾</span>
  <div class="dropdown-menu">
    <a class="dropdown-item" href="artikel.php">Artikel</a>
    <a class="dropdown-item" href="forum.php">Forum</a>
    <a class="dropdown-item" href="faq.php">FAQ</a>
  </div>
</div>
    </nav>
  </aside>

  <main class="main">
    <header class="topbar">
  <div class="topbar-right">
    <div class="icon-bell" id="notifBell">
      <i class="fa-solid fa-bell"></i>
      <?php if ($pendingCount > 0): ?>
        <span class="badge"><?= $pendingCount; ?></span>
      <?php endif; ?>

      <!-- Dropdown notifikasi -->
      <div class="notif-dropdown">
        <h4>Artikel Pending</h4>
        <ul>
          <?php
          $sqlNotif = "
  SELECT a.id, a.judul, r.nama 
  FROM artikel a
  LEFT JOIN regsitrasi r ON a.pegawai_id= r.id
  WHERE a.status='pending'
  ORDER BY a.created_at DESC
  LIMIT 5
";
          $resNotif = $conn->query($sqlNotif);
          if ($resNotif->num_rows > 0) {
            while ($row = $resNotif->fetch_assoc()) {
          echo "<li>
          <a href='review.php?id=".$row['id']."'>
            <strong>".htmlspecialchars($row['judul'])."</strong><br>
            <span style='font-size:12px;color:#666;'>oleh ".htmlspecialchars($row['nama'])."</span>
          </a>
        </li>";
}
          } else {
            echo "<li>Tidak ada artikel pending</li>";
          }
          ?>
        </ul>
        <a href="review.php" class="see-all">Lihat Semua</a>
      </div>
    </div>

   <div class="profile-wrapper">
  <div class="profile-pill">
    <div class="avatar">A</div>
    <span>Admin Utama</span>
    <i class="fa-solid fa-caret-down" style="font-size:12px;"></i>
  </div>

  <div class="profile-dropdown">
    <a href="logout.php">Keluar</a>
  </div>
</div>
</header>

    <section class="content">
      <!-- top cards -->
     <div class="cards" role="region" aria-label="ringkasan statistik">
  <a href="review.php" class="card">
    <div>
      <div class="count"><?= $artikelCount ?></div>
      <div class="label">Artikel</div>
    </div>
    <div style="font-size:22px;opacity:10">📄</div>
  </a>

  <a href="pengguna.php" class="card">
    <div>
      <div class="count"><?= $userCount ?></div>
      <div class="label">Pengguna</div>
    </div>
    <div style="font-size:22px;opacity:10">👥</div>
  </a>

  <a href="riwayat.php" class="card">
    <div>
      <div class="count"><?= $riwayatCount ?></div>
      <div class="label">Riwayat</div>
    </div>
    <div style="font-size:22px;opacity:510">⏱</div>
  </a>
</div>

      <!-- controls -->
      <div class="center-controls">
      </div>
      
      <!-- status aktif -->
      <hr class="divider">
      <div class="status-header">
  <h2>Status Aktif Pegawai</h2>
</div>
        <hr class="divider">
             
   <table class="table-pegawai" id="tabelPegawai">
<thead>
  <tr>
    <th>Nama Pegawai</th>
    <th>Email</th>
    <th>Satker</th>
    <th>Status</th>
  </tr>
</thead>
<tbody>
<?php while ($row = $resPegawai->fetch_assoc()): ?>
  <tr>
    <td><?= htmlspecialchars($row['nama']); ?></td>
    <td><?= htmlspecialchars($row['email']); ?></td>
    <td><?= htmlspecialchars($row['satker']); ?></td>
    <td>
  <?= waktuAktif($row['last_active']); ?>
</td>
  </tr>
<?php endwhile; ?>
</tbody>
</table>
          
        </div>
      </div>

    </section>
  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

  // === 1. Highlight nav-item yang diklik ===
  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => {
      document.querySelectorAll('.nav-item').forEach(x => x.style.opacity = 0.9);
      item.style.opacity = 1;
    });
  });

  // === 2. Auto refresh tabel pegawai tiap 60 detik ===
  setInterval(() => {
  fetch('dashboard.php?action=fetch_pegawai')
    .then(res => res.text())
    .then(html => {
      const tbody = document.querySelector('#tabelPegawai tbody');
      if (tbody) tbody.innerHTML = html;
    });
}, 60000);

  // === 3. Dropdown sidebar: Manajemen Konten ===
  const dropdownToggle = document.getElementById('manajemenKonten');
  const dropdownMenu = dropdownToggle?.querySelector('.dropdown-menu');
  if (dropdownToggle && dropdownMenu) {
    dropdownToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdownMenu.classList.toggle('active');
    });
  }

  // Klik di luar sidebar → tutup dropdown sidebar
  document.addEventListener('click', () => {
    if (dropdownMenu) dropdownMenu.classList.remove('active');
  });

  // === 4. Dropdown Notifikasi (klik, bukan hover) ===
  const notifBell = document.getElementById('notifBell');
  const notifDropdown = notifBell?.querySelector('.notif-dropdown');
  if (notifBell && notifDropdown) {
    notifBell.addEventListener('click', (e) => {
      e.stopPropagation();
      notifDropdown.style.display =
        notifDropdown.style.display === 'block' ? 'none' : 'block';
    });
  }

  // === 5. Dropdown Profil (klik) ===
  const profileButton = document.getElementById('profileButton');
  const profileDropdown = document.getElementById('profileDropdown');
  if (profileButton && profileDropdown) {
    profileButton.addEventListener('click', (e) => {
      e.stopPropagation();
      profileDropdown.classList.toggle('show');
    });
  }

  // === 6. Klik di luar → tutup semua dropdown ===
  document.addEventListener('click', (e) => {
    // Notifikasi
    if (notifDropdown && !notifBell.contains(e.target)) {
      notifDropdown.style.display = 'none';
    }
    // Profil
    if (profileDropdown && !profileButton.contains(e.target)) {
      profileDropdown.classList.remove('show');
    }
  });
});

const profilePill = document.querySelector('.profile-pill');
  const dropdown = document.querySelector('.profile-dropdown');

  if (profilePill && dropdown) {
    profilePill.addEventListener('click', (e) => {
      e.stopPropagation(); // biar gak ketutup langsung
      dropdown.classList.toggle('show');
    });

    // Klik di luar area -> tutup dropdown
    document.addEventListener('click', (e) => {
      if (!dropdown.contains(e.target) && !profilePill.contains(e.target)) {
        dropdown.classList.remove('show');
      }
    });
  }

document.addEventListener('click', (e) => {
  const manajemenKonten = document.getElementById('manajemenKonten');
  const dropdownMenu = manajemenKonten.querySelector('.dropdown-menu');
  if (!manajemenKonten.contains(e.target)) {
    dropdownMenu.classList.remove('active');
  }
});
</script>

</body>
</html>




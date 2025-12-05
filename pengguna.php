<?php
include 'koneksi.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    // ambil data pengguna
    $getUser = mysqli_query($conn, "SELECT email, nama FROM regsitrasi WHERE id='$id'");
    $user = mysqli_fetch_assoc($getUser);

    if ($user) {
        $email = $user['email'];
        $nama = $user['nama'];

        // masukkan ke blacklist
        mysqli_query($conn, "INSERT INTO blacklist (email, nama, aksi) VALUES ('$email', '$nama', 'Dihapus')");

        // hapus user dari registrasi
        mysqli_query($conn, "DELETE FROM regsitrasi WHERE id='$id'");

        echo "OK";
    } else {
        echo "User tidak ditemukan";
    }
    exit;
}

// Ambil data dari tabel registrasi (TANPA disetujui)
$query = "SELECT id, email, nama, role, satker, last_active FROM regsitrasi";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="logo kemhan 1.png">
  <title>Manajemen Pengguna</title>
  
  <!-- Bootstrap & SweetAlert -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  body {
    background-color: #f8f9fb;
    font-family: 'Inter', 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .header-bar {
    background: linear-gradient(90deg, #7a0000, #a30000);
    color: white;
    padding: 14px 36px;
    font-size: 22px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .table-container {
    margin: 25px auto;
    width: 92%;
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    overflow: hidden;
    border: 1px solid #e3e6ea;
  }

  table {
    width: 100%;
    border-collapse: collapse !important;
  }

  thead {
    background-color: #f6f7fb;
    font-weight: 600;
    color: #444;
    border-bottom: 1px solid #e0e3e7;
  }

  th, td {
    text-align: center;
    padding: 12px 16px;
    font-size: 14.5px;
    border-bottom: 1px solid #eceef1;
    vertical-align: middle;
  }

  thead th {
    padding: 14px 16px;
    font-weight: 600;
    background: #6672a248;
    border-top: 2px solid #dcdfe3;
    border-bottom: 2px solid #dcdfe3;
}

tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid #eceef1;
}

tbody td:first-child,
thead th:first-child {
    border-left: 1px solid #eceef1;
}

tbody td:last-child,
thead th:last-child {
    border-right: 1px solid #eceef1;
}

tbody tr:hover {
    background: #fafafa;
}

  tr:hover {
    background-color: #fafafa;
  }

   .back-btn-wrapper {
    width: 92%;
    margin: 22px auto 0;
    padding-left: 4px;
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

  .badge-success {
    background-color: #d1fae5;
    color: #0f5132;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
  }

  .badge-danger {
    background-color: #fee2e2;
    color: #842029;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
  }

  .btn-danger {
    background-color: #b30000;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    padding: 5px 16px;
    transition: 0.2s;
    font-size: 13.5px;
  }

  .btn-danger:hover {
    background-color: #8a0000;
  }

  .swal2-confirm {
    order: 2;
}

.swal2-cancel {
    order: 1;
}

  @media (max-width: 600px) {
  .back-btn-wrapper {
      padding-left: 4px;
  }
}

</style>

</head>
<body>

  <div class="header-bar">
    <i>👥</i> Manajemen Pengguna
  </div>

  <div class="back-btn-wrapper">
    <a href="<?= $backUrl ?>" class="btn-back-shopee">
        <svg class="arrow-shopee" viewBox="0 0 24 24">
            <path d="M15 6l-6 6 6 6" />
        </svg>
    </a>
</div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th style="width: 60px;">No</th>
          <th>Nama Email</th>
          <th>Role</th>
          <th>Satker</th>
          <th>Status</th>
          <th>Terakhir Aktif</th>
          <th style="width: 120px;">Aksi</th>
        </tr>
      </thead>

      <tbody>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) : ?>

<?php
$lastActive = strtotime($row['last_active']);
$now = time();
$diff = $now - $lastActive;
$statusText = ($diff <= 300) ? 'Online' : 'Offline';
?>

<tr>
  <td><?= $no++ ?></td>
  <td><?= htmlspecialchars($row['email']) ?></td>
  <td><?= !empty($row['role']) ? htmlspecialchars($row['role']) : 'User' ?></td>
  <td><?= htmlspecialchars($row['satker']) ?></td>

  <!-- STATUS REALTIME -->
  <td>
    <?php if ($statusText === 'Online'): ?>
      <span class="badge-success">Online</span>
    <?php else: ?>
      <span class="badge-danger">Offline</span>
    <?php endif; ?>
  </td>

  <!-- LAST ACTIVE -->
  <td><?= $row['last_active'] ? htmlspecialchars($row['last_active']) : '-' ?></td>

  <!-- AKSI -->
  <td style="display:flex; gap:6px; justify-content:center;">
    <button class="btn btn-danger btn-sm" onclick="hapusUser(<?= $row['id'] ?>)">Hapus</button>
  </td>
</tr>

<?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <script>
  function hapusUser(id) {
    Swal.fire({
      title: 'Yakin ingin menghapus akun ini?',
      text: 'Tindakan ini tidak dapat dibatalkan!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#b00000',
      cancelButtonColor: '#6c757d',
      cancelButtonText: 'Batal',
      confirmButtonText: 'Ya, Hapus!',
      reverseButtons: false
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('pengguna.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'id=' + id
        })
        .then(() => {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Akun berhasil dihapus.',
            showConfirmButton: false,
            timer: 1500
          });
          setTimeout(() => location.reload(), 1500);
        })
        .catch(() => {
          Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus.', 'error');
        });
      }
    });
  }
  </script>

</body>
</html>

<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'] ?? 'Pengguna';
$email = $_SESSION['email'];
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $isi = trim($_POST['isi']);

    if ($judul && $isi) {
        $stmt = $conn->prepare("INSERT INTO forum (judul, isi_text, penulis_email, penulis_nama, tanggal) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $judul, $isi, $email, $nama);
        if ($stmt->execute()) {
            header("Location: forum.php");
            exit;
        } else {
            $pesan = "Terjadi kesalahan saat menyimpan data.";
        }
    } else {
        $pesan = "Judul dan isi forum tidak boleh kosong.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buat Forum Baru</title>
  <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    /* ====== BASE STYLE ====== */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #f4f5f7;
      color: #222;
      line-height: 1.6;
    }

    /* ====== CONTAINER ====== */
    .container {
      max-width: 900px;
      margin: 40px auto;
      background: #fff;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 5px 25px rgba(0,0,0,0.08);
      animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h1 {
      font-size: 1.8rem;
      margin-bottom: 20px;
      border-bottom: 2px solid #a30202;
      display: inline-block;
      padding-bottom: 5px;
      color: #a30202;
    }
    /* ====== FORM ====== */
    .form-group {
      margin-bottom: 20px;
    }

    label {
      font-weight: 600;
      margin-bottom: 6px;
      display: block;
    }

    input[type="text"],
    textarea {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid #ddd;
      border-radius: 10px;
      font-size: 0.95rem;
      transition: all 0.2s;
    }

    input:focus,
    textarea:focus {
      border-color: #a30202;
      outline: none;
      box-shadow: 0 0 0 3px rgba(163, 2, 2, 0.15);
    }

    textarea {
      resize: vertical;
      min-height: 200px;
    }

    /* ====== BUTTONS ====== */
    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 15px;
      margin-top: 25px;
    }

    .btn {
      padding: 12px 22px;
      border: none;
      border-radius: 10px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s, opacity 0.2s;
    }

    .btn:hover {
      transform: translateY(-2px);
      opacity: 0.9;
    }

    .btn-publish {
      background: #a30202;
      color: #fff;
    }

    /* ====== MESSAGE ====== */
    .message {
      color: #b70000;
      text-align: center;
      margin-bottom: 15px;
      font-weight: 600;
    }

    /* ====== RESPONSIVE ====== */
    @media (max-width: 600px) {
      .form-actions {
        flex-direction: column;
      }
      .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Buat Forum Baru</h1>

    <?php if ($pesan): ?>
      <p class="message"><?= htmlspecialchars($pesan) ?></p>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="judul">Judul Forum</label>
        <input type="text" id="judul" name="judul" placeholder="Masukkan judul forum..." required>
      </div>

      <div class="form-group">
        <label for="isi">Isi Forum</label>
        <textarea id="isi" name="isi" placeholder="Tulis isi forum..." required></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-publish">Publikasikan</button>
      </div>
    </form>
  </div>

</body>
</html>

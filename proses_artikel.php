<?php
session_start();
include "koneksi.php";

$sukses = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $pegawai_id = $_SESSION['pegawai_id'] ?? 0;
    $judul = $_POST['judul'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $isi = $_POST['isi_artikel'] ?? '';
    $akses = $_POST['tipe'] ?? 'publik';
    $status = isset($_POST['draft']) ? 'draft' : 'pending';

    // ============================
    // VALIDASI FOTO WAJIB
    // ============================
    if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== 0) {
        echo "<script>alert('Foto wajib diunggah!'); window.history.back();</script>";
        exit;
    }

    // ============================
    // UPLOAD GAMBAR
    // ============================
    if (!is_dir("uploads")) mkdir("uploads", 0777, true);

    $gambar = time() . "_" . basename($_FILES['gambar']['name']);
    move_uploaded_file($_FILES['gambar']['tmp_name'], "uploads/" . $gambar);

    // ============================
    // UPLOAD PDF (opsional)
    // ============================
    $pdf_name = null;

    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === 0) {

        $ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));

        if ($ext !== "pdf") {
            die("File PDF tidak valid! Hanya format .pdf diperbolehkan.");
        }

        // bikin nama file lebih pendek & rapi
    $pdf_name = "kemhan_" . rand(10000,99999) . ".pdf";

    if (!is_dir("uploads/pdf")) mkdir("uploads/pdf", 0777, true);

    move_uploaded_file($_FILES['pdf']['tmp_name'], "uploads/pdf/" . $pdf_name);
}

    // ============================
    // SIMPAN KE DATABASE
    // ============================
    $stmt = $conn->prepare("INSERT INTO artikel 
        (judul, kategori, isi_artikel, tipe, status, gambar, pdf, pegawai_id, created_at) 
        VALUES (?,?,?,?,?,?,?,?,NOW())");

    $stmt->bind_param("sssssssi", 
        $judul,
        $kategori,
        $isi,
        $akses,
        $status,
        $gambar,
        $pdf_name,
        $pegawai_id
    );

    if ($stmt->execute()) {
        $sukses = true;
    } else {
        echo "❌ Error: " . $stmt->error;
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Artikel Dikirim</title>
<link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
<style>
    body {
        font-family: "Poppins", sans-serif;
        background: #f4f5f7;
        height: 100vh;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .notif-box {
        background: #ffffff;
        padding: 40px 45px;
        border-radius: 18px;
        width: 480px;
        box-shadow: 0 10px 35px rgba(0,0,0,0.08);
        text-align: center;
        animation: fadeIn .5s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0px); }
    }

    .notif-title {
        font-size: 1.6rem;
        font-weight: 600;
        color: #a30202;
        margin-bottom: 12px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }

    .notif-text {
        font-size: 1rem;
        color: #333;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    .btn-home {
        display: inline-block;
        padding: 12px 24px;
        background: #a30202;
        color: #fff;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn-home:hover {
        background: #8c0202;
        transform: translateY(-2px);
    }
</style>
</head>

<body>

<div class="notif-box">

    <div class="notif-title">✅ Artikel Berhasil Dikirim</div>

    <div class="notif-text">
        Artikel <strong>"<?= htmlspecialchars($judul); ?>"</strong> telah berhasil dikirim.<br>
        Saat ini sedang menunggu proses verifikasi dan persetujuan dari admin.<br>
    </div>

    <a href="pegawai.php" class="btn-home">Kembali ke Halaman Utama</a>

</div>

</body>
</html>

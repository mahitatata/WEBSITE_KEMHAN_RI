<?php
// hapuskomentar.php
session_start();
include "koneksi.php";

// harus login
if (!isset($_SESSION['email'])) {
    exit("error");
}

$komentar_id = intval($_POST['id'] ?? 0);
if ($komentar_id <= 0) exit("error");

$sessionNama = $_SESSION['nama'] ?? '';
$sessionRole = $_SESSION['role'] ?? 'user';

// ---- 1) coba cari di komentar_forum dulu ----
$stmt = $conn->prepare("SELECT nama FROM komentar_forum WHERE id = ?");
$stmt->bind_param("i", $komentar_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    // komentar ada di tabel komentar_forum
    $row = $res->fetch_assoc();
    $owner = $row['nama'];

    // izin: admin atau pemilik
    if (!($sessionRole === "admin" || ($sessionNama !== '' && $sessionNama === $owner))) {
        exit("error");
    }

    // hapus komentar forum + semua balasan (kolom balasan)
    $del = $conn->prepare("DELETE FROM komentar_forum WHERE id = ? OR balasan = ?");
    $del->bind_param("ii", $komentar_id, $komentar_id);
    if ($del->execute()) exit("success");
    else exit("error");
}

// ---- 2) jika tidak ada, coba di tabel komentar (artikel) ----
$stmt2 = $conn->prepare("SELECT nama FROM komentar WHERE id = ?");
$stmt2->bind_param("i", $komentar_id);
$stmt2->execute();
$res2 = $stmt2->get_result();

if ($res2 && $res2->num_rows > 0) {
    $row2 = $res2->fetch_assoc();
    $owner2 = $row2['nama'];

    if (!($sessionRole === "admin" || ($sessionNama !== '' && $sessionNama === $owner2))) {
        exit("error");
    }

    // hapus komentar artikel + semua balasan (kolom parent_id)
    $del2 = $conn->prepare("DELETE FROM komentar WHERE id = ? OR parent_id = ?");
    $del2->bind_param("ii", $komentar_id, $komentar_id);
    if ($del2->execute()) exit("success");
    else exit("error");
}

// ---- 3) tidak ditemukan di kedua tabel ----
exit("error");
?>

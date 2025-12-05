<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = intval($_GET['id']);

// Hapus komentar dulu
mysqli_query($conn, "DELETE FROM komentar_forum WHERE forum_id = $id");

// Hapus forum
mysqli_query($conn, "DELETE FROM forum WHERE id = $id");

header("Location: forum.php");
exit;
?>

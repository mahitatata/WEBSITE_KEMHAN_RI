<?php
session_start();

// Hanya user login yang boleh akses
if (!isset($_SESSION['pegawai_id']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Validasi parameter file
if (!isset($_GET['file'])) {
    echo "File tidak ditemukan.";
    exit;
}

$pdf = $_GET['file'];

// Cegah path traversal
if (strpos($pdf, "..") !== false) {
    die("Akses ditolak.");
}

// Pastikan nama file valid
$file = basename($pdf); 
$path = "uploads/pdf/" . $file;

if (!file_exists($path)) {
    echo "File PDF tidak ada.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lihat PDF</title>
<link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
<style>
    body, html {
        margin: 0;
        padding: 0;
        height: 100%;
    }
    iframe {
        width: 100%;
        height: 100vh;
        border: none;
    }
</style>
</head>
<body>

<iframe src="<?= htmlspecialchars($path) ?>"></iframe>

</body>
</html>

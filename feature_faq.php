<?php
include "koneksi.php";
session_start();

$id = intval($_POST['id']);

// Ambil status sekarang
$q = mysqli_query($conn, "SELECT featured FROM faq WHERE id = $id");
$row = mysqli_fetch_assoc($q);
$current = $row['featured'];

// Jika fitur limit (max 3 FAQ featured)
if ($current == 0) {
    $qCount = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM faq WHERE featured = 1");
    $count = mysqli_fetch_assoc($qCount)['jumlah'];

    if ($count >= 3) {
        echo json_encode([
            "status" => "limit"
        ]);
        exit;
    }
}

// toggle featured
$new = ($current == 1) ? 0 : 1;
mysqli_query($conn, "UPDATE faq SET featured = $new WHERE id = $id");

// respon ke JS
echo json_encode([
    "status" => "success",
    "featured" => $new
]);

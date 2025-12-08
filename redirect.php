<<<<<<< HEAD
<?php
include "koneksi.php";

$q = strtolower(trim($_GET['q'] ?? ""));
$response = ["status" => "not_found"];

if ($q === "") {
    echo json_encode($response);
    exit;
}

/* CARI ARTIKEL BERDASARKAN JUDUL */
$sql_artikel = "SELECT id FROM artikel 
                WHERE status='publish' 
                AND LOWER(judul) LIKE '%$q%' 
                LIMIT 1";

$resA = $conn->query($sql_artikel);

if ($resA && $resA->num_rows > 0) {
    echo json_encode([
        "status" => "found",
        "type" => "artikel",
        "redirect" => "artikel.php?search=" . urlencode($q)
    ]);
    exit;
}

/* CARI FORUM BERDASARKAN JUDUL */
$sql_forum = "SELECT id FROM forum 
              WHERE LOWER(judul) LIKE '%$q%' 
              LIMIT 1";

$resF = $conn->query($sql_forum);

if ($resF && $resF->num_rows > 0) {
    echo json_encode([
        "status" => "found",
        "type" => "forum",
        "redirect" => "forum.php?search=" . urlencode($q)
    ]);
    exit;
}

echo json_encode($response);
?>
=======
<?php
include "koneksi.php";

$q = strtolower(trim($_GET['q'] ?? ""));
$response = ["status" => "not_found"];

if ($q === "") {
    echo json_encode($response);
    exit;
}

/* CARI ARTIKEL BERDASARKAN JUDUL */
$sql_artikel = "SELECT id FROM artikel 
                WHERE status='publish' 
                AND LOWER(judul) LIKE '%$q%' 
                LIMIT 1";

$resA = $conn->query($sql_artikel);

if ($resA && $resA->num_rows > 0) {
    echo json_encode([
        "status" => "found",
        "type" => "artikel",
        "redirect" => "artikel.php?search=" . urlencode($q)
    ]);
    exit;
}

/* CARI FORUM BERDASARKAN JUDUL */
$sql_forum = "SELECT id FROM forum 
              WHERE LOWER(judul) LIKE '%$q%' 
              LIMIT 1";

$resF = $conn->query($sql_forum);

if ($resF && $resF->num_rows > 0) {
    echo json_encode([
        "status" => "found",
        "type" => "forum",
        "redirect" => "forum.php?search=" . urlencode($q)
    ]);
    exit;
}

echo json_encode($response);
?>
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

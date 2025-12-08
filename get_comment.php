<<<<<<< HEAD
<?php
include "koneksi.php";
$id = intval($_GET['id']);
$res = $conn->query("SELECT COUNT(*) AS total FROM komentar WHERE artikel_id = $id");
echo json_encode(['count' => ($res && $res->num_rows ? $res->fetch_assoc()['total'] : 0)]);
=======
<?php
include "koneksi.php";
$id = intval($_GET['id']);
$res = $conn->query("SELECT COUNT(*) AS total FROM komentar WHERE artikel_id = $id");
echo json_encode(['count' => ($res && $res->num_rows ? $res->fetch_assoc()['total'] : 0)]);
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

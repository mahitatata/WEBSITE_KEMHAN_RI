<?php
include "koneksi.php";
$id = intval($_GET['id']);
$res = $conn->query("SELECT COUNT(*) AS total FROM komentar WHERE artikel_id = $id");
echo json_encode(['count' => ($res && $res->num_rows ? $res->fetch_assoc()['total'] : 0)]);

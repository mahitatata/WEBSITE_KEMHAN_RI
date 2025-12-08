<<<<<<< HEAD
<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "msg" => "Unauthorized"]);
    exit;
}

$id = intval($_POST['id']);
$q = $conn->real_escape_string($_POST['question']);
$a = $conn->real_escape_string($_POST['answer']);

$conn->query("UPDATE faq SET question='$q', answer='$a' WHERE id=$id");

echo json_encode([
    "status" => "success",
    "id" => $id,
    "question" => $q,
    "answer" => nl2br($a)
]);
?>
=======
<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "msg" => "Unauthorized"]);
    exit;
}

$id = intval($_POST['id']);
$q = $conn->real_escape_string($_POST['question']);
$a = $conn->real_escape_string($_POST['answer']);

$conn->query("UPDATE faq SET question='$q', answer='$a' WHERE id=$id");

echo json_encode([
    "status" => "success",
    "id" => $id,
    "question" => $q,
    "answer" => nl2br($a)
]);
?>
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

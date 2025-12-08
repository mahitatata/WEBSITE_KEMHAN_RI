<<<<<<< HEAD
<?php
session_start();
include "koneksi.php";

if ($_SESSION['role'] !== 'admin') exit("forbidden");

$id = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id <= 0) exit("error");

if ($action === 'arsip') {
    $conn->query("UPDATE artikel SET arsip = 1 WHERE id=$id");
    exit("ok");
}

if ($action === 'unarsip') {
    $conn->query("UPDATE artikel SET arsip = 0 WHERE id=$id");
    exit("ok");
}

exit("error");
=======
<?php
session_start();
include "koneksi.php";


if ($_SESSION['role'] !== 'admin') exit("forbidden");

$id = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id <= 0) exit("error");

if ($action === 'arsip') {
    $conn->query("UPDATE artikel SET arsip = 1 WHERE id=$id");
    exit("ok");
}

if ($action === 'unarsip') {
    $conn->query("UPDATE artikel SET arsip = 0 WHERE id=$id");
    exit("ok");
}

exit("error");
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

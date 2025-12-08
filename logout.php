<<<<<<< HEAD
<?php
session_start();
include "koneksi.php";

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // set user jadi inactive
    $update = $conn->prepare("UPDATE regsitrasi SET status='inactive' WHERE email = ?");
    $update->bind_param("s", $email);
    $update->execute();
}

// Destroy session
session_unset();
session_destroy();

header("Location: index.php");
exit();
?>
=======
<?php
session_start();
include "koneksi.php";

if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // set user jadi inactive
    $update = $conn->prepare("UPDATE regsitrasi SET status='inactive' WHERE email = ?");
    $update->bind_param("s", $email);
    $update->execute();
}

// Destroy session
session_unset();
session_destroy();

header("Location: index.php");
exit();
?>
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

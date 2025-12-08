<<<<<<< HEAD
<?php
session_start();
include "koneksi.php";

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Ambil semua FAQ
$faq = $conn->query("SELECT * FROM faq ORDER BY id DESC");

// Admin Actions
if ($isAdmin) {

    // Tambah FAQ
    if (isset($_POST['add'])) {
        $q = $conn->real_escape_string($_POST['question']);
        $a = $conn->real_escape_string($_POST['answer']);
        $conn->query("INSERT INTO faq(question,answer) VALUES('$q','$a')");
    }

    // Update FAQ
    if (isset($_POST['update'])) {
        $id = intval($_POST['id']);
        $q = $conn->real_escape_string($_POST['question']);
        $a = $conn->real_escape_string($_POST['answer']);
        $conn->query("UPDATE faq SET question='$q', answer='$a' WHERE id=$id");
    }

    // Featured
    if (isset($_POST['featured'])) {
        $chosen = $_POST['featured'] ?? [];

        $conn->query("UPDATE faq SET featured=0");

        $limited = array_slice($chosen, 0, 3);
        foreach ($limited as $fid) {
            $conn->query("UPDATE faq SET featured=1 WHERE id=".intval($fid));
        }
    }
}

$editId = $_GET['edit'] ?? null;
$editData = null;

if($editId){
    $q = $conn->query("SELECT * FROM faq WHERE id=$editId");
    $editData = $q->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FAQs</title>
<link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
<link rel="stylesheet" href="faq.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="faq-page">

<?php include "header.php"; ?>

<main class="faq">
<form id="editForm" onsubmit="return false;">

<?php if($isAdmin): ?>
<div class="edit-modal" id="editModal">
  <div class="edit-box">
      <h3>Edit FAQ</h3>

      <form method="POST">
          <input type="hidden" name="id" id="editId">

          <label>Pertanyaan:</label>
          <input type="text" name="question" id="editQuestion" required>

          <label>Jawaban:</label>
          <textarea name="answer" id="editAnswer" required></textarea>

          <div class="edit-actions">
              <button type="submit" name="update" class="btn-save">Update</button>
              <button type="button" id="cancelEdit" class="btn-cancel">Batal</button>
          </div>
      </form>
  </div>
</div>
<?php endif; ?>
<section class="faq">
    <h2 class="faq-title">FAQs</h2>
    <div class="faq-wrapper">
    <div class="faq-container">

        <?php while($row = $faq->fetch_assoc()): ?>
        <div class="faq-item" data-id="<?= $row['id'] ?>">

            <div class="faq-header">

                <div class="faq-question">
                    <?= $row['question'] ?>
                </div>

                <?php if($isAdmin): ?>
                <div class="faq-actions">

                    <!-- Edit -->
                    <button class="btn-edit"
                        data-id="<?= $row['id'] ?>"
                        data-q="<?= htmlspecialchars($row['question']) ?>"
                        data-a="<?= htmlspecialchars($row['answer']) ?>">
                        <i class="fas fa-pen"></i>
                    </button>

                    <!-- Feature Toggle -->
                    <button class="btn-feature"
                        data-id="<?= $row['id'] ?>">
                        <?= $row['featured'] ? "⭐" : "☆" ?>
                    </button>

                    <!-- Dropdown -->
                    <i class="fas fa-chevron-down faq-toggle"></i>
                </div>
                <?php else: ?>
                    <i class="fas fa-chevron-down faq-toggle"></i>
                <?php endif; ?>
            </div>

            <div class="faq-answer">
                <?= nl2br($row['answer']) ?>
            </div>

        </div>
        <?php endwhile; ?>

    </div>
    </div>
</section>
</main>

<footer>
  <div class="footer-container">
    <section class="footer-section about">

    <!-- Logo -->
      <div class="footer-logo">
        <img src="logo kemhan 1.png" alt="Logo Kemhan">
        <div class="footer-instansi">
          <h4>Pusat Data dan Informasi</h4>
          <p>Kementerian Pertahanan Republik Indonesia</p>
        </div>
      </div>
      <p class="footer-desc">
        HanZone merupakan portal informasi dan layanan digital yang dikembangkan oleh
        Pusat Data dan Informasi Kemhan untuk mendukung transformasi digital pertahanan nasional.
      </p>
    </section>

    <!-- Social Media -->
    <div class="footer-section sosmed">
      <h4>Social Media</h4>
      <div class="social-icons">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

    <!-- Kontak -->
    <section class="footer-section contact">
      <h4>Informasi Kontak</h4>
      <ul>
        <li><i class="fas fa-map-marker-alt"></i> Jl. RS Fatmawati No.1, Jakarta Selatan</li>
        <li><i class="fas fa-phone-alt"></i> Telp: 021-7690009</li>
        <li><i class="fas fa-envelope"></i> 
          <a href="mailto:pusdatin@kemhan.go.id">pusdatin@kemhan.go.id</a>
        </li>
      </ul>
    </section>
  </div>

  <div class="footer-bottom">
    <p>&copy; 2025 HanZone | Pusat Data dan Informasi Kementerian Pertahanan</p>
  </div>
</footer>
<script src="faq.js"></script>
</body>
</html>




=======
<?php
session_start();
include "koneksi.php";

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Ambil semua FAQ
$faq = $conn->query("SELECT * FROM faq ORDER BY id DESC");

// Admin Actions
if ($isAdmin) {

  // Tambah FAQ
  if (isset($_POST['add'])) {
    $q = $conn->real_escape_string($_POST['question']);
    $a = $conn->real_escape_string($_POST['answer']);
    $conn->query("INSERT INTO faq(question,answer) VALUES('$q','$a')");
  }

  // Update FAQ
  if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $q = $conn->real_escape_string($_POST['question']);
    $a = $conn->real_escape_string($_POST['answer']);
    $conn->query("UPDATE faq SET question='$q', answer='$a' WHERE id=$id");
  }

  // Featured
  if (isset($_POST['featured'])) {
    $chosen = $_POST['featured'] ?? [];

    $conn->query("UPDATE faq SET featured=0");

    $limited = array_slice($chosen, 0, 3);
    foreach ($limited as $fid) {
      $conn->query("UPDATE faq SET featured=1 WHERE id=" . intval($fid));
    }
  }
}

$editId = $_GET['edit'] ?? null;
$editData = null;

if ($editId) {
  $q = $conn->query("SELECT * FROM faq WHERE id=$editId");
  $editData = $q->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FAQs</title>
  <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
  <link rel="stylesheet" href="faq.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="faq-page">

  <?php include "header.php"; ?>

  <main class="faq">
    <form id="editForm" onsubmit="return false;">

      <?php if ($isAdmin): ?>
        <div class="edit-modal" id="editModal">
          <div class="edit-box">
            <h3>Edit FAQ</h3>

            <form method="POST">
              <input type="hidden" name="id" id="editId">

              <label>Pertanyaan:</label>
              <input type="text" name="question" id="editQuestion" required>

              <label>Jawaban:</label>
              <textarea name="answer" id="editAnswer" required></textarea>

              <div class="edit-actions">
                <button type="submit" name="update" class="btn-save">Update</button>
                <button type="button" id="cancelEdit" class="btn-cancel">Batal</button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>
      <section class="faq">
        <h2 class="faq-title">FAQs</h2>
        <div class="faq-wrapper">
          <div class="faq-container">

            <?php while ($row = $faq->fetch_assoc()): ?>
              <div class="faq-item" data-id="<?= $row['id'] ?>">

                <div class="faq-header">

                  <div class="faq-question">
                    <?= $row['question'] ?>
                  </div>

                  <?php if ($isAdmin): ?>
                    <div class="faq-actions">

                      <!-- Edit -->
                      <button class="btn-edit"
                        data-id="<?= $row['id'] ?>"
                        data-q="<?= htmlspecialchars($row['question']) ?>"
                        data-a="<?= htmlspecialchars($row['answer']) ?>">
                        <i class="fas fa-pen"></i>
                      </button>

                      <!-- Feature Toggle -->
                      <button class="btn-feature"
                        data-id="<?= $row['id'] ?>">
                        <?= $row['featured'] ? "⭐" : "☆" ?>
                      </button>

                      <!-- Dropdown -->
                      <i class="fas fa-chevron-down faq-toggle"></i>
                    </div>
                  <?php else: ?>
                    <i class="fas fa-chevron-down faq-toggle"></i>
                  <?php endif; ?>
                </div>

                <div class="faq-answer">
                  <?= nl2br($row['answer']) ?>
                </div>

              </div>
            <?php endwhile; ?>

          </div>
        </div>
      </section>
  </main>

  <footer>
    <div class="footer-container">
      <section class="footer-section about">

        <!-- Logo -->
        <div class="footer-logo">
          <img src="logo kemhan 1.png" alt="Logo Kemhan">
          <div class="footer-instansi">
            <h4>Pusat Data dan Informasi</h4>
            <p>Kementerian Pertahanan Republik Indonesia</p>
          </div>
        </div>
        <p class="footer-desc">
          HanZone merupakan portal informasi dan layanan digital yang dikembangkan oleh
          Pusat Data dan Informasi Kemhan untuk mendukung transformasi digital pertahanan nasional.
        </p>
      </section>

      <!-- Social Media -->
      <div class="footer-section sosmed">
      <h4>Social Media</h4>
      <div class="social-icons">
        <a href="https://www.facebook.com/KementerianPertahananRI/" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <a href="https://x.com/Kemhan_RI" target="_blank"><i class="fab fa-twitter"></i></a>
        <a href="https://www.instagram.com/kemhanri/" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://www.youtube.com/@kemhan" target="_blank"><i class="fab fa-youtube"></i></a>
      </div>
    </div>

      <!-- Kontak -->
      <section class="footer-section contact">
        <h4>Informasi Kontak</h4>
        <ul>
          <li><i class="fas fa-map-marker-alt"></i> Jl. RS Fatmawati No.1, Jakarta Selatan</li>
          <li><i class="fas fa-phone-alt"></i> Telp: 021-7690009</li>
          <li><i class="fas fa-envelope"></i>
            <a href="mailto:pusdatin@kemhan.go.id">pusdatin@kemhan.go.id</a>
          </li>
        </ul>
      </section>
    </div>

    <div class="footer-bottom">
      <p>&copy; 2025 HanZone | Pusat Data dan Informasi Kementerian Pertahanan</p>
    </div>
  </footer>
  <script src="faq.js"></script>
</body>

</html>
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

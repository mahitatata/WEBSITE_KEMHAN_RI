<<<<<<< HEAD
<?php
session_start();
include "koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM regsitrasi WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['pegawai_id'] = $user['ID']; // simpan ID user-nya
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];


            // Update status & last_active di tabel regsitrasi
            $updateStatus = $conn->prepare("UPDATE regsitrasi SET status='active', last_active = NOW() WHERE email = ?");
            $updateStatus->bind_param("s", $email);
            $updateStatus->execute();

            // Kalau role = pegawai → sinkronkan ke tabel pegawai
            if (strtolower($user['role']) === 'pegawai') {
                $email = $user['email'];
                $nama = $user['nama'];
                $satker = isset($user['satker']) ? $user['satker'] : ''; // antisipasi kalau gak ada

                // Cek apakah email sudah ada di tabel pegawai
                $cekPegawai = $conn->prepare("SELECT id FROM pegawai WHERE Email = ?");
                $cekPegawai->bind_param("s", $email);
                $cekPegawai->execute();
                $hasil = $cekPegawai->get_result();

                if ($hasil->num_rows == 0) {
                    // Kalau belum ada, tambahkan
                    $insert = $conn->prepare("INSERT INTO pegawai (Email, Nama, Satker, last_active) VALUES (?, ?, ?, NOW())");
                    $insert->bind_param("sss", $email, $nama, $satker);
                    $insert->execute();
                } else {
                    // Kalau sudah ada, update last_active-nya aja
                    $update = $conn->prepare("UPDATE pegawai SET last_active = NOW() WHERE Email = ?");
                    $update->bind_param("s", $email);
                    $update->execute();
                }
            }

            // redirect sesuai role
            switch (strtolower($user['role'])) {
                case 'admin':
                    header("Location: dashboard.php");
                    break;
                case 'pegawai':
                    header("Location: pegawai.php");
                    break;
                default:
                    header("Location: index.php");
                    break;
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Font Awesome untuk icon mata -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center; 
            height: 100vh;
        }

        .container {
            background: #8B0000;
            padding: 40px;
            border-radius: 20px;
            width: 400px;
            color: white;
            position: relative;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .header-content { text-align: center; margin-bottom: 20px; }
        .logo { width: 60px; display: block; margin: 0 auto; }
        .hanzone { font-size: 24px; font-weight: bold; margin: 10px 0 0 0; }
        .subtitle { font-size: 14px; margin: 5px 0 0 0; }
        .welcome { text-align: center; font-size: 28px; font-weight: bold; margin-bottom: 20px; }

        form { display: flex; flex-direction: column; align-items: center; }

        .form-group { width: 85%; margin: 0 auto 15px auto; text-align: left; }
        label { display: block; margin-bottom: 5px; font-size: 14px; }

        input { padding: 12px; border-radius: 10px; border: none; outline: none; font-size: 14px; width: 100%; box-sizing: border-box; }

        button { background: #fff; color: #8B0000; border: none; padding: 12px 20px; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        button:hover { background: #ddd; }

        .form-actions { display: flex; justify-content: center; gap: 100px; width: 85%; margin: 20px auto 0 auto; }
        .btn-action { flex: 1; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.2s; text-align: center; border: none; padding: 12px 20px; }
        .btn-action.daftar { background: #fff; color: #8B0000; }
        .btn-action.kembali { background: #f0f0f0; color: #8B0000; }

        .login-link { text-align: center; margin-top: 15px; font-size: 14px; }
        .login-link a { color: rgb(255, 194, 89); font-weight: bold; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff4d4d;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 14px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
            z-index: 9999;
        }
        .toast.show { opacity: 1; transform: translateY(0); }

        /* Shake effect */
        .shake { animation: shake 0.4s ease; }
        @keyframes shake { 0%,100%{transform:translateX(0);}25%{transform:translateX(-6px);}50%{transform:translateX(6px);}75%{transform:translateX(-6px);} }

        .error-text { color: #ffcccc; font-size: 12px; margin-top: 4px; }

        .password-container { position: relative; width: 100%; display: flex; flex-direction: column; }
        .password-container input { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; outline: none; font-size: 14px; box-sizing: border-box; padding-right: 40px; }
        .toggle-password { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #555; font-size: 18px; line-height: 1; }
    </style>
</head>
<body>

    <!-- Toast -->
    <div id="toast" class="toast"></div>

    <div class="container">
        <div class="header-content">
            <img src="logo kemhan 1.png" alt="Logo" class="logo">
            <h2 class="hanzone">HanZone</h2>
            <p class="subtitle">Zona Pengetahuan Pertahanan</p>
        </div>

        <h2 class="welcome">Selamat Datang</h2>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
    <label for="password">Password</label>
    <div class="password-container">
        <input type="password" id="password" name="password" minlength="8" required placeholder="Masukkan kata sandi">
        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
    </div>
    <div id="passwordError" class="error-text"></div> <!-- Error ditempatkan di luar -->
</div>

            <div class="form-actions">
                <button type="button" class="btn-action" onclick="window.location.href='index.php'">Kembali</button>
                <button type="submit" class="btn-action">Masuk</button>
            </div>

            <p class="login-link">Belum punya akun? <a href="register.php">Daftar</a></p>
        </form>
    </div>

    <script>
    function showToast(msg) {
        const toast = document.getElementById("toast");
        toast.textContent = msg;
        toast.classList.add("show");
        setTimeout(() => toast.classList.remove("show"), 5000);
    }

    document.addEventListener("DOMContentLoaded", function() {
        <?php if (!empty($error)): ?>
            // Munculkan toast
            showToast("<?= $error ?>");

            // Shake input password
            const passwordInput = document.getElementById("password");
            passwordInput.classList.add("shake");
            setTimeout(() => passwordInput.classList.remove("shake"), 400);

            
            // Tambahkan pesan error kecil di bawah password
            document.getElementById("passwordError").textContent = "<?= $error ?>";
        <?php endif; ?>
    });

    // Toggle lihat password
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");
    togglePassword.addEventListener("click", () => {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            togglePassword.classList.remove("fa-eye");
            togglePassword.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            togglePassword.classList.remove("fa-eye-slash");
            togglePassword.classList.add("fa-eye");
        }
    });
    </script>
</body>
</html>
=======
<?php
session_start();
include "koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM regsitrasi WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['pegawai_id'] = $user['ID']; // simpan ID user-nya
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];


            // Update status & last_active di tabel regsitrasi
            $updateStatus = $conn->prepare("UPDATE regsitrasi SET status='active', last_active = NOW() WHERE email = ?");
            $updateStatus->bind_param("s", $email);
            $updateStatus->execute();

            // Kalau role = pegawai → sinkronkan ke tabel pegawai
            if (strtolower($user['role']) === 'pegawai') {
                $email = $user['email'];
                $nama = $user['nama'];
                $satker = isset($user['satker']) ? $user['satker'] : ''; // antisipasi kalau gak ada

                // Cek apakah email sudah ada di tabel pegawai
                $cekPegawai = $conn->prepare("SELECT id FROM pegawai WHERE Email = ?");
                $cekPegawai->bind_param("s", $email);
                $cekPegawai->execute();
                $hasil = $cekPegawai->get_result();

                if ($hasil->num_rows == 0) {
                    // Kalau belum ada, tambahkan
                    $insert = $conn->prepare("INSERT INTO pegawai (Email, Nama, Satker, last_active) VALUES (?, ?, ?, NOW())");
                    $insert->bind_param("sss", $email, $nama, $satker);
                    $insert->execute();
                } else {
                    // Kalau sudah ada, update last_active-nya aja
                    $update = $conn->prepare("UPDATE pegawai SET last_active = NOW() WHERE Email = ?");
                    $update->bind_param("s", $email);
                    $update->execute();
                }
            }

            // redirect sesuai role
            switch (strtolower($user['role'])) {
                case 'admin':
                    header("Location: dashboard.php");
                    break;
                case 'pegawai':
                    header("Location: pegawai.php");
                    break;
                default:
                    header("Location: index.php");
                    break;
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Font Awesome untuk icon mata -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center; 
            height: 100vh;
        }

        .container {
            background: #8B0000;
            padding: 40px;
            border-radius: 20px;
            width: 400px;
            color: white;
            position: relative;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .header-content { text-align: center; margin-bottom: 20px; }
        .logo { width: 60px; display: block; margin: 0 auto; }
        .hanzone { font-size: 24px; font-weight: bold; margin: 10px 0 0 0; }
        .subtitle { font-size: 14px; margin: 5px 0 0 0; }
        .welcome { text-align: center; font-size: 28px; font-weight: bold; margin-bottom: 20px; }

        form { display: flex; flex-direction: column; align-items: center; }

        .form-group { width: 85%; margin: 0 auto 15px auto; text-align: left; }
        label { display: block; margin-bottom: 5px; font-size: 14px; }

        input { padding: 12px; border-radius: 10px; border: none; outline: none; font-size: 14px; width: 100%; box-sizing: border-box; }

        button { background: #fff; color: #8B0000; border: none; padding: 12px 20px; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        button:hover { background: #ddd; }

        .form-actions { display: flex; justify-content: center; gap: 100px; width: 85%; margin: 20px auto 0 auto; }
        .btn-action { flex: 1; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.2s; text-align: center; border: none; padding: 12px 20px; }
        .btn-action.daftar { background: #fff; color: #8B0000; }
        .btn-action.kembali { background: #f0f0f0; color: #8B0000; }

        .login-link { text-align: center; margin-top: 15px; font-size: 14px; }
        .login-link a { color: rgb(255, 194, 89); font-weight: bold; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff4d4d;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 14px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
            z-index: 9999;
        }
        .toast.show { opacity: 1; transform: translateY(0); }

        /* Shake effect */
        .shake { animation: shake 0.4s ease; }
        @keyframes shake { 0%,100%{transform:translateX(0);}25%{transform:translateX(-6px);}50%{transform:translateX(6px);}75%{transform:translateX(-6px);} }

        .error-text { color: #ffcccc; font-size: 12px; margin-top: 4px; }

        .password-container { position: relative; width: 100%; display: flex; flex-direction: column; }
        .password-container input { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; outline: none; font-size: 14px; box-sizing: border-box; padding-right: 40px; }
        .toggle-password { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #555; font-size: 18px; line-height: 1; }
    </style>
</head>
<body>

    <!-- Toast -->
    <div id="toast" class="toast"></div>

    <div class="container">
        <div class="header-content">
            <img src="logo kemhan 1.png" alt="Logo" class="logo">
            <h2 class="hanzone">HanZone</h2>
            <p class="subtitle">Zona Pengetahuan Pertahanan</p>
        </div>

        <h2 class="welcome">Selamat Datang</h2>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
    <label for="password">Password</label>
    <div class="password-container">
        <input type="password" id="password" name="password" minlength="8" required placeholder="Masukkan kata sandi">
        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
    </div>
    <div id="passwordError" class="error-text"></div> <!-- Error ditempatkan di luar -->
</div>

            <div class="form-actions">
                <button type="button" class="btn-action" onclick="window.location.href='index.php'">Kembali</button>
                <button type="submit" class="btn-action">Masuk</button>
            </div>

            <p class="login-link">Belum punya akun? <a href="register.php">Daftar</a></p>
        </form>
    </div>

    <script>
    function showToast(msg) {
        const toast = document.getElementById("toast");
        toast.textContent = msg;
        toast.classList.add("show");
        setTimeout(() => toast.classList.remove("show"), 5000);
    }

    document.addEventListener("DOMContentLoaded", function() {
        <?php if (!empty($error)): ?>
            // Munculkan toast
            showToast("<?= $error ?>");

            // Shake input password
            const passwordInput = document.getElementById("password");
            passwordInput.classList.add("shake");
            setTimeout(() => passwordInput.classList.remove("shake"), 400);

            
            // Tambahkan pesan error kecil di bawah password
            document.getElementById("passwordError").textContent = "<?= $error ?>";
        <?php endif; ?>
    });

    // Toggle lihat password
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");
    togglePassword.addEventListener("click", () => {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            togglePassword.classList.remove("fa-eye");
            togglePassword.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            togglePassword.classList.remove("fa-eye-slash");
            togglePassword.classList.add("fa-eye");
        }
    });
    </script>
</body>
</html>
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

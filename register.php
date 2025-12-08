<<<<<<< HEAD
<?php
include "koneksi.php"; // koneksi ke database

if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $satker = $_POST['satker'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 🔒 Cek apakah email sudah diblacklist
    $email_escaped = mysqli_real_escape_string($conn, $email);
    $checkBlacklist = mysqli_query($conn, "SELECT * FROM blacklist WHERE email = '$email_escaped'");

    if (mysqli_num_rows($checkBlacklist) > 0) {
    header("Location: register.php?error=" . urlencode("Email ini telah diblokir."));
    exit;
}

    // 🧩 Tentukan role otomatis
    if ($email === "admin1@kemhan.go.id") {
        $role = "admin";
    } elseif (preg_match('/@kemhan\.go\.id$/', $email)) {
        $role = "pegawai";
    } else {
        $role = "guest";
    }

    // 📝 Insert ke regsitrasi
    $sql = "INSERT INTO regsitrasi (nama, email, satker, password, role)
            VALUES ('$nama', '$email', '$satker', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        header("Location: login.php?success=1");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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

        .beranda-link {
            position: absolute;
            top: 15px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        .beranda-link:hover {
            text-decoration: underline;
        }
        
        .header-content {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 60px;
            display: block;
            margin: 0 auto;
        }
        
        .hanzone {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 0;
        }
        
        .subtitle {
            font-size: 14px;
            margin-top: 5px;
            margin-bottom: 0;
        }

        .welcome {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .form-group {
            width: 85%;
            margin: 0 auto 15px auto;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }

        input {
            padding: 12px;
            border-radius: 10px;
            border: none;
            outline: none;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .custom-dropdown-container {
            position: relative;
            width: 100%; 
        }
        
        .custom-dropdown-toggle {
            background-color: white;
            color: black;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
        }

        .custom-dropdown-toggle::after {
            content: '▼';
            font-size: 12px;
        }

        .custom-dropdown-menu {
            position: absolute;
            top: 100%; 
            left: 0;
            width: 100%;
            list-style-type: none;
            padding: 0;
            margin: 5px 0 0 0;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 10;
            display: none; 
            max-height: 200px;
            overflow-y: auto;
        }

        .custom-dropdown-menu li {
            padding: 10px 12px;
            cursor: pointer;
            color: black;
        }

        .custom-dropdown-menu li:hover {
            background-color: #f0f0f0;
        }

        .custom-dropdown-menu li.selected {
            background-color: #8B0000;
            color: white;
        }

        button {
            background: rgb(255, 255, 255);
            color: #8B0000;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            box-sizing: border-box;
        }

        button:hover {
            background: #ddd;
        }

        .form-actions {
            display: flex; 
            justify-content: center; 
            gap: 100px;
            width: 85%; 
            margin: 20px auto 0 auto; 
    }

    .btn-action {
            flex: 1; 
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            text-align: center;
            border: none;
            padding: 12px 20px;
    }

    .btn-action.daftar {
            background: #fff;
            color: #8B0000;
    }

    .btn-action.kembali {
            background: #f0f0f0; 
            color: #8B0000;
    }

    .login-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
    }

    .login-link a {
            color: rgb(255, 194, 89);
            font-weight: bold;
            text-decoration: none;
    }

    .login-link a:hover {
            text-decoration: underline;
     }

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

    </style>
</head>
<body>

    <div id="toast" class="toast"></div>

    <div class="container">

        <div class="header-content">
            <img src="logo kemhan 1.png" alt="Logo" class="logo">
            <h2 class="hanzone">HanZone</h2>
            <p class="subtitle">Zona Pengetahuan Pertahanan</p>
        </div>

        <h2 class="welcome">Selamat Datang</h2>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" required>
            </div>
            
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
  <label for="satker">Satker</label>
  <input list="satker-list" id="satker" name="satker" placeholder="Ketik atau Pilih Satker">
  
  <datalist id="satker-list">
    <option value="Pihak Luar">
    <option value="Setjen">
    <option value="Itjen">
    <option value="Ditjen Strahan">
    <option value="Ditjen Pothan">
    <option value="Ditjen Renhan">
    <option value="Baranahan">
    <option value="Balitbang">
    <option value="Badiklat">
    <option value="Bainstrahan">
    <option value="Staf Ahli Bidang Politik">
    <option value="Staf Ahli Bidang Sosial">
    <option value="Staf Ahli Bidang Ekonomi">
    <option value="Staf Ahli Bidang Keamanan">
    <option value="Puslaik">
    <option value="Pusdatin">
  </datalist>
</div>


<!-- Tambahkan Font Awesome di <head> -->
<link 
  rel="stylesheet" 
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
/>

<div class="form-group">
  <label for="password">Password</label>
  <div class="password-container">
    <input 
      type="password" 
      id="password" 
      name="password" 
      minlength="8" 
      required 
      placeholder="Masukkan kata sandi"
    >
    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
  </div>
  <small class="password-hint">Kata sandi harus minimal 8 karakter dan menggunakan simbol khusus. </small>
</div>

<style>
.password-container {
  position: relative;
  width: 100%;
}

.password-container input {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid #ccc;
  outline: none;
  font-size: 14px;
  box-sizing: border-box;
  padding-right: 40px; /* ruang buat ikon */
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #555;
  font-size: 18px;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const togglePassword = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("password");

  togglePassword.addEventListener("click", () => {
    // cek tipe input
    if (passwordInput.type === "password") {
      passwordInput.type = "text";   // tampilkan
      togglePassword.classList.remove("fa-eye");
      togglePassword.classList.add("fa-eye-slash");
    } else {
      passwordInput.type = "password"; // sembunyikan
      togglePassword.classList.remove("fa-eye-slash");
      togglePassword.classList.add("fa-eye");
    }
  });
});


function showToast(msg) {
    const toast = document.getElementById("toast");
    toast.textContent = msg;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 5000);
}

<?php if (isset($_GET['error'])): ?>
    document.addEventListener("DOMContentLoaded", function() {
        showToast("<?= htmlspecialchars($_GET['error']) ?>");
    });
<?php endif; ?>

</script>


  <div class="form-actions">
    <button type="button" class="btn-action" onclick="window.location.href='index.php'">Kembali</button>
    <button type="submit" class="btn-action" name="register">Daftar</button>
</div>

<p class="login-link">
  Sudah punya akun? <a href="login.php">Masuk</a>
</p>

        </form>
    </div>
    
    <script>
        const toggle = document.getElementById('satker-toggle');
        const menu = document.getElementById('satker-menu');
        const hiddenInput = document.getElementById('satker');
        const menuItems = menu.querySelectorAll('li');
        
        // Menampilkan/menyembunyikan menu saat diklik
        toggle.addEventListener('click', () => {
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Menyembunyikan menu dropdown
        document.addEventListener('click', (event) => {
            if (!toggle.contains(event.target) && !menu.contains(event.target)) {
                menu.style.display = 'none';
            }
        });

        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                menuItems.forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                
                toggle.textContent = item.textContent;
                hiddenInput.value = item.dataset.value;
                menu.style.display = 'none'; 
            });
        });
    </script>
</body>
</html>
=======
<?php
include "koneksi.php"; // koneksi ke database

if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $satker = $_POST['satker'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 🔒 Cek apakah email sudah diblacklist
    $email_escaped = mysqli_real_escape_string($conn, $email);
    $checkBlacklist = mysqli_query($conn, "SELECT * FROM blacklist WHERE email = '$email_escaped'");

    if (mysqli_num_rows($checkBlacklist) > 0) {
    header("Location: register.php?error=" . urlencode("Email ini telah diblokir."));
    exit;
}

    // 🧩 Tentukan role otomatis
    if ($email === "admin1@kemhan.go.id") {
        $role = "admin";
    } elseif (preg_match('/@kemhan\.go\.id$/', $email)) {
        $role = "pegawai";
    } else {
        $role = "guest";
    }

    // 📝 Insert ke regsitrasi
    $sql = "INSERT INTO regsitrasi (nama, email, satker, password, role)
            VALUES ('$nama', '$email', '$satker', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        header("Location: login.php?success=1");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/x-icon" href="logo kemhan 1.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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

        .beranda-link {
            position: absolute;
            top: 15px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }

        .beranda-link:hover {
            text-decoration: underline;
        }
        
        .header-content {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 60px;
            display: block;
            margin: 0 auto;
        }
        
        .hanzone {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 0;
        }
        
        .subtitle {
            font-size: 14px;
            margin-top: 5px;
            margin-bottom: 0;
        }

        .welcome {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .form-group {
            width: 85%;
            margin: 0 auto 15px auto;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }

        input {
            padding: 12px;
            border-radius: 10px;
            border: none;
            outline: none;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .custom-dropdown-container {
            position: relative;
            width: 100%; 
        }
        
        .custom-dropdown-toggle {
            background-color: white;
            color: black;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
        }

        .custom-dropdown-toggle::after {
            content: '▼';
            font-size: 12px;
        }

        .custom-dropdown-menu {
            position: absolute;
            top: 100%; 
            left: 0;
            width: 100%;
            list-style-type: none;
            padding: 0;
            margin: 5px 0 0 0;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 10;
            display: none; 
            max-height: 200px;
            overflow-y: auto;
        }

        .custom-dropdown-menu li {
            padding: 10px 12px;
            cursor: pointer;
            color: black;
        }

        .custom-dropdown-menu li:hover {
            background-color: #f0f0f0;
        }

        .custom-dropdown-menu li.selected {
            background-color: #8B0000;
            color: white;
        }

        button {
            background: rgb(255, 255, 255);
            color: #8B0000;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            box-sizing: border-box;
        }

        button:hover {
            background: #ddd;
        }

        .form-actions {
            display: flex; 
            justify-content: center; 
            gap: 100px;
            width: 85%; 
            margin: 20px auto 0 auto; 
    }

    .btn-action {
            flex: 1; 
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            text-align: center;
            border: none;
            padding: 12px 20px;
    }

    .btn-action.daftar {
            background: #fff;
            color: #8B0000;
    }

    .btn-action.kembali {
            background: #f0f0f0; 
            color: #8B0000;
    }

    .login-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
    }

    .login-link a {
            color: rgb(255, 194, 89);
            font-weight: bold;
            text-decoration: none;
    }

    .login-link a:hover {
            text-decoration: underline;
     }

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

    </style>
</head>
<body>

    <div id="toast" class="toast"></div>

    <div class="container">

        <div class="header-content">
            <img src="logo kemhan 1.png" alt="Logo" class="logo">
            <h2 class="hanzone">HanZone</h2>
            <p class="subtitle">Zona Pengetahuan Pertahanan</p>
        </div>

        <h2 class="welcome">Selamat Datang</h2>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" required>
            </div>
            
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
  <label for="satker">Satker</label>
  <input list="satker-list" id="satker" name="satker" placeholder="Ketik atau Pilih Satker">
  
  <datalist id="satker-list">
    <option value="Pihak Luar">
    <option value="Setjen">
    <option value="Itjen">
    <option value="Ditjen Strahan">
    <option value="Ditjen Pothan">
    <option value="Ditjen Renhan">
    <option value="Baranahan">
    <option value="Balitbang">
    <option value="Badiklat">
    <option value="Bainstrahan">
    <option value="Staf Ahli Bidang Politik">
    <option value="Staf Ahli Bidang Sosial">
    <option value="Staf Ahli Bidang Ekonomi">
    <option value="Staf Ahli Bidang Keamanan">
    <option value="Puslaik">
    <option value="Pusdatin">
  </datalist>
</div>


<!-- Tambahkan Font Awesome di <head> -->
<link 
  rel="stylesheet" 
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
/>

<div class="form-group">
  <label for="password">Password</label>
  <div class="password-container">
    <input 
      type="password" 
      id="password" 
      name="password" 
      minlength="8" 
      required 
      placeholder="Masukkan kata sandi"
    >
    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
  </div>
  <small class="password-hint">Kata sandi harus minimal 8 karakter dan menggunakan simbol khusus. </small>
</div>

<style>
.password-container {
  position: relative;
  width: 100%;
}

.password-container input {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid #ccc;
  outline: none;
  font-size: 14px;
  box-sizing: border-box;
  padding-right: 40px; /* ruang buat ikon */
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #555;
  font-size: 18px;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const togglePassword = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("password");

  togglePassword.addEventListener("click", () => {
    // cek tipe input
    if (passwordInput.type === "password") {
      passwordInput.type = "text";   // tampilkan
      togglePassword.classList.remove("fa-eye");
      togglePassword.classList.add("fa-eye-slash");
    } else {
      passwordInput.type = "password"; // sembunyikan
      togglePassword.classList.remove("fa-eye-slash");
      togglePassword.classList.add("fa-eye");
    }
  });
});


function showToast(msg) {
    const toast = document.getElementById("toast");
    toast.textContent = msg;
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 5000);
}

<?php if (isset($_GET['error'])): ?>
    document.addEventListener("DOMContentLoaded", function() {
        showToast("<?= htmlspecialchars($_GET['error']) ?>");
    });
<?php endif; ?>

</script>


  <div class="form-actions">
    <button type="button" class="btn-action" onclick="window.location.href='index.php'">Kembali</button>
    <button type="submit" class="btn-action" name="register">Daftar</button>
</div>

<p class="login-link">
  Sudah punya akun? <a href="login.php">Masuk</a>
</p>

        </form>
    </div>
    
    <script>
        const toggle = document.getElementById('satker-toggle');
        const menu = document.getElementById('satker-menu');
        const hiddenInput = document.getElementById('satker');
        const menuItems = menu.querySelectorAll('li');
        
        // Menampilkan/menyembunyikan menu saat diklik
        toggle.addEventListener('click', () => {
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Menyembunyikan menu dropdown
        document.addEventListener('click', (event) => {
            if (!toggle.contains(event.target) && !menu.contains(event.target)) {
                menu.style.display = 'none';
            }
        });

        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                menuItems.forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                
                toggle.textContent = item.textContent;
                hiddenInput.value = item.dataset.value;
                menu.style.display = 'none'; 
            });
        });
    </script>
</body>
</html>
>>>>>>> 109b7aaf76d6ad31e925b329ea3ee97dab88b268

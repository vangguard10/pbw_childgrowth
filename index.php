<?php
session_start();
require 'db.php';

// Pesan error untuk login
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Cari user berdasarkan username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Simpan data user ke sesi
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                header("Location: admin_login.php");
                exit();
            } elseif ($user['role'] === 'user') {
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Role tidak valid.";
            }
        } else {
            $message = "Username atau password salah.";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css"> <!-- Sesuaikan dengan CSS Anda -->
</head>
<body>
    <div class="container">
        <h1>GrowSmart</h1>
        <p>Selamat Datang di Sistem Pemantauan Tumbuh Kembang Anak</p>

        <!-- Tab Navigation -->
        <div class="tab-container">
            <button class="tab-btn" id="login-tab">Login</button>
            <button class="tab-btn" id="register-tab">Register</button>
        </div>

        <!-- Login Form -->
        <div class="form-container" id="login-form">
            <h2>Login</h2>
            <form action="" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>

                <button type="submit">Login</button>
            </form>
        </div>

        <!-- Register Form -->
        <div class="form-container hidden" id="register-form">
            <h2>Register</h2>
            <form action="register.php" method="POST" onsubmit="return validateRegisterForm()">
                <label for="register-username">Username:</label>
                <input type="text" id="register-username" name="username" placeholder="Masukkan username" required>

                <label for="register-email">Email:</label>
                <input type="email" id="register-email" name="email" placeholder="Masukkan email" required>

                <label for="register-password">Password:</label>
                <input type="password" id="register-password" name="password" placeholder="Masukkan password" required>

                <label for="register-confirm-password">Konfirmasi Password:</label>
                <input type="password" id="register-confirm-password" name="confirm_password" placeholder="Masukkan ulang password" required>

                <p class="note">Password harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.</p>

                <button type="submit">Register</button>
            </form>
        </div>
    </div>

    <script>
        // Tab Navigation Logic
        document.getElementById('login-tab').addEventListener('click', function() {
            document.getElementById('login-form').classList.remove('hidden');
            document.getElementById('register-form').classList.add('hidden');
        });

        document.getElementById('register-tab').addEventListener('click', function() {
            document.getElementById('register-form').classList.remove('hidden');
            document.getElementById('login-form').classList.add('hidden');
        });

        // Validate Register Form
        function validateRegisterForm() {
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-confirm-password').value;

            // Password Requirements
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

            if (!passwordRegex.test(password)) {
                alert('Password harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.');
                return false;
            }

            if (password !== confirmPassword) {
                alert('Password dan Konfirmasi Password tidak cocok.');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>

<?php
session_start();
require 'db.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = '';

// Ambil data pengguna yang sedang login
$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "Pengguna tidak ditemukan!";
        exit();
    }
} catch (PDOException $e) {
    $message = "Error: " . $e->getMessage();
}

// Handle perubahan username dan password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $new_username = $_POST['username'];
        $new_password = $_POST['password'];
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Validasi username agar unik
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$new_username, $user_id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $message = "Username sudah digunakan. Pilih username lain.";
            } else {
                // Perbarui username dan password di database
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                $stmt->execute([$new_username, $hashed_password, $user_id]);
                $message = "Profil berhasil diperbarui!";
                $_SESSION['username'] = $new_username; // Update username di session
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }

    // Handle upload foto profil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = $user['username'] . '.' . $file_ext;
            $upload_dir = 'uploads/';
            $upload_path = $upload_dir . $new_file_name;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Perbarui nama file foto profil di database
                try {
                    $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                    $stmt->execute([$new_file_name, $user_id]);
                    $message = "Foto profil berhasil diperbarui!";
                } catch (PDOException $e) {
                    $message = "Error: " . $e->getMessage();
                }
            } else {
                $message = "Gagal mengupload foto profil.";
            }
        } else {
            $message = "Format file tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Pengaturan Akun</h1>
        <p>Selamat datang, <?php echo htmlspecialchars($user['username']); ?>!</p>
        <p><?php echo htmlspecialchars($message); ?></p>

        <!-- Tampilkan Foto Profil -->
        <div class="profile-picture img">
            <img src="uploads/<?php echo htmlspecialchars($user['profile_picture'] ?: 'default.jpg'); ?>" alt="Foto Profil" width="150">
        </div>

        <!-- Form Update Profil -->
        <form method="POST" enctype="multipart/form-data">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="password">Password Baru:</label>
            <input type="password" name="password" id="password" required>

            <label for="profile_picture">Foto Profil Baru:</label>
            <input type="file" name="profile_picture" id="profile_picture" accept="image/*">

            <button type="submit" name="update_profile">Perbarui Profil</button>
        </form>
        <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
    </div>
</body>
</html>

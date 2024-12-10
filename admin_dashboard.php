<?php
session_start();
require 'db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Pesan untuk operasi CRUD
$message = '';

// Handle operasi CRUD untuk tabel child_data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_child'])) {
        // Tambah data anak
        $name = $_POST['name'];
        $age = $_POST['age'];
        $height = $_POST['height'];
        $weight = $_POST['weight'];
        $motor_skill = $_POST['motor_skill'];
        $conclusion = $_POST['conclusion'];
        $parent_id = $_POST['user_id'];

        try {
            $stmt = $pdo->prepare("INSERT INTO child_data (name, age, height, weight, motor_skill, conclusion, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $age, $height, $weight, $motor_skill, $conclusion, $parent_id]);

            $message = "Data anak berhasil ditambahkan!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_child'])) {
        // Update data anak
        $child_id = $_POST['child_id'];
        $name = $_POST['name'];
        $age = $_POST['age'];
        $height = $_POST['height'];
        $weight = $_POST['weight'];
        $motor_skill = $_POST['motor_skill'];
        $conclusion = $_POST['conclusion'];
        $parent_id = $_POST['user_id'];

        try {
            $stmt = $pdo->prepare("UPDATE child_data SET name = ?, age = ?, height = ?, weight = ?, motor_skill = ?, conclusion = ?, user_id = ? WHERE id = ?");
            $stmt->execute([$name, $age, $height, $weight, $motor_skill, $conclusion, $parent_id, $child_id]);

            $message = "Data anak berhasil diperbarui!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_child'])) {
        // Hapus data anak
        $child_id = $_POST['child_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM child_data WHERE id = ?");
            $stmt->execute([$child_id]);
            $message = "Data anak berhasil dihapus!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Ambil data orang tua (users dengan role = 'user')
try {
    $parentDataStmt = $pdo->query("SELECT id, username, email FROM users WHERE role = 'user'");
    $parents = $parentDataStmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil data anak
    $childDataStmt = $pdo->query("SELECT * FROM child_data");
    $children = $childDataStmt->fetchAll(PDO::FETCH_ASSOC);

    // Kelompokkan data anak berdasarkan parent_id
    $childrenByParent = [];
    foreach ($children as $child) {
        $childrenByParent[$child['user_id']][] = $child;
    }
} catch (PDOException $e) {
    $message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css"> <!-- Pastikan CSS Anda konsisten -->
</head>
<body class="admin">
    <div class="container">
        <h1>Dashboard Admin</h1>
        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <div class="tab-container">
            <a href="logout.php" class="tab-btn">Log Out</a>
        </div>

        <h2>Data Orang Tua dan Anak</h2>
        <p><?php echo htmlspecialchars($message); ?></p>

        <!-- Tabel berdasarkan Orang Tua -->
        <?php foreach ($parents as $parent): ?>
            <div class="parent-section">
                <h3>Orang Tua: <?php echo htmlspecialchars($parent['username']); ?> (<?php echo htmlspecialchars($parent['email']); ?>)</h3>
                
                <!-- Tabel Data Anak -->
                <table>
                    <thead>
                        <tr>
                            <th>ID Anak</th>
                            <th>Nama Anak</th>
                            <th>Usia</th>
                            <th>Tinggi (cm)</th>
                            <th>Berat (kg)</th>
                            <th>Kemampuan Motorik</th>
                            <th>Hasil</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($childrenByParent[$parent['id']])): ?>
                            <?php foreach ($childrenByParent[$parent['id']] as $child): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($child['id']); ?></td>
                                    <td><?php echo htmlspecialchars($child['name']); ?></td>
                                    <td><?php echo htmlspecialchars($child['age']); ?></td>
                                    <td><?php echo htmlspecialchars($child['height']); ?></td>
                                    <td><?php echo htmlspecialchars($child['weight']); ?></td>
                                    <td><?php echo htmlspecialchars($child['motor_skill']); ?></td>
                                    <td><?php echo htmlspecialchars($child['conclusion']); ?></td>
                                    <td>
                                        <!-- Form untuk Edit -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="child_id" value="<?php echo htmlspecialchars($child['id']); ?>">
                                            <input type="text" name="name" value="<?php echo htmlspecialchars($child['name']); ?>" required>
                                            <input type="number" name="age" value="<?php echo htmlspecialchars($child['age']); ?>" required>
                                            <input type="number" name="height" value="<?php echo htmlspecialchars($child['height']); ?>" required>
                                            <input type="number" name="weight" value="<?php echo htmlspecialchars($child['weight']); ?>" required>
                                            <input type="text" name="motor_skill" value="<?php echo htmlspecialchars($child['motor_skill']); ?>" required>
                                            <input type="text" name="conclusion" value="<?php echo htmlspecialchars($child['conclusion']); ?>" required>
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($parent['id']); ?>">
                                            <button type="submit" name="update_child">Update</button>
                                        </form>
                                        <!-- Form untuk Delete -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="child_id" value="<?php echo htmlspecialchars($child['id']); ?>">
                                            <button type="submit" name="delete_child" onclick="return confirm('Hapus data ini?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Belum ada data anak untuk orang tua ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        <h2>Tambah Data Anak</h2>
        <!-- Form Tambah Data Anak -->
        <form method="POST">
            <label>Nama:</label>
            <input type="text" name="name" required>
            <label>Usia:</label>
            <input type="number" name="age" required>
            <label>Tinggi (cm):</label>
            <input type="number" name="height" required>
            <label>Berat (kg):</label>
            <input type="number" name="weight" required>
            <label>Kemampuan Motorik:</label>
            <input type="text" name="motor_skill" required>
            <label>Hasil:</label>
            <input type="text" name="conclusion" required>
            <label>Orang Tua:</label>
            <select name="user_id" required>
                <?php foreach ($parents as $parent): ?>
                <option value="<?php echo htmlspecialchars($parent['id']); ?>">
                    <?php echo htmlspecialchars($parent['username']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="add_child">Tambah</button>
        </form>
    </div>
</body>
</html>

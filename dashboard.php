<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "child_growth";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Ambil data perkembangan anak untuk user yang login
$sql = "SELECT * FROM child_data WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

// Ambil data pengguna dari database
$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Pengguna tidak ditemukan!";
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GrowSmart</title>
    <link rel="stylesheet" href="style.css">
</head>
<div class="profile-button">
    <button onclick="location.href='account.php'" class="btn-profile">
        <img src="uploads/<?php echo htmlspecialchars($user['profile_picture'] ?: 'default.jpg'); ?>" alt="Foto Profil" class="profilepicture">
        <span class="username"><?php echo htmlspecialchars($user['username']); ?></span>
    </button>
</div>
<div class="tab-container">
    <a href="logout.php" class="tab-btn">Log Out</a>
</div>
<body>
    <div class="container">
        <h1>GrowSmart</h1>
        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <p>Dashboard Pemantauan Tumbuh Kembang Anak</p>


        <!-- Form Input Data -->
        <div class="form-container">
            <h2>Masukkan Data Perkembangan Anak</h2>
            <label for="name">Nama Anak:</label>
            <input type="text" id="name" placeholder="Masukkan nama anak">

            <label for="age">Umur (Bulan):</label>
            <input type="number" id="age" placeholder="Umur dalam bulan">

            <label for="height">Tinggi (cm):</label>
            <input type="number" id="height" placeholder="Masukkan tinggi dalam cm">

            <label for="weight">Berat (kg):</label>
            <input type="number" id="weight" placeholder="Masukkan berat dalam kg">

            <label for="motor-skill">Kemampuan Motorik (Deskripsi):</label>
            <textarea id="motor-skill" placeholder="Deskripsi kemampuan motorik"></textarea>

            <button id="save-btn">Simpan Data</button>
        </div>

        <!-- Tabel Data Perkembangan Anak -->
        <div class="table-container">
            <h2>Riwayat Perkembangan Anak</h2>
            <table id="growth-table">
                <thead>
                    <tr>
                        <th>Nama Anak</th>
                        <th>Umur (Bulan)</th>
                        <th>Tinggi (cm)</th>
                        <th>Berat (kg)</th>
                        <th>Kemampuan Motorik</th>
                        <th>Kesimpulan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['age']) ?></td>
                        <td><?= htmlspecialchars($row['height']) ?></td>
                        <td><?= htmlspecialchars($row['weight']) ?></td>
                        <td><?= htmlspecialchars($row['motor_skill']) ?></td>
                        <td><?= htmlspecialchars($row['conclusion']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function determineStatus(age, height, weight) {
                let conclusion = "";

                if (weight < height * 0.1) {
                    conclusion = "Terlalu Kurus";
                } else if (weight > height * 0.22) {
                    conclusion = "Terlalu Gendut";
                } else if (height < age * 1.5) {
                    conclusion = "Terlalu Pendek";
                } else if (height > age * 3) {
                    conclusion = "Terlalu Tinggi";
                } else {
                    conclusion = "Normal";
                }

                return conclusion;
            }

            $("#save-btn").click(function() {
                const name = $("#name").val();
                const age = $("#age").val();
                const height = $("#height").val();
                const weight = $("#weight").val();
                const motorSkill = $("#motor-skill").val();

                if (name && age && height && weight && motorSkill) {
                    const conclusion = determineStatus(age, height, weight);

                    $.ajax({
                        url: "save_data.php",
                        method: "POST",
                        data: {
                            name: name,
                            age: age,
                            height: height,
                            weight: weight,
                            motor_skill: motorSkill,
                            conclusion: conclusion
                        },
                        success: function(response) {
                            if (response === "success") {
                                const newRow = `
                                    <tr>
                                        <td>${name}</td>
                                        <td>${age}</td>
                                        <td>${height}</td>
                                        <td>${weight}</td>
                                        <td>${motorSkill}</td>
                                        <td>${conclusion}</td>
                                    </tr>
                                `;
                                $("#growth-table tbody").append(newRow);
                                $("#name").val('');
                                $("#age").val('');
                                $("#height").val('');
                                $("#weight").val('');
                                $("#motor-skill").val('');
                            } else {
                                alert("Gagal menyimpan data!");
                            }
                        }
                    });
                } else {
                    alert("Mohon lengkapi semua data sebelum menyimpan.");
                }
            });
        });
    </script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "unauthorized";
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $motor_skill = $_POST['motor_skill'];
    $conclusion = $_POST['conclusion'];

    $stmt = $conn->prepare("INSERT INTO child_data (user_id, name, age, height, weight, motor_skill, conclusion) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiddss", $user_id, $name, $age, $height, $weight, $motor_skill, $conclusion);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
}
$conn->close();
?>

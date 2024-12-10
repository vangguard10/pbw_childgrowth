<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "child_growth";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Hash password
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // Check if email already exists
    $emailCheckStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $emailCheckStmt->bind_param("s", $email);
    $emailCheckStmt->execute();
    $emailCheckStmt->store_result();

    if ($emailCheckStmt->num_rows > 0) {
        die("Email sudah digunakan.");
    }
    $emailCheckStmt->close();

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "Registration successful!";
        header("Location: index.php");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>

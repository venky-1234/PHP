<?php
session_start();
include 'db.php'; // db.php now only sets up $conn

// Get input (consider sanitizing and validating in production)
$username = trim($_POST['username']);
$password = $_POST['password'];

// Prepare statement: get the user by username
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
if (!$stmt) {
    die(json_encode(["success" => false, "message" => "Preparation failed: " . $conn->error]));
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$response = [];

// Check if user exists and verify the password against the stored hash
if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $response = ["success" => true, "message" => "Login successful! Welcome " . htmlspecialchars($user['first_name'])];
} else {
    $response = ["success" => false, "message" => "Invalid credentials."];
}

echo json_encode($response);
$conn->close();
?>

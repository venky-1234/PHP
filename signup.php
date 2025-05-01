<?php
include 'db.php'; // This file now only sets up $conn

// Retrieve POST data
$data = $_POST;

// Validate that all required fields are present
$required = ['first_name', 'last_name', 'email', 'phone', 'username', 'password'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(["success" => false, "message" => "Missing field: $field"]);
        exit;
    }
}

// Hash the password using BCRYPT
$passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

// Prepare the SQL query using placeholders
$sql = "INSERT INTO users (first_name, last_name, email, phone, username, password) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// Check if preparation succeeded
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Preparation failed: " . $conn->error]);
    exit;
}

// Bind the parameters (all six are strings)
$stmt->bind_param(
    "ssssss",
    $data['first_name'],
    $data['last_name'],
    $data['email'],
    $data['phone'],
    $data['username'],
    $passwordHash
);

// Execute the statement and return the response
if ($stmt->execute()) {
    $response = ["success" => true, "message" => "Signup successful"];
} else {
    $response = ["success" => false, "message" => "Signup failed: " . $stmt->error];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>

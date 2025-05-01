<?php
// prices.php (Price Management)

include 'db.php';

// Fetch Products for dropdown
$products = [];
$result = $conn->query("SELECT product_id, product_name FROM products");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle Add Price
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'add_price') {
    $productId = $_POST['product_id'];
    $price = $_POST['price'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $stmt = $conn->prepare("INSERT INTO prices (product_id, price, start_date, end_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $productId, $price, $startDate, $endDate);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Price added successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Handle Edit Price
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'edit_price') {
    $priceId = $_POST['price_id'];
    $productId = $_POST['product_id'];
    $price = $_POST['price'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $stmt = $conn->prepare("UPDATE prices SET product_id = ?, price = ?, start_date = ?, end_date = ? WHERE price_id = ?");
    $stmt->bind_param("idssi", $productId, $price, $startDate, $endDate, $priceId);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Price updated successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Handle Delete Price
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'delete_price') {
    $priceId = $_POST['price_id'];
    $stmt = $conn->prepare("DELETE FROM prices WHERE price_id = ?");
    $stmt->bind_param("i", $priceId);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Price deleted successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Fetch Prices Data
$prices = [];
$result = $conn->query("
    SELECT prices.*, products.product_name 
    FROM prices 
    JOIN products ON prices.product_id = products.product_id
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $prices[] = $row;
    }
}
?>

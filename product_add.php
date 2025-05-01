<?php
// products.php (Product Management)

include 'db.php';

// Fetch Categories and Suppliers for dropdowns
$categories = [];
$result = $conn->query("SELECT category_id, category_name FROM categories");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$suppliers = [];
$result = $conn->query("SELECT supplier_id, supplier_name FROM suppliers");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'add_product') {
    $productName = $_POST['product_name'];
    $categoryId = $_POST['category_id'];
    $supplierId = $_POST['supplier_id'];
    $stmt = $conn->prepare("INSERT INTO products (product_name, category_id, supplier_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $productName, $categoryId, $supplierId);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Product added successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Handle Edit Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'edit_product') {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $categoryId = $_POST['category_id'];
    $supplierId = $_POST['supplier_id'];
    $stmt = $conn->prepare("UPDATE products SET product_name = ?, category_id = ?, supplier_id = ? WHERE product_id = ?");
    $stmt->bind_param("siii", $productName, $categoryId, $supplierId, $productId);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Product updated successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Handle Delete Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'delete_product') {
    $productId = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Product deleted successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Fetch Products Data
$products = [];
$result = $conn->query("
    SELECT p.*, c.category_name, s.supplier_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

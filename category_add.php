<?php
// categories.php (Category Management)

include 'db.php';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'add_category') {
    $categoryName = $_POST['category_name'];
    $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $categoryName);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Category added successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'edit_category') {
    $categoryId = $_POST['category_id'];
    $categoryName = $_POST['category_name'];
    $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
    $stmt->bind_param("si", $categoryName, $categoryId);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Category updated successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'delete_category') {
    $categoryId = $_POST['category_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Category deleted successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Fetch Categories Data
$categories = [];
$result = $conn->query("SELECT * FROM categories");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

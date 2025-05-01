<?php
// suppliers.php (Supplier Management)

include 'db.php';

// Handle Add Supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'add_supplier') {
    $supplierName = $_POST['supplier_name'];
    $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name) VALUES (?)");
    $stmt->bind_param("s", $supplierName);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Supplier added successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Handle Edit Supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'edit_supplier') {
    $supplierId = $_POST['supplier_id'];
    $supplierName = $_POST['supplier_name'];
    $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ? WHERE supplier_id = ?");
    $stmt->bind_param("si", $supplierName, $supplierId);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Supplier updated successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Handle Delete Supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'delete_supplier') {
    $supplierId = $_POST['supplier_id'];
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
    $stmt->bind_param("i", $supplierId);
    if ($stmt->execute()) {
        $message = ['success' => true, 'message' => 'Supplier deleted successfully'];
    } else {
        $message = ['success' => false, 'message' => 'Error: ' . $stmt->error];
    }
    $stmt->close();
}

// Fetch Suppliers Data
$suppliers = [];
$result = $conn->query("SELECT * FROM suppliers");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}
?>

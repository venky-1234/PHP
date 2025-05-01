<?php
// combined_page.php

include 'db.php';
include 'category_add.php';
include 'supplier_add.php';
include 'product_add.php';
include 'price_add.php';
// Initialize variables
$message = [];
$edit_data = [];

// Handle form submissions and actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'add_category':
            $categoryName = $_POST['category_name'];
            $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
            $stmt->bind_param("s", $categoryName);
            if ($stmt->execute()) {
                $message = ['success' => true, 'message' => 'Category added successfully'];
            } else {
                $message = ['success' => false, 'message' => 'Error: ' . $conn->error];
            }
            $stmt->close();
            break;

        case 'add_supplier':
            $supplierName = $_POST['supplier_name'];
            $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name) VALUES (?)");
            $stmt->bind_param("s", $supplierName);
            if ($stmt->execute()) {
                $message = ['success' => true, 'message' => 'Supplier added successfully'];
            } else {
                $message = ['success' => false, 'message' => 'Error: ' . $conn->error];
            }
            $stmt->close();
            break;

        case 'add_product':
            $productName = $_POST['product_name'];
            $categoryId = $_POST['category_id'];
            $supplierId = $_POST['supplier_id'];
            $stmt = $conn->prepare("INSERT INTO products (product_name, category_id, supplier_id) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $productName, $categoryId, $supplierId);
            if ($stmt->execute()) {
                $message = ['success' => true, 'message' => 'Product added successfully'];
            } else {
                $message = ['success' => false, 'message' => 'Error: ' . $conn->error];
            }
            $stmt->close();
            break;

        case 'add_price':
            $productId = $_POST['product_id'];
            $price = $_POST['price'];
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            $stmt = $conn->prepare("INSERT INTO prices (product_id, price, start_date, end_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $productId, $price, $startDate, $endDate);
            if ($stmt->execute()) {
                $message = ['success' => true, 'message' => 'Price added successfully'];
            } else {
                $message = ['success' => false, 'message' => 'Error: ' . $conn->error];
            }
            $stmt->close();
            break;

        case 'edit_category':
            $categoryId = $_POST['category_id'];
            $categoryName = $_POST['category_name'];
            $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
            $stmt->bind_param("si", $categoryName, $categoryId);
            if ($stmt->execute()) {
                $message = ['success' => true, 'message' => 'Category updated successfully'];
            } else {
                $message = ['success' => false, 'message' => 'Error: ' . $conn->error];
            }
            $stmt->close();
            break;

        case 'delete_category':
            $categoryId = $_POST['category_id'];
            $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->bind_param("i", $categoryId);
            if ($stmt->execute()) {
                $message = ['success' => true, 'message' => 'Category deleted successfully'];
            } else {
                $message = ['success' => false, 'message' => 'Error: ' . $conn->error];
            }
            $stmt->close();
            break;

        case 'get_category':
            $categoryId = $_POST['category_id'];
            $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
            $stmt->bind_param("i", $categoryId);
            $stmt->execute();
            $result = $stmt->get_result();
            $edit_data['category'] = $result->fetch_assoc();
            $stmt->close();
            break;

            // Add similar cases for suppliers, products, and prices
    }
}

// Fetch existing data
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
$suppliers = $conn->query("SELECT * FROM suppliers")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("
    SELECT p.*, c.category_name, s.supplier_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
")->fetch_all(MYSQLI_ASSOC);
$prices = $conn->query("
    SELECT prices.*, products.product_name
    FROM prices
    JOIN products ON prices.product_id = products.product_id
")->fetch_all(MYSQLI_ASSOC);

// Handle AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $id = intval($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $tableMap = ['category' => 'categories', 'supplier' => 'suppliers', 'product' => 'products'];

    if (strpos($action, 'delete_') === 0) {
        $section = str_replace('delete_', '', $action);
        $table = $tableMap[$section] ?? '';
        if ($table) $conn->query("DELETE FROM `$table` WHERE id = $id");
        exit;
    }

    if (strpos($action, 'get_') === 0) {
        $section = str_replace('get_', '', $action);
        $table = $tableMap[$section] ?? '';
        if ($table) {
            $result = $conn->query("SELECT * FROM `$table` WHERE id = $id");
            echo json_encode($result->fetch_assoc());
        }
        exit;
    }

    if (isset($_POST['update']) && isset($_POST['section'])) {
        $table = $tableMap[$_POST['section']];
        $conn->query("UPDATE `$table` SET name = '$name' WHERE id = $id");
        header("Location: dashboard.php");
        exit;
    }

    if (isset($_POST['add']) && isset($_POST['section'])) {
        $table = $tableMap[$_POST['section']];
        $conn->query("INSERT INTO `$table` (name) VALUES ('$name')");
        header("Location: dashboard.php");
        exit;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Inventory Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        /* Reset & base */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background: #f0f4f8;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            letter-spacing: 1px;
        }

        nav {
            background: #34495e;
            display: flex;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        nav button {
            background: none;
            border: none;
            color: #ecf0f1;
            padding: 1rem 2rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        nav button:hover,
        nav button.active {
            background: #1abc9c;
            color: #fff;
            outline: none;
        }

        main {
            flex-grow: 1;
            max-width: 1200px;
            margin: 2rem auto 4rem;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        section {
            display: none;
            animation: fadeIn 0.5s ease forwards;
        }

        section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .input-group {
            position: relative;
        }

        .input-group input,
        .input-group select,
        .input-group textarea {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border-radius: 8px;
            border: 1.5px solid #ccc;
            font-size: 1rem;
            transition: border-color 0.3s;
            outline: none;
            background: #fafafa;
        }

        .input-group input:focus,
        .input-group select:focus,
        .input-group textarea:focus {
            border-color: #1abc9c;
            background: #fff;
        }

        .input-group label {
            position: absolute;
            left: 3.2rem;
            top: 1.1rem;
            color: #999;
            font-size: 1rem;
            pointer-events: none;
            transition: all 0.3s;
            background: white;
            padding: 0 0.3rem;
        }

        .input-group input:focus+label,
        .input-group input:not(:placeholder-shown)+label,
        .input-group select:focus+label,
        .input-group select:not([value=""])+label,
        .input-group textarea:focus+label,
        .input-group textarea:not(:placeholder-shown)+label {
            top: -0.7rem;
            left: 2.8rem;
            font-size: 0.8rem;
            color: #1abc9c;
            font-weight: 600;
        }

        .input-group .fa {
            position: absolute;
            left: 1rem;
            top: 1.1rem;
            color: #1abc9c;
            font-size: 1.2rem;
        }

        button.submit-btn {
            background: #1abc9c;
            border: none;
            padding: 1rem 0;
            border-radius: 8px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button.submit-btn:hover {
            background: #16a085;
        }

        .message {
            text-align: center;
            font-weight: 600;
            margin-top: 0.5rem;
            min-height: 1.3rem;
            color: #e74c3c;
        }

        .message.success {
            color: #27ae60;
        }

        table.dataTable {
            width: 100% !important;
            margin-bottom: 1rem;
            border-collapse: collapse;
        }

        table.dataTable th,
        table.dataTable td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        table.dataTable thead th {
            background-color: #f2f2f2;
            border-bottom: 2px solid #dee2e6;
        }

        table.dataTable tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        table.dataTable tbody tr:hover {
            background-color: #e9ecef;
        }

        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 0.75rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5rem 0.75rem;
            margin: 0 0.25rem;
            border: 1px solid #ced4da;
            background-color: #fff;
            color: #495057;
            cursor: pointer;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #6c757d;
            cursor: not-allowed;
        }

        /* Custom styles for the edit form */
        .edit-form {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .edit-form.active {
            display: block;
        }
    </style>
</head>

<body>

    <header>Inventory Management</header>

    <nav>
        <button class="tab-btn active" data-target="categoriesSection"><i class="fa fa-list"></i> Categories</button>
        <button class="tab-btn" data-target="suppliersSection"><i class="fa fa-truck"></i> Suppliers</button>
        <button class="tab-btn" data-target="productsSection"><i class="fa fa-box"></i> Products</button>
        <button class="tab-btn" data-target="pricesSection"><i class="fa fa-tag"></i> Prices</button>
    </nav>

    <main>
        <!-- Categories Section -->
        <section id="categoriesSection" class="active">
            <h2>Manage Categories</h2>
            <form id="categoryForm" method="post">
                <input type="hidden" name="action" value="add_category">
                <div class="input-group">
                    <i class="fa fa-list"></i>
                    <input type="text" id="category_name" name="category_name" placeholder=" " required />
                    <label for="category_name">Category Name</label>
                </div>
                <button type="submit" class="submit-btn">Add Category</button>
                <?php if (isset($message) && isset($message['success']) && $_POST['action'] == 'add_category'): ?>
                    <div class="message success">
                        <?= htmlspecialchars($message['message']) ?>
                    </div>
                <?php endif; ?>
            </form>

            <h2>Categories List</h2>
            <table id="categoriesTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($category['category_id']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($category['category_name']) ?>
                            </td>
                            <td>
                                <button class="edit-btn" data-id="<?= htmlspecialchars($category['category_id']) ?>">Edit</button>
                                <button class="delete-btn"
                                    data-id="<?= htmlspecialchars($category['category_id']) ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (isset($edit_data['category'])): ?>
                <div class="edit-form active">
                    <h3>Edit Category</h3>
                    <form id="editCategoryForm" method="post">
                        <input type="hidden" name="action" value="edit_category">
                        <input type="hidden" name="category_id"
                            value="<?= htmlspecialchars($edit_data['category']['category_id']) ?>">
                        <div class="input-group">
                            <i class="fa fa-list"></i>
                            <input type="text" id="edit_category_name" name="category_name" placeholder=" "
                                value="<?= htmlspecialchars($edit_data['category']['category_name']) ?>" required />
                            <label for="edit_category_name">Category Name</label>
                        </div>
                        <button type="submit" class="submit-btn">Update Category</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>

        <!-- Suppliers Section -->
        <section id="suppliersSection">
            <h2>Manage Suppliers</h2>
            <form id="supplierForm" method="post">
                <input type="hidden" name="action" value="add_supplier">
                <div class="input-group">
                    <i class="fa fa-truck"></i>
                    <input type="text" id="supplier_name" name="supplier_name" placeholder=" " required />
                    <label for="supplier_name">Supplier Name</label>
                </div>
                <button type="submit" class="submit-btn">Add Supplier</button>
                <?php if (isset($message) && isset($message['success']) && $_POST['action'] == 'add_supplier'): ?>
                    <div class="message success">
                        <?= htmlspecialchars($message['message']) ?>
                    </div>
                <?php endif; ?>
            </form>

            <h2>Suppliers List</h2>
            <table id="suppliersTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Supplier Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($supplier['supplier_id']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($supplier['supplier_name']) ?>
                            </td>
                            <td>
                                <button class="edit-btn" data-id="<?= htmlspecialchars($supplier['supplier_id']) ?>">Edit</button>
                                <button class="delete-btn"
                                    data-id="<?= htmlspecialchars($supplier['supplier_id']) ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Products Section -->
        <section id="productsSection">
            <h2>Manage Products</h2>
            <form id="productForm" method="post">
                <input type="hidden" name="action" value="add_product">
                <div class="input-group">
                    <i class="fa fa-box"></i>
                    <input type="text" id="product_name" name="product_name" placeholder=" " required />
                    <label for="product_name">Product Name</label>
                </div>
                <div class="input-group">
                    <i class="fa fa-list"></i>
                    <select id="category_id" name="category_id" required>
                        <option value="" disabled selected></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category_id']) ?>">
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="category_id">Category</label>
                </div>
                <div class="input-group">
                    <i class="fa fa-truck"></i>
                    <select id="supplier_id" name="supplier_id" required>
                        <option value="" disabled selected></option>
                        <?php foreach ($suppliers as $sup): ?>
                            <option value="<?= htmlspecialchars($sup['supplier_id']) ?>">
                                <?= htmlspecialchars($sup['supplier_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="supplier_id">Supplier</label>
                </div>
                <button type="submit" class="submit-btn">Add Product</button>
                <?php if (isset($message) && isset($message['success']) && $_POST['action'] == 'add_product'): ?>
                    <div class="message success">
                        <?= htmlspecialchars($message['message']) ?>
                    </div>
                <?php endif; ?>
            </form>

            <h2>Products List</h2>
            <table id="productsTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($product['product_id']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($product['product_name']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($product['category_name']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($product['supplier_name']) ?>
                            </td>
                            <td>
                                <button class="edit-btn" data-id="<?= htmlspecialchars($product['product_id']) ?>">Edit</button>
                                <button class="delete-btn"
                                    data-id="<?= htmlspecialchars($product['product_id']) ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Prices Section -->
        <section id="pricesSection">
            <h2>Manage Prices</h2>
            <form id="priceForm" method="post">
                <input type="hidden" name="action" value="add_price">
                <div class="input-group">
                    <i class="fa fa-box"></i>
                    <select id="price_product_id" name="product_id" required>
                        <option value="" disabled selected></option>
                        <?php foreach ($products as $prod): ?>
                            <option value="<?= htmlspecialchars($prod['product_id']) ?>">
                                <?= htmlspecialchars($prod['product_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="price_product_id">Product</label>
                </div>
                <div class="input-group">
                    <i class="fa fa-tag"></i>
                    <input type="number" step="0.01" min="0" id="price" name="price" placeholder=" " required />
                    <label for="price">Price</label>
                </div>
                <div class="input-group">
                    <i class="fa fa-calendar"></i>
                    <input type="date" id="start_date" name="start_date" placeholder=" " required />
                    <label for="start_date">Start Date</label>
                </div>
                <div class="input-group">
                    <i class="fa fa-calendar"></i>
                    <input type="date" id="end_date" name="end_date" placeholder=" " required />
                    <label for="end_date">End Date</label>
                </div>
                <button type="submit" class="submit-btn">Add Price</button>
                <?php if (isset($message) && isset($message['success']) && $_POST['action'] == 'add_price'): ?>
                    <div class="message success">
                        <?= htmlspecialchars($message['message']) ?>
                    </div>
                <?php endif; ?>
            </form>

            <h2>Prices List</h2>
            <table id="pricesTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prices as $price): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($price['price_id']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($price['product_name']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($price['price']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($price['start_date']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($price['end_date']) ?>
                            </td>
                            <td>
                                <button class="edit-btn" data-id="<?= htmlspecialchars($price['price_id']) ?>">Edit</button>
                                <button class="delete-btn"
                                    data-id="<?= htmlspecialchars($price['price_id']) ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- jQuery & DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>

   <script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#categoriesTable, #suppliersTable, #productsTable, #pricesTable').DataTable();

        // Tab Navigation
        $('.tab-btn').on('click', function () {
            $('.tab-btn').removeClass('active');
            $(this).addClass('active');
            $('section').removeClass('active');
            $('#' + $(this).data('target')).addClass('active');
        });

        // Edit
        $('table').on('click', '.edit-btn', function () {
            const id = $(this).data('id');
            const section = $(this).closest('section').attr('id').replace('Section', '').toLowerCase();

            $.post('get_entry.php', {
                action: 'get_' + section,
                id: id
            }, function (response) {
                const data = JSON.parse(response);
                // Populate the form (you can customize this per section)
                $('#' + section + '_form input[name="id"]').val(data.id);
                $('#' + section + '_form input[name="' + section + '_name"]').val(data.name);
                $('#' + section + '_form .edit-form').addClass('active');
            });
        });

        // Delete
        $('table').on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            const section = $(this).closest('section').attr('id').replace('Section', '').toLowerCase();
            const action = 'delete_' + section;

            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the entry!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('delete_entry.php', {
                        action: action,
                        id: id
                    }, function () {
                        Swal.fire('Deleted!', 'The entry has been deleted.', 'success')
                            .then(() => window.location.reload());
                    });
                }
            });
        });
    });
</script>


</body>

</html>

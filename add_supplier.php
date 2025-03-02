<?php
include 'db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = trim($_POST['supplier_id']); // Manually entered supplier ID
    $supplier_name = trim($_POST['supplier_name']);
    $contact_number = trim($_POST['contact_number']);
    $products = $_POST['products'] ?? [];

    if (empty($supplier_id) || empty($supplier_name) || empty($contact_number) || empty($products)) {
        echo "<script>alert('All fields are required!'); window.history.back();</script>";
        exit;
    }

    // Insert supplier details with manual supplier_id
    $stmt_supplier = $conn->prepare("INSERT INTO suppliers (supplier_id, name, contact_number) VALUES (?, ?, ?)");
    if (!$stmt_supplier) {
        die("Error preparing supplier statement: " . $conn->error);
    }
    $stmt_supplier->bind_param("sss", $supplier_id, $supplier_name, $contact_number);
    
    if ($stmt_supplier->execute()) {
        // Insert supplier-product mapping
        $stmt_mapping = $conn->prepare("INSERT INTO stock_supplier (supplier_id, product_id) VALUES (?, ?)");
        if (!$stmt_mapping) {
            die("Error preparing product mapping statement: " . $conn->error);
        }
        $stmt_mapping->bind_param("ss", $supplier_id, $product_id);

        foreach ($products as $product_id) {
            if (!$stmt_mapping->execute()) {
                die("Error inserting product mapping: " . $stmt_mapping->error);
            }
        }
        echo "<script>alert('Supplier and products added successfully!'); window.location.href='index.php?page=suppliers';</script>";
    } else {
        die("Error adding supplier: " . $stmt_supplier->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h2 class="text-center mb-4">Add New Supplier</h2>
            <form method="POST" action="add_supplier.php">
                <div class="mb-3">
                    <label class="form-label">Supplier ID:</label>
                    <input type="text" class="form-control" name="supplier_id" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Supplier Name:</label>
                    <input type="text" class="form-control" name="supplier_name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contact Number:</label>
                    <input type="text" class="form-control" name="contact_number" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Products Supplied:</label>
                    <select class="form-select" name="products[]" multiple required>
                        <?php
                        $result = $conn->query("SELECT product_id, name FROM products");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['product_id']}'>{$row['name']}</option>";
                            }
                        } else {
                            echo "<option value=''>No products available</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-success w-100">Add Supplier</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
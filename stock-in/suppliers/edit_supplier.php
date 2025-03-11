<?php
include 'db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['supplier_id'])) {
    $supplier_id = $_GET['supplier_id'];
    $result = $conn->query("SELECT * FROM suppliers WHERE supplier_id = '$supplier_id'");
    $supplier = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = trim($_POST['supplier_id']);
    $supplier_name = trim($_POST['supplier_name']);
    $contact_number = trim($_POST['contact_number']);
    $products = $_POST['products'] ?? [];

    if (empty($supplier_id) || empty($supplier_name) || empty($contact_number) || empty($products)) {
        echo "<script>alert('All fields are required!'); window.history.back();</script>";
        exit;
    }

    // Update supplier details
    $stmt_supplier = $conn->prepare("UPDATE suppliers SET name = ?, contact_number = ? WHERE supplier_id = ?");
    if (!$stmt_supplier) {
        die("Error preparing supplier statement: " . $conn->error);
    }
    $stmt_supplier->bind_param("sss", $supplier_name, $contact_number, $supplier_id);
    
    if ($stmt_supplier->execute()) {
        // Delete existing supplier-product mappings
        $conn->query("DELETE FROM stock_supplier WHERE supplier_id = '$supplier_id'");

        // Insert new supplier-product mappings
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
        echo "<script>alert('Supplier and products updated successfully!'); window.location.href='index.php?page=suppliers';</script>";
    } else {
        die("Error updating supplier: " . $stmt_supplier->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h2 class="text-center mb-4">Edit Supplier</h2>
            <form method="POST" action="edit_supplier.php?supplier_id=<?php echo $supplier_id; ?>">
                <div class="mb-3">
                    <label class="form-label">Supplier ID:</label>
                    <input type="text" class="form-control" name="supplier_id" value="<?php echo $supplier['supplier_id']; ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Supplier Name:</label>
                    <input type="text" class="form-control" name="supplier_name" value="<?php echo $supplier['name']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contact Number:</label>
                    <input type="text" class="form-control" name="contact_number" value="<?php echo $supplier['contact_number']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Products Supplied:</label>
                    <select class="form-select" name="products[]" multiple required>
                        <?php
                        $result = $conn->query("SELECT product_id, name FROM products");
                        $supplier_products = $conn->query("SELECT product_id FROM stock_supplier WHERE supplier_id = '$supplier_id'")->fetch_all(MYSQLI_ASSOC);
                        $supplier_product_ids = array_column($supplier_products, 'product_id');

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $selected = in_array($row['product_id'], $supplier_product_ids) ? 'selected' : '';
                                echo "<option value='{$row['product_id']}' $selected>{$row['name']}</option>";
                            }
                        } else {
                            echo "<option value=''>No products available</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-success w-100">Update Supplier</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
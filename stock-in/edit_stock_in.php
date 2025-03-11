<?php
include 'db_connection.php';

// 1) Check if we're handling a POST to update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_stock_in'])) {
        // Retrieve form data
        $stock_in_id   = $_POST['stock_in_id'];
        $supplier_id   = $_POST['supplier_id'];
        $product_id    = $_POST['product_id'];
        $quantity      = $_POST['quantity'];
        $total_cost    = $_POST['total_cost'];
        $purchase_date = $_POST['purchase_date'];
        $orig_price    = $_POST['orig_price'];

        // Update stockintransaction
        $stmt = $conn->prepare("
            UPDATE stockintransaction
               SET supplier_id   = ?,
                   product_id    = ?,
                   quantity      = ?,
                   total_cost    = ?,
                   purchase_date = ?,
                   orig_price    = ?
             WHERE stock_in_id   = ?
        ");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        // quantity (int), total_cost/orig_price (decimal), supplier_id/product_id (string)
        $stmt->bind_param(
            "ssidsdi",
            $supplier_id,
            $product_id,
            $quantity,
            $total_cost,
            $purchase_date,
            $orig_price,
            $stock_in_id
        );
        if (!$stmt->execute()) {
            die("Error updating stockintransaction: " . $stmt->error);
        }
        $stmt->close();

        // (Optional) If you want to update the `stocks` table too, do it here.
        // Or rely on triggers, etc.

        echo "<script>
                alert('Stock-in details updated successfully!');
                window.location.href='index.php?page=inventory';
              </script>";
        exit;
    }
}
// 2) If not a POST, check for GET param to load the form
elseif (isset($_GET['stock_in_id'])) {
    $stock_in_id = $_GET['stock_in_id'];

    // Fetch the row from stockintransaction
    $stmt = $conn->prepare("
        SELECT * 
          FROM stockintransaction
         WHERE stock_in_id = ?
    ");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $stock_in_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("No stock-in record found with the given ID.");
    }
    $stock_in = $result->fetch_assoc();
    $stmt->close();
} else {
    // No POST, no GET => invalid request
    die("Invalid request.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Stock In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Edit Stock In</h5>

                <?php if (isset($stock_in)): ?>
                    <form method="POST" action="">
                        <!-- Hidden field to identify the record -->
                        <input type="hidden" name="stock_in_id" value="<?php echo $stock_in['stock_in_id']; ?>">

                        <!-- Supplier -->
                        <div class="mb-3">
                            <label class="form-label">Supplier</label>
                            <select class="form-select" name="supplier_id" required>
                                <option value="">Select Supplier</option>
                                <?php
                                $suppliers = $conn->query("SELECT * FROM suppliers");
                                while ($row = $suppliers->fetch_assoc()) {
                                    $selected = ($row['supplier_id'] === $stock_in['supplier_id']) ? 'selected' : '';
                                    echo "<option value='{$row['supplier_id']}' $selected>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Product -->
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Choose Product</option>
                                <?php
                                $products = $conn->query("SELECT * FROM products");
                                while ($row = $products->fetch_assoc()) {
                                    $selected = ($row['product_id'] === $stock_in['product_id']) ? 'selected' : '';
                                    echo "<option value='{$row['product_id']}' $selected>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity"
                                value="<?php echo $stock_in['quantity']; ?>" required>
                        </div>

                        <!-- Original Price -->
                        <div class="mb-3">
                            <label class="form-label">Original Price</label>
                            <input type="number" step="0.01" class="form-control" name="orig_price"
                                value="<?php echo $stock_in['orig_price']; ?>" required>
                        </div>

                        <!-- Total Cost -->
                        <div class="mb-3">
                            <label class="form-label">Total Cost</label>
                            <input type="number" step="0.01" class="form-control" name="total_cost"
                                value="<?php echo $stock_in['total_cost']; ?>" required>
                        </div>

                        <!-- Purchase Date -->
                        <div class="mb-3">
                            <label class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" name="purchase_date"
                                value="<?php echo $stock_in['purchase_date']; ?>" required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" name="update_stock_in" class="btn btn-success">
                            Update Stock In
                        </button>
                    </form>
                <?php else: ?>
                    <p>No stock-in record loaded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
<?php
$conn->close();
?>
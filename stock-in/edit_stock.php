<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit_stock'])) {
        $stock_id = $_POST['stock_id'];
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $original_price = $_POST['original_price'];
        $expiry_date = $_POST['expiry_date'];

        // Update stocks
        $query = "UPDATE stocks SET product_id = '$product_id', quantity = '$quantity', original_price = '$original_price', expiry_date = '$expiry_date' WHERE stock_id = '$stock_id'";
        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Stock details updated successfully!'); window.location.href='index.php?page=inventory';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
} else {
    if (isset($_GET['stock_id'])) {
        $stock_id = $_GET['stock_id'];
        $stock = $conn->query("SELECT * FROM stocks WHERE stock_id = '$stock_id'")->fetch_assoc();
        if ($stock) {
            $product = $conn->query("SELECT * FROM products WHERE product_id = '{$stock['product_id']}'")->fetch_assoc();
        } else {
            echo "<script>alert('No stock found with the provided ID!'); window.location.href='index.php?page=inventory';</script>";
        }
    } else {
        echo "<script>alert('No stock ID provided!'); window.location.href='index.php?page=inventory';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Edit Stock</h5>
                <form method="POST" action="">
                    <input type="hidden" name="stock_id" value="<?php echo $stock['stock_id']; ?>">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product</label>
                        <select class="form-select" name="product_id" id="product_id" required>
                            <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                            <?php
                            $products = $conn->query("SELECT * FROM products");
                            while ($row = $products->fetch_assoc()) {
                                if ($row['product_id'] != $product['product_id']) {
                                    echo "<option value='{$row['product_id']}'>{$row['name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" value="<?php echo $stock['quantity']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="original_price" class="form-label">Original Price</label>
                        <input type="number" step="0.01" class="form-control" name="original_price" value="<?php echo $stock['original_price']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date" value="<?php echo $stock['expiry_date']; ?>" required>
                    </div>
                    <button type="submit" name="edit_stock" class="btn btn-success">Update Stock</button>
                    <a href="index.php?page=inventory" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
<?php
$conn->close();
?>
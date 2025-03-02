<?php
include 'db_connection.php';

// Fetch available products with stock details
$products = $conn->query("
    SELECT p.product_id, p.name, p.category, p.selling_price, s.quantity as stock_quantity, s.expiry_date, s.stock_id 
    FROM products p 
    JOIN stocks s ON p.product_id = s.product_id 
    WHERE s.quantity > 0 
    ORDER BY s.expiry_date ASC
");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['cart'] as $stock_id => $quantity) {
        if ($quantity > 0) {
            // Fetch stock details
            $stock = $conn->query("SELECT * FROM stocks WHERE stock_id = '$stock_id'")->fetch_assoc();
            $product_id = $stock['product_id'];
            $expiry_date = $stock['expiry_date'];
            $category = $conn->query("SELECT category FROM products WHERE product_id = '$product_id'")->fetch_assoc()['category'];

            // Update stocks quantity
            $conn->query("UPDATE stocks SET quantity = quantity - $quantity WHERE stock_id = '$stock_id'");

            // Insert stockout details
            $stmt_stockout = $conn->prepare("INSERT INTO stockout (stock_out_id, product_id, stock_id, category, quantity, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stock_out_id = uniqid('stockout_');
            $stmt_stockout->bind_param("ssisis", $stock_out_id, $product_id, $stock_id, $category, $quantity, $expiry_date);
            $stmt_stockout->execute();
        }
    }

    echo "<script>alert('Stock out processed successfully!'); window.location.href='index.php?page=stock_out';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Stock Out</h5>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="products" class="form-label">Available Products</label>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock Quantity</th>
                                    <th>Expiry Date</th>
                                    <th>Stock Out Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $products->fetch_assoc()) { 
                                    $is_expired = strtotime($row['expiry_date']) < time();
                                    ?>
                                    <tr class="<?php echo $is_expired ? 'table-danger' : ''; ?>">
                                        <td><?php echo $row['product_id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['category']; ?></td>
                                        <td><?php echo $row['selling_price']; ?></td>
                                        <td><?php echo $row['stock_quantity']; ?></td>
                                        <td><?php echo $row['expiry_date']; ?></td>
                                        <td>
                                            <input type="number" name="cart[<?php echo $row['stock_id']; ?>]" min="0" max="<?php echo $row['stock_quantity']; ?>" value="0">
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success">Process Stock Out</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
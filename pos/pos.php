<?php
include '../db_connection.php';

// Fetch available products
$products = $conn->query("SELECT * FROM products WHERE quantity > 0");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = uniqid('order_');
    $employee_id = 1; // Assuming a logged-in employee with ID 1
    $transaction_date = date('Y-m-d');
    $status = 'Completed';
    $total_amount = 0;

    // Insert order details
    $stmt_order = $conn->prepare("INSERT INTO `order` (order_id, employee_id, transaction_date, status) VALUES (?, ?, ?, ?)");
    $stmt_order->bind_param("siss", $order_id, $employee_id, $transaction_date, $status);
    $stmt_order->execute();

    // Process each product in the cart
    foreach ($_POST['cart'] as $product_id => $quantity) {
        if ($quantity > 0) {
            $product = $conn->query("SELECT * FROM products WHERE product_id = '$product_id'")->fetch_assoc();
            $selling_price = $product['selling_price'];
            $total_amount += $selling_price * $quantity;

            // Update product quantity
            $conn->query("UPDATE products SET quantity = quantity - $quantity WHERE product_id = '$product_id'");

            // Insert stockout details
            $stmt_stockout = $conn->prepare("INSERT INTO stockout (stock_out_id, product_id, quantity) VALUES (?, ?, ?)");
            $stock_out_id = uniqid('stockout_');
            $stmt_stockout->bind_param("ssi", $stock_out_id, $product_id, $quantity);
            $stmt_stockout->execute();
        }
    }

    // Insert payment details
    $payment_id = uniqid('payment_');
    $stmt_payment = $conn->prepare("INSERT INTO payment (payment_id, order_id, total_amount) VALUES (?, ?, ?)");
    $stmt_payment->bind_param("ssd", $payment_id, $order_id, $total_amount);
    $stmt_payment->execute();

    echo "<script>alert('Order processed successfully!'); window.location.href='pos.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Point of Sale</h5>
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
                                    <th>Quantity</th>
                                    <th>Add to Cart</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $products->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo $row['product_id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['category']; ?></td>
                                        <td><?php echo $row['selling_price']; ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td>
                                            <input type="number" name="cart[<?php echo $row['product_id']; ?>]" min="1" max="<?php echo $row['quantity']; ?>" value="0">
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success">Process Order</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
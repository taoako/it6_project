<?php
include '../dbcon/db_connection.php';

$records_per_page = 5; // Number of records per page

$page = isset($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
$start_from = ($page - 1) * $records_per_page;
$total_pages_sql = "SELECT COUNT(*) FROM stocks WHERE quantity > 0";
$result = $conn->query($total_pages_sql);
$total_rows = $result->fetch_array()[0];
$total_pages = ceil($total_rows / $records_per_page);

// Fetch available products with stock details
$products = $conn->query("
    SELECT p.product_id, p.name, c.name AS category_name, s.quantity as stock_quantity, s.expiry_date, s.stock_id 
    FROM products p 
    JOIN stocks s ON p.product_id = s.product_id 
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE s.quantity > 0 
    ORDER BY s.expiry_date ASC
    LIMIT $start_from, $records_per_page
");

if (!$products) {
    die("Error fetching products: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['cart'] as $stock_id => $quantity) {
        if ($quantity > 0) {
            // Fetch stock details
            $stock_query = $conn->query("SELECT * FROM stocks WHERE stock_id = '$stock_id'");
            if (!$stock_query) {
                die("Error fetching stock details: " . $conn->error);
            }
            $stock = $stock_query->fetch_assoc();
            $product_id = $stock['product_id'];
            $expiry_date = $stock['expiry_date'];
            $category_query = $conn->query("SELECT c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = '$product_id'");
            if (!$category_query) {
                die("Error fetching category: " . $conn->error);
            }
            $category = $category_query->fetch_assoc()['category_name'];

            // Update stocks quantity
            if (!$conn->query("UPDATE stocks SET quantity = quantity - $quantity WHERE stock_id = '$stock_id'")) {
                die("Error updating stock quantity: " . $conn->error);
            }

            // Insert stockout details
            $stmt_stockout = $conn->prepare("INSERT INTO stockout (stock_out_id, product_id, stock_id, category, quantity, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt_stockout) {
                die("Error preparing stockout statement: " . $conn->error);
            }
            $stock_out_id = uniqid('stockout_');
            $stmt_stockout->bind_param("ssisis", $stock_out_id, $product_id, $stock_id, $category, $quantity, $expiry_date);
            if (!$stmt_stockout->execute()) {
                die("Error executing stockout statement: " . $stmt_stockout->error);
            }
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
    <link rel="stylesheet" href="../css/index_styles.css">
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
                                        <td><?php echo $row['category_name']; ?></td>
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
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                            <a class="page-link" href="<?php if ($page > 1) echo "?page=stock_out&pageno=" . ($page - 1);
                                                        else echo '#'; ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                            <li class="page-item <?php if ($page == $i) echo 'active'; ?>"><a class="page-link" href="?page=stock_out&pageno=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                        <?php } ?>
                        <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                            <a class="page-link" href="<?php if ($page < $total_pages) echo "?page=stock_out&pageno=" . ($page + 1);
                                                        else echo '#'; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>

</html>
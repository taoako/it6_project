<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_stock'])) {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $original_price = $_POST['original_price'];
        $expiry_date = $_POST['expiry_date'];
        $supplier_id = $_POST['supplier_id'];
        $total_cost = $quantity * $original_price;
        $purchase_date = date('Y-m-d');
        $selling_price = round($original_price * 1.30); // Adding 10% VAT and rounding to the nearest whole number

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert new stock into stockintransaction
            $query = "INSERT INTO stockintransaction (supplier_id, quantity, total_cost, purchase_date, product_id) 
                      VALUES ('$supplier_id', '$quantity', '$total_cost', '$purchase_date', '$product_id')";
            if ($conn->query($query) === TRUE) {
                // Get the last inserted stock_in_id
                $stock_in_id = $conn->insert_id;

                // Check if the stock already exists in the stocks table
                $query = "SELECT * FROM stocks WHERE product_id = '$product_id'";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    // Update existing stock
                    $query = "UPDATE stocks SET quantity = quantity + '$quantity', original_price = '$original_price', expiry_date = '$expiry_date', stock_in_id = '$stock_in_id', selling_price = '$selling_price' WHERE product_id = '$product_id'";
                } else {
                    // Insert new stock details into stocks table
                    $query = "INSERT INTO stocks (stock_in_id, product_id, quantity, original_price, expiry_date, selling_price) 
                              VALUES ('$stock_in_id', '$product_id', '$quantity', '$original_price', '$expiry_date', '$selling_price')";
                }

                if ($conn->query($query) === TRUE) {
                    // Commit transaction
                    $conn->commit();
                    echo "<script>alert('Stock and stock details added successfully!'); window.location.href='index.php?page=inventory';</script>";
                } else {
                    throw new Exception("Error: " . $conn->error);
                }
            } else {
                throw new Exception("Error: " . $conn->error);
            }
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            echo "<script>alert('" . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Add Stock</h5>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select class="form-select" name="supplier_id" id="supplier_id" required>
                            <option value="">Select Supplier</option>
                            <?php
                            $suppliers = $conn->query("SELECT * FROM suppliers");
                            while ($row = $suppliers->fetch_assoc()) {
                                echo "<option value='{$row['supplier_id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product</label>
                        <select class="form-select" name="product_id" id="product_id" required>
                            <option value="">Choose Product</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="original_price" class="form-label">Original Price</label>
                        <input type="number" step="0.01" class="form-control" name="original_price" id="original_price" required>
                    </div>
                    <div class="mb-3">
                        <label for="selling_price" class="form-label">Selling Price</label>
                        <input type="number" step="0.01" class="form-control" name="selling_price" id="selling_price" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date" required>
                    </div>
                    <button type="submit" name="add_stock" class="btn btn-success">Add Stock</button>
                    <a href="index.php?page=inventory" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#supplier_id').change(function() {
            var supplier_id = $(this).val();
            if (supplier_id) {
                $.ajax({
                    url: 'fetch_products.php',
                    type: 'POST',
                    data: {
                        supplier_id: supplier_id
                    },
                    success: function(response) {
                        $('#product_id').html(response);
                    }
                });
            } else {
                $('#product_id').html('<option value="">Choose Product</option>');
            }
        });

        $('#original_price').on('input', function() {
            var originalPrice = parseFloat($(this).val());
            if (!isNaN(originalPrice)) {
                var sellingPrice = originalPrice * 1.30; // Adding 10% VAT
                sellingPrice = Math.round(sellingPrice); // Rounding to the nearest whole number
                $('#selling_price').val(sellingPrice.toFixed(2));
            } else {
                $('#selling_price').val('');
            }
        });
    });
</script>

</html>
<?php
$conn->close();
?>
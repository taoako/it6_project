<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_stock'])) {
    $supplier_id = $_POST['supplier_id'];
    $stocks = $_POST['stocks'];

    // Start transaction
    $conn->begin_transaction();

    try {
        foreach ($stocks as $stock) {
            $product_id     = $stock['product_id'];
            $quantity       = (int) $stock['quantity'];
            $original_price = (float) $stock['original_price'];
            $expiry_date    = $stock['expiry_date'];

            // Check that all required fields are provided
            if (empty($supplier_id) || empty($product_id) || empty($quantity) || empty($original_price) || empty($expiry_date)) {
                throw new Exception('All fields are required!');
            }

            // Verify the supplier exists
            $stmt = $conn->prepare("SELECT supplier_id FROM suppliers WHERE supplier_id = ?");
            $stmt->bind_param("s", $supplier_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                throw new Exception('Invalid supplier selected!');
            }
            $stmt->close();

            $total_cost    = $quantity * $original_price;
            $purchase_date = date('Y-m-d');
            $selling_price = round($original_price * 1.30, 2);

            // Insert into stockintransaction
            $stmt = $conn->prepare("INSERT INTO stockintransaction (supplier_id, product_id, quantity, total_cost, purchase_date, orig_price) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ssidsd", $supplier_id, $product_id, $quantity, $total_cost, $purchase_date, $original_price);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting stockintransaction: " . $stmt->error);
            }
            $stock_in_id = $conn->insert_id;
            $stmt->close();

            // Check if a stock record already exists for the given product_id
            $stmt = $conn->prepare("SELECT * FROM stocks WHERE product_id = ?");
            $stmt->bind_param("s", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0) {
                // Update existing stock record
                $stmt = $conn->prepare("UPDATE stocks SET quantity = quantity + ?, expiry_date = ?, stock_in_id = ?, selling_price = ? WHERE product_id = ?");
                $stmt->bind_param("isids", $quantity, $expiry_date, $stock_in_id, $selling_price, $product_id);
            } else {
                // Insert a new stock record
                $stmt = $conn->prepare("INSERT INTO stocks (stock_in_id, product_id, quantity, expiry_date, selling_price) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isisd", $stock_in_id, $product_id, $quantity, $expiry_date, $selling_price);
            }

            if (!$stmt->execute()) {
                throw new Exception("Error updating/inserting stock: " . $stmt->error);
            }
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        echo "<script>alert('Stocks added successfully!'); window.location.href='index.php?page=inventory';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('" . $e->getMessage() . "'); window.location.href='index.php?page=inventory';</script>";
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
                    <div id="stock-entries">
                        <div class="stock-entry">
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Product</label>
                                <select class="form-select" name="stocks[0][product_id]" required>
                                    <option value="">Choose Product</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" name="stocks[0][quantity]" required>
                            </div>
                            <div class="mb-3">
                                <label for="original_price" class="form-label">Original Price</label>
                                <input type="number" step="0.01" class="form-control" name="stocks[0][original_price]" required>
                            </div>
                            <div class="mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" name="stocks[0][expiry_date]" required>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="add-stock-entry">Add Another Stock</button>
                    <button type="submit" name="add_stock" class="btn btn-success">Add Stocks</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let stockEntryIndex = 1;

            $('#add-stock-entry').click(function() {
                const newStockEntry = `
                    <div class="stock-entry">
                        <hr>
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product</label>
                            <select class="form-select" name="stocks[${stockEntryIndex}][product_id]" required>
                                <option value="">Choose Product</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="stocks[${stockEntryIndex}][quantity]" required>
                        </div>
                        <div class="mb-3">
                            <label for="original_price" class="form-label">Original Price</label>
                            <input type="number" step="0.01" class="form-control" name="stocks[${stockEntryIndex}][original_price]" required>
                        </div>
                        <div class="mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" name="stocks[${stockEntryIndex}][expiry_date]" required>
                        </div>
                    </div>
                `;
                $('#stock-entries').append(newStockEntry);
                stockEntryIndex++;
            });

            $('#supplier_id').change(function() {
                const supplier_id = $(this).val();
                if (supplier_id) {
                    $.ajax({
                        url: 'fetch_products.php',
                        type: 'POST',
                        data: {
                            supplier_id: supplier_id
                        },
                        success: function(response) {
                            $('select[name^="stocks"][name$="[product_id]"]').html(response);
                        }
                    });
                } else {
                    $('select[name^="stocks"][name$="[product_id]"]').html('<option value="">Choose Product</option>');
                }
            });

            $(document).on('input', 'input[name^="stocks"][name$="[original_price]"]', function() {
                const originalPrice = parseFloat($(this).val());
                const sellingPriceInput = $(this).closest('.stock-entry').find('input[name^="stocks"][name$="[selling_price]"]');
                if (!isNaN(originalPrice)) {
                    const sellingPrice = originalPrice * 1.30;
                    sellingPriceInput.val(sellingPrice.toFixed(2));
                } else {
                    sellingPriceInput.val('');
                }
            });
        });
    </script>
</body>

</html>
<?php
$conn->close();
?>
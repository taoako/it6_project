<?php
include 'db_connection.php';
include 'fetch_stock_in.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_stock_in'])) {
        $stock_in_id = $_POST['stock_in_id'];

        // Delete from stockintransaction
        $query = "DELETE FROM stockintransaction WHERE stock_in_id = '$stock_in_id'";
        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Stock-in details deleted successfully!'); window.location.href='index.php?page=inventory';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    } elseif (isset($_POST['delete_stock'])) {
        $stock_id = $_POST['stock_id'];

        // Delete from stocks
        $query = "DELETE FROM stocks WHERE stock_id = '$stock_id'";
        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Stock details deleted successfully!'); window.location.href='index.php?page=inventory';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daddy's Nook Employee Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .dashboard-header {
            background-color: #00ff99;
            color: white;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            padding: 15px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dashboard-header img {
            height: 50px;
            margin-right: 15px;
        }

        .container {
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #28a745;
            padding: 20px;
            min-height: 100vh;
        }

        .sidebar button {
            width: 100%;
            background-color: white;
            border: none;
            padding: 15px;
            margin: 10px 0;
            font-size: 16px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }

        .sidebar button:hover {
            background-color: #ccc;
        }

        .logout {
            background-color: #ff4d4d !important;
            color: white;
        }

        .logout:hover {
            background-color: #ff3333 !important;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        .add-button {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="dashboard-header">
        <img src="pics/daddys.jpg" alt="Daddy's Nook Logo">
        Daddy's Nook Employee Dashboard
    </div>
    <div class="container">
        <div class="sidebar">
            <button onclick="location.href='index.php?page=inventory'"><i class="fas fa-boxes"></i> Inventory</button>
            <button onclick="location.href='add_stock.php'"><i class="fas fa-plus-circle"></i> Add Stock</button>
            <button onclick="location.href='index.php?page=stock_out'"><i class="fas fa-minus-circle"></i> Stock Out</button>
            <button onclick="location.href='index.php?page=suppliers'"><i class="fas fa-truck"></i> Suppliers</button>
            <button onclick="location.href='index.php?page=products'"><i class="fas fa-box"></i> Products</button>
            <button class="logout" onclick="location.href='login/login.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
            <button onclick="location.href='login/manage_employees.php'"><i class="bi bi-person-gear"></i> Manage Employees</button>
        </div>
        <div class="content">
            <?php
            if (isset($_GET['page']) && $_GET['page'] == 'inventory') {
            ?>
                <div class="card">
                    <h3>Stock In</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Stock-in ID</th>
                                <th>Supplier ID</th>
                                <th>Product ID</th>
                                <th>Quantity</th>
                                <th>Total Cost</th>
                                <th>Purchase Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stockInTransaction = fetchStockInTransaction($conn);
                            if (!empty($stockInTransaction)) {
                                foreach ($stockInTransaction as $detail) {
                                    echo "<tr>
                                            <td>{$detail['Stock_in_id']}</td>
                                            <td>{$detail['supplier_id']}</td>
                                            <td>{$detail['product_id']}</td>
                                            <td>{$detail['quantity']}</td>
                                            <td>{$detail['total_cost']}</td>
                                            <td>{$detail['purchase_date']}</td>
                                            <td>
                                                <form method='POST' action='edit_stock_in.php' style='display:inline-block;'>
                                                    <input type='hidden' name='stock_in_id' value='{$detail['Stock_in_id']}'>
                                                    <input type='hidden' name='supplier_id' value='{$detail['supplier_id']}'>
                                                    <input type='hidden' name='product_id' value='{$detail['product_id']}'>
                                                    <input type='hidden' name='quantity' value='{$detail['quantity']}'>
                                                    <input type='hidden' name='total_cost' value='{$detail['total_cost']}'>
                                                    <input type='hidden' name='purchase_date' value='{$detail['purchase_date']}'>
                                                    <button type='submit' name='edit_stock_in' class='btn btn-warning btn-sm'>Edit</button>
                                                </form>
                                                <form method='POST' action='' style='display:inline-block;'>
                                                    <input type='hidden' name='stock_in_id' value='{$detail['Stock_in_id']}'>
                                                    <button type='submit' name='delete_stock_in' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this stock-in?\")'>Delete</button>
                                                </form>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>No Stock-In Records</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <h3>Stocks</h3>
                    <button class="add-button" onclick="location.href='add_stock.php'">+ Add Stocks</button>
                    <!-- Collapsible Stocks Table -->
                    <button class="btn btn-primary mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#stocksTable" aria-expanded="false" aria-controls="stocksTable">
                        Show/Hide Stocks
                    </button>
                    <div class="collapse" id="stocksTable">
                        <table class="mt-3">
                            <thead>
                                <tr>
                                    <th>Stock ID</th>
                                    <th>Stock-in ID</th>
                                    <th>Product ID</th>
                                    <th>Expiry Date</th>
                                    <th>Quantity</th>
                                    <th>Original Price</th>
                                    <th>Selling Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stocks = fetchStocks($conn);
                                if (!empty($stocks)) {
                                    foreach ($stocks as $stock) {
                                        echo "<tr>
                                                <td>{$stock['stock_id']}</td>
                                                <td>{$stock['stock_in_id']}</td>
                                                <td>{$stock['product_id']}</td>
                                                <td>{$stock['expiry_date']}</td>
                                                <td>{$stock['quantity']}</td>
                                                <td>{$stock['original_price']}</td>
                                                <td>{$stock['selling_price']}</td>
                                                <td>
                                                    <form method='POST' action='edit_stock.php' style='display:inline-block;'>
                                                        <input type='hidden' name='stock_id' value='{$stock['stock_id']}'>
                                                        <button type='submit' name='edit_stock' class='btn btn-warning btn-sm'>Edit</button>
                                                    </form>
                                                    <form method='POST' action='' style='display:inline-block;'>
                                                        <input type='hidden' name='stock_id' value='{$stock['stock_id']}'>
                                                        <button type='submit' name='delete_stock' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this stock?\")'>Delete</button>
                                                    </form>
                                                </td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center'>No stock found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php
            } else if (isset($_GET['page']) && $_GET['page'] == 'suppliers') {
            ?>
                <div class="card">
                    <h3>Suppliers</h3>
                    <button class="add-button" onclick="location.href='add_supplier.php'"><i class="fas fa-plus-circle"></i> Add Supplier</button>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Supplier ID</th>
                                <th>Supplier Name</th>
                                <th>Contact Number</th>
                                <th>Address</th>
                                <th>Contact Person</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $suppliers = $conn->query("SELECT * FROM suppliers");
                            if ($suppliers->num_rows > 0) {
                                while ($supplier = $suppliers->fetch_assoc()) {
                                    echo "<tr>
                                                <td>{$supplier['supplier_id']}</td>
                                                <td>{$supplier['name']}</td>
                                                <td>{$supplier['contact_number']}</td>
                                                <td>{$supplier['address']}</td>
                                                <td>{$supplier['contact_person']}</td>
                                                <td>
                                                    <a href='edit_supplier.php?supplier_id={$supplier['supplier_id']}' class='btn btn-warning btn-sm'>Edit</a>
                                                    <a href='delete_supplier.php?supplier_id={$supplier['supplier_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this supplier?\")'>Delete</a>
                                                </td>
                                              </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No Suppliers Found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php
            } elseif (isset($_GET['page']) && $_GET['page'] == 'stock_out') {
                include 'stock_out.php';
            } else if (isset($_GET['page']) && $_GET['page'] == 'products') {
            ?>
                <div class="card">
                    <h3>Products</h3>
                    <button class="add-button" onclick="location.href='add_product.php'"><i class="fas fa-plus-circle"></i> Add Product</button>
                    <table>
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $products = fetchProducts($conn);
                            if (!empty($products)) {
                                foreach ($products as $product) {
                                    echo "<tr>
                                                <td>{$product['product_id']}</td>
                                                <td>{$product['name']}</td>
                                                <td>{$product['category_name']}</td>
                                              </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>No Products Found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php
            } else {
            ?>
                <div class="card">
                    <div class="card-body">Please select an option from the sidebar.</div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</body>

</html>
<?php
if ($conn) {
    $conn->close();
}
?>
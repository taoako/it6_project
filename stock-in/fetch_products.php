<?php
include '../dbcon/db_connection.php';

if (isset($_POST['supplier_id'])) {
    $supplier_id = $_POST['supplier_id'];
    $query = "
        SELECT p.product_id, p.name 
        FROM products p
        JOIN stock_supplier ss ON p.product_id = ss.product_id
        WHERE ss.supplier_id = '$supplier_id'
    ";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['product_id']}'>{$row['name']}</option>";
        }
    } else {
        echo "<option value=''>No products available</option>";
    }
}

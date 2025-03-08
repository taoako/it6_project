<?php
include 'db_connection.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Delete product
    $query = "DELETE FROM products WHERE product_id = '$product_id'";
    if ($conn->query($query) === TRUE) {
        echo "<script>alert('Product deleted successfully!'); window.location.href='index.php?page=products';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.location.href='index.php?page=products';</script>";
    }
}

$conn->close();

<?php
include 'db_connection.php';

if (isset($_GET['supplier_id'])) {
    $supplier_id = $_GET['supplier_id'];

    // Delete supplier-product mappings
    $conn->query("DELETE FROM stockintransaction WHERE supplier_id = '$supplier_id'");

    // Delete supplier
    $conn->query("DELETE FROM suppliers WHERE supplier_id = '$supplier_id'");

    echo "<script>alert('Supplier deleted successfully!'); window.location.href='index.php?page=suppliers';</script>";
}
?>
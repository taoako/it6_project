<?php
function fetchPaginatedStockInTransaction($conn, $start_from, $records_per_page)
{
    $query = "
        SELECT s.*, p.name AS product_name, sup.name AS supplier_name
        FROM stockintransaction s
        JOIN products p ON s.product_id = p.product_id
        JOIN suppliers sup ON s.supplier_id = sup.supplier_id
        LIMIT $start_from, $records_per_page
    ";
    $result = $conn->query($query);
    $stockInTransaction = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stockInTransaction[] = $row;
        }
    }

    return $stockInTransaction;
}

function fetchPaginatedStocks($conn, $start_from, $records_per_page)
{
    $query = "
        SELECT s.*, p.name AS product_name, sup.name AS supplier_name
        FROM stocks s
        JOIN products p ON s.product_id = p.product_id
        JOIN stockintransaction si ON s.stock_in_id = si.stock_in_id
        JOIN suppliers sup ON si.supplier_id = sup.supplier_id
        LIMIT $start_from, $records_per_page
    ";
    $result = $conn->query($query);
    $stocks = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stocks[] = $row;
        }
    }

    return $stocks;
}

function fetchPaginatedSuppliers($conn, $start_from, $records_per_page)
{
    $query = "SELECT * FROM suppliers LIMIT $start_from, $records_per_page";
    $result = $conn->query($query);
    $suppliers = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
    }

    return $suppliers;
}

function fetchPaginatedProducts($conn, $start_from, $records_per_page)
{
    $query = "
        SELECT p.product_id, p.name, c.name as category_name, p.image
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        LIMIT $start_from, $records_per_page
    ";
    $result = $conn->query($query);
    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

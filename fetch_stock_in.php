<?php
function fetchStockInTransaction($conn)
{
    $query = "SELECT * FROM stockintransaction";
    $result = $conn->query($query);
    $stockInTransaction = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stockInTransaction[] = $row;
        }
    }

    return $stockInTransaction;
}

function fetchStocks($conn)
{
    $query = "SELECT * FROM stocks";
    $result = $conn->query($query);
    $stocks = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stocks[] = $row;
        }
    }

    return $stocks;
}

function fetchSuppliers($conn)
{
    $query = "SELECT * FROM suppliers";
    $result = $conn->query($query);
    $suppliers = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
    }

    return $suppliers;
}

function fetchProducts($conn)
{
    $query = "SELECT p.product_id, p.name, c.name AS category_name
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id";
    $result = $conn->query($query);
    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

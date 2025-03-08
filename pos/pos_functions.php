<?php
// pos_functions.php

/**
 * Fetch categories from the database.
 * Return an array of [ 'category_id' => ..., 'name' => ... ].
 */
function fetchCategories($conn)
{
    $sql = "SELECT category_id, name FROM categories";
    $result = $conn->query($sql);
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

/**
 * Fetch products (and optional stock info) filtered by category, search, and sort order.
 * Return an array of product records with fields:
 *   product_id, product_name, category_name, selling_price, quantity, image
 */
function fetchProducts($conn, $category = 'All', $search = '', $sort = 'name')
{
    // Build base query
    $query = "
        SELECT p.product_id, p.name AS product_name, p.image, c.name AS category_name,
               s.selling_price, s.quantity
          FROM products p
     LEFT JOIN categories c ON p.category_id = c.category_id
     LEFT JOIN stocks s ON p.product_id = s.product_id
         WHERE 1=1
    ";

    // Filter by category
    $bindTypes = '';
    $params = [];

    if ($category !== 'All') {
        $query .= " AND c.name = ? ";
        $bindTypes .= 's';
        $params[] = $category;
    }

    // Search by product name
    if (!empty($search)) {
        $query .= " AND p.name LIKE ? ";
        $bindTypes .= 's';
        $params[] = '%' . $search . '%';
    }

    // Sorting
    if ($sort === 'name') {
        $query .= " ORDER BY p.name ASC";
    } else {
        // Additional sorts can go here
        $query .= " ORDER BY p.product_id ASC";
    }

    $stmt = $conn->prepare($query);
    if (!empty($bindTypes)) {
        $stmt->bind_param($bindTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();

    return $products;
}

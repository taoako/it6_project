<?php
// cart_functions.php

/**
 * Ensure the cart array is set in the session.
 */
function initializeCart()
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add an item to the cart (or increment quantity if it already exists).
 */
function addToCart($product_id, $product_name, $selling_price, $quantity)
{
    // Convert to numeric just in case
    $selling_price = (float)$selling_price;
    $quantity      = (int)$quantity;

    if (isset($_SESSION['cart'][$product_id])) {
        // Already in cart, increment quantity
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        // New item
        $_SESSION['cart'][$product_id] = [
            'product_name'   => $product_name,
            'selling_price'  => $selling_price,
            'quantity'       => $quantity
        ];
    }
}

/**
 * Remove an item from the cart.
 */
function removeFromCart($product_id)
{
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

/**
 * Calculate total cost of items in the cart.
 */
function calculateTotal()
{
    $grandTotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $grandTotal += $item['selling_price'] * $item['quantity'];
    }
    return $grandTotal;
}

/**
 * Checkout: reduce stock in `stocks` table, clear cart.
 * Optionally record sale in a separate table or handle receipts.
 */
function checkout($conn, $receipt_description)
{
    $conn->begin_transaction();
    try {
        foreach ($_SESSION['cart'] as $pid => $item) {
            $qty = $item['quantity'];

            // Example logic: reduce from `stocks` where product_id = ?
            $updateSql = "UPDATE stocks
                             SET quantity = quantity - ?
                           WHERE product_id = ?
                             AND quantity >= ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("isi", $qty, $pid, $qty);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();

        // Clear cart
        $_SESSION['cart'] = [];

        // Save success message
        $_SESSION['checkout_success'] = "Purchase successful!<br>Receipt Description: " . htmlspecialchars($receipt_description);
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['checkout_error'] = "Error during checkout: " . $e->getMessage();
    }
}

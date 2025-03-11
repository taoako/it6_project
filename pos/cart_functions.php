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




function checkout($conn, $receipt_description, $amount_paid)
{
    $conn->begin_transaction();
    try {
        // Generate unique order ID
        $order_id = uniqid('order_');
        $employee_id = 1; // Placeholder; adapt to your real employee login
        $transaction_date = date('Y-m-d');
        $status = 'Completed';

        // Insert into `order` table
        $orderSql = "INSERT INTO `order` (order_id, employee_id, transaction_date, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($orderSql);
        if ($stmt === false) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("siss", $order_id, $employee_id, $transaction_date, $status);
        if ($stmt->execute() === false) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $stmt->close();

        // Update stocks and calculate total amount
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $pid => $item) {
            $qty = $item['quantity'];
            $total_amount += $item['selling_price'] * $qty;

            // Reduce stock quantity
            $updateSql = "UPDATE stocks SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?";
            $stmt = $conn->prepare($updateSql);
            if ($stmt === false) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }
            $stmt->bind_param("isi", $qty, $pid, $qty);
            if ($stmt->execute() === false) {
                throw new Exception("Execute statement failed: " . $stmt->error);
            }
            $stmt->close();
        }

        // Calculate change
        if ($amount_paid < $total_amount) {
            throw new Exception("Insufficient amount paid.");
        }
        $change = $amount_paid - $total_amount;

        // Insert into `payment` table
        $payment_id = uniqid('payment_');
        $paymentSql = "INSERT INTO payment (payment_id, order_id, total_amount, amount, payment_change) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($paymentSql);
        if ($stmt === false) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("ssddd", $payment_id, $order_id, $total_amount, $amount_paid, $change);
        if ($stmt->execute() === false) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $stmt->close();

        /**
         *  -- HERE: Insert into `sales` table --
         *  We'll record the final sale using order_id, payment_id, employee_id, total_amount, and a timestamp
         */
        $salesSql = "INSERT INTO sales (order_id, payment_id, employee_id, sales_date, total_amount)
                     VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($salesSql);
        if ($stmt === false) {
            throw new Exception("Prepare statement for sales failed: " . $conn->error);
        }
        // 'ssid' -> (string, string, int, double) but we have 4 placeholders
        // Actually we have 4 placeholders: (string, string, int, double) => "ssid"
        // The 5th is the NOW() function in the SQL, so no param needed for that
        $stmt->bind_param("ssid", $order_id, $payment_id, $employee_id, $total_amount);
        if ($stmt->execute() === false) {
            throw new Exception("Execute statement for sales failed: " . $stmt->error);
        }
        $stmt->close();

        // Everything succeeded, commit
        $conn->commit();

        // Clear cart
        $_SESSION['cart'] = [];

        // Save success message
        $_SESSION['checkout_success'] = "Purchase successful!<br>
            Receipt Description: " . htmlspecialchars($receipt_description) . "<br>
            Change: PHP " . number_format($change, 2);
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['checkout_error'] = "Error during checkout: " . $e->getMessage();
    }
}

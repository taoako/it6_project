<?php
session_start();

// Include dependencies
include '../dbcon/db_connection.php';
include 'pos_functions.php';
include 'cart_functions.php';

// 1. Initialize cart
initializeCart();

// Check if the user is logged in and has appropriate role (admin or employee)
if (!isset($_SESSION['loggedin']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employee')) {
    header("Location: ../login/login.php"); // Redirect to login if not logged in or not an admin/employee
    exit;
}

// 2. Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add to cart
    if (isset($_POST['add_to_cart'])) {
        $product_id    = $_POST['product_id'];
        $product_name  = $_POST['product_name'];
        $selling_price = $_POST['selling_price'];
        $quantity      = $_POST['quantity'];
        addToCart($product_id, $product_name, $selling_price, $quantity);
        header("Location: pos.php");
        exit;
    }
    // Remove from cart
    if (isset($_POST['remove_item'])) {
        $product_id = $_POST['product_id'];
        removeFromCart($product_id);
        header("Location: pos.php");
        exit;
    }
    // Checkout
    if (isset($_POST['checkout'])) {
        $receipt_description = $_POST['receipt_description'] ?? '';
        checkout($conn, $receipt_description);
        header("Location: pos.php");
        exit;
    }
}

// 3. Handle GET filters
$category = $_GET['category'] ?? 'All';
$search   = $_GET['search'] ?? '';
$sort     = $_GET['sort']   ?? 'name';

// 4. Fetch data
$categories = fetchCategories($conn);
$products   = fetchProducts($conn, $category, $search, $sort);

// Calculate cart total
$grandTotal = calculateTotal();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>POS - Purchase</title>
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="pos_style.css">
    <style>
        /* Add navigation bar styling */
        .nav-bar {
            background-color: #1b8a3f;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-bar h1 {
            margin: 0;
        }
        .nav-bar .user-info {
            display: flex;
            align-items: center;
        }
        .nav-bar .user-info span {
            margin-right: 15px;
        }
        .logout-btn {
            background-color: #8b93a7;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background-color: #6c757d;
        }
        /* If an admin is logged in, provide a link to the admin panel */
        .admin-link {
            background-color: #dc3545;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 10px;
        }
        .admin-link:hover {
            background-color: #bd2130;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <div class="nav-bar">
        <h1>DADDY'S NOOK - POS SYSTEM</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo $_SESSION['role']; ?>)</span>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="../stock-in/index.php" class="admin-link">Admin Dashboard</a>
            <?php endif; ?>
            <a href="../login/login.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Top Bar: Category Filter, Sort, Search -->
    <div class="top-bar">
        <h2 style="margin:0;">PURCHASE</h2>
        <form method="GET" action="">
            <label>Category:</label>
            <select name="category">
                <option value="All">All</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['name']; ?>"
                        <?php echo ($category === $cat['name']) ? 'selected' : ''; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Sort by:</label>
            <select name="sort">
                <option value="name" <?php if ($sort === 'name') echo 'selected'; ?>>Name</option>
                <!-- You can add more sort options here -->
            </select>

            <label>Search:</label>
            <input type="text" name="search" placeholder="Search product..." value="<?php echo htmlspecialchars($search); ?>">

            <button type="submit">Filter</button>
        </form>
    </div>

    <!-- Show success/error messages after checkout -->
    <?php if (!empty($_SESSION['checkout_success'])): ?>
        <div class="alert">
            <?php
            echo $_SESSION['checkout_success'];
            unset($_SESSION['checkout_success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['checkout_error'])): ?>
        <div class="alert error">
            <?php
            echo $_SESSION['checkout_error'];
            unset($_SESSION['checkout_error']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Main Container: Product Grid + Cart -->
    <div class="container">
        <!-- Product Grid (Left) -->
        <div class="product-grid">
            <?php if (count($products) === 0): ?>
                <p>No products found.</p>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                    <?php
                    // If there's no stock info or quantity is 0, skip
                    if (is_null($prod['selling_price']) || $prod['quantity'] <= 0) {
                        continue;
                    }
                    ?>
                    <div class="product-card">
                        <h4><?php echo htmlspecialchars($prod['product_name']); ?></h4>
                        <div class="price">
                            [<?php echo htmlspecialchars($prod['category_name'] ?? 'No Category'); ?>]
                            PHP <?php echo number_format($prod['selling_price'], 2); ?>
                        </div>
                        <div>Stock: <?php echo (int)$prod['quantity']; ?></div>
                        <div>
                            <img src="../<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['product_name']); ?>" width="100">
                            <!-- Debugging: Display the image path -->
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?php echo $prod['product_id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($prod['product_name']); ?>">
                            <input type="hidden" name="selling_price" value="<?php echo $prod['selling_price']; ?>">
                            <label>Qty:</label>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $prod['quantity']; ?>">
                            <button type="submit" name="add_to_cart">Add</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Cart (Right) -->
        <div class="cart">
            <h3>Cart</h3>
            <ul class="cart-items">
                <?php foreach ($_SESSION['cart'] as $pid => $item): ?>
                    <?php $itemTotal = $item['selling_price'] * $item['quantity']; ?>
                    <li>
                        <span>
                            <?php echo htmlspecialchars($item['product_name']); ?>
                            (<?php echo $item['quantity']; ?>x @ PHP <?php echo number_format($item['selling_price'], 2); ?>)
                        </span>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                            <button type="submit" name="remove_item" style="background:none;border:none;color:red;cursor:pointer;">
                                X
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <hr>
            <div>
                <label for="receipt_description">Receipt Description:</label>
                <textarea class="receipt-description" name="receipt_description" form="checkoutForm"></textarea>
            </div>
            <p><strong>Total: PHP <?php echo number_format($grandTotal, 2); ?></strong></p>

            <!-- Checkout Form -->
            <form method="POST" id="checkoutForm">
                <button type="submit" name="checkout" class="checkout-btn">Complete Purchase</button>
            </form>
        </div>
    </div>

</body>

</html>
<?php
$conn->close();
?>
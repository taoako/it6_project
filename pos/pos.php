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
        $amount_paid         = $_POST['amount_paid'] ?? 0;
        checkout($conn, $receipt_description, $amount_paid);
        header("Location: pos.php");
        exit;
    }
}

// 3. Handle GET filters
$category = $_GET['category'] ?? 'All';
$search   = $_GET['search']   ?? '';
$sort     = $_GET['sort']     ?? 'name';

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

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Green-themed navbar */
        .navbar-custom {
            background-color: #00FF99 !important;
            /* bright green */
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link,
        .navbar-custom .navbar-text {
            color: #000 !important;
            /* Dark text for contrast */
        }

        /* Push content below fixed navbar */
        body {
            margin-top: 60px;
            background-color: #f8f9fa;
            /* light gray background */
        }

        /* Fixed cart on the right side */
        .fixed-cart {
            position: fixed;
            top: 60px;
            /* match navbar height */
            right: 0;
            width: 300px;
            /* cart width */
            bottom: 0;
            background-color: #fff;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            overflow-y: auto;
        }

        /* Reserve space for the cart so products don't overlap it */
        .product-container {
            margin-right: 320px;
            /* cart width + some spacing */
            padding: 1rem;
        }

        /* Product card: fixed width to prevent layout shifting */
        .product-col {
            width: 200px;
            /* bigger card width */
        }

        .product-img {
            width: 100%;
            height: 120px;
            /* bigger image height */
            object-fit: contain;
        }

        /* Toast positioning for checkout success */
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            /* ensure it's on top */
        }
    </style>
</head>

<body>

    <!-- NAVBAR (Green Theme, fixed-top) -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">PURCHASE</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon" style="filter: invert(100%);"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <form class="d-flex align-items-center ms-auto" method="GET" action="">
                    <!-- Category Filter -->
                    <div class="me-2">
                        <a href="../login/login.php" class="logout-btn">Logout</a>
                        <label class="me-1">Category:</label>
                        <select name="category" class="form-select form-select-sm" style="width:auto;">
                            <option value="All">All</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['name']; ?>"
                                    <?php echo ($category === $cat['name']) ? 'selected' : ''; ?>>
                                    <?php echo $cat['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="me-2">
                        <label class="me-1">Sort:</label>
                        <select name="sort" class="form-select form-select-sm" style="width:auto;">
                            <option value="name" <?php if ($sort === 'name') echo 'selected'; ?>>Name</option>
                            <!-- Add more sort options here -->
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="me-2">
                        <label class="me-1">Search:</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            style="width:120px;">
                    </div>

                    <!-- Filter button -->
                    <button type="submit" class="btn btn-dark btn-sm me-3">Filter</button>

                    <!-- Sales Report Button -->
                    <button id="showSalesReportBtn" type="button" class="btn btn-secondary btn-sm">
                        Sales Report
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- TOAST CONTAINER (for checkout success) -->
    <div class="toast-container">
        <?php if (!empty($_SESSION['checkout_success'])): ?>
            <div id="checkoutToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php
                        echo $_SESSION['checkout_success'];
                        unset($_SESSION['checkout_success']);
                        ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- PRODUCT CONTAINER (Left) -->
    <div class="product-container">
        <?php if (count($products) === 0): ?>
            <p>No products found.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $prod): ?>
                    <?php
                    // Skip if no stock info or quantity is 0
                    if (is_null($prod['selling_price']) || $prod['quantity'] <= 0) {
                        continue;
                    }
                    ?>
                    <div class="col-auto product-col">
                        <div class="card mb-3">
                            <!-- Product Image -->
                            <?php if (!empty($prod['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($prod['image']); ?>"
                                    class="card-img-top product-img"
                                    alt="<?php echo htmlspecialchars($prod['product_name']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/150"
                                    class="card-img-top product-img"
                                    alt="No image">
                            <?php endif; ?>

                            <!-- Card Body -->
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1" style="font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($prod['product_name']); ?>
                                </h6>
                                <p class="mb-1" style="font-size: 0.75rem;">
                                    Category: <?php echo htmlspecialchars($prod['category_name'] ?? 'No Category'); ?>
                                </p>
                                <p class="mb-1" style="font-size: 0.75rem;">
                                    Price: PHP <?php echo number_format($prod['selling_price'], 2); ?>
                                </p>
                                <p class="mb-2" style="font-size: 0.75rem;">
                                    Stock: <?php echo (int)$prod['quantity']; ?>
                                </p>

                                <!-- Add to Cart Form -->
                                <form method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="product_id" value="<?php echo $prod['product_id']; ?>">
                                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($prod['product_name']); ?>">
                                    <input type="hidden" name="selling_price" value="<?php echo $prod['selling_price']; ?>">

                                    <label class="me-1 mb-0" style="font-size: 0.75rem;">Qty:</label>
                                    <input type="number" name="quantity"
                                        class="form-control form-control-sm me-2"
                                        value="1" min="1" max="<?php echo $prod['quantity']; ?>"
                                        style="width: 50px;">
                                    <button type="submit" name="add_to_cart" class="btn btn-success btn-sm">
                                        Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- FIXED CART ON THE RIGHT -->
    <div class="fixed-cart">
        <h5>Cart</h5>
        <hr>
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <ul class="list-group mb-3">
                <?php foreach ($_SESSION['cart'] as $pid => $item): ?>
                    <?php $itemTotal = $item['selling_price'] * $item['quantity']; ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                            <small><?php echo $item['quantity']; ?>x @
                                PHP <?php echo number_format($item['selling_price'], 2); ?></small>
                        </div>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                            <button type="submit" name="remove_item" class="btn btn-sm btn-danger">
                                X
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p><strong>Total: PHP <?php echo number_format($grandTotal, 2); ?></strong></p>
        <hr>
        <!-- Checkout Form -->
        <form method="POST">
            <div class="mb-2">
                <label for="receipt_description" class="form-label">Receipt Description:</label>
                <textarea name="receipt_description" rows="2" class="form-control"></textarea>
            </div>
            <div class="mb-2">
                <label for="amount_paid" class="form-label">Amount Paid:</label>
                <input type="number" name="amount_paid" step="0.01" min="0" required
                    class="form-control">
            </div>
            <button type="submit" name="checkout" class="btn btn-primary w-100">
                Complete Purchase
            </button>
        </form>
    </div>

    <!-- Bootstrap JS (including Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // If you have a separate sales_report.php snippet, load it dynamically:
        document.getElementById('showSalesReportBtn').addEventListener('click', function() {
            fetch('sales_report.php')
                .then(response => response.text())
                .then(html => {
                    // Insert snippet into DOM
                    const snippetContainer = document.createElement('div');
                    snippetContainer.innerHTML = html;
                    document.body.appendChild(snippetContainer);

                    // Show the modal
                    const modalEl = document.getElementById('salesReportModal');
                    const myModal = new bootstrap.Modal(modalEl, {});
                    myModal.show();
                })
                .catch(err => {
                    console.error('Error loading sales report snippet:', err);
                    alert('Failed to load sales report.');
                });
        });

        // Show the checkout success toast if it exists
        document.addEventListener('DOMContentLoaded', function() {
            const checkoutToastEl = document.getElementById('checkoutToast');
            if (checkoutToastEl) {
                const toast = new bootstrap.Toast(checkoutToastEl, {
                    delay: 5000
                });
                toast.show();
            }
        });
    </script>

</body>

</html>
<?php
$conn->close();
?>
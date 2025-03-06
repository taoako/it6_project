<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $product_id = $_POST['product_id'];
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];

        // Insert new product
        $query = "INSERT INTO products (product_id, name, category_id) VALUES ('$product_id', '$name', '$category_id')";
        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Product added successfully!'); window.location.href='index.php?page=products';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    } elseif (isset($_POST['add_category'])) {
        $new_category = $_POST['new_category'];

        // Insert new category
        $query = "INSERT INTO categories (name) VALUES ('$new_category')";
        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Category added successfully!'); window.location.href='add_product.php';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Add Product</h5>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product ID</label>
                        <input type="text" class="form-control" name="product_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" name="category_id" id="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $categories = $conn->query("SELECT * FROM categories");
                            while ($row = $categories->fetch_assoc()) {
                                echo "<option value='{$row['category_id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="add_product" class="btn btn-success">Add Product</button>
                    <a href="index.php?page=products" class="btn btn-secondary">Cancel</a>
                </form>
                <hr>
                <h5 class="card-title">Add New Category</h5>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="new_category" class="form-label">New Category</label>
                        <input type="text" class="form-control" name="new_category" required>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
<?php
$conn->close();
?>
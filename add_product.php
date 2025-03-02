<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $category = $_POST['category'];

    // Insert new product
    $stmt = $conn->prepare("INSERT INTO products (product_id, name, category) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $product_id, $name, $category);
    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully!'); window.location.href='index.php?page=products';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
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
                        <input type="text" class="form-control" id="product_id" name="product_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Product</button>
                    <a href="index.php?page=products" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
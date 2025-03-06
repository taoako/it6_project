<?php
include 'db_connection.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $product = $conn->query("SELECT * FROM products WHERE product_id = '$product_id'")->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $image = $_FILES['image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    // Move the uploaded file to the target directory
    if (!empty($image) && move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $query = "UPDATE products SET name = '$name', category_id = '$category_id', image = '$target_file' WHERE product_id = '$product_id'";
    } else {
        $query = "UPDATE products SET name = '$name', category_id = '$category_id' WHERE product_id = '$product_id'";
    }

    if ($conn->query($query) === TRUE) {
        echo "<script>alert('Product updated successfully!'); window.location.href='index.php?page=products';</script>";
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
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card border-success">
            <div class="card-body">
                <h5 class="card-title">Edit Product</h5>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="name" value="<?php echo $product['name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" name="category_id" id="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $categories = $conn->query("SELECT * FROM categories");
                            while ($row = $categories->fetch_assoc()) {
                                $selected = $row['category_id'] == $product['category_id'] ? 'selected' : '';
                                echo "<option value='{$row['category_id']}' $selected>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" name="image">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="100" class="mt-2">
                    </div>
                    <button type="submit" class="btn btn-success">Update Product</button>
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
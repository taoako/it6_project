<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_employee'])) {
    // Delete: Remove employee
    $employee_id = $_POST['employee_id'];

    $sql = "DELETE FROM employee WHERE employee_id = $employee_id";
    if ($conn->query($sql)) {
        echo "<script>alert('Employee deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

$sql = "SELECT * FROM employee";
$result = $conn->query($sql);
$employees = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center text-success">Daddy's Nook</h2>
        <div class="card shadow mt-4">
            <div class="card-body">
                <h5 class="card-title text-center text-primary">Employee List</h5>
                <table class="table table-hover table-bordered text-center">
                    <thead class="table-success">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Password</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo $employee['employee_id']; ?></td>
                                <td><?php echo $employee['name']; ?></td>
                                <td>••••••••</td>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="employee_id" value="<?php echo $employee['employee_id']; ?>">
                                        <button type="submit" name="delete_employee" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <a href="../index.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>
<?php
$conn->close();
?>

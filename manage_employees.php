<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include '../dbcon/db_connection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Delete employee
    if (isset($_POST['delete_employee'])) {
        $employee_id = $_POST['employee_id'];
        $stmt = $conn->prepare("DELETE FROM employee WHERE employee_id = ?");
        $stmt->bind_param("i", $employee_id);
        if ($stmt->execute()) {
            echo "<script>alert('Employee deleted successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
        $stmt->close();
    }
    
    // Add new employee
    if (isset($_POST['add_employee'])) {
        $name = $_POST['name'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $role = $_POST['role'];
        
        $stmt = $conn->prepare("INSERT INTO employee (name, first_name, last_name, role) 
                VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $first_name, $last_name, $role);
        if ($stmt->execute()) {
            echo "<script>alert('Employee added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
        $stmt->close();
    }
    
    // Update employee
    if (isset($_POST['update_employee'])) {
        $employee_id = $_POST['employee_id'];
        $name = $_POST['name'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $role = $_POST['role'];
        
        $stmt = $conn->prepare("UPDATE employee SET name = ?, first_name = ?, 
                last_name = ?, role = ? WHERE employee_id = ?");
        $stmt->bind_param("ssssi", $name, $first_name, $last_name, $role, $employee_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Employee updated successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
        $stmt->close();
    }
}

// Fetch employees for display
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
    <style>
        .btn-action {
            margin-right: 5px;
        }
        .modal-header {
            background-color: #198754;
            color: white;
        }
        .action-buttons {
            display: flex;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center text-success">Daddy's Nook</h2>
        
        <div class="card shadow mt-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Employee List</h5>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    <i class="bi bi-plus-circle"></i> Add New Employee
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-center">
                        <thead class="table-success">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?php echo $employee['employee_id']; ?></td>
                                    <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['role']); ?></td>
                                    <td class="action-buttons">
                                        <button type="button" class="btn btn-warning btn-sm btn-action edit-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editEmployeeModal"
                                                data-id="<?php echo $employee['employee_id']; ?>"
                                                data-name="<?php echo htmlspecialchars($employee['name']); ?>"
                                                data-firstname="<?php echo htmlspecialchars($employee['first_name']); ?>"
                                                data-lastname="<?php echo htmlspecialchars($employee['last_name']); ?>"
                                                data-role="<?php echo htmlspecialchars($employee['role']); ?>">
                                            Edit
                                        </button>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="employee_id" value="<?php echo $employee['employee_id']; ?>">
                                            <button type="submit" name="delete_employee" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Are you sure you want to delete this employee?');">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Corrected "Back to Dashboard" link -->
        <a href="../index.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Username</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="employee" selected>Employee</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_employee" class="btn btn-success">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="employee_id" id="edit_employee_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_employee" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Populate edit form with employee data
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const firstName = this.getAttribute('data-firstname');
                    const lastName = this.getAttribute('data-lastname');
                    const role = this.getAttribute('data-role');
                    
                    document.getElementById('edit_employee_id').value = id;
                    document.getElementById('edit_name').value = name;
                    document.getElementById('edit_first_name').value = firstName;
                    document.getElementById('edit_last_name').value = lastName;
                    document.getElementById('edit_role').value = role;
                });
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>
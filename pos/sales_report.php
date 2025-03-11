<?php
session_start();
include '../dbcon/db_connection.php';

// 1. Daily Sales
$dailySql = "SELECT * FROM daily_sales_view ORDER BY sales_day DESC";
$dailyResult = $conn->query($dailySql);

// 2. Monthly Sales
$monthlySql = "SELECT * FROM monthly_sales_view ORDER BY sales_year DESC, sales_month DESC";
$monthlyResult = $conn->query($monthlySql);

// 3. Sales by Employee
$employeeSql = "SELECT * FROM sales_by_employee_view ORDER BY total_sales DESC";
$employeeResult = $conn->query($employeeSql);

// We'll not close $conn here if you want to keep using it
?>

<!-- START OF THE BOOTSTRAP MODAL SNIPPET -->
<div class="modal fade" id="salesReportModal" tabindex="-1" aria-labelledby="salesReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salesReportModalLabel">Sales Reports</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- Nav tabs for Daily, Monthly, Employee -->
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#dailyTabContent"
                            type="button" role="tab" aria-controls="dailyTabContent" aria-selected="true">
                            Daily
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthlyTabContent"
                            type="button" role="tab" aria-controls="monthlyTabContent" aria-selected="false">
                            Monthly
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employeeTabContent"
                            type="button" role="tab" aria-controls="employeeTabContent" aria-selected="false">
                            By Employee
                        </button>
                    </li>
                </ul>

                <!-- Tab contents -->
                <div class="tab-content" id="myTabContent" style="margin-top: 1rem;">
                    <!-- DAILY TAB -->
                    <div class="tab-pane fade show active" id="dailyTabContent" role="tabpanel" aria-labelledby="daily-tab">
                        <h6>Daily Sales</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Sales Day</th>
                                        <th>Total Sales (PHP)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($dailyResult && $dailyResult->num_rows > 0): ?>
                                        <?php while ($row = $dailyResult->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['sales_day']; ?></td>
                                                <td><?php echo number_format($row['total_sales'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2">No daily data found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- MONTHLY TAB -->
                    <div class="tab-pane fade" id="monthlyTabContent" role="tabpanel" aria-labelledby="monthly-tab">
                        <h6>Monthly Sales</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Year</th>
                                        <th>Month</th>
                                        <th>Total Sales (PHP)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($monthlyResult && $monthlyResult->num_rows > 0): ?>
                                        <?php while ($row = $monthlyResult->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['sales_year']; ?></td>
                                                <td><?php echo $row['sales_month']; ?></td>
                                                <td><?php echo number_format($row['total_sales'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3">No monthly data found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- EMPLOYEE TAB -->
                    <div class="tab-pane fade" id="employeeTabContent" role="tabpanel" aria-labelledby="employee-tab">
                        <h6>Sales by Employee</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Employee Name</th>
                                        <th>Total Sales (PHP)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($employeeSql && $employeeResult->num_rows > 0): ?>
                                        <?php while ($row = $employeeResult->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['employee_id']; ?></td>
                                                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                                                <td><?php echo number_format($row['total_sales'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3">No employee data found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!-- /.tab-content -->

            </div><!-- /.modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- END OF THE BOOTSTRAP MODAL SNIPPET -->
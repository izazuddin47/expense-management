<?php
include 'db_connect.php';

session_start();
if(isset($_SESSION['email'])){
header("location:login.php");
exit;
}
// Query to get the total number of members
$members_query = "SELECT COUNT(*) AS total_members FROM users";
$members_result = mysqli_query($conn, $members_query);

// Get the total number of members
if ($members_result) {
    $row = mysqli_fetch_assoc($members_result);
    $total_members = $row['total_members'];
} else {
    $total_members = 0;
}

// Query to get the total balance (sum of all expenses)
$balance_query = "SELECT SUM(balance) AS total_balance FROM users";
$balance_result = mysqli_query($conn, $balance_query);

// Get the total balance
if ($balance_result) {
    $row = mysqli_fetch_assoc($balance_result);
    $total_balance = $row['total_balance'];
} else {
    $total_balance = 0; // If no expenses, balance is 0
}

// Query to get the total expenses (sum of all expenses)
$expenses_query = "SELECT SUM(amount) AS total_expenses FROM expense";
$expenses_result = mysqli_query($conn, $expenses_query);

// Get the total expenses
if ($expenses_result) {
    $row = mysqli_fetch_assoc($expenses_result);
    $total_expenses = $row['total_expenses'];
} else {
    $total_expenses = 0; // If no expenses, set it to 0
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Expense Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .chart-container {
            position: relative;
            height: 200px;
            width: 100%;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Expense Manager</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="expense.php">Expense</a></li>
                <li class="nav-item"><a class="nav-link" href="Addmember.php">Add Member</a></li>
                <li class="nav-item"><a class="nav-link" href="collection.php">Collection</a></li>
                <li class="nav-item"><a class="nav-link" href="report.php">Report</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row g-3">
            <!-- Total Balance -->
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Balance</h5>
                        <h3><?php echo number_format($total_balance, 2); ?> PKR</h3> <!-- Displaying total balance -->
                    </div>
                </div>
            </div>

            <!-- Total Expenses -->
            <div class="col-md-4">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Total Expenses</h5>
                        <h3><?php echo number_format($total_expenses, 2); ?> PKR</h3> <!-- Displaying total expenses -->
                    </div>
                </div>
            </div>

            <!-- Total Members -->
            <div class="col-md-4">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Total Members</h5>
                        <h3><?php echo $total_members; ?></h3> <!-- Displaying total members -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Expense Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Expense Breakdown</h5>
                        <div class="chart-container">
                            <canvas id="expenseChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Transactions</h5>
                        <ul class="list-group">
                            <li class="list-group-item">Breakfast </li>
                            <li class="list-group-item">lunch </li>
                            <li class="list-group-item">Dinner </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('expenseChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Dinner', 'lunch', 'Breakfast', 'Others'],
                datasets: [{
                    data: [300, 150, 100, 50],
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4caf50']
                }]
            }
        });
    </script>
</body>
</html>

<?php
include 'db_connect.php';

// Fetch all users for the dropdown
$userQuery = "SELECT id, name FROM users";
$userResult = mysqli_query($conn, $userQuery);

// Get the total number of users
$userCountQuery = "SELECT COUNT(*) AS total_users FROM users";
$userCountResult = mysqli_query($conn, $userCountQuery);
$totalUsersRow = mysqli_fetch_assoc($userCountResult);
$totalUsers = $totalUsersRow['total_users'];

// Initialize variables
$weeklyReport = [];
$selectedUserId = isset($_POST['user_id']) ? $_POST['user_id'] : null;
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d', strtotime('monday this week'));
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : date('Y-m-d', strtotime('sunday this week'));
$total_collections = 0; // Initialize to avoid "Undefined variable" warning
$total_expnese = 0;     // Initialize to avoid "Undefined variable" warning
$balance = 0;           // Initialize to avoid "Undefined variable" warning

if ($selectedUserId) {
    // Fetch weekly collections for the selected user
    $reportQuery = "
        SELECT SUM(amount) as total_collection
        FROM collections 
        WHERE member_id = '$selectedUserId'
        AND created_at BETWEEN '$from_date' AND '$to_date'
    ";
    $reportResult = mysqli_query($conn, $reportQuery);
    if ($reportResult && mysqli_num_rows($reportResult) > 0) {
        $row = mysqli_fetch_array($reportResult);
        $total_collections = ($row['total_collection'] !== null) ? $row['total_collection'] : 0;
    }

    // Fetch weekly expenses
    $sql_expense = "
        SELECT SUM(amount) as total_expense
        FROM expense
        WHERE created_at BETWEEN '$from_date' AND '$to_date'
        AND NOT FIND_IN_SET($selectedUserId, exclude_member)
    ";
    $expenseResult = mysqli_query($conn, $sql_expense);
    if ($expenseResult && mysqli_num_rows($expenseResult) > 0) {
        $row_expense = mysqli_fetch_assoc($expenseResult);
        $total_expnese = ($row_expense['total_expense'] !== null) ? ($row_expense['total_expense'] / $totalUsers) : 0;
    }

    // Calculate the balance
    $balance = round($total_collections - $total_expnese , 2);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            margin-bottom: 30px;
            text-align: center;
        }
        label {
            font-size: 16px;
            color: #555;
        }
        select {
            padding: 8px;
            font-size: 14px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #555;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #e9e9e9;
        }
        .no-data {
            text-align: center;
            color: red;

        }
        input[type="date"] {
            padding: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
        }
        label {
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Expense Manager</a>
            <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="expense.php">Expense</a></li>   
           <li class="nav-item"><a class="nav-link" href="Addmember.php">Add Member</a></li>
                <li class="nav-item"><a class="nav-link" href="collection.php">Collection</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
<div class="container">
    <h1>Weekly Report</h1>

    <!-- User Dropdown -->
    <form method="POST" action="" style="text-align: left; margin-left: 20px;">
    
        <label for="user_id">Select User:</label>
        
        <select name="user_id" id="user_id" required>
            
            <option value="">-- Select User --</option>
            <?php while ($user = mysqli_fetch_assoc($userResult)): ?>
                <option value="<?= $user['id']; ?>" <?= $selectedUserId == $user['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($user['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
       
        <label for="from_date">From Date:</label>
        <input type="date" id="from_date" name="from_date" value="<?= $from_date; ?>" required>
        <label for="to_date">To Date:</label>
        <input type="date" id="to_date" name="to_date"  value="<?= $to_date; ?>" required>

        <button type="submit">Generate Report</button>
    </form>

    <!-- Weekly Report Table -->
    <?php if ($selectedUserId): ?>
        <table>
            <thead>
            <tr>
                <th>Week</th>
                <th>Total Collections (PKR)</th>
                <th>Total Expenses (PKR)</th>
                <th>Balance (PKR)</th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $from_date . ' - ' . $to_date; ?></td>
                    <td><?= round($total_collections , 2        ) ?></td>
                    <td><?=  round($total_expnese , 2 )?></td>
                    <td><?= round($balance, 2); ?> PKR</td>
                </tr>
            </tbody>
        </table>
    <?php elseif ($selectedUserId): ?>
        <p class="no-data">No data found for the selected user.</p>
    <?php endif; ?>
</div>
</body>
</html>

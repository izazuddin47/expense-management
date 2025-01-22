<?php
include 'db_connect.php';

// Handle form submission for adding an expense
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    // Sanitize inputs
    $expense_type = mysqli_real_escape_string($conn, $_POST['expenseType']);
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
    $exclude_member = isset($_POST['exclude_member']) && is_array($_POST['exclude_member']) 
        ? $_POST['exclude_member'] : [];

    // Use custom expense type if 'Others' is selected
    if ($expense_type == 'Others' && !empty($_POST['otherExpenseType'])) {
        $expense_type = mysqli_real_escape_string($conn, $_POST['otherExpenseType']);
    }

    // Insert the expense data
    $exclude_member_str = implode(',', $exclude_member); // Save as a comma-separated string
    $query = "INSERT INTO expense (expense_type, amount, created_at, exclude_member) 
              VALUES ('$expense_type', '$amount', NOW(), '$exclude_member_str')";

    if (mysqli_query($conn, $query)) {
        echo "<div class='alert alert-success'>Expense added successfully!</div>";

        // Fetch all users
        $query_users = "SELECT id, balance FROM users";
        $result_users = mysqli_query($conn, $query_users);

        if ($result_users && mysqli_num_rows($result_users) > 0) {
            $valid_users = [];
            while ($user = mysqli_fetch_assoc($result_users)) {
                // Include only users who are not in the excluded list
                if (!in_array($user['id'], $exclude_member)) {
                    $valid_users[] = $user;
                }
            }

            // Calculate expense per user
            $total_users = count($valid_users);

            if ($total_users > 0) {
                $expense_per_user = $amount / $total_users;

                // Update balance for valid users
                foreach ($valid_users as $user) {
                    $user_id = $user['id'];
                    $new_balance = $user['balance'] - $expense_per_user;

                    $update_query = "UPDATE users SET balance = $new_balance WHERE id = $user_id";
                    mysqli_query($conn, $update_query);
                }
            } else {
                echo "<div class='alert alert-warning'>All members are excluded. No balance updates made.</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>No users found to deduct the expense.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
}

// Fetch members
$sql = "SELECT id, name FROM users";
$result = mysqli_query($conn, $sql);
$members = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #002D62;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #007FFF;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #002D62;
            color: white;
        }
        .nano {
            display: none; /* Default hidden */
        }

        .nano.active {
            display: block; /* Show when active */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Expense Manager</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="Addmember.php">Add Member</a></li>
            <li class="nav-item"><a class="nav-link" href="collection.php">Collection</a></li>
            <li class="nav-item"><a class="nav-link" href="report.php">Report</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <h1>Add Expense</h1>
    <form action="" method="POST">
        <div class="form-group">
            <label for="expenseType">Expense Type</label>
            <select id="expenseType" name="expenseType" required>
                <option value="">Select expense type</option>
                <option value="Breakfast">Breakfast</option>
                <option value="Lunch">Lunch</option>
                <option value="Dinner">Dinner</option>
                <option value="Others">Others</option>
            </select>
        </div>

        <div>
            <label for="exclude_member_checkbox">Exclude Member</label>
            <input type="checkbox" id="exclude_member_checkbox" name="exclude_member_checkbox" value="1">
        </div>

        <div class="nano" id="exclude_member_container" style="display: none;">
            <label for="exclude_member">Exclude Member</label>
            <select name="exclude_member[]" id="exclude_member" multiple>
                <?php foreach ($members as $member): ?>
                    <option value="<?= $member['id']; ?>"><?= $member['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" required>
        </div>

        <button type="submit" name="add_expense" class="btn">Add Expense</button>
    </form>
</div>

<div class="container">
    <h3>Members' Expenses</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Expense Type</th>
                <th>Amount (PKR)</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Fetch and display expenses
        $expenses_query = "SELECT e.expense_type, e.amount, e.created_at, GROUP_CONCAT(u.name) AS names 
                           FROM expense e 
                           LEFT JOIN users u ON FIND_IN_SET(u.id, e.exclude_member) = 0 
                           GROUP BY e.id";
        $expenses_result = mysqli_query($conn, $expenses_query);

        if ($expenses_result && mysqli_num_rows($expenses_result) > 0) {
            while ($expense = mysqli_fetch_assoc($expenses_result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($expense['names']) . "</td>";
                echo "<td>" . htmlspecialchars($expense['expense_type']) . "</td>";
                echo "<td>" . htmlspecialchars($expense['amount']) . "</td>";
                echo "<td>" . htmlspecialchars($expense['created_at']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No expenses found</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('exclude_member_checkbox');
        const excludeMemberContainer = document.getElementById('exclude_member_container');

        checkbox.addEventListener('change', function () {
            excludeMemberContainer.style.display = this.checked ? 'block' : 'none';
        });
    });
</script>

</body>
</html>

<?php
mysqli_close($conn);
?>

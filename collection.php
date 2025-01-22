<?php
include 'db_connect.php';

session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_collection'])) {
    $member_id = mysqli_real_escape_string($conn, $_POST['member_id']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Insert the collection into the database
        $query = "INSERT INTO collections (member_id, amount , created_at) 
                  VALUES ('$member_id', '$amount', NOW())";
        if (!mysqli_query($conn, $query)) {
            throw new Exception("Error inserting collection: " . mysqli_error($conn));
        }

        // Update the contributor's balance
        $updateContributor = "UPDATE users SET balance = balance + $amount WHERE id = $member_id";
        if (!mysqli_query($conn, $updateContributor)) {
            throw new Exception("Error updating contributor balance: " . mysqli_error($conn));
        }

        // Fetch total members to calculate share per member
        // $query = "SELECT COUNT(*) AS total_members FROM users";
        // $result = mysqli_query($conn, $query);
        // $total_members = mysqli_fetch_assoc($result)['total_members'];

        // // Calculate share for each member (excluding the contributor)
        // $share_per_member = $amount / $total_members;

        // Update other members' balances
        // $updateOthers = "UPDATE users SET balance = balance - $share_per_member WHERE id != $member_id";
        // if (!mysqli_query($conn, $updateOthers)) {
        //     throw new Exception("Error updating other members' balances: " . mysqli_error($conn));
        // }

        // Commit the transaction
        mysqli_commit($conn);
        echo "<div class='alert alert-success'>Collection added and balances updated successfully!</div>";
    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        mysqli_rollback($conn);
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Fetch members from the database for dropdown selection
$query = "SELECT id, name FROM users";
$result = mysqli_query($conn, $query);

$members = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
}

?>

<!-- HTML code remains unchanged below -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        h2, h3 {
            margin-top: 20px;
        }
        .form-label {
            font-weight: bold;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .alert {
            margin-top: 10px;
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
                <li class="nav-item"><a class="nav-link" href="report.php">Report</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

<div class="container mt-5">
    <h2 class="text-center">Collections Management System</h2>

    <!-- Form to Add Collections -->
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white">
            <h5>Add New Collection</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label for="member_id" class="form-label">Member Name:</label>
                    <select name="member_id" id="member_id" class="form-control" required>
                        <option value="">-- Select Member --</option>
                        <?php
                        // Populate dropdown with member names
                        foreach ($members as $member) {
                            echo "<option value='" . $member['id'] . "'>" . htmlspecialchars($member['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="amount" class="form-label">Amount (PKR):</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" name="add_collection" class="btn btn-success">Add Collection</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Display Collections -->
    <h3 class="mt-5">Collections Records</h3>
    <table class="table table-bordered table-striped ">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Member Name</th>
            <th>Amount (PKR)</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Fetch collections data
        $query = "SELECT c.id, u.name AS member_name, c.amount, c.created_at
                  FROM collections c
                  JOIN users u ON c.member_id = u.id
                  ORDER BY c.created_at DESC";

        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['member_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No collections found</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
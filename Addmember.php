<?php
// Include database connection
include 'db_connect.php';

// Handle Add Member (already working)
if (isset($_POST['add'])) {
    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Insert data into the database
    $sql = "INSERT INTO users (name, email, phone) VALUES ('$name', '$email', '$phone')";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success'>Member added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Handle Update Operation
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $conn->query("UPDATE expenses SET description='$description', amount='$amount', date='$date' WHERE id=$id");
    header("Location: ".$_SERVER['PHP_SELF']); // Refresh the page
    exit;
}

// Handle Delete Operation
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM expenses WHERE id=$id");
    header("Location: ".$_SERVER['PHP_SELF']); // Refresh the page
    exit;
}


// Fetch all users for the dropdown
$userQuery = "SELECT id, name FROM users";
$userResult = mysqli_query($conn, $userQuery);

// Initialize variables
$weeklyReport = [];
$selectedUserId = isset($_POST['user_id']) ? $_POST['user_id'] : null;

if ($selectedUserId) {
    // Fetch weekly collections and expenses for the selected user
    $reportQuery = "
        SELECT 
            YEARWEEK(c.created_at, 1) AS week,
            u.name AS user_name,
            SUM(c.amount) AS total_collections,
            SUM(e.amount) AS total_expense
        FROM users u
        LEFT JOIN collections c ON u.id = c.id AND u.id = '$selectedUserId'
        LEFT JOIN expense e ON u.id = e.id AND u.id = '$selectedUserId'
        WHERE u.id = '$selectedUserId'
        GROUP BY YEARWEEK(c.created_at, 1)
        ORDER BY YEARWEEK(c.created_at, 1) DESC
    ";
    
    $reportResult = mysqli_query($conn, $reportQuery);
    if ($reportResult) {
        while ($row = mysqli_fetch_assoc($reportResult)) {
            $weeklyReport[] = $row;
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        h1, h2 {
            color:rgb(9, 11, 14);
        }
        .alert {
            margin-top: 20px;
        }
        .btn-info {
            margin-bottom: 20px;
        }
        .table {
            margin-top: 30px;
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
                <li class="nav-item"><a class="nav-link" href="collection.php">Collection</a></li>
                <li class="nav-item"><a class="nav-link" href="report.php">Report</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 class="text-center mb-4">User Information Form</h1>
        
        <!-- Button to toggle form visibility -->
            
        
        <!-- Form for adding a new member, initially hidden -->
        <div id="addForm" style="display: none;">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" id="name" placeholder="Enter your full name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" id="phone" placeholder="Enter your phone number" required>
                </div>
                <button type="submit" name="add" class="btn btn-primary">Add Member</button>
            </form>
        </div>

        <!-- Member List Table -->
        <h2 class="mt-5">Member List</h2>
        <table class="table table-bordered">
            <thead class ="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch data from the database
                $query = "SELECT * FROM users ORDER BY id DESC";
                $result = mysqli_query($conn, $query);
                // Check if any rows are returned
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                        // Add Update/Delete links with GET parameters for respective actions
                       
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No members found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

      
      


    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
        }

        select, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>



   

   



    <!-- JavaScript for toggling the form visibility -->
    <script>
        let toggleBtn = document.getElementById('toggleFormBtn');
        let form = document.getElementById('addForm');
        toggleBtn.addEventListener('click', function() {
            if (form.style.display === "none") {
                form.style.display = "block";
                toggleBtn.textContent = "Hide Form"; // Change button text to "Hide Form"
            } else {
                form.style.display = "none";
                toggleBtn.textContent = "Add New Member"; // Change button text to "Add New Member"
            }
        });
    </script>
</body>
</html>

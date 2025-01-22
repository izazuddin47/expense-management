<?php

include 'db_connect.php'; // Ensure this file connects to the database properly

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);

    $errors = [];

    // Validation checks
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password should be at least 6 characters.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "The email is invalid.";
    }

    // Check if email already exists
    $email_check_query = "SELECT email FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $email_check_query);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Email already exists.";
    }

    // Insert data if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Secure password hashing
        $query = "INSERT INTO users (name, email, password, phone) VALUES ('$name', '$email', '$hashed_password', '$phone')";

        if (mysqli_query($conn, $query)) {
            echo "<div class='alert alert-success'>Registration successful! You can now log in.</div>";
            header("Location: login.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
}

mysqli_close($conn);
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<section class="vh-100" style="background-color: #eee;">
  <div class="container h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-lg-12 col-xl-11">
        <div class="card text-black" style="border-radius: 25px;">
          <div class="card-body p-md-5">
            <div class="row justify-content-center">
              <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">
                <p class="text-center h1 fw-bold mb-5 mx-1 mx-md-4 mt-4">Sign up</p>
                <form class="mx-1 mx-md-4" action="" method="POST">

                  <div class="mb-4">
                    <label class="form-label" for="name">Your Name</label>
                    <input type="text" name="name" id="name" class="form-control" required />
                  </div>

                  <div class="mb-4">
                    <label class="form-label" for="email">Your Email</label>
                    <input type="email" name="email" id="email" class="form-control" required />
                  </div>

                  <div class="mb-4">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control" required />
                  </div>

                  <div class="mb-4">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required />
                  </div>

                  <div class="mb-4">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required />
                  </div>

                  <div class="form-check d-flex justify-content-center mb-5">
                    <input class="form-check-input me-2" type="checkbox" value="" id="terms" required />
                    <label class="form-check-label" for="terms">
                      I agree to all statements in <a href="#!">Terms of Service</a>
                    </label>
                  </div>

                  <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
                    <button type="submit" name="register" class="btn btn-primary btn-lg">Register</button>
                  </div>
 

                </form>
              </div>
              <div class="col-md-10 col-lg-6 col-xl-7 d-flex align-items-center order-1 order-lg-2">
                <img src="https://media.istockphoto.com/id/1424757003/photo/budget-and-financial-planning-concept-including-a-management-or-executive-cfo-estimating-the.jpg?s=1024x1024&w=is&k=20&c=SvT2cAEMBp2jrWLlX82SJbKACjtJAIqj3XEZM7V4E-4=" class="img-fluid" alt="Sample image">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</body>
</html>

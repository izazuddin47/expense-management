<?php

include 'db_connect.php'; 
session_start();


if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password']; // Ensure 'password' field is correct in DB

    // Check if email exists
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Error in query: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        // echo "User fetched from DB: <pre>" . print_r($user, true) . "</pre>";

        // Verify the hashed password
        if (password_verify($pass, $user['password'])) {
            session_start(); // Start session
            $_SESSION['username'] = $email;
            $_SESSION['logged_in'] = true;
            header("location: index.php"); // Redirect to dashboard
            exit; // Stop script execution after redirect
        } else {
            echo "<div class='alert alert-danger'>Invalid password.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No account found with this email.</div>";
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <title>login</title>
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<section class="vh-100" style="background-color: #eee;">
  <div class="container h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-lg-12 col-xl-11">
        <div class="card text-black" style="border-radius: 25px;">
          <div class="card-body p-md-5">
            <div class="row justify-content-center">
              <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">

                <p class="text-center h1 fw-bold mb-5 mx-1 mx-md-4 mt-4">Sign in</p>

                <form class="mx-1 mx-md-4" action="" method="POST">

<div class="d-flex flex-row align-items-center mb-4">
    <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
    <div data-mdb-input-init class="form-outline flex-fill mb-0">
        <input type="text" name="email" id="form3Example3c" class="form-control" placeholder="Enter your email or phone" />
        <label class="form-label" for="form3Example3c">Email/phone</label>
    </div>
</div>

<div class="d-flex flex-row align-items-center mb-4">
    <i class="fas fa-lock fa-lg me-3 fa-fw"></i>
    <div data-mdb-input-init class="form-outline flex-fill mb-0">
        <input type="password" name="password" id="form3Example4c" class="form-control" placeholder="Enter your password" />
        <label class="form-label" for="form3Example4c">Password</label>
    </div>
</div>

<div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
    <button type="login" name="submit" class="btn btn-primary btn-lg">Login</button>

</div>




</form>


              </div>
              <div class="col-md-10 col-lg-6 col-xl-7 d-flex align-items-center order-1 order-lg-2">

                <img src="https://media.istockphoto.com/id/1424757003/photo/budget-and-financial-planning-concept-including-a-management-or-executive-cfo-estimating-the.jpg?s=1024x1024&w=is&k=20&c=SvT2cAEMBp2jrWLlX82SJbKACjtJAIqj3XEZM7V4E-4="
                  class="img-fluid" alt="Sample image">

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

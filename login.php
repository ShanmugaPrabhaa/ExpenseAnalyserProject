<?php
require_once 'config.php';
require_once 'conn.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, username, password_hash, role_id FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if email exists
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $username, $password_hash, $role_id);
        $stmt->fetch();

        // Hash the entered password using SHA-256
        $hashed_password = hash('sha256', $password);

        // Verify the password
        if ($hashed_password === $password_hash) {
            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id();

            // Store user information in session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role_id'] = $role_id;
            $_SESSION['session_id'] = session_id();  // Store the session ID

            // Redirect based on role
            if ($role_id == 1) {
                header("Location: userdashboard.php");
            } elseif (in_array($role_id, [2, 3, 4])) {
                header("Location: admindashboard.php");
            } else {
                // Handle cases where the role_id is unexpected
                echo "Unauthorized access.";
            }
            exit();
        } else {
            $error = "Password is not correct";
        }
    } else {
        $error = "Email does not exist";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Login</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <style>
    .login-background {
      background: url('assets/img/login.png') no-repeat center center fixed;
      background-size: cover;
      filter: blur(3px);
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: -1;
    }
    .login-container {
      position: relative;
      z-index: 1;
    }
    .select-role {
      color: rgb(0, 149, 255);
      position: absolute;
      top: 15px;
      right: 15px;
    }
    .error {
      color: red;
    }
  </style>
</head>
<body>
  <div class="login-background"></div>
  <main>
    <div class="container login-container">
      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
              <div class="d-flex justify-content-center py-4">
                <a href="index.html" class="logo d-flex align-items-center w-auto">
                  <img src="assets/img/logo.png" alt="">
                  <span class="d-none d-lg-block">ExpenseAnalyser</span>
                </a>
              </div><!-- End Logo -->
              <div class="card mb-3">
                <div class="card-body">
                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                    <p class="text-center small">Enter your Email & password to login</p>
                  </div>

                  <?php if (isset($error)): ?>
                    <div class="error text-center mb-3"><?php echo $error; ?></div>
                  <?php endif; ?>

                  <form class="row g-3 needs-validation" action="login.php" method="POST" novalidate>
                    <div class="col-12">
                      <label for="yourUsername" class="form-label">Email</label>
                      <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="text" name="username" class="form-control" id="yourUsername" required>
                        <div class="invalid-feedback">Please enter your Email.</div>
                      </div>
                    </div>
                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Password</label>
                      <input type="password" name="password" class="form-control" id="yourPassword" required>
                      <div class="invalid-feedback">Please enter your password!</div>
                    </div>
                    <div class="col-12">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" value="true" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                      </div>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">Login</button>
                    </div>
                    <div class="col-12">
                      <p class="small mb-0">Don't have account? <a href="register.html">Create an account</a></p>
                      <p class="small mb-0"><a href="forgot-password.html">Forgot Password? </a></p>
                    </div>
                    <div>
                      <p class="small mb-0"><a href="adminlogin.php">Login As Admin  </a></p>
                    </div>
                  </form>

                </div>
              </div>

            </div>
          </div>
        </div>
      </section>

                    

    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="assets/vendor/jquery/jquery.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/js/main.js"></script>
  <script>
    function selectRole(role) {
      document.getElementById('dropdownMenuButton').innerText = role;
    }
  </script>

</body>
</html>

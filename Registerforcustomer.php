<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include './Phpmodules/Conres.php'; // DB connection

$form = "login"; // default form

// --- Registration ---
if (isset($_POST['regsignup'])) {
  $form = "register";

  $fname = trim($_POST['regfname']);
  $lname = trim($_POST['reglname']);
  $email = trim($_POST['regemail']);
  $phone = trim($_POST['phone']);
  $pass  = $_POST['regpassword'];
  $cpass = $_POST['regcpassword'];

  // Phone validation
  if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $_SESSION['msg'] = "❌ Phone number must be exactly 10 digits.";
  }
  // Password match
  elseif ($pass != $cpass) {
    $_SESSION['msg'] = "❌ Passwords do not match.";
  }
  // Duplicate email check
  else {
    $check = mysqli_query($con, "SELECT 1 FROM rescustomer WHERE EMail='$email' LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
      $_SESSION['msg'] = "❌ Email already registered.";
    } else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $q = "INSERT INTO rescustomer (FirstName, LastName, EMail, phone, Password)
            VALUES ('$fname', '$lname', '$email', '$phone', '$hash')";
      if (mysqli_query($con, $q)) {
        $_SESSION['msg'] = "✅ Registration Successful.";
        $form = "login";
      } else {
        $_SESSION['msg'] = "❌ Error: " . mysqli_error($con);
      }
    }
  }
}

// --- Login ---
if (isset($_POST['signin'])) {
  $form = "login";
  $email = $_POST['email'];
  $pass  = $_POST['password'];

  // Step 1: Check if admin
  $q1 = "SELECT * FROM admintable WHERE Email='$email' LIMIT 1";
  $res1 = mysqli_query($con, $q1);

  if ($row1 = mysqli_fetch_assoc($res1)) {
    if ($pass === $row1['Password']) {
      $_SESSION['role'] = "admin";
      $_SESSION['admin'] = $row1['Email'];
      header("Location: AdminPages/adminpanel.php");
      exit;
    } else {
      $_SESSION['msg'] = "❌ Wrong Admin Password.";
    }
  } else {
    // Step 2: Check customer
    $q2 = "SELECT * FROM rescustomer WHERE EMail='$email' LIMIT 1";
    $res2 = mysqli_query($con, $q2);

    if ($row2 = mysqli_fetch_assoc($res2)) {
      if (password_verify($pass, $row2['Password'])) {
        // ✅ Save full details into session (added phone here)
        $_SESSION['username']   = $row2['FirstName'] . ' ' . $row2['LastName'];
        $_SESSION['user_email'] = $row2['EMail'];
        $_SESSION['user_phone'] = $row2['phone']; // ✅ added this line
        $_SESSION['role']       = "customer";

        // Redirect after login
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'Homes.php';
        header("Location: $redirect");
        exit;
      } else {
        $_SESSION['msg'] = "❌ Wrong Password.";
      }
    } else {
      // Email not found — trigger JS alert
      $_SESSION['msg'] = "❌ Email not found.";
      $_SESSION['alert'] = true; // flag to trigger JS alert
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register Page for Customer</title>
  <link rel="stylesheet" href="../script/css/bootstrap.min.css">
  <link rel="stylesheet" href="../script/fontawesome-free-7.0.0-web/css/all.min.css">
  <script src="../script/js/bootstrap.bundle.min.js"></script>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "poppins", sans-serif;
    }

    body {
      background-color: #c9d6ff;
      background: linear-gradient(to right, #e2e2e2, #c9d6ff);
    }

    .container {
      background: #fff;
      width: 450px;
      padding: 1rem 1.2rem;
      margin: 40px auto;
      border-radius: 10px;
      box-shadow: 0 20px 35px rgba(0, 0, 1, 0.9);
    }

    form {
      margin: 20px;
    }

    .form-title {
      font-size: 1.5rem;
      font-weight: bold;
      text-align: center;
      padding: 1.3rem;
      margin-bottom: 0.4rem;
    }

    input {
      color: inherit;
      width: 100%;
      background-color: transparent;
      border: none;
      border-bottom: 1px solid #757575;
      padding-left: 1.5rem;
      font-size: 15px;
    }

    .input-group {
      padding: 6px 0;
      position: relative;
    }

    .input-group i {
      position: absolute;
      color: black;
    }

    input:focus {
      background-color: transparent;
      outline: transparent;
      border-radius: 2px solid hsl(327, 90%, 28%);
    }

    input::placeholder {
      color: transparent;
    }

    label {
      color: #757575;
      position: absolute;
      left: 1.4rem;
      top: 0.3rem;
      cursor: auto;
      transition: 0.3s ease all;
    }

    input:focus~label,
    input:not(:placeholder-shown)~label {
      top: -0.4rem;
      color: hsl(327, 90%, 28%);
      font-size: 14px;
      background: white;
      padding: 0 5px;
    }

    .btn {
      font-size: 1.1rem;
      padding: 8px 0;
      border-radius: 5px;
      outline: none;
      border: none;
      width: 100%;
      background: rgb(125, 125, 235);
      color: white;
      cursor: pointer;
      transition: 0.9s;
    }

    .btn:hover {
      background: #07001f;
    }

    .icons {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 1.5rem;
      margin-top: 1rem;
    }

    .icons i {
      color: rgb(125, 125, 235);
      padding: 10px;
      padding-right: 30px;
      border-radius: 10px;
      font-size: 1.5rem;
      cursor: pointer;
      border: 2px solid #dfe9f5;
      margin: 0.15px;
      transition: 1s;
    }

    .icons i:hover {
      background: #07001f;
      font-size: 1.6rem;
      border: 2px solid rgb(125, 125, 235);
    }

    .links {
      display: flex;
      justify-content: space-around;
      padding: 0 4rem;
      margin-top: 0.9rem;
      font-weight: bold;
    }

    button {
      color: rgb(125, 125, 235);
      border: none;
      background-color: transparent;
      font-size: 1rem;
      font-weight: bold;
    }

    button:hover {
      text-decoration: underline;
      color: blue;
    }

    .hidden {
      display: none !important;
      position: absolute;
      left: -9999px;
    }
  </style>
</head>

<body>
  <!-- Register Form -->
  <div class="container" id="signup" style="<?php echo ($form == 'register') ? '' : 'display:none;'; ?>">
    <h1 class="form-title">Register</h1>
    <form method="post" action="">
      <?php
      if (isset($_SESSION['msg'])) {
        echo "<p style='color:red;'>" . $_SESSION['msg'] . "</p>";
        unset($_SESSION['msg']);
      }
      ?>
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="regfname" id="fname" placeholder="First Name" required>
        <label for="fname">First Name</label>
      </div>
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="reglname" id="lname" placeholder="Last Name" required>
        <label for="lname">Last Name</label>
      </div>
      <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="regemail" id="email" placeholder="Email" required>
        <label for="email">Email</label>
      </div>
      <div class="input-group">
        <i class="fas fa-phone"></i>
        <input type="text" name="phone" id="phone" placeholder="Phone Number" required>
        <label for="phone">PhoneNo</label>
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="regpassword" id="password" placeholder="Password" required>
        <label for="password">Password</label>
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="regcpassword" id="cpassword" placeholder="Confirm password" required>
        <label for="cpassword">Confirm Password</label>
      </div>
      <input type="submit" class="btn" name="regsignup" value="Sign Up">
    </form>
    <div class="icons">
      <i class="fab fa-google"></i>
      <i class="fab fa-facebook-f"></i>
    </div>
    <div class="links">
      <p>Already Have Account ? </p>
      <button id="signInButton">Sign In</button>
    </div>
  </div>

  <!-- Login Form -->
  <div class="container" id="signIn" style="<?php echo ($form == 'login') ? '' : 'display:none;'; ?>">
    <h1 class="form-title">Sign In</h1>
    <form method="post" action="">
      <?php
      if (isset($_SESSION['msg']) && empty($_SESSION['alert'])) {
        echo "<p style='color:red;'>" . $_SESSION['msg'] . "</p>";
        unset($_SESSION['msg']);
      }
      ?>
      <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" id="lemail" placeholder="Email" required>
        <label for="lemail">Email</label>
      </div>
      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" id="lpassword" placeholder="Password" required>
        <label for="lpassword">Password</label>
      </div>
      <input type="submit" class="btn" name="signin" value="Sign In">
    </form>
    <div class="icons">
      <i class="fab fa-google"></i>
      <i class="fab fa-facebook-f"></i>
    </div>
    <div class="links">
      <p>Don't have account yet? </p>
      <button id="signupButton">Sign Up</button>
    </div>

    <?php
    // JS alert for email not found
    if (isset($_SESSION['alert']) && $_SESSION['alert'] === true) {
      $alertMsg = "Email not found!";
      echo "<script>alert('$alertMsg');</script>";
      unset($_SESSION['alert']);
      unset($_SESSION['msg']);
    }
    ?>
  </div>

  <script>
    const signUpButton = document.getElementById('signInButton');
    const signInButton = document.getElementById('signupButton');
    const signInform = document.getElementById('signIn');
    const signUpForm = document.getElementById('signup');

    signInButton.addEventListener('click', function() {
      signInform.style.display = "none";
      signUpForm.style.display = "block";
    });

    signUpButton.addEventListener('click', function() {
      signUpForm.style.display = "none";
      signInform.style.display = "block";
    });
  </script>
</body>

</html>
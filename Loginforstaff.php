<?php
include './Phpmodules/Conres.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$msg = "";
$form = "login";

// ================= Staff Registration =================
if (isset($_POST['SignUpButton'])) {
  $form = "register";

  $fname  = trim($_POST['fname']);
  $lname  = trim($_POST['lname']);
  $email  = trim($_POST['email']);
  $phone  = trim($_POST['phone']);
  $skill  = trim($_POST['skill']);
  $exp    = trim($_POST['experience']);
  $pass   = $_POST['password'];
  $cpass  = $_POST['cpassword'];

  // --- Phone validation ---
  if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $msg = "❌ Phone number must be exactly 10 digits.";
  }
  // --- Password match ---
  elseif ($pass != $cpass) {
    $msg = "❌ Passwords do not match.";
  } else {
    // --- Check if email already exists ---
    // Note: Using 'ID' to be consistent with your DB
    $checkStmt = $con->prepare("SELECT ID FROM restaff WHERE EMail=? LIMIT 1");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
      $msg = "❌ Email already registered.";
    } else {
      // --- File Upload ---
      $photo = "";
      if (!empty($_FILES['image']['name'])) {
        $folder = "uploads/";
        if (!is_dir($folder)) {
          mkdir($folder, 0777, true);
        }
        $photo = $folder . time() . "_" . basename($_FILES['image']['name']);
        // Simplified move logic
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $photo)) {
           // Handle upload error if needed, but proceeding for now
        }
        // Store just the filename if you prefer, or full path. 
        // Based on previous code, we store full path relative to root usually, 
        // but here let's stick to storing the filename for cleaner DB.
        // Adjusting to store just the name for consistency with 'staffjob.php' display logic:
        $photo = basename($photo); 
      }

      if ($msg == "") {
        // --- Hash Password ---
        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

        // --- Insert staff using prepared statement ---
        $stmt = $con->prepare("INSERT INTO restaff 
          (FirstName, LastName, EMail, PhoneNumber, Skill, Experience, Photo, Password) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $fname, $lname, $email, $phone, $skill, $exp, $photo, $hashedPass);

        if ($stmt->execute()) {
          $msg = "✅ Registration successful! You can now login.";
          $form = "login";
        } else {
          $msg = "❌ Error: " . $stmt->error;
        }
      }
    }
  }
}

// ================= Login =================
if (isset($_POST['LoginButton'])) {
  $email = $_POST['loemail'];
  $pass  = $_POST['lopassword'];

  // --- Check Admin Login ---
  $stmt = $con->prepare("SELECT * FROM admintable WHERE Email=? LIMIT 1");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($row = $res->fetch_assoc()) {
    if ($row['Password'] === $pass) {
      $_SESSION['role'] = "admin";
      $_SESSION['admin'] = $row['Email'];
      header("Location: AdminPages/adminpanel.php");
      exit;
    } else {
      $msg = "❌ Wrong Admin Password.";
    }
  } else {
    // --- Check Staff Login ---
    $stmt2 = $con->prepare("SELECT * FROM restaff WHERE EMail=? LIMIT 1");
    $stmt2->bind_param("s", $email);
    $stmt2->execute();
    $res2 = $stmt2->get_result();

    if ($row2 = $res2->fetch_assoc()) {
      if (password_verify($pass, $row2['Password'])) {
        $_SESSION['role'] = "staff";
        $_SESSION['username'] = $row2['FirstName'];
        
        // ✅ CRITICAL FIX: Changed 'id' to 'ID' to match your database screenshot
        $_SESSION['staff_id'] = $row2['ID']; 
        
        header("Location: staffjob.php");
        exit;
      } else {
        $msg = "❌ Wrong Staff Password.";
      }
    } else {
      // Email not found — set message (this will trigger a JS alert on the login form)
      $msg = "❌ No account found with that email.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fixzy Staff Login/Register</title>
  <link rel="stylesheet" href="../script/css/bootstrap.min.css">
  <link rel="stylesheet" href="../script/fontawesome-free-7.0.0-web/css/all.min.css">
  <script src="../script/js/bootstrap.bundle.min.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "poppins" sans-serif;
    }

    body {
      background: linear-gradient(to right, #e2e2e2, #c9d6ff);
    }

    .container {
      background: #fff;
      width: 450px;
      padding: 1rem 1.2rem;
      margin: 40px auto;
      border-radius: 10px;
      box-shadow: 0 20px 35px rgb(0, 0, 0, 0.2);
    }

    form {
      margin: 20px;
    }

    .from-title {
      font-size: 1.5rem;
      font-weight: bold;
      text-align: center;
      padding: 0.5rem;
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
      padding: 10px 0;
      position: relative;
    }

    .input-group i {
      position: absolute;
      color: black;
    }

    input:focus {
      outline: none;
      border-radius: 2px;
    }

    input::placeholder {
      color: transparent;
    }

    label {
      color: #757575;
      position: absolute;
      left: 1.5rem;
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
      transition: 0.3s;
    }

    .btn:hover {
      background: #07001f;
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
  </style>
</head>

<body>

  <div class="container" id="Loginemp" style="<?php echo $form == 'login' ? '' : 'display:none;'; ?>">
    <h1 class="from-title">Login</h1>
    <?php if (!empty($msg) && $form == "login") {
      echo "<p style='color:red; text-align:center;'>$msg</p>";
    } ?>
    <form method="post">
      <div class="input-group"><i class="fas fa-envelope"></i>
        <input type="email" name="loemail" placeholder="Email" required>
        <label>Email</label>
      </div>
      <div class="input-group"><i class="fas fa-lock"></i>
        <input type="password" name="lopassword" placeholder="Password" required>
        <label>Password</label>
      </div>
      <button type="submit" name="LoginButton" class="btn">Login</button>
    </form>
    <div class="links">
      <p>Don't have an account?</p>
      <button onclick="toggleForm('register')">Register</button>
    </div>

    <?php
    if (!empty($msg) && $form == "login" && strpos($msg, "No account found") !== false) {
      $clean = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
      echo "<script type='text/javascript'>alert(\"$clean\");</script>";
    }
    ?>
  </div>

  <div class="container" id="SignUpemp" style="<?php echo $form == 'register' ? '' : 'display:none;'; ?>">
    <h1 class="from-title">Register as Staff</h1>
    <?php if (!empty($msg) && $form == "register") {
      echo "<p style='color:red; text-align:center;'>$msg</p>";
    } ?>
    <form method="post" enctype="multipart/form-data">
      <div class="input-group"><i class="fas fa-user"></i>
        <input type="text" name="fname" placeholder="First Name" required>
        <label>First Name</label>
      </div>
      <div class="input-group"><i class="fas fa-user"></i>
        <input type="text" name="lname" placeholder="Last Name" required>
        <label>Last Name</label>
      </div>
      <div class="input-group"><i class="fas fa-envelope"></i>
        <input type="email" name="email" placeholder="Email" required>
        <label>Email</label>
      </div>
      <div class="input-group"><i class="fas fa-phone"></i>
        <input type="tel" name="phone" placeholder="Phone Number" required>
        <label>Phone Number</label>
      </div>
      <div class="input-group"><i class="fas fa-people-group"></i>
        <input type="text" name="skill" placeholder="Enter Skill" required>
        <label>Enter Skill</label>
      </div>
      <div class="input-group"><i class="fas fa-clock"></i>
        <input type="number" name="experience" placeholder="Experience" required>
        <label>Experience</label>
      </div>
      <div class="input-group"><i class="fas fa-image"></i>
        <input type="file" name="image">
        <label>Upload Photo</label>
      </div>
      <div class="input-group"><i class="fas fa-lock"></i>
        <input type="password" name="password" placeholder="Password" required>
        <label>Password</label>
      </div>
      <div class="input-group"><i class="fas fa-lock"></i>
        <input type="password" name="cpassword" placeholder="Confirm Password" required>
        <label>Confirm Password</label>
      </div>
      <button type="submit" name="SignUpButton" class="btn">Register</button>
    </form>
    <div class="links">
      <p>Already have an account?</p>
      <button onclick="toggleForm('login')">Login</button>
    </div>
  </div>

  <script>
    function toggleForm(form) {
      document.getElementById("Loginemp").style.display = (form === "login") ? "block" : "none";
      document.getElementById("SignUpemp").style.display = (form === "register") ? "block" : "none";
    }
  </script>

</body>

</html>
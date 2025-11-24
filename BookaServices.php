<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include './Phpmodules/Conres.php';

$success = "";
$error = "";

// ✅ Logged-in user info
$userFullName = $_SESSION['username'] ?? "";
$userEmail = $_SESSION['user_email'] ?? "";
$userPhone = $_SESSION['user_phone'] ?? "";

// ✅ Backup fetch phone if not in session
if (empty($userPhone) && !empty($userEmail)) {
  $q = "SELECT phone FROM rescustomer WHERE EMail='$userEmail' LIMIT 1";
  $res = mysqli_query($con, $q);
  if ($row = mysqli_fetch_assoc($res)) {
    $_SESSION['user_phone'] = $row['phone'];
    $userPhone = $row['phone'];
  }
}

// ✅ Get selected service from URL
$selectedService = isset($_GET['service']) ? trim($_GET['service']) : "";

// ✅ Preserve form values
$old = [
  'fullname' => $userFullName,
  'email' => $userEmail,
  'phone' => $userPhone,
  'service' => $selectedService,
  'address' => '',
  'date' => '',
  'time' => '',
  'issue' => ''
];

// ✅ Fetch all services dynamically
$serviceList = [];
$res = mysqli_query($con, "SELECT DISTINCT service_name FROM services ORDER BY service_name ASC");
while ($row = mysqli_fetch_assoc($res)) {
  $serviceList[] = $row['service_name'];
}

// ✅ Handle form submit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
  foreach ($old as $key => $value) {
    if (isset($_POST[$key])) $old[$key] = trim($_POST[$key]);
  }

  if (
    $old['fullname'] == "" || $old['email'] == "" || $old['phone'] == "" ||
    $old['service'] == "" || $old['date'] == "" || $old['time'] == ""
  ) {
    $error = "Please fill in all required fields.";
  } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
    $error = "❌ Please enter a valid email address.";
  } elseif (!preg_match("/^[0-9]{10}$/", $old['phone'])) {
    $error = "❌ Please enter a valid 10-digit phone number.";
  } elseif (strlen($old['issue']) > 500) {
    $error = "❌ Issue description cannot exceed 500 characters.";
  } else {
    $dayOfWeek = date('w', strtotime($old['date']));
    $timeCheck = strtotime($old['time']);
    $formattedTime = date("h:i A", $timeCheck);

    if (strtotime($old['date']) < strtotime(date("Y-m-d"))) {
      $error = "❌ You cannot book a past date.";
    } elseif ($dayOfWeek == 0) {
      $error = "❌ Sorry, bookings cannot be made on Sundays.";
    } elseif ($dayOfWeek >= 1 && $dayOfWeek <= 5 && ($timeCheck < strtotime("08:00") || $timeCheck > strtotime("19:00"))) {
      $error = "❌ Bookings allowed only 08:00 AM - 07:00 PM (Mon–Fri).";
    } elseif ($dayOfWeek == 6 && ($timeCheck < strtotime("09:00") || $timeCheck > strtotime("17:00"))) {
      $error = "❌ Bookings allowed only 09:00 AM - 05:00 PM (Saturday).";
    }

    if ($error == "") {
      $check = $con->prepare("SELECT id FROM bookingservice WHERE Email=? AND Date=? AND Time=?");
      $check->bind_param("sss", $old['email'], $old['date'], $formattedTime);
      $check->execute();
      $check->store_result();

      if ($check->num_rows > 0) {
        $error = "❌ You already have a booking at this date & time with this email.";
      } else {
        $stmt = $con->prepare("INSERT INTO bookingservice (fullname, Email, PhoneNumber, Service, Address, Date, Time, Description)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $old['fullname'], $old['email'], $old['phone'], $old['service'], $old['address'], $old['date'], $formattedTime, $old['issue']);
        if ($stmt->execute()) {
          $_SESSION['success'] = "✅ Appointment booked successfully!";
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
        } else {
          $error = "Something went wrong. Please try again.";
        }
        $stmt->close();
      }
      $check->close();
    }
  }
}

if (isset($_SESSION['success'])) {
  $success = $_SESSION['success'];
  unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book a Service - Fixzy</title>
  <link rel="stylesheet" href="../script/css/bootstrap.min.css">
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold text-primary" href="#">Fixzy</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="Homes.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="service.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="#">About Us</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
        </ul>
        <a href="#" class="btn btn-primary ms-3">Book Now</a>
      </div>
    </div>
  </nav>

  <!-- Booking Section -->
  <section class="py-5">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold">Book a Service</h2>
        <p class="text-muted">Fill out the form below to schedule a service appointment with one of our professionals.</p>
      </div>

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4">
              <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
              <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

              <form method="POST" action="">
                <div class="row g-3">
                  <div class="col-md-6">
                    <input type="text" class="form-control" name="fullname" placeholder="Full Name" required maxlength="50"
                      value="<?php echo htmlspecialchars($old['fullname']); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <input type="email" class="form-control" name="email" placeholder="Email" required maxlength="100"
                      value="<?php echo htmlspecialchars($old['email']); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <input type="text" class="form-control" name="phone" placeholder="Phone Number" required pattern="[0-9]{10}" title="Enter 10 digit phone number"
                      value="<?php echo htmlspecialchars($old['phone']); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <select class="form-select" name="service" required>
                      <option value="">Select a service</option>
                      <?php foreach ($serviceList as $service): ?>
                        <option value="<?= htmlspecialchars($service) ?>" <?= ($old['service'] == $service) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($service) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-12">
                    <input type="text" class="form-control" name="address" placeholder="Address" required maxlength="200"
                      value="<?php echo htmlspecialchars($old['address']); ?>">
                  </div>
                  <div class="col-md-6">
                    <input type="date" class="form-control" name="date" id="datePicker" required min="<?php echo date('Y-m-d'); ?>"
                      value="<?php echo htmlspecialchars($old['date']); ?>">
                  </div>
                  <div class="col-md-6">
                    <input type="time" class="form-control" name="time" id="timePicker" required
                      value="<?php echo htmlspecialchars($old['time']); ?>">
                  </div>
                  <div class="col-12">
                    <textarea class="form-control" name="issue" rows="3" placeholder="Issue Description (Optional)" maxlength="500"><?php echo htmlspecialchars($old['issue']); ?></textarea>
                  </div>
                  <div class="col-12">
                    <button type="submit" name="submit" class="btn btn-primary w-100">Schedule Appointment</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card shadow-sm border-0 p-4">
            <h5 class="fw-bold mb-3">Booking Information</h5>
            <p><strong>Our Service Hours</strong><br>
              Monday - Friday: 8:00 AM - 7:00 PM <br>
              Saturday: 9:00 AM - 5:00 PM <br>
              Sunday: Closed
            </p>
            <p><strong>Response Time</strong><br>
              We aim to confirm all bookings within 2 hours during business hours.
            </p>
            <p><strong>Why Choose Fixzy?</strong></p>
            <ul class="list-unstyled">
              <li>✔ Professional, background-checked technicians</li>
              <li>✔ Upfront, transparent pricing</li>
              <li>✔ 90-day service guarantee</li>
              <li>✔ Licensed, insured professionals</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    const dateInput = document.getElementById("datePicker");
    const timeInput = document.getElementById("timePicker");
    dateInput.addEventListener("change", function() {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const selectedDate = new Date(this.value);
      if (selectedDate < today) {
        alert("❌ You cannot select a past date.");
        this.value = "";
      } else if (selectedDate.getDay() === 0) {
        alert("❌ Sorry, bookings are not allowed on Sundays.");
        this.value = "";
      }
    });
    timeInput.addEventListener("change", function() {
      const dateVal = dateInput.value;
      if (!dateVal) {
        alert("Please select a date first.");
        this.value = "";
        return;
      }
      const day = new Date(dateVal).getDay();
      const t = this.value;
      if (day >= 1 && day <= 5 && (t < "08:00" || t > "19:00")) {
        alert("❌ Time must be 08:00 AM - 07:00 PM (Mon–Fri).");
        this.value = "";
      } else if (day === 6 && (t < "09:00" || t > "17:00")) {
        alert("❌ Time must be 09:00 AM - 05:00 PM (Saturday).");
        this.value = "";
      }
    });
  </script>
</body>

</html>
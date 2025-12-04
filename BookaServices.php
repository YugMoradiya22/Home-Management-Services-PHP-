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
    $error = "Please enter a valid email address.";
  } elseif (!preg_match("/^[0-9]{10}$/", $old['phone'])) {
    $error = "Please enter a valid 10-digit phone number.";
  } elseif (strlen($old['issue']) > 500) {
    $error = "Issue description cannot exceed 500 characters.";
  } else {
    $dayOfWeek = date('w', strtotime($old['date']));
    $timeCheck = strtotime($old['time']);
    $formattedTime = date("h:i A", $timeCheck);

    if (strtotime($old['date']) < strtotime(date("Y-m-d"))) {
      $error = "You cannot book a past date.";
    } elseif ($dayOfWeek == 0) {
      $error = "Sorry, bookings cannot be made on Sundays.";
    } elseif ($dayOfWeek >= 1 && $dayOfWeek <= 5 && ($timeCheck < strtotime("08:00") || $timeCheck > strtotime("19:00"))) {
      $error = "Bookings allowed only 08:00 AM - 07:00 PM (Mon–Fri).";
    } elseif ($dayOfWeek == 6 && ($timeCheck < strtotime("09:00") || $timeCheck > strtotime("17:00"))) {
      $error = "Bookings allowed only 09:00 AM - 05:00 PM (Saturday).";
    }

    if ($error == "") {
      // ✅ LOGIC: Check for duplicate booking of SAME service at SAME time
      $check = $con->prepare("SELECT id FROM bookingservice WHERE Email=? AND Date=? AND Time=? AND Service=?");
      $check->bind_param("ssss", $old['email'], $old['date'], $formattedTime, $old['service']);
      $check->execute();
      $check->store_result();

      if ($check->num_rows > 0) {
        $error = "You already have a booking for **" . htmlspecialchars($old['service']) . "** at this time.";
      } else {
        // --- 🟢 FIX APPLIED: GENERATE OTP ---
        $otp_code = rand(1000, 9999); 

        // --- 🟢 FIX APPLIED: INSERT OTP INTO DB ---
        $stmt = $con->prepare("INSERT INTO bookingservice (fullname, Email, PhoneNumber, Service, Address, Date, Time, Description, OTP)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Added extra 's' to types and $otp_code to params
        $stmt->bind_param("sssssssss", $old['fullname'], $old['email'], $old['phone'], $old['service'], $old['address'], $old['date'], $formattedTime, $old['issue'], $otp_code);
        
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        --secondary-bg: #F3F4F6;
        --card-bg: #ffffff;
        --text-dark: #1F2937;
        --accent-color: #4F46E5;
    }

    body { background-color: var(--secondary-bg); font-family: 'Poppins', sans-serif; color: var(--text-dark); }

    /* Navbar */
    .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 15px 0; border-bottom: 1px solid rgba(0,0,0,0.05); }
    .brand-text { font-weight: 800; color: var(--accent-color); font-size: 1.6rem; }

    /* Header */
    .header-bg { background: var(--primary-gradient); height: 220px; border-radius: 0 0 30px 30px; margin-bottom: -120px; position: relative; z-index: 1; }
    .page-title { color: white; text-align: center; padding-top: 50px; }

    /* Cards */
    .booking-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: none; overflow: hidden; }
    .card-body { padding: 40px; }

    /* Form Elements */
    .form-label { font-weight: 500; font-size: 0.9rem; color: #6B7280; margin-bottom: 8px; }
    .form-control, .form-select { border-radius: 10px; padding: 12px 15px; border: 1px solid #E5E7EB; background: #F9FAFB; transition: 0.3s; }
    .form-control:focus, .form-select:focus { border-color: var(--accent-color); background: white; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
    
    .btn-submit { background: var(--accent-color); color: white; font-weight: 600; padding: 14px; border-radius: 12px; border: none; width: 100%; transition: 0.3s; }
    .btn-submit:hover { background: #4338CA; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3); }

    /* Info Sidebar */
    .info-card { background: white; border-radius: 20px; padding: 30px; margin-bottom: 20px; border: 1px solid #E5E7EB; }
    .info-icon { width: 40px; height: 40px; background: #EEF2FF; color: var(--accent-color); display: flex; align-items: center; justify-content: center; border-radius: 10px; margin-bottom: 15px; font-size: 1.2rem; }
    .hours-list li { margin-bottom: 10px; display: flex; justify-content: space-between; font-size: 0.95rem; color: #4B5563; }
    .hours-list li strong { color: #111; }
    .closed-badge { background: #FEE2E2; color: #DC2626; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; }

    /* 🎨 CUSTOM ALERT DESIGN */
    .custom-alert {
        border: none;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
        animation: slideIn 0.4s ease-out;
    }
    .alert-success-custom {
        background-color: #ECFDF5;
        color: #065F46;
        border-left: 5px solid #10B981;
    }
    .alert-danger-custom {
        background-color: #FEF2F2;
        color: #991B1B;
        border-left: 5px solid #EF4444;
    }
    .alert-icon { font-size: 1.5rem; margin-right: 15px; }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand brand-text" href="Homes.php">Fixzy.</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item"><a class="nav-link" href="Homes.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="service.php">All Services</a></li>
          <li class="nav-item ms-3"><a href="my_bookings.php" class="btn btn-outline-dark btn-sm rounded-pill px-4">My Bookings</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="header-bg">
    <div class="page-title">
      <h2 class="fw-bold display-6">Schedule Service</h2>
      <p class="opacity-75">Book expert professionals in just a few clicks</p>
    </div>
  </div>

  <div class="container pb-5" style="position: relative; z-index: 10;">
    <div class="row g-4 justify-content-center">
      
      <div class="col-lg-8">
        <div class="booking-card">
          <div class="card-body">
            
            <?php if ($success): ?>
              <div class="custom-alert alert-success-custom" id="msgAlert">
                <i class="bi bi-check-circle-fill alert-icon"></i>
                <div><strong>Success!</strong><br><?php echo $success; ?></div>
              </div>
            <?php endif; ?>

            <?php if ($error): ?>
              <div class="custom-alert alert-danger-custom" id="msgAlert">
                <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
                <div><strong>Oops!</strong><br><?php echo $error; ?></div>
              </div>
            <?php endif; ?>

            <div id="js-alert-container"></div>

            <form method="POST" action="">
              <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-person-lines-fill"></i> Contact Details</h5>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="form-label">Full Name</label>
                  <input type="text" class="form-control" name="fullname" required value="<?php echo htmlspecialchars($old['fullname']); ?>" readonly style="background-color: #f3f4f6;">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email Address</label>
                  <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars($old['email']); ?>" readonly style="background-color: #f3f4f6;">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone Number</label>
                  <input type="text" class="form-control" name="phone" required value="<?php echo htmlspecialchars($old['phone']); ?>" readonly style="background-color: #f3f4f6;">
                </div>
              </div>

              <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-calendar-check"></i> Service Details</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Choose Service</label>
                  <select class="form-select" name="service" required>
                    <option value="">-- Select a service --</option>
                    <?php foreach ($serviceList as $service): ?>
                      <option value="<?= htmlspecialchars($service) ?>" <?= ($old['service'] == $service) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($service) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                
                <div class="col-12">
                  <label class="form-label">Home Address</label>
                  <input type="text" class="form-control" name="address" placeholder="House No, Street, Area..." required value="<?php echo htmlspecialchars($old['address']); ?>">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Preferred Date</label>
                  <input type="date" class="form-control" name="date" id="datePicker" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($old['date']); ?>">
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">Preferred Time</label>
                  <input type="time" class="form-control" name="time" id="timePicker" required value="<?php echo htmlspecialchars($old['time']); ?>">
                  <div class="form-text text-muted">See operating hours on the right</div>
                </div>

                <div class="col-12">
                  <label class="form-label">Describe Issue (Optional)</label>
                  <textarea class="form-control" name="issue" rows="3" placeholder="Briefly describe the problem..."><?php echo htmlspecialchars($old['issue']); ?></textarea>
                </div>
              </div>

              <div class="mt-5">
                <button type="submit" name="submit" class="btn-submit">
                  Confirm Booking <i class="bi bi-arrow-right ms-2"></i>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="info-card shadow-sm">
          <div class="info-icon"><i class="bi bi-clock-history"></i></div>
          <h5 class="fw-bold mb-3">Operating Hours</h5>
          <ul class="list-unstyled hours-list mb-0">
            <li><span>Monday - Friday</span><strong>08:00 AM – 07:00 PM</strong></li>
            <li><span>Saturday</span><strong>09:00 AM – 05:00 PM</strong></li>
            <li><span>Sunday</span><span class="closed-badge">CLOSED</span></li>
          </ul>
        </div>
        <div class="info-card shadow-sm" style="background: #F0FDF4; border-color: #BBF7D0;">
          <div class="info-icon" style="background: #DCFCE7; color: #16A34A;"><i class="bi bi-shield-check"></i></div>
          <h5 class="fw-bold mb-3">Why Fixzy?</h5>
          <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-muted">
            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Verified Professionals</li>
            <li><i class="bi bi-check-circle-fill text-success me-2"></i> Transparent Pricing</li>
            <li><i class="bi bi-check-circle-fill text-success me-2"></i> 90-Day Service Warranty</li>
            <li><i class="bi bi-check-circle-fill text-success me-2"></i> 24/7 Customer Support</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <script src="../script/js/bootstrap.bundle.min.js"></script>
  <script>
    // --- 1. Auto Dismiss PHP Alerts ---
    setTimeout(function() {
      const alert = document.getElementById('msgAlert');
      if(alert) {
        alert.style.transition = "opacity 0.5s ease";
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500);
      }
    }, 4000);

    // --- 2. Function to Show Custom JS Errors (Toasts) ---
    function showError(message) {
        const container = document.getElementById("js-alert-container");
        
        // Create Alert HTML dynamically
        const alertDiv = document.createElement("div");
        alertDiv.className = "custom-alert alert-danger-custom";
        alertDiv.innerHTML = `
            <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
            <div><strong>Attention!</strong><br>${message}</div>
        `;

        // Append to container
        container.appendChild(alertDiv);

        // Auto remove after 3 seconds
        setTimeout(() => {
            alertDiv.style.transition = "opacity 0.5s ease";
            alertDiv.style.opacity = "0";
            setTimeout(() => alertDiv.remove(), 500);
        }, 3000);
    }

    const dateInput = document.getElementById("datePicker");
    const timeInput = document.getElementById("timePicker");

    // --- 3. Validate Date (Using Custom Alert) ---
    dateInput.addEventListener("change", function() {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const selectedDate = new Date(this.value);
      
      if (selectedDate < today) {
        showError("You cannot select a past date.");
        this.value = "";
      } else if (selectedDate.getDay() === 0) { // 0 = Sunday
        showError("Sorry, we are closed on Sundays.");
        this.value = "";
      }
    });

    // --- 4. Validate Time (Using Custom Alert) ---
    timeInput.addEventListener("change", function() {
      const dateVal = dateInput.value;
      if (!dateVal) {
        showError("Please select a date first.");
        this.value = "";
        return;
      }
      
      const day = new Date(dateVal).getDay();
      const t = this.value; // "HH:MM" format
      
      // Mon-Fri (1-5): 08:00 to 19:00
      if (day >= 1 && day <= 5) {
        if (t < "08:00" || t > "19:00") {
          showError("Weekday hours: 08:00 AM - 07:00 PM");
          this.value = "";
        }
      } 
      // Saturday (6): 09:00 to 17:00
      else if (day === 6) {
        if (t < "09:00" || t > "17:00") {
          showError("Saturday hours: 09:00 AM - 05:00 PM");
          this.value = "";
        }
      }
    });
  </script>
</body>
</html>
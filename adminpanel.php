<?php
include '../Phpmodules/Conres.php';

// Fetch stats for cards
$totalCustomers = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM rescustomer"))['count'];
$totalStaff = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM restaff"))['count'];
$totalServices = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM services"))['count'];
$totalBookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM bookingservice"))['count'];

// Fetch latest bookings
$bookingQuery = "SELECT ID, FullName, service, Date, PhoneNumber, Time, Address 
                 FROM bookingservice ORDER BY ID ASC LIMIT 5";
$bookingResult = mysqli_query($con, $bookingQuery);

// Fetch new staff members
$staffQuery = "SELECT ID, FirstName, LastName, Skill, Experience, PhoneNumber 
               FROM restaff ORDER BY ID ASC LIMIT 5";
$staffResult = mysqli_query($con, $staffQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="./script/css/bootstrap.min.css" rel="stylesheet">
  <script src="./script/js/bootstrap.bundle.js"></script>
  <link href="./script/bootstrap-icons-1.13.1/bootstrap-icons-1.13.1/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f9;
    }

    .sidebar {
      height: 100vh;
      background: linear-gradient(180deg, #0d47a1, #1976d2);
      color: white;
      padding-top: 20px;
      position: fixed;
      width: 240px;
    }

    .sidebar h4 {
      text-align: center;
      font-weight: bold;
      margin-bottom: 30px;
    }

    .sidebar a {
      display: block;
      padding: 12px 20px;
      color: white;
      text-decoration: none;
      font-size: 16px;
      border-radius: 8px;
      margin: 6px 10px;
      transition: 0.3s;
    }

    .sidebar a:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .sidebar a.logout-btn {
      position: absolute;
      bottom: 20px;
      width: 90%;
      background: #d32f2f;
    }

    .sidebar a.logout-btn:hover {
      background: #f44336;
    }

    .header {
      margin-left: 240px;
      background: #0d47a1;
      color: white;
      padding: 15px;
      font-size: 20px;
      font-weight: bold;
    }

    .content {
      margin-left: 240px;
      padding: 20px;
    }

    .card-stats {
      border-radius: 12px;
      padding: 20px;
      color: white;
      box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .bg-blue {
      background: #1976d2;
    }

    .bg-green {
      background: #388e3c;
    }

    .bg-orange {
      background: #f57c00;
    }

    .bg-red {
      background: #d32f2f;
    }

    .card-stats h3 {
      margin: 0;
      font-size: 26px;
      font-weight: bold;
    }

    .card-stats p {
      margin: 5px 0 0;
      font-size: 16px;
    }

    .table thead {
      background: #1976d2;
      color: white;
    }

    .card {
      border-radius: 12px;
      margin-bottom: 25px;
    }

    .footer {
      margin-left: 240px;
      background: #0d47a1;
      color: white;
      text-align: center;
      padding: 12px;
      margin-top: 30px;
      font-size: 14px;
    }

    /* Loader */
    #loader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h4><i class="bi bi-speedometer2"></i> Admin</h4>
    <a href="adminpanel.php"><i class="bi bi-house-door-fill"></i> Dashboard</a>
    <a href="Managecus.php"><i class="bi bi-people-fill"></i> Manage Customers</a>
    <a href="Managestaff.php"><i class="bi bi-person-workspace"></i> Manage Staff</a>
    <a href="Manaervice.php"><i class="bi bi-box-seam"></i> Manage Services</a>
    <a href="Manbooking.php"><i class="bi bi-calendar-check-fill"></i> Manage Bookings</a>
    <a href="admin_assign.php"><i class="bi bi-calendar-check-fill"></i> Assign Role</a>
    <a href="../Homes.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>

  <!-- Header -->
  <div class="header">
    📊 Admin Dashboard
  </div>

  <!-- Content -->
  <div class="content">
    <!-- Stat Cards -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card-stats bg-blue">
          <h3><?= $totalCustomers ?></h3>
          <p>Total Customers</p><i class="bi bi-people-fill fs-3"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-stats bg-green">
          <h3><?= $totalStaff ?></h3>
          <p>Total Staff</p><i class="bi bi-person-workspace fs-3"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-stats bg-orange">
          <h3><?= $totalServices ?></h3>
          <p>Total Services</p><i class="bi bi-box-seam fs-3"></i>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-stats bg-red">
          <h3><?= $totalBookings ?></h3>
          <p>Total Bookings</p><i class="bi bi-calendar-check-fill fs-3"></i>
        </div>
      </div>
    </div>
    <!-- Latest Bookings -->
    <div class="card">
      <div class="card-body">
        <h4 class="mb-3"><i class="bi bi-journal-text"></i> Latest Bookings</h4>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Service</th>
              <th>Date</th>
              <th>Phone</th>
              <th>Time</th>
              <th>Address</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($bookingResult)) { ?>
              <tr>
                <td><?= $row['ID'] ?></td>
                <td><?= htmlspecialchars($row['FullName']) ?></td>
                <td><?= htmlspecialchars($row['service']) ?></td>
                <td><?= $row['Date'] ?></td>
                <td><?= $row['PhoneNumber'] ?></td>
                <td><?= $row['Time'] ?></td>
                <td><?= htmlspecialchars($row['Address']) ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- New Staff -->
    <div class="card">
      <div class="card-body">
        <h4 class="mb-3"><i class="bi bi-person-lines-fill"></i> New Staff Members</h4>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Full Name</th>
              <th>Skill</th>
              <th>Experience</th>
              <th>Phone</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($staffResult)) { ?>
              <tr>
                <td><?= $row['ID'] ?></td>
                <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                <td><?= htmlspecialchars($row['Skill']) ?></td>
                <td><?= htmlspecialchars($row['Experience']) ?> yrs</td>
                <td><?= $row['PhoneNumber'] ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    &copy; <?= date("Y") ?> Fixzy Services. All Rights Reserved.
  </div>
</body>

</html>
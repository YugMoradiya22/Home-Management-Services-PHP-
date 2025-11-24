<?php
include '../Phpmodules/Conres.php';

// ---- Stats for cards ----
$totalCustomers = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM rescustomer"))['count'];
$totalStaff     = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM restaff"))['count'];
$totalServices  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM services"))['count'];
$totalBookings  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM bookingservice"))['count'];

// ---- Pagination setup ----
$limit = 10;
$staffPage    = isset($_GET['spage']) ? (int)$_GET['spage'] : 1;
$staffPage    = max($staffPage, 1); // safety
$staffOffset  = ($staffPage - 1) * $limit;

// ---- Fetch staff ---- (optimized select: fetch only required columns)
$staffResult = mysqli_query(
  $con,
  "SELECT Id, FirstName, LastName, Email, PhoneNumber, Skill, Experience, Photo 
   FROM restaff ORDER BY Id ASC LIMIT $limit OFFSET $staffOffset"
);

// ---- Total pages ----
$totalStaffRows  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM restaff"))['total'];
$totalStaffPages = ceil($totalStaffRows / $limit);

// ---- Handle Add Staff ----
if (isset($_POST['add_staff'])) {
  $fname   = trim($_POST['fname']);
  $lname   = trim($_POST['lname']);
  $email   = trim($_POST['email']);
  $phone   = trim($_POST['phone']);
  $skill   = trim($_POST['skill']);
  $exp     = trim($_POST['experience']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Upload photo
  $photoName = null;
  if (!empty($_FILES['photo']['name'])) {
    $photoName = time() . "_" . basename($_FILES['photo']['name']);
    move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/" . $photoName);
  }

  $stmt = $con->prepare("INSERT INTO restaff (FirstName, LastName, Email, PhoneNumber, Skill, Experience, Photo, Password) VALUES (?,?,?,?,?,?,?,?)");
  $stmt->bind_param("ssssssss", $fname, $lname, $email, $phone, $skill, $exp, $photoName, $password);
  $stmt->execute();
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// ---- Handle Delete ----
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  mysqli_query($con, "DELETE FROM restaff WHERE Id=$id");
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// ---- Handle Edit Staff ----
if (isset($_POST['edit_staff'])) {
  $id      = intval($_POST['staff_id']);
  $fname   = trim($_POST['fname']);
  $lname   = trim($_POST['lname']);
  $email   = trim($_POST['email']);
  $phone   = trim($_POST['phone']);
  $skill   = trim($_POST['skill']);
  $exp     = trim($_POST['experience']);

  $photoQuery = "";
  if (!empty($_FILES['photo']['name'])) {
    $photoName = time() . "_" . basename($_FILES['photo']['name']);
    move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/" . $photoName);
    $photoQuery = ", Photo='$photoName'";
  }

  $query = "UPDATE restaff SET FirstName='$fname', LastName='$lname', Email='$email', PhoneNumber='$phone',
            Skill='$skill', Experience='$exp' $photoQuery WHERE Id=$id";
  mysqli_query($con, $query);

  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Staff</title>
  <link href="./script/css/bootstrap.min.css" rel="stylesheet">

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
      background: white;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
  </style>
</head>

<body>
  <!-- Loader -->
  <div id="loader">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

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
  <div class="header">👨‍💼 Staff Management</div>

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

    <!-- Staff Data -->
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4><i class="bi bi-journal-text"></i> Staff Data </h4>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="bi bi-person-plus"></i> Add Staff</button>
        </div>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Skill</th>
              <th>Exp</th>
              <th>Photo</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($staffResult)) { ?>
              <tr>
                <td><?= $row['Id'] ?></td>
                <td><?= htmlspecialchars($row['FirstName'] . " " . $row['LastName']) ?></td>
                <td><?= htmlspecialchars($row['Email']) ?></td>
                <td><?= htmlspecialchars($row['PhoneNumber']) ?></td>
                <td><?= htmlspecialchars($row['Skill']) ?></td>
                <td><?= htmlspecialchars($row['Experience']) ?> Years</td>
                <td><?php if ($row['Photo']) { ?><img src="../uploads/<?= htmlspecialchars($row['Photo']) ?>" width="40" class="rounded"><?php } ?></td>
                <td>
                  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editStaffModal<?= $row['Id'] ?>"><i class="bi bi-pencil"></i> Edit</button>
                  <a href="?delete=<?= $row['Id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this staff?');"><i class="bi bi-trash"></i> Delete</a>
                </td>
              </tr>
              <!-- Edit Modal -->
              <div class="modal fade" id="editStaffModal<?= $row['Id'] ?>">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST" enctype="multipart/form-data">
                      <div class="modal-header">
                        <h5>Edit Staff</h5>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="staff_id" value="<?= $row['Id'] ?>">
                        <input type="text" name="fname" value="<?= htmlspecialchars($row['FirstName']) ?>" class="form-control mb-2" required>
                        <input type="text" name="lname" value="<?= htmlspecialchars($row['LastName']) ?>" class="form-control mb-2" required>
                        <input type="email" name="email" value="<?= htmlspecialchars($row['Email']) ?>" class="form-control mb-2" required>
                        <input type="text" name="phone" value="<?= htmlspecialchars($row['PhoneNumber']) ?>" class="form-control mb-2" required>
                        <input type="text" name="skill" value="<?= htmlspecialchars($row['Skill']) ?>" class="form-control mb-2" required>
                        <input type="text" name="experience" value="<?= htmlspecialchars($row['Experience']) ?>" class="form-control mb-2" required>
                        <input type="file" name="photo" class="form-control mb-2">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_staff" class="btn btn-primary">Save Changes</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            <?php } ?>
          </tbody>
        </table>
        <!-- Pagination -->
        <nav>
          <ul class="pagination">
            <?php for ($i = 1; $i <= $totalStaffPages; $i++): ?>
              <li class="page-item <?= ($i == $staffPage) ? 'active' : '' ?>"><a class="page-link" href="?spage=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">&copy; <?= date("Y") ?> Fixzy Services. All Rights Reserved.</div>

  <!-- Add Staff Modal -->
  <div class="modal fade" id="addStaffModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" enctype="multipart/form-data">
          <div class="modal-header">
            <h5>Add Staff</h5>
          </div>
          <div class="modal-body">
            <input type="text" name="fname" class="form-control mb-2" placeholder="First Name" required>
            <input type="text" name="lname" class="form-control mb-2" placeholder="Last Name" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <input type="text" name="phone" class="form-control mb-2" placeholder="Phone" required>
            <input type="text" name="skill" class="form-control mb-2" placeholder="Skill" required>
            <input type="text" name="experience" class="form-control mb-2" placeholder="Experience" required>
            <input type="file" name="photo" class="form-control mb-2">
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="add_staff" class="btn btn-success">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="./script/js/bootstrap.bundle.js"></script>
  <script>
    // Hide loader after page loads
    window.onload = function() {
      document.getElementById("loader").style.display = "none";
    }
  </script>
</body>

</html>
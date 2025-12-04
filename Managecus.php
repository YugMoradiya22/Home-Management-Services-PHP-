<?php
include '../Phpmodules/Conres.php';

// ---- Handle Add Customer ----
if (isset($_POST['add_customer'])) {
  $fname   = trim($_POST['fname']);
  $lname   = trim($_POST['lname']);
  $email   = trim($_POST['email']);
  $phone   = trim($_POST['phone']);
  $passwordHashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $con->prepare("INSERT INTO rescustomer (FirstName, LastName, EMail, Phone, Password) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $fname, $lname, $email, $phone, $passwordHashed);
  $stmt->execute();
  $stmt->close();

  echo "<script>alert('Customer added successfully!'); window.location.href=window.location.href;</script>";
}

// ---- Handle Delete ----
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $stmt = $con->prepare("DELETE FROM rescustomer WHERE Id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
  echo "<script>alert('Customer deleted successfully!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
}

// ---- Handle Update ----
if (isset($_POST['update_customer'])) {
  $id     = intval($_POST['id']);
  $fname  = trim($_POST['fname']);
  $lname  = trim($_POST['lname']);
  $email  = trim($_POST['email']);
  $phone  = trim($_POST['phone']);

  $stmt = $con->prepare("UPDATE rescustomer SET FirstName=?, LastName=?, EMail=?, Phone=? WHERE Id=?");
  $stmt->bind_param("ssssi", $fname, $lname, $email, $phone, $id);
  $stmt->execute();
  $stmt->close();

  echo "<script>alert('Customer updated successfully!'); window.location='" . $_SERVER['REQUEST_URI'] . "';</script>";
}

// ================== PAGINATION ==================
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$totalCustomersData  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM rescustomer"));
$totalRecords        = $totalCustomersData['total'];
$totalPages          = ceil($totalRecords / $perPage);
$offset = ($page - 1) * $perPage;

$customerQuery  = "SELECT Id, FirstName, LastName, EMail, Phone 
                   FROM rescustomer 
                   ORDER BY Id ASC 
                   LIMIT $offset, $perPage";
$customerResult = mysqli_query($con, $customerQuery);

// ---- Stats (optimized with fewer queries) ----
list($totalCustomers) = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM rescustomer"));
list($totalStaff)     = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM restaff"));
list($totalServices)  = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM services"));
list($totalBookings)  = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM bookingservice"));
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
  <div class="header">📊 Customer Dashboard</div>

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

    <!-- Customer Data -->
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4><i class="bi bi-journal-text"></i> Customer Data </h4>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="bi bi-person-plus"></i> Add Customer</button>
        </div>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($customerResult)) { ?>
              <tr>
                <td><?= $row['Id'] ?></td>
                <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                <td><?= htmlspecialchars($row['EMail']) ?></td>
                <td><?= htmlspecialchars($row['Phone']) ?></td>
                <td>
                  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCustomerModal"
                    data-id="<?= $row['Id'] ?>" data-fname="<?= htmlspecialchars($row['FirstName']) ?>"
                    data-lname="<?= htmlspecialchars($row['LastName']) ?>" data-email="<?= htmlspecialchars($row['EMail']) ?>"
                    data-phone="<?= htmlspecialchars($row['Phone']) ?>"><i class="bi bi-pencil-square"></i> Edit</button>
                  <a href="?delete=<?= $row['Id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
        <!-- Pagination -->
        <!-- Pagination Links -->
        <nav>
          <ul class="pagination justify-content-start">
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
              <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php } ?>
          </ul>
        </nav>

      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">&copy; <?= date("Y") ?> Fixzy Services. All Rights Reserved.</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Loader
    window.onload = function() {
      document.getElementById("loader").style.display = "none";
    };
    // Fill edit modal
    var editModal = document.getElementById('editCustomerModal');
    editModal.addEventListener('show.bs.modal', function(event) {
      var button = event.relatedTarget;
      document.getElementById('edit-id').value = button.getAttribute('data-id');
      document.getElementById('edit-fname').value = button.getAttribute('data-fname');
      document.getElementById('edit-lname').value = button.getAttribute('data-lname');
      document.getElementById('edit-email').value = button.getAttribute('data-email');
      document.getElementById('edit-phone').value = button.getAttribute('data-phone');
    });
  </script>

  <!-- Add Customer Modal -->
  <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label>First Name</label><input type="text" name="fname" class="form-control" required></div>
          <div class="mb-3"><label>Last Name</label><input type="text" name="lname" class="form-control" required></div>
          <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" required></div>
          <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button></div>
      </form>
    </div>
  </div>

  <!-- Edit Customer Modal -->
  <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <input type="hidden" name="id" id="edit-id">
        <div class="modal-header">
          <h5 class="modal-title">Edit Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label>First Name</label><input type="text" name="fname" id="edit-fname" class="form-control" required></div>
          <div class="mb-3"><label>Last Name</label><input type="text" name="lname" id="edit-lname" class="form-control" required></div>
          <div class="mb-3"><label>Email</label><input type="email" name="email" id="edit-email" class="form-control" required></div>
          <div class="mb-3"><label>Phone</label><input type="text" name="phone" id="edit-phone" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_customer" class="btn btn-primary">Update Customer</button></div>
      </form>
    </div>
  </div>
</body>

</html>
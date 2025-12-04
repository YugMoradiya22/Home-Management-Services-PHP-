<?php
include '../Phpmodules/Conres.php';

// ---- Handle Add Booking Form Submission ----
if (isset($_POST['add_booking'])) {
    $customer    = trim($_POST['customer']);
    $email       = trim($_POST['email']);
    $phone       = trim($_POST['phone']);
    $service     = trim($_POST['service']);
    $address     = trim($_POST['address']);
    $date        = trim($_POST['date']);
    $time        = trim($_POST['time']);
    $description = trim($_POST['description']);

    // Validate booking time/day
    $dayOfWeek = date('w', strtotime($date)); // 0=Sunday, 6=Saturday
    $hourMinute = strtotime($time);

    if ($dayOfWeek == 0) {
        echo "<script>alert('Bookings are not allowed on Sunday.'); window.history.back();</script>";
        exit;
    } elseif ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
        if ($hourMinute < strtotime('08:00') || $hourMinute > strtotime('19:00')) {
            echo "<script>alert('Booking time on weekdays must be between 08:00 AM and 07:00 PM.'); window.history.back();</script>";
            exit;
        }
    } elseif ($dayOfWeek == 6) {
        if ($hourMinute < strtotime('09:00') || $hourMinute > strtotime('17:00')) {
            echo "<script>alert('Booking time on Saturday must be between 09:00 AM and 05:00 PM.'); window.history.back();</script>";
            exit;
        }
    }

    $stmt = $con->prepare("INSERT INTO bookingservice (FullName, Email, PhoneNumber, Service, Address, Date, Time, Description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $customer, $email, $phone, $service, $address, $date, $time, $description);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Booking added successfully!'); window.location.href=window.location.href;</script>";
}

// ---- Handle Delete ----
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $con->prepare("DELETE FROM bookingservice WHERE Id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Booking deleted successfully!'); window.location='" . $_SERVER['PHP_SELF'] . "';</script>";
}

// ================== PAGINATION ==================
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$totalBookingsQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM bookingservice");
$totalBookingsData  = mysqli_fetch_assoc($totalBookingsQuery);
$totalRecords       = $totalBookingsData['total'];
$totalPages         = ceil($totalRecords / $perPage);

$offset = ($page - 1) * $perPage;

$bookingQuery = "SELECT * FROM bookingservice ORDER BY Id DESC  LIMIT $offset, $perPage";
$bookingResult = mysqli_query($con, $bookingQuery);

// ---- Stats for cards ----
$totalCustomers = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM rescustomer"))['count'];
$totalStaff     = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM restaff"))['count'];
$totalServices  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM services"))['count'];
$totalBookings  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM bookingservice"))['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
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
    </style>
</head>

<body>
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

    <div class="header">📊 Booking Management</div>

    <div class="content">
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

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4><i class="bi bi-journal-text"></i> Booking Data </h4>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBookingModal"><i class="bi bi-plus-circle"></i> Add Booking</button>
                </div>

                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Service</th>
                            <th>Address</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($bookingResult)) { ?>
                            <tr>
                                <td><?= $row['ID'] ?></td>
                                <td><?= htmlspecialchars($row['FullName']) ?></td>
                                <td><?= htmlspecialchars($row['Email']) ?></td>
                                <td><?= htmlspecialchars($row['PhoneNumber']) ?></td>
                                <td><?= htmlspecialchars($row['Service']) ?></td>
                                <td><?= htmlspecialchars($row['Address']) ?></td>
                                <td><?= htmlspecialchars($row['Date']) ?></td>
                                <td><?= htmlspecialchars($row['Time']) ?></td>
                                <td><?= htmlspecialchars($row['Description']) ?></td>
                                <td>
                                    <a href="?delete=<?= $row['ID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

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

    <!-- Add Booking Modal -->
    <div class="modal fade" id="addBookingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Booking</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Customer Name</label><input type="text" name="customer" class="form-control" required></div>
                    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" required></div>
                    <div class="mb-3"><label>Service</label><input type="text" name="service" class="form-control" required></div>
                    <div class="mb-3"><label>Address</label><input type="text" name="address" class="form-control" required></div>
                    <div class="mb-3"><label>Date</label><input type="date" name="date" class="form-control" required></div>
                    <div class="mb-3"><label>Time</label><input type="time" name="time" class="form-control" required></div>
                    <div class="mb-3"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_booking" class="btn btn-primary">Add Booking</button>
                </div>
            </form>
        </div>
    </div>

    <script src="./script/js/bootstrap.bundle.js"></script>

</body>

</html>
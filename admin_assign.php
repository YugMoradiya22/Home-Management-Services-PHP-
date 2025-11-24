<?php
session_start();
include '../Phpmodules/Conres.php';

// ✅ Assign staff to booking (with restriction)
if (isset($_POST['assign'])) {
    $booking_id = $_POST['booking_id'];
    $staff_id   = $_POST['staff_id'];

    // Check if staff already has an active (Pending or Accepted) job
    $check = $con->prepare("SELECT COUNT(*) FROM bookingservice WHERE StaffID=? AND Status IN ('Pending', 'Accepted')");
    $check->bind_param("i", $staff_id);
    $check->execute();
    $check->bind_result($active_jobs);
    $check->fetch();
    $check->close();

    if ($active_jobs > 0) {
        $message = "⚠️ This staff member already has an active job. Please wait until it's completed before assigning a new one.";
    } else {
        // Assign the job
        $sql = "UPDATE bookingservice 
                SET StaffID=?, Assigned=1, Status='Pending' 
                WHERE ID=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ii", $staff_id, $booking_id);
        $stmt->execute();
        $message = "✅ Staff assigned successfully!";
    }
}

// ✅ Delete booking
if (isset($_POST['delete'])) {
    $booking_id = $_POST['booking_id'];
    $sql = "DELETE FROM bookingservice WHERE ID=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $message = "🗑️ Booking deleted successfully!";
}

// ✅ Fetch unassigned bookings
$bookings = $con->query("SELECT * FROM bookingservice WHERE StaffID IS NULL OR Assigned=0");

// ✅ Fetch assigned bookings with staff name
$assigned = $con->query("
    SELECT b.*, r.FirstName, r.LastName
    FROM bookingservice b
    LEFT JOIN restaff r ON b.StaffID = r.ID
    WHERE b.Assigned=1
    ORDER BY 
        CASE 
            WHEN b.Status = 'Pending' THEN 1
            WHEN b.Status = 'Accepted' THEN 2
            WHEN b.Status = 'Completed' THEN 3
            WHEN b.Status = 'Cancelled' THEN 4
            ELSE 5
        END ASC,
        b.Date DESC,
        b.Time DESC
");

// ✅ Fetch all staff
$staffs = $con->query("SELECT ID, FirstName, LastName, Skill FROM restaff ORDER BY FirstName ASC");
$staff_list = [];
while ($row = $staffs->fetch_assoc()) {
    $staff_list[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Assign Jobs</title>
    <link href="./script/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f9fafb;
            font-family: "Segoe UI", sans-serif;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100%;
            background: linear-gradient(180deg, #2563eb, #9333ea);
            color: white;
            transition: left 0.3s ease;
            z-index: 1000;
            padding-top: 20px;
        }

        .sidebar.active {
            left: 0;
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
            border-radius: 6px;
            margin: 5px 10px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .sidebar a.logout {
            position: absolute;
            bottom: 20px;
            width: 90%;
            text-align: center;
            background: #dc2626;
        }

        .header {
            background: #2563eb;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .header button {
            background: white;
            color: #2563eb;
            border: none;
            font-size: 20px;
            border-radius: 6px;
            padding: 5px 10px;
        }

        .page-header {
            background: linear-gradient(135deg, #2563eb, #9333ea);
            color: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 30px;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .card-header {
            border-radius: 16px 16px 0 0 !important;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .table th {
            background: #f1f5f9;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .table td {
            vertical-align: middle;
        }

        .badge {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 30px;
        }

        .badge.pending {
            background: #facc15;
            color: #374151;
        }

        .badge.completed {
            background: #22c55e;
            color: #fff;
        }

        .badge.accepted {
            background: #3b82f6;
            color: #fff;
        }

        .badge.cancelled {
            background: #ef4444;
            color: #fff;
        }

        .badge.default {
            background: #9ca3af;
            color: #fff;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #2563eb, #9333ea);
            border: none;
            color: #fff;
            border-radius: 8px;
            padding: 6px 14px;
            transition: 0.3s;
        }

        .btn-gradient:hover {
            filter: brightness(1.1);
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <h4><i class="bi bi-speedometer2"></i> Admin</h4>
        <a href="adminpanel.php"><i class="bi bi-house-door-fill"></i> Dashboard</a>
        <a href="Managecus.php"><i class="bi bi-people-fill"></i> Manage Customers</a>
        <a href="Managestaff.php"><i class="bi bi-person-workspace"></i> Manage Staff</a>
        <a href="Manaervice.php"><i class="bi bi-box-seam"></i> Manage Services</a>
        <a href="Manbooking.php"><i class="bi bi-calendar-check-fill"></i> Manage Bookings</a>
        <a href="admin_assign.php" class="active"><i class="bi bi-person-lines-fill"></i> Assign Jobs</a>
        <a href="../Homes.php" class="logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <div class="header">
        <button id="toggleSidebar"><i class="bi bi-list"></i></button>
        <h5 class="mb-0">📌 Job Assignment</h5>
    </div>

    <div class="content container py-4" id="content">
        <div class="page-header">
            <h2 class="fw-bold mb-1"><i class="bi bi-clipboard-check"></i> Admin – Assign Jobs</h2>
            <p class="mb-0">Manage and assign customer bookings to staff efficiently</p>
        </div>

        <?php if (!empty($message)) { ?>
            <div class="alert alert-info shadow-sm"><?= $message ?></div>
        <?php } ?>

        <!-- Unassigned Jobs -->
        <div class="card">
            <div class="card-header bg-white">
                <i class="bi bi-hourglass-split text-warning"></i> Unassigned Jobs
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Service</th>
                                <th>Address</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($bookings->num_rows > 0) {
                                while ($booking = $bookings->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= $booking['FullName'] ?></td>
                                        <td><?= $booking['PhoneNumber'] ?></td>
                                        <td><?= $booking['Service'] ?></td>
                                        <td><?= $booking['Address'] ?></td>
                                        <td><?= $booking['Date'] ?></td>
                                        <td><?= $booking['Time'] ?></td>
                                        <td>
                                            <form method="post" class="d-flex">
                                                <input type="hidden" name="booking_id" value="<?= $booking['ID'] ?>">
                                                <select name="staff_id" class="form-select form-select-sm me-2" required>
                                                    <option value="">-- Select Staff --</option>
                                                    <?php foreach ($staff_list as $s) { ?>
                                                        <option value="<?= $s['ID'] ?>"><?= $s['FirstName'] . ' ' . $s['LastName'] ?> (<?= $s['Skill'] ?>)</option>
                                                    <?php } ?>
                                                </select>
                                                <button type="submit" name="assign" class="btn btn-gradient btn-sm me-2">Assign</button>
                                                <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete this booking?');"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                            <?php }
                            } else {
                                echo "<tr><td colspan='7' class='text-center text-muted'>No unassigned jobs</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assigned Jobs -->
        <div class="card">
            <div class="card-header bg-white"><i class="bi bi-people-fill text-success"></i> Assigned Jobs</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Service</th>
                                <th>Address</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Staff</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($assigned->num_rows > 0) {
                                while ($a = $assigned->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= $a['FullName'] ?></td>
                                        <td><?= $a['PhoneNumber'] ?></td>
                                        <td><?= $a['Service'] ?></td>
                                        <td><?= $a['Address'] ?></td>
                                        <td><?= $a['Date'] ?></td>
                                        <td><?= $a['Time'] ?></td>
                                        <td><?= $a['FirstName'] ? "<b>" . $a['FirstName'] . " " . $a['LastName'] . "</b>" : "<span class='text-danger'>Not Assigned</span>" ?></td>
                                        <td><span class="badge <?= strtolower($a['Status']) ?>"><?= ucfirst($a['Status']) ?></span></td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?= $a['ID'] ?>">
                                                <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete this assigned booking?');"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                            <?php }
                            } else {
                                echo "<tr><td colspan='9' class='text-center text-muted'>No assigned jobs</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById("sidebar");
        const content = document.getElementById("content");
        document.getElementById("toggleSidebar").addEventListener("click", () => {
            sidebar.classList.toggle("active");
            content.classList.toggle("shifted");
        });
    </script>
</body>

</html>
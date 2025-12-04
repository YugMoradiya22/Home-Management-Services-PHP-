<?php
// admin_assign.php
session_start();
include '../Phpmodules/Conres.php';

// ================== HANDLE ASSIGNMENT ==================
if (isset($_POST['assign'])) {
    $booking_id = $_POST['booking_id'];
    $staff_id   = $_POST['staff_id'];

    // 1. Check if staff is already busy
    $check = $con->prepare("SELECT COUNT(*) FROM bookingservice WHERE StaffID=? AND Status='Accepted'");
    $check->bind_param("i", $staff_id);
    $check->execute();
    $check->bind_result($isBusy);
    $check->fetch();
    $check->close();

    if ($isBusy > 0) {
        $msg = "⚠️ This staff member is currently working on another job (Status: Accepted).";
        $msg_type = "warning";
    } else {
        // Check/Generate OTP logic
        $otpCheck = $con->prepare("SELECT OTP FROM bookingservice WHERE ID=?");
        $otpCheck->bind_param("i", $booking_id);
        $otpCheck->execute();
        $otpRes = $otpCheck->get_result();
        $otpRow = $otpRes->fetch_assoc();
        $otpCheck->close();

        if (empty($otpRow['OTP'])) {
            $new_otp = rand(1000, 9999);
            $sql = "UPDATE bookingservice SET StaffID=?, Assigned=1, Status='Pending', OTP=? WHERE ID=?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("isi", $staff_id, $new_otp, $booking_id);
        } else {
            $sql = "UPDATE bookingservice SET StaffID=?, Assigned=1, Status='Pending' WHERE ID=?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ii", $staff_id, $booking_id);
        }
        
        if ($stmt->execute()) {
            $msg = "✅ Staff assigned successfully!";
            $msg_type = "success";
        } else {
            $msg = "❌ Database error: " . $stmt->error;
            $msg_type = "danger";
        }
        $stmt->close();
    }
}

// ================== FETCH DATA ==================
// 1. Get Staff List
$staff_sql = "SELECT s.ID, s.FirstName, s.LastName, s.Skill, 
              (SELECT COUNT(*) FROM bookingservice b WHERE b.StaffID = s.ID AND b.Status = 'Accepted') as ActiveJobs
              FROM restaff s ORDER BY s.FirstName ASC";
$staffs = $con->query($staff_sql);
$staff_list = [];
while ($row = $staffs->fetch_assoc()) {
    $staff_list[] = $row;
}

// 2. Get All Bookings & Separate them
$sql_bookings = "SELECT b.*, r.FirstName as StaffFname, r.LastName as StaffLname 
                 FROM bookingservice b 
                 LEFT JOIN restaff r ON b.StaffID = r.ID 
                 WHERE b.Status != 'Completed' AND b.Status != 'Cancelled'
                 ORDER BY b.Date DESC, b.Time ASC";
$bookings_result = $con->query($sql_bookings);

$unassigned_jobs = [];
$assigned_jobs = [];

while($row = $bookings_result->fetch_assoc()) {
    if (empty($row['StaffID'])) {
        $unassigned_jobs[] = $row;
    } else {
        $assigned_jobs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Jobs - Admin</title>
    <link href="./script/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: sans-serif; }
        .sidebar { height: 100vh; background: linear-gradient(180deg, #0d47a1, #1976d2); color: white; padding-top: 20px; position: fixed; width: 240px; z-index: 1000; }
        .sidebar h4 { text-align: center; font-weight: bold; margin-bottom: 30px; }
        .sidebar a { display: block; padding: 12px 20px; color: white; text-decoration: none; font-size: 16px; border-radius: 8px; margin: 6px 10px; transition: 0.3s; }
        .sidebar a:hover { background: rgba(255, 255, 255, 0.2); }
        .sidebar a.logout-btn { position: absolute; bottom: 20px; width: 90%; background: #d32f2f; }
        .main-content { margin-left: 240px; }
        .header { background: #0d47a1; color: white; padding: 15px; font-size: 20px; font-weight: bold; }
        .content-body { padding: 25px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .table thead { background: #1976d2; color: white; }
        .table th, .table td { vertical-align: middle; }
        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; }
        .status-Pending { background: #ffc107; color: #000; }
        .status-Accepted { background: #0d6efd; color: #fff; }
        .status-Assigned { background: #17a2b8; color: #fff; }
        .busy-staff { color: #dc3545; font-weight: bold; background: #ffe6e6; }
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
        <a href="admin_assign.php" style="background: rgba(255,255,255,0.2);"><i class="bi bi-person-check-fill"></i> Assign Role</a>
        <a href="../Homes.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <div class="main-content">
        <div class="header">📌 Job Assignment Dashboard</div>

        <div class="content-body">
            
            <?php if (isset($msg)) { ?>
                <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show">
                    <?= $msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-circle-fill"></i> Unassigned Jobs (Pending Action)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer & Service</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($unassigned_jobs)): ?>
                                    <?php foreach($unassigned_jobs as $row): ?>
                                    <tr>
                                        <td>#<?= $row['ID'] ?></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($row['Service']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($row['FullName']) ?></div>
                                        </td>
                                        <td>
                                            <div><?= date('M d', strtotime($row['Date'])) ?></div>
                                            <div class="small text-primary"><?= date('h:i A', strtotime($row['Time'])) ?></div>
                                        </td>
                                        <td><small><?= htmlspecialchars($row['Address']) ?></small></td>
                                        <td width="350">
                                            <form method="POST" class="d-flex gap-2">
                                                <input type="hidden" name="booking_id" value="<?= $row['ID'] ?>">
                                                <select name="staff_id" class="form-select form-select-sm" required>
                                                    <option value="">Select Staff...</option>
                                                    <?php foreach ($staff_list as $s): 
                                                        $busyLabel = ($s['ActiveJobs'] > 0) ? " (Busy)" : " (Available)";
                                                        $optionClass = ($s['ActiveJobs'] > 0) ? "busy-staff" : "";
                                                    ?>
                                                        <option value="<?= $s['ID'] ?>" class="<?= $optionClass ?>">
                                                            <?= htmlspecialchars($s['FirstName'] . ' ' . $s['LastName']) ?> - <?= $s['Skill'] ?><?= $busyLabel ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" name="assign" class="btn btn-sm btn-danger">Assign</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">All jobs are assigned! 🎉</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-success mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle-fill"></i> Assigned Jobs (Work in Progress)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer & Service</th>
                                    <th>Date & Time</th>
                                    <th>Assigned Staff</th>
                                    <th>Status</th>
                                    <th>Change Staff</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($assigned_jobs)): ?>
                                    <?php foreach($assigned_jobs as $row): ?>
                                    <tr>
                                        <td>#<?= $row['ID'] ?></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($row['Service']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($row['FullName']) ?></div>
                                        </td>
                                        <td>
                                            <div><?= date('M d', strtotime($row['Date'])) ?></div>
                                            <div class="small text-primary"><?= date('h:i A', strtotime($row['Time'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-success">
                                                <i class="bi bi-person-badge"></i> 
                                                <?= htmlspecialchars($row['StaffFname'] . ' ' . $row['StaffLname']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-status status-<?= $row['Status'] ?>">
                                                <?= $row['Status'] ?>
                                            </span>
                                        </td>
                                        <td width="350">
                                            <form method="POST" class="d-flex gap-2">
                                                <input type="hidden" name="booking_id" value="<?= $row['ID'] ?>">
                                                <select name="staff_id" class="form-select form-select-sm" required>
                                                    <option value="">Re-assign to...</option>
                                                    <?php foreach ($staff_list as $s): 
                                                        $busyLabel = ($s['ActiveJobs'] > 0) ? " (Busy)" : " (Available)";
                                                        $optionClass = ($s['ActiveJobs'] > 0) ? "busy-staff" : "";
                                                    ?>
                                                        <option value="<?= $s['ID'] ?>" class="<?= $optionClass ?>" <?= ($row['StaffID'] == $s['ID']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($s['FirstName'] . ' ' . $s['LastName']) ?> - <?= $s['Skill'] ?><?= $busyLabel ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" name="assign" class="btn btn-sm btn-primary">Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">No jobs assigned yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="./script/js/bootstrap.bundle.js"></script>
</body>
</html>
<?php
session_start();
include './Phpmodules/Conres.php';

// ================= Accept Job =================
if (isset($_POST['accept'])) {
    $job_id = $_POST['job_id'];

    // Get staff assigned to job
    $stmt = $con->prepare("SELECT StaffID FROM bookingservice WHERE ID=?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $stmt->bind_result($staff_id);
    $stmt->fetch();
    $stmt->close();

    // Check if staff already has an accepted job
    $check = $con->prepare("SELECT COUNT(*) FROM bookingservice WHERE StaffID=? AND Status='Accepted'");
    $check->bind_param("i", $staff_id);
    $check->execute();
    $check->bind_result($active_jobs);
    $check->fetch();
    $check->close();

    if ($active_jobs > 0) {
        $message = "You already have an active job! Complete it before accepting another.";
    } else {
        $stmt = $con->prepare("UPDATE bookingservice SET Status='Accepted' WHERE ID=?");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $message = "Job marked as accepted!";
    }
}

// ================= Complete Job =================
if (isset($_POST['complete'])) {
    $job_id = $_POST['job_id'];
    $stmt = $con->prepare("UPDATE bookingservice SET Status='Completed' WHERE ID=?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $message = "Job marked as completed!";
}

// ================= Fetch Jobs =================
$sql = "SELECT 
            b.ID, b.FullName, b.PhoneNumber, b.Service, b.Address, b.Date, b.Time, b.Status,
            CONCAT(r.FirstName, ' ', r.LastName) AS StaffName, r.Skill, b.StaffID
        FROM bookingservice b
        LEFT JOIN restaff r ON b.StaffID = r.ID
        ORDER BY b.Date DESC, b.Time DESC";
$jobs = $con->query($sql);

// ================= Fetch Stats =================
$totalJobs = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM bookingservice"))[0];
$pendingJobs = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM bookingservice WHERE Status='Pending'"))[0];
$completedJobs = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM bookingservice WHERE Status='Completed'"))[0];
$totalStaff = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM restaff"))[0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Dashboard</title>
    <link href="./script/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #2563eb, #9333ea);
            padding: 16px 32px;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .topbar h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .topbar a {
            color: #fff;
            text-decoration: none;
            background: #ef4444;
            padding: 6px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: 0.3s;
        }

        .topbar a:hover {
            background: #dc2626;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin: 24px 32px;
        }

        .card-stat {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease;
        }

        .card-stat:hover {
            transform: translateY(-4px);
        }

        .card-stat .icon {
            font-size: 2rem;
            color: #fff;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .icon.jobs {
            background: #3b82f6;
        }

        .icon.pending {
            background: #facc15;
            color: #374151;
        }

        .icon.completed {
            background: #22c55e;
        }

        .icon.staff {
            background: #0ea5e9;
        }

        .table-responsive {
            margin: 24px 32px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
            background: #fff;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.95rem;
        }

        .table th,
        .table td {
            padding: 12px 16px;
            text-align: left;
        }

        .table thead th {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .table tbody tr:hover {
            background-color: #e0f2fe;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-block;
            text-align: center;
            min-width: 70px;
        }

        .badge.pending {
            background: #facc15;
            color: #374151;
        }

        .badge.accepted {
            background: #3b82f6;
            color: #fff;
        }

        .badge.completed {
            background: #22c55e;
            color: #fff;
        }

        .btn-action {
            border: none;
            border-radius: 8px;
            padding: 4px 12px;
            font-size: 0.85rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
        }

        .btn-accept {
            background: #3b82f6;
            color: #fff;
        }

        .btn-complete {
            background: #16a34a;
            color: #fff;
        }

        .btn-accept:hover {
            background: #2563eb;
        }

        .btn-complete:hover {
            background: #15803d;
        }

        .text-muted {
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="topbar">
        <h2><i class="bi bi-clipboard-check"></i> Job Dashboard</h2>
        <a href="Homes.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <div class="stats">
        <div class="card-stat">
            <div class="icon jobs"><i class="bi bi-journal-check"></i></div>
            <div>
                <span class="text-muted">Total Jobs</span>
                <h4><?= $totalJobs ?></h4>
            </div>
        </div>
        <div class="card-stat">
            <div class="icon pending"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <span class="text-muted">Pending Jobs</span>
                <h4><?= $pendingJobs ?></h4>
            </div>
        </div>
        <div class="card-stat">
            <div class="icon completed"><i class="bi bi-check2-circle"></i></div>
            <div>
                <span class="text-muted">Completed Jobs</span>
                <h4><?= $completedJobs ?></h4>
            </div>
        </div>
        <div class="card-stat">
            <div class="icon staff"><i class="bi bi-person-lines-fill"></i></div>
            <div>
                <span class="text-muted">Total Staff</span>
                <h4><?= $totalStaff ?></h4>
            </div>
        </div>
    </div>

    <?php if (!empty($message)) { ?>
        <div class="alert alert-info" style="margin:0 32px;"><?= htmlspecialchars($message) ?></div>
    <?php } ?>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Service</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Staff</th>
                    <th>Skill</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if any staff has an active accepted job
                $activeStaff = [];
                $res = $con->query("SELECT StaffID FROM bookingservice WHERE Status='Accepted'");
                while ($r = $res->fetch_assoc()) {
                    $activeStaff[] = $r['StaffID'];
                }

                while ($job = $jobs->fetch_assoc()) {
                    $staffBusy = in_array($job['StaffID'], $activeStaff);
                ?>
                    <tr>
                        <td><?= htmlspecialchars($job['FullName']) ?></td>
                        <td><?= htmlspecialchars($job['PhoneNumber']) ?></td>
                        <td><?= htmlspecialchars($job['Service']) ?></td>
                        <td><?= htmlspecialchars($job['Address']) ?></td>
                        <td><?= htmlspecialchars($job['Date']) ?></td>
                        <td><?= htmlspecialchars($job['Time']) ?></td>
                        <td><?= $job['StaffName'] ?: 'Not Assigned' ?></td>
                        <td><?= $job['Skill'] ?: '-' ?></td>
                        <td><span class="badge <?= strtolower($job['Status']) ?>"><?= ucfirst($job['Status']) ?></span></td>
                        <td>
                            <?php if ($job['Status'] == 'Pending' && !empty($job['StaffName']) && !$staffBusy) { ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="job_id" value="<?= $job['ID'] ?>">
                                    <button type="submit" name="accept" class="btn-action btn-accept"><i class="bi bi-check2"></i> Accept</button>
                                </form>
                            <?php } elseif ($job['Status'] == 'Accepted' && !empty($job['StaffName'])) { ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="job_id" value="<?= $job['ID'] ?>">
                                    <button type="submit" name="complete" class="btn-action btn-complete"><i class="bi bi-check-circle"></i> Complete</button>
                                </form>
                            <?php } else { ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script src="./script/js/bootstrap.bundle.min.js"></script>
</body>
</html>

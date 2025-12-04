<?php
session_start();
include './Phpmodules/Conres.php';

// 1. Check Staff Login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'staff') {
    header("Location: Loginforstaff.php");
    exit;
}

$staff_id = $_SESSION['staff_id'];
$message = "";
$msg_type = "";

// 2. FETCH STAFF PROFILE
$staffQuery = $con->prepare("SELECT FirstName, Photo FROM restaff WHERE ID = ?");
$staffQuery->bind_param("i", $staff_id);
$staffQuery->execute();
$staffRes = $staffQuery->get_result();
$staffData = $staffRes->fetch_assoc();

// Navbar variables
$staffName = $staffData['FirstName'] ?? "Staff";
$staffPhoto = !empty($staffData['Photo']) ? "uploads/" . $staffData['Photo'] : "";

// ================= ACTION: ACCEPT JOB =================
if (isset($_POST['accept'])) {
    $job_id = $_POST['job_id'];

    $check = $con->prepare("SELECT COUNT(*) FROM bookingservice WHERE StaffID=? AND Status IN ('Accepted', 'In Progress')");
    $check->bind_param("i", $staff_id);
    $check->execute();
    $check->bind_result($active_jobs);
    $check->fetch();
    $check->close();

    if ($active_jobs > 0) {
        $message = "⚠️ Please complete your active job first.";
        $msg_type = "warning";
    } else {
        $stmt = $con->prepare("UPDATE bookingservice SET Status='Accepted' WHERE ID=?");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $message = "✅ Job Accepted!";
        $msg_type = "success";
    }
}

// ================= ACTION: START WORK (VERIFY OTP) =================
if (isset($_POST['verify_otp'])) {
    $job_id = $_POST['job_id'];
    $entered_otp = trim($_POST['otp_code']);

    $stmt = $con->prepare("SELECT OTP FROM bookingservice WHERE ID=?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['OTP'] == $entered_otp) {
        $update = $con->prepare("UPDATE bookingservice SET Status='In Progress' WHERE ID=?");
        $update->bind_param("i", $job_id);
        $update->execute();
        $message = "🚀 Work Started!";
        $msg_type = "success";
    } else {
        $message = "❌ Incorrect OTP.";
        $msg_type = "danger";
    }
}

// ================= ACTION: COMPLETE JOB =================
if (isset($_POST['complete'])) {
    $job_id = $_POST['job_id'];
    $stmt = $con->prepare("UPDATE bookingservice SET Status='Completed' WHERE ID=?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $message = "🎉 Job Completed!";
    $msg_type = "success";
}

// ================= FETCH JOBS =================
$sql = "SELECT * FROM bookingservice 
        WHERE StaffID = ? 
        AND Status != 'Cancelled' 
        ORDER BY 
        CASE 
            WHEN Status = 'In Progress' THEN 1 
            WHEN Status = 'Accepted' THEN 2 
            WHEN Status = 'Pending' THEN 3 
            ELSE 4 
        END, Date DESC";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

// Stats
$stmt_stats = $con->prepare("SELECT COUNT(*) FROM bookingservice WHERE StaffID=? AND Status='Completed'");
$stmt_stats->bind_param("i", $staff_id);
$stmt_stats->execute();
$stmt_stats->bind_result($total_completed);
$stmt_stats->fetch();
$stmt_stats->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Dashboard</title>
    <link href="./script/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4F46E5;
            --bg-body: #F3F4F6;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; padding-bottom: 80px; }
        
        /* Navbar */
        .navbar { background: white; padding: 12px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.03); position: sticky; top: 0; z-index: 1000; }
        .brand-logo { font-weight: 800; font-size: 1.4rem; color: var(--primary); text-decoration: none; }
        .profile-pill { background: #f3f4f6; padding: 5px 5px 5px 15px; border-radius: 50px; display: flex; align-items: center; gap: 10px; }
        .avatar-img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
        .avatar-placeholder { width: 38px; height: 38px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        
        /* Dashboard Header */
        .header-card {
            background: linear-gradient(135deg, #4F46E5 0%, #8B5CF6 100%);
            border-radius: 16px;
            padding: 25px;
            color: white;
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2);
            margin-bottom: 30px;
        }

        /* --- NEW PRO JOB CARD DESIGN --- */
        .job-card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            border-left: 5px solid transparent; /* Colored border logic handled inline */
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .job-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }

        .card-body { padding: 20px; flex: 1; }
        
        .service-badge { font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase; font-weight: 700; color: #6B7280; background: #F3F4F6; padding: 4px 10px; border-radius: 6px; }
        
        .customer-name { font-size: 1.1rem; font-weight: 700; color: #1F2937; margin-bottom: 2px; }
        .job-address { font-size: 0.9rem; color: #6B7280; display: flex; align-items: start; gap: 6px; line-height: 1.4; margin-bottom: 15px; }
        
        .time-box { background: #eff6ff; border-radius: 10px; padding: 12px; display: flex; align-items: center; gap: 12px; margin-bottom: 15px; border: 1px solid #dbeafe; }
        .time-box i { font-size: 1.2rem; color: #2563EB; }
        
        .action-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
        .btn-icon-label { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; border-radius: 8px; font-size: 0.9rem; font-weight: 500; text-decoration: none; border: 1px solid #e5e7eb; color: #374151; transition: 0.2s; }
        .btn-icon-label:hover { background: #f9fafb; border-color: #d1d5db; color: #111827; }

        .main-action-btn { width: 100%; padding: 12px; border-radius: 10px; font-weight: 600; border: none; display: flex; align-items: center; justify-content: center; gap: 8px; }
        
        /* Status Colors */
        .border-Pending { border-left-color: #F59E0B !important; }
        .border-Accepted { border-left-color: #3B82F6 !important; }
        .border-In { border-left-color: #8B5CF6 !important; } /* In Progress */
        .border-Completed { border-left-color: #10B981 !important; }

        .badge-status { padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .bg-Pending { background: #FEF3C7; color: #D97706; }
        .bg-Accepted { background: #DBEAFE; color: #2563EB; }
        .bg-In { background: #EDE9FE; color: #7C3AED; }
        .bg-Completed { background: #D1FAE5; color: #059669; }

        .otp-input { font-size: 2rem; letter-spacing: 8px; text-align: center; border: 2px solid #e5e7eb; border-radius: 12px; height: 60px; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="brand-logo" href="#"><i class="bi bi-tools"></i> Fixzy<span class="fw-light text-dark">Staff</span></a>
            <div class="d-flex align-items-center gap-3">
                <div class="profile-pill">
                    <div class="d-none d-md-block text-end" style="line-height:1.1;">
                        <small class="d-block fw-bold text-dark"><?php echo htmlspecialchars($staffName); ?></small>
                        <span style="font-size:0.7rem; color:#6b7280;">Staff</span>
                    </div>
                    <?php if (!empty($staffPhoto)): ?>
                        <img src="<?php echo htmlspecialchars($staffPhoto); ?>" class="avatar-img">
                    <?php else: ?>
                        <div class="avatar-placeholder"><?php echo strtoupper(substr($staffName, 0, 1)); ?></div>
                    <?php endif; ?>
                </div>
                <a href="logout.php" class="text-danger fs-5"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <div class="header-card d-flex align-items-center justify-content-between">
            <div>
                <h3 class="fw-bold mb-1">My Tasks</h3>
                <p class="mb-0 opacity-75">Manage your service requests</p>
            </div>
            <div class="text-center bg-white bg-opacity-20 rounded-3 p-3">
                <h2 class="fw-bold m-0"><?php echo $total_completed; ?></h2>
                <small class="opacity-75">Completed</small>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?> shadow-sm rounded-3 border-0 mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                     $statusKey = ($row['Status'] == 'In Progress') ? 'In' : $row['Status'];
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="job-card border-<?php echo $statusKey; ?>">
                        <div class="card-body d-flex flex-column">
                            
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="service-badge"><?php echo htmlspecialchars($row['Service']); ?></span>
                                <span class="badge-status bg-<?php echo $statusKey; ?>">
                                    <?php echo $row['Status']; ?>
                                </span>
                            </div>

                            <h5 class="customer-name"><?php echo htmlspecialchars($row['FullName']); ?></h5>
                            <div class="job-address">
                                <i class="bi bi-geo-alt-fill text-muted mt-1"></i>
                                <span><?php echo htmlspecialchars($row['Address']); ?></span>
                            </div>

                            <div class="time-box mt-auto">
                                <i class="bi bi-calendar-check"></i>
                                <div style="line-height: 1.2;">
                                    <div class="fw-bold text-dark"><?php echo date('D, M d', strtotime($row['Date'])); ?></div>
                                    <small class="text-primary fw-bold"><?php echo date('h:i A', strtotime($row['Time'])); ?></small>
                                </div>
                            </div>
                            
                            <div class="action-row">
                                <a href="tel:<?php echo htmlspecialchars($row['PhoneNumber']); ?>" class="btn-icon-label">
                                    <i class="bi bi-telephone-fill text-success"></i> Call
                                </a>
                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($row['Address']); ?>" target="_blank" class="btn-icon-label">
                                    <i class="bi bi-map-fill text-primary"></i> Map
                                </a>
                            </div>

                            <?php if ($row['Status'] == 'Pending'): ?>
                                <form method="POST" class="w-100">
                                    <input type="hidden" name="job_id" value="<?php echo $row['ID']; ?>">
                                    <button type="submit" name="accept" class="main-action-btn btn btn-primary text-white shadow-sm">
                                        Accept Job
                                    </button>
                                </form>

                            <?php elseif ($row['Status'] == 'Accepted'): ?>
                                <button type="button" class="main-action-btn btn btn-warning text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#otpModal<?php echo $row['ID']; ?>">
                                    <i class="bi bi-shield-lock-fill"></i> Verify & Start
                                </button>
                                
                                <div class="modal fade" id="otpModal<?php echo $row['ID']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <form method="POST">
                                                <div class="modal-body p-4 text-center">
                                                    <div class="mb-3 text-primary"><i class="bi bi-shield-check" style="font-size: 3rem;"></i></div>
                                                    <h5>Start Service</h5>
                                                    <p class="text-muted small">Enter 4-digit OTP from customer</p>
                                                    <input type="hidden" name="job_id" value="<?php echo $row['ID']; ?>">
                                                    <input type="tel" name="otp_code" class="form-control otp-input mb-3" maxlength="4" placeholder="••••" required autocomplete="off">
                                                    <button type="submit" name="verify_otp" class="btn btn-primary w-100 fw-bold py-2">Verify</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php elseif ($row['Status'] == 'In Progress'): ?>
                                <form method="POST" class="w-100">
                                    <input type="hidden" name="job_id" value="<?php echo $row['ID']; ?>">
                                    <button type="submit" name="complete" class="main-action-btn btn btn-success text-white shadow-sm">
                                        <i class="bi bi-check-circle-fill"></i> Complete Job
                                    </button>
                                </form>

                            <?php else: ?>
                                <button disabled class="main-action-btn btn btn-light text-success border">
                                    <i class="bi bi-check-all"></i> Finished
                                </button>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="bg-white rounded-4 shadow-sm p-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="80" class="mb-3 opacity-50">
                        <h5>No Tasks Assigned</h5>
                        <p class="text-muted small">Relax! You have no pending work.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="./script/js/bootstrap.bundle.min.js"></script>
</body>
</html>
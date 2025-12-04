<?php
// my_bookings.php
session_start();
include './Phpmodules/Conres.php';

// Check if user is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: Registerforcustomer.php?redirect=my_bookings.php");
    exit;
}

$userEmail = $_SESSION['user_email'];

// --- PAGINATION LOGIC ---
$limit = 9; // Number of bookings per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// 1. Get Total Records
$count_query = "SELECT COUNT(ID) AS total FROM bookingservice WHERE Email = ?";
$stmt_count = $con->prepare($count_query);
$stmt_count->bind_param("s", $userEmail);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$row_count = $count_result->fetch_assoc();
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $limit);

// 2. Get Records for Current Page
$query = "SELECT * FROM bookingservice WHERE Email = ? ORDER BY Date DESC, Time DESC LIMIT ?, ?";
$stmt = $con->prepare($query);
$stmt->bind_param("sii", $userEmail, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Fixzy</title>
    
    <link rel="stylesheet" href="../script/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* 🎨 PREMIUM COLOR PALETTE */
            --primary-gradient: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            --secondary-bg: #F3F4F6;
            --card-bg: #ffffff;
            --text-dark: #1F2937;
            --text-muted: #6B7280;
            
            /* Status Colors */
            --status-pending-bg: #FFF7ED;
            --status-pending-text: #C2410C;
            
            --status-accepted-bg: #EFF6FF;
            --status-accepted-text: #2563EB;
            
            --status-completed-bg: #ECFDF5;
            --status-completed-text: #059669;
        }

        body {
            background-color: var(--secondary-bg);
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- Navbar --- */
        .navbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 15px 0;
        }
        .brand-text {
            font-weight: 800;
            color: #4F46E5;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
        }

        /* --- Hero Section --- */
        .dashboard-header {
            background: var(--primary-gradient);
            padding: 3.5rem 0 6rem;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.2);
        }
        
        .dashboard-header::before, .dashboard-header::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }
        .dashboard-header::before { width: 300px; height: 300px; top: -100px; left: -50px; }
        .dashboard-header::after { width: 500px; height: 500px; bottom: -200px; right: -100px; }

        /* --- Cards Layout --- */
        .bookings-container {
            margin-top: -4rem; /* Floating effect */
            flex: 1;
            padding-bottom: 40px;
        }

        .booking-card {
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .booking-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -10px rgba(79, 70, 229, 0.15);
        }

        /* Left Border Status Indicator */
        .booking-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 6px;
            background: #ccc;
        }
        .booking-card.status-Pending::before { background: #F97316; }
        .booking-card.status-Accepted::before { background: #3B82F6; }
        .booking-card.status-Completed::before { background: #10B981; }

        .card-body { padding: 1.8rem; flex-grow: 1; margin-left: 8px; }
        
        .service-name { 
            font-size: 1.25rem; 
            font-weight: 700; 
            margin-bottom: 0.8rem; 
            color: #111827; 
        }

        /* Badge Pills */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }
        .status-Pending .status-pill { background: var(--status-pending-bg); color: var(--status-pending-text); }
        .status-Accepted .status-pill { background: var(--status-accepted-bg); color: var(--status-accepted-text); }
        .status-Completed .status-pill { background: var(--status-completed-bg); color: var(--status-completed-text); }

        /* Icon Info Rows */
        .meta-row {
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--text-muted);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .meta-icon-box {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F3F4F6;
            border-radius: 10px;
            color: #4F46E5;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Footer Button */
        .card-footer-custom {
            padding: 1.2rem 1.8rem;
            background: #fff;
            margin-left: 6px;
        }
        
        .btn-details {
            width: 100%;
            background: #fff;
            color: #4F46E5;
            font-weight: 600;
            border: 2px solid #E0E7FF;
            padding: 10px;
            border-radius: 12px;
            transition: 0.2s;
        }
        .btn-details:hover {
            background: #4F46E5;
            color: white;
            border-color: #4F46E5;
        }

        /* Pagination */
        .pagination .page-link {
            border: none; color: #6B7280; background: white; border-radius: 10px; margin: 0 4px;
            font-weight: 600; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .pagination .page-link:hover { background: #F3F4F6; color: #4F46E5; }
        .pagination .active .page-link { background: #4F46E5; color: white; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3); }
        .pagination .disabled .page-link { opacity: 0.5; cursor: not-allowed; }

        /* Empty State */
        .empty-box {
            background: white; border-radius: 20px; padding: 4rem 2rem;
            text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand brand-text" href="Homes.php">Fixzy.</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="Homes.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="service.php">Services</a></li>
                    <li class="nav-item ms-3">
                        <a href="logout.php" class="btn btn-outline-danger btn-sm px-4" style="border-radius: 20px;">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="dashboard-header">
        <div class="container">
            <h1 class="fw-bold mb-2 display-5">My Bookings</h1>
            <p class="opacity-90 fs-5">Track your service history and expert details</p>
        </div>
    </header>

    <div class="container bookings-container">
        <?php if ($total_records > 0): ?>
            <div class="row g-4">
                <?php while ($row = $result->fetch_assoc()): 
                    $status = ucfirst($row['Status']); 
                    $statusIcon = 'bi-hourglass-split';
                    if($status == 'Accepted') $statusIcon = 'bi-check-circle-fill';
                    if($status == 'Completed') $statusIcon = 'bi-trophy-fill';
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="booking-card status-<?php echo $status; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="status-pill">
                                        <i class="bi <?php echo $statusIcon; ?>"></i> 
                                        <span><?php echo $status; ?></span>
                                    </div>
                                </div>

                                <h3 class="service-name"><?php echo htmlspecialchars($row['Service']); ?></h3>

                                <div class="mt-4">
                                    <div class="meta-row">
                                        <div class="meta-icon-box"><i class="bi bi-calendar-week"></i></div>
                                        <div>
                                            <span class="d-block small text-muted">Date & Time</span>
                                            <span class="fw-bold text-dark">
                                                <?php echo date('M d, Y', strtotime($row['Date'])); ?> • 
                                                <?php echo date('h:i A', strtotime($row['Time'])); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="meta-row">
                                        <div class="meta-icon-box"><i class="bi bi-geo-alt"></i></div>
                                        <div>
                                            <span class="d-block small text-muted">Location</span>
                                            <span class="text-dark" style="font-size:0.9rem; line-height: 1.3;">
                                                <?php echo substr($row['Address'], 0, 30) . (strlen($row['Address'])>30 ? '...' : ''); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer-custom">
                                <a href="booking_details.php?id=<?php echo $row['ID']; ?>" class="btn btn-details">
                                    View Full Details <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="<?php if($page > 1) echo "?page=".($page - 1); ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                        <a class="page-link" href="<?php if($page < $total_pages) echo "?page=".($page + 1); ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="empty-box">
                        <div class="mb-4">
                            <i class="bi bi-calendar-plus text-muted" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="fw-bold text-dark">No upcoming bookings</h3>
                        <p class="text-muted mb-4">It looks quiet here. Ready to schedule your first home service?</p>
                        <a href="service.php" class="btn btn-primary btn-lg px-5 shadow-sm" style="background: #4F46E5; border:none; border-radius: 50px;">
                            Explore Services
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="../script/js/bootstrap.bundle.min.js"></script>
</body>
</html>
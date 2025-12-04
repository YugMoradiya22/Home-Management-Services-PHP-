<?php
// booking_details.php
session_start();
include './Phpmodules/Conres.php';

// 1. Check Login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: Registerforcustomer.php");
    exit;
}

// 2. Check if ID is present
if (!isset($_GET['id'])) {
    header("Location: my_bookings.php");
    exit;
}

$bookingId = $_GET['id'];
$userEmail = $_SESSION['user_email'];

// ================= HANDLE RATING SUBMISSION =================
if (isset($_POST['submit_feedback'])) {
    $rating = intval($_POST['rating']);
    $review = trim($_POST['review']);
    
    if ($rating > 0 && $rating <= 5) {
        $updateSql = "UPDATE bookingservice SET Rating=?, Review=? WHERE ID=? AND Email=?";
        $stmt = $con->prepare($updateSql);
        $stmt->bind_param("isis", $rating, $review, $bookingId, $userEmail);
        
        if ($stmt->execute()) {
            echo "<script>alert('Thank you for your feedback! ⭐'); window.location.href='booking_details.php?id=$bookingId';</script>";
            exit;
        } else {
            echo "<script>alert('Error saving feedback.');</script>";
        }
    }
}

// 3. FETCH BOOKING & STAFF DETAILS
$sql = "SELECT b.*, 
               r.FirstName AS StaffFname, 
               r.LastName AS StaffLname, 
               r.PhoneNumber AS StaffPhone, 
               r.Photo AS StaffPhoto, 
               r.Skill AS StaffSkill
        FROM bookingservice b
        LEFT JOIN restaff r ON b.StaffID = r.ID
        WHERE b.ID = ? AND b.Email = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("is", $bookingId, $userEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Booking not found!'); window.location='my_bookings.php';</script>";
    exit;
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Fixzy</title>
    
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

        body { background-color: var(--secondary-bg); font-family: 'Poppins', sans-serif; color: var(--text-dark); min-height: 100vh; }

        /* Navbar */
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 15px 0; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .brand-text { font-weight: 800; color: var(--accent-color); font-size: 1.6rem; }
        
        /* Header Background */
        .header-bg { background: var(--primary-gradient); height: 200px; border-radius: 0 0 30px 30px; margin-bottom: -100px; }

        /* Cards */
        .detail-card { background: var(--card-bg); border-radius: 20px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); overflow: hidden; margin-bottom: 20px; height: 100%; }
        .card-header-custom { padding: 20px 25px; border-bottom: 1px solid #f0f0f0; background: #fff; display: flex; justify-content: space-between; align-items: center; }
        .card-body-custom { padding: 30px; }
        
        /* Text Styles */
        .section-label { text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #6B7280; margin-bottom: 5px; display: block; }
        .info-text { font-size: 1.1rem; font-weight: 500; color: #111; margin-bottom: 20px; }

        /* Staff Image & Badge */
        .staff-img-wrapper { position: relative; width: 100px; height: 100px; margin: 0 auto 15px; }
        .staff-img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .verified-badge { position: absolute; bottom: 0; right: 0; background: #10B981; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border: 2px solid white; }
        
        /* Status Badges */
        .status-badge { padding: 8px 16px; border-radius: 50px; font-weight: 600; font-size: 0.9rem; }
        .status-Pending { background: #FFF7ED; color: #C2410C; }
        .status-Accepted { background: #EFF6FF; color: #2563EB; }
        .status-Completed { background: #ECFDF5; color: #059669; }

        /* Buttons */
        .btn-back { background: rgba(255,255,255,0.2); color: white; padding: 8px 20px; border-radius: 50px; text-decoration: none; backdrop-filter: blur(5px); transition: 0.3s; }
        .btn-back:hover { background: white; color: var(--accent-color); }
        .btn-call { background: #10B981; color: white; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: 600; display: block; text-align: center; transition: 0.3s; }
        .btn-call:hover { background: #059669; color: white; }

        /* Rating Star Logic */
        .star-rating { direction: rtl; display: inline-flex; font-size: 2rem; justify-content: center; width: 100%; }
        .star-rating input { display: none; }
        .star-rating label { color: #ddd; cursor: pointer; transition: color 0.2s; padding: 0 5px; }
        .star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #f59e0b; }
        
        .rating-display { color: #f59e0b; font-size: 1.2rem; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand brand-text" href="Homes.php">Fixzy.</a>
            <div class="ms-auto"><a href="my_bookings.php" class="btn btn-outline-dark btn-sm px-4 rounded-pill">My Bookings</a></div>
        </div>
    </nav>

    <div class="header-bg pt-5">
        <div class="container pt-4">
            <a href="my_bookings.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back to List</a>
        </div>
    </div>

    <div class="container pb-5" style="position: relative; z-index: 10;">
        <div class="row g-4">
            
            <div class="col-lg-8">
                <div class="detail-card">
                    <div class="card-header-custom">
                        <h5 class="m-0 fw-bold">Booking Summary</h5>
                        <span class="status-badge status-<?php echo $data['Status']; ?>">
                            <?php echo $data['Status']; ?>
                        </span>
                    </div>
                    <div class="card-body-custom">
                        <div class="mb-4">
                            <label class="section-label">Service</label>
                            <h2 class="fw-bold" style="color: var(--accent-color);"><?php echo htmlspecialchars($data['Service']); ?></h2>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="section-label">Date</label>
                                <p class="info-text"><?php echo date('D, M j, Y', strtotime($data['Date'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="section-label">Time</label>
                                <p class="info-text"><?php echo date('h:i A', strtotime($data['Time'])); ?></p>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="section-label">Address</label>
                            <p class="info-text text-muted"><?php echo htmlspecialchars($data['Address']); ?></p>
                        </div>
                        
                        <?php if (!empty($data['Description'])): ?>
                            <div class="mt-4 p-3 bg-light rounded border-start border-4 border-primary">
                                <label class="section-label mb-1">Note</label>
                                <p class="mb-0 fst-italic text-secondary">"<?php echo htmlspecialchars($data['Description']); ?>"</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="detail-card">
                    <div class="card-header-custom"><h5 class="m-0 fw-bold">Assigned Expert</h5></div>
                    <div class="card-body-custom d-flex flex-column justify-content-center h-100">
                        
                        <?php if (empty($data['StaffID'])): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-hourglass-split display-4 text-warning"></i>
                                <h5 class="mt-3 fw-bold">Finding a Pro...</h5>
                                <p class="text-muted small">We are currently looking for a nearby expert. Check back soon!</p>
                            </div>
                        
                        <?php else: ?>
                            <div class="text-center">
                                <?php 
                                    // 🟢 FIX: Added 'uploads/' because DB only stores filename
                                    $photoPath = !empty($data['StaffPhoto']) ? 'uploads/' . $data['StaffPhoto'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                                ?>
                                <div class="staff-img-wrapper">
                                    <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="Staff" class="staff-img">
                                    <div class="verified-badge"><i class="bi bi-check-lg"></i></div>
                                </div>
                                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($data['StaffFname'] . ' ' . $data['StaffLname']); ?></h4>
                                <span class="badge bg-secondary mb-3"><?php echo htmlspecialchars($data['StaffSkill']); ?></span>

                                <?php if ($data['Status'] == 'Accepted'): ?>
                                    <div class="alert alert-primary text-center shadow-sm border-0 mb-3 p-2">
                                        <small class="text-uppercase fw-bold text-primary opacity-75" style="font-size: 0.7rem;">Start OTP</small>
                                        <div class="display-6 fw-bold text-dark letter-spacing-2" style="font-size: 2rem;">
                                            <?php echo htmlspecialchars($data['OTP']); ?>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Give this code to staff</small>
                                    </div>
                                <?php endif; ?>
                                <a href="tel:<?php echo htmlspecialchars($data['StaffPhone']); ?>" class="btn-call mb-3">
                                    <i class="bi bi-telephone-fill"></i> Call Expert
                                </a>

                                <?php if ($data['Status'] == 'Completed'): ?>
                                    <hr>
                                    <?php if (empty($data['Rating'])): ?>
                                        <h6 class="fw-bold mt-3">Job Completed?</h6>
                                        <button class="btn btn-warning w-100 fw-bold text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#ratingModal">
                                            <i class="bi bi-star-fill"></i> Rate Service
                                        </button>
                                    <?php else: ?>
                                        <div class="mt-3 p-3 bg-light rounded text-start">
                                            <label class="section-label">Your Review</label>
                                            <div class="rating-display mb-1">
                                                <?php for($i=0; $i<$data['Rating']; $i++) echo '<i class="bi bi-star-fill"></i>'; ?>
                                                <?php for($i=$data['Rating']; $i<5; $i++) echo '<i class="bi bi-star"></i>'; ?>
                                            </div>
                                            <p class="small text-muted mb-0 fst-italic">"<?php echo htmlspecialchars($data['Review']); ?>"</p>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="ratingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST">
                    <div class="modal-header border-0 pb-0 justify-content-center">
                        <h5 class="modal-title fw-bold">Rate Your Experience</h5>
                    </div>
                    <div class="modal-body text-center">
                        <p class="text-muted">How was the service provided by <b><?php echo htmlspecialchars($data['StaffFname']); ?></b>?</p>
                        
                        <div class="star-rating mb-3">
                            <input type="radio" name="rating" id="s5" value="5" required><label for="s5"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" name="rating" id="s4" value="4"><label for="s4"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" name="rating" id="s3" value="3"><label for="s3"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" name="rating" id="s2" value="2"><label for="s2"><i class="bi bi-star-fill"></i></label>
                            <input type="radio" name="rating" id="s1" value="1"><label for="s1"><i class="bi bi-star-fill"></i></label>
                        </div>

                        <textarea name="review" class="form-control bg-light border-0" rows="3" placeholder="Write a short review..." required></textarea>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="submit_feedback" class="btn btn-primary rounded-pill px-4">Submit Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../script/js/bootstrap.bundle.min.js"></script>
</body>
</html>
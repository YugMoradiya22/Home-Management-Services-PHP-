<?php
session_start();
include '../Phpmodules/Conres.php';

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Stats
$totalCustomers = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM rescustomer"))['count'];
$totalStaff     = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM restaff"))['count'];
$totalServices  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM services"))['count'];
$totalBookings  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM bookingservice"))['count'];

// Add Service
if (isset($_POST['add_service'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) die("Invalid CSRF token.");

    $category     = trim(mysqli_real_escape_string($con, $_POST['category']));
    $service_name = trim(mysqli_real_escape_string($con, $_POST['service_name']));
    $description  = trim(mysqli_real_escape_string($con, $_POST['description']));
    $visit_charge = floatval($_POST['visit_charge']);
    $offers       = trim(mysqli_real_escape_string($con, $_POST['offers']));

    if (empty($category) || empty($service_name) || empty($description) || $visit_charge <= 0) {
        echo "<script>alert('Please fill all fields correctly.');window.history.back();</script>"; exit;
    }
    if (strlen($service_name) > 100 || strlen($description) > 500) {
        echo "<script>alert('Input text too long.');window.history.back();</script>"; exit;
    }

    $check = mysqli_prepare($con, "SELECT id FROM services WHERE service_name = ?");
    mysqli_stmt_bind_param($check, "s", $service_name);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    if (mysqli_stmt_num_rows($check) > 0) {
        mysqli_stmt_close($check);
        echo "<script>alert('This service already exists.');window.history.back();</script>"; exit;
    }
    mysqli_stmt_close($check);

    $imagePath = "";
    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg','image/png','image/webp'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Only JPG, PNG, and WEBP files are allowed.');window.history.back();</script>"; exit;
        }
        $targetDir = "uploads/";
        if (!is_dir("../" . $targetDir)) mkdir("../" . $targetDir, 0777, true);
        $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9_\.-]/","_", basename($_FILES["image"]["name"]));
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES["image"]["tmp_name"], "../" . $targetFile);
        $imagePath = $targetFile;
    }

    $stmt = mysqli_prepare($con, "INSERT INTO services (category, service_name, description, visit_charge, offers, image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "sssiss", $category, $service_name, $description, $visit_charge, $offers, $imagePath);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "<script>alert('Service Added Successfully');window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
}

// Delete Service
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // optionally delete image file (not implemented to avoid accidental deletion)
    mysqli_query($con, "DELETE FROM services WHERE id=$id");
    echo "<script>alert('Service Deleted Successfully');window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
}

// Update Service
if (isset($_POST['update_service'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) die("Invalid CSRF token.");

    $id           = intval($_POST['id']);
    $category     = trim(mysqli_real_escape_string($con, $_POST['category']));
    $service_name = trim(mysqli_real_escape_string($con, $_POST['service_name']));
    $description  = trim(mysqli_real_escape_string($con, $_POST['description']));
    $visit_charge = floatval($_POST['visit_charge']);
    $offers       = trim(mysqli_real_escape_string($con, $_POST['offers']));

    if (empty($category) || empty($service_name) || empty($description) || $visit_charge <= 0) {
        echo "<script>alert('Please fill all fields correctly.');window.history.back();</script>"; exit;
    }

    $updateQuery = "UPDATE services SET category=?, service_name=?, description=?, visit_charge=?, offers=?";
    $params = [$category, $service_name, $description, $visit_charge, $offers];
    $types = "sssds";

    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg','image/png','image/webp'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Only JPG, PNG, and WEBP files are allowed.');window.history.back();</script>"; exit;
        }
        $targetDir = "uploads/";
        if (!is_dir("../" . $targetDir)) mkdir("../" . $targetDir, 0777, true);
        $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9_\.-]/","_", basename($_FILES["image"]["name"]));
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES["image"]["tmp_name"], "../" . $targetFile);
        $imagePath = $targetFile;
        $updateQuery .= ", image=?";
        $params[] = $imagePath;
        $types .= "s";
    }

    $updateQuery .= " WHERE id=?";
    $params[] = $id;
    $types .= "i";

    $stmt = mysqli_prepare($con, $updateQuery);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "<script>alert('Service Updated Successfully');window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
}

// Pagination & Search
$limit  = 10;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim(mysqli_real_escape_string($con, $_GET['search'])) : '';
$whereClause = '';
if (!empty($search)) {
    $whereClause = "WHERE category LIKE '%$search%' OR service_name LIKE '%$search%' OR description LIKE '%$search%'";
}

$serviceQuery = "SELECT id, category, service_name, description, visit_charge, offers, image, created_at FROM services $whereClause ORDER BY id DESC LIMIT $limit OFFSET $offset";
$serviceResult = mysqli_query($con, $serviceQuery);

$totalServicesCountQuery = "SELECT COUNT(*) as total FROM services $whereClause";
$totalServicesCount = mysqli_fetch_assoc(mysqli_query($con, $totalServicesCountQuery))['total'];
$totalPages = ceil($totalServicesCount / $limit);
$serialStart = $offset + 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Dashboard</title>
<link href="./script/css/bootstrap.min.css" rel="stylesheet">
<script src="./script/js/bootstrap.bundle.js"></script>
<link href="./script/bootstrap-icons-1.13.1/bootstrap-icons-1.13.1/bootstrap-icons.css" rel="stylesheet">
<style>
body {background-color:#f4f6f9;}
.sidebar {height:100vh;background:linear-gradient(180deg,#0d47a1,#1976d2);color:white;padding-top:20px;position:fixed;width:240px;}
.sidebar h4{text-align:center;font-weight:bold;margin-bottom:30px;}
.sidebar a{display:block;padding:12px 20px;color:white;text-decoration:none;font-size:16px;border-radius:8px;margin:6px 10px;}
.sidebar a:hover{background:rgba(255,255,255,0.2);}
.sidebar a.logout-btn{position:absolute;bottom:20px;width:90%;background:#d32f2f;}
.sidebar a.logout-btn:hover{background:#f44336;}
.header{margin-left:240px;background:#0d47a1;color:white;padding:15px;font-size:20px;font-weight:bold;}
.content{margin-left:240px;padding:20px;}
.card-stats{border-radius:12px;padding:20px;color:white;box-shadow:0px 4px 6px rgba(0,0,0,0.1);text-align:center;}
.bg-blue{background:#1976d2;}
.bg-green{background:#388e3c;}
.bg-orange{background:#f57c00;}
.bg-red{background:#d32f2f;}
.card-stats h3{margin:0;font-size:26px;font-weight:bold;}
.card-stats p{margin:5px 0 0;font-size:16px;}
.table thead{background:#1976d2;color:white;}
.card{border-radius:12px;margin-bottom:25px;}
.footer{margin-left:240px;background:#0d47a1;color:white;text-align:center;padding:12px;margin-top:30px;font-size:14px;}
.service-img{width:60px;height:60px;object-fit:cover;border-radius:8px;}
.modal-content{border-radius:12px;}
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

<div class="header">📊 Service Dashboard</div>
<div class="content">
<div class="row mb-4">
<div class="col-md-3"><div class="card-stats bg-blue"><h3><?= $totalCustomers ?></h3><p>Total Customers</p><i class="bi bi-people-fill fs-3"></i></div></div>
<div class="col-md-3"><div class="card-stats bg-green"><h3><?= $totalStaff ?></h3><p>Total Staff</p><i class="bi bi-person-workspace fs-3"></i></div></div>
<div class="col-md-3"><div class="card-stats bg-orange"><h3><?= $totalServices ?></h3><p>Total Services</p><i class="bi bi-box-seam fs-3"></i></div></div>
<div class="col-md-3"><div class="card-stats bg-red"><h3><?= $totalBookings ?></h3><p>Total Bookings</p><i class="bi bi-calendar-check-fill fs-3"></i></div></div>
</div>

<div class="card">
<div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
<h4><i class="bi bi-box-seam"></i> Services Data</h4>
<div class="d-flex align-items-center mt-2 mt-md-0">
<form method="GET" class="d-flex me-2">
<input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="form-control me-2" placeholder="Search by name, category..." style="width:220px;">
<button class="btn btn-primary"><i class="bi bi-search"></i></button>
</form>
<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addServiceModal"><i class="bi bi-plus-circle"></i> Add Service</button>
</div>
</div>

<table class="table table-hover">
<thead><tr><th>#</th><th>Category</th><th>Service Name</th><th>Description</th><th>Visit Charge</th><th>Offers</th><th>Image</th><th>Created At</th><th>Action</th></tr></thead>
<tbody>
<?php $serial=$serialStart; mysqli_data_seek($serviceResult,0); while($row=mysqli_fetch_assoc($serviceResult)){ ?>
<tr>
<td><?= $serial++ ?></td>
<td><?= htmlspecialchars($row['category']) ?></td>
<td><?= htmlspecialchars($row['service_name']) ?></td>
<td><?= htmlspecialchars($row['description']) ?></td>
<td>₹<?= number_format($row['visit_charge'],2) ?></td>
<td><?= htmlspecialchars($row['offers'] ?? '-') ?></td>
<td><?php if(!empty($row['image'])){ ?><img src="../<?= $row['image'] ?>" class="service-img"><?php } else {echo "<span class='text-muted'>No Image</span>";} ?></td>
<td><?= $row['created_at'] ?></td>
<td>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editServiceModal<?= $row['id'] ?>"><i class="bi bi-pencil-square"></i> Edit</button>
    <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this service?');"><i class="bi bi-trash"></i> Delete</a>
</td>
</tr>

<!-- Edit Modal for this row -->
<div class="modal fade" id="editServiceModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <div class="modal-header">
          <h5 class="modal-title">Edit Service</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <label>Category</label>
            <input type="text" name="category" class="form-control mb-2" required value="<?= htmlspecialchars($row['category']) ?>">

            <label>Service Name</label>
            <input type="text" name="service_name" class="form-control mb-2" required value="<?= htmlspecialchars($row['service_name']) ?>">

            <label>Description</label>
            <textarea name="description" class="form-control mb-2" required><?= htmlspecialchars($row['description']) ?></textarea>

            <label>Visit Charge</label>
            <input type="number" name="visit_charge" class="form-control mb-2" required value="<?= htmlspecialchars($row['visit_charge']) ?>">

            <label>Offers</label>
            <input type="text" name="offers" class="form-control mb-2" value="<?= htmlspecialchars($row['offers']) ?>">

            <label>Image (optional)</label>
            <input type="file" name="image" class="form-control">
            <?php if(!empty($row['image'])): ?><div class="mt-2"><small>Current:</small><br><img src="../<?= $row['image'] ?>" style="width:100px;height:60px;object-fit:cover;border-radius:6px;"></div><?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_service" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php } // end loop ?>
</tbody></table>

<nav><ul class="pagination justify-content-center">
<?php for($i=1;$i<=$totalPages;$i++): ?>
<li class="page-item <?= ($i==$page)?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?><?= !empty($search)?'&search='.urlencode($search):'' ?>"><?= $i ?></a></li>
<?php endfor; ?>
</ul></nav>
</div></div></div>

<!-- ADD SERVICE MODAL -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="modal-header">
          <h5 class="modal-title">Add Service</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <label>Category</label>
            <input type="text" name="category" class="form-control mb-2" required>

            <label>Service Name</label>
            <input type="text" name="service_name" class="form-control mb-2" required>

            <label>Description</label>
            <textarea name="description" class="form-control mb-2" required></textarea>

            <label>Visit Charge</label>
            <input type="number" step="0.01" name="visit_charge" class="form-control mb-2" required>

            <label>Offers</label>
            <input type="text" name="offers" class="form-control mb-2">

            <label>Image</label>
            <input type="file" name="image" class="form-control">
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_service" class="btn btn-success">Add Service</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="footer">© <?= date('Y') ?> Service Management System. All Rights Reserved.</div>
</body></html>

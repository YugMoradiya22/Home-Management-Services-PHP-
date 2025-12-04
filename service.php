<?php
// service.php
include './Phpmodules/Conres.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Fetch categories
$catResult = $con->query("SELECT DISTINCT category FROM services ORDER BY category ASC");

// Search & filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

if (!empty($search) && !empty($selectedCategory)) {
    $stmt = $con->prepare("SELECT * FROM services WHERE category = ? AND (service_name LIKE ? OR description LIKE ?) ORDER BY created_at DESC LIMIT 50");
    $like = "%$search%";
    $stmt->bind_param("sss", $selectedCategory, $like, $like);
} elseif (!empty($search)) {
    $stmt = $con->prepare("SELECT * FROM services WHERE service_name LIKE ? OR category LIKE ? OR description LIKE ? ORDER BY created_at DESC LIMIT 50");
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
} elseif (!empty($selectedCategory)) {
    $stmt = $con->prepare("SELECT * FROM services WHERE category = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->bind_param("s", $selectedCategory);
} else {
    $stmt = $con->prepare("SELECT * FROM services ORDER BY created_at DESC LIMIT 50");
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Services — Fixzy</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="../script/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --accent:#ff6b6b;
      --muted:#6c757d;
      --card-bg:#ffffff;
      --page-bg: linear-gradient(180deg,#f3f7ff 0%, #eef5ff 100%);
    }
    body{font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background: var(--page-bg); color:#222;}
    .topbar{background: #0b1220; color: #fff; padding:14px 0; box-shadow: 0 6px 20px rgba(11,18,32,0.08);}
    .brand{font-weight:700; color:var(--accent); font-size:1.35rem;}
    .hero{
      max-width:1100px; margin:30px auto 10px; padding:34px; border-radius:14px;
      background: linear-gradient(90deg,#ffffff, #f8fbff);
      box-shadow: 0 10px 30px rgba(32,40,80,0.06);
      display:flex; gap:20px; align-items:center;
    }
    .hero h1{margin:0; font-size:1.9rem; color:#0b1b3a;}
    .hero p{margin:6px 0 0; color:var(--muted);}
    .search-panel{max-width:1100px; margin:18px auto; padding:18px; border-radius:12px; background:#fff; box-shadow:0 6px 22px rgba(16,24,64,0.04);}
    .search-panel .form-control, .search-panel .form-select{border-radius:999px;}
    .services-grid{max-width:1100px; margin:24px auto 80px; display:grid; gap:20px; grid-template-columns: repeat(auto-fill,minmax(260px,1fr));}
    .service-card{background:var(--card-bg); border-radius:12px; overflow:hidden; display:flex; flex-direction:column; min-height:360px; box-shadow: 0 8px 26px rgba(17,24,49,0.06);}
    .service-card .img{height:180px; background:#e9eefc; display:block; width:100%; object-fit:cover;}
    .card-body{padding:18px 16px; display:flex; flex-direction:column; gap:8px; flex:1;}
    .card-title{font-size:1.05rem; color:#0b2a52; font-weight:700; margin:0;}
    .card-category{color:var(--muted); font-size:0.9rem; font-weight:600;} /* ✅ added category style */
    .card-desc{color:var(--muted); font-size:0.95rem; flex:1;}
    .meta-row{display:flex; gap:10px; align-items:center; margin-top:8px;}
    .price{color:var(--accent); font-weight:700;}
    .offer{color:#2f9d46; font-weight:600; font-size:0.95rem;}
    .btn-book{border-radius: 999px; padding:8px 18px;}
    footer{background:#0b1220; color:#fff; padding:26px 0; margin-top:40px;}
    @media (max-width:576px){ .hero{flex-direction:column; text-align:center;} }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="brand">Fixzy</div>
      <nav class="d-none d-md-flex" aria-label="main-nav">
        <a href="Homes.php" class="text-white me-3">Home</a>
        <a href="service.php" class="text-white me-3">Services</a>
        <a href="#" class="text-white">Contact</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero">
      <div>
        <h1>Fast, trusted home services</h1>
        <p>Pick a service and book an appointment in seconds — professional technicians, upfront pricing.</p>
      </div>
      <div class="ms-auto text-end">
        <a class="btn btn-outline-dark" href="Homes.php">Explore Home</a>
      </div>
    </section>

    <section class="search-panel">
      <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-6 col-12">
          <input type="text" name="search" class="form-control" placeholder="Search services or description..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4 col-8">
          <select name="category" class="form-select">
            <option value="">All Categories</option>
            <?php while ($cat = $catResult->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($cat['category']) ?>" <?= ($selectedCategory == $cat['category']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['category']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-2 col-4 text-end">
          <button class="btn btn-primary w-100">Search</button>
        </div>
      </form>
      <?php if (!empty($search) || !empty($selectedCategory)): ?>
        <p class="mt-3 mb-0" style="color:#6b7280;">
          Showing results <?= $search ? 'for <strong>'.htmlspecialchars($search).'</strong>' : '' ?>
          <?= $selectedCategory ? ' in <strong>'.htmlspecialchars($selectedCategory).'</strong>' : '' ?>
        </p>
      <?php endif; ?>
    </section>

    <section class="services-grid container">
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $img = 'uploads/default.jpg';
          if (!empty($row['image'])) {
            if (file_exists("uploads/".$row['image'])) $img = "uploads/".htmlspecialchars($row['image']);
            else $img = htmlspecialchars($row['image']);
          }
          $serviceEsc = htmlspecialchars($row['service_name']);
          $categoryEsc = htmlspecialchars($row['category']); // ✅ Added category variable
          $serviceUrl = "BookaServices.php?service=" . urlencode($row['service_name']);
          echo '
            <article class="service-card">
              <img src="'.$img.'" alt="'.$serviceEsc.'" class="img">
              <div class="card-body">
                <h3 class="card-title">'.$serviceEsc.'</h3>
                <div class="card-category">Category: '.$categoryEsc.'</div> <!-- ✅ Added category line -->
                <div class="card-desc">'.htmlspecialchars($row['description']).'</div>
                <div class="meta-row">
                  <div class="price">₹'.htmlspecialchars($row['visit_charge']).'</div>
                  <div class="ms-auto offer">'.(!empty($row['offers'])?htmlspecialchars($row['offers']):'No offers').'</div>
                </div>
                <div class="mt-3">
                  <a class="btn btn-primary btn-book" href="'.$serviceUrl.'">Book Now</a>
                </div>
              </div>
            </article>';
        }
      } else {
        echo '<div class="col-12 text-center" style="grid-column:1/-1;color:#6b7280;padding:40px;background:#fff;border-radius:12px;">No services found.</div>';
      }
      $con->close();
      ?>
    </section>
  </main>

  <footer>
    <div class="container text-center">
      <p style="margin-bottom:6px;">&copy; <?= date('Y') ?> Fixzy — All rights reserved</p>
      <small style="color:#97a0b8;">Terms • Privacy</small>
    </div>
  </footer>

  <script src="../script/js/bootstrap.bundle.min.js"></script>
</body>
</html>

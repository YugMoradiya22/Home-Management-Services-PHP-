<?php
session_start();
// Updated to check for 'user_email' because that is what your Login page saves
$isLoggedIn = isset($_SESSION['user_email']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HomeFix - Book Expert Home Services Online</title>
    <link rel="stylesheet" href="../script/css/bootstrap.min.css" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .hero-content {
            position: relative;
            z-index: 2;
        }
    </style>
</head>

<body>
    <div class="full-pages">

        <nav class="navbar navbar-expand-lg navbar-light bg-light" style="margin: 0 auto;">
            <div class="container">
                <div class="logowala">Fixzy</div>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto me-3">
                        <li class="nav-item"><a class="nav-link active" href="#homepages">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="#cardsection">Services</a></li>
                        <li class="nav-item"><a class="nav-link" href="#How-We-work">About Us</a></li>
                        <li class="nav-item"><a class="nav-link" href="#footerqw">Contact</a></li>

                        <?php if ($isLoggedIn): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="my_bookings.php" style="color: #0d6efd; font-weight: 600;">My Bookings</a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="btn btn-danger px-3 py-1 mx-1 text-white" href="logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle btn btn-outline-primary px-3 py-1 mx-1 text-dark"
                                    href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Login
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="loginDropdown">
                                    <li><a class="dropdown-item" href="Registerforcustomer.php?redirect=Homes.php">Client Login</a></li>
                                    <li><a class="dropdown-item" href="Loginforstaff.php?redirect=index.php">Staff Login</a></li>
                                </ul>

                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="onlyforsection" id="homepages">
            <section class="hero-section">
                <div class="Overlay"></div>
                <div class="hero-content">
                    <h1 class="display-5 text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.6);">
                        Professional Home Repair Services at Your Fingertips
                    </h1>
                    <p style="color: #fdfdfdff; text-shadow: 2px 2px 4px rgba(36, 255, 3, 0.6); font-size: 18px;">
                        <b>Repairwala dhoondhne ki tension kyu? Jab apke paas hai apna Repairwala!</b>
                    </p>

                    <div class="hero-buttons mt-4 text-center d-flex justify-content-center gap-3">
                        <div id="registerArea">
                            <a href="<?php echo $isLoggedIn ? 'BookaServices.php' : 'Registerforcustomer.php?redirect=BookaServices.php'; ?>">
                                <button class="btn btn-light btn-lg px-4">Book Now</button>
                            </a>
                        </div>

                        <div id="loginArea">
                            <a href="<?php echo $isLoggedIn ? 'service.php' : 'Registerforcustomer.php?redirect=service.php'; ?>">
                                <button class="btn btn-primary btn-lg px-4">Our Services</button>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section class="services-section" id="cardsection">
            <h2 class="cardforcenter">Our Services</h2>
            <p class="cardforcenter">We provide a wide range of professional home repair and maintenance services to
                keep your home in perfect condition.</p>
            <div class="container">
                <?php
                $services = [
                    ['💧', 'Plumbing Services', 'Leaking pipes, clogged drains, fixture installations, and all your plumbing needs.'],
                    ['🧹', 'Home Cleaning', 'Thorough cleaning services for any room in your home, including deep cleaning options.'],
                    ['🔨', 'Carpentry', 'Furniture assembly, repairs, custom woodwork, and general carpentry services.'],
                    ['🔧', 'Electrical Repair', 'Wiring problems, outlet repairs, lighting installation, and electrical maintenance.'],
                    ['❄️', 'AC Maintenance', 'Installation, repairs, maintenance, and cleaning of AC units for optimal performance.'],
                    ['👨‍🔧', 'Home Automation Solution', 'We provide Automation solutions to make your home smarter with your finger.']
                ];

                foreach ($services as $service) {
                    echo '<div class="service-card">';
                    echo '<div class="icon">' . $service[0] . '</div>';
                    echo '<h3>' . $service[1] . '</h3>';
                    echo '<p>' . $service[2] . '</p>';
                    echo '<a href="' . ($isLoggedIn ? 'service.php' : 'Registerforcustomer.php?redirect=service.php') . '">Learn More</a>';
                    echo '</div>';
                }
                ?>
            </div>

            <div class="btn-wrapper">
                <a href="<?php echo $isLoggedIn ? 'service.php' : 'Registerforcustomer.php?redirect=service.php'; ?>">
                    <button class="view-btn">View All Services</button>
                </a>
            </div>
        </section>
    </div>

    <section class="how-it-works" id="How-We-work">
        <h2>How It Works</h2>
        <p>Getting your home repaired has never been easier. Follow these simple steps:</p>

        <div class="steps">
            <div class="step">
                <div class="icon">🖱️</div>
                <h3>1. Choose a Service</h3>
                <p>Browse our range of home repair services and select what you need.</p>
            </div>

            <div class="step">
                <div class="icon">📅</div>
                <h3>2. Book Appointment</h3>
                <p>Select your preferred date and time and provide your details.</p>
            </div>

            <div class="step">
                <div class="icon">🛠️</div>
                <h3>3. Get It Fixed</h3>
                <p>Our professional will arrive and complete the service with quality work.</p>
            </div>
        </div>

        <a href="<?php echo $isLoggedIn ? 'service.php' : 'Registerforcustomer.php?redirect=service.php'; ?>" class="cta-button">Book a Service Now</a>
    </section>

    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        <p>Hear from our satisfied customers about their experiences with our services.</p>

        <div class="cards-customer">
            <div class="testimonial-card">
                <div class="stars">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p class="review">"The plumber arrived on time and fixed our leaking sink quickly. Very professional
                    and clean work. Would definitely recommend!"</p>
                <div class="customer-info">
                    <div class="avatar">SG</div>
                    <div><strong>Shubman Gill</strong><br><span>Plumbing Service</span></div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="stars">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                </div>
                <p class="review">"Amazing service! The electrician was knowledgeable and fixed our wiring issues
                    efficiently. Great communication throughout."</p>
                <div class="customer-info">
                    <div class="avatar">JS</div>
                    <div><strong>Jay Sharma</strong><br><span>Electrical Repair</span></div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="stars">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                </div>
                <p class="review">"The home cleaning service was exceptional. My house has never looked better. The
                    team was friendly and thorough. 10/10 service!"</p>
                <div class="customer-info">
                    <div class="avatar">RJ</div>
                    <div><strong>Rakesh Jain</strong><br><span>Home Cleaning</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="call-to-action">
        <h2>Ready to Get Your Home Fixed?</h2>
        <p>Book a service today and let our professionals take care of your home repair needs.</p>
        <a href="<?php echo $isLoggedIn ? 'service.php' : 'Registerforcustomer.php?redirect=service.php'; ?>">Book a Service Now</a>
    </section>

    <footer class="bg-dark text-white pt-4" id="footerqw">
        <div class="container text-center text-md-start">
            <div class="row mt-3">
                <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-4">
                    <h5 class="text-uppercase fw-bold">Fixzy</h5>
                    <p>Your trusted partner for professional home repair services. Quality work, affordable prices.</p>
                </div>

                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">Quick Links</h6>
                    <p><a href="#homepages" class="text-white text-decoration-none">Home</a></p>
                    <p><a href="#cardsection" class="text-white text-decoration-none">Services</a></p>
                    <p><a href="#footerqw" class="text-white text-decoration-none">About Us</a></p>
                    <p><a href="#footerqw" class="text-white text-decoration-none">Contact</a></p>
                </div>

                <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">Our Services</h6>
                    <p><a href="#homepages" class="text-white text-decoration-none">Plumbing</a></p>
                    <p><a href="#cardsection" class="text-white text-decoration-none">Electrical Repair</a></p>
                    <p><a href="#footerqw" class="text-white text-decoration-none">AC Maintenance</a></p>
                    <p><a href="#footerqw" class="text-white text-decoration-none">Home Painting</a></p>
                    <p><a href="#footerqw" class="text-white text-decoration-none">Home Cleaning</a></p>
                </div>

                <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">Contact</h6>
                    <p><i class="bi bi-geo-alt-fill me-2"></i> Surat, Gujarat, India</p>
                    <p><i class="bi bi-envelope-fill me-2"></i> support@Fixzy.com</p>
                    <p><i class="bi bi-telephone-fill me-2"></i> +91 9876543210</p>
                </div>
            </div>
        </div>

        <div class="text-center p-3 mt-3" style="background-color: rgba(255, 255, 255, 0.05);">
            © 2025 HomeFix. All rights reserved.
        </div>
    </footer>

    <script src="../script/js/bootstrap.bundle.min.js"></script>
</body>

</html>
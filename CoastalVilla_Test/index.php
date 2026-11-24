<?php
session_start();

require_once "config/database.php";

$isLoggedIn = isset($_SESSION["user_id"]);
$userName = $isLoggedIn ? $_SESSION["user_name"] : "";

// Get active rooms from database
$sql = "SELECT * FROM rooms WHERE room_status = 'Active'";
$stmt = $conn->prepare($sql);
$stmt->execute();

$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Coastal Villa | Your Beach Escape</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- NAVIGATION BAR -->
    <header class="navbar">

        <a href="index.php" class="logo">
            Coastal<span>Villa.</span>
        </a>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="#rooms">Rooms</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
        </nav>

        <div class="nav-actions">

            <?php if ($isLoggedIn): ?>

                <span class="welcome-user">
                    Hello, <?php echo htmlspecialchars($userName); ?>
                </span>

                <a href="logout.php" class="login-btn">
                    Logout
                </a>

            <?php else: ?>

                <a href="login.php" class="login-btn">
                    Login
                </a>

                <a href="register.php" class="register-btn">
                    Sign Up
                </a>

            <?php endif; ?>

        </div>

    </header>

    <?php include "includes/availability-search.php"; ?>
    <!-- HERO SECTION -->
    <section class="hero">

        <div class="hero-overlay">

            <div class="hero-content">

                <p class="hero-subtitle">
                    WELCOME TO COASTAL VILLA
                </p>

                <h1>
                    Escape to the
                    <span>Beautiful Coast</span>
                </h1>

                <p class="hero-description">
                    Experience peaceful moments, beautiful ocean views
                    and unforgettable stays at our coastal paradise.
                </p>

                <a href="#rooms" class="primary-btn">
                    Explore Our Rooms
                </a>

            </div>

        </div>

    </section>


    <!-- INTRODUCTION SECTION -->
    <section class="intro" id="about">

        <p class="section-label">YOUR PERFECT GETAWAY</p>

        <h2>Relax, Refresh & Reconnect</h2>

        <p>
            Welcome to Coastal Villa, your perfect destination
            for a relaxing beach holiday. Enjoy comfortable
            accommodation, warm hospitality and the beauty
            of Sri Lanka's coastline.
        </p>

    </section>
    
    
    <!-- FACILITIES SECTION -->
    <section class="facilities">

        <div class="facility">
            <img src="assets/images/ocean.jfif" alt="Ocean View"
                style="width:100%; height:220px; object-fit:cover; display:block;">
            <h3>Ocean Views</h3>
            <p>Enjoy beautiful coastal surroundings.</p>
        </div>

        <div class="facility">
            <img src="assets/images/pool.jfif" alt="Swimming Pool"
                style="width:100%; height:220px; object-fit:cover; display:block;">
            <h3>Swimming Pool</h3>
            <p>Relax and refresh in our swimming pool.</p>
        </div>

        <div class="facility">
            <img src="assets/images/restaurant.jfif" alt="Restaurant"
                style="width:100%; height:220px; object-fit:cover; display:block;">
            <h3>Restaurant</h3>
            <p>Enjoy delicious meals during your stay.</p>
        </div>

        <div class="facility">
            <img src="assets/images/wifi.jfif" alt="Free Wi-Fi"
                style="width:100%; height:220px; object-fit:cover; display:block;">
            <h3>Free Wi-Fi</h3>
            <p>Stay connected throughout your visit.</p>
        </div>

    </section>



    <!-- ROOMS SECTION -->
    <section class="rooms-section" id="rooms">

        <div class="section-heading">

            <p class="section-label">STAY WITH COMFORT</p>

            <h2>Discover Our Rooms</h2>

            <p>
                Find the perfect room for your unforgettable holiday.
            </p>

        </div>

        
<div class="rooms-container">

    <?php if (count($rooms) > 0): ?>

        <?php foreach ($rooms as $room): ?>

            <div class="room-card">

                <div class="room-image">

                    <?php if (!empty($room["image"])): ?>

                        <img
                            src="<?php echo htmlspecialchars($room["image"]); ?>"
                            alt="<?php echo htmlspecialchars($room["room_name"]); ?>"
                        >

                    <?php else: ?>

                        <div class="room-placeholder-image">
                            🏖️
                        </div>

                    <?php endif; ?>

                </div>

                <div class="room-details">

                    <span class="room-type">
                        <?php echo htmlspecialchars($room["room_type"]); ?>
                    </span>

                    <h3>
                        <?php echo htmlspecialchars($room["room_name"]); ?>
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($room["description"]); ?>
                    </p>

                    <p class="room-facilities">
                        <strong>Facilities:</strong>
                        <?php echo htmlspecialchars($room["facilities"]); ?>
                    </p>

                    <p class="room-guests">
                        <strong>Maximum Guests:</strong>
                        <?php echo (int)$room["max_guests"]; ?>
                    </p>

                    <div class="room-bottom">

                        <p class="room-price">
                            Rs. <?php echo number_format($room["price_per_night"], 2); ?>
                            <span>/ night</span>
                        </p>

                        <a href="rooms.php" class="primary-btn">
                            View Rooms
                        </a>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p>No rooms are currently available.</p>

    <?php endif; ?>

</div>


    </section>


    <!-- BOOKING SECTION -->
    <section class="booking-banner">

        <h2>Your Beach Holiday Awaits!</h2>

        <p>
            Make your next holiday special with Coastal Villa.
        </p>

        <a href="#rooms" class="primary-btn">
            Book Your Stay
        </a>

    </section>


    <!-- FOOTER -->
    <footer id="contact">

        <div class="footer-content">

            <div>
                <h2>Coastal<span>Villa.</span></h2>

                <p>
                    Your perfect destination for a peaceful
                    coastal holiday in Sri Lanka.
                </p>
            </div>

            <div>
                <h3>Quick Links</h3>
                <a href="index.php">Home</a>
                <a href="#rooms">Rooms</a>
                <a href="#about">About Us</a>
            </div>

            <div>
                <h3>Contact Us</h3>
                <p>Email: info@coastalvilla.com</p>
                <p>Location: Sri Lanka</p>
            </div>

        </div>

        <div class="copyright">
            © 2026 Coastal Villa. All Rights Reserved.
        </div>

    </footer>

</body>
</html>


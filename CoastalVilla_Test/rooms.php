<?php
session_start();

require_once "config/database.php";

// Get all active rooms from the database
$sql = "SELECT * FROM rooms WHERE room_status = 'Active'";
$stmt = $conn->prepare($sql);
$stmt->execute();

$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

$isLoggedIn = isset($_SESSION["user_id"]);
$userName = $isLoggedIn ? $_SESSION["user_name"] : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Our Rooms | Coastal Villa</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header class="navbar">

    <a href="index.php" class="logo">
        Coastal<span>Villa.</span>
    </a>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms</a>
        <a href="index.php#about">About</a>
        <a href="index.php#contact">Contact</a>
    </nav>

    <div class="nav-actions">

        <?php if ($isLoggedIn): ?>

            <span class="welcome-user">
                Hello, <?php echo htmlspecialchars($userName); ?>
            </span>

            <a href="logout.php" class="login-btn">Logout</a>

        <?php else: ?>

            <a href="login.php" class="login-btn">Login</a>
            <a href="register.php" class="register-btn">Sign Up</a>

        <?php endif; ?>

    </div>

</header>

<?php include "includes/availability-search.php"; ?>

<section class="rooms-section">

    <div class="section-heading">

        <p class="section-label">COMFORT & LUXURY</p>

        <h2>Our Accommodation</h2>

        <p>
            Discover the perfect room for your relaxing beach holiday.
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

                            <a href="room-details.php?id=<?php echo $room['room_id']; ?>" class="primary-btn">
                                View Details
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


<footer id="contact">

    <div class="copyright">
        © 2026 Coastal Villa. All Rights Reserved.
    </div>

</footer>

</body>
</html>

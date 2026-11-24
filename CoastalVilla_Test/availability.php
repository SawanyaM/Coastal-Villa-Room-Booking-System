<?php
session_start();
require_once "config/database.php";

$rooms = [];
$error = "";

$checkIn = $_GET["check_in"] ?? "";
$checkOut = $_GET["check_out"] ?? "";
$guests = $_GET["guests"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "GET"
    && isset($_GET["check_in"], $_GET["check_out"], $_GET["guests"])) {

    $today = date("Y-m-d");
        if (
            empty($checkIn) ||
            empty($checkOut) ||
            empty($guests) ||
            strtotime($checkIn) === false ||
            strtotime($checkOut) === false ||
            $checkIn < $today ||
            $checkOut <= $checkIn ||
            !ctype_digit((string)$guests) ||
            (int)$guests < 1 ||
            (int)$guests > 6
        ) {
        $error = "Please select valid dates and guest numbers.";
        } else {

        $sql = "
            SELECT *
            FROM rooms
            WHERE room_status = 'Active'
            AND max_guests >= :guests
            AND NOT EXISTS (
                SELECT 1
                FROM bookings
                WHERE bookings.room_id = rooms.room_id
                AND bookings.booking_status IN ('Pending', 'Confirmed')
                AND bookings.check_in < :check_out
                AND bookings.check_out > :check_in
            )
        ";

        $stmt = $conn->prepare($sql);

            $stmt->execute([
                ":guests" => (int)$guests,
                ":check_in" => $checkIn,
                ":check_out" => $checkOut
            ]);

            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$isLoggedIn = isset($_SESSION["user_id"]);
$userName = $isLoggedIn ? $_SESSION["user_name"] : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms | Coastal Villa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Coastal<span>Villa.</span></a>

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

<section class="rooms-section">

    <div class="section-heading">
        <p class="section-label">YOUR COASTAL GETAWAY</p>
        <h2>Available Rooms</h2>
    </div>

    <?php if ($error): ?>

        <p class="availability-message">
            <?php echo htmlspecialchars($error); ?>
        </p>

    <?php elseif (isset($_GET["check_in"])): ?>

        <p class="availability-message">
            Search results for
            <?php echo htmlspecialchars($checkIn); ?>
            to
            <?php echo htmlspecialchars($checkOut); ?>
            · <?php echo (int)$guests; ?> guest(s)
        </p>

        <?php if (count($rooms) > 0): ?>

            <div class="rooms-container">

                <?php foreach ($rooms as $room): ?>

                    <div class="room-card">

                        <div class="room-image">
                            <?php if (!empty($room["image"])): ?>
                                <img
                                    src="<?php echo htmlspecialchars($room["image"]); ?>"
                                    alt="<?php echo htmlspecialchars($room["room_name"]); ?>"
                                >
                            <?php else: ?>
                                <div class="room-placeholder-image">🏖️</div>
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

                            <p>
                                <strong>Facilities:</strong>
                                <?php echo htmlspecialchars($room["facilities"]); ?>
                            </p>

                            <p>
                                <strong>Maximum Guests:</strong>
                                <?php echo (int)$room["max_guests"]; ?>
                            </p>

                            <div class="room-bottom">
                                <p class="room-price">
                                    Rs. <?php echo number_format($room["price_per_night"], 2); ?>
                                    <span>/ night</span>
                                </p>

                                <a href="booking.php?room_id=<?php echo $room['room_id']; ?>&check_in=<?php echo urlencode($checkIn); ?>&check_out=<?php echo urlencode($checkOut); ?>&guests=<?php echo urlencode($guests); ?>" class="primary-btn">
                                    Book Now
                                </a>
                                </a>
                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <p class="availability-message">
                Sorry, no rooms are available for your selected dates and guest number.
            </p>

        <?php endif; ?>

    <?php else: ?>

        <p class="availability-message">
            Please select your dates and guests to search for available rooms.
        </p>

    <?php endif; ?>

    <div class="section-heading">
        <a href="rooms.php" class="primary-btn">View All Rooms</a>
    </div>

</section>

</body>
</html>

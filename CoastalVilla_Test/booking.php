
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION["user_id"];
$error = "";
$success = false;
$booking = null;

// Check whether a date is valid
function validDate($date) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    return checkdate(
        (int)substr($date, 5, 2),
        (int)substr($date, 8, 2),
        (int)substr($date, 0, 4)
    );
}

// Handle booking confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $roomId = $_POST["room_id"] ?? "";
    $checkIn = $_POST["check_in"] ?? "";
    $checkOut = $_POST["check_out"] ?? "";
    $guests = $_POST["guests"] ?? "";
    $token = $_POST["csrf_token"] ?? "";

    if (
        !hash_equals($_SESSION["csrf_token"] ?? "", $token) ||
        !ctype_digit((string)$roomId) ||
        !ctype_digit((string)$guests) ||
        (int)$guests < 1 ||
        (int)$guests > 6 ||
        !validDate($checkIn) ||
        !validDate($checkOut) ||
        $checkIn < date("Y-m-d") ||
        $checkOut <= $checkIn
    ) {
        $error = "Invalid booking details. Please search again.";
    } else {
        try {
            $conn->beginTransaction();

            // Lock the selected room while checking availability
            $stmt = $conn->prepare(
                "SELECT * FROM rooms
                 WHERE room_id = :room_id
                 AND room_status = 'Active'
                 FOR UPDATE"
            );

            $stmt->execute([
                ":room_id" => (int)$roomId
            ]);

            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room || (int)$guests > (int)$room["max_guests"]) {
                throw new Exception("This room is not suitable for your booking.");
            }

            // Check overlapping bookings
            $stmt = $conn->prepare(
                "SELECT COUNT(*) FROM bookings
                 WHERE room_id = :room_id
                 AND booking_status IN ('Pending', 'Confirmed')
                 AND check_in < :check_out
                 AND check_out > :check_in"
            );

            $stmt->execute([
                ":room_id" => (int)$roomId,
                ":check_in" => $checkIn,
                ":check_out" => $checkOut
            ]);

            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Sorry, this room is no longer available.");
            }

            // Calculate total amount on the server
            $date1 = new DateTime($checkIn);
            $date2 = new DateTime($checkOut);
            $nights = $date1->diff($date2)->days;

            $totalAmount = $nights * (float)$room["price_per_night"];

            // Save booking in the database
            $stmt = $conn->prepare(
                "INSERT INTO bookings
                (user_id, room_id, check_in, check_out, total_amount, booking_status)
                VALUES
                (:user_id, :room_id, :check_in, :check_out, :total_amount, 'Pending')"
            );

            $stmt->execute([
                ":user_id" => $userId,
                ":room_id" => (int)$roomId,
                ":check_in" => $checkIn,
                ":check_out" => $checkOut,
                ":total_amount" => $totalAmount
            ]);

            $bookingId = $conn->lastInsertId();

            $conn->commit();

            // Prevent duplicate submission on page refresh
            header("Location: booking.php?booking_success=" . $bookingId);
            exit();

        } catch (Exception $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}

// Show successful booking details
if (isset($_GET["booking_success"]) &&
    ctype_digit((string)$_GET["booking_success"])) {

    $stmt = $conn->prepare(
        "SELECT bookings.*, rooms.room_name, rooms.room_type
         FROM bookings
         JOIN rooms ON bookings.room_id = rooms.room_id
         WHERE bookings.booking_id = :booking_id
         AND bookings.user_id = :user_id"
    );

    $stmt->execute([
        ":booking_id" => (int)$_GET["booking_success"],
        ":user_id" => $userId
    ]);

    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        $success = true;
    }
}

// Display booking form details
if (!$success && $_SERVER["REQUEST_METHOD"] === "GET") {

    $roomId = $_GET["room_id"] ?? "";
    $checkIn = $_GET["check_in"] ?? "";
    $checkOut = $_GET["check_out"] ?? "";
    $guests = $_GET["guests"] ?? "";

    if (
        !ctype_digit((string)$roomId) ||
        !ctype_digit((string)$guests) ||
        (int)$guests < 1 ||
        (int)$guests > 6 ||
        !validDate($checkIn) ||
        !validDate($checkOut) ||
        $checkIn < date("Y-m-d") ||
        $checkOut <= $checkIn
    ) {
        die("Invalid booking details. Please search again.");
    }

    $stmt = $conn->prepare(
        "SELECT * FROM rooms
         WHERE room_id = :room_id
         AND room_status = 'Active'"
    );

    $stmt->execute([
        ":room_id" => (int)$roomId
    ]);

    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room || (int)$guests > (int)$room["max_guests"]) {
        die("Selected room is not suitable for your booking.");
    }

    // Check availability before displaying the form
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM bookings
         WHERE room_id = :room_id
         AND booking_status IN ('Pending', 'Confirmed')
         AND check_in < :check_out
         AND check_out > :check_in"
    );

    $stmt->execute([
        ":room_id" => (int)$roomId,
        ":check_in" => $checkIn,
        ":check_out" => $checkOut
    ]);

    if ($stmt->fetchColumn() > 0) {
        die("Sorry, this room is no longer available. Please search again.");
    }

    $nights = (new DateTime($checkIn))->diff(
        new DateTime($checkOut)
    )->days;

    $totalAmount = $nights * (float)$room["price_per_night"];
}

// Create a security token for the form
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$userName = $_SESSION["user_name"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Room | Coastal Villa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Coastal<span>Villa.</span></a>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms</a>
    </nav>

    <div class="nav-actions">
        <span class="welcome-user">
            Hello, <?php echo htmlspecialchars($userName); ?>
        </span>
        <a href="logout.php" class="login-btn">Logout</a>
    </div>
</header>

<section class="booking-section">

    <?php if ($success): ?>

        <div class="booking-card">
            <h2>Booking Successful!</h2>

            <p>Your booking has been saved successfully.</p>

            <p>
                <strong>Booking ID:</strong>
                <?php echo (int)$booking["booking_id"]; ?>
            </p>

            <p>
                <strong>Room:</strong>
                <?php echo htmlspecialchars($booking["room_name"]); ?>
            </p>

            <p>
                <strong>Check-in:</strong>
                <?php echo htmlspecialchars($booking["check_in"]); ?>
            </p>

            <p>
                <strong>Check-out:</strong>
                <?php echo htmlspecialchars($booking["check_out"]); ?>
            </p>

            <p>
                <strong>Total Amount:</strong>
                Rs. <?php echo number_format($booking["total_amount"], 2); ?>
            </p>

            <p>
                <strong>Booking Status:</strong>
                <?php echo htmlspecialchars($booking["booking_status"]); ?>
            </p>

            <a href="index.php" class="primary-btn">Back to Home</a>
        </div>

    <?php else: ?>

        <div class="section-heading">
            <p class="section-label">RESERVE YOUR STAY</p>
            <h2>Complete Your Booking</h2>
            <p>Please review your booking details.</p>
        </div>

        <?php if ($error): ?>
            <p class="availability-message">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <div class="booking-card">

            <h3><?php echo htmlspecialchars($room["room_name"]); ?></h3>

            <p>
                <strong>Room Type:</strong>
                <?php echo htmlspecialchars($room["room_type"]); ?>
            </p>

            <p>
                <strong>Check-in:</strong>
                <?php echo htmlspecialchars($checkIn); ?>
            </p>

            <p>
                <strong>Check-out:</strong>
                <?php echo htmlspecialchars($checkOut); ?>
            </p>

            <p>
                <strong>Guests:</strong>
                <?php echo (int)$guests; ?>
            </p>

            <p>
                <strong>Number of Nights:</strong>
                <?php echo $nights; ?>
            </p>

            <p>
                <strong>Price per Night:</strong>
                Rs. <?php echo number_format($room["price_per_night"], 2); ?>
            </p>

            <div class="booking-total">
                <strong>Total Amount:</strong>
                Rs. <?php echo number_format($totalAmount, 2); ?>
            </div>

            <form method="POST" action="booking.php">

                <input type="hidden" name="room_id"
                       value="<?php echo (int)$roomId; ?>">

                <input type="hidden" name="check_in"
                       value="<?php echo htmlspecialchars($checkIn); ?>">

                <input type="hidden" name="check_out"
                       value="<?php echo htmlspecialchars($checkOut); ?>">

                <input type="hidden" name="guests"
                       value="<?php echo (int)$guests; ?>">

                <input type="hidden" name="csrf_token"
                       value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

                <button type="submit" class="primary-btn">
                    Confirm Booking
                </button>

            </form>

        </div>

    <?php endif; ?>

</section>

</body>
</html>

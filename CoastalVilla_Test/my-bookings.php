
<?php
session_start();

require_once "config/database.php";

// Check whether the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION["user_id"];

// Generate CSRF token
if (empty($_SESSION["my_bookings_token"])) {
    $_SESSION["my_bookings_token"] = bin2hex(random_bytes(32));
}

// Handle booking cancellation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel_booking"])) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["my_bookings_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please try again.");
    }

    $bookingId = $_POST["booking_id"] ?? "";

    if (ctype_digit($bookingId)) {

        $cancelSql = "UPDATE bookings
                      SET booking_status = 'Cancelled'
                      WHERE booking_id = ?
                      AND user_id = ?
                      AND booking_status = 'Pending'";

        $cancelStmt = $conn->prepare($cancelSql);
        $cancelStmt->execute([$bookingId, $userId]);

        if ($cancelStmt->rowCount() > 0) {
            $_SESSION["booking_message"] = "Your booking has been cancelled successfully!";
        } else {
            $_SESSION["booking_message"] = "Booking could not be cancelled.";
        }
    }

    header("Location: my-bookings.php");
    exit();
}

// Get success or cancellation message
$message = $_SESSION["booking_message"] ?? "";
unset($_SESSION["booking_message"]);

// Get bookings belonging to the logged-in user only
$sql = "SELECT bookings.*, rooms.room_name, rooms.room_type, rooms.image
        FROM bookings
        INNER JOIN rooms ON bookings.room_id = rooms.room_id
        WHERE bookings.user_id = ?
        ORDER BY bookings.booking_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Bookings - Coastal Villa</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header>
    <nav class="navbar">
        <h2>Coastal Villa</h2>

        <div>
            <a href="index.php">Home</a>
            <a href="rooms.php">Rooms</a>
            <a href="my-bookings.php" class="active">My Bookings</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
</header>

<main class="my-bookings-page">

    <section class="my-bookings-heading">
        <span class="booking-eyebrow">YOUR STAY, YOUR WAY</span>
        <h1>My Bookings</h1>
        <p>View and manage your reservations at Coastal Villa.</p>
    </section>

    <?php if ($message !== ""): ?>
        <div class="booking-message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (count($bookings) > 0): ?>

        <div class="my-bookings-list">

            <?php foreach ($bookings as $booking): ?>

                <?php
                    $status = $booking["booking_status"];

                    $statusClass = "status-pending";

                    if ($status === "Confirmed") {
                        $statusClass = "status-confirmed";
                    } elseif ($status === "Cancelled") {
                        $statusClass = "status-cancelled";
                    }
                ?>

                <article class="my-booking-card">

                    <div class="my-booking-image">
                        <?php if (!empty($booking["image"])): ?>
                            <img
                                src="<?php echo htmlspecialchars($booking["image"]); ?>"
                                alt="<?php echo htmlspecialchars($booking["room_name"]); ?>">
                        <?php else: ?>
                            <div class="booking-image-placeholder">
                                Coastal Villa
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="my-booking-content">

                        <div class="booking-card-top">
                            <div>
                                <span class="booking-room-type">
                                    <?php echo htmlspecialchars($booking["room_type"]); ?>
                                </span>

                                <h2>
                                    <?php echo htmlspecialchars($booking["room_name"]); ?>
                                </h2>
                            </div>

                            <span class="booking-status <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </div>

                        <div class="booking-id">
                            Booking ID: #<?php echo (int)$booking["booking_id"]; ?>
                        </div>

                        <div class="booking-date-grid">

                            <div class="booking-date-item">
                                <span>CHECK-IN</span>
                                <strong>
                                    <?php echo htmlspecialchars($booking["check_in"]); ?>
                                </strong>
                            </div>

                            <div class="booking-date-item">
                                <span>CHECK-OUT</span>
                                <strong>
                                    <?php echo htmlspecialchars($booking["check_out"]); ?>
                                </strong>
                            </div>

                        </div>

                        <div class="booking-card-bottom">

                            <div class="booking-amount">
                                <span>Total Amount</span>
                                <strong>
                                    Rs. <?php echo number_format($booking["total_amount"], 2); ?>
                                </strong>
                            </div>

                            <?php if ($status === "Pending"): ?>

                                <form method="POST"
                                      action="my-bookings.php"
                                      onsubmit="return confirm('Are you sure you want to cancel this booking?');">

                                    <input type="hidden"
                                           name="booking_id"
                                           value="<?php echo (int)$booking["booking_id"]; ?>">

                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?php echo htmlspecialchars($_SESSION["my_bookings_token"]); ?>">

                                    <button type="submit"
                                            name="cancel_booking"
                                            class="cancel-btn">
                                        Cancel Booking
                                    </button>

                                </form>

                            <?php endif; ?>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="no-bookings-card">
            <h2>No Bookings Yet</h2>
            <p>You haven't made any reservations. Explore our rooms and plan your next getaway.</p>

            <a href="rooms.php" class="primary-btn">
                Explore Rooms
            </a>
        </div>

    <?php endif; ?>

</main>

</body>
</html>

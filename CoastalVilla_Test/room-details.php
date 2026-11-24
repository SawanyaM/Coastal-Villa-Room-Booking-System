
<?php
session_start();

require_once "config/database.php";

$isLoggedIn = isset($_SESSION["user_id"]);
$userName = $isLoggedIn ? $_SESSION["user_name"] : "";

// Get room ID from URL
if (!isset($_GET["id"])) {
    header("Location: rooms.php");
    exit();
}

$roomId = (int) $_GET["id"];

// Get selected room details
$sql = "SELECT * FROM rooms WHERE room_id = ? AND room_status = 'Active'";
$stmt = $conn->prepare($sql);
$stmt->execute([$roomId]);

$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    header("Location: rooms.php");
    exit();
}

$pricePerNight = (float)$room["price_per_night"];
$today = date("Y-m-d");
$tomorrow = date("Y-m-d", strtotime("+1 day"));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($room["room_name"]); ?> | Coastal Villa</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .booking-form {
            margin-top: 25px;
        }

        .booking-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .booking-form input,
        .booking-form select {
            display: block;
            padding: 12px;
            width: 100%;
            margin-top: 8px;
            box-sizing: border-box;
        }

        .booking-summary {
            background: #f5f7f3;
            padding: 18px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .booking-summary p {
            margin: 10px 0;
        }

        .booking-message {
            margin-top: 12px;
            font-size: 14px;
        }

        .error-message {
            color: #c0392b;
        }

        .success-message {
            color: #247344;
        }

        .total-price {
            font-size: 20px;
            font-weight: bold;
            color: #28513e;
        }
    </style>
</head>

<body>

<header class="navbar">

    <div class="logo">Coastal Villa</div>

    <nav>
        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms</a>
        <a href="index.php#about">About</a>
        <a href="index.php#contact">Contact</a>
    </nav>

    <div>
        <?php if ($isLoggedIn): ?>
            <span>Hello, <?php echo htmlspecialchars($userName); ?></span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Sign Up</a>
        <?php endif; ?>
    </div>

</header>

<section class="rooms-section">

    <div class="section-heading">
        <p class="section-label">COMFORT & LUXURY</p>

        <h2><?php echo htmlspecialchars($room["room_name"]); ?></h2>

        <p>Discover your perfect stay at Coastal Villa.</p>
    </div>

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

            <h3><?php echo htmlspecialchars($room["room_name"]); ?></h3>

            <p><?php echo htmlspecialchars($room["description"]); ?></p>

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
                    Rs. <?php echo number_format($pricePerNight, 2); ?>
                    <span>/ night</span>
                </p>

            </div>

            <div style="margin-top: 25px;">

                <h3>Book This Room</h3>

                <form
                    action="booking.php"
                    method="GET"
                    id="bookingForm"
                    class="booking-form"
                >

                    <input
                        type="hidden"
                        name="room_id"
                        value="<?php echo (int)$room["room_id"]; ?>"
                    >

                    <div style="margin: 15px 0;">

                        <label for="check_in">Check-in Date</label>

                        <input
                            type="date"
                            id="check_in"
                            name="check_in"
                            min="<?php echo $today; ?>"
                            required
                        >

                    </div>

                    <div style="margin: 15px 0;">

                        <label for="check_out">Check-out Date</label>

                        <input
                            type="date"
                            id="check_out"
                            name="check_out"
                            min="<?php echo $tomorrow; ?>"
                            required
                        >

                    </div>

                    <div style="margin: 15px 0;">

                        <label for="guests">Number of Guests</label>

                        <select
                            id="guests"
                            name="guests"
                            required
                        >

                            <?php for ($i = 1; $i <= (int)$room["max_guests"]; $i++): ?>

                                <option value="<?php echo $i; ?>">
                                    <?php echo $i; ?> Guest(s)
                                </option>

                            <?php endfor; ?>

                        </select>

                    </div>

                    <!-- Booking cost calculation -->
                    <div class="booking-summary">

                        <h3>Booking Summary</h3>

                        <p>
                            Price per Night:
                            <strong>
                                Rs. <?php echo number_format($pricePerNight, 2); ?>
                            </strong>
                        </p>

                        <p>
                            Number of Nights:
                            <strong id="numberOfNights">0</strong>
                        </p>

                        <p>
                            Number of Guests:
                            <strong id="selectedGuests">1</strong>
                        </p>

                        <p class="total-price">
                            Estimated Total:
                            <span id="estimatedTotal">Rs. 0.00</span>
                        </p>

                        <p id="bookingMessage" class="booking-message">
                            Please select your check-in and check-out dates.
                        </p>

                    </div>

                    <button type="submit" class="primary-btn">
                        Book Now
                    </button>

                </form>

            </div>

        </div>

    </div>

    <p style="text-align:center; margin-top:30px;">
        <a href="rooms.php">← Back to Rooms</a>
    </p>

</section>

<script>

// Get booking form elements
const bookingForm = document.getElementById("bookingForm");

const checkIn = document.getElementById("check_in");
const checkOut = document.getElementById("check_out");
const guests = document.getElementById("guests");

const numberOfNights = document.getElementById("numberOfNights");
const selectedGuests = document.getElementById("selectedGuests");
const estimatedTotal = document.getElementById("estimatedTotal");
const bookingMessage = document.getElementById("bookingMessage");

// Room price from PHP
const pricePerNight = <?php echo json_encode($pricePerNight); ?>;

// Calculate booking details
function calculateBooking() {

    const checkInDate = checkIn.value;
    const checkOutDate = checkOut.value;

    const guestCount = parseInt(guests.value, 10);

    // Update guest count using DOM manipulation
    selectedGuests.textContent = guestCount;

    // Reset calculation
    numberOfNights.textContent = "0";
    estimatedTotal.textContent = "Rs. 0.00";

    if (!checkInDate || !checkOutDate) {

        bookingMessage.textContent =
            "Please select your check-in and check-out dates.";

        bookingMessage.className = "booking-message";

        return false;
    }

    // Convert dates into timestamps
    const startDate = new Date(checkInDate + "T00:00:00");
    const endDate = new Date(checkOutDate + "T00:00:00");

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Validate check-in date
    if (startDate < today) {

        bookingMessage.textContent =
            "Check-in date cannot be in the past.";

        bookingMessage.className =
            "booking-message error-message";

        return false;
    }

    // Validate check-out date
    if (endDate <= startDate) {

        bookingMessage.textContent =
            "Check-out date must be after check-in date.";

        bookingMessage.className =
            "booking-message error-message";

        return false;
    }

    // Calculate number of nights
    const difference = endDate - startDate;

    const nights = difference / (1000 * 60 * 60 * 24);

    // Calculate estimated booking cost
    const total = nights * pricePerNight;

    // Display calculated values using DOM manipulation
    numberOfNights.textContent = nights;

    estimatedTotal.textContent =
        "Rs. " + total.toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    bookingMessage.textContent =
        "Your booking estimate has been calculated successfully.";

    bookingMessage.className =
        "booking-message success-message";

    return true;
}

// Update minimum check-out date when check-in changes
checkIn.addEventListener("change", function() {

    if (checkIn.value) {

        const selectedDate = new Date(checkIn.value + "T00:00:00");

        selectedDate.setDate(selectedDate.getDate() + 1);

        const year = selectedDate.getFullYear();
        const month = String(selectedDate.getMonth() + 1).padStart(2, "0");
        const day = String(selectedDate.getDate()).padStart(2, "0");

        const minimumCheckout =
            year + "-" + month + "-" + day;

        checkOut.min = minimumCheckout;

        if (checkOut.value && checkOut.value <= checkIn.value) {
            checkOut.value = "";
        }
    }

    calculateBooking();

});

// Recalculate when check-out date changes
checkOut.addEventListener("change", calculateBooking);

// Update guest count when selection changes
guests.addEventListener("change", calculateBooking);

// Validate form before submitting
bookingForm.addEventListener("submit", function(event) {

    if (!checkIn.value || !checkOut.value || !guests.value) {

        event.preventDefault();

        bookingMessage.textContent =
            "Please complete all booking fields.";

        bookingMessage.className =
            "booking-message error-message";

        return;
    }

    if (!calculateBooking()) {

        event.preventDefault();

        alert("Please select valid booking dates.");

        return;
    }

});

</script>

</body>
</html>

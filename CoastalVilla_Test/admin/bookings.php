
<?php
session_start();

require_once "../config/database.php";

// Admin access protection
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Update booking status
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["booking_id"], $_POST["status"])) {

    $bookingId = (int) $_POST["booking_id"];
    $status = $_POST["status"];

    if (in_array($status, ["Confirmed", "Cancelled"], true)) {

        $updateSql = "UPDATE bookings
                      SET booking_status = ?
                      WHERE booking_id = ?
                      AND booking_status = 'Pending'";

        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$status, $bookingId]);
    }

    header("Location: bookings.php");
    exit();
}

// Get all bookings with customer and room details
$sql = "SELECT bookings.*, users.name, users.email,
               rooms.room_name, rooms.room_type
        FROM bookings
        INNER JOIN users ON bookings.user_id = users.user_id
        INNER JOIN rooms ON bookings.room_id = rooms.room_id
        ORDER BY bookings.booking_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Bookings | Coastal Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .booking-management {
            padding: 40px 5%;
            overflow-x: auto;
        }

        .booking-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 30px;
            min-width: 900px;
        }

        .booking-table th,
        .booking-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .booking-table th {
            background: #28513e;
            color: white;
        }

        .booking-table tr:hover {
            background: #f5f7f3;
        }

        .status {
            font-weight: bold;
        }

        .pending { color: #d49b45; }
        .confirmed { color: green; }
        .cancelled { color: red; }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 3px;
        }

        .confirm-btn {
            background: #28513e;
            color: white;
        }

        .cancel-btn {
            background: #c0392b;
            color: white;
        }
    </style>
</head>

<body>

<header class="navbar">

    <a href="../index.php" class="logo">
        Coastal<span>Villa.</span>
    </a>

    <div class="nav-actions">
        <a href="dashboard.php" class="login-btn">Dashboard</a>
        <a href="../logout.php" class="login-btn">Logout</a>
    </div>

</header>

<section class="booking-management">

    <div class="section-heading">
        <p class="section-label">ADMIN PANEL</p>
        <h2>Manage Bookings</h2>
        <p>View and manage all customer bookings.</p>
    </div>

    <table class="booking-table">

        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Room</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

            <?php if (count($bookings) > 0): ?>

                <?php foreach ($bookings as $booking): ?>

                    <tr>
                        <td><?php echo $booking["booking_id"]; ?></td>

                        <td><?php echo htmlspecialchars($booking["name"]); ?></td>

                        <td><?php echo htmlspecialchars($booking["email"]); ?></td>

                        <td><?php echo htmlspecialchars($booking["room_name"]); ?></td>

                        <td><?php echo htmlspecialchars($booking["check_in"]); ?></td>

                        <td><?php echo htmlspecialchars($booking["check_out"]); ?></td>

                        <td>Rs. <?php echo number_format($booking["total_amount"], 2); ?></td>

                        <td>
                            <span class="status <?php echo strtolower($booking["booking_status"]); ?>">
                                <?php echo htmlspecialchars($booking["booking_status"]); ?>
                            </span>
                        </td>

                        <td>

                            <?php if ($booking["booking_status"] === "Pending"): ?>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id"
                                           value="<?php echo $booking["booking_id"]; ?>">

                                    <button type="submit" name="status" value="Confirmed"
                                            class="action-btn confirm-btn">
                                        Confirm
                                    </button>
                                </form>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id"
                                           value="<?php echo $booking["booking_id"]; ?>">

                                    <button type="submit" name="status" value="Cancelled"
                                            class="action-btn cancel-btn">
                                        Cancel
                                    </button>
                                </form>

                            <?php else: ?>

                                No Action

                            <?php endif; ?>

                        </td>
                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="9" style="text-align:center;">
                        No bookings found.
                    </td>
                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</section>

</body>
</html>

<?php
session_start();

require_once "../config/database.php";

// Check whether the user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Get total rooms
$roomStmt = $conn->prepare("SELECT COUNT(*) FROM rooms");
$roomStmt->execute();
$totalRooms = $roomStmt->fetchColumn();

// Get total bookings
$bookingStmt = $conn->prepare("SELECT COUNT(*) FROM bookings");
$bookingStmt->execute();
$totalBookings = $bookingStmt->fetchColumn();

// Get pending bookings
$pendingStmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Pending'");
$pendingStmt->execute();
$pendingBookings = $pendingStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Coastal Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .admin-dashboard {
            padding: 50px 6%;
            min-height: 70vh;
        }

        .admin-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-top: 35px;
        }

        .admin-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
        }

        .admin-card h3 {
            color: #28513e;
            margin-bottom: 15px;
        }

        .admin-card p {
            font-size: 32px;
            font-weight: bold;
            color: #d49b45;
        }

        .admin-welcome {
            color: #738078;
        }
    </style>
</head>

<body>

<header class="navbar">

    <a href="../index.php" class="logo">
        Coastal<span>Villa.</span>
    </a>

    <div class="nav-actions">
        <span class="welcome-user">
            Admin: <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
        </span>

        <a href="../logout.php" class="login-btn">Logout</a>
    </div>

</header>

<section class="admin-dashboard">

    <div class="section-heading">
        <p class="section-label">ADMIN PANEL</p>

        <h2>Admin Dashboard</h2>

        <p class="admin-welcome">
            Welcome to Coastal Villa Management System.
        </p>
    </div>

    <div class="admin-cards">

        <div class="admin-card">
            <h3>Total Rooms</h3>
            <p><?php echo $totalRooms; ?></p>
        </div>

        <div class="admin-card">
            <h3>Total Bookings</h3>
            <p><?php echo $totalBookings; ?></p>
        </div>

        <div class="admin-card">
            <h3>Pending Bookings</h3>
            <p><?php echo $pendingBookings; ?></p>
        </div>

    </div>

    <div style="text-align:center; margin-top:40px;">

    <a href="bookings.php" class="primary-btn">
        Manage Bookings
    </a>

    </div>

    <a href="rooms.php" class="primary-btn">
    Manage Rooms
    </a>

</section>

</body>
</html>

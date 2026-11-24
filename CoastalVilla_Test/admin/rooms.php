
<?php
session_start();

require_once "../config/database.php";

// Admin access protection
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Create CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$error = "";

// Get message from URL
if (isset($_GET["message"])) {
    $message = $_GET["message"];
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // CSRF protection
    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please go back and try again.");
    }

    $action = $_POST["action"] ?? "";

    // ADD ROOM
    if ($action === "add") {

        $roomName = trim($_POST["room_name"] ?? "");
        $roomType = trim($_POST["room_type"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $facilities = trim($_POST["facilities"] ?? "");
        $price = filter_var($_POST["price_per_night"] ?? "", FILTER_VALIDATE_FLOAT);
        $maxGuests = filter_var($_POST["max_guests"] ?? "", FILTER_VALIDATE_INT);
        $image = trim($_POST["image"] ?? "");
        $status = $_POST["room_status"] ?? "Active";

        if (
            $roomName === "" ||
            $roomType === "" ||
            $description === "" ||
            $price === false || $price <= 0 ||
            $maxGuests === false || $maxGuests < 1 ||
            !in_array($status, ["Active", "Inactive"], true)
        ) {
            $error = "Please enter valid room information.";
        } else {

            $sql = "INSERT INTO rooms
                    (room_name, room_type, description, facilities,
                     price_per_night, max_guests, image, room_status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                $roomName,
                $roomType,
                $description,
                $facilities,
                $price,
                $maxGuests,
                $image,
                $status
            ]);

            header("Location: rooms.php?message=Room added successfully!");
            exit();
        }
    }

    // UPDATE ROOM
    elseif ($action === "edit") {

        $roomId = (int)($_POST["room_id"] ?? 0);

        $roomName = trim($_POST["room_name"] ?? "");
        $roomType = trim($_POST["room_type"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $facilities = trim($_POST["facilities"] ?? "");
        $price = filter_var($_POST["price_per_night"] ?? "", FILTER_VALIDATE_FLOAT);
        $maxGuests = filter_var($_POST["max_guests"] ?? "", FILTER_VALIDATE_INT);
        $image = trim($_POST["image"] ?? "");
        $status = $_POST["room_status"] ?? "Active";

        if (
            $roomId <= 0 ||
            $roomName === "" ||
            $roomType === "" ||
            $description === "" ||
            $price === false || $price <= 0 ||
            $maxGuests === false || $maxGuests < 1 ||
            !in_array($status, ["Active", "Inactive"], true)
        ) {
            $error = "Please enter valid room information.";
        } else {

            $sql = "UPDATE rooms
                    SET room_name = ?,
                        room_type = ?,
                        description = ?,
                        facilities = ?,
                        price_per_night = ?,
                        max_guests = ?,
                        image = ?,
                        room_status = ?
                    WHERE room_id = ?";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                $roomName,
                $roomType,
                $description,
                $facilities,
                $price,
                $maxGuests,
                $image,
                $status,
                $roomId
            ]);

            header("Location: rooms.php?message=Room updated successfully!");
            exit();
        }
    }

    // DELETE ROOM
    elseif ($action === "delete") {

        $roomId = (int)($_POST["room_id"] ?? 0);

        // Check whether this room has booking records
        $checkSql = "SELECT COUNT(*) FROM bookings WHERE room_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$roomId]);

        $bookingCount = (int)$checkStmt->fetchColumn();

        if ($roomId <= 0) {
            $error = "Invalid room selected.";
        } elseif ($bookingCount > 0) {

            header("Location: rooms.php?message=Cannot delete this room because booking records exist. Deactivate it instead.");
            exit();

        } else {

            $deleteSql = "DELETE FROM rooms WHERE room_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->execute([$roomId]);

            header("Location: rooms.php?message=Room deleted successfully!");
            exit();
        }
    }

    // ACTIVATE / DEACTIVATE
    elseif ($action === "status") {

        $roomId = (int)($_POST["room_id"] ?? 0);
        $roomStatus = $_POST["room_status"] ?? "";

        if (
            $roomId > 0 &&
            in_array($roomStatus, ["Active", "Inactive"], true)
        ) {

            $updateSql = "UPDATE rooms SET room_status = ? WHERE room_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([$roomStatus, $roomId]);

            header("Location: rooms.php?message=Room status updated successfully!");
            exit();
        }
    }
}

// Check whether Add or Edit form should be displayed
$formAction = $_GET["action"] ?? "";
$editRoom = null;

if ($formAction === "edit" && isset($_GET["id"])) {

    $roomId = (int)$_GET["id"];

    $editSql = "SELECT * FROM rooms WHERE room_id = ?";
    $editStmt = $conn->prepare($editSql);
    $editStmt->execute([$roomId]);

    $editRoom = $editStmt->fetch(PDO::FETCH_ASSOC);

    if (!$editRoom) {
        header("Location: rooms.php?message=Room not found.");
        exit();
    }
}

// Get all rooms
$sql = "SELECT * FROM rooms ORDER BY room_id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();

$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for safe output
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Rooms | Coastal Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .manage-rooms {
            padding: 40px 5%;
            overflow-x: auto;
        }

        .manage-room-table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
            background: white;
            margin-top: 25px;
        }

        .manage-room-table th,
        .manage-room-table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .manage-room-table th {
            background: #28513e;
            color: white;
        }

        .manage-room-table tr:hover {
            background: #f5f7f3;
        }

        .room-status {
            font-weight: bold;
        }

        .active-status {
            color: green;
        }

        .inactive-status {
            color: red;
        }

        .room-action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            margin: 3px;
        }

        .deactivate-btn {
            background: #c0392b;
        }

        .activate-btn {
            background: #28513e;
        }

        .edit-btn {
            background: #2874a6;
            text-decoration: none;
            display: inline-block;
        }

        .delete-btn {
            background: #922b21;
        }

        .add-room-btn {
            display: inline-block;
            background: #28513e;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 20px;
        }

        .room-form {
            background: white;
            max-width: 750px;
            padding: 25px;
            margin: 30px 0;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .room-form h3 {
            color: #28513e;
            margin-bottom: 20px;
        }

        .room-form label {
            display: block;
            margin: 14px 0 7px;
            font-weight: 600;
        }

        .room-form input,
        .room-form textarea,
        .room-form select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .room-form textarea {
            min-height: 90px;
            resize: vertical;
        }

        .save-btn {
            background: #28513e;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .cancel-btn {
            display: inline-block;
            color: #28513e;
            margin-left: 12px;
            text-decoration: none;
        }

        .message {
            background: #eaf7ef;
            color: #247344;
            padding: 12px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .error-message {
            background: #fdecea;
            color: #b42318;
            padding: 12px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .action-forms {
            display: inline-block;
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

<section class="manage-rooms">

    <div class="section-heading">
        <p class="section-label">ADMIN PANEL</p>
        <h2>Manage Rooms</h2>
        <p>View and manage Coastal Villa rooms.</p>
    </div>

    <?php if ($message !== ""): ?>
        <div class="message"><?php echo e($message); ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="error-message"><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if ($formAction === "add" || $formAction === "edit"): ?>

        <div class="room-form">

            <h3>
                <?php echo $formAction === "add" ? "Add New Room" : "Edit Room"; ?>
            </h3>

            <form method="POST" action="rooms.php">

                <input type="hidden" name="csrf_token"
                       value="<?php echo e($_SESSION["csrf_token"]); ?>">

                <input type="hidden" name="action"
                       value="<?php echo e($formAction); ?>">

                <?php if ($formAction === "edit"): ?>
                    <input type="hidden" name="room_id"
                           value="<?php echo (int)$editRoom["room_id"]; ?>">
                <?php endif; ?>

                <label>Room Name</label>
                <input type="text" name="room_name" required
                       value="<?php echo e($editRoom["room_name"] ?? ""); ?>">

                <label>Room Type</label>
                <input type="text" name="room_type" required
                       placeholder="Example: Deluxe, Family, Luxury"
                       value="<?php echo e($editRoom["room_type"] ?? ""); ?>">

                <label>Description</label>
                <textarea name="description" required><?php
                    echo e($editRoom["description"] ?? "");
                ?></textarea>

                <label>Facilities</label>
                <textarea name="facilities"
                          placeholder="Example: WiFi, Air Conditioning, Sea View"><?php
                    echo e($editRoom["facilities"] ?? "");
                ?></textarea>

                <label>Price Per Night (Rs.)</label>
                <input type="number" name="price_per_night"
                       min="1" step="0.01" required
                       value="<?php echo e($editRoom["price_per_night"] ?? ""); ?>">

                <label>Maximum Guests</label>
                <input type="number" name="max_guests"
                       min="1" required
                       value="<?php echo e($editRoom["max_guests"] ?? ""); ?>">

                <label>Room Image Path</label>
                <input type="text" name="image"
                       placeholder="Example: assets/images/deluxe.jpg"
                       value="<?php echo e($editRoom["image"] ?? ""); ?>">

                <label>Room Status</label>
                <select name="room_status">
                    <option value="Active"
                        <?php echo (($editRoom["room_status"] ?? "Active") === "Active") ? "selected" : ""; ?>>
                        Active
                    </option>

                    <option value="Inactive"
                        <?php echo (($editRoom["room_status"] ?? "") === "Inactive") ? "selected" : ""; ?>>
                        Inactive
                    </option>
                </select>

                <button type="submit" class="save-btn">
                    <?php echo $formAction === "add" ? "Add Room" : "Update Room"; ?>
                </button>

                <a href="rooms.php" class="cancel-btn">Cancel</a>

            </form>

        </div>

    <?php else: ?>

        <a href="rooms.php?action=add" class="add-room-btn">
            + Add New Room
        </a>

    <?php endif; ?>

    <table class="manage-room-table">

        <thead>
            <tr>
                <th>Room ID</th>
                <th>Room Name</th>
                <th>Room Type</th>
                <th>Price / Night</th>
                <th>Maximum Guests</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

            <?php if (count($rooms) > 0): ?>

                <?php foreach ($rooms as $room): ?>

                    <tr>

                        <td><?php echo (int)$room["room_id"]; ?></td>

                        <td><?php echo e($room["room_name"]); ?></td>

                        <td><?php echo e($room["room_type"]); ?></td>

                        <td>Rs. <?php echo number_format($room["price_per_night"], 2); ?></td>

                        <td><?php echo (int)$room["max_guests"]; ?></td>

                        <td>
                            <span class="room-status <?php echo strtolower(e($room["room_status"])); ?>-status">
                                <?php echo e($room["room_status"]); ?>
                            </span>
                        </td>

                        <td>

                            <a href="rooms.php?action=edit&id=<?php echo (int)$room["room_id"]; ?>"
                               class="room-action-btn edit-btn">
                                Edit
                            </a>

                            <?php if ($room["room_status"] === "Active"): ?>

                                <form method="POST" class="action-forms">

                                    <input type="hidden" name="csrf_token"
                                           value="<?php echo e($_SESSION["csrf_token"]); ?>">

                                    <input type="hidden" name="action" value="status">

                                    <input type="hidden" name="room_id"
                                           value="<?php echo (int)$room["room_id"]; ?>">

                                    <button type="submit" name="room_status"
                                            value="Inactive"
                                            class="room-action-btn deactivate-btn">
                                        Deactivate
                                    </button>

                                </form>

                            <?php else: ?>

                                <form method="POST" class="action-forms">

                                    <input type="hidden" name="csrf_token"
                                           value="<?php echo e($_SESSION["csrf_token"]); ?>">

                                    <input type="hidden" name="action" value="status">

                                    <input type="hidden" name="room_id"
                                           value="<?php echo (int)$room["room_id"]; ?>">

                                    <button type="submit" name="room_status"
                                            value="Active"
                                            class="room-action-btn activate-btn">
                                        Activate
                                    </button>

                                </form>

                            <?php endif; ?>

                            <form method="POST" class="action-forms"
                                  onsubmit="return confirm('Are you sure you want to delete this room?');">

                                <input type="hidden" name="csrf_token"
                                       value="<?php echo e($_SESSION["csrf_token"]); ?>">

                                <input type="hidden" name="action" value="delete">

                                <input type="hidden" name="room_id"
                                       value="<?php echo (int)$room["room_id"]; ?>">

                                <button type="submit"
                                        class="room-action-btn delete-btn">
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="7">No rooms found.</td>
                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</section>

</body>
</html>

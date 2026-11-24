
<?php

session_start();

require_once "config/database.php";

$error = "";

$name = "";
$email = "";
$contact = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contact = trim($_POST["contact"] ?? "");

    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Server-side validation
    if (
        empty($name) ||
        empty($email) ||
        empty($contact) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {

        $error = "Password must contain at least 8 characters.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        try {

            // Check whether email already exists
            $check = $conn->prepare(
                "SELECT user_id FROM users WHERE email = ?"
            );

            $check->execute([$email]);

            if ($check->fetch()) {

                $error = "This email is already registered.";

            } else {

                // Securely hash password
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Register new guest
                $sql = "INSERT INTO users
                        (name, email, contact_number, password, role)
                        VALUES (?, ?, ?, ?, 'guest')";

                $stmt = $conn->prepare($sql);

                $stmt->execute([
                    $name,
                    $email,
                    $contact,
                    $hashed_password
                ]);

                // Redirect after successful registration
                header("Location: login.php?registered=success");
                exit();

            }

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $error = "Something went wrong. Please try again.";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | Coastal Villa</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: #f4f7f5;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .register-container {
            width: 100%;
            max-width: 1100px;
            min-height: 680px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.12);
        }

        /* Left image section */

        .register-image {
            background:
                linear-gradient(
                    rgba(0, 35, 30, 0.45),
                    rgba(0, 35, 30, 0.55)
                ),
                url("https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200")
                center/cover no-repeat;

            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 45px;
            color: white;
        }

        .brand {
            font-size: 27px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .brand span {
            color: #d8b878;
        }

        .image-text h1 {
            font-size: 42px;
            line-height: 1.2;
            margin-bottom: 18px;
        }

        .image-text p {
            font-size: 16px;
            line-height: 1.8;
            max-width: 400px;
            color: #f1f1f1;
        }

        .image-footer {
            font-size: 13px;
            color: #eeeeee;
        }

        /* Right form section */

        .register-form-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 45px 50px;
        }

        .welcome-text {
            color: #b28b4e;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .register-form-section h2 {
            font-size: 30px;
            color: #173f38;
            margin-bottom: 12px;
        }

        .description {
            color: #777;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #dddddd;
            border-radius: 9px;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
        }

        .form-group input:focus {
            border-color: #246b5c;
            box-shadow: 0 0 0 3px rgba(36, 107, 92, 0.10);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 65px;
        }

        .toggle-password {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #246b5c;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            line-height: 1.5;
        }

        .error-message {
            background: #fff0f0;
            color: #c0392b;
            border: 1px solid #f2cccc;
        }

        .register-button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 9px;
            background: #174f43;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 5px;
        }

        .register-button:hover {
            background: #103b32;
            transform: translateY(-2px);
        }

        .login-text {
            text-align: center;
            color: #777;
            font-size: 14px;
            margin-top: 24px;
        }

        .login-text a {
            color: #246b5c;
            font-weight: bold;
            text-decoration: none;
        }

        .login-text a:hover {
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-home a {
            color: #777;
            font-size: 13px;
            text-decoration: none;
        }

        .back-home a:hover {
            color: #174f43;
        }

        /* Mobile responsive */

        @media (max-width: 800px) {

            body {
                padding: 15px;
            }

            .register-container {
                grid-template-columns: 1fr;
                max-width: 480px;
            }

            .register-image {
                min-height: 240px;
                padding: 25px;
            }

            .image-text h1 {
                font-size: 30px;
            }

            .image-footer {
                display: none;
            }

            .register-form-section {
                padding: 35px 28px;
            }

            .register-form-section h2 {
                font-size: 27px;
            }

        }

    </style>

</head>

<body>

    <div class="register-container">

        <!-- Left image section -->

        <div class="register-image">

            <div class="brand">
                COASTAL <span>VILLA.</span>
            </div>

            <div class="image-text">

                <h1>Your Journey<br>Begins Here.</h1>

                <p>
                    Create your account and discover
                    a relaxing stay, beautiful surroundings
                    and unforgettable moments at Coastal Villa.
                </p>

            </div>

            <div class="image-footer">
                Your perfect stay, just a registration away.
            </div>

        </div>

        <!-- Right registration form -->

        <div class="register-form-section">

            <p class="welcome-text">Join Coastal Villa</p>

            <h2>Create Your Account</h2>

            <p class="description">
                Register as a guest and start planning
                your perfect beach holiday.
            </p>

            <?php if (!empty($error)): ?>

                <div class="message error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="register.php" id="registerForm">

                <div class="form-group">

                    <label for="name">Full Name</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?php echo htmlspecialchars($name); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                        value="<?php echo htmlspecialchars($email); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="contact">Contact Number</label>

                    <input
                        type="tel"
                        id="contact"
                        name="contact"
                        placeholder="Enter your contact number"
                        value="<?php echo htmlspecialchars($contact); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="password">Password</label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimum 8 characters"
                            minlength="8"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword('password', this)"
                        >
                            Show
                        </button>

                    </div>

                </div>

                <div class="form-group">

                    <label for="confirm_password">Confirm Password</label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Re-enter your password"
                            minlength="8"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword('confirm_password', this)"
                        >
                            Show
                        </button>

                    </div>

                </div>

                <button type="submit" class="register-button">
                    Create Guest Account
                </button>

            </form>

            <p class="login-text">
                Already have an account?
                <a href="login.php">Login Here</a>
            </p>

            <div class="back-home">
                <a href="index.php">← Back to Home</a>
            </div>

        </div>

    </div>

    <script>

        // Show and hide password
        function togglePassword(fieldId, button) {

            const password = document.getElementById(fieldId);

            if (password.type === "password") {

                password.type = "text";
                button.textContent = "Hide";

            } else {

                password.type = "password";
                button.textContent = "Show";

            }

        }

        // Registration form validation
        const registerForm = document.getElementById("registerForm");

        const password = document.getElementById("password");

        const confirmPassword = document.getElementById("confirm_password");

        // Check whether both passwords match
        function validatePasswordMatch() {

            if (confirmPassword.value !== password.value) {

                confirmPassword.setCustomValidity(
                    "Passwords do not match."
                );

            } else {

                confirmPassword.setCustomValidity("");

            }

        }

        // Validate while typing
        password.addEventListener("input", validatePasswordMatch);

        confirmPassword.addEventListener("input", validatePasswordMatch);

        // Validate before submitting
        registerForm.addEventListener("submit", function(event) {

            validatePasswordMatch();

            if (!registerForm.checkValidity()) {

                event.preventDefault();

                registerForm.reportValidity();

            }

        });

    </script>

</body>

</html>

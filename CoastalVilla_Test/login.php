
<?php

session_start();

require_once "config/database.php";

$error = "";

// Registration success message
$registered = isset($_GET["registered"]) &&
              $_GET["registered"] === "success";

// Login process
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $sql = "SELECT * FROM users WHERE email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_role"] = $user["role"];

            // Admin and Guest redirection
            if ($user["role"] == "admin") {

                header("Location: admin/dashboard.php");
                exit();

            } else {

                header("Location: index.php");
                exit();

            }

        } else {

            $error = "Invalid email or password.";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Coastal Villa</title>

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

        .login-container {
            width: 100%;
            max-width: 1100px;
            min-height: 620px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.12);
        }

        /* Left image section */

        .login-image {
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

        /* Right login section */

        .login-form-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 55px 50px;
        }

        .welcome-text {
            color: #b28b4e;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .login-form-section h2 {
            font-size: 32px;
            color: #173f38;
            margin-bottom: 12px;
        }

        .description {
            color: #777;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 9px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
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

        .error-message {
            background: #fff0f0;
            color: #c0392b;
            border: 1px solid #f2cccc;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .success-message {
            background: #eaf7ef;
            color: #247344;
            border: 1px solid #c9e9d4;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .login-button {
            width: 100%;
            padding: 16px;
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

        .login-button:hover {
            background: #103b32;
            transform: translateY(-2px);
        }

        .register-text {
            text-align: center;
            color: #777;
            font-size: 14px;
            margin-top: 28px;
        }

        .register-text a {
            color: #246b5c;
            font-weight: bold;
            text-decoration: none;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
            margin-top: 25px;
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

            .login-container {
                grid-template-columns: 1fr;
                max-width: 480px;
            }

            .login-image {
                min-height: 260px;
                padding: 25px;
            }

            .image-text h1 {
                font-size: 30px;
            }

            .image-footer {
                display: none;
            }

            .login-form-section {
                padding: 40px 28px;
            }

            .login-form-section h2 {
                font-size: 28px;
            }

        }

    </style>

</head>

<body>

    <div class="login-container">

        <!-- Left image section -->

        <div class="login-image">

            <div class="brand">
                COASTAL <span>VILLA.</span>
            </div>

            <div class="image-text">

                <h1>Escape to<br>Paradise.</h1>

                <p>
                    Experience comfort, relaxation and
                    unforgettable moments at Coastal Villa.
                    Your perfect getaway starts here.
                </p>

            </div>

            <div class="image-footer">
                Your perfect stay, just a login away.
            </div>

        </div>

        <!-- Right login form -->

        <div class="login-form-section">

            <p class="welcome-text">Welcome Back</p>

            <h2>Login to Your Account</h2>

            <p class="description">
                Enter your details to access your
                Coastal Villa account.
            </p>

            <!-- Registration success message -->

            <?php if ($registered): ?>

                <div class="success-message">
                    Registration successful! Please login to your account.
                </div>

            <?php endif; ?>

            <!-- Login error message -->

            <?php if (!empty($error)): ?>

                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm">

                <div class="form-group">

                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
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
                            placeholder="Enter your password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword()"
                        >
                            Show
                        </button>

                    </div>

                </div>

                <button type="submit" class="login-button">
                    Login to Account
                </button>

            </form>

            <p class="register-text">
                Don't have an account?
                <a href="register.php">Register Here</a>
            </p>

            <div class="back-home">
                <a href="index.php">← Back to Home</a>
            </div>

        </div>

    </div>

    <script>

        // Show and hide password
        function togglePassword() {

            const password = document.getElementById("password");

            const button = document.querySelector(".toggle-password");

            if (password.type === "password") {

                password.type = "text";
                button.textContent = "Hide";

            } else {

                password.type = "password";
                button.textContent = "Show";

            }

        }

        // Login form JavaScript validation
        const loginForm = document.getElementById("loginForm");

        const email = document.getElementById("email");

        const password = document.getElementById("password");

        loginForm.addEventListener("submit", function(event) {

            // Check empty fields
            if (email.value.trim() === "" || password.value.trim() === "") {

                event.preventDefault();

                alert("Please enter your email and password.");

                return;

            }

            // Check valid email format
            if (!email.checkValidity()) {

                event.preventDefault();

                alert("Please enter a valid email address.");

                email.focus();

                return;

            }

        });

    </script>

</body>

</html>

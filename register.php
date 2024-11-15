<?php
session_start();

// Ensure admin or superadmin access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header('Location: login.php');
    exit();
}


require 'db.php'; // Include database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Load Composer's autoloader

ini_set('display_errors', 1);
error_reporting(E_ALL);

function sendemail_verify($username, $email, $verify_token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = 'designer.androniconucum@gmail.com';
        $mail->Password = 'till oyfn hdto gogd'; // Please secure your password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('designer.androniconucum@gmail.com', $username);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification from Andronico';
        $mail->Body = "
            <h1>CONGRATULATIONS!!</h1>
            <h2>You are now registered</h2>
            <h5>Verify your email by clicking the link below:</h5>
            <a href='http://localhost/myProject/verify-email.php?token=$verify_token'>Click here to verify</a>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

$message = '';
$messageClass = '';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];
    $verify_token = bin2hex(random_bytes(16));

    $valid = true;

    // Username validation
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W)[a-zA-Z\d\W]{5,}$/', $username)) {
        $message = "Username must contain at  least one uppercase letter, <br> one lowercase letter, and one special character.";
        $messageClass = "text-red-600";
        $valid = false;
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageClass = "text-red-600";
        $valid = false;
    }

    // Password validation
    if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $messageClass = "text-red-600";
        $valid = false;
    } elseif ($password !== $repassword) {
        $message = "Passwords do not match.";
        $messageClass = "text-red-600";
        $valid = false;
    }

    // Check if username already exists
    $username_query = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $username_query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $username_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($username_result) > 0) {
        $message = "Username is already taken.";
        $messageClass = "text-red-600";
        $valid = false;
    }
    mysqli_stmt_close($stmt);

    // Check if email already exists
    $email_query = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $email_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $email_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($email_result) > 0) {
        $message = "Email is already registered.";
        $messageClass = "text-red-600";
        $valid = false;
    }
    mysqli_stmt_close($stmt);

    // If all validations pass, insert the user
    if ($valid) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, email, password, verify_token, verify_status) VALUES (?, ?, ?, ?, 0)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password_hashed, $verify_token);

        if (mysqli_stmt_execute($stmt)) {
            if (sendemail_verify($username, $email, $verify_token)) {
                $message = "Check your email to verify your account.";
                $messageClass = "text-green-600";
            } else {
                $message = "Failed to send verification email. Please try again.";
                $messageClass = "text-red-600";
            }
        } else {
            $message = "Registration failed. Please try again.";
            $messageClass = "text-red-600";
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="script.js"></script>
    <link rel="stylesheet" href="style.css">
    <title>Register</title>
</head>
<body class="bg-[#080914]">
    <div class="flex h-screen items-center text-[#E4E2DD] flex-col justify-center">
        <div class="border border-black p-12 max-[400px]:p-5 rounded-md justify-center items-center flex flex-col font-worksans bg-[#13141F] leading-snug">
            <p class="font-black xl:text-[2.5rem] bg-gradient-to-r from-[#9fa1dd] via-[#3539cc] to-[#242779] inline-block text-transparent bg-clip-text">
                CS31A
            </p>

            <div class="flex flex-col text-start w-full">
                <p class="text-[3rem] font-bold text-start mb-1">Register</p>
            </div>

            <?php if ($message): ?>
                <div id="registrationMessage" class="mt-2 text-center <?php echo $messageClass; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="flex flex-col gap-1 mt-2 w-[18rem]">
                <input type="email" name="email" id="registerEmail" placeholder="Email" class="p-2 rounded-md text-black" oninput="validateRegisterEmail()" required>
                <span id="registerEmailError" class="text-red-600 text-[0.9rem]"></span>
                <span id="registerEmailSuccess" class="text-green-600 mb-[0.3rem] text-[0.9rem]"></span>

                <input type="text" name="username" id="registerUsername" placeholder="Username" class="p-2 rounded-md text-black" oninput="validateRegisterUsername()" required>
                <span id="registerUsernameError" class="text-red-600 text-[0.9rem]"></span>
                <span id="registerUsernameSuccess" class="text-green-600 mb-1 text-[0.9rem]"></span>

                <input type="password" name="password" id="registerPassword" placeholder="Password" class="p-2 rounded-md text-black" oninput="validateRegisterPassword()" required>
                <span id="registerPasswordError" class="text-red-600"></span>
                <span id="registerPasswordSuccess" class="text-green-600 mb-1"></span>

                <input type="password" name="repassword" id="registerRePassword" placeholder="Re-enter password" class="p-2 rounded-md text-black" oninput="validateRegisterRePassword()" required>
                <span id="registerRePasswordError" class="text-red-600"></span>
                <span id="registerRePasswordSuccess" class="text-green-600 mb-1"></span>

                <div class="flex w-full justify-end">
                    <button type="submit" name="register" class="bg-[#9FA1DD] mt-2 py-2 px-4 rounded-md text-black">Register</button>
                </div>
            </form>

            <hr class="bg-white w-full mt-6 mb-2">
            <p>Already have an account? <a href="login.php" class="text-blue-600 underline">Login</a></p>
        </div>
    </div>
</body>
</html>

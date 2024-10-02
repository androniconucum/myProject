<?php
// Include database connection
require 'db.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to send verification email
function sendemail_verify($username, $email, $verify_token) 
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host       = 'smtp.gmail.com';
        $mail->Username   = 'designer.androniconucum@gmail.com';
        $mail->Password   = 'till oyfn hdto gogd'; // Update with your actual app-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('designer.androniconucum@gmail.com', $username);
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Email Verification from Andronico';

        // Email template for verification
        $email_template = "
        <h1> CONGRATULATIONS!! </h1>
        <h2>You are now registered</h2>
        <h5>Verify your email address to login by clicking the link below:</h5>
        <br/><br/>
        <a href='http://localhost/myProject/verify-email.php?token=$verify_token'>Click here to verify</a>";

        $mail->Body = $email_template;
        $mail->send();
        return true;
    } catch(Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Initialize message variables
$message = '';
$messageClass = '';

// Process registration
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $verify_token = bin2hex(random_bytes(16)); // Generate a random verification token

    // Check if user already exists
    $check_query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "Email is already registered.";
        $messageClass = "text-red-600"; // Error message styling
    } else {
        // Insert user into database
        $query = "INSERT INTO users (username, email, password, verify_token, verify_status) VALUES (?, ?, ?, ?, 0)";
        $stmt = mysqli_stmt_init($conn);

        if (mysqli_stmt_prepare($stmt, $query)) {
            mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password, $verify_token);
            if (mysqli_stmt_execute($stmt)) {
                // Send verification email
                if (sendemail_verify($username, $email, $verify_token)) {
                    $message = "Check your email to verify your account.";
                    $messageClass = "text-green-600"; // Success message styling
                } else {
                    $message = "Failed to send verification email. Please try again.";
                    $messageClass = "text-red-600"; // Error message styling
                }
            } else {
                $message = "Failed to register. Please try again.";
                $messageClass = "text-red-600"; // Error message styling
            }
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
            <p class="font-black xl:text-[2.5rem] lg:text-[2.5rem] md:text-[2.5rem] sm:text-[2.5rem] max-[639px]:text-[2.5rem] max-[400px]:text-[2.5rem] bg-gradient-to-r from-[#9fa1dd] via-[#3539cc] to-[#242779] inline-block text-transparent bg-clip-text">CS31A</p>
            <div class="flex flex-col text-start w-full">
                <p class="text-[3rem] max-[639px]:text-[3rem] max-[400px]:text-[3rem] font-bold text-start mb-1">Register</p>
            </div>

            <!-- EMAIL VERIFICATION MESSAGE INSIDE FORM -->
            <?php if ($message): ?>
                <div id="registrationMessage" class="mt-2 text-center <?php echo $messageClass; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="flex flex-col gap-1 mt-2 w-[18rem]">
                <input type="email" name="email" id="registerEmail" placeholder="Email" class="p-2 focus:outline-none text-black rounded-md max-[400px]:p-2.5" oninput="validateRegisterEmail()">
                <span id="registerEmailError" class="text-red-600 mt-[0.1rem] text-[0.9rem]"></span>
                <span id="registerEmailSuccess" class="text-green-600 mb-[0.3rem] text-[0.9rem]"></span>

                <input type="text" name="username" id="registerUsername" placeholder="Username" class="p-2 focus:outline-none text-black rounded-md max-[400px]:p-2.5" oninput="validateRegisterUsername()">
                <span id="registerUsernameError" class="text-red-600 text-[0.9rem]"></span>
                <span id="registerUsernameSuccess" class="text-green-600 mb-1 text-[0.9rem]"></span>

                <input type="password" name="password" id="registerPassword" placeholder="Password" class="p-2 focus:outline-none text-black rounded-md max-[400px]:p-2.5" oninput="validateRegisterPassword()">
                <span id="registerPasswordError" class="text-red-600"></span>
                <span id="registerPasswordSuccess" class="text-green-600 mb-1"></span>

                <input type="password" name="repassword" id="registerRePassword" placeholder="Re-enter password" class="p-2 focus:outline-none text-black rounded-md max-[400px]:p-2.5" oninput="validateRegisterRePassword()">
                <span id="registerRePasswordError" class="text-red-600"></span>
                <span id="registerRePasswordSuccess" class="text-green-600 mb-1"></span>

                <div class="flex w-full justify-end">
                    <button type="submit" name="register" class="bg-[#9FA1DD] mt-2 font-medium py-2 px-4 rounded-md text-black">Register</button>
                </div>
            </form>

            <hr class="bg-white w-full mt-6 mb-2">
            <p>Already have an account? <a href="login.php" class="text-blue-600 underline">Login</a></p>
        </div>
    </div>
</body>
</html>

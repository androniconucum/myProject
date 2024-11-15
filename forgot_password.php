<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.dashboard.php');
    } else {
        header('Location: user.dashboard.php'); // For regular users
    }
    exit();
}

require 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$message = '';
$messageClass = '';

if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];

    // Check if email exists
    $check_query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $reset_token = bin2hex(random_bytes(16)); // Generate a random reset token

        // Update the user with the reset token
        $update_query = "UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?";
        $stmt = mysqli_stmt_init($conn);
        if (mysqli_stmt_prepare($stmt, $update_query)) {
            mysqli_stmt_bind_param($stmt, "ss", $reset_token, $email);
            if (mysqli_stmt_execute($stmt)) {
                // Send reset email
                $reset_link = "http://localhost/myProject/reset_password.php?token=$reset_token";
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->SMTPAuth = true;
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Username = 'designer.androniconucum@gmail.com';
                    $mail->Password = 'till oyfn hdto gogd';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('designer.androniconucum@gmail.com', 'CS31A');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $mail->Body = "Click this link to reset your password: <a href='$reset_link'>Reset Password</a>";
                    $mail->send();

                    $message = "Reset link has been sent to your email.";
                    $messageClass = "text-green-600";
                } catch (Exception $e) {
                    $message = "Failed to send email. Please try again.";
                    $messageClass = "text-red-600";
                }
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Email does not exist.";
        $messageClass = "text-red-600";
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
    <title>Forgot Password</title>
</head>
<body class="bg-[#080914]">
    <div class="flex h-screen items-center text-[#E4E2DD] flex-col justify-center">
        <div class="border border-black p-10 rounded-md justify-center items-center flex flex-col font-worksans bg-[#13141F] leading-snug"> 
            <p class="font-black text-2xl bg-gradient-to-r from-[#9fa1dd] via-[#3539cc] to-[#242779] inline-block text-transparent bg-clip-text">CS31A</p>
            <div class="flex flex-col text-start w-full">
                <p class="text-2xl font-bold text-start mb-1">Forgot Password</p>
            </div>
            <form method="POST" class="flex flex-col gap-1 mt-2 w-72">
                <?php if ($message): ?>
                    <span class="<?php echo $messageClass; ?> font-worksans"><?php echo $message; ?></span>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Enter your email" class="p-2 focus:outline-none text-black rounded-md" required>
                
                <div class="flex w-full justify-end">
                    <button type="submit" name="forgot_password" class="bg-[#9FA1DD] mt-2 font-medium py-2 px-3 rounded-md text-black">Send Link</button>
                </div>
            </form>
            <hr class="bg-white w-full mt-6 mb-2">
            <a href="login.php" class="text-red-600">Back to Login</a>
        </div>
    </div>
</body>
</html>

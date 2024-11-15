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

// Check if the token is provided
if (!isset($_GET['token'])) {
    header('Location: forgot_password.php'); // Redirect to forgot password page
    exit();
}

$token = $_GET['token'];
$message = '';
$messageClass = '';

// Prepare the statement to check the token
$query = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()";
$stmt = mysqli_stmt_init($conn);

if (mysqli_stmt_prepare($stmt, $query)) {
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        if (isset($_POST['reset_password'])) {
            $new_password = $_POST['new_password'];
            $update_query = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?";
            if (mysqli_stmt_prepare($stmt, $update_query)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $token);
                mysqli_stmt_execute($stmt);
                $message = "Your password has been reset successfully! <a href='login.php' class='text-green-600 underline'>Back to Login</a>";
                $messageClass = "text-green-600";
            } else {
                $message = "Failed to reset password. Please try again.";
                $messageClass = "text-red-600";
            }
        }
    } else {
        $message = "Invalid or expired token.";
        $messageClass = "text-red-600";
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
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
    <title>Reset Password</title>
</head>
<body class="bg-[#080914]">
    <div class="flex h-screen items-center text-[#E4E2DD] flex-col justify-center">
        <div class="border border-black p-10 rounded-md justify-center items-center flex flex-col font-worksans bg-[#13141F] leading-snug"> 
            <p class="font-black text-2xl bg-gradient-to-r from-[#9fa1dd] via-[#3539cc] to-[#242779] inline-block text-transparent bg-clip-text">CS31A</p>
            <div class="flex flex-col text-start w-full">
                <p class="text-3xl font-bold text-start mb-1">Reset Password</p>
            </div>
            <form method="POST" class="flex flex-col gap-1 mt-2 w-72">
                <?php if ($message): ?>
                    <span class="<?php echo $messageClass; ?> font-worksans"><?php echo $message; ?></span>
                <?php endif; ?>
                <input type="password" name="new_password" placeholder="Enter new password" class="p-2 focus:outline-none text-black rounded-md" required>
                <div class="flex w-full justify-end">
                    <button type="submit" name="reset_password" class="bg-[#9FA1DD] mt-2 font-medium py-2 px-4 rounded-md text-black">Reset Password</button>
                </div>
            </form>
            <hr class="bg-white w-full mt-6 mb-2">
            <a href="login.php" class="text-red-600">Back to Login</a>
        </div>
    </div>
</body>
</html>

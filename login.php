<?php
// Include database connection
require 'db.php';

// Start session
session_start();

// Initialize error message variable
$errorMsg = '';

// Process login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check user credentials
    // Check user credentials
$query = "SELECT * FROM users WHERE username = ? LIMIT 1";
$stmt = mysqli_stmt_init($conn);

if (mysqli_stmt_prepare($stmt, $query)) {
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        // Check if account is locked
        if ($user['status'] == 1) {
            // Check if the account lock duration has expired
            $lock_time = strtotime($user['lock_time']);
            $current_time = time();
            if ($current_time < $lock_time) {
                $errorMsg = "Your account is locked. Contact admin."; // Show lock error
            } else {
                // Reset attempts and unlock account
                $update_query = "UPDATE users SET status = 0, attempts = 0, lock_time = NULL WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                mysqli_stmt_execute($update_stmt);
                // Proceed with password verification below after unlocking
            }
        }

        // If account is not locked (either already unlocked or just unlocked above)
        if ($user['status'] == 0) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Reset attempts on successful login
                $update_query = "UPDATE users SET attempts = 0 WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                mysqli_stmt_execute($update_stmt);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'superadmin') {
                    header("Location: superadmin.dashboard.php");
                } else {
                    header("Location: user.dashboard.php");
                }
                exit();
            } else {
                // Increment login attempts
                $attempts = $user['attempts'] + 1;
                $update_query = "UPDATE users SET attempts = ?, lock_time = NOW() WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "ii", $attempts, $user['id']);
                mysqli_stmt_execute($stmt);
                
                // Lock account based on attempts
                if ($attempts >= 5) {
                    $errorMsg = "Your account has been locked. Contact an admin to unlock.";
                    $lock_query = "UPDATE users SET status = 1 WHERE id = ?";
                    $lock_stmt = mysqli_prepare($conn, $lock_query);
                    mysqli_stmt_bind_param($lock_stmt, "i", $user['id']);
                    mysqli_stmt_execute($lock_stmt);
                } else if ($attempts == 3) {
                    $errorMsg = "3 incorrect attempts. Account suspended for 20 seconds.";
                } else if ($attempts == 4) {
                    $errorMsg = "4 incorrect attempts. Account suspended for 2 minutes. If you continue, your account will be locked.";
                } else {
                    $errorMsg = "Invalid username or password.";
                }
            }
        }
    } else {
        $errorMsg = "Invalid username or password.";
    }
    mysqli_stmt_close($stmt);
}
    }
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
    <script src="script.js"></script>
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body class="bg-[#080914]">
    <div class="flex h-screen items-center text-[#E4E2DD] flex-col justify-center">
        <div class="border border-black p-10 max-[400px]:p-5 rounded-md justify-center items-center flex flex-col font-worksans bg-[#13141F] leading-snug"> 
            <p class="font-black xl:text-[2.5rem] lg:text-[2.5rem] md:text-[2.5rem] sm:text-[2.5rem] max-[639px]:text-[2.5rem] max-[400px]:text-[2.5rem] bg-gradient-to-r from-[#9fa1dd] via-[#3539cc] to-[#242779] inline-block text-transparent bg-clip-text">CS31A</p>
            <div class="flex flex-col text-start w-full">
                <p class="text-[3rem] max-[639px]:text-[3rem] max-[400px]:text-[3rem] font-bold text-start mb-1">Login</p>
            </div>
            <form method="POST" class="flex flex-col gap-1 mt-2 w-[18rem]">
                <input type="text" name="username" id="loginUsername" placeholder="Username" class="p-2 focus:outline-none text-black rounded-md max-[400px]:p-2.5" required>
                <span id="loginUsernameError" class="text-red-600 font-worksans"></span>
                <span id="loginUsernameSuccess" class="text-green-600 mb-1 font-worksans"></span>

                <input type="password" name="password" id="loginPassword" placeholder="Password" class="p-2 focus:outline-none text-black rounded-md max-[400px]:p-2.5" required>
                <span id="loginPasswordError" class="text-red-600"></span>
                <span id="loginPasswordSuccess" class="text-green-600 "></span>

                <?php if ($errorMsg): ?>
                    <span class="text-red-600 font-worksans "><?php echo $errorMsg; ?></span>
                <?php endif; ?>
                
                <div class="flex w-full justify-end">
                    <button type="submit" name="login" class="bg-[#9FA1DD] mt-2 font-medium py-2 px-4 rounded-md text-black">Login</button>
                </div>
            </form>
            <hr class="bg-white w-full mt-6 mb-2">
            <p>Don't have an account? <a href="register.php" class="text-blue-600 underline">Register</a></p>
        </div>
    </div>
</body>
</html>

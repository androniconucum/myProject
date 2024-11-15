<?php
// Include database connection
require 'db.php';

// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'superadmin') {
        header('Location: superadmin.dashboard.php');
    } elseif($_SESSION['role'] === 'admin') {
        header('Location: admin.dashboard.php'); 
    }
    else {
        header('Location: user.dashbaord.php');
    }
    exit();
}

// Initialize error message variable
$errorMsg = '';


// Function to update the last action for users
function updateUserLastAction($conn, $user_id, $action) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET last_action = ?, last_action_time = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $action, $user_id);
    mysqli_stmt_execute($stmt);
}

// Process login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check user credentials
    $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $query)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Check if the user's email is verified
            if ($user['verify_status'] == 0) {
                $errorMsg = "Please verify your email before logging in.";
            } else {
                // Check if the account is frozen
                if ($user['status'] == 2) {
                    $current_time = date('Y-m-d H:i:s');
                    if ($current_time < $user['freeze_time']) {
                        $errorMsg = "Your account is frozen for 8 hours. Contact admin."; // Show freeze error
                    } else {
                        // Account is frozen but freeze time has expired, reset status
                        $update_query = "UPDATE users SET status = 0, attempts = 0, freeze_time = NULL WHERE id = ?";
                        $update_stmt = mysqli_prepare($conn, $update_query);
                        mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                        mysqli_stmt_execute($update_stmt);
                    }
                }

                // Check if the account is not frozen
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

                        // Update last action for the user
                        $last_action = "Logged in"; // Define the last action
                        mysqli_query($conn, "UPDATE users SET last_action = '$last_action', last_action_time = NOW() WHERE id = " . $_SESSION['user_id']);

                        // Redirect based on role
                        if ($user['role'] === 'admin') {
                            header("Location: admin.dashboard.php");
                        } else {
                            header("Location: user.dashboard.php");
                        }
                        exit();
                    } else {
                        // Increment login attempts
                        $attempts = $user['attempts'] + 1;

                        // Check if the account needs to be frozen
                        if ($attempts >= 5) {
                            $errorMsg = "Your account has been frozen for 8 hours. Contact an admin to unlock.";
                            $freeze_time = date('Y-m-d H:i:s', strtotime('+8 hours'));

                            // Update user status to frozen
                            $update_query = "UPDATE users SET status = 2, attempts = ?, freeze_time = ? WHERE id = ?";
                            $stmt = mysqli_prepare($conn, $update_query);
                            mysqli_stmt_bind_param($stmt, "isi", $attempts, $freeze_time, $user['id']);
                            mysqli_stmt_execute($stmt);
                        } else {
                            // Update attempts count
                            $update_query = "UPDATE users SET attempts = ? WHERE id = ?";
                            $stmt = mysqli_prepare($conn, $update_query);
                            mysqli_stmt_bind_param($stmt, "ii", $attempts, $user['id']);
                            mysqli_stmt_execute($stmt);

                            // Provide feedback based on the number of attempts
                            if ($attempts == 3) {
                                $errorMsg = "3 incorrect attempts. Account suspended for 20 seconds.";
                            } elseif ($attempts == 4) {
                                $errorMsg = "4 incorrect attempts. Account suspended for 2 minutes. If you continue, your account will be frozen.";
                            } else {
                                $errorMsg = "Invalid username or password.";
                            }
                        }
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
                <?php if ($errorMsg): ?>
                    <span class="text-red-600 font-worksans "><?php echo $errorMsg; ?></span>
                <?php endif; ?>
                <input type="text" name="username" id="loginUsername" placeholder="Username" class="p-2 focus:outline-none text-black rounded-md max-[400px]:p-2.5" required>
                <span id="loginUsernameError" class="text-red-600 font-worksans"></span>
                <span id="loginUsernameSuccess" class="text-green-600 mb-1 font-worksans"></span>

                <input type="password" name="password" id="loginPassword" placeholder="Password" class="p-2 focus:outline-none text-black rounded-md max-[400px]:p-2.5" required>
                <span id="loginPasswordError" class="text-red-600"></span>
                <span id="loginPasswordSuccess" class="text-green-600 "></span>
                
                <div class="flex w-full justify-end">
                    <button type="submit" name="login" class="bg-[#9FA1DD] mt-2 font-medium py-2 px-4 rounded-md text-black">Login</button>
                </div>
            </form>
            <hr class="bg-white w-full mt-6 mb-2">
            <a href="forgot_password.php" class="text-red-600">Forgot Password?</a>
        </div>
    </div>
</body>
</html>

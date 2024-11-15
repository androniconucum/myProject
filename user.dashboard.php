<?php
// Start session
session_start();

include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    // If not logged in, redirect to login page
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Function to log user action
function logUserAction($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "INSERT INTO audit_logs (user_id, username, role, action, timestamp) 
        SELECT id, username, role, 'Accessed user dashboard', NOW() 
        FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
}

// Check if the action has already been logged for this session
if (!isset($_SESSION['logged_action'])) {
    logUserAction($conn, $user_id);
    // Set session variable to indicate that the action has been logged
    $_SESSION['logged_action'] = true;
}

// Fetch user information
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>User Dashboard</title>
</head>
<body class="bg-[#080914]">
    <div class="flex justify-center h-screen items-center flex-col">
    <p class="text-[5.5rem] font-extrabold bg-gradient-to-r from-[#9fa1dd] via-[#3539cc] to-[#242779] inline-block text-transparent bg-clip-text">HELLO USER</p>

    <!-- Logout button -->
    <div class="mt-5">
        <form method="POST" action="logout.php">
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">Logout</button>
        </form>
    </div>
    </div>
</body>
</html>

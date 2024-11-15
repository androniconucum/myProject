<?php
// Include database connection
require 'db.php'; // Make sure this is the correct path to your database connection file

// Start session
session_start();

// Function to update user's last action
function updateUserLastAction($conn, $user_id, $action) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET last_action = ?, last_action_time = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $action, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt); // Close the statement
}

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "INSERT INTO audit_logs (user_id, username, role, action, timestamp) 
    SELECT id, username, role, 'User logged out', NOW() 
    FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

    // Log user last action on logout
    $action = 'Logged out';
    updateUserLastAction($conn, $user_id, $action); // Call the function

    // Destroy the session
    session_unset();
    session_destroy();

    // Redirect to login page
    header('Location: login.php');
    exit();
} else {
    // If not logged in, redirect to login
    header('Location: login.php');
    exit();
}
?>

<?php
require_once "db.php"; // Include your database connection file

if (isset($_GET['token'])) {
    $verify_token = $_GET['token'];

    // Prepare the SQL statement to check if the token exists in the database
    $query = "SELECT * FROM users WHERE verify_token = ? LIMIT 1";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $query)) {
        mysqli_stmt_bind_param($stmt, "s", $verify_token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Check if the token is found
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

            // Check the verification status
            if ($user['verify_status'] == 0) {
                // Update the verify_status to 1 (verified)
                $update_query = "UPDATE users SET verify_status = 1 WHERE verify_token = ?";
                $update_stmt = mysqli_stmt_init($conn);

                if (mysqli_stmt_prepare($update_stmt, $update_query)) {
                    mysqli_stmt_bind_param($update_stmt, "s", $verify_token);
                    if (mysqli_stmt_execute($update_stmt)) {
                        echo "<div class='text-green-500 font-bold'>Your email has been successfully verified. You can now <a href='http://localhost/myProject/login.php'>Login</a></div>";
                    } else {
                        echo "<div class='text-red-500 font-bold'>Verification failed. Please try again later.</div>";
                    }
                }
            } else {
                echo "<div class='text-yellow-500 font-bold'>Your email is already verified. You can <a href='http://localhost/myProject/login.php'>Login</a></div>";
            }
        } else {
            echo "<div class='text-red-500 font-bold'>Invalid token. Please check your email and click on the verification link again.</div>";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "<div class='text-red-500 font-bold'>Failed to prepare the SQL statement.</div>";
    }

    mysqli_close($conn);
} else {
    echo "<div class='text-red-500 font-bold'>No verification token provided.</div>";
}
?>

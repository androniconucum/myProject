<?php
require 'db.php';

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo 'taken';
    } else {
        echo 'available';
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>

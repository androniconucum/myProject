<?php
// Include database connection
require 'db.php';

// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}


// Array of allowed pages for admin
$allowed_pages = ['admin.dashboard.php'];

// Check the requested page
$current_page = basename($_SERVER['PHP_SELF']);
if (!in_array($current_page, $allowed_pages)) {
    header('Location: admin.dashboard.php');
    exit();
}

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Pagination logic for users
$limit = 5; // Number of users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Initialize search variable
$search = '';
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

// Fetch total number of users with search condition
$total_query = "SELECT COUNT(*) as total FROM users WHERE role != 'admin' AND (username LIKE '%$search%' OR email LIKE '%$search%')";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $limit);

// Fetch users for the current page with search condition
$query = "SELECT * FROM users WHERE role != 'admin' AND (username LIKE '%$search%' OR email LIKE '%$search%') LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);


// Handle account actions (delete, promote, freeze, unfreeze)
if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];
    $target_username = getUsernameById($conn, $user_id);

    if ($action == 'delete') {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        logAuditAction($conn, $_SESSION['user_id'], "Deleted user account: $target_username");
        header('Location: admin.dashboard.php');
        exit();
    } elseif ($action == 'promote') {
        $stmt = mysqli_prepare($conn, "UPDATE users SET role = 'admin' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        logAuditAction($conn, $_SESSION['user_id'], "Promoted user to admin: $target_username");
        header('Location: admin.dashboard.php');
        exit();
    } elseif ($action == 'freeze') {
        $stmt = mysqli_prepare($conn, "UPDATE users SET status = 1, attempts = 0, freeze_time = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        logAuditAction($conn, $_SESSION['user_id'], "Froze user account: $target_username");
        header('Location: admin.dashboard.php');
        exit();
    } elseif ($action == 'unfreeze') {
        $stmt = mysqli_prepare($conn, "UPDATE users SET status = 0, attempts = 0, freeze_time = NULL WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        logAuditAction($conn, $_SESSION['user_id'], "Unfroze user account: $target_username");
        header('Location: admin.dashboard.php');
        exit();
    }
}

// Function to log audit actions
function logAuditAction($conn, $user_id, $action) {
    $stmt = mysqli_prepare($conn, "SELECT username, role FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    $username = $user['username'] ?? 'Unknown User';
    $role = $user['role'] ?? 'Unknown Role';
    
    $stmt = mysqli_prepare($conn, "INSERT INTO audit_logs (user_id, username, role, action, timestamp) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $username, $role, $action);
    mysqli_stmt_execute($stmt);
}

// Function to get username by ID
function getUsernameById($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT username FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['username'] ?? 'Unknown User';
}

// Fetch audit logs with pagination
$audit_limit = 10;
$audit_page = isset($_GET['audit_page']) ? (int)$_GET['audit_page'] : 1;
$audit_offset = ($audit_page - 1) * $audit_limit;

// Get total number of audit logs
$total_audit_query = "SELECT COUNT(*) as total FROM audit_logs al 
                      LEFT JOIN users u ON al.user_id = u.id 
                      WHERE u.role != 'superadmin'";
$total_audit_result = mysqli_query($conn, $total_audit_query);
$total_audit_row = mysqli_fetch_assoc($total_audit_result);
$total_audit_logs = $total_audit_row['total'];
$total_audit_pages = ceil($total_audit_logs / $audit_limit);

// Fetch audit logs
$audit_query = "SELECT al.* FROM audit_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE u.role != 'superadmin' 
                ORDER BY al.timestamp DESC 
                LIMIT $audit_limit OFFSET $audit_offset";
$audit_result = mysqli_query($conn, $audit_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin Dashboard</title>
</head>
<body class="bg-[#080914] p-4 sm:p-10">
    <h1 class="text-4xl font-extrabold mb-7 text-[#dedeef]">Admin Dashboard</h1>

    <!-- Search Bar -->
    <div class="mb-5 flex flex-col sm:flex-row justify-between items-start">
        <a href="register.php" class="bg-green-600 text-white px-4 py-2 rounded mb-2 sm:mb-0">Register an Account</a>
        <form method="GET" class="flex items-center justify-end w-full sm:w-auto">
            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" class="border p-2 m-0.5 rounded-l w-full sm:w-64" placeholder="Search by username or email...">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r">Search</button>
        </form>
    </div>

    <!-- Users Table -->
    <div class="overflow-x-auto mb-10">
        <h2 class="text-2xl font-bold mb-4 text-[#dedeef]">User Management</h2>
        <table class="min-w-full bg-[#242779] border-collapse text-[#dedeef] table-auto">
            <thead>
                <tr class="font-black">
                    <th class="border px-4 py-2">ID</th>
                    <th class="border px-4 py-2">Username</th>
                    <th class="border px-4 py-2">Email</th>
                    <th class="border px-4 py-2">Role</th>
                    <th class="border px-4 py-2">Status</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr class="font-medium"> 
                        <td class="border px-4 py-2"><?= $row['id']; ?></td> 
                        <td class="border px-4 py-2"><?= htmlspecialchars($row['username']); ?></td> 
                        <td class="border px-4 py-2"><?= htmlspecialchars($row['email']); ?></td> 
                        <td class="border px-4 py-2"><?= 'User'; ?></td> <td class="border px-4 py-2"><?= $row['status'] == 1 ? 'Frozen' : 'Active'; ?></td> 
                        <td class="border px-4 py-2"> <form method="POST" class="inline"> <input type="hidden" name="user_id" value="<?= $row['id']; ?>"> 
                        <button type="submit" name="action" value="delete" class="bg-red-500 text-[#dedeef] rounded px-2 py-1">Delete</button> 
                        <button type="submit" name="action" value="promote" class="bg-green-500 text-[#dedeef] rounded px-2 py-1">Promote</button> 
                        <button type="submit" name="action" value="freeze" class="bg-orange-500 text-[#dedeef] rounded px-2 py-1">Freeze</button> 
                        <button type="submit" name="action" value="unfreeze" class="bg-yellow-500 text-[#dedeef] rounded px-2 py-1">Unfreeze</button> 
                    </form> 
                </td> 
            </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
     <!-- Users Pagination -->
     <div class="flex justify-between items-center mb-6">
        <nav aria-label="Users pagination">
            <ul class="flex list-style-none">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="mx-1">
                        <a href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>" 
                           class="bg-blue-500 text-white px-4 py-2 rounded <?= $i === $page ? 'bg-blue-700' : ''; ?>">
                            <?= $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <!-- Audit Logs Table -->
    <div class="overflow-x-auto mb-10">
    <h2 class="text-2xl font-bold mb-4 text-[#dedeef]">Audit Logs</h2>
    <table class="min-w-full bg-[#242779] border-collapse text-[#dedeef] table-auto">
        <thead>
            <tr class="font-black">
                <th class="border px-4 py-2">Timestamp</th>
                <th class="border px-4 py-2">Username</th>
                <th class="border px-4 py-2">Role</th>
                <th class="border px-4 py-2">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($log = mysqli_fetch_assoc($audit_result)) : ?>
                <tr class="font-medium">
                    <td class="border px-4 py-2"><?= htmlspecialchars($log['timestamp']); ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($log['username']); ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($log['role']); ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($log['action']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Audit Logs Pagination -->
<div class="flex justify-between items-center mb-6">
    <nav aria-label="Audit logs pagination">
        <ul class="flex list-style-none">
            <?php for ($i = 1; $i <= $total_audit_pages; $i++): ?>
                <li class="mx-1">
                    <a href="?audit_page=<?= $i; ?>" 
                       class="bg-purple-500 text-white px-4 py-2 rounded <?= $i === $audit_page ? 'bg-purple-700' : ''; ?>">
                        <?= $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <div>
        <form method="POST" action="logout.php" class="flex">
            <button type="submit" class="bg-red-600 text-[#dedeef] px-4 py-2 rounded">Logout</button>
        </form>
    </div>
</div>
</body>
</html>
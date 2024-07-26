<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$total_books = $conn->query("SELECT COUNT(*) FROM ebooks")->fetch_row()[0];
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetch_row()[0];
$total_checkouts = $conn->query("SELECT COUNT(*) FROM checkouts WHERE returned = 0")->fetch_row()[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard</h2>
        
        <div class="dashboard-stats">
            <div class="stat-box">
                <h3>Total Books</h3>
                <p><?php echo $total_books; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="stat-box">
                <h3>Current Checkouts</h3>
                <p><?php echo $total_checkouts; ?></p>
            </div>
        </div>

        <h3>Admin Functions</h3>
        <ul class="admin-functions">
            <li><a href="manage_books.php" class="button">Manage Books</a></li>
            <li><a href="manage_authors.php" class="button">Manage Authors</a></li>
            <li><a href="manage_categories.php" class="button">Manage Categories</a></li>
            <li><a href="manage_checkouts.php" class="button">Manage Checkouts</a></li>
            <li><a href="manage_user.php" class="button">Manage Users</a></li>
        </ul>

        <p><a href="logout.php" class="button">Logout</a></p>
    </div>
</body>
</html>
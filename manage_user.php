<?php
session_start();
require_once 'db_connect.php';

// Debugging: Log session data
error_log("Session data: " . print_r($_SESSION, true));

// Improved authentication check
if (!isset($_SESSION['user_id'])) {
    error_log("User ID not set in session. Redirecting to login.");
    header("Location: login.php");
    exit();
}

error_log("Authentication successful. Proceeding to manage users.");

// Rest of your code for managing users
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    } elseif (isset($_POST['toggle_admin'])) {
        $user_id = $_POST['user_id'];
        // Check if 'is_admin' column exists
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE users SET is_admin = NOT is_admin WHERE id = ?");
        } else {
            // If 'is_admin' doesn't exist, you might want to handle this differently
            error_log("is_admin column does not exist in users table");
            // For now, we'll just do nothing
            $stmt = $conn->prepare("SELECT 1"); // Dummy query
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
}

$result = $conn->query("SELECT * FROM users");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Manage Users</h2>
        <table>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Admin</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo isset($user['is_admin']) ? ($user['is_admin'] ? 'Yes' : 'No') : 'N/A'; ?></td>
                <td>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="submit" name="toggle_admin" value="<?php echo isset($user['is_admin']) && $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>" class="button">
                        <input type="submit" name="delete" value="Delete" class="button delete" onclick="return confirm('Are you sure you want to delete this user?');">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><a href="admin_dashboard.php">Back to Admin Dashboard</a></p>
    </div>
</body>
</html>
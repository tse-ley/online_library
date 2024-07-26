<?php
session_start();
require_once 'db_connect.php';

$error = '';
$success = '';

// Check if any admin exists
$check_admin = $conn->query("SELECT * FROM users WHERE role = 'admin'");
$admin_exists = $check_admin->num_rows > 0;

// Only check for admin login if admins already exist
if ($admin_exists && (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($conn, $_POST['username']);
    $password = $_POST['password'];
    $email = sanitize_input($conn, $_POST['email']);
    $role = 'admin';

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Username or email already exists.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new admin
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $role);

        if ($stmt->execute()) {
            $success = "Admin user created successfully.";
        } else {
            $error = "Failed to create admin user. " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Admin User</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Create Admin User</h2>
        <?php
        if ($error) {
            echo "<p class='error'>$error</p>";
        }
        if ($success) {
            echo "<p class='success'>$success</p>";
        }
        ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="submit" value="Create Admin">
        </form>
        <?php if ($admin_exists): ?>
            <p><a href="admin_dashboard.php">Back to Admin Dashboard</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
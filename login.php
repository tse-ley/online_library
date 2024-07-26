<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare statement to select user based on email
    $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $db_email, $db_password, $role);
    $stmt->fetch();

    // Verify the provided password against the hashed password in the database
    if ($stmt->num_rows > 0 && password_verify($password, $db_password)) {
        // Store user information in session variables
        $_SESSION['user_id'] = $id;
        $_SESSION['email'] = $db_email;
        $_SESSION['role'] = $role;
        $_SESSION['message'] = "Welcome back!";

        // Redirect based on user role
        if ($role === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        // Set error message if login fails
        $error = "Login failed. Invalid email or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
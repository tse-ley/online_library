<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32));

    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
    $stmt->bind_param("ss", $token, $email);

    if ($stmt->execute()) {
        
        $reset_link = "http://yourdomain.com/reset_password.php?token=$token";
        $message = "Password reset link: $reset_link";
    } else {
        $error = "Error occurred. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php 
        if (isset($error)) echo "<p class='error'>$error</p>";
        if (isset($message)) echo "<p class='success'>$message</p>";
        ?>
        <form method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="submit" value="Reset Password">
        </form>
        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>
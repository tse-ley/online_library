<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("ss", $new_password, $token);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $message = "Password updated successfully. You can now login with your new password.";
    } else {
        $error = "Invalid or expired token. Please try again.";
    }
}

$token = $_GET['token'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php 
        if (isset($error)) echo "<p class='error'>$error</p>";
        if (isset($message)) echo "<p class='success'>$message</p>";
        ?>
        <form method="post">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <input type="submit" value="Reset Password">
        </form>
        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>
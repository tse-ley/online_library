<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Our Online library</h1>

        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['email']); ?>!</p>
            <p>You are logged in as a user. <a href="user_dashboard.php">Go to Dashboard</a></p>
            <form method="post" action="logout.php">
                <input type="submit" value="Logout">
            </form>
        <?php else: ?>
            <p>Hello, guest! Please <a href="login.php">login</a> or <a href="register.php">register</a> to continue.</p>
        <?php endif; ?>

        <p><a href="about.php">About Us</a> | <a href="contact.php">Contact Us</a></p>
    </div>
</body>
</html>

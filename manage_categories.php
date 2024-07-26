
<?php
session_start();
require_once 'db_connect.php';

// Debugging: Output all session variables
echo "Session variables:<br>";
print_r($_SESSION);
echo "<br><br>";

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "You are not authorized to access this page. Redirecting to login...";
    header("Refresh: 3; URL=login.php");
    exit();
}

// Rest of your code remains the same
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... (POST handling code)
}

$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Manage Categories</h2>
        
        <h3>Add New Category</h3>
        <form method="post">
            <input type="text" name="name" placeholder="Category Name" required>
            <input type="submit" name="add_category" value="Add Category">
        </form>

        <h3>Category List</h3>
        <table>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td><?php echo htmlspecialchars($category['name']); ?></td>
                <td>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                        <input type="text" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                        <input type="submit" name="edit_category" value="Edit" class="button">
                        <input type="submit" name="delete_category" value="Delete" class="button delete" onclick="return confirm('Are you sure you want to delete this category?');">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p><a href="admin_dashboard.php" class="button">Back to Admin Dashboard</a></p>
    </div>
</body>
</html>

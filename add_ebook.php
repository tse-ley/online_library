<?php
session_start();
require_once 'db_connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    $_SESSION['message'] = "Access denied.";
    header("Location: login.php");
    exit();
}

// Fetch all authors and categories for the dropdown menus
$authors = $conn->query("SELECT id, name FROM authors ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize_input($conn, $_POST['title']);
    $author_id = sanitize_input($conn, $_POST['author_id']);
    $category_id = sanitize_input($conn, $_POST['category_id']);
    $isbn = sanitize_input($conn, $_POST['isbn']);
    $description = sanitize_input($conn, $_POST['description']);
    $file_path = sanitize_input($conn, $_POST['file_path']); // In a real scenario, you'd handle file uploads here

    $stmt = $conn->prepare("INSERT INTO ebooks (title, author_id, category_id, isbn, description, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siisss", $title, $author_id, $category_id, $isbn, $description, $file_path);

    if ($stmt->execute()) {
        $_SESSION['message'] = "E-book added successfully.";
        header("Location: manage_books.php");
        exit();
    } else {
        $error = "Failed to add e-book. " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add E-book</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Add New E-book</h2>
        <?php
        if (isset($error)) {
            echo "<p class='error'>$error</p>";
        }
        if (isset($_SESSION['message'])) {
            echo "<p class='success'>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']);
        }
        ?>
        <form method="post">
            <input type="text" name="title" placeholder="Title" required>
            <select name="author_id" required>
                <option value="">Select Author</option>
                <?php foreach ($authors as $author): ?>
                    <option value="<?php echo $author['id']; ?>"><?php echo htmlspecialchars($author['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="isbn" placeholder="ISBN" required>
            <textarea name="description" placeholder="Book Description" rows="4"></textarea>
            <input type="text" name="file_path" placeholder="File Path" required>
            <input type="submit" value="Add E-book">
        </form>
        <p><a href="manage_books.php">Back to Manage Books</a></p>
    </div>
</body>
</html>
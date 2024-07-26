<?php
session_start();
require_once 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("POST data received: " . print_r($_POST, true));

    if (isset($_POST['add_ebook'])) {
        $title = $_POST['title'];
        $author_id = intval($_POST['author_id']);
        $category_id = intval($_POST['category_id']);
        $isbn = $_POST['isbn'];
        $file_path = $_POST['file_path'];
        
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO ebooks (title, author_id, category_id, isbn, file_path, available) VALUES (?, ?, ?, ?, ?, 1)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("siiss", $title, $author_id, $category_id, $isbn, $file_path);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $conn->commit();
            error_log("eBook added successfully");
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error adding eBook: " . $e->getMessage());
        }
    } elseif (isset($_POST['edit_ebook'])) {
        $ebook_id = intval($_POST['ebook_id']);
        $title = $_POST['title'];
        $author_id = intval($_POST['author_id']);
        $category_id = intval($_POST['category_id']);
        $isbn = $_POST['isbn'];
        $file_path = $_POST['file_path'];
        
        $stmt = $conn->prepare("UPDATE ebooks SET title = ?, author_id = ?, category_id = ?, isbn = ?, file_path = ? WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
        } else {
            $stmt->bind_param("siissi", $title, $author_id, $category_id, $isbn, $file_path, $ebook_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
            } else {
                error_log("eBook updated successfully");
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    } elseif (isset($_POST['delete_ebook'])) {
        $ebook_id = intval($_POST['ebook_id']);
        
        $stmt = $conn->prepare("DELETE FROM ebooks WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
        } else {
            $stmt->bind_param("i", $ebook_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
            } else {
                error_log("eBook deleted successfully");
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
}

$ebooks = $conn->query("SELECT e.*, a.name as author_name, c.name as category_name FROM ebooks e 
                       JOIN authors a ON e.author_id = a.id 
                       JOIN categories c ON e.category_id = c.id")->fetch_all(MYSQLI_ASSOC);
$authors = $conn->query("SELECT id, name FROM authors")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT id, name FROM categories")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage eBooks</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Manage eBooks</h2>
        
        <h3>Add New eBook</h3>
        <form method="post">
            <input type="text" name="title" placeholder="Title" required>
            <select name="author_id" required>
                <?php foreach ($authors as $author): ?>
                    <option value="<?php echo $author['id']; ?>"><?php echo htmlspecialchars($author['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="isbn" placeholder="ISBN" required>
            <input type="text" name="file_path" placeholder="File Path" required>
            <input type="submit" name="add_ebook" value="Add eBook">
        </form>

        <h3>eBook List</h3>
        <table>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>ISBN</th>
                <th>File Path</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($ebooks as $ebook): ?>
            <tr>
                <td><?php echo htmlspecialchars($ebook['title']); ?></td>
                <td><?php echo htmlspecialchars($ebook['author_name']); ?></td>
                <td><?php echo htmlspecialchars($ebook['category_name']); ?></td>
                <td><?php echo htmlspecialchars($ebook['isbn']); ?></td>
                <td><?php echo htmlspecialchars($ebook['file_path']); ?></td>
                <td><?php echo $ebook['available'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="ebook_id" value="<?php echo $ebook['id']; ?>">
                        <input type="submit" name="edit_ebook" value="Edit" class="button">
                        <input type="submit" name="delete_ebook" value="Delete" class="button delete" onclick="return confirm('Are you sure you want to delete this eBook?');">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p><a href="admin_dashboard.php" class="button">Back to Admin Dashboard</a></p>
    </div>
</body>
</html>
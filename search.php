<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$query = "SELECT e.*, a.name AS author_name, c.name AS category_name 
          FROM ebooks e 
          JOIN authors a ON e.author_id = a.id 
          JOIN categories c ON e.category_id = c.id 
          WHERE (e.title LIKE ? OR a.name LIKE ?)";

if (!empty($category)) {
    $query .= " AND c.id = ?";
}

$stmt = $conn->prepare($query);

if (!empty($category)) {
    $search_param = "%$search%";
    $stmt->bind_param("ssi", $search_param, $search_param, $category);
} else {
    $search_param = "%$search%";
    $stmt->bind_param("ss", $search_param, $search_param);
}

$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search E-books</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Search E-books</h2>
        
        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="Search by title or author" value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $category ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" value="Search">
        </form>

        <h3>Search Results</h3>
        <?php if (empty($books)): ?>
            <p>No books found.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Available</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                    <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                    <td><?php echo $book['available'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <?php if ($book['available']): ?>
                            <a href="checkout.php?id=<?php echo $book['id']; ?>" class="button">Checkout</a>
                        <?php else: ?>
                            <span class="unavailable">Unavailable</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <p><a href="user_dashboard.php" class="button">Back to Dashboard</a></p>
    </div>
</body>
</html>
<?php
session_start();
require_once 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1); 

// Function to check if user is logged in and is an admin
function check_admin_auth() {
    if (!isset($_SESSION['user_id'])) {
        error_log("Session 'user_id' not set.");
        return false;
    }
    if (!isset($_SESSION['is_admin'])) {
        error_log("Session 'is_admin' not set.");
        return false;
    }
    if (!$_SESSION['is_admin']) {
        error_log("User is not an admin.");
        return false;
    }
    return true;
}

// Rest of your code starts here
error_log("Admin authentication successful. Proceeding with author management.");

// Function to execute prepared statements
function execute_statement($conn, $query, $types, ...$params) {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    return $stmt;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_author'])) {
        $name = $_POST['name'];
        $ebooks = $_POST['ebooks'] ?? [];

        $stmt = execute_statement($conn, "INSERT INTO authors (name) VALUES (?)", "s", $name);
        $author_id = $stmt->insert_id;

        foreach ($ebooks as $ebook_id) {
            execute_statement($conn, "INSERT INTO author_ebook (author_id, ebook_id) VALUES (?, ?)", "ii", $author_id, $ebook_id);
        }
    } elseif (isset($_POST['edit_author'])) {
        $author_id = $_POST['author_id'];
        $name = $_POST['name'];
        $ebooks = $_POST['ebooks'] ?? [];

        execute_statement($conn, "UPDATE authors SET name = ? WHERE id = ?", "si", $name, $author_id);
        execute_statement($conn, "DELETE FROM author_ebook WHERE author_id = ?", "i", $author_id);

        foreach ($ebooks as $ebook_id) {
            execute_statement($conn, "INSERT INTO author_ebook (author_id, ebook_id) VALUES (?, ?)", "ii", $author_id, $ebook_id);
        }
    } elseif (isset($_POST['delete_author'])) {
        $author_id = $_POST['author_id'];

        execute_statement($conn, "DELETE FROM authors WHERE id = ?", "i", $author_id);
        execute_statement($conn, "DELETE FROM author_ebook WHERE author_id = ?", "i", $author_id);
    }
}

// Fetch authors and ebooks
$authors = $conn->query("SELECT * FROM authors")->fetch_all(MYSQLI_ASSOC);
$ebooks = $conn->query("SELECT * FROM ebooks")->fetch_all(MYSQLI_ASSOC);
// Start of HTML output
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Authors</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Authors</h2>

        <h3>Add New Author</h3>
        <form method="post">
            <input type="text" name="name" placeholder="Author Name" required>
            <label>Select eBooks</label>
            <select name="ebooks[]" multiple class="select2">
                <?php foreach ($ebooks as $ebook): ?>
                <option value="<?php echo $ebook['id']; ?>"><?php echo htmlspecialchars($ebook['title']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="add_author" value="Add Author">
        </form>

        <h3>Author List</h3>
        <table>
            <tr>
                <th>Name</th>
                <th>eBooks</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($authors as $author): ?>
            <tr>
                <td><?php echo htmlspecialchars($author['name']); ?></td>
                <td>
                    <?php
                    $author_ebooks = $conn->query("SELECT e.title FROM author_ebook ae JOIN ebooks e ON ae.ebook_id = e.id WHERE ae.author_id = " . $author['id'])->fetch_all(MYSQLI_ASSOC);
                    echo implode(", ", array_column($author_ebooks, 'title'));
                    ?>
                </td>
                <td>
                    <button onclick="editAuthor(<?php echo $author['id']; ?>)">Edit</button>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="author_id" value="<?php echo $author['id']; ?>">
                        <input type="submit" name="delete_author" value="Delete" class="button delete" onclick="return confirm('Are you sure you want to delete this author?');">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <div id="editAuthorModal" style="display: none;">
            <h3>Edit Author</h3>
            <form method="post" id="editAuthorForm">
                <input type="hidden" name="author_id" id="editAuthorId">
                <input type="text" name="name" id="editAuthorName" required>
                <label>Select eBooks</label>
                <select name="ebooks[]" multiple id="editAuthorEbooks" class="select2">
                    <?php foreach ($ebooks as $ebook): ?>
                    <option value="<?php echo $ebook['id']; ?>"><?php echo htmlspecialchars($ebook['title']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" name="edit_author" value="Save Changes">
            </form>
        </div>

        <p><a href="admin_dashboard.php" class="button">Back to Admin Dashboard</a></p>
    </div>

    <script>
    $(document).ready(function() {
        $('.select2').select2();
    });

    function editAuthor(authorId) {
        // Fetch author details via AJAX and populate the form
        $.ajax({
            url: 'get_author_details.php',
            method: 'GET',
            data: { author_id: authorId },
            success: function(response) {
                var author = JSON.parse(response);
                $('#editAuthorId').val(author.id);
                $('#editAuthorName').val(author.name);
                $('#editAuthorEbooks').val(author.ebooks).trigger('change');
                $('#editAuthorModal').show();
            }
        });
    }
    </script>
</body>
</html>
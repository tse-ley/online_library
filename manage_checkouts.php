<?php
session_start();
require_once 'db_connect.php';


// Handle form submission for updating checkout details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_checkout'])) {
        $checkout_id = $_POST['checkout_id'];
        $return_date = $_POST['return_date'];
        $returned = isset($_POST['returned']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE checkouts SET return_date = ?, returned = ? WHERE id = ?");
        $stmt->bind_param("sii", $return_date, $returned, $checkout_id);
        $stmt->execute();

        if ($returned) {
            $stmt = $conn->prepare("UPDATE ebooks e JOIN checkouts c ON e.id = c.ebook_id SET e.available = 1 WHERE c.id = ?");
            $stmt->bind_param("i", $checkout_id);
            $stmt->execute();
        }
    }
}

// Fetch checkout data to display
$checkouts = $conn->query("SELECT c.*, u.username, e.title FROM checkouts c 
                           JOIN users u ON c.user_id = u.id 
                           JOIN ebooks e ON c.ebook_id = e.id 
                           ORDER BY c.issue_date DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Checkouts</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Manage Checkouts</h2>
        <table>
            <tr>
                <th>User</th>
                <th>Book</th>
                <th>Issue Date</th>
                <th>Return Date</th>
                <th>Returned</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($checkouts as $checkout): ?>
            <tr>
                <td><?php echo htmlspecialchars($checkout['username']); ?></td>
                <td><?php echo htmlspecialchars($checkout['title']); ?></td>
                <td><?php echo htmlspecialchars($checkout['issue_date']); ?></td>
                <td><?php echo htmlspecialchars($checkout['return_date']); ?></td>
                <td><?php echo $checkout['returned'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="checkout_id" value="<?php echo $checkout['id']; ?>">
                        <input type="date" name="return_date" value="<?php echo $checkout['return_date']; ?>">
                        <input type="checkbox" name="returned" <?php if ($checkout['returned']) echo 'checked'; ?>> Returned
                        <input type="submit" name="update_checkout" value="Update">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>

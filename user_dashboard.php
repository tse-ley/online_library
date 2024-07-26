<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$result = $conn->query("SELECT * FROM ebooks WHERE available = 1");
$ebooks = $result->fetch_all(MYSQLI_ASSOC);


$stmt = $conn->prepare("SELECT c.id, e.title, c.issue_date, c.return_date, c.returned FROM checkouts c JOIN ebooks e ON c.ebook_id = e.id WHERE c.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$checkouts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Welcome to Your Dashboard</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <p class="success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <h3>Available E-books</h3>
        <table id="ebooks-table">
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Action</th>
            </tr>
            <?php foreach ($ebooks as $ebook): ?>
            <tr>
                <td><?php echo htmlspecialchars($ebook['title']); ?></td>
                <td><?php echo htmlspecialchars($ebook['author']); ?></td>
                <td><a href="checkout.php?id=<?php echo $ebook['id']; ?>" class="button checkout-btn">Checkout</a></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h3>Your Checkouts</h3>
        <table id="checkouts-table">
            <tr>
                <th>Title</th>
                <th>Issue Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($checkouts as $checkout): ?>
            <tr>
                <td><?php echo htmlspecialchars($checkout['title']); ?></td>
                <td><?php echo $checkout['issue_date']; ?></td>
                <td><?php echo $checkout['return_date']; ?></td>
                <td><?php echo $checkout['returned'] ? 'Returned' : 'Checked Out'; ?></td>
                <td>
                    <?php if (!$checkout['returned']): ?>
                        <a href="return_ebook.php?id=<?php echo $checkout['id']; ?>" class="button return-btn">Return</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p><a href="search.php" class="button">Search E-books</a></p>
        <p><a href="logout.php" class="button">Logout</a></p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
       
        var checkoutButtons = document.querySelectorAll('.checkout-btn');
        checkoutButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to check out this e-book?')) {
                    e.preventDefault();
                }
            });
        });

     
        var returnButtons = document.querySelectorAll('.return-btn');
        returnButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to return this e-book?')) {
                    e.preventDefault();
                }
            });
        });

    
    
        var checkoutsTable = document.getElementById('checkouts-table');
        var rows = checkoutsTable.getElementsByTagName('tr');
        for (var i = 1; i < rows.length; i++) {
            var returnDate = new Date(rows[i].cells[2].innerHTML);
            var today = new Date();
            if (returnDate < today && rows[i].cells[3].innerHTML === 'Checked Out') {
                rows[i].style.backgroundColor = '#ffcccc';
            }
        }
    });
    </script>
</body>
</html>
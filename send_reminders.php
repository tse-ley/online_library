<?php
require_once 'db_connect.php';

$stmt = $conn->prepare("
    SELECT u.email, e.title, c.return_date 
    FROM checkouts c
    JOIN users u ON c.user_id = u.id
    JOIN ebooks e ON c.ebook_id = e.id
    WHERE c.return_date < CURDATE() AND c.returned = 0
");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $to = $row['email'];
    $subject = "Overdue E-book Reminder";
    $message = "Dear user,\n\nThe e-book '{$row['title']}' was due on {$row['return_date']}. Please return it as soon as possible.\n\nThank you,\nLibrary Management System";
    $headers = "From: library@example.com";

    mail($to, $subject, $message, $headers);
}

echo "Reminders sent successfully.";
?>
<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $checkout_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Verify that the checkout belongs to the current user
    $stmt = $conn->prepare("SELECT ebook_id FROM checkouts WHERE id = ? AND user_id = ? AND returned = 0");
    $stmt->bind_param("ii", $checkout_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $checkout = $result->fetch_assoc();
        $ebook_id = $checkout['ebook_id'];

        // Update checkout status
        $stmt = $conn->prepare("UPDATE checkouts SET returned = 1, return_date = CURRENT_DATE() WHERE id = ?");
        $stmt->bind_param("i", $checkout_id);
        
        if ($stmt->execute()) {
            // Update book availability
            $conn->query("UPDATE ebooks SET available = 1 WHERE id = $ebook_id");
            $_SESSION['message'] = "E-book returned successfully.";
        } else {
            $_SESSION['error'] = "Error returning the e-book. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Invalid return request.";
    }
    
    header("Location: user_dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: user_dashboard.php");
    exit();
}
?>
<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $ebook_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    $issue_date = date('Y-m-d');
    $return_date = date('Y-m-d', strtotime('+30 days')); 

    $stmt = $conn->prepare("SELECT available FROM ebooks WHERE id = ?");
    $stmt->bind_param("i", $ebook_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    if ($book['available']) {
        
        $stmt = $conn->prepare("INSERT INTO checkouts (user_id, ebook_id, issue_date, return_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $ebook_id, $issue_date, $return_date);
        
        if ($stmt->execute()) {
            
            $conn->query("UPDATE ebooks SET available = 0 WHERE id = $ebook_id");
            $_SESSION['message'] = "E-book checked out successfully. Please return by $return_date.";
        } else {
            $_SESSION['error'] = "Error checking out the e-book. Please try again.";
        }
    } else {
        $_SESSION['error'] = "This e-book is currently unavailable.";
    }
    
    header("Location: user_dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: user_dashboard.php");
    exit();
}
?>
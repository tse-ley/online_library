<?php

$db_host = 'localhost';  
$db_name = 'library';
$db_user = 'root';  
$db_pass = '';  


try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
     $conn->set_charset("utf8mb4");

} catch (Exception $e) {
   
    error_log("Database connection error: " . $e->getMessage());
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}


if (!function_exists('sanitize_input')) {
    function sanitize_input($conn, $input) {
        return mysqli_real_escape_string($conn, trim($input));
    }
}

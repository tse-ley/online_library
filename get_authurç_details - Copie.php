<?php
require_once 'db_connect.php';

if (isset($_GET['author_id'])) {
    $author_id = $_GET['author_id'];
    
    $author = $conn->query("SELECT * FROM authors WHERE id = $author_id")->fetch_assoc();
    $author_ebooks = $conn->query("SELECT ebook_id FROM author_ebook WHERE author_id = $author_id")->fetch_all(MYSQLI_ASSOC);
    
    $author['ebooks'] = array_column($author_ebooks, 'ebook_id');
    
    echo json_encode($author);
}
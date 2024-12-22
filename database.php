<?php
function getConnection() {
    $host = 'localhost';
    $db = 'dukali';
    $user = 'your_username';
    $pass = 'your_password';

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }
    return $conn;
}

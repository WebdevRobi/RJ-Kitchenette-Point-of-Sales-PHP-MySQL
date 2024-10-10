<?php
$host = 'localhost';
$user = 'root'; // Change if necessary
$password = '';
$dbname = 'pos';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
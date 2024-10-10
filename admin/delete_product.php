<?php
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $product_id = $_POST['delete_product_id'];

    // Database connection
    $host = "localhost"; // Your database host
    $db_user = "root"; // Your database username
    $db_password = ""; // Your database password
    $db_name = "pos"; // Your database name

    $conn = new mysqli($host, $db_user, $db_password, $db_name);

    if ($conn->connect_error) {
        header("HTTP/1.1 500 Internal Server Error");
        exit();
    }

    // Delete product query
    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product_id);

    if ($stmt->execute()) {
        $response = ['success' => true];
    } else {
        $response = ['success' => false];
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    header("HTTP/1.1 400 Bad Request");
}
?>
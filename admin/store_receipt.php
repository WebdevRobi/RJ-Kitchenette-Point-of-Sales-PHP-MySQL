<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

// Database connection
$host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "pos";

$conn = new mysqli($host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get data from the AJAX request
$data = json_decode(file_get_contents('php://input'), true);
$orderSummary = $data['orderSummary'];
$totalAmount = $data['totalAmount'];
$paymentMethod = $data['paymentMethod'];
$pwd_discount = $data['pwd_discount'] ?? false; // Get PWD flag (default to false)
$employee_id = $_SESSION['employee_id'];

// Apply 5% discount if the customer is a PWD
if ($pwd_discount) {
    $discount = 0.05; // 5% discount
    $discountedAmount = $totalAmount * (1 - $discount);
} else {
    $discountedAmount = $totalAmount;
}

// Create an invoice number
$invoice_number = uniqid("INV-");

// Prepare to insert order into the database
$stmt = $conn->prepare("INSERT INTO orders (employee_id, order_details, total_amount, payment_method, invoice_number, pwd_discount) VALUES (?, ?, ?, ?, ?, ?)");
$order_details = json_encode($orderSummary);
$stmt->bind_param("isdssi", $employee_id, $order_details, $discountedAmount, $paymentMethod, $invoice_number, $pwd_discount);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Order has been successfully processed.', 'discountedAmount' => $discountedAmount]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save the order.']);
}

$stmt->close();
$conn->close();
?>
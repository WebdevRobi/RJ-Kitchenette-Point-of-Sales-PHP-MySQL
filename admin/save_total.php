<?php
session_start();

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Check if totalAmount is set
if (isset($data['totalAmount'])) {
    // Store totalAmount in session
    $_SESSION['totalAmount'] = $data['totalAmount'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
}
?>

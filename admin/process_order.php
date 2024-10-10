<?php
session_start();
// include your database connection here

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the product ID and quantity from the POST request
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Sanitize inputs
    $product_id = filter_var($product_id, FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_INT);

    // Debugging output
    error_log("Product ID: " . $product_id); // Log product ID
    error_log("Quantity: " . $quantity); // Log quantity

    // Fetch the product from the database
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // Debugging output
    error_log("Product: " . print_r($product, true)); // Log product details

    // Check if product exists and sufficient stock is available
    if ($product) {
        // Your existing logic here
    } else {
        // Product not found
        $response = [
            'success' => false,
            'message' => 'Product not found.',
        ];
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>

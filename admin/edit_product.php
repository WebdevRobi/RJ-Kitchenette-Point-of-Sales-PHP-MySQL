<?php
// edit_product.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if 'id' exists in the POST request
    if (isset($_POST['id']) && isset($_POST['photo']) &&
        isset($_POST['product_name']) && isset($_POST['product_description']) &&
        isset($_POST['category']) && isset($_POST['price']) &&
        isset($_POST['stock'])) {
        
        // Retrieve and sanitize the posted data
        $id = intval($_POST['id']); // Cast to int
        $product_name = $_POST['product_name'];
        $product_description = $_POST['product_description'];
        $category = $_POST['category'];
        $price = floatval($_POST['price']); // Cast to float
        $stock = intval($_POST['stock']); // Cast to int
        $photo = $_POST['photo'];

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
        // Prepare the update statement
        $stmt = $conn->prepare("UPDATE products SET product_name = ?, product_description = ?, category = ?, price = ?, stock = ?, photo = ? WHERE id = ?");
        $stmt->bind_param("sssdisi", $product_name, $product_description, $category, $price, $stock, $photo, $id); // Adjust types

        // Execute and check for errors
        if ($stmt->execute()) {
            echo "Product updated successfully";
        } else {
            echo "Error updating product: " . $stmt->error;
        }

        // Clean up
        $stmt->close();
        $conn->close();
    } else {
        echo "Error: Missing required fields.";
    }
}
?>
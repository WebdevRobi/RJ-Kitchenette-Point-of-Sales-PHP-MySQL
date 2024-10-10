<?php
session_start();
include 'config/db.php'; // Ensure this path is correct

// Check if user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../index.php"); // Redirect to login page if not logged in
    exit();
}

// Logout handling
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php"); // Redirect to login page after logout
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_number = $conn->real_escape_string($_POST['invoice_number']);
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $price = $conn->real_escape_string($_POST['price']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert refund into the refunds table
        $stmt1 = $conn->prepare("INSERT INTO refunds (invoice_number, product_name, price, payment_method) VALUES (?, ?, ?, ?)");
        $stmt1->bind_param('ssds', $invoice_number, $product_name, $price, $payment_method);
        $stmt1->execute();

        if ($stmt1->affected_rows === 0) {
            throw new Exception("Failed to insert refund.");
        }

        // Delete order from orders table
        $stmt2 = $conn->prepare("DELETE FROM orders WHERE invoice_number = ? AND product_name = ? LIMIT 1");
        $stmt2->bind_param('si', $invoice_number, $product_name);
        $stmt2->execute();

        // Check if an order was deleted
        if ($stmt2->affected_rows === 0) {
            throw new Exception("No matching order found.");
        }

        // Update stock in products table
        $stmt3 = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
        $stmt3->bind_param('ii', $quantity, $product_id);
        $stmt3->execute();

        // Commit transaction
        $conn->commit();

        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Refund processed successfully!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'refund.php'; // Redirect back to the refund form
                });
              </script>";
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error processing refund!',
                    text: '{$e->getMessage()}',
                });
              </script>";
    }
}

// Close the connection
$conn->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <?php include 'includes/web_icon.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="flex">
    <?php include 'includes/sidebar.php'; ?>
       <!-- Main content -->
       <div class="flex-grow p-6 bg-gray-100">
        <nav class="flex items-center justify-between bg-white shadow-md p-4">
            <div>
                <h2 class="text-2xl font-bold">Welcome, <?php echo $_SESSION['full_name']; ?></h2>
            </div>
            <div>
                <button id="logoutButton" class="bg-red-500 text-white font-bold py-2 px-4 rounded">Logout</button>
            </div>
        </nav>
        <br>
    <div class="container mx-auto bg-white p-5 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Refund Form</h2>
        <form id="refundForm" method="POST" action="refund.php">
            <div class="mb-4">
                <label class="block text-gray-700">Invoice Number</label>
                <input type="text" name="invoice_number" class="border p-2 w-full" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Product Name</label>
                <input type="text" name="product_name" class="border p-2 w-full" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Price</label>
                <input type="text" name="price" class="border p-2 w-full" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Payment Method</label>
                <input type="text" name="payment_method" class="border p-2 w-full" required>
            </div>
            
            
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Process Refund</button>
        </form>
    </div>
</body>
</html>
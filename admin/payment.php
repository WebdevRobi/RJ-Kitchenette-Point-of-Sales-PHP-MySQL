<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../index.php"); // Redirect to login if not logged in
    exit();
}

// Sample products array (this should come from your order summary)
$products = [
    ['name' => 'Product 1', 'quantity' => 2, 'price' => 100.00],
    ['name' => 'Product 2', 'quantity' => 1, 'price' => 150.00],
];

// Calculate subtotal for each product
$totalAmount = 0;
foreach ($products as $product) {
    $subtotal = $product['quantity'] * $product['price'];
    $totalAmount += $subtotal;
}

$invoiceNumber = uniqid('INV-'); // Generate a unique invoice number
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Payment Example</title>
    <style>
        /* Styling for the receipt */
        .receipt {
            display: none; /* Hide by default */
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Order Summary Section -->
    <div class="p-5 bg-white shadow-md rounded m-4">
        <h2 class="text-xl font-bold mb-4">Order Summary</h2>
        <table class="w-full">
            <thead>
                <tr>
                    <th class="text-left">Product</th>
                    <th class="text-left">Quantity</th>
                    <th class="text-left">Price</th>
                    <th class="text-left">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <?php $subtotal = $product['quantity'] * $product['price']; ?>
                <tr>
                    <td><?php echo $product['name']; ?></td>
                    <td><?php echo $product['quantity']; ?></td>
                    <td>₱<?php echo number_format($product['price'], 2); ?></td>
                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h3 class="mt-4 font-bold">Total: ₱<span id="totalAmount"><?php echo number_format($totalAmount, 2); ?></span></h3>
    </div>

    <!-- Payment Button Section -->
    <div class="mt-4 text-center">
        <button id="payButton" class="bg-green-500 text-white font-bold py-2 px-4 rounded">Pay</button>
    </div>

    <!-- Payment Method Modal -->
    <div id="paymentModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-5 rounded shadow-md w-1/3">
            <h3 class="text-lg font-bold mb-4">Select Payment Method</h3>
            <select id="paymentMethod" class="border rounded-md p-2 w-full mb-4">
                <option value="">Select Payment Method</option>
                <option value="Cash">Cash</option>
                <option value="Gcash">Gcash</option>
                <option value="Maya">Maya</option>
                <option value="Credit/Debit Card">Credit/Debit Card</option>
            </select>
            <button id="payModalButton" class="bg-blue-500 text-white font-bold py-2 px-4 rounded">Pay</button>
            <button id="closeModal" class="bg-red-500 text-white font-bold py-2 px-4 rounded mt-2">Close</button>
        </div>
    </div>

    <!-- Receipt Section -->
    <div id="receipt" class="receipt">
        <h3 class="text-xl font-bold">Receipt</h3>
        <p><strong>Company Logo:</strong> <img src="path_to_your_logo.png" alt="Company Logo" class="w-20 h-20 rounded-full"></p>
        <p><strong>Invoice Number:</strong> <span id="invoiceNumber"></span></p>
        <p><strong>Payment Method:</strong> <span id="receiptPaymentMethod"></span></p>
        <h4 class="mt-4">Products:</h4>
        <ul id="receiptProducts"></ul>
        <h4 class="mt-4 font-bold">Total Amount: ₱<span id="receiptTotalAmount"></span></h4>
    </div>

    <script>
        document.getElementById('payButton').addEventListener('click', function() {
            document.getElementById('paymentModal').classList.remove('hidden'); // Show modal
        });

        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('paymentModal').classList.add('hidden'); // Hide modal
        });

        document.getElementById('payModalButton').addEventListener('click', function() {
            const paymentMethod = document.getElementById('paymentMethod').value;
            const totalAmount = document.getElementById('totalAmount').innerText;

            if (paymentMethod) {
                // Populate receipt
                const invoiceNumber = "<?php echo $invoiceNumber; ?>"; // Get invoice number from PHP
                document.getElementById('invoiceNumber').innerText = invoiceNumber;
                document.getElementById('receiptPaymentMethod').innerText = paymentMethod;
                document.getElementById('receiptTotalAmount').innerText = totalAmount;

                // Add products to receipt
                const receiptProductsList = document.getElementById('receiptProducts');
                receiptProductsList.innerHTML = ''; // Clear previous entries
                <?php foreach ($products as $product): ?>
                    const li = document.createElement('li');
                    li.innerText = "<?php echo $product['name']; ?> - Qty: <?php echo $product['quantity']; ?> - Price: ₱<?php echo number_format($product['price'], 2); ?> - Subtotal: ₱<?php echo number_format($product['quantity'] * $product['price'], 2); ?>";
                    receiptProductsList.appendChild(li);
                <?php endforeach; ?>

                document.getElementById('receipt').style.display = 'block'; // Show receipt
                document.getElementById('paymentModal').classList.add('hidden'); // Hide modal
            } else {
                Swal.fire('Error!', 'Please select a payment method.', 'error');
            }
        });
    </script>

</body>
</html>

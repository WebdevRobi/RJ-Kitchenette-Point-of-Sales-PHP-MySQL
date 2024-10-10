<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "pos";

$conn = new mysqli($host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the latest order for the current employee
$employee_id = (int)$_SESSION['employee_id'];
$order_query = $conn->prepare("SELECT * FROM orders WHERE employee_id = ? ORDER BY date DESC LIMIT 1");
$order_query->bind_param("i", $employee_id);
$order_query->execute();
$result = $order_query->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
    $order_details = json_decode($order['order_details'], true);
    $total_amount = (float)$order['total_amount'];
    $isPWD = isset($order['is_pwd']) ? $order['is_pwd'] : false; // Check for the PWD discount
    $payment_method = $order['payment_method'];

    // Calculate discount if the order is for a PWD
    $discount = $isPWD ? 0.05 : 0; // 5% discount for PWD
    $discount_amount = $total_amount * $discount; // Calculate discount amount
    $final_amount = $total_amount - $discount_amount; // Calculate final amount after discount

    // Format the date
    $date = new DateTime($order['date']);
    $formatted_date = $date->format('F d, Y');

    $invoice_number = isset($order['invoice_number']) ? htmlspecialchars($order['invoice_number']) : 'N/A';
} else {
    // Defaults in case no order is found
    $order_details = [];
    $total_amount = 0;
    $discount_amount = 0;
    $final_amount = 0;
    $payment_method = 'N/A';
    $formatted_date = 'N/A'; // Use default date
    $invoice_number = 'N/A';
}

// Update this to set the appropriate icon based on the payment method
switch ($payment_method) {
    case "credit_card":
        $icon_path = "payment_icon/credit_card.png";
        break;
    case "debit_card":
        $icon_path = "payment_icon/debit_card.png";
        break;
    case "gcash":
        $icon_path = "payment_icon/gcash.png";
        break;
    case "maya":
        $icon_path = "payment_icon/maya_icon.png";
        break;
    case "cash":
        $icon_path = "payment_icon/cash.png"; // You might want to leave this empty for cash
        break;
    default:
        $icon_path = "payment_icon/cash.png"; // Default behavior if no valid payment method is selected
        break;
}


$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include 'includes/web_icon.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media print {
            #printButton {
                display: none; // Hide print button during print
            }
            .logo {
                width: 100px; // Adjust logo size
                height: auto;
                border-radius: 50%; // Circular logo
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-md mx-auto p-5 bg-white shadow-lg rounded-lg mt-10">
        <div class="flex flex-col items-center mb-4">
            <img src="img/logo.jpg" alt="Company Logo" class="logo w-24 h-24 rounded-full mb-2"> 
            <div class="text-center">
                <h1 class="text-xl font-bold">R/J Kitchenette</h1>
                <p class="text-sm text-gray-600">Fatima Village, Porac Pampanga, Philippines</p>
                <p class="text-sm text-gray-600">Phone: (63) 956-738-7411</p>
            </div>
        </div>
        
        <h2 class="text-2xl font-bold text-center mb-4">Receipt</h2>
        <p class="font-semibold">Invoice Number: <span class="font-normal"><?php echo htmlspecialchars($invoice_number); ?></span></p>
        <p class="font-semibold">Date: <span class="font-normal"><?php echo htmlspecialchars($formatted_date); ?></span></p>

        <?php if (!empty($order_details)) { ?>
            <div class="mt-4">
                <h3 class="font-semibold text-lg">Order Details</h3>
                <table class="min-w-full bg-white border border-gray-300 mt-2">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border border-gray-300 px-4 py-2 text-left">QTY</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Item</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_details as $item) { ?>
                            <tr>
                                <td class="border border-gray-300 px-4 py-2"><?php echo $item['quantity']; ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="border border-gray-300 px-4 py-2">₱<?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="mt-4 flex justify-between font-semibold">
                    <h3>Total Amount:</h3>
                    <p>₱<?php echo number_format($total_amount, 2); ?></p>
                </div>
                <?php if ($isPWD) { ?>
                    <div class="flex justify-between font-semibold text-red-500">
                        <h3>Discount (PWD 5%):</h3>
                        <p>-₱<?php echo number_format($discount_amount, 2); ?></p>
                    </div>
                <?php } ?>
                <div class="flex justify-between font-semibold">
                    <h3>Final Amount:</h3>
                    <p>₱<?php echo number_format($final_amount, 2); ?></p>
                </div>
                <div class="flex justify-between font-semibold">
    <h3>Payment Method:</h3>
    <div class="flex items-center">
        <p><?php echo htmlspecialchars($payment_method); ?></p>
        <img id="receiptPaymentIcon" src="" alt="Payment Method Icon" style="margin-left: 10px; width: 30px; height: auto; display: <?php echo $payment_method ? 'inline' : 'none'; ?>;">
    </div>
</div>
            </div>
        <?php } else { ?>
            <p class="text-red-500">No order details found.</p>
        <?php } ?>

        <div class="text-center mt-6">
            <button id="printButton" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition" onclick="window.print()">Print Receipt</button>
        </div>
    </div>
</body>
</html>

<script>
    document.getElementById("receiptPaymentIcon").src = "<?php echo $icon_path; ?>";
</script>
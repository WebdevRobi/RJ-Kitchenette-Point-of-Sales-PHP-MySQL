<?php
// Start the session
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['full_name'])) {
    header("Location: login.php");
    exit();
}

// Database connection details
$host = "localhost"; // Change if required
$db_user = "root";   // Change if required
$db_password = "";   // Change if required
$db_name = "pos";    // Change if required

$conn = new mysqli($host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize the date variables
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// Prepare the SQL query
$sql = "SELECT payment_method, invoice_number, order_details, date, SUM(total_amount) AS total_sales FROM orders";
$conditions = [];

if ($start_date && $end_date) {
    $conditions[] = "date BETWEEN '$start_date' AND '$end_date'";
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " GROUP BY date ORDER BY date ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Sales Per Date</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
    @media print {
            body * {
                visibility: hidden; /* Hide everything */
            }
            .printable-area, .printable-area * {
                visibility: visible; /* Show only printable area */
            }
            .printable-area {
                position: absolute; 
                left: 90; 
                top: 0; 
                width: 90%; /* Make it full width, if necessary */
            }
            /* Hide elements not to be printed */
            .navbar, .sidebar, .date-filter, button {
                display: none; /* Hide navbar, sidebar, date filter, and buttons */
            }
        }
    </style>
</head>

<body class="bg-gray-100 flex">
    
    <!-- Sidebar -->
    <div class="sidebar bg-gray-800 text-white h-screen p-5 w-64">
        <img src="img/logo.jpg" alt="Company Logo" class="w-16 h-16 rounded-full mx-auto mb-4">
        <h2 class="text-center font-bold text-xl mb-5">R/J Kitchenette</h2>
        <ul>
            <li class="mb-3"><a href="dashboard.php" class="flex items-center hover:text-gray-300"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a></li>
            <li class="mb-3"><a href="add_products.php" class="flex items-center hover:text-gray-300"><i class="fas fa-box mr-2"></i> Products</a></li>
            <li class="mb-3"><a href="add_category.php" class="flex items-center hover:text-gray-300"><i class="fas fa-tags mr-2"></i> Category</a></li>
            <li class="mb-3"><a href="pos_sales.php" class="flex items-center hover:text-gray-300"><i class="fas fa-cash-register mr-2"></i> POS</a></li>
            <li class="mb-3"><a href="sales.php" class="flex items-center hover:text-gray-300"><i class="fas fa-peso-sign mr-2"></i> Sales Monitoring</a></li>
            <li class="mb-3"><a href="add_supplier.php" class="flex items-center hover:text-gray-300"><i class="fas fa-store mr-2"></i> Supplier</a></li>
            <li class="mb-3"><a href="attendance.php" class="flex items-center hover:text-gray-300"><i class="fas fa-calendar-check mr-2"></i> Attendance</a></li>
            <li class="mb-3"><a href="leaves.php" class="flex items-center hover:text-gray-300"><i class="fas fa-file-alt mr-2"></i> Leaves</a></li>
            <li class="mb-3"><a href="logout.php" class="flex items-center hover:text-gray-300"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main content -->
    <div class="flex-1 p-5">
        <!-- Navbar -->
        <nav class="navbar flex items-center justify-between bg-white shadow-md p-4 mb-5">
            <div>
                <h2 class="text-2xl font-bold">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>
            </div>
        </nav>

        <h1 class="text-2xl font-bold mb-5">Total Sales Per Date</h1>

        <!-- Date Filter Form -->
        <form method="POST" class="date-filter mb-5">
            <div class="flex space-x-4 mb-4">
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="flex-1 p-2 border rounded" required>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="flex-1 p-2 border rounded" required>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Search</button>
            </div>
        </form>

        <!-- Print Button -->
        <button onclick="window.print()" class="mb-5 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition">
            Print Sales Record
        </button>

        <div class="printable-area">
            <h2 class="text-xl font-semibold mb-3">Sales Summary</h2>
            <table class="min-w-full border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2">Order Date</th>
                        <th class="border px-4 py-2">Total Sales (₱)</th>
                        <th class="border px-4 py-2">Payment Method</th>
                        <th class="border px-4 py-2">Invoice Number</th>
                        <th class="border px-4 py-2">Order Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php 
                            $current_date = null; 
                            $total_sales_for_date = 0; // Initialize total sales for the date
                        ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                                // Get formatted date
                                $date = new DateTime($row['date']); 
                                $formatted_date = $date->format('F j, Y');

                                // Check if we need to reset the total for a new date
                                if ($current_date !== $formatted_date) {
                                    if ($current_date !== null) {
                                        // Output the total sales for the previous date
                                        echo "<tr><td colspan='4' class='border px-4 py-2 font-bold text-right'>Total Sales for {$current_date}: </td><td class='border px-4 py-2 font-bold'>₱" . number_format($total_sales_for_date, 2) . "</td></tr>";
                                    }
                                    // Reset total sales for the new date
                                    $current_date = $formatted_date;
                                    $total_sales_for_date = 0; // Reset total sales for the new date
                                }

                                // Add to total sales for the present date
                                $total_sales_for_date += $row['total_sales']; 
                            ?>
                            <tr>
                                <td class="border px-4 py-2"><?php echo $formatted_date; ?></td>
                                <td class="border px-4 py-2"><?php echo number_format($row['total_sales'], 2); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                                <td class="border px-4 py-2">
                                    <?php
                                        // Check if order_details is set in the row
                                        if (isset($row['order_details'])) {
                                            // Decode the JSON data for order details
                                            $order_details = json_decode($row['order_details'], true);

                                            // Check if order details is valid and display it
                                            if (is_array($order_details)) {
                                                foreach ($order_details as $item) {
                                                    // Adjust to match your JSON structure
                                                    echo htmlspecialchars($item['name']) . " - Quantity: " . htmlspecialchars($item['quantity']) . "<br>";
                                                }
                                            } else {
                                                echo "No details available.";
                                            }
                                        } else {
                                            echo "No details available.";
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php 
                            // Output the total sales for the last date
                            if ($current_date !== null) {
                                echo "<tr><td colspan='4' class='border px-4 py-2 font-bold text-right'>Total Sales for {$current_date}: </td><td class='border px-4 py-2 font-bold'>₱" . number_format($total_sales_for_date, 2) . "</td></tr>";
                            }
                        ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="border px-4 py-2 text-center">No sales recorded for this date range</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../index.php");
    exit();
}

// Database connection
$host = "localhost"; // Your database host
$db_user = "root"; // Your database username
$db_password = ""; // Your database password
$db_name = "pos"; // Your database name

$conn = new mysqli($host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from the database
$products = [];
$result = $conn->query("SELECT id, product_name, product_description, price, stock, photo FROM products");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle form submission for sales
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Fetch product details
    $product_query = $conn->query("SELECT * FROM products WHERE id = $product_id");
    $product = $product_query->fetch_assoc();

    // Check if the product exists and sufficient stock is available
    if ($product) {
        // Calculate subtotal
        $subtotal = $product['price'] * $quantity;

        // Check stock availability
        if ($product['stock'] >= $quantity) {
            // Update stock
            $new_stock = $product['stock'] - $quantity;
            $conn->query("UPDATE products SET stock = $new_stock WHERE id = $product_id");

            // Return JSON response for JavaScript to handle
            echo json_encode([
                'success' => true,
                'product_name' => $product['product_name'],
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'photo' => $product['photo']
            ]);
            exit; 
        } else {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Sales</title>
    <style>
        #paymentModal {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100 flex">
    <div class="bg-gray-800 text-white h-screen p-5 w-64">
        <img src="img/logo.jpg" alt="Company Logo" class="w-16 h-16 rounded-full mx-auto mb-4">
        <h2 class="text-center font-bold text-xl mb-5">R/J Kitchenette</h2>
        <ul>
        <li class="mb-3"><a href="dashboard.php" class="flex items-center hover:text-gray-300"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a></li>
            <li class="mb-3"><a href="add_products.php" class="flex items-center hover:text-gray-300"><i class="fas fa-box mr-2"></i> Products</a></li>
            <li class="mb-3"><a href="add_category.php" class="flex items-center hover:text-gray-300"><i class="fas fa-tags mr-2"></i> Category</a></li>
            <li class="mb-3"><a href="pos_sales.php" class="flex items-center hover:text-gray-300"><i class="fas fa-cash-register mr-2"></i> POS</a></li>
            <li class="mb-3"><a href="sales.php" class="flex items-center hover:text-gray-300"><i class="fas fa-peso-sign mr-2"></i> Sales</a></li>
            <li class="mb-3"><a href="attendance.php" class="flex items-center hover:text-gray-300"><i class="fas fa-calendar-check mr-2"></i> Attendance</a></li>
            <li class="mb-3"><a href="leaves.php" class="flex items-center hover:text-gray-300"><i class="fas fa-file-alt mr-2"></i> Leaves</a></li>
            <li class="mb-3"><a href="logout.php" class="flex items-center hover:text-gray-300"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a></li>
        </ul>
    </div>

    <div class="flex-grow p-6 bg-gray-100">
        <nav class="flex items-center justify-between bg-white shadow-md p-4">
            <div>
                <h2 class="text-2xl font-bold">Welcome, <?php echo $_SESSION['full_name']; ?></h2>
            </div>
            <div>
                <button id="logoutButton" class="bg-red-500 text-white font-bold py-2 px-4 rounded">Logout</button>
            </div>
        </nav>

        <div class="flex justify-between container mx-auto bg-white p-5 rounded shadow-md mt-4">
            <div class="w-3/5 pr-4">
                <h2 class="text-2xl font-bold mb-4">Products</h2>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach ($products as $product): ?>
                        <div class="border rounded-lg p-4 shadow">
                            <img src="product_uploads/<?php echo htmlspecialchars($product['photo']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="w-24 h-24 object-cover mb-2">
                            <h3 class="font-semibold"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($product['product_description']); ?></p>
                            <p class="text-gray-600">Price: ₱<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>
                            <p class="text-gray-600">Stock: <?php echo htmlspecialchars($product['stock']); ?></p>
                            <form method="POST" class="mt-2 add-to-order-form">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                <input type="number" name="quantity" min="1" max="<?php echo htmlspecialchars($product['stock']); ?>" required class="border rounded-md p-1" placeholder="Qty" value="1">
                                <button type="submit" class="bg-blue-500 text-white font-bold py-1 px-2 rounded mt-2">Add to Order</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="w-2/5 pl-4">
                <h2 class="text-2xl font-bold mb-4">Order Summary</h2>
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 px-4 py-2">QTY</th>
                            <th class="border border-gray-300 px-4 py-2">Order</th>
                            <th class="border border-gray-300 px-4 py-2">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="salesSummary">
                    </tbody>
                </table>
                <div class="mt-4 text-center">
                    <h3 class="font-semibold">Total: ₱<span id="totalAmount">0.00</span></h3>
                    <button id="payButton" class="bg-green-500 text-white font-bold py-2 px-4 rounded">Pay</button>
                    <button class="bg-gray-300 text-black font-bold py-2 px-4 rounded">Pay Later</button>
                </div>
            </div>
        </div>
    </div>

    <div id="paymentModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white p-5 rounded shadow-md w-1/3">
            <h3 class="text-lg font-bold mb-4">Payment Details</h3>
            <div id="modalContent"></div>
            <select id="paymentMethod" class="border rounded-md p-1 mt-2 mb-4">
                <option value="">Select Payment Method</option>
                <option value="cash">Cash</option>
                <option value="credit_card">Credit Card</option>
                <option value="debit_card">Debit Card</option>
                <option value="gcash">GCash</option>
            </select>
            <button id="confirmPayment" class="bg-blue-500 text-white font-bold py-2 px-4 rounded">Confirm Payment</button>
            <button onclick="document.getElementById('paymentModal').style.display='none'" class="bg-red-500 text-white font-bold py-2 px-4 rounded">Cancel</button>
        </div>
    </div>

    <script>
        const orderSummary = [];
        let totalAmount = 0;

        document.querySelectorAll('.add-to-order-form').forEach(form => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const formData = new FormData(form);

                const response = await fetch('create_order.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    orderSummary.push({
                        name: data.product_name,
                        quantity: parseInt(formData.get('quantity')),
                        subtotal: data.subtotal,
                    });

                    totalAmount += data.subtotal;
                    updateSalesSummary();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error'
                    });
                }
            });
        });

        function updateSalesSummary() {
            const salesSummaryElement = document.getElementById('salesSummary');
            salesSummaryElement.innerHTML = '';

            orderSummary.forEach(item => {
                salesSummaryElement.innerHTML += `
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">${item.quantity}</td>
                        <td class="border border-gray-300 px-4 py-2">${item.name}</td>
                        <td class="border border-gray-300 px-4 py-2">₱${item.subtotal.toFixed(2)}</td>
                    </tr>
                `;
            });

            document.getElementById('totalAmount').innerText = totalAmount.toFixed(2);
        }

        document.getElementById('payButton').addEventListener('click', () => {
            if (orderSummary.length === 0) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please add items to the order before proceeding to pay.',
                    icon: 'error'
                });
                return;
            }

            document.getElementById('paymentModal').style.display = 'flex';
            let modalContent = '';
            orderSummary.forEach(item => {
                modalContent += `
                    <div class="flex justify-between mb-2">
                        <span>${item.quantity} x ${item.name}</span>
                        <span>₱${item.subtotal.toFixed(2)}</span>
                    </div>
                `;
            });
            modalContent += `<hr><div class="flex justify-between font-semibold mt-2"><span>Total:</span><span>₱${totalAmount.toFixed(2)}</span></div>`;
            document.getElementById('modalContent').innerHTML = modalContent;
        });

        document.getElementById('confirmPayment').addEventListener('click', async () => {
            const paymentMethod = document.getElementById('paymentMethod').value;
            if (!paymentMethod) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select a payment method.',
                    icon: 'error'
                });
                return;
            }

            const orderDetails = { 
                orderSummary: orderSummary, 
                totalAmount: totalAmount, 
                paymentMethod: paymentMethod 
            };

            try {
                const response = await fetch('store_receipt.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(orderDetails),
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.href = "receipt.php"; // Redirect to receipt page
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: result.message || 'Could not process your payment, please try again.',
                        icon: 'error'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Network error, please try again.',
                    icon: 'error'
                });
            }
        });

        document.getElementById('logoutButton').addEventListener('click', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to logout!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php'; // Redirect to logout
                }
            });
        });
    </script>
</body>
</html>
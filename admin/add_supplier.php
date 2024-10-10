<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../index.php"); // Redirect to login if not logged in
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_name = $conn->real_escape_string($_POST['supplier_name']);
    $supplier_code = $conn->real_escape_string($_POST['supplier_code']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    // Insert category into database
    $query = "INSERT INTO supplier (supplier_name, supplier_code, phone) VALUES ('$supplier_name', '$supplier_code', '$phone')";
    
    if ($conn->query($query) === TRUE) {
        echo "<script>alert('New Supplier added successfully.');</script>";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }

    $conn->close();
    header("Location: add_supplier.php"); // Redirect back to category page after adding
    exit();
}

// Handle category deletion
if (isset($_GET['delete'])) {
    $supplier_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM supplier WHERE id = $supplier_id";
    if ($conn->query($delete_query) === TRUE) {
        echo "<script>alert('Category deleted successfully.');</script>";
    } else {
        echo "Error deleting supplier: " . $conn->error;
    }
    header("Location: add_supplier.php"); // Redirect back to the page
    exit();
}

// Fetch categories
$categories = $conn->query("SELECT * FROM supplier");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include 'includes/web_icon.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Add Supplier</title>
</head>
<body class="flex bg-gray-100">
     <!-- Sidebar -->
<div class="bg-gray-800 text-white h-screen p-5 w-64">
    <img src="img/logo.jpg" alt="Company Logo" class="w-16 h-16 rounded-full mx-auto mb-4"> <!-- Circular Logo -->
    <h2 class="text-center font-bold text-xl mb-5">R/J Kitchenette</h2>
    <ul>
    <li class="mb-3"><a href="dashboard.php" class="flex items-center hover:text-gray-300"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a></li>
            <li class="mb-3"><a href="add_products.php" class="flex items-center hover:text-gray-300"><i class="fas fa-box mr-2"></i> Products</a></li>
            <li class="mb-3"><a href="add_category.php" class="flex items-center hover:text-gray-300"><i class="fas fa-tags mr-2"></i> Category</a></li>
            <li class="mb-3"><a href="pos_sales.php" class="flex items-center hover:text-gray-300"><i class="fas fa-cash-register mr-2"></i> POS</a></li>
            <li class="mb-3"><a href="sales.php" class="flex items-center hover:text-gray-300"><i class="fas fa-peso-sign mr-2"></i> Sales Monitoring</a></li>
            <li class="mb-3"><a href="add_supplier.php" class="flex items-center hover:text-gray-300"><i class="fas fa-store mr-2"></i> Suppliers</a></li>
            <li class="mb-3"><a href="attendance.php" class="flex items-center hover:text-gray-300"><i class="fas fa-calendar-check mr-2"></i> Attendance</a></li>
            <li class="mb-3"><a href="leaves.php" class="flex items-center hover:text-gray-300"><i class="fas fa-file-alt mr-2"></i> Leaves</a></li>
            <li class="mb-3"><a href="logout.php" class="flex items-center hover:text-gray-300"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a></li>
    </ul>
</div>

     <!-- Main content -->
     <div class="flex-grow p-6 bg-gray-100">
        <nav class="flex items-center justify-between bg-white shadow-md p-4">
            <div>
                <h2 class="text-2xl font-bold">Welcome, <?php echo $_SESSION['full_name']; ?></h2>
            </div>
            
        </nav>

        <!-- Add Category Form -->
        <div class="bg-white p-8 rounded shadow-md mb-5">
            <h2 class="text-2xl font-bold mb-4 text-center">Add New Supplier</h2>
            <form id="SupplierForm" method="POST" action="add_supplier.php">
                <div class="mb-4">
                    <label for="supplier_name" class="block text-sm font-medium text-gray-700">Supplier Name</label>
                    <input type="text" name="supplier_name" id="supplier_name" required class="mt-1 p-2 border border-gray-300 rounded w-full" />
                </div>
                <div class="mb-4">
                    <label for="supplier_code" class="block text-sm font-medium text-gray-700">Supplier code</label>
                    <input type="text" name="supplier_code" id="supplier_code" required class="mt-1 p-2 border border-gray-300 rounded w-full" />
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Supplier Contact</label>
                    <input type="text" name="phone" id="phone" required class="mt-1 p-2 border border-gray-300 rounded w-full" />
                </div>
                <div class="flex justify-center">
                    <button type="button" id="addSupplierButton" class="bg-blue-500 text-white font-bold py-2 px-4 rounded">Add Supplier</button>
                </div>
            </form>
        </div>

        <!-- Categories List -->
        <div class="bg-white p-8 rounded shadow-md">
            <h2 class="text-2xl font-bold mb-4 text-center">Existing Suppliers</h2>
            <table class="min-w-full border-collapse">
                <thead>
                    <tr>
                        <th class="border p-2">Supplier Name</th>
                        <th class="border p-2">Supplier Code</th>
                        <th class="border p-2">Contact</th>
                        <th class="border p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $categories->fetch_assoc()) : ?>
                        <tr>
                            <td class="border p-2"><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['supplier_code']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td class="border p-2">
                                <button class="text-red-500 deleteButton" data-id="<?php echo $row['id']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Handle form submission with SweetAlert
        document.getElementById('addSupplierButton').addEventListener('click', function() {
            Swal.fire({
                title: 'Confirm Submission',
                text: 'Are you sure you want to add this Supplier?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, add it!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('SupplierForm').submit(); // Submit the form
                }
            });
        });

        // Handle delete button click with SweetAlert
        document.querySelectorAll('.deleteButton').forEach(button => {
            button.addEventListener('click', function() {
                const supplierId = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to delete the category
                        window.location.href = `add_supplier.php?delete=${supplierId}`;
                    }
                });
            });
        });

        // Add product SweetAlert
        document.getElementById('productsLink').addEventListener('click', function(e) {
            e.preventDefault(); // Prevent the default link behavior
            Swal.fire({
                title: 'Add Product',
                text: 'Are you sure you want to add a new Product?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, add it!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to products page or add product logic here
                    window.location.href = 'add_products.php'; // Adjust the path as needed
                }
            });
        });

        
        // Go to POS SweetAlert
        document.getElementById('posLink').addEventListener('click', function(e) {
            e.preventDefault(); // Prevent the default link behavior
            Swal.fire({
                title: 'POS',
                text: 'Go to POS?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, add it!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to products page or add product logic here
                    window.location.href = 'pos_sales.php'; // Adjust the path as needed
                }
            });
        });
             // Logout confirmation with SweetAlert
             document.getElementById('logoutButton').addEventListener('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'No, stay logged in'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'dashboard.php?logout=true'; // Redirect to logout
                }
            });
        });
    </script>
</body>
</html>

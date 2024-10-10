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

// Query to count total users, products, and categories
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalProducts = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];
$totalCategories = $conn->query("SELECT COUNT(*) AS total FROM categories")->fetch_assoc()['total'];
$totalSuppliers = $conn->query("SELECT COUNT(*) AS total FROM supplier")->fetch_assoc()['total'];

// Query to get total sales
$totalSales = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE DATE(date) = CURDATE()")->fetch_assoc()['total'] ?? 0; 
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
    <title>Dashboard</title>
    <style>
        /* Sidebar styling */
        .sidebar {
            height: 100vh; /* Full-height sidebar */
        }
        /* Circular logo */
        .logo {
            border-radius: 50%;
            width: 40px; /* Adjust size as needed */
            height: 40px; /* Adjust size as needed */
            object-fit: cover; /* Ensure the image covers the circle */
        }
    </style>
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

        <div class="mt-6">
            <h3 class="text-xl font-semibold">Dashboard Content</h3>
            
            <!-- Display totals -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-6"> <!-- Responsive Grid -->
                <div class="bg-white p-4 rounded shadow-md text-center flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold">Total Users</h4>
                        <p class="text-2xl"><?php echo $totalUsers; ?></p>
                    </div>
                    <div class="text-3xl text-blue-500">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="bg-white p-4 rounded shadow-md text-center flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold">Total Products</h4>
                        <p class="text-2xl"><?php echo $totalProducts; ?></p>
                    </div>
                    <div class="text-3xl text-green-500">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <div class="bg-white p-4 rounded shadow-md text-center flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold">Total Categories</h4>
                        <p class="text-2xl"><?php echo $totalCategories; ?></p>
                    </div>
                    <div class="text-3xl text-yellow-500">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
                <div class="bg-white p-4 rounded shadow-md text-center flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold">Total Sales</h4>
                        <p class="text-2xl"><?php echo number_format($totalSales, 2); ?></p> <!-- Format the sales amount -->
                    </div>
                    <div class="text-3xl text-red-500">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                </div>
                <div class="bg-white p-4 rounded shadow-md text-center flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold">Total Suppliers</h4>
                        <p class="text-2xl"><?php echo $totalSuppliers; ?></p>
                    </div>
                    <div class="text-3xl text-yellow-500">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
            </div>
            <!-- You can add charts, tables, or other components here -->
        </div>
    </div>

    <script>
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

        // Add product
        document.getElementById('productsLink')?.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent the default link behavior
            Swal.fire({
                title: 'Add Product',
                text: 'Are you sure you want to add a new product?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, add it!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'add_products.php'; // Adjust the path as needed
                }
            });
        });

        // Add category
        document.getElementById('categoryLink')?.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent the default link behavior
            Swal.fire({
                title: 'Add Category',
                text: 'Are you sure you want to add a new Category?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, add it!',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'add_category.php'; // Adjust the path as needed
                }
            });
        });
    </script>

</body>
</html>
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

// Handle delete product
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['delete_product'];

    // Perform delete operation
    $delete_query = "DELETE FROM products WHERE id = '$product_id'";
    if ($conn->query($delete_query) === TRUE) {
        echo "Product deleted successfully.";
    } else {
        echo "Error deleting product: " . $conn->error;
    }
}

// Handle form submission for editing product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_product'])) {
    $product_id = $_POST['edit_product'];
    $product_name = $conn->real_escape_string($_POST['edit_product_name']);
    $product_code = $conn->real_escape_string($_POST['edit_product_code']);
    $product_description = $conn->real_escape_string($_POST['edit_product_description']);
    $category = $conn->real_escape_string($_POST['edit_category']); // This will be the category ID
    $price = $_POST['edit_price'];
    $stock = $_POST['edit_stock'];

    // Update product in database
    $update_query = "UPDATE products SET product_name = '$product_name', product_code = '$product_code', 
                     product_description = '$product_description', category = '$category', price = '$price', 
                     stock = '$stock' WHERE id = '$product_id'";

    if ($conn->query($update_query) === TRUE) {
        echo "Product updated successfully.";
    } else {
        echo "Error updating product: " . $conn->error;
    }
}

// Handle form submission for adding product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $product_code = $conn->real_escape_string($_POST['product_code']);
    $product_description = $conn->real_escape_string($_POST['product_description']);
    $category = $conn->real_escape_string($_POST['category']); // This will be the category ID
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    // Handle file upload
    $photo = "";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "product_uploads/";
        $photo = basename($_FILES['photo']['name']);
        $target_file = $target_dir . $photo;

        // Check if file is an image
        $check = getimagesize($_FILES['photo']['tmp_name']);
        if ($check === false) {
            echo "File is not an image.";
            exit();
        }

        // Upload file
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            echo "Sorry, there was an error uploading your file.";
            exit();
        }
    }

    // Insert product into database
    $query = "INSERT INTO products (product_name, product_code, product_description, category, price, stock, photo, created_at) 
              VALUES ('$product_name', '$product_code', '$product_description', '$category', '$price', '$stock', '$photo', NOW())";

    if ($conn->query($query) === TRUE) {
        echo "New product added successfully.";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }

    $conn->close();
    header("Location: add_products.php"); // Redirect back to products page after adding
    exit();
}

// Fetch products from the database based on category filter
$filterCondition = '';
if (isset($_GET['categoryFilter']) && $_GET['categoryFilter'] !== '') {
    $categoryFilter = $conn->real_escape_string($_GET['categoryFilter']);
    $filterCondition = " WHERE products.category = '$categoryFilter'";
}

$query = "SELECT products.*, categories.category_name 
          FROM products 
          JOIN categories ON products.category = categories.id
          $filterCondition";
$result = $conn->query($query);

$products = []; // Initialize the products array

if ($result->num_rows > 0) {
    // Fetch all products
    while ($row = $result->fetch_assoc()) {
        $products[] = $row; // Add each product to the array
    }
} else {
    echo "No products found."; // Handle case with no products
}

// Fetch categories from the database
$categories = []; // Initialize the categories array
$result = $conn->query("SELECT * FROM categories"); // Adjust table name as needed

if ($result->num_rows > 0) {
    // Fetch all categories
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row; // Add each category to the array
    }
} else {
    echo "No categories found."; // Handle case with no categories
}

// Close the connection (optional, can be done at the end)
$conn->close();
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
    <title>Add Product</title>
</head>
<body class="flex">
  <!-- Sidebar -->
  <div class="bg-gray-800 text-white h-screen p-5 w-64">
    <img src="img/logo.jpg" alt="Company Logo" class="w-16 h-16 rounded-full mx-auto mb-4"> <!-- Circular Logo -->
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

    <!-- Products Table -->
    <div class="bg-white p-8 rounded shadow-md">
        <h2 class="text-2xl font-bold mb-4 text-center">Products List</h2>

        <!-- Add Product Button -->
        <div class="mt-4 text-center">
            <button id="addProductButton" class="bg-blue-500 text-white font-bold py-2 px-4 rounded">Add Product</button>
        </div>

        <!-- Category Filter Dropdown -->
        <div class="mb-4">
            <label for="categoryFilter" class="block<label for="categoryFilter" class="block text-sm font-medium text-gray-700 mt-4">Filter by Category:</label>
<select id="categoryFilter" name="categoryFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
    <option value="">All Categories</option>
    <?php foreach ($categories as $category) : ?>
        <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
    <?php endforeach; ?>
</select>

<!-- JavaScript to handle category filter change -->
<script>
    document.getElementById('categoryFilter').addEventListener('change', function() {
        const categoryId = this.value;
        window.location.href = `add_products.php?categoryFilter=${categoryId}`;
    });
</script>

<!-- Product List Table -->
<div class="mt-4">
    <table class="min-w-full bg-white">
        <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Name</th>
                <th class="py-3 px-6 text-left">Code</th>
                <th class="py-3 px-6 text-left">Description</th>
                <th class="py-3 px-6 text-center">Category</th>
                <th class="py-3 px-6 text-center">Price</th>
                <th class="py-3 px-6 text-center">Stock</th>
                <th class="py-3 px-6 text-center">Image</th>
                <th class="py-3 px-6 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product) : ?>
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo $product['product_name']; ?></td>
                    <td class="py-3 px-6 text-left"><?php echo $product['product_code']; ?></td>
                    <td class="py-3 px-6 text-left"><?php echo $product['product_description']; ?></td>
                    <td class="py-3 px-6 text-center"><?php echo $product['category_name']; ?></td>
                    <td class="py-3 px-6 text-center"><?php echo $product['price']; ?></td>
                    <td class="py-3 px-6 text-center"><?php echo $product['stock']; ?></td>
                    <td class="py-2">
                            <?php if (!empty($product['photo'])): ?>
                                <img src="product_uploads/<?php echo htmlspecialchars($product['photo']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="w-16 h-16 object-cover">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                    <td class="py-3 px-6 text-center">
                        <!-- Edit Product Form -->
                        <form action="edit_product.php" method="POST" class="inline">
                            <input type="hidden" name="edit_product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        </form>
                        <!-- Delete Product Form -->
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="inline">
                            <input type="hidden" name="delete_product" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="text-red-600 hover:text-red-900 ml-2">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
</div>

<script>
    // Show Add Product Modal
    document.getElementById('addProductButton').addEventListener('click', function () {
        document.getElementById('addProductModal').classList.remove('hidden');
    });

    // Hide Add Product Modal
    document.getElementById('closeModalButton').addEventListener('click', function () {
        document.getElementById('addProductModal').classList.add('hidden');
    });

    // Show Edit Product Modal
    const editButtons = document.querySelectorAll('.editProductButton');
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const productId = button.getAttribute('data-product-id');
            const product = <?php echo json_encode($products); ?>;
            const selectedProduct = product.find(prod => prod.id == productId);

            document.getElementById('edit_product_id').value = selectedProduct.id;
            document.getElementById('edit_product_name').value = selectedProduct.product_name;
            document.getElementById('edit_product_code').value = selectedProduct.product_code;
            document.getElementById('edit_product_description').value = selectedProduct.product_description;
            document.getElementById('edit_category').value = selectedProduct.category;
            document.getElementById('edit_price').value = selectedProduct.price;
            document.getElementById('edit_stock').value = selectedProduct.stock;

            document.getElementById('editProductModal').classList.remove('hidden');
        });
    });

    // Hide Edit Product Modal
    document.getElementById('closeEditModalButton').addEventListener('click', function () {
        document.getElementById('editProductModal').classList.add('hidden');
    });

    // Confirm Delete Product
    const deleteButtons = document.querySelectorAll('.deleteProductButton');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const productId = button.parentElement.querySelector('input[name="delete_product"]').value;

            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this product!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Perform AJAX request to delete product
                    const formData = new FormData();
                    formData.append('delete_product_id', productId);

                    fetch('delete_product.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Deleted!',
                                'Your product has been deleted.',
                                'success'
                            ).then(() => {
                                window.location.reload(); // Reload page after deletion
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete product.',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            'Failed to delete product.',
                            'error'
                        );
                    });
                }
            });
        });
    });
</script>

</body>
</html>
<?php
include 'config/db.php'; // Ensure this path is correct
session_start();

// Check if user is already logged in and redirect to dashboard
if (isset($_SESSION['employee_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

// This will be an AJAX response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $password = $_POST['password'];

    // Prepare and execute the SQL statement to fetch user
    $query = "SELECT * FROM users WHERE employee_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['full_name'] = $user['full_name']; // Save full name in session

            // Return a success response
            echo json_encode(['status' => 'success', 'message' => 'Login successful.']);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Employee ID not found.']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Login</title>
    <style>
        /* Circular logo */
        .logo {
            border-radius: 50%;
            width: 80px; /* Adjust size as needed */
            height: 80px; /* Adjust size as needed */
            object-fit: cover; /* Ensure the image covers the circle */
        }
    </style>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="bg-white p-8 rounded shadow-md w-96">
        <img src="img/logo.jpg" alt="Company Logo" class="logo mx-auto mb-4"> <!-- Circular Company Logo -->
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
        <form id="loginForm">
            <div class="mb-4">
                <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID</label>
                <input type="text" name="employee_id" id="employee_id" required class="mt-1 p-2 border border-gray-300 rounded w-full" />
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 p-2 border border-gray-300 rounded w-full" />
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white font-bold py-2 rounded">Login</button>
        </form>
    </div>

    <script>
        // Handle form submission with AJAX
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Get form data
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'admin/dashboard.php'; // Ensure this path is correct
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An unexpected error occurred.',
                    icon: 'error'
                });
            });
        });
    </script>
</body>
</html>

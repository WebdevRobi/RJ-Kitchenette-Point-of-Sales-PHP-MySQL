<?php
include 'config/db.php'; // Ensure this path is correct
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name']; // Get full name
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Generate employee_id (format: RJ + 3 random digits)
    $employee_id = 'RJ' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

    // Handle photo upload
    $target_dir = "uploads/"; // Directory where photos will be uploaded
    $target_file = $target_dir . basename($_FILES["photo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if the uploaded file is an image
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check === false) {
        echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'File is not an image.',
                    icon: 'error'
                });
              </script>";
        $uploadOk = 0;
    }

    // Check file size (optional: limit to 2MB)
    if ($_FILES["photo"]["size"] > 2000000) {
        echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Sorry, your file is too large.',
                    icon: 'error'
                });
              </script>";
        $uploadOk = 0;
    }

    // Allow only certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.',
                    icon: 'error'
                });
              </script>";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk === 1) {
        // Try to upload the file
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            // Prepare and execute the SQL statement to insert user
            $query = "INSERT INTO users (employee_id, full_name, username, password, photo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssss", $employee_id, $full_name, $username, $password, $target_file);

            if ($stmt->execute()) {
                echo "<script>
                        Swal.fire({
                            title: 'Success!',
                            text: 'Registration successful. You can now log in.',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = 'login.php'; // Redirect to login page
                        });
                      </script>";
            } else {
                echo "<script>
                        Swal.fire({
                            title: 'Error!',
                            text: 'Registration failed: " . $stmt->error . "',
                            icon: 'error'
                        });
                      </script>";
            }
        } else {
            echo "<script>
                    Swal.fire({
                        title: 'Error!',
                        text: 'Sorry, there was an error uploading your file.',
                        icon: 'error'
                    });
                  </script>";
        }
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
    <title>Register</title>
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
        <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>
        <form method="POST" enctype="multipart/form-data"> <!-- Add enctype for file upload -->
            <div class="mb-4">
                <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="full_name" id="full_name" required class="mt-1 p-2 border border-gray-300 rounded w-full" />
            </div>
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" id="username" required class="mt-1 p-2 border border-gray-300 rounded w-full" />
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 p-2 border border-gray-300 rounded w-full" />
            </div>
            <div class="mb-4">
                <label for="photo" class="block text-sm font-medium text-gray-700">Profile Photo</label>
                <input type="file" name="photo" id="photo" accept="image/*" required class="mt-1 p-2 border border-gray-300 rounded w-full" />
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white font-bold py-2 rounded">Register</button>
        </form>
    </div>
</body>
</html>

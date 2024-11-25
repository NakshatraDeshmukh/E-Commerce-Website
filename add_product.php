<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'login_register');  // Make sure this matches your database credentials
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['product_image'])) {
    // Get form data
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_FILES['product_image']['name'];
    $target_dir = "images/";  // Change this to your directory if needed
    $target_file = $target_dir . basename($product_image);

    // Check if the file is uploaded successfully
    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        // Insert product into the database
        $stmt = $conn->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $product_name, $product_price, $product_image);

        // Execute query
        if ($stmt->execute()) {
            echo "Product added successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Error uploading image.";
    }
}

// Close the connection
$conn->close();

// Redirect to dashboard after the operation
header("Location: admin_dashboard.php");
exit;
?>
<form action="add_product.php" method="POST" enctype="multipart/form-data">
    <label for="product_name">Product Name:</label>
    <input type="text" name="product_name" id="product_name" required>

    <label for="product_price">Product Price:</label>
    <input type="number" step="0.01" name="product_price" id="product_price" required>

    <label for="product_image">Product Image:</label>
    <input type="file" name="product_image" id="product_image" required>

    <button type="submit">Add Product</button>
</form>

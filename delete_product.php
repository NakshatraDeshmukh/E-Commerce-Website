<?php
session_start();

// Include the database connection
require_once 'dbconnection.php';

// Check if product_id is set
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Get the image filename from the database
    $sql = "SELECT image FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // If the product exists, proceed with deletion
    if ($product) {
        // Delete the image from the 'uploads' folder
        $image_path = "uploads/" . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);  // Delete the image file
        }

        // Proceed with deleting the product from the database
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Product and image deleted successfully!";
        } else {
            $_SESSION['message'] = "Error deleting product: " . $stmt->error;
        }

        // Close the prepared statement and database connection
        $stmt->close();
    } else {
        $_SESSION['message'] = "Product not found.";
    }

    $conn->close();
    
    // Redirect back to admin dashboard
    header("Location: admin_dashboard.php");
    exit();
} else {
    // If product_id is not set, show an error message
    $_SESSION['message'] = "Product ID not found!";
    header("Location: admin_dashboard.php");
    exit();
}
?>

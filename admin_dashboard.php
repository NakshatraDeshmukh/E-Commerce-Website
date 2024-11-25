<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'login_register');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products for display
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Check for form submission for adding a product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['product_image'])) {
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $productImage = $_FILES['product_image'];

    // Handle image upload
    $imageName = time() . '_' . basename($productImage['name']);
    $targetDir = 'uploads/';
    $targetFile = $targetDir . $imageName;

    if (move_uploaded_file($productImage['tmp_name'], $targetFile)) {
        // Insert new product into the database
        $stmt = $conn->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $productName, $productPrice, $imageName);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Error uploading image.";
    }
}

// Check for form submission for deleting a product
if (isset($_POST['delete_product'])) {
    $productId = $_POST['product_id'];

    // Fetch the product details first to get the image name
    $sql = "SELECT image FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    // If product exists, delete from database and remove image file
    if ($product) {
        // Delete the image file from the uploads folder
        $imagePath = 'uploads/' . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath); // Delete the image
        }

        // Now delete the product from the database
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();

        // Confirm the deletion
        echo "Product and associated image deleted successfully.";
    } else {
        echo "Product not found.";
    }

    $stmt->close();
}

// Fetch products for display again
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
   /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body styling */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f7f8fa;
    color: #333;
    transition: all 0.3s ease;
}

/* Topbar styling */
.topbar {
    position: fixed;
    top: 0;
    width: 100%;
    height: 70px;
    background-color: #343a40;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.topbar h1 {
    font-size: 24px;
    margin-left: 10px;
}

.toggle-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    width: 50px;
    height: 40px;
}

.bar {
    display: block;
    width: 100%;
    height: 4px;
    background-color: white;
    border-radius: 2px;
    transition: 0.3s;
}

/* Sidebar styling */
.sidebar {
    width: 220px;
    height: 100vh;
    background-color: #2196f3;
    padding-top: 80px;
    position: fixed;
    left: -220px;
    transition: left 0.3s ease;
    box-shadow: 2px 0px 8px rgba(0, 0, 0, 0.2);
}

.sidebar.open {
    left: 0;
}

.sidebar a {
    display: block;
    color: white;
    padding: 20px;
    text-decoration: none;
    font-size: 18px;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.sidebar a:hover {
    background-color: #0069d9;
}

/* Sidebar active state */
.sidebar a.active {
    background-color: #0d47a1;
}

.sidebar a:hover {
    background-color: #004a91;
}

/* Main content styling */
.main {
    margin-left: 0;
    padding: 80px 30px 30px 30px;
    width: 100%;
    transition: margin-left 0.3s ease;
}

/* Product Grid */
.product-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
    padding: 20px 0;
}

.product-item {
    background-color: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-align: center;
    width: 250px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.product-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 15px;
}

.product-item h6 {
    font-size: 18px;
    font-weight: 500;
    margin-bottom: 10px;
}

.product-item p {
    color: #555;
    margin-bottom: 15px;
    font-size: 16px;
}

.product-item button {
    padding: 10px 20px;
    background-color: #e91e63;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.product-item button:hover {
    background-color: #d81b60;
}

form label {
    margin-top: 10px;
    font-weight: 600;
    font-size: 16px;
}

form input,
form button {
    padding: 12px 15px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
}

/* Form button */
form button {
    background-color: #4CAF50;
    color: white;
    cursor: pointer;
}

form button:hover {
    background-color: #45a049;
}

/* Sidebar Toggle */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main {
        margin-left: 0;
    }

    .product-grid {
        flex-direction: column;
        align-items: center;
    }
}

</style>
</head>
<body>

    <!-- Topbar -->
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <h1>Admin Dashboard</h1>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="#">Dashboard</a>
        <a href="#">Manage Products</a>
        <a href="#">Orders</a>
        <a href="#">Settings</a>
        <a href="index.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <h2>Add New Product</h2>
        <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
            <label for="product_name">Product Name:</label>
            <input type="text" name="product_name" id="product_name" required>

            <label for="product_price">Product Price:</label>
            <input type="number" step="0.01" name="product_price" id="product_price" required>

            <label for="product_image">Product Image:</label>
            <input type="file" name="product_image" id="product_image" required>

            <button type="submit">Add Product</button>
        </form>

        <h2>Product List</h2>
        <div class="product-grid">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="product-item">
                    <img src="uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                    <h6><?php echo $row['name']; ?></h6>
                    <p>$<?php echo $row['price']; ?></p>
                    <form action="admin_dashboard.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_product">Delete</button>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- JavaScript to Toggle Sidebar -->
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("open");

            // Adjust the main content margin when sidebar is toggled
            var main = document.querySelector(".main");
            if (sidebar.classList.contains("open")) {
                main.style.marginLeft = "200px"; // Move content to the right when sidebar is open
            } else {
                main.style.marginLeft = "0"; // Move content back to the left when sidebar is closed
            }
        }
    </script>

</body>
</html>

<?php $conn->close(); ?> 
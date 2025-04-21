<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item = mysqli_real_escape_string($conn, $_POST['item']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);

    // Insert product details
    $sql = "INSERT INTO products (username, item, price, address, details) VALUES ('$username', '$item', '$price', '$address', '$details')";
    if ($conn->query($sql) === TRUE) {
        $productId = $conn->insert_id; // Get inserted product ID

        // Handle multiple file uploads
        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                $fileName = basename($_FILES['photos']['name'][$key]);
                $targetDir = "uploads/";
                $targetFile = $targetDir . uniqid() . "_" . $fileName; 

                if (move_uploaded_file($tmp_name, $targetFile)) {
                    // Save file path to database
                    $conn->query("INSERT INTO product_images (product_id, image_path) VALUES ('$productId', '$targetFile')");
                }
            }
        }

        echo "<script>alert('Product added successfully!'); window.location.href='home.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function addPhotoInput() {
            let container = document.getElementById("photo-container");
            let newInput = document.createElement("input");
            newInput.type = "file";
            newInput.name = "photos[]"; 
            newInput.accept = "image/*";
            container.appendChild(newInput);
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Add Product</h2>
    <form action="addproduct.php" method="POST" enctype="multipart/form-data">
        <label for="item">Item Name:</label>
        <input type="text" id="item" name="item" required>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" required>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required>

        <label for="details">More Details:</label>
        <textarea id="details" name="details" rows="4" required></textarea>

        <label>Upload Photos:</label>
        <div id="photo-container">
            <input type="file" name="photos[]" accept="image/*" required>
        </div>
        <button type="button" onclick="addPhotoInput()">+ Add More Photos</button>

        <button type="submit">Post Product</button>
    </form>
</div>

</body>
</html>

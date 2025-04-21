<?php
session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Check if the product_id is set in the URL
if (!isset($_GET['product_id'])) {
    header("Location: upload_history.php");
    exit();
}

$product_id = intval($_GET['product_id']);

// Fetch product details (Photo, Item, Price, Address, Details, Uploaded On)
$product_sql = "SELECT item, price, address, details, photo, created_at FROM products WHERE id = ?";
$stmt = $conn->prepare($product_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();
$stmt->close();

// Fetch comments for the product
$comments_sql = "SELECT u.username, c.comment, c.created_at 
                 FROM comments c 
                 JOIN users u ON c.user_id = u.id 
                 WHERE c.product_id = ? 
                 ORDER BY c.created_at DESC";
$stmt = $conn->prepare($comments_sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$comments_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments for <?php echo htmlspecialchars($product['item']); ?></title>
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Comments for <?php echo htmlspecialchars($product['item']); ?></h1>
        <a href="upload_history.php" class="btn">Back</a>

        <!-- Product Details Section -->
        <div class="product-details">
            <img src="<?php echo htmlspecialchars($product['photo']); ?>" width="80" height="80" alt="Product Image">
            <p><strong>Item:</strong> <?php echo htmlspecialchars($product['item']); ?></p>
            <p><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($product['address']); ?></p>
            <p><strong>Details:</strong> <?php echo htmlspecialchars($product['details']); ?></p>
            <p><strong>Uploaded On:</strong> <?php echo htmlspecialchars($product['created_at']); ?></p>
        </div>

        <!-- Comments Section for this Product -->
        <div class="comments-section">
            <h2>Comments</h2>
            <?php
            if ($comments_result->num_rows > 0) {
                while ($row = $comments_result->fetch_assoc()) {
                    echo "<div class='comment-box'>";
                    echo "<p><strong>" . htmlspecialchars($row['username']) . "</strong> says:</p>";
                    echo "<p>" . htmlspecialchars($row['comment']) . "</p>";
                    echo "<p><em>Posted at: " . $row['created_at'] . "</em></p>";
                    echo "</div><hr>";
                }
            } else {
                echo "<p>No comments found for this product.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>

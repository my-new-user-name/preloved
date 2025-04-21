<?php
session_start();
include('db.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get total users
$userQuery = "SELECT COUNT(*) AS total_users FROM users";
$userResult = $conn->query($userQuery);
$totalUsers = $userResult->fetch_assoc()['total_users'];

// Get total products
$productQuery = "SELECT COUNT(*) AS total_products FROM products";
$productResult = $conn->query($productQuery);
$totalProducts = $productResult->fetch_assoc()['total_products'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_products.php">Manage Products</a></li>
                <li><a href="view_comments_likes.php">View comment and likes</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        

        <!-- Main Content -->
        <div class="admin-content">
            <h1>Welcome, Admin</h1>
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <p><?php echo $totalUsers; ?></p>
                </div>
                <div class="card">
                    <h3>Total Products</h3>
                    <p><?php echo $totalProducts; ?></p>
                </div>
            </div>
        </div>
    </div>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
}

.admin-container {
    display: flex;
    min-height: 100vh;
    background-color: #f4f4f4;
}

.dashboard-cards {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: 20px;
}

.card {
    background-color:rgb(229, 240, 253);
    color: black;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    width: 400px;
}

.card h3 {
    margin-bottom: 10px;
}

.card p {
    font-size: 24px;
    font-weight: bold;
}


.sidebar {
    width: 250px;
    background-color: #343a40;
    color: white;
    padding: 20px;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 20px;
}

.sidebar ul {
    list-style-type: none;
    padding: 0;
}

.sidebar ul li {
    margin: 10px 0;
}

.sidebar ul li a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 10px;
    border-radius: 5px;
    transition: background 0.3s;
}

.sidebar ul li a:hover {
    background-color: #007bff;
}

.admin-content {
    flex-grow: 1;
    background-color: #fff;
    padding: 20px;
    margin-left: 20px;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 10px;
    text-align: center;
}

th {
    background-color: #007bff;
    color: white;
}

td img {
    border-radius: 5px;
    object-fit: cover;
}

.admin-content a {
    text-decoration: none;
    font-weight: bold;
    padding: 5px 10px;
    border-radius: 5px;
    transition: background 0.3s;
}

.admin-content a:hover {
    color: #c82333;
}


</style>

</body>
</html>

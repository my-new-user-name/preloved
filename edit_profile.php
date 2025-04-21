<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, email, birthday, phone, address, province, city, barangay, profile_picture FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $birthday = $_POST['birthday'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];

    // Handle profile picture upload
    if (!empty($_FILES["profile_picture"]["name"])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image type
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed_types)) {
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
            $profile_picture = $target_file;

            // Update profile picture in DB
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE username = ?");
            $stmt->bind_param("ss", $profile_picture, $username);
            $stmt->execute();
        }
    }

    // Update user details
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, birthday = ?, phone = ?, address = ?, province = ?, city = ?, barangay = ? WHERE username = ?");
    $stmt->bind_param("ssssssssss", $first_name, $last_name, $email, $birthday, $phone, $address, $province, $city, $barangay, $username);

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        // Refresh data
        $stmt = $conn->prepare("SELECT first_name, last_name, email, birthday, phone, address, province, city, barangay, profile_picture FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error = "Profile update failed!";
    }
}
// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $message = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
    } else {
        // Update new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $hashed_password, $username);

        if ($update_stmt->execute()) {
            $message = "Password updated successfully!";
        } else {
            $message = "Error updating password.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="style_main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>

<div class="header">
    <h2 class="back-to-profile"><a href="userprofile.php"><i class='bx bx-arrow-back'></i></a></h2>
</div>


<div class="container">
    <h1>Edit Profile</h1>
    <?php if (isset($success)): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data">
        <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'default.png'); ?>" width="100" height="100" alt="Profile Picture">

        <label for="profile_picture">Change Profile Picture:</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">

        <input type="text" id="username" name="username" placeholder="Username:" required>

        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" placeholder="First Name:" required>

        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" placeholder="Last Name:" required>

        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email:" required>


        <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>" placeholder="Birthday:" required>

        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="Phone Number:" required>

        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" placeholder="Address:" required>

        <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($user['province']); ?>" placeholder="Province:" required>

        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" placeholder="City:" required>

        <input type="text" id="barangay" name="barangay" value="<?php echo htmlspecialchars($user['barangay']); ?>" placeholder="Barangay:" required>

        <button type="submit">Update Profile</button>
        <button type="button" onclick="openChangePasswordModal()">Change Password</button>
    </form>
</div>

<style>
.container {
    background: #ecdbec;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 100%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    margin-top: 60px;
}

h1 {
    text-align: center;
    color: #333;
}

form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3px;
}

img {
    display: block;
    margin: 0 auto 20px auto; /* centers horizontally and adds space below */
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solidrgb(245, 247, 245);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-left: 5px;
}

label {
    font-weight: bold;
    color: #000;
    grid-column: span 2
}

input[type="text"],
input[type="email"],
input[type="date"],
input[type="tel"] {
    width: 90%;
    padding: 10px;
    border: 1px solid #000;
    border-radius: 5px;
    grid-column: span 1;
    background-color:rgb(201, 177, 180);
}

input[type="file"] {
    width: 95%;
    padding: 10px;
    border: 1px solid #000;
    border-radius: 5px;
    padding: 5px;
    grid-column: span 2;
    background-color:rgb(201, 177, 180);
}

input[type="file"] {
    padding: 10px;
}

button {
    background-color:rgb(204, 158, 165);
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    width: 48%;
}

button:hover {
    background-color:rgb(235, 168, 178);
}

.success {
    color: green;
    text-align: center;
}

.error {
    color: red;
    text-align: center;
}

</style>


    <!-- Change Password Form -->
<div id="changePasswordModal" class="modal" >
    
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
        <div class="form-container"> <!-- Added border box -->
            <span class="close-button" onclick="closeChangePasswordModal()">&times;</span>
            <h2>Change Password</h2>
            <form method="POST">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required>

                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>

                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit">Update Password</button>
            </form>
        </div>
</div>

<style>
/* Modal Background */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Modal Content */
.modal-content {
    background: #ffccd5;
    padding: 20px;
    border-radius: 10px;
    width: 1000px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    position: relative;
}

.form-container {
    border: 2px solid #ccc; /* Gray border */
    padding: 15px;
    border-radius: 8px;
    max-width: 500px;
    background-color: #ecdbec;
    box-sizing: border-box;
    position: relative;
    margin-left: 500px;
    margin-top: 200px;
}


/* Close Button */
.close-button {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 30px;
    font-weight: bold;
    cursor: pointer;
    color: black;
    visibility: visible;
}

/* Form Elements */
.modal form {
    display: flex;
    flex-direction: column;
}

h2 {
    text-align: center;
}

label {
   
    margin-top: 10px;
}

input[type="password"] {
    background-color: rgb(204, 158, 165);
}

button {
    margin-top: 15px;
    padding: 10px;
    background-color:rgb(204, 158, 165);
    color: black;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}

button:hover {
    background-color: rgb(218, 144, 155);
}
</style>

<script>
// Function to open the Change Password modal
function openChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'block';
}

// Function to close the Change Password modal
function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'none';
}

// Optional: Close when clicking outside modal content
window.addEventListener('click', function(event) {
    const changeModal = document.getElementById('changePasswordModal');
    if (event.target === changeModal) {
        changeModal.style.display = 'none';
    }
});
</script>

</body>
</html>

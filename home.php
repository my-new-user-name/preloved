<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$selectedUser = '';

if (isset($_GET['user'])) {
    $selectedUser = mysqli_real_escape_string($conn, $_GET['user']);
    $showChatBox = true;
} else {
    $showChatBox = false;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-time Chat</title>
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Happy Thrift</h1>
       
        <div class="header-right">
        <a href="userprofile.php" class="profile"><i class='bx bx-user-circle'></i></a>
        <a href="message.php" class="see-all-messages-btn"><i class='bx bxl-messenger'></i></a>
        <!-- Messages dropdown -->
        <div class="messages-dropdown">
            <button class="messages-btn"><i class='bx bx-envelope'></i></button>
            <ul class="messages-list">
                <?php 
                $sql = "SELECT username FROM users WHERE username != ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $user = ucfirst($row['username']);
                    echo "<li><a href='#' class='select-user' data-user='" . htmlspecialchars($row['username']) . "'>$user</a></li>";
                }
                ?>
            </ul>
        </div>

        

        <a href="edit_profile.php" class="settings"><i class='bx bx-cog'></i></a>
        <a href="logout.php" class="logout"><i class='bx bx-log-out'></i></a>
        
        </div>
    </div>

    <div class="account-info">
      <div class="welcome">
        <h2><i class='bx bx-user-circle'></i></h2>
      </div>

    <!-- Add Product Button -->
      <div class="add-product">
        <button class="btn" onclick="openModal()">Post Item Here</button>
      </div>
</div>



 <!-- Add Product Modal -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeModal()">&times;</span>
        <h2>Create a Post</h2>

        <form action="addproduct.php" method="POST" enctype="multipart/form-data">

        <div class="form-group">
             
            <input type="text" id="item" name="item" placeholder="Item" required>
            <input type="number" id="price" name="price" placeholder="Price" required>
            <input type="text" id="address" name="address" placeholder="Address" required>
            <textarea id="details" name="details" rows="3" placeholder="Details" required></textarea>
        </div>

        <label>Upload Photos:</label>
        <div id="photo-container">
            <button type="button" class="file-button" onclick="document.getElementById('file-upload').click();">
            <i class='bx bx-image'></i>
            </button>
            <input type="file" id="file-upload" class="file-input" name="photos[]" accept="image/*">
            <button type="button" class="file-button" onclick="addPhotoInput()"><i class='bx bx-plus'></i></button>  
        </div>

        <button type="submit" class="post-button">Post</button>
        </form>
    </div>
</div>


<script>
    function openModal() {
        document.getElementById("addProductModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("addProductModal").style.display = "none";
    }

    window.onclick = function(event) {
        var modal = document.getElementById("addProductModal");
        if (event.target == modal) {
            closeModal();
        }
    };
</script>


    <!-- Chat box (hidden by default) -->
    <div class="chat-box" id="chat-box" style="display: none;">
        <div class="chat-box-header">
            <h2 id="chat-user-name"></h2>
            <?php if (!empty($row['email'])): ?>
  
<?php else: ?>
    <a href="view_profile.php?user=<?php echo urlencode($receiver['email']); ?>">View Profile</a>
<?php endif; ?>

            <button class="close-btn" onclick="closeChat()">✖</button>
            <li>
</li>



        </div>
        <div class="chat-box-body" id="chat-box-body">
            <!-- Chat messages will be loaded here -->
        </div>
        <form class="chat-form" id="chat-form">
            <input type="hidden" id="sender" value="<?php echo $username; ?>">
            <input type="hidden" id="receiver">
            <input type="text" id="message" placeholder="Type your message..." required>
            <button type="submit">Send</button>
        </form>

        <div id="typing-indicator" style="color: gray; margin-top: 5px;"></div>
        

    </div>

    

    <div class="product-list">
    <h2>Preloved Items</h2>
    <table border="1">
        
        <div class="product-grid">
        <?php
        include('db.php');

        $sql = "SELECT p.id, p.item, p.price, p.address, p.details, p.photo, p.likes, p.reports, u.username 
                FROM products p 
                JOIN users u ON p.username = u.username
                ORDER BY p.created_at DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $productId = $row['id'];
                $postedBy = htmlspecialchars($row['username']);
        ?>
        <div class="product-item">
          <div class="product-header">
            <div class="product-posted-by"><i class='bx bx-user-circle'></i> Posted by: <?php echo $postedBy; ?></div>
            <a href="#" class="report-product" data-id="<?php echo $productId; ?>" data-user="<?php echo $postedBy; ?>"><i class='bx bx-dots-horizontal-rounded'></i></a>
             </div>
            <div class="product-info">
            <div class="product-title">Item: <?php echo htmlspecialchars($row['item']); ?></div>
                <div class="product-price">₱<?php echo number_format($row['price'], 2); ?></div>
                <div class="product-address">Address: <?php echo htmlspecialchars($row['address']); ?></div>
                <img src="<?php echo htmlspecialchars($row['photo']); ?>" >
                <div class="product-details">Details: <?php echo htmlspecialchars($row['details']); ?></div>
                
                
                <div class="product-actions">
                    <button class="like-btn" data-id="<?php echo $productId; ?>"><i class='bx bx-heart' style='color:black' ></i><span class="like-count"><?php echo $row['likes']; ?></span></button>
                    <a href="#" class="select-user" data-user="<?php echo $postedBy; ?>"><i class='bx bx-message-rounded'></i></a>
                    <button class="comment-btn" data-id="<?php echo $productId; ?>"><i class='bx bx-comment' style='color:#000' ></i></button>
                    <button class="view-comments-btn" data-id="<?php echo $productId; ?>"><i class='bx bx-show'></i></button>
                </div>
            </div>
        </div>
        <?php
            }
        } else {
            echo "<p>No products added yet.</p>";
        }
        ?>
    </div>

<?php
include('db.php');

if (isset($_GET['product_id'])) {
    $productId = intval($_GET['product_id']);
    
    $sql = "SELECT c.comment, c.created_at, u.username 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.product_id = ? 
            ORDER BY c.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='comment'>";
            echo "<strong>" . htmlspecialchars($row['username']) . ":</strong> ";
            echo "<span>" . htmlspecialchars($row['comment']) . "</span>";
            echo "<br><small>" . $row['created_at'] . "</small>";
            echo "</div>";
        }
    } else {
        echo "<p>No comments yet.</p>";
    }
    
    $stmt->close();
} else {
    echo "<p></p>";
}
?>



<script>
    document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".report-product").forEach(button => {
        button.addEventListener("click", function() {
            const productId = this.getAttribute("data-id");
            const username = this.getAttribute("data-user");

            if (confirm("Are you sure you want to report this product?")) {
                fetch("report_product.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `product_id=${productId}&username=${username}`
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                });
            }
        });
    });
});

</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $(".view-comments-btn").click(function () {
        let productId = $(this).data("id");
        let commentSection = $("#comment-section-" + productId);
        let commentList = $("#comment-list-" + productId);

        // Toggle visibility of the comment section
        commentSection.toggle();

        if (commentSection.is(":visible")) {
            console.log("Fetching comments for product ID:", productId); // Debugging
            $.ajax({
                url: "fetch_comment.php",
                type: "POST",
                data: { product_id: productId },
                success: function (response) {
                    console.log("Response from server:", response); // Debugging
                    commentList.html(response); // Display comments
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching comments:", status, error);
                }
            });
        }
    });
});


$(".submit-comment").click(function () {
    let productId = $(this).data("id");
    let commentInput = $("#comment-input-" + productId);
    let commentText = commentInput.val().trim();

    if (commentText === "") {
        alert("Please enter a comment.");
        return;
    }

    // Show loading indicator
    let submitButton = $(this);
    submitButton.prop("disabled", true).text("Posting...");

    $.ajax({
        url: "post_comment.php",
        type: "POST",
        data: { product_id: productId, comment: commentText },
        success: function (response) {
            if (response.status === "success") {
                // Append the new comment to the list
                $("#comment-list-" + productId).append(response.html);
                commentInput.val(""); // Clear input field
            } else {
                alert("Failed to post comment: " + response.message);
            }
        },
        error: function (xhr, status, error) {
            alert("An error occurred while posting the comment. Please try again.");
        },
        complete: function () {
            // Restore button state
            submitButton.prop("disabled", false).text("Post");
        }
    });
});
</script>

        </table>
    </div>
</div>






<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
$(document).ready(function() {
    $(".comment-btn").click(function() {
        var productId = $(this).data("id");
        var comment = prompt("Enter your comment:");

        if (comment) {
            $.ajax({
                url: "add_comment.php",
                type: "POST",
                data: { product_id: productId, comment: comment },
                success: function(response) {
                    console.log("Server Response:", response); // Debugging
                    if (response.trim() === "success") {
                        alert("Comment added successfully!");
                        location.reload(); // Reload page to show the new comment
                    } else {
                        alert("Error: " + response);
                    }
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error:", error);
                }
            });
        }
    });
});
</script>
</body>
</html>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    function fetchNotifications() {
        $.ajax({
            url: "fetch_notifications.php",
            type: "GET",
            success: function(response) {
                console.log("Server Response:", response); // Debugging

                let notifications = JSON.parse(response);
                let notifCount = notifications.length;
                $("#notif-count").text(notifCount > 0 ? notifCount : "");

                let html = "";
                notifications.forEach(function(notif) {
                    html += `<li>${notif.message} <small>${notif.created_at}</small></li>`;
                });

                $(".notif-list").html(html);
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error:", error);
            }
        });
    }

    // Fetch notifications every 5 seconds
    setInterval(fetchNotifications, 5000);
    fetchNotifications();

    // Show dropdown when clicked
    $(".notif-btn").click(function() {
        $(".notif-list").toggle();
    });
});



</script>


<script>
$(document).ready(function() {
    let productId = 1; // Change this dynamically if needed

    // In your existing code, replace the fetchComments function with this:
function fetchComments() {
    $.ajax({
        url: "fetch_comment.php",
        type: "GET",
        data: { product_id: productId },
        success: function(response) {
            try {
                // Parse the response into a JSON object
                let result = JSON.parse(response);

                // Check if the server returned a success status
                if (result.status === "success") {
                    let comments = result.data; // Access the 'data' array
                    let html = '';

                    // Iterate over comments
                    comments.forEach(commentData => {
                        html += `<div class='comment'>
                            <strong>${commentData.username}</strong>: ${commentData.comment} 
                            <small>${commentData.created_at}</small>
                            <button onclick="replyComment(${commentData.id})">Reply</button>
                            <div id="replies-${commentData.id}" class="replies">`;

                        // Add replies if they exist
                        if (commentData.replies && commentData.replies.length > 0) {
                            commentData.replies.forEach(reply => {
                                html += `<div class='reply'>
                                    <strong>${reply.username}</strong>: ${reply.comment} 
                                    <small>${reply.created_at}</small>
                                </div>`;
                            });
                        }

                        html += `</div>
                            <textarea id="reply-input-${commentData.id}" style="display:none;"></textarea>
                            <button id="reply-btn-${commentData.id}" onclick="addComment(${commentData.id})" style="display:none;">Post Reply</button>
                        </div>`;
                    });

                    // Update the comments container
                    $("#comments-container").html(html);
                } else {
                    console.error("Server error:", result.message);
                }
            } catch (e) {
                console.error("Error parsing response:", e);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
        }
    });
}

    fetchComments();
});




</script>

<script>
    function addPhotoInput() {
        var photoContainer = document.getElementById("photo-container");
        var newInput = document.createElement("input");
        newInput.type = "file";
        newInput.name = "photos[]"; // Keeps the input as an array
        newInput.accept = "image/*";
        newInput.required = false; // Only the first input should be required
        photoContainer.appendChild(newInput);
    }
</script>


<script>
    $(document).ready(function() {
        // Toggle messages dropdown
        $(".messages-btn").click(function() {
            $(".messages-list").toggle();
        });

        // Open chat box when user is selected
        $(".select-user").click(function() {
            var selectedUser = $(this).data("user");
            $("#chat-user-name").text(selectedUser);
            $("#receiver").val(selectedUser);
            $("#chat-box").show();
            fetchMessages();
        });

        // Like button functionality
        $(".like-btn").click(function() {
            var button = $(this);
            var productId = button.data("id");

            $.ajax({
                url: "likeproduct.php",
                type: "POST",
                data: { product_id: productId },
                success: function(response) {
                    if (response !== "error") {
                        button.find(".like-count").text(response);
                    } else {
                        alert("Error liking the product.");
                    }
                }
            });
        });

        // Fetch messages
        function fetchMessages() {
            var sender = $("#sender").val();
            var receiver = $("#receiver").val();
            
            $.ajax({
                url: "fetch_messages.php",
                type: "POST",
                data: {sender: sender, receiver: receiver},
                success: function(data) {
                    $("#chat-box-body").html(data);
                    scrollChatToBottom();
                }
            });
        }

        // Scroll chat to bottom
        function scrollChatToBottom() {
            var chatBox = $("#chat-box-body");
            chatBox.scrollTop(chatBox.prop("scrollHeight"));
        }

        // Submit chat form
        $("#chat-form").submit(function(e) {
            e.preventDefault();
            var sender = $("#sender").val();
            var receiver = $("#receiver").val();
            var message = $("#message").val();

            $.ajax({
                url: "submit_message.php",
                type: "POST",
                data: {sender: sender, receiver: receiver, message: message},
                success: function() {
                    $("#message").val('');
                    fetchMessages();
                }
            });
        });

        // Close chat box
        window.closeChat = function() {
            $("#chat-box").hide();
        };
    });
</script>

<script>
    $(document).ready(function() {
    $(".comment-btn").click(function() {
        var productId = $(this).data("id");
        var comment = prompt("Enter your comment:");

        if (comment) {
            $.ajax({
                url: "add_comment.php",
                type: "POST",
                data: { product_id: productId, comment: comment },
                success: function(response) {
                    if (response === "success") {
                        alert("Comment added successfully!");
                    } else {
                        alert("Error adding comment.");
                    }
                }
            });
        }
    });
});

</script>

<style>
.messages-dropdown {
    display: inline-block;
    position: relative;
}

.messages-btn {
    background: none;
    border: none;
    font-weight: bold;
    cursor: pointer;
    color: green;
}

.messages-list {
    display: none;
    position: absolute;
    background: white;
    list-style: none;
    padding: 0;
    margin: 0;
    border: 1px solid gray;
    width: 150px;
}

.messages-list li {
    padding: 10px;
}

.messages-list li a {
    text-decoration: none;
    color: black;
}

.messages-list li:hover {
    background: lightgray;
}
/* Chat Box (Messenger Style) */
.chat-box {
    position: fixed;
    bottom: 10px;
    right: 110px;
    width: 350px;
    max-height: 500px;
    background: white;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: column;
}

.chat-box-header {
    background: #0084ff;
    color: white;
    padding: 10px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-box-header h2 {
    font-size: 16px;
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

.chat-box-body {
    padding: 10px;
    height: 350px;
    overflow-y: auto;
    background: #f1f1f1;
    display: flex;
    flex-direction: column;
}

.chat-box-body .message {
    padding: 8px 12px;
    border-radius: 18px;
    margin-bottom: 5px;
    max-width: 75%;
    word-wrap: break-word;
    font-size: 14px;
}

.chat-box-body .sent {
    align-self: flex-end;
    background: #0084ff;
    color: white;
}

.chat-box-body .received {
    align-self: flex-start;
    background: #e4e6eb;
    color: black;
}

.chat-form {
    display: flex;
    padding: 8px;
    border-top: 1px solid #ddd;
    background: white;
    border-radius: 0 0 10px 10px;
}

.chat-form input {
    flex: 1;
    padding: 8px;
    border: none;
    outline: none;
    border-radius: 5px;
}

.chat-form button {
    background: #0084ff;
    border: none;
    color: white;
    padding: 8px 12px;
    border-radius: 5px;
    margin-left: 5px;
    cursor: pointer;
}

</style>

</body>
</html>
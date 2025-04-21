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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="message.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

<div class="chat-container">
        <div class="chat-sidebar">
            <h2>Chats</h2>
            <ul class="user-list">
                <?php 
                 $sql = "SELECT username FROM users WHERE username != ?";
                 $stmt = $conn->prepare($sql);
                 $stmt->bind_param("s", $username);
                 $stmt->execute();
                 $result = $stmt->get_result();
                 while ($row = $result->fetch_assoc()) {
                 echo "<li><a href='message.php?user=" . htmlspecialchars($row['username']) . "'>";
                echo "<i class='bx bx-user-circle'></i>"; // Boxicons user icon
                echo ucfirst($row['username']);
                echo "</a></li>";
                }
                ?>
            </ul>

        </div>
        
        <div class="chat-box">
            <?php if ($selectedUser): ?>
                <div class="chat-header">
                    <h2><?php echo htmlspecialchars($selectedUser); ?></h2>
                </div>
                <div class="chat-messages" id="chat-messages">
                    <!-- Messages will load here -->
                </div>
                <form id="chat-form">
                    <input type="hidden" id="sender" value="<?php echo $username; ?>">
                    <input type="hidden" id="receiver" value="<?php echo $selectedUser; ?>">
                    <input type="text" id="message" placeholder="Type your message..." required>
                    <button type="submit">Send</button>
                </form>
            <?php else: ?>
                <p>Select a user to start chatting.</p>
            <?php endif; ?>
        </div>
</div>

    <script>
        $(document).ready(function () {
            function loadMessages() {
                let sender = $('#sender').val();
                let receiver = $('#receiver').val();
                if (receiver) {
                    $.ajax({
                        url: "fetch_messages.php",
                        type: "POST",
                        data: { sender: sender, receiver: receiver },
                        success: function (response) {
                            $('#chat-messages').html(response);
                        }
                    });
                }
            }
            
            setInterval(loadMessages, 3000);
            
            $('#chat-form').submit(function (e) {
                e.preventDefault();
                let sender = $('#sender').val();
                let receiver = $('#receiver').val();
                let message = $('#message').val();
                
                $.ajax({
                    url: "send_message.php",
                    type: "POST",
                    data: { sender: sender, receiver: receiver, message: message },
                    success: function () {
                        $('#message').val('');
                        loadMessages();
                    }
                });
            });
        });

        let typingTimer;
let typing = false;

$('#message').on('input', function () {
    clearTimeout(typingTimer);
    if (!typing) {
        typing = true;
        updateTypingStatus(true);
    }
    typingTimer = setTimeout(() => {
        typing = false;
        updateTypingStatus(false);
    }, 1000); // User stopped typing after 1s
});

function updateTypingStatus(isTyping) {
    let sender = $('#sender').val();
    let receiver = $('#receiver').val();
    $.post('update_typing.php', {
        sender: sender,
        receiver: receiver,
        is_typing: isTyping
    });
}

function checkTypingStatus() {
    let sender = $('#sender').val();
    let receiver = $('#receiver').val();
    $.post('check_typing.php', {
        sender: sender,
        receiver: receiver
    }, function (response) {
        if (response === 'typing') {
            $('#typing-indicator').text(receiver + ' is typing...');
        } else {
            $('#typing-indicator').text('');
        }
    });
}

setInterval(checkTypingStatus, 1000); // Check every second

    </script>

<style>
body {
    margin: 0;
    padding: 0;
    background-color: fff;
    font-family: 'Segoe UI', sans-serif;
    color: #fff; /* Make text white for better contrast */
}


.chat-container {
    display: flex;
    height: 100vh; /* Adjusts the height of the container */
    width: 100%; /* Ensures the container spans the full width */
    background-color:#ddd; /* Light background color for the chat area */
    border: 1px solid ; /* Border around the entire container */
    border-radius: 8px; /* Rounded corners for the container */
}

.chat-sidebar {
    width: 300px; /* Width of the sidebar */
    background-color: #fff; /* White background for the sidebar */
    border-right: 1px solid #fff; /* Border separating the sidebar */
    overflow-y: auto; /* Allows scrolling if there are many users */
    padding: 20px; /* Padding for inner space */
}

.chat-box {
    flex-grow: 1; /* The chat-box takes the remaining space */
    display: flex;
    flex-direction: column;
    padding: 20px;
    background-color: #fff; /* White background for chat box */
}

.chat-sidebar h2 {
    font-size: 24px; /* Increased font size */
    font-weight: bold;
    margin-bottom: 20px; /* Space between the title and the list */
    color: #333; /* Darker text color */
}

.user-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.user-list li {
    padding: 12px 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
}

.user-list li i {
    margin-right: 15px;
    font-size: 30px;
    color: #333;
}

.user-list li:hover {
    background-color: #f4f4f4;
}

.chat-header {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #444;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding-right: 20px; /* Space for scrollbar */
    margin-bottom: 15px;
}

#chat-form {
    display: flex;
    padding: 10px;
    background-color: #f4f4f4;
    border-top: 1px solid #ddd;
}

#chat-form input[type="text"] {
    flex: 1;
    padding: 12px;
    border-radius: 20px;
    border: 1px solid #ddd;
    margin-right: 10px;
    font-size: 16px;
    color: #333;
}

#chat-form button {
    padding: 10px 18px;
    background-color: #0084ff;
    color: #fff;
    border: none;
    border-radius: 20px;
    font-weight: bold;
    cursor: pointer;
}

#chat-form button:hover {
    background-color: #006fe0;
}


.chat-sidebar {
    width: 320px;
    height: 540px;
    max-height: 95vh;                     /* Limit height for scrolling */
    background-color: #f0f2f5;            /* Messenger-like light bg */
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 15px;
    margin: 10px;
    margin-left: 20px;
    overflow-y: auto;                     /* Enable vertical scrolling */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    scrollbar-width: thin;               /* For Firefox */
    scrollbar-color: #ccc transparent;   /* For Firefox */
}

/* Chrome/Edge/Safari scrollbar styling */
.chat-sidebar::-webkit-scrollbar {
    width: 6px;
}

.chat-sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.chat-sidebar::-webkit-scrollbar-thumb {
    background-color: #ccc;
    border-radius: 3px;
}

/* List Styling */
.user-list li {
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background-color 0.2s ease;
}

.user-list li:hover {
    background-color: #e4e6eb;
}

.user-list li i {
    font-size: 90px;
    margin-right: 10px;
    color: #65676b;
}

.user-list li a {
    color: #050505;
    font-size: 28px;
    font-weight: 500;
    text-decoration: none;
}


.chat-sidebar h2 {
    margin-bottom: 32px;
    font-size: 50px;
    color: #333;
    border-bottom: 1px solid #ddd;
    padding-bottom: 8px;
}

.user-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.user-list li {
    display: flex;
    align-items: center;
    padding: 10px;
    border-radius: 8px;
    transition: background-color 0.2s ease;
}

.user-list li i {
    font-size: 60px;
    color: #444;
    margin-right: 10px;
}

.user-list li a {
    text-decoration: none;
    color: #000;
    font-weight: bold;
    font-size: 18px;
}

.user-list li:hover {
    background-color: #f5f5f5;
}

.chat-box {
    flex: 1;
    width: 750px;
    display: flex;
    flex-direction: column;
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-radius: 10px;
    margin: 8px 5px 5px 0;
    padding: 0;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    height: 570px;
    margin-left: 1px;
    margin-right: 50px;
}

.chat-header {
    padding: 15px;
    background-color: #f0f2f5;
    border-bottom: 1px solid #ddd;
    font-size: 29px;
    font-weight: 600;
    color: #050505;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background-color: #ffffff;
    scrollbar-width: thin;
    scrollbar-color: #ccc transparent;
}

.chat-messages::-webkit-scrollbar {
    width: 6px;
}
.chat-messages::-webkit-scrollbar-thumb {
    background-color: #ccc;
    border-radius: 3px;
}

.message-row {
    display: flex;
    margin-bottom: 5px;
}

.sent {
    justify-content: flex-end;
}

.received {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 60%;
    padding: 10px;
    border-radius: 15px;
    background-color: #DCF8C6; /* Sent */
    color: #000;
}

.received .message-bubble {
    background-color: #f1f0f0;
}

.timestamp-center {
    text-align: center;
    font-size: 11px;
    color: #999;
    margin: 5px 0 15px;
}


/* Input area */
#chat-form {
    display: flex;
    padding: 12px 16px;
    background-color: #f0f2f5;
    border-top: 1px solid #ddd;
}

#chat-form input[type="text"] {
    flex: 1;
    padding: 12px 15px;
    border-radius: 25px;
    border: 1px solid #ccc;
    background-color: #fff;
    color: #050505;
    font-size: 14px;
    outline: none;
    margin-right: 10px;
}

#chat-form button {
    padding: 10px 18px;
    background-color: #0084ff;
    color: #fff;
    border: none;
    border-radius: 25px;
    font-weight: bold;
    cursor: pointer;
}

#chat-form button:hover {
    background-color: #006fe0;
}
.chat-container {
    display: flex;
    height: 90vh;
    width: 100%;
    gap: 20px;
    padding: 20px;
    box-sizing: border-box;
    background-color: #f5f5f5;
}

/* Sidebar */
.chat-sidebar {
    width: 300px;
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow-y: auto;
    padding: 15px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.chat-sidebar h2 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #333;
}

.user-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.user-list li {
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
}

.user-list li:hover {
    background-color: #f0f0f0;
}

.user-list li a {
    color: #333;
    text-decoration: none;
    font-size: 16px;
    display: flex;
    align-items: center;
    width: 100%;
}

.user-list li i {
    margin-right: 10px;
    font-size: 24px;
}

/* Chat Box */
.chat-box {
    flex: 1;
    display: flex;
    flex-direction: column;
    background-color: #ffffff;
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.chat-header {
    padding: 15px;
    background-color: #ffffff;
    border-bottom: 1px solid #ddd;
    font-size: 18px;
    font-weight: 600;
    color: #000;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    color: #000;
}

#chat-form {
    display: flex;
    padding: 12px 16px;
    background-color: #f9f9f9;
    border-top: 1px solid #ddd;
}

#chat-form input[type="text"] {
    flex: 1;
    padding: 12px 15px;
    border-radius: 25px;
    border: 1px solid #ccc;
    font-size: 14px;
    margin-right: 10px;
}

#chat-form button {
    padding: 10px 18px;
    background-color: #0084ff;
    color: #fff;
    border: none;
    border-radius: 25px;
    font-weight: bold;
    cursor: pointer;
}

#chat-form button:hover {
    background-color: #006fe0;
}


</style>

</body>
</html>

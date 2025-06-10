<?php
$host = "localhost";
$db = "college_helpdesk";
$user = "root";
$pass = ""; // change if you have a password

$conn = new mysqli('sql203.infinityfree.com', 'if0_39199339', 'prat1019', 'if0_39199339_college_helpdesk');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$action = $_POST['action'] ?? '';
$user_email = $_POST['email'] ?? '';

if ($action === 'send') {
    $message = $_POST['message'];
    $sender = $_POST['sender']; // 'user' or 'bot'

    $stmt = $conn->prepare("INSERT INTO chat_messages (user_email, message, sender) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user_email, $message, $sender);
    $stmt->execute();
    echo json_encode(["success" => true]);
}
else if ($action === 'fetch') {
    $stmt = $conn->prepare("SELECT message, sender, timestamp FROM chat_messages WHERE user_email = ? ORDER BY timestamp ASC");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $chats = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["success" => true, "chats" => $chats]);
}
else if ($action === 'clear') {
    $stmt = $conn->prepare("DELETE FROM chat_messages WHERE user_email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    echo json_encode(["success" => true]);
}
$conn->close();
?>

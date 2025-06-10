<?php
require_once 'config.php';

session_start();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'send_message') {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $message = sanitizeInput($_POST['message']);
    $department = sanitizeInput($_POST['department'] ?? '');
    
    // Simple keyword matching for demo
    $answer = "I couldn't find an answer. Would you like to raise a ticket for this question?";
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT answer FROM faqs WHERE question LIKE ? AND (department = ? OR ? = '') LIMIT 1");
    $keyword = "%" . explode(" ", $message)[0] . "%";
    $stmt->bind_param("sss", $keyword, $department, $department);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $answer = $row['answer'];
    }
    
    // Save to chat history
    $stmt = $conn->prepare("INSERT INTO chat_history (user_id, message, answer, department) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $message, $answer, $department);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'answer' => $answer]);
} elseif ($action === 'get_history') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM chat_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = [];
    
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    echo json_encode(['success' => true, 'history' => array_reverse($history)]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
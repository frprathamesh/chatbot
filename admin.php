<?php
require_once 'config.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'get_user_stats') {
    $conn = getDBConnection();
    
    // Get total users
    $stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $total_users = $result->fetch_assoc()['total_users'];
    
    // Get active today (example - you might need to adjust this)
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as active_today FROM chat_history WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $result = $stmt->get_result();
    $active_today = $result->fetch_assoc()['active_today'];
    
    echo json_encode([
        'success' => true,
        'total_users' => $total_users,
        'active_today' => $active_today
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
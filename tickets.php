<?php
require_once 'config.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'submit_ticket') {
    $department = sanitizeInput($_POST['department']);
    $message = sanitizeInput($_POST['message']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO tickets (user_id, department, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_SESSION['user_id'], $department, $message);
    
    if ($stmt->execute()) {
        // Create notification for admin
        $ticket_id = $conn->insert_id;
        $stmt = $conn->prepare("SELECT id FROM users WHERE is_admin = 1 LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        $title = "New Ticket Submitted";
        $message = "User {$_SESSION['user_name']} submitted a ticket (ID: $ticket_id) in $department department";
        
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $admin['id'], $title, $message);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Ticket submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit ticket']);
    }
} elseif ($action === 'get_tickets') {
    if (!$_SESSION['is_admin']) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $status = $_POST['status'] ?? 'open';
    $conn = getDBConnection();
    
    if ($status === 'all') {
        $stmt = $conn->prepare("SELECT t.*, u.name as user_name FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
    } else {
        $stmt = $conn->prepare("SELECT t.*, u.name as user_name FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.status = ? ORDER BY t.created_at DESC");
        $stmt->bind_param("s", $status);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $tickets = [];
    
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
    
    echo json_encode(['success' => true, 'tickets' => $tickets]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

?>
<?php
require_once 'config.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'request_document') {
    $document_id = intval($_POST['document_id']);
    $reason = sanitizeInput($_POST['reason']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO document_requests (user_id, document_id, reason) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $_SESSION['user_id'], $document_id, $reason);
    
    if ($stmt->execute()) {
        // Create notification for admin
        $request_id = $conn->insert_id;
        $stmt = $conn->prepare("SELECT id FROM users WHERE is_admin = 1 LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        $title = "New Document Request";
        $message = "User {$_SESSION['user_name']} requested a document (ID: $request_id)";
        
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $admin['id'], $title, $message);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Document request submitted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit request']);
    }
} elseif ($action === 'download_document') {
    $document_id = intval($_POST['document_id']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT name, file_path FROM documents WHERE id = ?");
    $stmt->bind_param("i", $document_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Document not found']);
        exit;
    }
    
    $document = $result->fetch_assoc();
    $file_path = $document['file_path'];
    
    if (file_exists($file_path)) {
        // Create notification for admin
        $title = "Document Downloaded";
        $message = "User {$_SESSION['user_name']} downloaded {$document['name']}";
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE is_admin = 1 LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $admin['id'], $title, $message);
        $stmt->execute();
        
        // Return file info for download
        echo json_encode([
            'success' => true,
            'message' => 'Downloading document...',
            'file_name' => basename($file_path),
            'file_path' => $file_path
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'File not found on server']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
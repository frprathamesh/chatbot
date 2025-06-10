<?php
require_once 'config.php';

session_start();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get_faqs') {
    $department = $_POST['department'] ?? '';
    $conn = getDBConnection();
    
    if (empty($department)) {
        $stmt = $conn->prepare("SELECT * FROM faqs ORDER BY department, created_at DESC");
    } else {
        $stmt = $conn->prepare("SELECT * FROM faqs WHERE department = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $department);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $faqs = [];
    
    while ($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
    
    echo json_encode(['success' => true, 'faqs' => $faqs]);
} elseif ($action === 'add_faq') {
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $question = sanitizeInput($_POST['question']);
    $answer = sanitizeInput($_POST['answer']);
    $department = sanitizeInput($_POST['department']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO faqs (question, answer, department) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $question, $answer, $department);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'FAQ added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add FAQ']);
    }
} // Update the delete_faq action in faqs.php
elseif ($action === 'delete_faq') {
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $faq_id = intval($_POST['faq_id']);
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->bind_param("i", $faq_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'FAQ deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete FAQ']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
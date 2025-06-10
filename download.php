<?php
$allowed = ['bonafide', 'transfer'];
$type = $_POST['type'] ?? '';

if (!in_array($type, $allowed)) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

$files = [
    'bonafide' => 'C:\xampp\htdocs\MP\bonafide.pdf',
    'transfer' => 'C:\Users\Lenovo\Documents\transfer.pdf'
];

$filepath = $files[$type];

if (!file_exists($filepath)) {
    header('HTTP/1.1 404 Not Found');
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.basename($filepath).'"';
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
?>
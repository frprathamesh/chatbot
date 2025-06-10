<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'college_helpdesk');

function getDBConnection() {
    $conn = new mysqli('sql203.infinityfree.com', 'epiz_XXXXXXX', 'prat1019', 'if0_39199339_college_helpdesk');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>

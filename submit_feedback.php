<?php
// DB credentials
$host = "localhost";
$user = "root";
$password = ""; // use your MySQL password if set
$dbname = "college_helpdesk";

// Create connection
$conn = new mysqli('sql203.infinityfree.com', 'if0_39199339', 'prat1019', 'if0_39199339_college_helpdesk');


// Check connection
if ($conn->connect_error) {
  die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

// Get and sanitize inputs
$data = json_decode(file_get_contents("php://input"), true);

$fullName = $conn->real_escape_string($data['fullName']);
$email = $conn->real_escape_string($data['email']);
$rating = (int) $data['rating'];
$feedbackText = $conn->real_escape_string($data['feedbackText']);

// Insert into DB
$sql = "INSERT INTO feedback (full_name, email, rating, feedback_text) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssis", $fullName, $email, $rating, $feedbackText);

if ($stmt->execute()) {
  echo json_encode(["status" => "success", "message" => "Feedback submitted"]);
} else {
  echo json_encode(["status" => "error", "message" => "Failed to submit feedback"]);
}

$stmt->close();
$conn->close();
?>

<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['id']) && !empty($data['username'])) {
    $_SESSION['user_id'] = $data['id'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['full_name'] = $data['full_name'];
    $_SESSION['role'] = $data['role'];
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session data']);
}
?>
<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if user is approved (except admin)
if ($_SESSION['user_type'] !== 'admin') {
    $conn = getConnection();
    $sql = "SELECT status FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    if ($user['status'] !== 'approved') {
        session_destroy();
        header("Location: ../login.php?error=not_approved");
        exit();
    }
}
?>
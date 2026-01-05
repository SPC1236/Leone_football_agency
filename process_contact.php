<?php
require_once 'config/config.php';

// Start session for flash messages
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['contact_error'] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_error'] = "Please enter a valid email address.";
    } else {
        $conn = getConnection();
        
        // Prepare SQL statement
        $sql = "INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
            
            if ($stmt->execute()) {
                $_SESSION['contact_success'] = "Thank you for your message! We'll get back to you soon.";
                // Store form data in session to repopulate on error
                $_SESSION['contact_form_data'] = $_POST;
            } else {
                $_SESSION['contact_error'] = "Sorry, there was an error. Please try again.";
                $_SESSION['contact_form_data'] = $_POST;
            }
            
            $stmt->close();
        } else {
            $_SESSION['contact_error'] = "Database error. Please try again.";
            $_SESSION['contact_form_data'] = $_POST;
        }
        
        $conn->close();
    }
    
    // Redirect back to contact page
    header("Location: contact.php#contact-form");
    exit();
} else {
    header("Location: contact.php");
    exit();
}
?>
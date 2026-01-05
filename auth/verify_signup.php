<?php
// auth/verify_signup.php - User Registration Handler
require_once '../config/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
redirectIfLoggedIn();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../signup.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('error', 'Invalid request. Please try again.');
    header("Location: ../signup.php");
    exit();
}

// Sanitize and validate inputs
$full_name = sanitize($_POST['full_name']);
$username = sanitize($_POST['username']);
$email = sanitize($_POST['email']);
$phone = sanitize($_POST['phone'] ?? '');
$user_type = sanitize($_POST['user_type']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validation array
$errors = [];

// Validate full name
if (empty($full_name) || strlen($full_name) < 3) {
    $errors[] = 'Full name must be at least 3 characters long.';
}

// Validate username
if (empty($username) || strlen($username) < 4) {
    $errors[] = 'Username must be at least 4 characters long.';
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username can only contain letters, numbers, and underscores.';
}

// Validate email
if (empty($email) || !isValidEmail($email)) {
    $errors[] = 'Please provide a valid email address.';
}

// Validate user type
$valid_types = ['player', 'agent', 'club_manager'];
if (!in_array($user_type, $valid_types)) {
    $errors[] = 'Invalid user type selected.';
}

// Validate password
if (empty($password)) {
    $errors[] = 'Password is required.';
} elseif (!isStrongPassword($password)) {
    $errors[] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters and include uppercase, lowercase, and number.';
}

// Validate password confirmation
if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match.';
}

// If there are validation errors, redirect back
if (!empty($errors)) {
    $_SESSION['signup_errors'] = $errors;
    $_SESSION['signup_data'] = $_POST; // Keep form data
    setFlashMessage('error', implode('<br>', $errors));
    header("Location: ../signup.php");
    exit();
}

try {
    $conn = getDBConnection();
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        setFlashMessage('error', 'Username already exists. Please choose another.');
        header("Location: ../signup.php");
        exit();
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        setFlashMessage('error', 'Email address already registered. Please login or use another email.');
        header("Location: ../signup.php");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Insert into users table
    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password, user_type, full_name, phone, is_approved) 
        VALUES (?, ?, ?, ?, ?, ?, 0)
    ");
    $stmt->execute([$username, $email, $hashed_password, $user_type, $full_name, $phone]);
    
    $user_id = $conn->lastInsertId();
    
    // Insert into appropriate role-specific table
    if ($user_type === 'player') {
        $stmt = $conn->prepare("
            INSERT INTO players (user_id, nationality) 
            VALUES (?, 'Sierra Leone')
        ");
        $stmt->execute([$user_id]);
        
    } elseif ($user_type === 'agent') {
        $stmt = $conn->prepare("
            INSERT INTO agents (user_id, total_clients, total_deals_completed) 
            VALUES (?, 0, 0)
        ");
        $stmt->execute([$user_id]);
        
    } elseif ($user_type === 'club_manager') {
        $stmt = $conn->prepare("
            INSERT INTO club_managers (user_id) 
            VALUES (?)
        ");
        $stmt->execute([$user_id]);
    }
    
    // Create notification for admin
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, user_type, title, message, notification_type)
        VALUES (1, 'admin', 'New Registration', 'New user registered: {$full_name} ({$user_type})', 'system')
    ");
    $stmt->execute();
    
    // Log the registration activity
    $stmt = $conn->prepare("
        INSERT INTO activity_log (user_id, user_type, action, description, ip_address)
        VALUES (?, ?, 'REGISTRATION', 'User registered: {$full_name}', ?)
    ");
    $stmt->execute([$user_id, $user_type, $_SERVER['REMOTE_ADDR']]);
    
    // Commit transaction
    $conn->commit();
    
    // Clear any old signup data
    unset($_SESSION['signup_data']);
    unset($_SESSION['signup_errors']);
    
    // Set success message
    setFlashMessage('success', 'Registration successful! Your account is pending admin approval. You will be notified once approved.');
    header("Location: ../login.php");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log the error
    error_log("Registration Error: " . $e->getMessage());
    
    setFlashMessage('error', 'An error occurred during registration. Please try again.');
    header("Location: ../signup.php");
    exit();
}
?>
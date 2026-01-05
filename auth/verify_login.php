<?php
// auth/verify_login.php - Login Verification Handler
require_once '../config/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
redirectIfLoggedIn();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('error', 'Invalid request. Please try again.');
    header("Location: ../login.php");
    exit();
}

$username = sanitize($_POST['username']);
$password = $_POST['password'];

// Validate inputs
if (empty($username) || empty($password)) {
    setFlashMessage('error', 'Please fill in all fields.');
    header("Location: ../login.php");
    exit();
}

try {
    $conn = getDBConnection();
    
    // Check if user is trying to login as admin
    $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND user_type = 'admin'");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Admin login
        if (password_verify($password, $admin['password'])) {
            // Update last login
            $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$admin['id']]);

            // Set session variables
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['full_name'] = $admin['full_name'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['is_admin'] = true;

            header("Location: ../admin/index.php");
            exit();
        } else {
            setFlashMessage('error', 'Invalid credentials.');
            header("Location: ../login.php");
            exit();
        }
    }
    
    // Regular user login
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        setFlashMessage('error', 'Invalid credentials.');
        header("Location: ../login.php");
        exit();
    }
    
    // Check if account is locked
    if (isAccountLocked($user['id'], $conn)) {
        $remaining_time = ceil((ACCOUNT_LOCK_TIME - (time() - strtotime($user['lock_time']))) / 60);
        setFlashMessage('error', "Account is locked due to too many failed login attempts. Please try again in {$remaining_time} minutes.");
        header("Location: ../login.php");
        exit();
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        $locked = incrementFailedAttempts($username, $conn);
        
        if ($locked) {
            setFlashMessage('error', 'Too many failed login attempts. Your account has been locked for 30 minutes.');
        } else {
            $stmt = $conn->prepare("SELECT failed_login_attempts FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $attempts = $stmt->fetch()['failed_login_attempts'];
            $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
            setFlashMessage('error', "Invalid credentials. {$remaining} attempts remaining before account lockout.");
        }
        
        header("Location: ../login.php");
        exit();
    }
    
    // Check if account is approved
    if (!$user['is_approved']) {
        setFlashMessage('error', 'Your account is pending approval. Please wait for admin approval.');
        header("Location: ../login.php");
        exit();
    }
    
    // Successful login
    resetFailedAttempts($user['id'], $conn);
    
    // Update last login
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_admin'] = false;
    
    // Redirect to appropriate dashboard
    $dashboard_file = "../dashboards/" . $user['user_type'] . "_dashboard.php";
    header("Location: $dashboard_file");
    exit();
    
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    setFlashMessage('error', 'An error occurred. Please try again.');
    header("Location: ../login.php");
    exit();
}
?>
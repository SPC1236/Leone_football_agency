<?php
// auth/session_manager.php - Advanced Session Management
// This file provides additional session security and management features

/**
 * Initialize secure session with enhanced security settings
 */
function initSecureSession() {
    // Prevent session hijacking
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    
    // Set session name
    session_name('FREETOWN_FA_SESSION');
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID periodically to prevent fixation attacks
    if (!isset($_SESSION['session_started'])) {
        session_regenerate_id(true);
        $_SESSION['session_started'] = time();
    } else {
        // Regenerate every 30 minutes
        if (time() - $_SESSION['session_started'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['session_started'] = time();
        }
    }
    
    // Validate session fingerprint
    validateSessionFingerprint();
}

/**
 * Create session fingerprint to prevent session hijacking
 */
function createSessionFingerprint() {
    $fingerprint = md5(
        $_SERVER['HTTP_USER_AGENT'] . 
        $_SERVER['REMOTE_ADDR'] .
        'FREETOWN_FA_SALT'
    );
    $_SESSION['fingerprint'] = $fingerprint;
}

/**
 * Validate session fingerprint
 */
function validateSessionFingerprint() {
    if (!isset($_SESSION['fingerprint'])) {
        createSessionFingerprint();
        return true;
    }
    
    $current_fingerprint = md5(
        $_SERVER['HTTP_USER_AGENT'] . 
        $_SERVER['REMOTE_ADDR'] .
        'FREETOWN_FA_SALT'
    );
    
    if ($_SESSION['fingerprint'] !== $current_fingerprint) {
        // Session hijacking detected
        destroySession();
        setFlashMessage('error', 'Session validation failed. Please login again.');
        header("Location: /login.php");
        exit();
    }
    
    return true;
}

/**
 * Destroy session completely
 */
function destroySession() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Store user data in session after successful login
 * @param array $user_data - User information
 * @param bool $is_admin - Whether user is admin
 */
function createUserSession($user_data, $is_admin = false) {
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Store user information
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['full_name'] = $user_data['full_name'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['is_admin'] = $is_admin;
    
    if (!$is_admin) {
        $_SESSION['user_type'] = $user_data['user_type'];
    } else {
        $_SESSION['user_type'] = 'admin';
    }
    
    // Create session fingerprint
    createSessionFingerprint();
    
    // Set session start time
    $_SESSION['session_started'] = time();
    $_SESSION['last_activity'] = time();
    
    // Store login time
    $_SESSION['login_time'] = date('Y-m-d H:i:s');
}

/**
 * Check if "Remember Me" is enabled and create persistent cookie
 * @param int $user_id
 * @param string $token - Unique remember token
 */
function createRememberMeCookie($user_id, $token) {
    // Hash token before storing
    $hashed_token = password_hash($token, PASSWORD_DEFAULT);
    
    // Store in cookie (30 days)
    setcookie(
        'remember_me',
        $user_id . ':' . $token,
        time() + (30 * 24 * 60 * 60),
        '/',
        '',
        false, // Set to true in production with HTTPS
        true // HTTP only
    );
    
    try {
        $conn = getDBConnection();
        
        // Store token in database (create remember_tokens table first)
        // For now, store in session
        $_SESSION['remember_token'] = [
            'user_id' => $user_id,
            'token' => $hashed_token,
            'expiry' => date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60))
        ];
        
    } catch (Exception $e) {
        error_log("Remember me error: " . $e->getMessage());
    }
}

/**
 * Validate and authenticate using "Remember Me" cookie
 * @return bool Success status
 */
function validateRememberMeCookie() {
    if (!isset($_COOKIE['remember_me'])) {
        return false;
    }
    
    list($user_id, $token) = explode(':', $_COOKIE['remember_me'], 2);
    
    try {
        $conn = getDBConnection();
        
        // Check admin first
        $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        $is_admin = true;
        
        if (!$user) {
            // Check regular users
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND is_approved = 1");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            $is_admin = false;
        }
        
        if ($user && isset($_SESSION['remember_token'])) {
            if (password_verify($token, $_SESSION['remember_token']['token'])) {
                // Valid token, create session
                createUserSession($user, $is_admin);
                return true;
            }
        }
        
        // Invalid token, remove cookie
        setcookie('remember_me', '', time() - 3600, '/');
        return false;
        
    } catch (Exception $e) {
        error_log("Remember me validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Clear "Remember Me" cookie
 */
function clearRememberMeCookie() {
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
    
    unset($_SESSION['remember_token']);
}

/**
 * Get user session information
 * @return array Session info
 */
function getSessionInfo() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'user_type' => $_SESSION['user_type'] ?? null,
        'is_admin' => $_SESSION['is_admin'] ?? false,
        'login_time' => $_SESSION['login_time'] ?? null,
        'last_activity' => $_SESSION['last_activity'] ?? null,
        'session_duration' => time() - ($_SESSION['session_started'] ?? time())
    ];
}

/**
 * Check for concurrent sessions (optional security feature)
 * Limit one active session per user
 * @param int $user_id
 * @return bool
 */
function checkConcurrentSessions($user_id) {
    // This would require a sessions table in database
    // For basic implementation, we'll skip this
    // In production, implement proper session storage in DB
    return true;
}

/**
 * Log session activity
 * @param string $activity - Activity description
 */
function logSessionActivity($activity) {
    if (!isLoggedIn()) {
        return;
    }
    
    try {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("
            INSERT INTO activity_log (user_id, user_type, action, description, ip_address, user_agent)
            VALUES (?, ?, 'SESSION_ACTIVITY', ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['user_type'],
            $activity,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
    } catch (Exception $e) {
        error_log("Session activity log error: " . $e->getMessage());
    }
}

/**
 * Extend session timeout (for active users)
 */
function extendSession() {
    if (isLoggedIn()) {
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Check if user's account status has changed (deactivated, unapproved, etc.)
 * @return bool True if account is valid
 */
function validateAccountStatus() {
    if (!isLoggedIn() || isAdmin()) {
        return true;
    }
    
    try {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("
            SELECT is_approved, account_locked 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user || !$user['is_approved'] || $user['account_locked']) {
            destroySession();
            setFlashMessage('error', 'Your account has been deactivated or locked.');
            header("Location: /login.php");
            exit();
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Account status check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get session statistics for admin dashboard
 * @return array Session statistics
 */
function getSessionStatistics() {
    try {
        $conn = getDBConnection();
        
        // Count active sessions (last 30 minutes)
        $stmt = $conn->query("
            SELECT 
                COUNT(DISTINCT user_id) as active_users,
                user_type
            FROM activity_log
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            AND action IN ('LOGIN', 'SESSION_ACTIVITY')
            GROUP BY user_type
        ");
        
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Session statistics error: " . $e->getMessage());
        return [];
    }
}

// Initialize secure session when this file is included
initSecureSession();
?>
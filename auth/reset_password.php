
<?php
/* ============================================
   auth/reset_password.php - Reset Password Handler
   ============================================ */
?>
<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

redirectIfLoggedIn();

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$valid_token = false;

// Verify token
if (!empty($token) && isset($_SESSION['password_reset'])) {
    $reset_data = $_SESSION['password_reset'];
    
    if ($reset_data['token'] === $token && strtotime($reset_data['expiry']) > time()) {
        $valid_token = true;
    } else {
        $message = 'Invalid or expired reset link.';
        $message_type = 'error';
    }
} else {
    $message = 'Invalid reset link.';
    $message_type = 'error';
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $message = 'Please fill in all fields.';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $message_type = 'error';
    } elseif (!isStrongPassword($new_password)) {
        $message = 'Password must be at least 8 characters with uppercase, lowercase, and number.';
        $message_type = 'error';
    } else {
        try {
            $conn = getDBConnection();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['password_reset']['user_id']]);
            
            // Log activity
            $stmt = $conn->prepare("
                INSERT INTO activity_log (user_id, user_type, action, description)
                VALUES (?, 'user', 'PASSWORD_RESET', 'Password reset successfully')
            ");
            $stmt->execute([$_SESSION['password_reset']['user_id']]);
            
            // Clear reset data
            unset($_SESSION['password_reset']);
            
            setFlashMessage('success', 'Password reset successfully! You can now login with your new password.');
            header("Location: ../login.php");
            exit();
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $message = 'An error occurred. Please try again.';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Freetown Football Agency</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary-blue: #1a365d;
            --accent-green: #2d5a27;
            --light-green: #4a7c59;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(26, 54, 93, 0.9), rgba(26, 54, 93, 0.9)), url('../images/Hero1.jpeg');
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 90%;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-header h1 {
            color: var(--primary-blue);
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--primary-blue);
            font-weight: 600;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1rem;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent-green);
        }
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-error {
            background-color: #fee;
            color: #c33;
        }
        .alert-success {
            background-color: #efe;
            color: #3c3;
        }
        .btn-auth {
            width: 100%;
            padding: 14px;
            background-color: var(--accent-green);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-auth:hover {
            background-color: var(--light-green);
        }
    </style>
</head>
<body>
    <div class="auth-box">
        <div class="auth-header">
            <h1>Reset Password</h1>
            <p>Enter your new password</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($valid_token): ?>
        <form method="POST">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" 
                       placeholder="Enter new password" required>
                <small style="color: #666;">Min. 8 characters with uppercase, lowercase & number</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                       placeholder="Re-enter new password" required>
            </div>
            
            <button type="submit" class="btn-auth">Reset Password</button>
        </form>
        <?php else: ?>
            <p style="text-align: center;">
                <a href="forgot_password.php" style="color: var(--accent-green); text-decoration: none; font-weight: 600;">
                    Request a new reset link
                </a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
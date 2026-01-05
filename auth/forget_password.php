<?php
/* ============================================
   forgot_password.php - Forgot Password Page
   ============================================ */
require_once 'config/config.php';
require_once 'includes/functions.php';

redirectIfLoggedIn();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    
    if (!isValidEmail($email)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'error';
    } else {
        try {
            $conn = getDBConnection();
            
            // Check if email exists
            $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store in session (in production, store in database)
                $_SESSION['password_reset'] = [
                    'user_id' => $user['id'],
                    'token' => $token,
                    'expiry' => $expiry,
                    'email' => $email
                ];
                
                // In production, send email with reset link
                // For now, display the link
                $reset_link = SITE_URL . "/auth/reset_password.php?token=" . $token;
                
                $message = "Password reset instructions would be sent to your email. For testing, use this link: <a href='$reset_link'>Reset Password</a>";
                $message_type = 'success';
                
            } else {
                // Don't reveal if email exists (security best practice)
                $message = 'If an account exists with this email, you will receive password reset instructions.';
                $message_type = 'success';
            }
            
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
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
    <title>Forgot Password | Freetown Football Agency</title>
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
        .auth-footer {
            text-align: center;
            margin-top: 20px;
        }
        .auth-footer a {
            color: var(--accent-green);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="auth-box">
        <div class="auth-header">
            <h1>Forgot Password?</h1>
            <p>Enter your email to reset your password</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="Enter your registered email" required>
            </div>
            
            <button type="submit" class="btn-auth">Send Reset Link</button>
        </form>
        
        <div class="auth-footer">
            <p>Remember your password? <a href="../login.php">Login</a></p>
        </div>
    </div>
</body>
</html>

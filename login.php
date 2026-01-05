<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $user_type = $_SESSION['user_type'];
    header("Location: dashboards/{$user_type}_dashboard.php");
    exit();
}

$error_message = '';
$success_message = '';
$page_title = 'Login';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        $conn = getConnection();
        
        // Check if user exists
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check login attempts
            $now = date('Y-m-d H:i:s');
            $last_attempt = strtotime($user['last_login_attempt'] ?? '2000-01-01');
            $time_diff = (strtotime($now) - $last_attempt) / 60; // Difference in minutes
            
            // Reset attempts after 30 minutes
            if ($time_diff > 30 && $user['login_attempts'] >= 5) {
                $sql = "UPDATE users SET login_attempts = 0, last_login_attempt = NULL WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                $user['login_attempts'] = 0;
            }
            
            // Check if account is locked
            if ($user['login_attempts'] >= 5) {
                $error_message = "Account locked due to too many failed login attempts. Try again in 30 minutes.";
            } else {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Check if user is approved
                    if ($user['status'] !== 'approved') {
                        $error_message = "Your account is pending approval by an administrator.";
                    } else {
                        // Reset login attempts
                        $sql = "UPDATE users SET login_attempts = 0, last_login_attempt = NULL WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user['id']);
                        $stmt->execute();
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_type'] = $user['user_type'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['email'] = $user['email'];
                        
                        // Redirect to appropriate dashboard
                        $user_type = $user['user_type'];
                        if ($user_type == 'admin') {
                            header("Location: admin/index.php");
                        } else {
                            header("Location: dashboards/{$user_type}_dashboard.php");
                        }
                        exit();
                    }
                } else {
                    // Increment login attempts
                    $new_attempts = $user['login_attempts'] + 1;
                    $sql = "UPDATE users SET login_attempts = ?, last_login_attempt = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isi", $new_attempts, $now, $user['id']);
                    $stmt->execute();
                    
                    $remaining_attempts = 5 - $new_attempts;
                    if ($remaining_attempts > 0) {
                        $error_message = "Invalid credentials. You have $remaining_attempts attempts remaining.";
                    } else {
                        $error_message = "Account locked due to too many failed login attempts. Try again in 30 minutes.";
                    }
                }
            }
        } else {
            $error_message = "Invalid credentials.";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <title><?php echo $page_title; ?> | Freetown Football Agency</title>
    <style>
        /* Additional inline styles for debugging */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .auth-page-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .auth-main-content {
            flex: 1;
            padding: 60px 20px;
            background-color: #f5f5f5;
        }
        
        .auth-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            min-height: 600px;
        }
        
        .auth-left {
            flex: 1;
            background: linear-gradient(135deg, #1a365d, #2d4a8a);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-left h2 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            color: white;
        }
        
        .auth-left p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 25px;
            opacity: 0.9;
        }
        
        .auth-left ul {
            margin: 25px 0;
            padding-left: 20px;
        }
        
        .auth-left li {
            margin-bottom: 12px;
            position: relative;
            list-style: none;
        }
        
        .auth-left li:before {
            content: '✓';
            position: absolute;
            left: -25px;
            color: #4a7c59;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .auth-right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .auth-header h2 {
            color: #1a365d;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .auth-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1a365d;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box; /* Important for proper width calculation */
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2d5a27;
            box-shadow: 0 0 0 3px rgba(45, 90, 39, 0.1);
        }
        
        .auth-btn {
            width: 100%;
            padding: 16px;
            background-color: #2d5a27;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .auth-btn:hover {
            background-color: #4a7c59;
            transform: translateY(-2px);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .auth-footer p {
            margin-bottom: 10px;
            color: #666;
        }
        
        .auth-footer a {
            color: #2d5a27;
            font-weight: 600;
            text-decoration: none;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .auth-container {
                flex-direction: column;
            }
            
            .auth-left, .auth-right {
                padding: 40px 30px;
            }
            
            .auth-left {
                border-radius: 10px 10px 0 0;
            }
            
            .auth-right {
                border-radius: 0 0 10px 10px;
            }
        }
        
        @media (max-width: 576px) {
            .auth-main-content {
                padding: 30px 15px;
            }
            
            .auth-left, .auth-right {
                padding: 30px 20px;
            }
            
            .auth-left h2 {
                font-size: 1.8rem;
            }
            
            .auth-header h2 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <div class="container header-container">
            <div class="logo">Freetown<span>Football</span>Agency</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About & Services</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="login.php" class="active">Login Portal</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
            <div class="mobile-menu">☰</div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="auth-main-content">
        <div class="container auth-container">
            <!-- Left Side: Introduction -->
            <div class="auth-left">
                <h2>Welcome Back!</h2>
                <p>Access your Freetown Football Agency account to manage your football career or representation.</p>
                
                <h3 style="margin-top: 30px; color: #4a7c59;">Benefits of Your Account:</h3>
                <ul>
                    <li>Access to exclusive opportunities</li>
                    <li>Manage your profile and stats</li>
                    <li>Connect with clubs and agents</li>
                    <li>Track your career progress</li>
                    <li>Receive personalized recommendations</li>
                </ul>
                
                <p style="margin-top: 30px; font-style: italic;">
                    "The platform that launched my professional career in Europe."<br>
                    <strong>- John Kamara, Professional Footballer</strong>
                </p>
            </div>
            
            <!-- Right Side: Login Form -->
            <div class="auth-right">
                <div class="auth-header">
                    <h2>Login Portal</h2>
                    <p>Enter your credentials to access your account</p>
                </div>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username or Email *</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Enter your username or email" required
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Enter your password" required>
                    </div>
                    
                    <button type="submit" class="auth-btn">Login to Your Account</button>
                </form>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="signup.php">Create one here</a></p>
                    <p><a href="index.php">← Back to Home</a></p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-container">
                <div class="footer-col">
                    <h3>Freetown Football Agency</h3>
                    <p>Connecting Sierra Leonean football talent with global opportunities since 2010.</p>
                    <div class="social-links">
                        <a href="#">FB</a>
                        <a href="#">WS</a>
                        <a href="#">IG</a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="login.php">Login Portal</a></li>
                        <li><a href="signup.php">Sign Up</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contact Info</h3>
                    <p>12 Wilkinson Road, Freetown</p>
                    <p>Sierra Leone</p>
                    <p>Phone: +232 76 123 456</p>
                    <p>Email: info@freetownfa.com</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 Freetown Football Agency. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
        
        // Add active class to current page in navigation
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-links a');
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href');
                if (linkPage === currentPage || 
                    (currentPage === '' && linkPage === 'index.php')) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
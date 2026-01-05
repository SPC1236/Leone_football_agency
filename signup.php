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
$page_title = 'Sign Up';

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || 
        empty($user_type) || empty($full_name)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } else {
        $conn = getConnection();
        
        // Check if username or email already exists
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Username or email already exists.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (username, email, password, user_type, full_name, phone, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $username, $email, $hashed_password, $user_type, $full_name, $phone);
            
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                
                // Create specific type record
                if ($user_type == 'player') {
                    $sql = "INSERT INTO players (user_id) VALUES (?)";
                } elseif ($user_type == 'agent') {
                    $sql = "INSERT INTO agents (user_id) VALUES (?)";
                } elseif ($user_type == 'manager') {
                    $sql = "INSERT INTO club_managers (user_id) VALUES (?)";
                }
                
                if (isset($sql)) {
                    $stmt2 = $conn->prepare($sql);
                    $stmt2->bind_param("i", $user_id);
                    $stmt2->execute();
                    $stmt2->close();
                }
                
                $success_message = "Registration successful! Your account is pending approval by an administrator.";
            } else {
                $error_message = "Registration failed. Please try again.";
            }
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
    <title><?php echo $page_title; ?> | Freetown Football Agency</title>
    <style>
        /* Same styles as login.php */
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
        
        .form-control, .select-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background-color: white;
        }
        
        .select-control {
            cursor: pointer;
        }
        
        .form-control:focus, .select-control:focus {
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
        
        .password-strength {
            margin-top: 8px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        
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
                <li><a href="login.php">Login Portal</a></li>
                <li><a href="signup.php" class="active">Sign Up</a></li>
            </ul>
            <div class="mobile-menu">☰</div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="auth-main-content">
        <div class="container auth-container">
            <!-- Left Side: Introduction -->
            <div class="auth-left">
                <h2>Join Our Community</h2>
                <p>Register with Freetown Football Agency and take the next step in your football career or representation journey.</p>
                
                <h3 style="margin-top: 30px; color: #4a7c59;">Why Join Us?</h3>
                <ul>
                    <li><strong>For Players:</strong> Get discovered by top clubs worldwide</li>
                    <li><strong>For Agents:</strong> Access to exceptional Sierra Leonean talent</li>
                    <li><strong>For Managers:</strong> Find the perfect players for your club</li>
                    <li>Professional contract negotiation</li>
                    <li>Career development guidance</li>
                    <li>International placement opportunities</li>
                </ul>
                
                <p style="margin-top: 30px; font-style: italic;">
                    "Since joining, I've secured contracts for 5 talented players in European leagues."<br>
                    <strong>- Mohamed Bangura, Licensed Agent</strong>
                </p>
            </div>
            
            <!-- Right Side: Signup Form -->
            <div class="auth-right">
                <div class="auth-header">
                    <h2>Create Account</h2>
                    <p>Choose your role and complete your registration</p>
                </div>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" id="signupForm">
                    <div class="form-group">
                        <label for="user_type">I am a: *</label>
                        <select id="user_type" name="user_type" class="select-control" required>
                            <option value="">Select your role</option>
                            <option value="player" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'player') ? 'selected' : ''; ?>>Player</option>
                            <option value="agent" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'agent') ? 'selected' : ''; ?>>Agent</option>
                            <option value="manager" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'manager') ? 'selected' : ''; ?>>Club Manager</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               placeholder="Enter your full name" required
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Choose a username (min. 3 characters)" required
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="Enter your email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               placeholder="Enter your phone number (optional)"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Enter password (min. 8 characters)" required
                               oninput="checkPasswordStrength(this.value)">
                        <div id="password-strength" class="password-strength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Confirm your password" required>
                    </div>
                    
                    <button type="submit" class="auth-btn" id="submitBtn">Create Your Account</button>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
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
        
        // Password strength indicator
        function checkPasswordStrength(password) {
            const indicator = document.getElementById('password-strength');
            
            if (!password) {
                indicator.textContent = '';
                indicator.className = 'password-strength';
                return;
            }
            
            let strength = 0;
            let text = '';
            let className = '';
            
            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Character type checks
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Determine strength level
            if (strength <= 2) {
                text = 'Weak password';
                className = 'strength-weak';
            } else if (strength <= 4) {
                text = 'Medium password';
                className = 'strength-medium';
            } else {
                text = 'Strong password';
                className = 'strength-strong';
            }
            
            indicator.textContent = text;
            indicator.className = 'password-strength ' + className;
        }
        
        // Update button text based on user type
        document.getElementById('user_type').addEventListener('change', function() {
            const submitBtn = document.getElementById('submitBtn');
            const userType = this.value;
            
            if (userType === 'player') {
                submitBtn.textContent = 'Register as Player';
            } else if (userType === 'agent') {
                submitBtn.textContent = 'Register as Agent';
            } else if (userType ===
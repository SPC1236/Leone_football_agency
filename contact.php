<?php
require_once 'config/config.php';

$success_message = '';
$error_message = '';
$page_title = 'Contact Us';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        $conn = getConnection();
        
        // Prepare SQL statement
        $sql = "INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
            
            if ($stmt->execute()) {
                $success_message = "Thank you for your message! We'll get back to you soon.";
                // Clear form after successful submission
                $_POST = [];
            } else {
                $error_message = "Sorry, there was an error sending your message. Please try again. Error: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $error_message = "Database error: " . $conn->error;
        }
        
        $conn->close();
    }
}
include 'includes/header.php';
?>
<head>
    <link rel="stylesheet" href="css/contact.css">
</head>
<body>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content">
            <h1>Get In Touch With Our Team</h1>
            <p>Ready to take your football career to the next level? Contact us today for a consultation with our expert team.</p>
            <div class="hero-btns">
                <a href="#contact-form" class="btn">Send a Message</a>
                <a href="tel:+23276123456" class="btn">Call Us Now</a>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact-form-section" id="contact-form">
        <div class="container">
            <div class="section-title">
                <h2>Contact Us</h2>
                <p>Get in touch with our team for player representation, scouting inquiries, or partnership opportunities</p>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Get In Touch</h3>
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">📍</div>
                            <div class="contact-text">
                                <h4>Our Location</h4>
                                <p>12 Wilkinson Road, Freetown<br>Sierra Leone</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">📞</div>
                            <div class="contact-text">
                                <h4>Phone Number</h4>
                                <p>+232 76 123 456<br>+232 33 987 654</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">✉️</div>
                            <div class="contact-text">
                                <h4>Email Address</h4>
                                <p>info@freetownfa.com<br>players@freetownfa.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">🕒</div>
                            <div class="contact-text">
                                <h4>Working Hours</h4>
                                <p>Monday - Friday: 8:00 AM - 6:00 PM<br>Saturday: 9:00 AM - 1:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email address" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter your phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" class="form-control" required>
                                <option value="">Select a subject</option>
                                <option value="player-representation" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'player-representation') ? 'selected' : ''; ?>>Player Representation</option>
                                <option value="scouting-inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'scouting-inquiry') ? 'selected' : ''; ?>>Scouting Inquiry</option>
                                <option value="partnership" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'partnership') ? 'selected' : ''; ?>>Partnership Opportunity</option>
                                <option value="general" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'general') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Your Message *</label>
                            <textarea id="message" name="message" class="form-control" placeholder="Tell us about your inquiry..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn" style="width: 100%;">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
   <?php
include 'includes/footer.php';
?>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    </script>
</body>
</html>
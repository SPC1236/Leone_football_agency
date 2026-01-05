<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <?php if (isset($page_title)): ?>
        <title><?php echo htmlspecialchars($page_title); ?> | Freetown Football Agency</title>
    <?php else: ?>
        <title>Freetown Football Agency</title>
    <?php endif; ?>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <div class="container header-container">
            <div class="logo">Freetown<span>Football</span>Agency</div>
            <ul class="nav-links">
                <li><a href="index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'class="active"' : ''; ?>>Home</a></li>
                <li><a href="about.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'class="active"' : ''; ?>>About & Services</a></li>
                <li><a href="contact.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'class="active"' : ''; ?>>Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_type'] == 'admin'): ?>
                        <li><a href="admin/index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && basename(dirname($_SERVER['PHP_SELF'])) == 'admin') ? 'class="active"' : ''; ?>>Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="dashboards/<?php echo $_SESSION['user_type']; ?>_dashboard.php" <?php echo (strpos($_SERVER['PHP_SELF'], $_SESSION['user_type'].'_dashboard.php') !== false) ? 'class="active"' : ''; ?>>Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'class="active"' : ''; ?>>Login Portal</a></li>
                    <li><a href="signup.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'signup.php') ? 'class="active"' : ''; ?>>Sign Up</a></li>
                <?php endif; ?>
            </ul>
            <div class="mobile-menu">☰</div>
        </div>
    </header>
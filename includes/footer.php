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
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="<?php echo $_SESSION['user_type'] . '_dashboard.php'; ?>">Dashboard</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login Portal</a></li>
                            <li><a href="signup.php">Sign Up</a></li>
                        <?php endif; ?>
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
        document.querySelector('.mobile-menu')?.addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    </script>
</body>
</html>
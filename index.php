<?php
require_once 'config/config.php';

// Get sample players for display
$conn = getConnection();
$players = [];

$sql = "SELECT * FROM players LIMIT 3";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Add placeholder data for display
        $player = [
            'image_url' => 'images/Hero1.jpeg',
            'position' => $row['position'] ?? 'Footballer',
            'name' => 'Player ' . $row['id'],
            'description' => 'Talented ' . ($row['position'] ?? 'player') . ' from Sierra Leone',
            'nationality' => $row['nationality'] ?? 'Sierra Leonean'
        ];
        $players[] = $player;
    }
}
$conn->close();

$page_title = 'Home';
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Freetown Football Agency | Home</title>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content">
            <h1>Connecting Sierra Leonean Football Talent with Global Opportunities</h1>
            <p>We represent and develop exceptional football players from Freetown and across Sierra Leone, providing pathways to professional careers worldwide.</p>
            <div class="hero-btns">
                <a href="about.php" class="btn">Learn More</a>
                <a href="contact.php" class="btn btn-outline">Get In Touch</a>
            </div>
        </div>
    </section>

    <!-- Key Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-number">100+</div>
                    <div class="stat-text">Players Represented</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">30</div>
                    <div class="stat-text">Nations Connected</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">$50M+</div>
                    <div class="stat-text">In Contracts</div>
                </div>
            </div>
        </div>
    </section>

 <!-- Talent Section -->
<section class="talent">
    <div class="container">
        <div class="section-title">
            <h2>Our Talent</h2>
            <p>Discover some of the exceptional football talents we represent from Sierra Leone</p>
        </div>
        <div class="talent-grid">
            <?php if (!empty($players)): ?>
                <?php 
                $player_count = 1;
                foreach ($players as $player): 
                ?>
                <div class="talent-card">
                    <!-- Dynamically set image path based on player count -->
                    <div class="talent-img" style="background-image: url('<?php echo "images/player" . $player_count . ".jpeg"; ?>')">
                        <div class="talent-overlay">
                            <div class="talent-position"><?php echo htmlspecialchars($player['position']); ?></div>
                            <h3><?php echo htmlspecialchars($player['name']); ?></h3>
                        </div>
                    </div>
                    <div class="talent-content">
                        <p><?php echo htmlspecialchars($player['description']); ?></p>
                        <div class="talent-nationality">
                            <div class="flag"></div>
                            <span><?php echo htmlspecialchars($player['nationality']); ?></span>
                        </div>
                    </div>
                </div>
                <?php 
                $player_count++;
                endforeach; 
                ?>
            <?php else: ?>
                <p>No players to display at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

    <!-- Services Section -->
    <section class="services">
        <div class="container">
            <div class="section-title">
                <h2>Our Services</h2>
                <p>Comprehensive support for footballers at every stage of their career</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">⚽</div>
                    <h3>Player Representation</h3>
                    <p>We negotiate contracts and secure the best opportunities for our players with clubs worldwide.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">📊</div>
                    <h3>Career Development</h3>
                    <p>Strategic planning and guidance to help players reach their full potential on and off the pitch.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🌍</div>
                    <h3>International Placement</h3>
                    <p>Connecting Sierra Leonean talent with opportunities in leagues across Africa, Europe, and beyond.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Client Testimonials</h2>
                <p>What players and clubs say about working with us</p>
            </div>
            <div class="testimonials-container">
                <div class="testimonial">
                    <p class="testimonial-text">Freetown Football Agency transformed my career. They secured my first professional contract in Europe and have supported me every step of the way.</p>
                    <div class="testimonial-author">
                        <div class="author-img">JK</div>
                        <div class="author-info">
                            <h4>John Kamara</h4>
                            <p>Professional Footballer</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <p class="testimonial-text">Working with Freetown Football Agency has been exceptional. Their professionalism and network have helped us discover incredible talent from Sierra Leone.</p>
                    <div class="testimonial-author">
                        <div class="author-img">MS</div>
                        <div class="author-info">
                            <h4>Michael Stevens</h4>
                            <p>Club Scout, European Club</p>
                        </div>
                    </div>
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
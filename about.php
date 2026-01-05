<?php
require_once 'config/config.php';

$team_members = [
    [
        'image_url' => 'images/member1.jpeg',  
        'name' => 'Mohamed Bangura',
        'role' => 'CEO & Founder',
        'description' => 'Former professional footballer with 15+ years of experience in player management.'
    ],
    [
        'image_url' => 'images/member2.jpeg',  
        'name' => 'Fatmata Koroma',
        'role' => 'Head of Scouting',
        'description' => 'Expert in talent identification with extensive network across West Africa.'
    ],
    [
        'image_url' => 'images/member3.jpeg',  
        'name' => 'David Johnson',
        'role' => 'Legal Advisor',
        'description' => 'Specialist in sports law and international contract negotiations.'
    ]
];

$page_title = 'About Us';
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freetown Football Agency | About Us</title>
    <link rel="stylesheet" href="css/about.css">
</head>
<body>
    <!-- Header & Navigation -->
 

    <!-- Mission Section -->
    <section class="mission">
        <div class="container">
            <div class="mission-content">
                <div class="mission-text">
                    <h2>Our Mission & Vision</h2>
                    <p>At Freetown Football Agency, our mission is to identify, develop, and promote exceptional football talent from Sierra Leone, creating pathways to professional careers both locally and internationally.</p>
                    <p>Founded in 2010, we've dedicated ourselves to elevating Sierra Leonean football by providing comprehensive support to players at every stage of their development.</p>
                    <p>We believe in the untapped potential of West African football and are committed to building bridges between local talent and global opportunities.</p>
                    <p>Our vision is to establish Sierra Leone as a recognized hub for football excellence, where young athletes can achieve their dreams while contributing to the development of our nation's sporting culture.</p>
                    <a href="contact.php" class="btn" style="margin-top: 20px;">Get In Touch</a>
                </div>
                <div class="mission-image">
                    Developing Sierra Leone's Football Future
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
<section class="team">
    <div class="container">
        <div class="section-title">
            <h2>Our Team</h2>
            <p>Meet the dedicated professionals behind Freetown Football Agency</p>
        </div>
        <div class="team-grid">
            <?php foreach ($team_members as $member): ?>
            <div class="team-member">
                <div class="member-img" style="background-image: url('<?php echo htmlspecialchars($member['image_url']); ?>')">
                    <!-- Fallback initials if image doesn't load -->
                    <div class="member-initials" style="display: none;">
                        <?php 
                        // Get initials from name
                        $names = explode(' ', $member['name']);
                        $initials = substr($names[0], 0, 1) . substr(end($names), 0, 1);
                        echo htmlspecialchars($initials);
                        ?>
                    </div>
                    <div class="member-overlay">
                        <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                    </div>
                </div>
                <div class="member-info">
                    <div class="member-role"><?php echo htmlspecialchars($member['role']); ?></div>  
                    <p><?php echo htmlspecialchars($member['description']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
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
                    <p>We negotiate contracts and secure the best opportunities for our players with clubs worldwide, ensuring fair terms and career advancement.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">📊</div>
                    <h3>Career Development</h3>
                    <p>Strategic planning and guidance to help players reach their full potential on and off the pitch, including skills assessment and improvement plans.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🌍</div>
                    <h3>International Placement</h3>
                    <p>Connecting Sierra Leonean talent with opportunities in leagues across Africa, Europe, Asia, and the Americas through our global network.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">💼</div>
                    <h3>Contract Negotiation</h3>
                    <p>Expert negotiation to secure favorable terms, protect our players' interests, and ensure fair compensation throughout their careers.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">📈</div>
                    <h3>Career Transition</h3>
                    <p>Supporting players in planning for life after their professional playing careers with education, training, and business opportunities.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🎓</div>
                    <h3>Educational Support</h3>
                    <p>Balancing football development with academic opportunities for young players, including scholarship assistance and tutoring.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Process Diagram -->
    <section class="process">
        <div class="container">
            <div class="section-title">
                <h2>Our Process</h2>
                <p>How we identify, develop, and place football talent</p>
            </div>
            <div class="process-steps">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Identification</h3>
                        <p>Scouting talent across Sierra Leone through our extensive network of local scouts and partnerships with football academies.</p>
                    </div>
                </div>
                <div class="process-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Assessment</h3>
                        <p>Comprehensive evaluation of technical, physical, tactical and mental attributes through trials and performance analysis.</p>
                    </div>
                </div>
                <div class="process-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Development</h3>
                        <p>Personalized training programs, mentorship, and support to enhance skills, fitness, and professional readiness.</p>
                    </div>
                </div>
                <div class="process-step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Placement</h3>
                        <p>Securing opportunities with appropriate clubs and teams that match the player's skills, ambitions, and development needs.</p>
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
    // Handle missing team images
    document.addEventListener('DOMContentLoaded', function() {
        const teamImages = document.querySelectorAll('.member-img');
        
        teamImages.forEach(imgDiv => {
            const bgImage = imgDiv.style.backgroundImage;
            
            // Check if background image is set
            if (!bgImage || bgImage === 'none') {
                // Show the initials fallback
                const initialsDiv = imgDiv.querySelector('.member-initials');
                if (initialsDiv) {
                    initialsDiv.style.display = 'flex';
                    // Add some basic styling to the initials
                    initialsDiv.style.backgroundColor = '#0056b3';
                    initialsDiv.style.color = 'white';
                    initialsDiv.style.width = '100%';
                    initialsDiv.style.height = '100%';
                    initialsDiv.style.justifyContent = 'center';
                    initialsDiv.style.alignItems = 'center';
                    initialsDiv.style.fontSize = '2rem';
                    initialsDiv.style.fontWeight = 'bold';
                }
            } else {
                // Test if the image loads
                const img = new Image();
                const url = bgImage.replace(/url\(['"]?(.*?)['"]?\)/i, '$1');
                
                img.onerror = function() {
                    // Hide the background image
                    imgDiv.style.backgroundImage = 'none';
                    // Show the initials fallback
                    const initialsDiv = imgDiv.querySelector('.member-initials');
                    if (initialsDiv) {
                        initialsDiv.style.display = 'flex';
                        // Add some basic styling to the initials
                        initialsDiv.style.backgroundColor = '#0056b3';
                        initialsDiv.style.color = 'white';
                        initialsDiv.style.width = '100%';
                        initialsDiv.style.height = '100%';
                        initialsDiv.style.justifyContent = 'center';
                        initialsDiv.style.alignItems = 'center';
                        initialsDiv.style.fontSize = '2rem';
                        initialsDiv.style.fontWeight = 'bold';
                    }
                };
                
                img.src = url;
            }
        });
        
        // Mobile menu toggle
        document.querySelector('.mobile-menu').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    });
</script>


</body>
</html>
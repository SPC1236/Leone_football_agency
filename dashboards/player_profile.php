<?php
require_once '../auth/check_auth.php';

// Only allow player access
if ($_SESSION['user_type'] !== 'player') {
    header("Location: ../login.php");
    exit();
}

// Static player data
$player = [
    'full_name' => $_SESSION['full_name'],
    'email' => $_SESSION['email'],
    'position' => 'Forward',
    'age' => 22,
    'height' => '6\'2"',
    'weight' => '185 lbs',
    'nationality' => 'Sierra Leonean',
    'current_club' => 'Freetown FC',
    'preferred_foot' => 'Right',
    'playing_experience' => '5 years',
    'strengths' => ['Speed', 'Finishing', 'Dribbling', 'Positioning'],
    'bio' => 'Talented forward with excellent goal-scoring ability. Strong technical skills and great work ethic. Looking for professional opportunities in Europe.',
    'profile_completion' => 85,
    'profile_views' => 124
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>My Profile | Freetown Football Agency</title>
    <style>
        .profile-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-green));
            color: white;
            padding: 30px;
            border-radius: var(--radius-md);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary-blue);
            font-weight: bold;
            border: 5px solid white;
            box-shadow: var(--shadow-md);
        }
        
        .profile-info h1 {
            margin: 0 0 10px 0;
            color: white;
        }
        
        .profile-stats {
            display: flex;
            gap: 20px;
        }
        
        .stat-item {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .info-card {
            background: var(--white);
            padding: 25px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-section h3 {
            color: var(--primary-blue);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .info-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .info-value {
            color: var(--text-primary);
        }
        
        .strengths-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .strength-tag {
            background: var(--bg-light);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--primary-blue);
            border: 1px solid var(--primary-blue);
        }
        
        .progress-bar {
            height: 10px;
            background: var(--border-color);
            border-radius: 5px;
            margin: 20px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-green), var(--primary-blue));
            border-radius: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn-edit {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-download {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .scouts-section {
            margin-top: 30px;
        }
        
        .scout-card {
            background: var(--bg-light);
            padding: 15px;
            border-radius: var(--radius-sm);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .scout-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="dashboard-sidebar">
            <div class="sidebar-header">
                <h3>Player Dashboard</h3>
                <div class="user-role">Player</div>
            </div>
            <div class="sidebar-menu">
                <a href="player_dashboard.php" class="menu-item">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="player_profile.php" class="menu-item active">
                    <span class="menu-icon">👤</span>
                    <span>My Profile</span>
                </a>
                <a href="player_opportunities.php" class="menu-item">
                    <span class="menu-icon">⚽</span>
                    <span>Opportunities</span>
                </a>
                <a href="player_contracts.php" class="menu-item">
                    <span class="menu-icon">📝</span>
                    <span>Contracts</span>
                </a>
                <a href="player_messages.php" class="menu-item">
                    <span class="menu-icon">💬</span>
                    <span>Messages</span>
                </a>
                <a href="../logout.php" class="menu-item">
                    <span class="menu-icon">🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="dashboard-main">
            <div class="profile-header">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($player['full_name'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h1><?php echo htmlspecialchars($player['full_name']); ?></h1>
                        <p style="opacity: 0.9;">Professional Footballer • <?php echo $player['position']; ?></p>
                    </div>
                </div>
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $player['profile_views']; ?></div>
                        <div>Profile Views</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $player['profile_completion']; ?>%</div>
                        <div>Profile Complete</div>
                    </div>
                </div>
            </div>

            <div class="profile-grid">
                <!-- Left Column -->
                <div>
                    <div class="info-card">
                        <div class="info-section">
                            <h3>Personal Information</h3>
                            <div class="info-row">
                                <div class="info-label">Full Name:</div>
                                <div class="info-value"><?php echo htmlspecialchars($player['full_name']); ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email:</div>
                                <div class="info-value"><?php echo htmlspecialchars($player['email']); ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Age:</div>
                                <div class="info-value"><?php echo $player['age']; ?> years</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Nationality:</div>
                                <div class="info-value"><?php echo $player['nationality']; ?></div>
                            </div>
                        </div>

                        <div class="info-section">
                            <h3>Football Information</h3>
                            <div class="info-row">
                                <div class="info-label">Position:</div>
                                <div class="info-value"><?php echo $player['position']; ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Current Club:</div>
                                <div class="info-value"><?php echo $player['current_club']; ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Height/Weight:</div>
                                <div class="info-value"><?php echo $player['height']; ?> / <?php echo $player['weight']; ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Preferred Foot:</div>
                                <div class="info-value"><?php echo $player['preferred_foot']; ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Experience:</div>
                                <div class="info-value"><?php echo $player['playing_experience']; ?></div>
                            </div>
                        </div>

                        <div class="info-section">
                            <h3>Strengths & Skills</h3>
                            <div class="strengths-grid">
                                <?php foreach ($player['strengths'] as $strength): ?>
                                    <span class="strength-tag"><?php echo $strength; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="info-section">
                            <h3>Bio</h3>
                            <p style="line-height: 1.6; color: var(--text-secondary);">
                                <?php echo $player['bio']; ?>
                            </p>
                        </div>

                        <div class="action-buttons">
                            <button class="btn-edit">
                                <span>✏️</span> Edit Profile
                            </button>
                            <button class="btn-download">
                                <span>📥</span> Download CV
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <div class="info-card">
                        <h3 style="margin-top: 0;">Profile Completion</h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $player['profile_completion']; ?>%;"></div>
                        </div>
                        <p style="text-align: center; font-weight: 600; color: var(--primary-blue);">
                            <?php echo $player['profile_completion']; ?>% Complete
                        </p>
                        
                        <div style="margin-top: 30px;">
                            <h4>To improve your profile:</h4>
                            <ul style="color: var(--text-secondary);">
                                <li>Add video highlights</li>
                                <li>Complete medical information</li>
                                <li>Add references</li>
                                <li>Upload recent match statistics</li>
                            </ul>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3 style="margin-top: 0;">Recent Profile Views</h3>
                        <div class="scouts-section">
                            <div class="scout-card">
                                <div class="scout-avatar">JS</div>
                                <div>
                                    <div style="font-weight: 600;">John Smith</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Scout • Manchester United</div>
                                </div>
                            </div>
                            <div class="scout-card">
                                <div class="scout-avatar">MB</div>
                                <div>
                                    <div style="font-weight: 600;">Maria Brown</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Agent • European Sports</div>
                                </div>
                            </div>
                            <div class="scout-card">
                                <div class="scout-avatar">DC</div>
                                <div>
                                    <div style="font-weight: 600;">David Clark</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Coach • Local Academy</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="player_dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="player_profile.php" class="menu-item active">
                <span class="menu-icon">👤</span>
                <span>Profile</span>
            </a>
            <a href="player_opportunities.php" class="menu-item">
                <span class="menu-icon">⚽</span>
                <span>Opportunities</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>
</body>
</html>
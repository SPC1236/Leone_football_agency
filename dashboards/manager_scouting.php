<?php
require_once '../auth/check_auth.php';

// Only allow manager access
if ($_SESSION['user_type'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

// Static players data for scouting
$players = [
    [
        'id' => 1,
        'name' => 'Alusine Sesay',
        'age' => 18,
        'position' => 'Right Winger',
        'nationality' => 'Sierra Leonean',
        'current_club' => 'Freetown Youth Academy',
        'transfer_status' => 'Available',
        'market_value' => '$150,000',
        'rating' => 85,
        'contract_ends' => '2024-06-30',
        'strengths' => ['Speed', 'Dribbling', 'Crossing'],
        'weaknesses' => ['Defensive Work', 'Physicality'],
        'agent' => 'Freetown Football Agency'
    ],
    [
        'id' => 2,
        'name' => 'John Kamara',
        'age' => 22,
        'position' => 'Central Midfielder',
        'nationality' => 'Sierra Leonean',
        'current_club' => 'East End Lions',
        'transfer_status' => 'Under Contract',
        'market_value' => '$80,000',
        'rating' => 78,
        'contract_ends' => '2024-12-31',
        'strengths' => ['Passing', 'Vision', 'Work Rate'],
        'weaknesses' => ['Pace', 'Aerial Duels'],
        'agent' => 'West African Sports'
    ],
    [
        'id' => 3,
        'name' => 'Fatmata Conteh',
        'age' => 20,
        'position' => 'Center Back',
        'nationality' => 'Sierra Leonean',
        'current_club' => 'Mighty Blackpool',
        'transfer_status' => 'Available',
        'market_value' => '$60,000',
        'rating' => 72,
        'contract_ends' => '2024-08-15',
        'strengths' => ['Tackling', 'Positioning', 'Leadership'],
        'weaknesses' => ['Ball Distribution', 'Speed'],
        'agent' => 'Local Representation'
    ],
    [
        'id' => 4,
        'name' => 'Samuel Koroma',
        'age' => 25,
        'position' => 'Goalkeeper',
        'nationality' => 'Sierra Leonean',
        'current_club' => 'Bo Rangers',
        'transfer_status' => 'Transfer Listed',
        'market_value' => '$100,000',
        'rating' => 80,
        'contract_ends' => '2025-05-30',
        'strengths' => ['Reflexes', 'Command of Area', 'Distribution'],
        'weaknesses' => ['Coming for Crosses'],
        'agent' => 'Professional Agents SL'
    ],
    [
        'id' => 5,
        'name' => 'Ibrahim Mansaray',
        'age' => 19,
        'position' => 'Striker',
        'nationality' => 'Sierra Leonean',
        'current_club' => 'Port Loko FC',
        'transfer_status' => 'Available',
        'market_value' => '$120,000',
        'rating' => 82,
        'contract_ends' => '2024-07-01',
        'strengths' => ['Finishing', 'Movement', 'Strength'],
        'weaknesses' => ['Hold-up Play', 'Link-up'],
        'agent' => 'Freetown Football Agency'
    ]
];

// Filter parameters
$filter_position = $_GET['position'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Apply filters
if ($filter_position !== 'all') {
    $players = array_filter($players, function($player) use ($filter_position) {
        return stripos($player['position'], $filter_position) !== false;
    });
}

if ($filter_status !== 'all') {
    $players = array_filter($players, function($player) use ($filter_status) {
        return $player['transfer_status'] === $filter_status;
    });
}

if ($search) {
    $players = array_filter($players, function($player) use ($search) {
        return stripos($player['name'], $search) !== false || 
               stripos($player['current_club'], $search) !== false ||
               stripos($player['agent'], $search) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Player Scouting | Manager Dashboard</title>
    <style>
        .scouting-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-section {
            background: var(--white);
            padding: 20px;
            border-radius: var(--radius-md);
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
        }
        
        .filter-form {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }
        
        .filter-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .filter-group select,
        .filter-group input {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 1rem;
        }
        
        .btn-filter {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            height: 42px;
        }
        
        .scouting-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            text-align: center;
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }
        
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .player-card {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: 25px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease;
        }
        
        .player-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .player-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .player-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-green));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .transfer-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-available {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-contract {
            background: rgba(0, 123, 255, 0.15);
            color: #004085;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }
        
        .status-listed {
            background: rgba(220, 53, 69, 0.15);
            color: #721c24;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .player-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .rating-bar {
            height: 8px;
            background: var(--border-color);
            border-radius: 4px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .rating-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-green), var(--primary-blue));
            border-radius: 4px;
        }
        
        .skills-section {
            margin: 20px 0;
        }
        
        .skills-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .skill-tag {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
        }
        
        .strength-tag {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .weakness-tag {
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .player-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
        
        .btn-action {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-scout {
            background: var(--primary-blue);
            color: white;
        }
        
        .btn-contact {
            background: var(--accent-green);
            color: white;
        }
        
        .btn-offer {
            background: var(--accent-orange);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-secondary);
            grid-column: 1 / -1;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .scouting-notes {
            margin-top: 30px;
            padding: 20px;
            background: var(--bg-light);
            border-radius: var(--radius-md);
        }
        
        @media (max-width: 768px) {
            .scouting-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                min-width: 100%;
            }
            
            .players-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="dashboard-sidebar">
            <div class="sidebar-header">
                <h3>Manager Dashboard</h3>
                <div class="user-role">Club Manager</div>
            </div>
            <div class="sidebar-menu">
                <a href="manager_dashboard.php" class="menu-item">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="manager_scouting.php" class="menu-item active">
                    <span class="menu-icon">🔍</span>
                    <span>Player Scouting</span>
                </a>
                <a href="manager_contacts.php" class="menu-item">
                    <span class="menu-icon">🤝</span>
                    <span>Agent Contacts</span>
                </a>
                </a>
                <a href="../logout.php" class="menu-item">
                    <span class="menu-icon">🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="dashboard-main">
            <div class="dashboard-header">
                <h1>Player Scouting</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Scouting Statistics -->
            <div class="scouting-stats">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo count($players); ?></div>
                    <div>Players Scouted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-number">$510K</div>
                    <div>Total Market Value</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-number">3</div>
                    <div>Transfer Targets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-number">79.4%</div>
                    <div>Average Rating</div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="scouting-header">
                    <h2 style="margin: 0;">Player Database</h2>
                    <div style="color: var(--text-secondary);">
                        <?php echo count($players); ?> player<?php echo count($players) != 1 ? 's' : ''; ?> found
                    </div>
                </div>
                
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="position">Position</label>
                        <select id="position" name="position">
                            <option value="all" <?php echo $filter_position === 'all' ? 'selected' : ''; ?>>All Positions</option>
                            <option value="Goalkeeper" <?php echo $filter_position === 'Goalkeeper' ? 'selected' : ''; ?>>Goalkeeper</option>
                            <option value="Defender" <?php echo $filter_position === 'Defender' ? 'selected' : ''; ?>>Defender</option>
                            <option value="Midfielder" <?php echo $filter_position === 'Midfielder' ? 'selected' : ''; ?>>Midfielder</option>
                            <option value="Forward" <?php echo $filter_position === 'Forward' ? 'selected' : ''; ?>>Forward</option>
                            <option value="Winger" <?php echo $filter_position === 'Winger' ? 'selected' : ''; ?>>Winger</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Transfer Status</label>
                        <select id="status" name="status">
                            <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="Available" <?php echo $filter_status === 'Available' ? 'selected' : ''; ?>>Available</option>
                            <option value="Under Contract" <?php echo $filter_status === 'Under Contract' ? 'selected' : ''; ?>>Under Contract</option>
                            <option value="Transfer Listed" <?php echo $filter_status === 'Transfer Listed' ? 'selected' : ''; ?>>Transfer Listed</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search">Search Players</label>
                        <input type="text" id="search" name="search" 
                               placeholder="Name, club, or agent..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <span>🔍</span> Apply Filters
                    </button>
                    
                    <?php if ($filter_position !== 'all' || $filter_status !== 'all' || $search): ?>
                        <a href="manager_scouting.php" class="btn-filter" style="background: var(--text-secondary);">
                            <span>✕</span> Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Players Grid -->
            <?php if (empty($players)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🔍</div>
                    <h3>No Players Found</h3>
                    <p>No players match your search criteria. Try adjusting your filters.</p>
                </div>
            <?php else: ?>
                <div class="players-grid">
                    <?php foreach ($players as $player): ?>
                    <div class="player-card">
                        <div class="player-header">
                            <div class="player-avatar">
                                <?php echo strtoupper(substr($player['name'], 0, 1)); ?>
                            </div>
                            <span class="transfer-status status-<?php echo strtolower(str_replace(' ', '_', $player['transfer_status'])); ?>">
                                <?php echo $player['transfer_status']; ?>
                            </span>
                        </div>
                        
                        <h3 style="margin: 0 0 10px 0;"><?php echo $player['name']; ?></h3>
                        <p style="color: var(--text-secondary); margin: 0 0 15px 0;">
                            <?php echo $player['position']; ?> • <?php echo $player['age']; ?> years • <?php echo $player['nationality']; ?>
                        </p>
                        
                        <div class="player-details">
                            <div class="detail-item">
                                <span class="detail-label">Current Club</span>
                                <span class="detail-value"><?php echo $player['current_club']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Market Value</span>
                                <span class="detail-value"><?php echo $player['market_value']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Contract Ends</span>
                                <span class="detail-value"><?php echo date('M Y', strtotime($player['contract_ends'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Agent</span>
                                <span class="detail-value"><?php echo $player['agent']; ?></span>
                            </div>
                        </div>
                        
                        <div style="margin: 20px 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="font-weight: 600;">Scout Rating</span>
                                <span style="font-weight: 600; color: var(--primary-blue);"><?php echo $player['rating']; ?>%</span>
                            </div>
                            <div class="rating-bar">
                                <div class="rating-fill" style="width: <?php echo $player['rating']; ?>%;"></div>
                            </div>
                        </div>
                        
                        <div class="skills-section">
                            <div style="margin-bottom: 10px;">
                                <strong style="color: var(--accent-green);">Strengths:</strong>
                                <div class="skills-grid">
                                    <?php foreach ($player['strengths'] as $strength): ?>
                                        <span class="skill-tag strength-tag"><?php echo $strength; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div>
                                <strong style="color: var(--accent-red);">Areas to Improve:</strong>
                                <div class="skills-grid">
                                    <?php foreach ($player['weaknesses'] as $weakness): ?>
                                        <span class="skill-tag weakness-tag"><?php echo $weakness; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="player-actions">
                            <button class="btn-action btn-scout" onclick="viewPlayer(<?php echo $player['id']; ?>)">
                                <span>🔍</span> Detailed Scout
                            </button>
                            <button class="btn-action btn-contact" onclick="contactAgent(<?php echo $player['id']; ?>)">
                                <span>🤝</span> Contact Agent
                            </button>
                            <button class="btn-action btn-offer" onclick="makeOffer(<?php echo $player['id']; ?>)">
                                <span>💰</span> Make Offer
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Scouting Notes Section -->
            <div class="scouting-notes">
                <h3 style="margin-top: 0;">Scouting Notes & Strategy</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="color: var(--primary-blue);">Top Priority Targets</h4>
                        <ul>
                            <li><strong>Alusine Sesay:</strong> Young winger with high potential. Consider loan with option to buy.</li>
                            <li><strong>Ibrahim Mansaray:</strong> Natural goalscorer, could solve our striker issues.</li>
                            <li><strong>Samuel Koroma:</strong> Experienced goalkeeper for backup role.</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: var(--primary-blue);">Transfer Strategy</h4>
                        <ul>
                            <li>Focus on young Sierra Leonean talent</li>
                            <li>Maximum budget: $200,000 for this window</li>
                            <li>Priority: Winger and Striker positions</li>
                            <li>Consider loan deals for younger players</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="manager_dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="manager_scouting.php" class="menu-item active">
                <span class="menu-icon">🔍</span>
                <span>Scouting</span>
            </a>
            <a href="manager_contracts.php" class="menu-item">
                <span class="menu-icon">📝</span>
                <span>Contracts</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <script>
        function viewPlayer(playerId) {
            alert('Viewing detailed scouting report for player #' + playerId + '\n\nThis would show full player analysis, statistics, video highlights, and medical reports.');
        }
        
        function contactAgent(playerId) {
            const playerName = <?php echo json_encode($players[0]['name'] ?? 'Player'); ?>;
            if (confirm('Contact agent for ' + playerName + '?\n\nThis will open communication with their representative.')) {
                const message = prompt('Enter your initial message to the agent:');
                if (message) {
                    alert('Message sent to agent!\n\nPlayer: ' + playerName + '\nMessage: ' + message);
                }
            }
        }
        
        function makeOffer(playerId) {
            const player = <?php echo json_encode($players[0] ?? []); ?>;
            if (player && playerId === player.id) {
                const amount = prompt('Enter transfer offer amount for ' + player.name + ':\n\nCurrent market value: ' + player.market_value, player.market_value);
                if (amount) {
                    const type = prompt('Offer type:\n1. Permanent Transfer\n2. Loan with Option to Buy\n3. Loan Only\n\nEnter number:', '1');
                    if (type) {
                        const offerTypes = {
                            '1': 'Permanent Transfer',
                            '2': 'Loan with Option to Buy',
                            '3': 'Loan Only'
                        };
                        alert('Transfer offer submitted!\n\nPlayer: ' + player.name + '\nAmount: ' + amount + '\nType: ' + (offerTypes[type] || 'Unknown'));
                    }
                }
            } else {
                alert('Making transfer offer for player #' + playerId + '\n\nThis would open the transfer negotiation interface.');
            }
        }
        
        // New player recommendation
        setTimeout(function() {
            if (Math.random() > 0.5) {
                if (confirm('New Talent Alert!\n\nOur scouts have identified a promising young defender from Kenema. Would you like to add him to your scouting list?')) {
                    alert('Player added to scouting database!\n\nName: Mohamed Kabia\nAge: 19\nPosition: Center Back\nClub: Kenema FC\nEstimated Value: $75,000');
                }
            }
        }, 8000);
    </script>
</body>
</html>
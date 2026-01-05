<?php
require_once '../auth/check_auth.php';

// Only allow agent access
if ($_SESSION['user_type'] !== 'agent') {
    header("Location: ../login.php");
    exit();
}

// Static scouting data
$scouting_list = [
    [
        'id' => 1,
        'name' => 'Alusine Sesay',
        'age' => 18,
        'position' => 'Winger',
        'club' => 'Freetown Youth Academy',
        'potential' => 'A+',
        'rating' => 85,
        'last_seen' => '2024-01-12',
        'status' => 'high_priority',
        'notes' => 'Exceptional speed and dribbling, needs tactical development'
    ],
    [
        'id' => 2,
        'name' => 'Ibrahim Mansaray',
        'age' => 20,
        'position' => 'Defensive Midfielder',
        'club' => 'Makeni FC',
        'potential' => 'A',
        'rating' => 78,
        'last_seen' => '2024-01-08',
        'status' => 'watchlist',
        'notes' => 'Strong physical presence, good passing range'
    ],
    [
        'id' => 3,
        'name' => 'Kadiatu Bangura',
        'age' => 17,
        'position' => 'Forward',
        'club' => 'Girls Football Academy',
        'potential' => 'A+',
        'rating' => 88,
        'last_seen' => '2024-01-05',
        'status' => 'high_priority',
        'notes' => 'Natural goalscorer, excellent movement'
    ],
    [
        'id' => 4,
        'name' => 'Mohamed Turay',
        'age' => 22,
        'position' => 'Center Back',
        'club' => 'Port Loko FC',
        'potential' => 'B+',
        'rating' => 72,
        'last_seen' => '2023-12-20',
        'status' => 'monitoring',
        'notes' => 'Solid defender, limited technical ability'
    ],
    [
        'id' => 5,
        'name' => 'Foday Kamara',
        'age' => 19,
        'position' => 'Goalkeeper',
        'club' => 'Bo Youth Team',
        'potential' => 'A',
        'rating' => 80,
        'last_seen' => '2023-12-15',
        'status' => 'watchlist',
        'notes' => 'Excellent reflexes, needs experience'
    ]
];

$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

if ($search) {
    $scouting_list = array_filter($scouting_list, function($player) use ($search) {
        return stripos($player['name'], $search) !== false || 
               stripos($player['position'], $search) !== false ||
               stripos($player['club'], $search) !== false;
    });
}

if ($filter !== 'all') {
    $scouting_list = array_filter($scouting_list, function($player) use ($filter) {
        return $player['status'] === $filter;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Scouting | Agent Dashboard</title>
    <style>
        .scouting-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .scouting-search {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-input {
            padding: 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            width: 300px;
            font-size: 1rem;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 8px 20px;
            border: none;
            background: var(--bg-light);
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }
        
        .filter-tab.active {
            background: var(--primary-blue);
            color: white;
            box-shadow: var(--shadow-sm);
        }
        
        .scouting-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            opacity: 0.8;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }
        
        .scouting-table {
            background: var(--white);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
        }
        
        .table-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr 1fr;
            padding: 15px 20px;
            background: var(--primary-blue);
            color: white;
            font-weight: 600;
        }
        
        .table-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr 1fr;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            align-items: center;
            transition: background-color 0.2s;
        }
        
        .table-row:hover {
            background-color: var(--bg-light);
        }
        
        .table-row:last-child {
            border-bottom: none;
        }
        
        .player-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .player-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-green));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .potential-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            min-width: 40px;
        }
        
        .potential-a-plus {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .potential-a {
            background: rgba(0, 123, 255, 0.15);
            color: #004085;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }
        
        .potential-b-plus {
            background: rgba(255, 193, 7, 0.15);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .rating-bar {
            height: 8px;
            background: var(--border-color);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .rating-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-green), var(--primary-blue));
            border-radius: 4px;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-high_priority {
            background-color: #dc3545;
            animation: pulse 2s infinite;
        }
        
        .status-watchlist {
            background-color: #ffc107;
        }
        
        .status-monitoring {
            background-color: #17a2b8;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-scout {
            background: var(--primary-blue);
            color: white;
        }
        
        .btn-contact {
            background: var(--accent-green);
            color: white;
        }
        
        .btn-add {
            background: var(--accent-orange);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .scouting-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: flex-end;
        }
        
        .btn-add-player {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 1200px) {
            .table-header, .table-row {
                grid-template-columns: 2fr 1fr 1fr 1fr 1fr 2fr;
            }
            
            .table-header div:nth-child(5),
            .table-row div:nth-child(5) {
                display: none;
            }
        }
        
        @media (max-width: 992px) {
            .table-header, .table-row {
                grid-template-columns: 2fr 1fr 1fr 2fr;
            }
            
            .table-header div:nth-child(4),
            .table-row div:nth-child(4) {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .scouting-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-input {
                width: 100%;
            }
            
            .scouting-table {
                overflow-x: auto;
            }
            
            .table-header, .table-row {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="dashboard-sidebar">
            <div class="sidebar-header">
                <h3>Agent Dashboard</h3>
                <div class="user-role">Agent</div>
            </div>
            <div class="sidebar-menu">
                <a href="agent_dashboard.php" class="menu-item">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="agent_players.php" class="menu-item">
                    <span class="menu-icon">👥</span>
                    <span>My Players</span>
                </a>
                <a href="agent_contracts.php" class="menu-item">
                    <span class="menu-icon">📝</span>
                    <span>Contracts</span>
                </a>
                <a href="agent_scouting.php" class="menu-item active">
                    <span class="menu-icon">🔍</span>
                    <span>Scouting</span>
                </a>
                <a href="agent_messages.php" class="menu-item">
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
            <div class="dashboard-header">
                <h1>Talent Scouting</h1>
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
                    <div class="stat-number"><?php echo count($scouting_list); ?></div>
                    <div>Players in Scouting List</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-number">2</div>
                    <div>High Priority Targets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-number">81%</div>
                    <div>Average Potential Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-number">3</div>
                    <div>Players to Watch</div>
                </div>
            </div>

            <!-- Header with Search/Filter -->
            <div class="scouting-header">
                <h2 style="margin: 0;">Scouting List</h2>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div class="scouting-search">
                        <form method="GET" style="display: flex; gap: 10px; width: 100%;">
                            <input type="text" name="search" class="search-input" placeholder="Search players..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="action-btn" style="background: var(--primary-blue); color: white; padding: 10px 20px;">
                                Search
                            </button>
                            <?php if ($search): ?>
                                <a href="agent_scouting.php" class="action-btn" style="background: var(--text-secondary); color: white; padding: 10px 20px;">
                                    Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div class="filter-tabs">
                        <button class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                                onclick="window.location.href='?filter=all'">
                            All Players
                        </button>
                        <button class="filter-tab <?php echo $filter === 'high_priority' ? 'active' : ''; ?>" 
                                onclick="window.location.href='?filter=high_priority'">
                            High Priority
                        </button>
                        <button class="filter-tab <?php echo $filter === 'watchlist' ? 'active' : ''; ?>" 
                                onclick="window.location.href='?filter=watchlist'">
                            Watchlist
                        </button>
                        <button class="filter-tab <?php echo $filter === 'monitoring' ? 'active' : ''; ?>" 
                                onclick="window.location.href='?filter=monitoring'">
                            Monitoring
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scouting Table -->
            <?php if (empty($scouting_list)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🔍</div>
                    <h3>No Players Found</h3>
                    <p>No players match your search criteria. Try a different search or filter.</p>
                </div>
            <?php else: ?>
                <div class="scouting-table">
                    <div class="table-header">
                        <div>Player</div>
                        <div>Age</div>
                        <div>Position</div>
                        <div>Club</div>
                        <div>Potential</div>
                        <div>Rating</div>
                        <div>Actions</div>
                    </div>
                    
                    <?php foreach ($scouting_list as $player): ?>
                    <div class="table-row">
                        <div class="player-cell">
                            <div class="player-avatar">
                                <?php echo strtoupper(substr($player['name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 600;"><?php echo $player['name']; ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                    <span class="status-indicator status-<?php echo $player['status']; ?>"></span>
                                    <?php echo str_replace('_', ' ', ucfirst($player['status'])); ?>
                                </div>
                            </div>
                        </div>
                        <div><?php echo $player['age']; ?></div>
                        <div><?php echo $player['position']; ?></div>
                        <div><?php echo $player['club']; ?></div>
                        <div>
                            <span class="potential-badge potential-<?php echo strtolower($player['potential']); ?>">
                                <?php echo $player['potential']; ?>
                            </span>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: 600;"><?php echo $player['rating']; ?>%</span>
                                <div class="rating-bar" style="flex: 1;">
                                    <div class="rating-fill" style="width: <?php echo $player['rating']; ?>%;"></div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div style="display: flex; gap: 8px;">
                                <button class="action-btn btn-scout" onclick="viewPlayer(<?php echo $player['id']; ?>)">
                                    <span>👁️</span> Scout
                                </button>
                                <button class="action-btn btn-contact" onclick="contactPlayer(<?php echo $player['id']; ?>)">
                                    <span>✉️</span> Contact
                                </button>
                                <button class="action-btn btn-add" onclick="addToPortfolio(<?php echo $player['id']; ?>)">
                                    <span>➕</span> Add
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Player Notes Section -->
            <div class="content-card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3>Recent Scouting Notes</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; gap: 15px;">
                        <div style="display: flex; gap: 15px; padding: 15px; background: var(--bg-light); border-radius: var(--radius-sm);">
                            <div style="font-size: 1.5rem;">📝</div>
                            <div>
                                <div style="font-weight: 600;">Alusine Sesay - Jan 12, 2024</div>
                                <div style="color: var(--text-secondary);">Exceptional speed and dribbling ability. Needs work on defensive contribution and tactical awareness. Recommended for trial with European academy.</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 15px; padding: 15px; background: var(--bg-light); border-radius: var(--radius-sm);">
                            <div style="font-size: 1.5rem;">🎯</div>
                            <div>
                                <div style="font-weight: 600;">Kadiatu Bangura - Jan 5, 2024</div>
                                <div style="color: var(--text-secondary);">Natural goalscorer with excellent movement. Great first touch and finishing. Recommend immediate contract offer before other agents notice.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Add Player Section -->
            <div class="scouting-actions">
                <button class="btn-add-player" onclick="addNewProspect()">
                    <span>➕</span> Add New Prospect
                </button>
                <button class="btn-add-player" style="background: var(--primary-blue);" onclick="generateScoutingReport()">
                    <span>📊</span> Generate Report
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="agent_dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="agent_players.php" class="menu-item">
                <span class="menu-icon">👥</span>
                <span>Players</span>
            </a>
            <a href="agent_scouting.php" class="menu-item active">
                <span class="menu-icon">🔍</span>
                <span>Scouting</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <script>
        function viewPlayer(playerId) {
            alert('Viewing detailed scouting report for player #' + playerId + '\n\nThis would show full player analysis, statistics, video highlights, and assessment data.');
        }
        
        function contactPlayer(playerId) {
            const player = <?php echo json_encode($scouting_list[0] ?? []); ?>;
            if (player && playerId === player.id) {
                const message = prompt('Enter message to send to ' + player.name + ':');
                if (message) {
                    alert('Message sent to ' + player.name + '!\n\nMessage: ' + message);
                }
            } else {
                alert('Contacting player #' + playerId + '\n\nThis would open a contact form to reach out to the player or their representatives.');
            }
        }
        
        function addToPortfolio(playerId) {
            if (confirm('Add this player to your portfolio?\n\nThis player will be added to your "My Players" list for management.')) {
                alert('Player #' + playerId + ' added to your portfolio!\n\nYou can now manage their career and contracts.');
            }
        }
        
        function addNewProspect() {
            const name = prompt('Enter new prospect name:');
            if (name) {
                const position = prompt('Enter position:');
                if (position) {
                    alert('New prospect "' + name + '" added to scouting list!\n\nPosition: ' + position + '\n\nIn a real system, you would complete a detailed scouting profile.');
                }
            }
        }
        
        function generateScoutingReport() {
            alert('Generating scouting report...\n\nThis would create a PDF report of all players in your scouting list with analysis and recommendations.');
        }
        
        // New talent notification
        setTimeout(function() {
            if (Math.random() > 0.6) {
                if (confirm('New Talent Alert!\n\nA promising young player has been recommended by our scouting network. Would you like to add them to your scouting list?')) {
                    const newPlayer = {
                        name: 'James Cole',
                        age: 16,
                        position: 'Attacking Midfielder',
                        club: 'Youth Academy'
                    };
                    alert('Added ' + newPlayer.name + ' (' + newPlayer.age + ' years, ' + newPlayer.position + ') to your scouting list!');
                }
            }
        }, 8000);
    </script>
</body>
</html>
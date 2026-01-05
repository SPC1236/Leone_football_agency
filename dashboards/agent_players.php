<?php
require_once '../auth/check_auth.php';

// Only allow agent access
if ($_SESSION['user_type'] !== 'agent') {
    header("Location: ../login.php");
    exit();
}

// Static players data
$players = [
    [
        'id' => 1,
        'name' => 'Mohamed Bangura',
        'age' => 21,
        'position' => 'Forward',
        'nationality' => 'Sierra Leonean',
        'current_club' => 'Freetown FC',
        'status' => 'active',
        'contract_end' => '2024-12-31',
        'market_value' => '$150,000',
        'last_assessment' => '2024-01-10',
        'notes' => 'High potential, needs European exposure'
    ],
    [
        'id' => 2,
        'name' => 'John Kamara',
        'age' => 19,
        'position' => 'Midfielder',
        'nationality' => 'Sierra Leonean',
        'current_club' => 'East End Lions',
        'status' => 'negotiating',
        'contract_end' => '2024-06-30',
        'market_value' => '$80,000',
        'last_assessment' => '2024-01-05',
        'notes' => 'Technical skills excellent, physically developing'
    ],
    [
        'id' => 3,
        'name' => 'Fatmata Conteh',
        'age' => 23,
        'position' => 'Defender',
        'nationality' => 'Sierra Leonean',
        'current_club' => 'Mighty Blackpool',
        'status' => 'scouting',
        'contract_end' => '2024-08-15',
        'market_value' => '$60,000',
        'last_assessment' => '2023-12-20',
        'notes' => 'Strong defensive abilities, leadership potential'
    ],
    [
        'id' => 4,
        'name' => 'Samuel Koroma',
        'age' => 25,
        'position' => 'Goalkeeper',
        'nationality' => 'Sierra Leonean',
        'current_club' => 'Bo Rangers',
        'status' => 'inactive',
        'contract_end' => '2025-05-30',
        'market_value' => '$100,000',
        'last_assessment' => '2024-01-08',
        'notes' => 'Experienced, seeking international move'
    ]
];

// Filter players
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

if ($search) {
    $players = array_filter($players, function($player) use ($search) {
        return stripos($player['name'], $search) !== false || 
               stripos($player['position'], $search) !== false ||
               stripos($player['current_club'], $search) !== false;
    });
}

if ($filter !== 'all') {
    $players = array_filter($players, function($player) use ($filter) {
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
    <title>My Players | Agent Dashboard</title>
    <style>
        .players-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .search-filter {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-box {
            padding: 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            width: 300px;
            font-size: 1rem;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            background: var(--bg-light);
            padding: 5px;
            border-radius: var(--radius-sm);
        }
        
        .filter-tab {
            padding: 8px 20px;
            border: none;
            background: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }
        
        .filter-tab.active {
            background: var(--white);
            color: var(--primary-blue);
            box-shadow: var(--shadow-sm);
        }
        
        .players-stats {
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
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }
        
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
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
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-green));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .player-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-negotiating {
            background: rgba(255, 193, 7, 0.15);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .status-scouting {
            background: rgba(0, 123, 255, 0.15);
            color: #004085;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }
        
        .status-inactive {
            background: rgba(108, 117, 125, 0.15);
            color: #383d41;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }
        
        .player-details {
            display: grid;
            gap: 10px;
            margin: 20px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color-light);
        }
        
        .detail-label {
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .detail-value {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .player-notes {
            background: var(--bg-light);
            padding: 15px;
            border-radius: var(--radius-sm);
            margin: 15px 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .player-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
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
        
        .btn-view {
            background: var(--primary-blue);
            color: white;
        }
        
        .btn-contact {
            background: var(--accent-green);
            color: white;
        }
        
        .btn-manage {
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
        
        @media (max-width: 768px) {
            .players-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                width: 100%;
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
                <h3>Agent Dashboard</h3>
                <div class="user-role">Agent</div>
            </div>
            <div class="sidebar-menu">
                <a href="agent_dashboard.php" class="menu-item">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="agent_players.php" class="menu-item active">
                    <span class="menu-icon">👥</span>
                    <span>My Players</span>
                </a>
                <a href="agent_contracts.php" class="menu-item">
                    <span class="menu-icon">📝</span>
                    <span>Contracts</span>
                </a>
                <a href="agent_scouting.php" class="menu-item">
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
                <h1>My Players</h1>
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

            <!-- Statistics -->
            <div class="players-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($players); ?></div>
                    <div>Total Players</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">$390,000</div>
                    <div>Total Market Value</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">1</div>
                    <div>Active Negotiations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">3</div>
                    <div>Upcoming Assessments</div>
                </div>
            </div>

            <!-- Header with Search/Filter -->
            <div class="players-header">
                <h2 style="margin: 0;">Player Portfolio</h2>
                <div class="search-filter">
                    <form method="GET" style="display: flex; gap: 10px;">
                        <input type="text" name="search" class="search-box" placeholder="Search players..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn-action" style="background: var(--primary-blue); color: white; padding: 10px 20px;">
                            Search
                        </button>
                        <?php if ($search): ?>
                            <a href="agent_players.php" class="btn-action" style="background: var(--text-secondary); color: white; padding: 10px 20px;">
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                    
                    <div class="filter-tabs">
                        <button class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                                onclick="window.location.href='?filter=all'">
                            All
                        </button>
                        <button class="filter-tab <?php echo $filter === 'active' ? 'active' : ''; ?>" 
                                onclick="window.location.href='?filter=active'">
                            Active
                        </button>
                        <button class="filter-tab <?php echo $filter === 'negotiating' ? 'active' : ''; ?>" 
                                onclick="window.location.href='?filter=negotiating'">
                            Negotiating
                        </button>
                        <button class="filter-tab <?php echo $filter === 'scouting' ? 'active' : ''; ?>" 
                                onclick="window.location.href='?filter=scouting'">
                            Scouting
                        </button>
                    </div>
                </div>
            </div>

            <!-- Players Grid -->
            <?php if (empty($players)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <h3>No Players Found</h3>
                    <p>No players match your search criteria. Try a different search or filter.</p>
                </div>
            <?php else: ?>
                <div class="players-grid">
                    <?php foreach ($players as $player): ?>
                    <div class="player-card">
                        <div class="player-header">
                            <div class="player-avatar">
                                <?php echo strtoupper(substr($player['name'], 0, 1)); ?>
                            </div>
                            <span class="player-status status-<?php echo $player['status']; ?>">
                                <?php echo ucfirst($player['status']); ?>
                            </span>
                        </div>
                        
                        <h3 style="margin: 0 0 10px 0;"><?php echo $player['name']; ?></h3>
                        <p style="color: var(--text-secondary); margin: 0 0 15px 0;">
                            <?php echo $player['position']; ?> • <?php echo $player['age']; ?> years
                        </p>
                        
                        <div class="player-details">
                            <div class="detail-row">
                                <span class="detail-label">Club:</span>
                                <span class="detail-value"><?php echo $player['current_club']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Nationality:</span>
                                <span class="detail-value"><?php echo $player['nationality']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Market Value:</span>
                                <span class="detail-value"><?php echo $player['market_value']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Contract Ends:</span>
                                <span class="detail-value"><?php echo date('M j, Y', strtotime($player['contract_end'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="player-notes">
                            <strong>Agent Notes:</strong><br>
                            <?php echo $player['notes']; ?>
                        </div>
                        
                        <div class="player-actions">
                            <button class="btn-action btn-view" onclick="viewPlayer(<?php echo $player['id']; ?>)">
                                <span>👁️</span> View
                            </button>
                            <button class="btn-action btn-contact" onclick="contactPlayer(<?php echo $player['id']; ?>)">
                                <span>✉️</span> Contact
                            </button>
                            <button class="btn-action btn-manage" onclick="managePlayer(<?php echo $player['id']; ?>)">
                                <span>⚙️</span> Manage
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Add Player Section -->
            <div class="content-card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3>Add New Player to Portfolio</h3>
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 20px;">Add a new player to your portfolio to start managing their career.</p>
                    <button class="btn-action" style="background: var(--accent-green); color: white; padding: 12px 30px;" onclick="addPlayer()">
                        <span>➕</span> Add New Player
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="agent_dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="agent_players.php" class="menu-item active">
                <span class="menu-icon">👥</span>
                <span>Players</span>
            </a>
            <a href="agent_contracts.php" class="menu-item">
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
            alert('Viewing player #' + playerId + '\n\nThis would open a detailed player profile with statistics, performance data, and career history.');
        }
        
        function contactPlayer(playerId) {
            const message = prompt('Enter message to send to player:');
            if (message) {
                alert('Message sent to player #' + playerId + '!\n\nMessage: ' + message);
            }
        }
        
        function managePlayer(playerId) {
            const action = prompt('Select action:\n1. Update Contract\n2. Schedule Assessment\n3. Update Market Value\n4. Set Transfer Status\n\nEnter number:');
            if (action) {
                alert('Action ' + action + ' initiated for player #' + playerId);
            }
        }
        
        function addPlayer() {
            const name = prompt('Enter player name:');
            if (name) {
                const position = prompt('Enter player position:');
                if (position) {
                    alert('New player "' + name + '" added to your portfolio as a ' + position + '!\n\nIn a real system, you would complete a detailed player profile.');
                }
            }
        }
        
        // Auto-refresh notification
        setTimeout(function() {
            if (Math.random() > 0.7) {
                if (confirm('Player Update!\n\nOne of your players has received a new contract offer. Would you like to review?')) {
                    // Could redirect or show modal
                }
            }
        }, 10000);
    </script>
</body>
</html>
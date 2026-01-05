<?php
require_once '../auth/check_auth.php';

// Only allow player access
if ($_SESSION['user_type'] !== 'player') {
    header("Location: ../login.php");
    exit();
}

// Static opportunities data
$opportunities = [
    [
        'id' => 1,
        'title' => 'European Club Trials',
        'club' => 'FC Barcelona Academy',
        'location' => 'Barcelona, Spain',
        'date' => 'Next Month',
        'type' => 'Trial',
        'status' => 'active',
        'description' => '3-day trial with FC Barcelona academy scouts. Open for talented forwards.',
        'requirements' => ['Age 18-25', 'Professional experience', 'Valid passport']
    ],
    [
        'id' => 2,
        'title' => 'Professional Contract Offer',
        'club' => 'Ajax Cape Town',
        'location' => 'Cape Town, South Africa',
        'date' => 'Immediate',
        'type' => 'Contract',
        'status' => 'active',
        'description' => '1-year professional contract with option to extend. Includes accommodation.',
        'requirements' => ['Medical clearance', 'Agent representation', 'Work permit']
    ],
    [
        'id' => 3,
        'title' => 'Scouting Combine',
        'club' => 'Multiple European Clubs',
        'location' => 'Lisbon, Portugal',
        'date' => '2 weeks',
        'type' => 'Combine',
        'status' => 'upcoming',
        'description' => 'Annual scouting combine with representatives from 20+ European clubs.',
        'requirements' => ['Registration fee', 'Travel arrangements', 'Medical certificate']
    ],
    [
        'id' => 4,
        'title' => 'Local Academy Scholarship',
        'club' => 'Freetown Football Academy',
        'location' => 'Freetown, Sierra Leone',
        'date' => 'Next Season',
        'type' => 'Scholarship',
        'status' => 'pending',
        'description' => 'Full scholarship for advanced training program with professional coaching.',
        'requirements' => ['Age under 23', 'Local residence', 'Academic records']
    ]
];

$filter = $_GET['filter'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Opportunities | Freetown Football Agency</title>
    <style>
        .opportunities-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
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
        
        .filter-tab:hover:not(.active) {
            background: rgba(255,255,255,0.5);
        }
        
        .opportunity-card {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .opportunity-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .opportunity-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .opportunity-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-trial {
            background: rgba(255, 193, 7, 0.15);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .badge-contract {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .badge-combine {
            background: rgba(0, 123, 255, 0.15);
            color: #004085;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }
        
        .badge-scholarship {
            background: rgba(111, 66, 193, 0.15);
            color: #542c85;
            border: 1px solid rgba(111, 66, 193, 0.3);
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
        }
        
        .status-upcoming {
            background: rgba(0, 123, 255, 0.15);
            color: #004085;
        }
        
        .status-pending {
            background: rgba(255, 193, 7, 0.15);
            color: #856404;
        }
        
        .opportunity-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
            padding: 20px;
            background: var(--bg-light);
            border-radius: var(--radius-sm);
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-icon {
            font-size: 1.2rem;
            opacity: 0.7;
        }
        
        .requirements {
            margin-top: 20px;
        }
        
        .requirements h4 {
            margin-bottom: 10px;
            color: var(--text-secondary);
        }
        
        .requirement-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .requirement-tag {
            background: var(--white);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        
        .opportunity-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            justify-content: flex-end;
        }
        
        .btn-apply {
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
        
        .btn-details {
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
        
        @media (max-width: 768px) {
            .opportunity-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .opportunity-actions {
                flex-direction: column;
            }
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
                <a href="player_profile.php" class="menu-item">
                    <span class="menu-icon">👤</span>
                    <span>My Profile</span>
                </a>
                <a href="player_opportunities.php" class="menu-item active">
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
            <div class="dashboard-header">
                <h1>Football Opportunities</h1>
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

            <div class="opportunities-header">
                <h2 style="margin: 0;">Available Opportunities</h2>
                <div class="filter-tabs">
                    <button class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                            onclick="window.location.href='?filter=all'">
                        All (4)
                    </button>
                    <button class="filter-tab <?php echo $filter === 'active' ? 'active' : ''; ?>" 
                            onclick="window.location.href='?filter=active'">
                        Active (2)
                    </button>
                    <button class="filter-tab <?php echo $filter === 'upcoming' ? 'active' : ''; ?>" 
                            onclick="window.location.href='?filter=upcoming'">
                        Upcoming (1)
                    </button>
                    <button class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>" 
                            onclick="window.location.href='?filter=pending'">
                        Pending (1)
                    </button>
                </div>
            </div>

            <?php 
            // Filter opportunities
            $filtered_opportunities = array_filter($opportunities, function($opp) use ($filter) {
                return $filter === 'all' || $opp['status'] === $filter;
            });
            
            if (empty($filtered_opportunities)):
            ?>
                <div class="empty-state">
                    <div class="empty-state-icon">⚽</div>
                    <h3>No Opportunities Found</h3>
                    <p>No opportunities match your current filter. Try a different filter or check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($filtered_opportunities as $opportunity): ?>
                <div class="opportunity-card">
                    <div class="opportunity-header">
                        <div>
                            <h3 style="margin: 0 0 5px 0;"><?php echo $opportunity['title']; ?></h3>
                            <p style="color: var(--text-secondary); margin: 0;">
                                <strong><?php echo $opportunity['club']; ?></strong> • <?php echo $opportunity['location']; ?>
                            </p>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px;">
                            <span class="opportunity-badge badge-<?php echo strtolower($opportunity['type']); ?>">
                                <?php echo $opportunity['type']; ?>
                            </span>
                            <span class="status-badge status-<?php echo $opportunity['status']; ?>">
                                <?php echo ucfirst($opportunity['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <p style="line-height: 1.6; color: var(--text-secondary);">
                        <?php echo $opportunity['description']; ?>
                    </p>
                    
                    <div class="opportunity-info">
                        <div class="info-item">
                            <span class="info-icon">📍</span>
                            <div>
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">Location</div>
                                <div style="font-weight: 600;"><?php echo $opportunity['location']; ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-icon">📅</span>
                            <div>
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">Start Date</div>
                                <div style="font-weight: 600;"><?php echo $opportunity['date']; ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-icon">⚽</span>
                            <div>
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">Type</div>
                                <div style="font-weight: 600;"><?php echo $opportunity['type']; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="requirements">
                        <h4>Requirements:</h4>
                        <div class="requirement-tags">
                            <?php foreach ($opportunity['requirements'] as $requirement): ?>
                                <span class="requirement-tag"><?php echo $requirement; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="opportunity-actions">
                        <button class="btn-details" onclick="showDetails(<?php echo $opportunity['id']; ?>)">
                            <span>🔍</span> View Details
                        </button>
                        <button class="btn-apply" onclick="applyOpportunity(<?php echo $opportunity['id']; ?>)">
                            <span>📝</span> Apply Now
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="player_dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="player_profile.php" class="menu-item">
                <span class="menu-icon">👤</span>
                <span>Profile</span>
            </a>
            <a href="player_opportunities.php" class="menu-item active">
                <span class="menu-icon">⚽</span>
                <span>Opportunities</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <script>
        function showDetails(opportunityId) {
            alert('Showing details for opportunity #' + opportunityId + '\n\nThis feature would show a detailed modal with full information.');
        }
        
        function applyOpportunity(opportunityId) {
            if (confirm('Are you sure you want to apply for this opportunity?\n\nYour profile will be submitted for review.')) {
                alert('Application submitted successfully for opportunity #' + opportunityId + '!\n\nOur team will contact you within 48 hours.');
                // In a real app, this would submit to server
            }
        }
        
        // Simulate new opportunity notification
        setTimeout(function() {
            if (Math.random() > 0.5) {
                if (confirm('New opportunity alert!\n\nScouting camp in Ghana has just been added. Would you like to view it?')) {
                    window.location.href = '?filter=all';
                }
            }
        }, 10000);
    </script>
</body>
</html>
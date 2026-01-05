<?php
require_once '../auth/check_auth.php';

// Only allow player access
if ($_SESSION['user_type'] !== 'player') {
    header("Location: ../login.php");
    exit();
}

// Static contracts data
$contracts = [
    [
        'id' => 1,
        'title' => 'Professional Player Contract',
        'club' => 'Ajax Cape Town',
        'type' => 'Professional',
        'duration' => '1 year',
        'value' => '$50,000',
        'status' => 'pending',
        'date' => '2024-01-15',
        'deadline' => '2024-02-15',
        'description' => 'Full-time professional player contract with monthly salary, bonuses, and accommodation.'
    ],
    [
        'id' => 2,
        'title' => 'Academy Scholarship Contract',
        'club' => 'Freetown Football Academy',
        'type' => 'Scholarship',
        'duration' => '2 years',
        'value' => 'Full Scholarship',
        'status' => 'active',
        'date' => '2023-09-01',
        'deadline' => '2025-08-31',
        'description' => 'Full scholarship covering training, education, and accommodation.'
    ],
    [
        'id' => 3,
        'title' => 'Trial Period Contract',
        'club' => 'FC Barcelona Academy',
        'type' => 'Trial',
        'duration' => '3 months',
        'value' => '€2,000/month',
        'status' => 'expired',
        'date' => '2023-06-01',
        'deadline' => '2023-09-01',
        'description' => '3-month trial period with potential for full contract extension.'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Contracts | Freetown Football Agency</title>
    <style>
        .contracts-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: var(--white);
            padding: 25px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            text-align: center;
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .summary-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }
        
        .contract-card {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease;
        }
        
        .contract-card:hover {
            transform: translateY(-3px);
        }
        
        .contract-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .contract-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: rgba(255, 193, 7, 0.15);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .status-active {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-expired {
            background: rgba(108, 117, 125, 0.15);
            color: #383d41;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }
        
        .contract-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        
        .detail-item {
            background: var(--bg-light);
            padding: 15px;
            border-radius: var(--radius-sm);
        }
        
        .detail-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .contract-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            justify-content: flex-end;
        }
        
        .btn-view {
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
        
        .btn-sign {
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
        
        .btn-negotiate {
            background: var(--accent-orange);
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
        
        .timeline {
            position: relative;
            padding-left: 30px;
            margin: 30px 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-color);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-bottom: 20px;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--primary-blue);
        }
        
        .timeline-date {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }
        
        .timeline-content {
            background: var(--bg-light);
            padding: 15px;
            border-radius: var(--radius-sm);
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
            .contract-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .contract-actions {
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
                <a href="player_opportunities.php" class="menu-item">
                    <span class="menu-icon">⚽</span>
                    <span>Opportunities</span>
                </a>
                <a href="player_contracts.php" class="menu-item active">
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
                <h1>My Contracts</h1>
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

            <!-- Contracts Summary -->
            <div class="contracts-summary">
                <div class="summary-card">
                    <div class="summary-icon">📋</div>
                    <div class="summary-number"><?php echo count($contracts); ?></div>
                    <div>Total Contracts</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">✅</div>
                    <div class="summary-number">1</div>
                    <div>Active Contracts</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">⏳</div>
                    <div class="summary-number">1</div>
                    <div>Pending Review</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">💰</div>
                    <div class="summary-number">$50K</div>
                    <div>Total Value</div>
                </div>
            </div>

            <!-- Contract Timeline -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Contract Timeline</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">Jan 15, 2024</div>
                            <div class="timeline-content">
                                <strong>New Contract Offer</strong><br>
                                Professional contract from Ajax Cape Town
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">Sep 1, 2023</div>
                            <div class="timeline-content">
                                <strong>Contract Started</strong><br>
                                Academy scholarship with Freetown Football Academy
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">Jun 1, 2023</div>
                            <div class="timeline-content">
                                <strong>Trial Period Ended</strong><br>
                                FC Barcelona Academy trial completed
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts List -->
            <h2 style="margin: 30px 0 20px 0;">All Contracts</h2>
            
            <?php if (empty($contracts)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <h3>No Contracts Found</h3>
                    <p>You don't have any contracts yet. Check the Opportunities page for available offers.</p>
                </div>
            <?php else: ?>
                <?php foreach ($contracts as $contract): ?>
                <div class="contract-card">
                    <div class="contract-header">
                        <div>
                            <h3 style="margin: 0 0 10px 0;"><?php echo $contract['title']; ?></h3>
                            <p style="color: var(--text-secondary); margin: 0;">
                                <strong><?php echo $contract['club']; ?></strong> • <?php echo $contract['type']; ?> Contract
                            </p>
                        </div>
                        <span class="contract-status status-<?php echo $contract['status']; ?>">
                            <?php echo ucfirst($contract['status']); ?>
                        </span>
                    </div>
                    
                    <p style="line-height: 1.6; color: var(--text-secondary);">
                        <?php echo $contract['description']; ?>
                    </p>
                    
                    <div class="contract-details">
                        <div class="detail-item">
                            <div class="detail-label">Contract Value</div>
                            <div class="detail-value"><?php echo $contract['value']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Duration</div>
                            <div class="detail-value"><?php echo $contract['duration']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Start Date</div>
                            <div class="detail-value"><?php echo date('F j, Y', strtotime($contract['date'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Deadline</div>
                            <div class="detail-value"><?php echo date('F j, Y', strtotime($contract['deadline'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="contract-actions">
                        <?php if ($contract['status'] === 'pending'): ?>
                            <button class="btn-negotiate" onclick="negotiateContract(<?php echo $contract['id']; ?>)">
                                <span>💬</span> Negotiate Terms
                            </button>
                            <button class="btn-sign" onclick="signContract(<?php echo $contract['id']; ?>)">
                                <span>✍️</span> Sign Contract
                            </button>
                        <?php endif; ?>
                        <button class="btn-view" onclick="viewContract(<?php echo $contract['id']; ?>)">
                            <span>🔍</span> View Details
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
            <a href="player_contracts.php" class="menu-item active">
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
        function viewContract(contractId) {
            alert('Viewing contract #' + contractId + '\n\nThis would open a detailed view of the contract document.');
        }
        
        function signContract(contractId) {
            if (confirm('Are you ready to sign this contract?\n\nPlease review all terms carefully before signing.')) {
                alert('Contract #' + contractId + ' signed successfully!\n\nCongratulations on your new contract!');
                // In a real app, this would update contract status
            }
        }
        
        function negotiateContract(contractId) {
            alert('Negotiation requested for contract #' + contractId + '\n\nYour agent will contact you to discuss terms.');
        }
        
        // Contract reminder
        setTimeout(function() {
            const pendingContracts = <?php echo json_encode(array_filter($contracts, fn($c) => $c['status'] === 'pending')); ?>;
            if (pendingContracts.length > 0) {
                if (confirm('Contract Reminder!\n\nYou have ' + pendingContracts.length + ' pending contract(s) that require your attention. Would you like to review them now?')) {
                    // Stay on page
                }
            }
        }, 5000);
    </script>
</body>
</html>
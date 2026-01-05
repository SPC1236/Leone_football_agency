<?php
require_once '../auth/check_auth.php';

// Only allow agent access
if ($_SESSION['user_type'] !== 'agent') {
    header("Location: ../login.php");
    exit();
}

// Static contracts data
$contracts = [
    [
        'id' => 1,
        'player_name' => 'Mohamed Bangura',
        'club' => 'Ajax Cape Town',
        'type' => 'Professional',
        'status' => 'negotiating',
        'value' => '$50,000/year',
        'duration' => '2 years',
        'start_date' => '2024-02-01',
        'end_date' => '2026-01-31',
        'commission' => '10%',
        'notes' => 'Finalizing salary details and bonuses'
    ],
    [
        'id' => 2,
        'player_name' => 'John Kamara',
        'club' => 'Freetown FC',
        'type' => 'Extension',
        'status' => 'signed',
        'value' => '$25,000/year',
        'duration' => '1 year',
        'start_date' => '2024-01-15',
        'end_date' => '2024-12-31',
        'commission' => '8%',
        'notes' => 'Successfully negotiated 20% salary increase'
    ],
    [
        'id' => 3,
        'player_name' => 'Fatmata Conteh',
        'club' => 'Mighty Blackpool',
        'type' => 'Transfer',
        'status' => 'pending',
        'value' => '$40,000 transfer fee',
        'duration' => '3 years',
        'start_date' => '2024-03-01',
        'end_date' => '2027-02-28',
        'commission' => '12%',
        'notes' => 'Waiting for medical clearance and work permit'
    ],
    [
        'id' => 4,
        'player_name' => 'Samuel Koroma',
        'club' => 'Bo Rangers',
        'type' => 'Loan',
        'status' => 'active',
        'value' => '$15,000/season',
        'duration' => '6 months',
        'start_date' => '2023-09-01',
        'end_date' => '2024-02-29',
        'commission' => '5%',
        'notes' => 'Option to buy at $100,000'
    ]
];

$filter = $_GET['filter'] ?? 'all';
if ($filter !== 'all') {
    $contracts = array_filter($contracts, function($contract) use ($filter) {
        return $contract['status'] === $filter;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Contracts | Agent Dashboard</title>
    <style>
        .contracts-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        
        .contracts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .contract-tabs {
            display: flex;
            gap: 10px;
            background: var(--bg-light);
            padding: 5px;
            border-radius: var(--radius-sm);
        }
        
        .contract-tab {
            padding: 8px 20px;
            border: none;
            background: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }
        
        .contract-tab.active {
            background: var(--white);
            color: var(--primary-blue);
            box-shadow: var(--shadow-sm);
        }
        
        .contracts-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .contract-card {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: 30px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease;
        }
        
        .contract-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .contract-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
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
        
        .status-negotiating {
            background: rgba(255, 193, 7, 0.15);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .status-signed {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-pending {
            background: rgba(0, 123, 255, 0.15);
            color: #004085;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }
        
        .status-active {
            background: rgba(111, 66, 193, 0.15);
            color: #542c85;
            border: 1px solid rgba(111, 66, 193, 0.3);
        }
        
        .contract-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        
        .detail-group {
            background: var(--bg-light);
            padding: 20px;
            border-radius: var(--radius-sm);
        }
        
        .detail-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .detail-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .commission-badge {
            background: linear-gradient(135deg, var(--accent-green), var(--primary-blue));
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .contract-timeline {
            position: relative;
            padding-left: 30px;
            margin: 25px 0;
        }
        
        .contract-timeline::before {
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
            margin-bottom: 15px;
            padding-bottom: 15px;
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
            background: var(--white);
            padding: 12px 15px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
        }
        
        .contract-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            justify-content: flex-end;
        }
        
        .btn-action {
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-view {
            background: var(--primary-blue);
            color: white;
        }
        
        .btn-edit {
            background: var(--accent-orange);
            color: white;
        }
        
        .btn-finalize {
            background: var(--accent-green);
            color: white;
        }
        
        .btn-download {
            background: var(--text-secondary);
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
                <a href="agent_contracts.php" class="menu-item active">
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
                <h1>Contract Management</h1>
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
                    <div class="summary-icon">💰</div>
                    <div class="summary-number">$130K</div>
                    <div>Annual Value</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">🎯</div>
                    <div class="summary-number">$13K</div>
                    <div>Total Commission</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">⚖️</div>
                    <div class="summary-number">1</div>
                    <div>Active Negotiations</div>
                </div>
            </div>

            <!-- Header with Filter -->
            <div class="contracts-header">
                <h2 style="margin: 0;">Contract Portfolio</h2>
                <div class="contract-tabs">
                    <button class="contract-tab <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                            onclick="window.location.href='?filter=all'">
                        All Contracts
                    </button>
                    <button class="contract-tab <?php echo $filter === 'negotiating' ? 'active' : ''; ?>" 
                            onclick="window.location.href='?filter=negotiating'">
                        Negotiating
                    </button>
                    <button class="contract-tab <?php echo $filter === 'signed' ? 'active' : ''; ?>" 
                            onclick="window.location.href='?filter=signed'">
                        Signed
                    </button>
                    <button class="contract-tab <?php echo $filter === 'active' ? 'active' : ''; ?>" 
                            onclick="window.location.href='?filter=active'">
                        Active
                    </button>
                </div>
            </div>

            <!-- Contracts List -->
            <?php if (empty($contracts)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <h3>No Contracts Found</h3>
                    <p>No contracts match your current filter. Try a different filter or create a new contract.</p>
                </div>
            <?php else: ?>
                <div class="contracts-list">
                    <?php foreach ($contracts as $contract): ?>
                    <div class="contract-card">
                        <div class="contract-header">
                            <div>
                                <h3 style="margin: 0 0 10px 0;"><?php echo $contract['player_name']; ?></h3>
                                <p style="color: var(--text-secondary); margin: 0;">
                                    <strong><?php echo $contract['club']; ?></strong> • <?php echo $contract['type']; ?> Contract
                                </p>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                                <span class="contract-status status-<?php echo $contract['status']; ?>">
                                    <?php echo ucfirst($contract['status']); ?>
                                </span>
                                <span class="commission-badge">
                                    <?php echo $contract['commission']; ?> Commission
                                </span>
                            </div>
                        </div>
                        
                        <div class="contract-details">
                            <div class="detail-group">
                                <div class="detail-label">Contract Value</div>
                                <div class="detail-value"><?php echo $contract['value']; ?></div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Duration</div>
                                <div class="detail-value"><?php echo $contract['duration']; ?></div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">Start Date</div>
                                <div class="detail-value"><?php echo date('M j, Y', strtotime($contract['start_date'])); ?></div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">End Date</div>
                                <div class="detail-value"><?php echo date('M j, Y', strtotime($contract['end_date'])); ?></div>
                            </div>
                        </div>
                        
                        <div class="contract-timeline">
                            <div class="timeline-item">
                                <div class="timeline-date">Contract Period</div>
                                <div class="timeline-content">
                                    <?php echo date('M j, Y', strtotime($contract['start_date'])); ?> 
                                    to 
                                    <?php echo date('M j, Y', strtotime($contract['end_date'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: var(--bg-light); padding: 15px; border-radius: var(--radius-sm); margin: 20px 0;">
                            <strong>Agent Notes:</strong><br>
                            <?php echo $contract['notes']; ?>
                        </div>
                        
                        <div class="contract-actions">
                            <button class="btn-action btn-view" onclick="viewContract(<?php echo $contract['id']; ?>)">
                                <span>🔍</span> View Details
                            </button>
                            <button class="btn-action btn-edit" onclick="editContract(<?php echo $contract['id']; ?>)">
                                <span>✏️</span> Edit Terms
                            </button>
                            <?php if ($contract['status'] === 'negotiating'): ?>
                                <button class="btn-action btn-finalize" onclick="finalizeContract(<?php echo $contract['id']; ?>)">
                                    <span>✅</span> Finalize
                                </button>
                            <?php endif; ?>
                            <button class="btn-action btn-download" onclick="downloadContract(<?php echo $contract['id']; ?>)">
                                <span>📥</span> Download
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- New Contract Section -->
            <div class="content-card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3>Create New Contract</h3>
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 20px;">Start a new contract negotiation for one of your players.</p>
                    <button class="btn-action" style="background: var(--accent-green); color: white; padding: 12px 30px;" onclick="newContract()">
                        <span>➕</span> Create New Contract
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
            <a href="agent_players.php" class="menu-item">
                <span class="menu-icon">👥</span>
                <span>Players</span>
            </a>
            <a href="agent_contracts.php" class="menu-item active">
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
            alert('Viewing contract #' + contractId + '\n\nThis would show the full contract document with all clauses and terms.');
        }
        
        function editContract(contractId) {
            const changes = prompt('Enter contract changes to propose:');
            if (changes) {
                alert('Contract changes proposed for contract #' + contractId + '!\n\nChanges: ' + changes + '\n\nThese will be sent to the club for review.');
            }
        }
        
        function finalizeContract(contractId) {
            if (confirm('Are you ready to finalize this contract?\n\nThis will mark the contract as signed and activate all terms.')) {
                alert('Contract #' + contractId + ' finalized successfully!\n\nCommission will be processed upon contract activation.');
            }
        }
        
        function downloadContract(contractId) {
            alert('Downloading contract #' + contractId + '\n\nThis would download a PDF version of the contract document.');
        }
        
        function newContract() {
            const player = prompt('Enter player name for new contract:');
            if (player) {
                const club = prompt('Enter club name:');
                if (club) {
                    alert('New contract negotiation started for ' + player + ' with ' + club + '!\n\nIn a real system, you would complete contract details.');
                }
            }
        }
        
        // Contract deadline reminder
        setTimeout(function() {
            const expiringContracts = <?php echo json_encode(array_filter($contracts, function($c) {
                $daysLeft = (strtotime($c['end_date']) - time()) / (60 * 60 * 24);
                return $daysLeft < 60 && $daysLeft > 0;
            })); ?>;
            
            if (expiringContracts.length > 0) {
                if (confirm('Contract Alert!\n\nYou have ' + expiringContracts.length + ' contract(s) expiring soon. Would you like to review renewal options?')) {
                    // Could show modal with expiring contracts
                }
            }
        }, 5000);
    </script>
</body>
</html>
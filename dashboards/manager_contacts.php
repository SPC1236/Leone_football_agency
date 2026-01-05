<?php
require_once '../auth/check_auth.php';

// Only allow manager access
if ($_SESSION['user_type'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

// Static agent contacts data
$agents = [
    [
        'id' => 1,
        'name' => 'John Smith',
        'agency' => 'Freetown Football Agency',
        'email' => 'john.smith@ffa.com',
        'phone' => '+232 76 123 456',
        'specialization' => 'Youth Development',
        'players_represented' => 25,
        'rating' => 4.8,
        'last_contact' => '2024-01-15',
        'status' => 'active',
        'notes' => 'Primary contact for youth recruitment in Sierra Leone. Very responsive and professional.'
    ],
    [
        'id' => 2,
        'name' => 'Maria Johnson',
        'agency' => 'West African Sports',
        'email' => 'maria@wasports.com',
        'phone' => '+232 77 234 567',
        'specialization' => 'Professional Transfers',
        'players_represented' => 15,
        'rating' => 4.5,
        'last_contact' => '2024-01-10',
        'status' => 'active',
        'notes' => 'Strong connections with European clubs. Handles complex international transfers.'
    ],
    [
        'id' => 3,
        'name' => 'David Kamara',
        'agency' => 'Professional Agents SL',
        'email' => 'david@proagents.sl',
        'phone' => '+232 88 345 678',
        'specialization' => 'Local Talent',
        'players_represented' => 8,
        'rating' => 4.2,
        'last_contact' => '2023-12-20',
        'status' => 'inactive',
        'notes' => 'Focuses on grassroots talent. Good for discovering hidden gems in local leagues.'
    ],
    [
        'id' => 4,
        'name' => 'Michael Brown',
        'agency' => 'European Sports Network',
        'email' => 'm.brown@esn.eu',
        'phone' => '+44 20 7123 4567',
        'specialization' => 'International Transfers',
        'players_represented' => 40,
        'rating' => 4.9,
        'last_contact' => '2023-11-05',
        'status' => 'active',
        'notes' => 'Based in London. Excellent for European market access. High commission but worth it.'
    ],
    [
        'id' => 5,
        'name' => 'Sarah Conteh',
        'agency' => 'Sierra Sports Management',
        'email' => 'sarah@ssm.sl',
        'phone' => '+232 76 987 654',
        'specialization' => 'Women\'s Football',
        'players_represented' => 12,
        'rating' => 4.7,
        'last_contact' => '2024-01-05',
        'status' => 'new',
        'notes' => 'Specializes in women\'s football talent. Growing network in Africa and Europe.'
    ]
];

// Filter parameters
$filter_specialization = $_GET['specialization'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Apply filters
if ($filter_specialization !== 'all') {
    $agents = array_filter($agents, function($agent) use ($filter_specialization) {
        return stripos($agent['specialization'], $filter_specialization) !== false;
    });
}

if ($filter_status !== 'all') {
    $agents = array_filter($agents, function($agent) use ($filter_status) {
        return $agent['status'] === $filter_status;
    });
}

if ($search) {
    $agents = array_filter($agents, function($agent) use ($search) {
        return stripos($agent['name'], $search) !== false || 
               stripos($agent['agency'], $search) !== false ||
               stripos($agent['email'], $search) !== false;
    });
}

// Calculate statistics
$total_agents = count($agents);
$active_agents = count(array_filter($agents, fn($a) => $a['status'] === 'active'));
$total_players = array_sum(array_column($agents, 'players_represented'));
$avg_rating = round(array_sum(array_column($agents, 'rating')) / max(1, $total_agents), 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Agent Contacts | Manager Dashboard</title>
    <style>
        .contacts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-section {
            background: var(--white);
            padding: 25px;
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
        
        .contacts-summary {
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
            font-size: 2.2rem;
            font-weight: bold;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }
        
        .contacts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }
        
        .contact-card {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: 30px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .contact-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .contact-avatar {
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
        
        .contact-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-inactive {
            background: rgba(108, 117, 125, 0.15);
            color: #383d41;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }
        
        .status-new {
            background: rgba(0, 123, 255, 0.15);
            color: #004085;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }
        
        .contact-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 25px 0;
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
        
        .rating-display {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }
        
        .stars {
            display: flex;
            gap: 3px;
        }
        
        .star {
            color: #ffc107;
            font-size: 1.2rem;
        }
        
        .rating-text {
            font-weight: 600;
            color: var(--primary-blue);
        }
        
        .agent-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
            padding: 20px;
            background: var(--bg-light);
            border-radius: var(--radius-sm);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .contact-notes {
            background: var(--bg-light);
            padding: 15px;
            border-radius: var(--radius-sm);
            margin: 20px 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .last-contact {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 10px 0;
        }
        
        .contact-actions {
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
        
        .btn-call {
            background: var(--accent-green);
            color: white;
        }
        
        .btn-email {
            background: var(--primary-blue);
            color: white;
        }
        
        .btn-meeting {
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
        
        .quick-contacts {
            margin-top: 40px;
            padding: 25px;
            background: var(--bg-light);
            border-radius: var(--radius-md);
        }
        
        @media (max-width: 768px) {
            .contacts-header {
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
            
            .contacts-grid {
                grid-template-columns: 1fr;
            }
            
            .contact-details,
            .agent-stats {
                grid-template-columns: 1fr;
            }
            
            .contact-actions {
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
                <h3>Manager Dashboard</h3>
                <div class="user-role">Club Manager</div>
            </div>
            <div class="sidebar-menu">
                <a href="manager_dashboard.php" class="menu-item">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="manager_scouting.php" class="menu-item">
                    <span class="menu-icon">🔍</span>
                    <span>Player Scouting</span>
                </a>
                <a href="manager_contacts.php" class="menu-item active">
                    <span class="menu-icon">🤝</span>
                    <span>Agent Contacts</span>
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
                <h1>Agent Contacts</h1>
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

            <!-- Contacts Summary -->
            <div class="contacts-summary">
                <div class="summary-card">
                    <div class="summary-icon">🤝</div>
                    <div class="summary-number"><?php echo $total_agents; ?></div>
                    <div>Total Agents</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">✅</div>
                    <div class="summary-number"><?php echo $active_agents; ?></div>
                    <div>Active Contacts</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">👥</div>
                    <div class="summary-number"><?php echo $total_players; ?></div>
                    <div>Players Represented</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">⭐</div>
                    <div class="summary-number"><?php echo $avg_rating; ?>/5</div>
                    <div>Average Rating</div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="contacts-header">
                    <h2 style="margin: 0;">Agent Network</h2>
                    <div style="color: var(--text-secondary);">
                        <?php echo count($agents); ?> contact<?php echo count($agents) != 1 ? 's' : ''; ?> found
                    </div>
                </div>
                
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="specialization">Specialization</label>
                        <select id="specialization" name="specialization">
                            <option value="all" <?php echo $filter_specialization === 'all' ? 'selected' : ''; ?>>All Specializations</option>
                            <option value="Youth" <?php echo $filter_specialization === 'Youth' ? 'selected' : ''; ?>>Youth Development</option>
                            <option value="Professional" <?php echo $filter_specialization === 'Professional' ? 'selected' : ''; ?>>Professional Transfers</option>
                            <option value="Local" <?php echo $filter_specialization === 'Local' ? 'selected' : ''; ?>>Local Talent</option>
                            <option value="International" <?php echo $filter_specialization === 'International' ? 'selected' : ''; ?>>International Transfers</option>
                            <option value="Women" <?php echo $filter_specialization === 'Women' ? 'selected' : ''; ?>>Women's Football</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $filter_status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="new" <?php echo $filter_status === 'new' ? 'selected' : ''; ?>>New</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search">Search Contacts</label>
                        <input type="text" id="search" name="search" 
                               placeholder="Name, agency, or email..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <span>🔍</span> Apply Filters
                    </button>
                    
                    <?php if ($filter_specialization !== 'all' || $filter_status !== 'all' || $search): ?>
                        <a href="manager_contacts.php" class="btn-filter" style="background: var(--text-secondary);">
                            <span>✕</span> Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Contacts Grid -->
            <?php if (empty($agents)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🤝</div>
                    <h3>No Contacts Found</h3>
                    <p>No agent contacts match your search criteria. Try adjusting your filters.</p>
                </div>
            <?php else: ?>
                <div class="contacts-grid">
                    <?php foreach ($agents as $agent): ?>
                    <div class="contact-card">
                        <div class="contact-header">
                            <div class="contact-avatar">
                                <?php echo strtoupper(substr($agent['name'], 0, 1)); ?>
                            </div>
                            <span class="contact-status status-<?php echo $agent['status']; ?>">
                                <?php echo ucfirst($agent['status']); ?>
                            </span>
                        </div>
                        
                        <h3 style="margin: 0 0 5px 0;"><?php echo $agent['name']; ?></h3>
                        <p style="color: var(--text-secondary); margin: 0 0 20px 0;">
                            <strong><?php echo $agent['agency']; ?></strong> • <?php echo $agent['specialization']; ?>
                        </p>
                        
                        <div class="rating-display">
                            <div class="stars">
                                <?php
                                $fullStars = floor($agent['rating']);
                                $hasHalfStar = ($agent['rating'] - $fullStars) >= 0.5;
                                
                                for ($i = 1; $i <= 5; $i++):
                                    if ($i <= $fullStars): ?>
                                        <span class="star">★</span>
                                    <?php elseif ($i == $fullStars + 1 && $hasHalfStar): ?>
                                        <span class="star">⭐</span>
                                    <?php else: ?>
                                        <span class="star" style="color: #ddd;">☆</span>
                                    <?php endif;
                                endfor;
                                ?>
                            </div>
                            <div class="rating-text"><?php echo $agent['rating']; ?>/5</div>
                        </div>
                        
                        <div class="contact-details">
                            <div class="detail-item">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?php echo $agent['email']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Phone</span>
                                <span class="detail-value"><?php echo $agent['phone']; ?></span>
                            </div>
                        </div>
                        
                        <div class="agent-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $agent['players_represented']; ?></div>
                                <div class="stat-label">Players</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo round($agent['rating'], 1); ?>/5</div>
                                <div class="stat-label">Rating</div>
                            </div>
                        </div>
                        
                        <div class="last-contact">
                            <strong>Last Contact:</strong> <?php echo date('F j, Y', strtotime($agent['last_contact'])); ?>
                            (<?php echo round((time() - strtotime($agent['last_contact'])) / (60 * 60 * 24)); ?> days ago)
                        </div>
                        
                        <div class="contact-notes">
                            <strong>Notes:</strong><br>
                            <?php echo $agent['notes']; ?>
                        </div>
                        
                        <div class="contact-actions">
                            <button class="btn-action btn-call" onclick="callAgent(<?php echo $agent['id']; ?>)">
                                <span>📞</span> Call
                            </button>
                            <button class="btn-action btn-email" onclick="emailAgent(<?php echo $agent['id']; ?>)">
                                <span>✉️</span> Email
                            </button>
                            <button class="btn-action btn-meeting" onclick="scheduleMeeting(<?php echo $agent['id']; ?>)">
                                <span>📅</span> Meeting
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Quick Contacts Section -->
            <div class="quick-contacts">
                <h3 style="margin-top: 0;">Quick Contact Tools</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div style="padding: 20px; background: white; border-radius: var(--radius-sm);">
                        <h4 style="margin: 0 0 15px 0; color: var(--primary-blue);">Bulk Email</h4>
                        <p style="color: var(--text-secondary); margin-bottom: 15px;">Send an email to multiple agents at once.</p>
                        <button class="btn-action" style="background: var(--primary-blue); color: white; width: 100%;" onclick="bulkEmail()">
                            <span>📧</span> Compose Bulk Email
                        </button>
                    </div>
                    
                    <div style="padding: 20px; background: white; border-radius: var(--radius-sm);">
                        <h4 style="margin: 0 0 15px 0; color: var(--primary-blue);">Contact Groups</h4>
                        <p style="color: var(--text-secondary); margin-bottom: 15px;">Manage your agent contact groups.</p>
                        <button class="btn-action" style="background: var(--accent-green); color: white; width: 100%;" onclick="manageGroups()">
                            <span>👥</span> Manage Groups
                        </button>
                    </div>
                    
                    <div style="padding: 20px; background: white; border-radius: var(--radius-sm);">
                        <h4 style="margin: 0 0 15px 0; color: var(--primary-blue);">Import Contacts</h4>
                        <p style="color: var(--text-secondary); margin-bottom: 15px;">Import new agent contacts from file.</p>
                        <button class="btn-action" style="background: var(--accent-orange); color: white; width: 100%;" onclick="importContacts()">
                            <span>📥</span> Import Contacts
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Add Contact Section -->
            <div style="margin-top: 30px; text-align: center;">
                <button class="btn-action" style="background: var(--accent-green); color: white; padding: 15px 40px; font-size: 1.1rem;" onclick="addNewContact()">
                    <span>➕</span> Add New Agent Contact
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="manager_dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="manager_scouting.php" class="menu-item">
                <span class="menu-icon">🔍</span>
                <span>Scouting</span>
            </a>
            <a href="manager_contacts.php" class="menu-item active">
                <span class="menu-icon">🤝</span>
                <span>Contacts</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <script>
        function callAgent(agentId) {
            const agent = <?php echo json_encode($agents[0] ?? []); ?>;
            if (agent && agentId === agent.id) {
                if (confirm('Call ' + agent.name + ' at ' + agent.phone + '?')) {
                    alert('Calling ' + agent.name + '...\n\nPhone: ' + agent.phone + '\n\nIn a real system, this would initiate the call.');
                }
            } else {
                alert('Calling agent #' + agentId + '...\n\nThis would initiate a phone call to the agent.');
            }
        }
        
        function emailAgent(agentId) {
            const agent = <?php echo json_encode($agents[0] ?? []); ?>;
            if (agent && agentId === agent.id) {
                const subject = prompt('Email subject for ' + agent.name + ':');
                if (subject) {
                    const body = prompt('Email message:');
                    if (body) {
                        alert('Email ready to send!\n\nTo: ' + agent.email + '\nSubject: ' + subject + '\n\nMessage: ' + body);
                    }
                }
            } else {
                alert('Opening email composer for agent #' + agentId);
            }
        }
        
        function scheduleMeeting(agentId) {
            const agent = <?php echo json_encode($agents[0] ?? []); ?>;
            if (agent && agentId === agent.id) {
                const date = prompt('Enter meeting date (YYYY-MM-DD):', '2024-01-30');
                if (date) {
                    const time = prompt('Enter meeting time (HH:MM):', '14:00');
                    if (time) {
                        const purpose = prompt('Meeting purpose:');
                        if (purpose) {
                            alert('Meeting scheduled!\n\nWith: ' + agent.name + '\nDate: ' + date + '\nTime: ' + time + '\nPurpose: ' + purpose);
                        }
                    }
                }
            } else {
                alert('Scheduling meeting with agent #' + agentId);
            }
        }
        
        function addNewContact() {
            const name = prompt('Enter agent name:');
            if (name) {
                const agency = prompt('Enter agency name:');
                if (agency) {
                    const email = prompt('Enter email address:');
                    if (email) {
                        alert('New agent contact added!\n\nName: ' + name + '\nAgency: ' + agency + '\nEmail: ' + email + '\n\nContact details saved to database.');
                    }
                }
            }
        }
        
        function bulkEmail() {
            const subject = prompt('Enter email subject for all agents:');
            if (subject) {
                const message = prompt('Enter email message:');
                if (message) {
                    alert('Bulk email composed!\n\nSubject: ' + subject + '\n\nMessage: ' + message + '\n\nReady to send to all active agents.');
                }
            }
        }
        
        function manageGroups() {
            alert('Opening contact group management...\n\nHere you can create groups like:\n- Youth Agents\n- European Agents\n- Local Scouts\n- Women\'s Football Agents');
        }
        
        function importContacts() {
            alert('Opening contact import tool...\n\nSupported formats:\n- CSV files\n- vCard (.vcf)\n- Excel spreadsheets\n- Manual entry');
        }
        
        // Contact reminder
        setTimeout(function() {
            const oldContacts = <?php echo json_encode(array_filter($agents, function($a) {
                $daysSinceContact = (time() - strtotime($a['last_contact'])) / (60 * 60 * 24);
                return $daysSinceContact > 30 && $a['status'] === 'active';
            })); ?>;
            
            if (oldContacts.length > 0) {
                if (confirm('Contact Reminder!\n\nYou have ' + oldContacts.length + ' agent(s) you haven\'t contacted in over 30 days. Would you like to reach out?')) {
                    // Could show modal with old contacts
                }
            }
        }, 5000);
    </script>
</body>
</html>
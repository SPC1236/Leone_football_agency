<?php
require_once '../config/config.php';
require_once '../auth/check_auth.php';

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database connection
$conn = getConnection();

// First, let's check what columns exist in the agents table
$columns_result = $conn->query("SHOW COLUMNS FROM agents");
$agent_columns = [];
while ($col = $columns_result->fetch_assoc()) {
    $agent_columns[] = $col['Field'];
}

// Determine which status columns exist
$has_is_approved = in_array('is_approved', $agent_columns);
$has_is_suspended = in_array('is_suspended', $agent_columns);
$has_status = in_array('status', $agent_columns);

// Handle actions (approve, reject, delete)
if (isset($_GET['action'])) {
    $agent_id = $_GET['id'] ?? 0;
    $action = $_GET['action'];
    
    if ($agent_id > 0) {
        switch ($action) {
            case 'approve':
                if ($has_is_approved) {
                    $stmt = $conn->prepare("UPDATE agents SET is_approved = 1 WHERE id = ?");
                } elseif ($has_status) {
                    $stmt = $conn->prepare("UPDATE agents SET status = 'approved' WHERE id = ?");
                } else {
                    // If no status column, just set a flag
                    $stmt = $conn->prepare("UPDATE agents SET approved = 1 WHERE id = ?");
                }
                $stmt->bind_param("i", $agent_id);
                $stmt->execute();
                $message = "Agent approved successfully!";
                break;
                
            case 'reject':
                if ($has_is_approved) {
                    $stmt = $conn->prepare("UPDATE agents SET is_approved = 0 WHERE id = ?");
                } elseif ($has_status) {
                    $stmt = $conn->prepare("UPDATE agents SET status = 'rejected' WHERE id = ?");
                } else {
                    $stmt = $conn->prepare("UPDATE agents SET approved = 0 WHERE id = ?");
                }
                $stmt->bind_param("i", $agent_id);
                $stmt->execute();
                $message = "Agent rejected!";
                break;
                
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM agents WHERE id = ?");
                $stmt->bind_param("i", $agent_id);
                $stmt->execute();
                $message = "Agent deleted successfully!";
                break;
                
            case 'view':
                // This will be handled in the modal
                break;
        }
        
        // Redirect to avoid form resubmission
        header("Location: manage_agents.php?message=" . urlencode($message));
        exit();
    }
}

// Get filter values
$filter_status = $_GET['status'] ?? 'all';
$search_query = $_GET['search'] ?? '';

// Build the SQL query with filters
$sql = "SELECT a.*, u.full_name, u.email 
        FROM agents a 
        JOIN users u ON a.user_id = u.id 
        WHERE 1=1";

$params = [];
$types = "";

// Apply status filter based on available columns
if ($filter_status !== 'all') {
    if ($filter_status === 'approved') {
        if ($has_is_approved) {
            $sql .= " AND a.is_approved = 1";
        } elseif ($has_status) {
            $sql .= " AND a.status = 'approved'";
        } elseif (in_array('approved', $agent_columns)) {
            $sql .= " AND a.approved = 1";
        }
    } elseif ($filter_status === 'pending') {
        if ($has_is_approved) {
            $sql .= " AND a.is_approved = 0";
        } elseif ($has_status) {
            $sql .= " AND a.status = 'pending'";
        } elseif (in_array('approved', $agent_columns)) {
            $sql .= " AND a.approved = 0";
        }
    } elseif ($filter_status === 'suspended') {
        if ($has_is_suspended) {
            $sql .= " AND a.is_suspended = 1";
        } elseif ($has_status) {
            $sql .= " AND a.status = 'suspended'";
        }
    }
}

// Apply search filter
if (!empty($search_query)) {
    $sql .= " AND (a.license_number LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR a.company_name LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params = array_fill(0, 4, $search_param);
    $types = "ssss";
}

// Complete the query
$sql .= " ORDER BY a.id DESC";

// Prepare and execute query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$agents = $result->fetch_all(MYSQLI_ASSOC);

// Get counts for statistics based on available columns
$total_agents = $conn->query("SELECT COUNT(*) as count FROM agents")->fetch_assoc()['count'];

if ($has_is_approved) {
    $approved_agents = $conn->query("SELECT COUNT(*) as count FROM agents WHERE is_approved = 1")->fetch_assoc()['count'];
    $pending_agents = $conn->query("SELECT COUNT(*) as count FROM agents WHERE is_approved = 0")->fetch_assoc()['count'];
} elseif ($has_status) {
    $approved_agents = $conn->query("SELECT COUNT(*) as count FROM agents WHERE status = 'approved'")->fetch_assoc()['count'];
    $pending_agents = $conn->query("SELECT COUNT(*) as count FROM agents WHERE status = 'pending'")->fetch_assoc()['count'];
} elseif (in_array('approved', $agent_columns)) {
    $approved_agents = $conn->query("SELECT COUNT(*) as count FROM agents WHERE approved = 1")->fetch_assoc()['count'];
    $pending_agents = $conn->query("SELECT COUNT(*) as count FROM agents WHERE approved = 0")->fetch_assoc()['count'];
} else {
    $approved_agents = 0;
    $pending_agents = $total_agents;
}

if ($has_is_suspended) {
    $suspended_agents = $conn->query("SELECT COUNT(*) as count FROM agents WHERE is_suspended = 1")->fetch_assoc()['count'];
} elseif ($has_status) {
    $suspended_agents = $conn->query("SELECT COUNT(*) as count FROM agents WHERE status = 'suspended'")->fetch_assoc()['count'];
} else {
    $suspended_agents = 0;
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Manage Agents | Admin Dashboard</title>
    <style>
        /* Additional styles for agent management */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }
        
        .filter-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn-filter {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-filter:hover {
            background: #0056b3;
        }
        
        .agent-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .agent-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        
        .btn-delete {
            background: #6c757d;
            color: white;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-suspended {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 80%;
            max-width: 700px;
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .agent-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            display: block;
        }
        
        .detail-value {
            color: #666;
        }
        
        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
        }
        
        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="dashboard-sidebar">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
                <div class="user-role">Administrator</div>
            </div>
            
            <div class="sidebar-menu">
                <a href="index.php" class="menu-item">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="approve_users.php" class="menu-item">
                    <span class="menu-icon">👥</span>
                    <span>Approve Users</span>
                </a>
                <a href="manage_players.php" class="menu-item">
                    <span class="menu-icon">⚽</span>
                    <span>Manage Players</span>
                </a>
                <a href="manage_agents.php" class="menu-item active">
                    <span class="menu-icon">🤝</span>
                    <span>Manage Agents</span>
                </a>
                <a href="manage_managers.php" class="menu-item">
                    <span class="menu-icon">🏢</span>
                    <span>Manage Managers</span>
                </a>
                <a href="reports.php" class="menu-item">
                    <span class="menu-icon">📈</span>
                    <span>Reports</span>
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
                <h1>Manage Agents</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Display message if any -->
            <?php if (isset($_GET['message'])): ?>
                <div class="message message-success">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="agent-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_agents; ?></div>
                    <div class="stat-label">Total Agents</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $approved_agents; ?></div>
                    <div class="stat-label">Approved Agents</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pending_agents; ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $suspended_agents; ?></div>
                    <div class="stat-label">Suspended</div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="" class="filter-form">
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Agents</option>
                            <option value="approved" <?php echo $filter_status === 'approved' ? 'selected' : ''; ?>>Approved Only</option>
                            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending Approval</option>
                            <option value="suspended" <?php echo $filter_status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" 
                               placeholder="Search by name, email, or license..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    
                    <?php if ($filter_status !== 'all' || !empty($search_query)): ?>
                        <a href="manage_agents.php" class="btn-filter" style="background: #6c757d;">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Agents Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Agents List</h3>
                    <div class="card-actions">
                        
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($agents)): ?>
                        <p>No agents found matching your criteria.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Company</th>
                                        <th>License #</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($agents as $agent): ?>
                                    <tr>
                                        <td>#<?php echo $agent['id']; ?></td>
                                        <td><?php echo htmlspecialchars($agent['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($agent['email']); ?></td>
                                        <td><?php echo htmlspecialchars($agent['company_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($agent['license_number']); ?></td>
                                        <td>
                                            <?php
                                            $status_class = 'status-pending';
                                            $status_text = 'Pending';
                                            
                                            // Determine status based on available columns
                                            if ($has_is_approved && isset($agent['is_approved'])) {
                                                if ($agent['is_approved'] == 1) {
                                                    $status_class = 'status-approved';
                                                    $status_text = 'Approved';
                                                }
                                                }

                                        
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="agent-actions">
                                                <button class="action-btn btn-view" 
                                                        onclick="viewAgent(<?php echo $agent['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                
                                                <?php 
                                                // Show approve/reject buttons if agent is not approved
                                                $show_approve_buttons = true;
                                                
                                                if ($has_is_approved && isset($agent['is_approved']) && $agent['is_approved'] == 1) {
                                                    $show_approve_buttons = false;
                                                } elseif ($has_status && isset($agent['status']) && $agent['status'] == 'approved') {
                                                    $show_approve_buttons = false;
                                                }
                                                
                                                if ($show_approve_buttons): 
                                                ?>
                                                    <a href="?action=approve&id=<?php echo $agent['id']; ?>" 
                                                       class="action-btn btn-approve"
                                                       onclick="return confirm('Approve this agent?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </a>
                                                    <a href="?action=reject&id=<?php echo $agent['id']; ?>" 
                                                       class="action-btn btn-reject"
                                                       onclick="return confirm('Reject this agent?')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="?action=delete&id=<?php echo $agent['id']; ?>" 
                                                   class="action-btn btn-delete"
                                                   onclick="return confirm('Are you sure you want to delete this agent? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Details Modal -->
    <div id="agentModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h3>Agent Details</h3>
            <div id="agentDetails">
                <!-- Agent details will be loaded here via AJAX -->
                <p>Loading agent information...</p>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.dashboard-sidebar').classList.toggle('active');
        }
        
        // Modal functions
        function openModal() {
            document.getElementById('agentModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('agentModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('agentModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // View agent details
        function viewAgent(agentId) {
            // Show loading message
            document.getElementById('agentDetails').innerHTML = '<p>Loading agent information...</p>';
            
            // Open modal
            openModal();
            
            // Show a simple details view
            setTimeout(function() {
                document.getElementById('agentDetails').innerHTML = 
                    '<form id="agentForm">' +
                    '    <input type="hidden" id="agent_id" value="' + agentId + '">' +
                    '    <div style="margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 5px;">' +
                    '        <p><strong>Agent ID:</strong> #' + agentId + '</p>' +
                    '        <p>Use the action buttons below to manage this agent.</p>' +
                    '    </div>' +
                    '    <div style="display: flex; gap: 10px; margin-top: 20px;">' +
                    '        <a href="manage_agents.php?action=approve&id=' + agentId + '" class="action-btn btn-approve" onclick="return confirm(\'Approve this agent?\')">' +
                    '            <i class="fas fa-check"></i> Approve Agent' +
                    '        </a>' +
                    '        <a href="manage_agents.php?action=delete&id=' + agentId + '" class="action-btn btn-delete" onclick="return confirm(\'Are you sure you want to delete this agent?\')">' +
                    '            <i class="fas fa-trash"></i> Delete Agent' +
                    '        </a>' +
                    '        <button type="button" class="action-btn btn-view" onclick="closeModal()">' +
                    '            <i class="fas fa-times"></i> Close' +
                    '        </button>' +
                    '    </div>' +
                    '</form>';
            }, 500);
        }
        
        // Export agents function (placeholder)
        function exportAgents() {
            alert('Export functionality will be implemented soon!');
        }
        
        // Auto-close success message after 5 seconds
        setTimeout(function() {
            var message = document.querySelector('.message');
            if (message) {
                message.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>
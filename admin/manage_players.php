<?php
// admin/manage_players.php - Player Management
require_once '../config/config.php';
require_once '../auth/check_auth.php';

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$conn = getConnection();
$error_message = '';
$success_message = '';

// Handle player deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_player'])) {
    $player_user_id = (int)$_POST['player_user_id'];
    try {
        // Start a transaction
        $conn->begin_transaction();
        
        // First delete from players table
        $stmt = $conn->prepare("DELETE FROM players WHERE user_id = ?");
        $stmt->bind_param("i", $player_user_id);
        $stmt->execute();
        
        // Then delete from users table
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $player_user_id);
        $stmt->execute();
        
        $conn->commit();
        $success_message = 'Player deleted successfully!';
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = 'Error deleting player: ' . $e->getMessage();
    }
}

// Handle player approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_player'])) {
    $player_user_id = (int)$_POST['player_user_id'];
    try {
        $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $player_user_id);
        $stmt->execute();
        $success_message = 'Player approved successfully!';
    } catch (Exception $e) {
        $error_message = 'Error approving player: ' . $e->getMessage();
    }
}

// Fetch all players with their user info
$sql = "SELECT 
            u.id as user_id,
            u.username,
            u.email,
            u.full_name,
            u.phone,
            u.status,
            u.created_at,
            p.position,
            p.age,
            p.nationality,
            p.current_club,
            p.height,
            p.weight
        FROM users u
        LEFT JOIN players p ON u.id = p.user_id
        WHERE u.user_type = 'player'
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);
$players = [];
if ($result) {
    $players = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();

// Get counts
$total_players = count($players);
$approved_players = count(array_filter($players, fn($p) => $p['status'] === 'approved'));
$pending_players = count(array_filter($players, fn($p) => $p['status'] === 'pending'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Players | Admin Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        /* Additional styles for players page */
        .search-box {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            width: 300px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-box:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(45, 90, 39, 0.1);
        }
        
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: var(--white);
            padding: 25px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: var(--transition);
        }
        
        .stat-box:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-box h3 {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .stat-box .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            line-height: 1;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge.approved {
            background-color: rgba(40, 167, 69, 0.15);
            color: #155724;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .badge.pending {
            background-color: rgba(255, 193, 7, 0.15);
            color: #856404;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .badge.free {
            background-color: rgba(108, 117, 125, 0.15);
            color: #383d41;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: var(--white);
            padding: 30px;
            border-radius: var(--radius-md);
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow-lg);
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .search-box {
                width: 100%;
                margin-top: 15px;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php 
        // Create sidebar content
        $conn = getConnection();
        $sql = "SELECT COUNT(*) as count FROM users WHERE status = 'pending'";
        $result = $conn->query($sql);
        $pending_count = $result ? $result->fetch_assoc()['count'] : 0;
        $conn->close();
        ?>
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
                    <?php if ($pending_count > 0): ?>
                        <span class="notification"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="manage_players.php" class="menu-item active">
                    <span class="menu-icon">⚽</span>
                    <span>Manage Players</span>
                </a>
                <a href="manage_agents.php" class="menu-item">
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
                <h1>Manage Players</h1>
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

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="stats-summary">
                <div class="stat-box">
                    <h3>Total Players</h3>
                    <div class="number"><?php echo $total_players; ?></div>
                </div>
                <div class="stat-box">
                    <h3>Approved Players</h3>
                    <div class="number"><?php echo $approved_players; ?></div>
                </div>
                <div class="stat-box">
                    <h3>Pending Approval</h3>
                    <div class="number"><?php echo $pending_players; ?></div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3>All Players (<?php echo $total_players; ?>)</h3>
                    <input type="text" id="searchBox" class="search-box" placeholder="Search players by name, email, or position...">
                </div>
                <div class="card-body">
                    <?php if ($total_players > 0): ?>
                        <div class="table-responsive">
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Age</th>
                                        <th>Club</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($players as $player): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($player['full_name']); ?></strong><br>
                                            <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($player['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($player['position'] ?? 'Not set'); ?></td>
                                        <td><?php echo htmlspecialchars($player['age'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($player['current_club'] ?? 'Free Agent'); ?></td>
                                        <td>
                                            <?php if ($player['status'] === 'approved'): ?>
                                                <span class="badge approved">Approved</span>
                                            <?php elseif ($player['status'] === 'pending'): ?>
                                                <span class="badge pending">Pending</span>
                                            <?php else: ?>
                                                <span class="badge free"><?php echo ucfirst($player['status']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($player['created_at'])); ?></td>
                                        <td>
                                            <?php if ($player['status'] === 'pending'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="player_user_id" value="<?php echo $player['user_id']; ?>">
                                                    <button type="submit" name="approve_player" class="action-btn btn-approve">Approve</button>
                                                </form>
                                            <?php endif; ?>
                                            <button class="action-btn btn-edit" onclick="viewPlayerDetails(<?php echo $player['user_id']; ?>)">View</button>
                                            <button class="action-btn btn-delete" onclick="confirmDelete(<?php echo $player['user_id']; ?>, '<?php echo htmlspecialchars(addslashes($player['full_name'])); ?>')">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">⚽</div>
                            <h4>No Players Found</h4>
                            <p>No players have registered yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="index.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="approve_users.php" class="menu-item">
                <span class="menu-icon">👥</span>
                <span>Approve</span>
            </a>
            <a href="manage_players.php" class="menu-item active">
                <span class="menu-icon">⚽</span>
                <span>Players</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3 style="color: #dc3545; margin-bottom: 15px;">Confirm Delete</h3>
            <p>Are you sure you want to delete <strong id="deletePlayerName"></strong>?</p>
            <p style="color: #dc3545; margin-top: 10px; font-size: 0.9rem;">
                ⚠️ This action cannot be undone! All player data will be permanently removed.
            </p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="player_user_id" id="deletePlayerId">
                <input type="hidden" name="delete_player" value="1">
                <div class="modal-actions">
                    <button type="button" class="btn btn-light" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #dc3545, #e83e8c); color: white;">Delete Player</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Player Details Modal -->
    <div id="playerModal" class="modal">
        <div class="modal-content">
            <h3 id="playerModalTitle" style="margin-bottom: 20px;">Player Details</h3>
            <div id="playerDetails"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-light" onclick="closePlayerModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.dashboard-sidebar').classList.toggle('active');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.dashboard-sidebar');
            const toggleBtn = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 992 && 
                !sidebar.contains(event.target) && 
                !toggleBtn.contains(event.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
        
        // Search functionality
        document.getElementById('searchBox').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.dashboard-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        function viewPlayerDetails(userId) {
            // Show loading state
            document.getElementById('playerDetails').innerHTML = '<p>Loading player details...</p>';
            document.getElementById('playerModal').classList.add('active');
            
            // In a real application, you would fetch player details via AJAX
            // For now, we'll show basic info
            fetch(`get_player_details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const player = data.player;
                        document.getElementById('playerModalTitle').textContent = player.full_name;
                        document.getElementById('playerDetails').innerHTML = `
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                                <div>
                                    <strong>Email:</strong><br>
                                    ${player.email}
                                </div>
                                <div>
                                    <strong>Phone:</strong><br>
                                    ${player.phone || 'Not provided'}
                                </div>
                                <div>
                                    <strong>Position:</strong><br>
                                    ${player.position || 'Not set'}
                                </div>
                                <div>
                                    <strong>Age:</strong><br>
                                    ${player.age || 'N/A'}
                                </div>
                                <div>
                                    <strong>Nationality:</strong><br>
                                    ${player.nationality || 'Not set'}
                                </div>
                                <div>
                                    <strong>Current Club:</strong><br>
                                    ${player.current_club || 'Free Agent'}
                                </div>
                            </div>
                            <div>
                                <strong>Status:</strong>
                                ${player.status === 'approved' ? '<span class="badge approved">Approved</span>' : 
                                  player.status === 'pending' ? '<span class="badge pending">Pending Approval</span>' : 
                                  '<span class="badge free">' + player.status + '</span>'}
                            </div>
                            <div style="margin-top: 15px;">
                                <strong>Registered:</strong> ${new Date(player.created_at).toLocaleDateString()}
                            </div>
                        `;
                    } else {
                        document.getElementById('playerDetails').innerHTML = `<p>Error loading player details: ${data.message}</p>`;
                    }
                })
                .catch(error => {
                    document.getElementById('playerDetails').innerHTML = `<p>Error loading player details. Please try again.</p>`;
                });
        }
        
        function confirmDelete(userId, playerName) {
            document.getElementById('deletePlayerId').value = userId;
            document.getElementById('deletePlayerName').textContent = playerName;
            document.getElementById('deleteModal').classList.add('active');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }
        
        function closePlayerModal() {
            document.getElementById('playerModal').classList.remove('active');
        }
        
        // Close modals when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
                closePlayerModal();
            }
        });
    </script>
</body>
</html>
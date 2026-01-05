<?php
require_once '../config/config.php';
require_once '../auth/check_auth.php';

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$conn = getConnection();

// Get statistics
$stats = [];
$sql = "SELECT COUNT(*) as count, status FROM users GROUP BY status";
$result = $conn->query($sql);
$user_stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
while ($row = $result->fetch_assoc()) {
    $user_stats[$row['status']] = $row['count'];
}

$stats['total_users'] = array_sum($user_stats);
$stats['pending_users'] = $user_stats['pending'];
$stats['approved_users'] = $user_stats['approved'];

// Get user type counts
$sql = "SELECT COUNT(*) as count, user_type FROM users WHERE status = 'approved' GROUP BY user_type";
$result = $conn->query($sql);
$type_stats = ['player' => 0, 'agent' => 0, 'manager' => 0, 'admin' => 0];
while ($row = $result->fetch_assoc()) {
    $type_stats[$row['user_type']] = $row['count'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Admin Dashboard | Freetown Football Agency</title>
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
                <a href="index.php" class="menu-item active">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="approve_users.php" class="menu-item">
                    <span class="menu-icon">👥</span>
                    <span>Approve Users</span>
                    <?php if ($stats['pending_users'] > 0): ?>
                        <span class="notification"><?php echo $stats['pending_users']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="manage_players.php" class="menu-item">
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
                <h1>Admin Dashboard</h1>
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

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['pending_users']; ?></h3>
                        <p>Pending Approvals</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3><?php echo $stats['approved_users']; ?></h3>
                        <p>Approved Users</p>
                    </div>
                </div>
            </div>

            <!-- User Type Distribution -->
            <div class="content-card">
                <div class="card-header">
                    <h3>User Distribution</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <h4>Players: <?php echo $type_stats['player']; ?></h4>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo ($type_stats['player'] / max(1, $stats['approved_users'])) * 100; ?>%; background-color: var(--accent-green);"></div>
                            </div>
                        </div>
                        <div>
                            <h4>Agents: <?php echo $type_stats['agent']; ?></h4>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo ($type_stats['agent'] / max(1, $stats['approved_users'])) * 100; ?>%; background-color: var(--secondary-blue);"></div>
                            </div>
                        </div>
                        <div>
                            <h4>Managers: <?php echo $type_stats['manager']; ?></h4>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo ($type_stats['manager'] / max(1, $stats['approved_users'])) * 100; ?>%; background-color: var(--light-green);"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <a href="approve_users.php" class="btn" style="background-color: var(--light-green);">Approve Users</a>
                        <a href="manage_players.php" class="btn" style="background-color: var(--primary-blue);">Manage Players</a>
                        <a href="manage_agents.php" class="btn" style="background-color: var(--light-green);">Manage Agents</a>
                        <a href="reports.php" class="btn" style="background-color: #438cccff;">View Reports</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="index.php" class="menu-item active">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="approve_users.php" class="menu-item">
                <span class="menu-icon">👥</span>
                <span>Approve</span>
            </a>
            <a href="manage_players.php" class="menu-item">
                <span class="menu-icon">⚽</span>
                <span>Players</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Logout</span>
            </a>
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
    
    // Mobile menu toggle for sidebar
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            
            // Close sidebar on mobile after clicking
            if (window.innerWidth <= 992) {
                document.querySelector('.dashboard-sidebar').classList.remove('active');
            }
        });
    });
    
    // Animate progress bars on page load
    document.addEventListener('DOMContentLoaded', function() {
        const progressBars = document.querySelectorAll('.progress');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 300);
        });
    });
</script>
</body>
</html>
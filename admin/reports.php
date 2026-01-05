<?php
require_once '../config/config.php';
require_once '../auth/check_auth.php';

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Reports | Admin Dashboard</title>
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
                <a href="manage_agents.php" class="menu-item">
                    <span class="menu-icon">🤝</span>
                    <span>Manage Agents</span>
                </a>
                <a href="manage_managers.php" class="menu-item">
                    <span class="menu-icon">🏢</span>
                    <span>Manage Managers</span>
                </a>
                <a href="reports.php" class="menu-item active">
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
                <h1>Reports & Analytics</h1>
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

            <div class="content-card">
                <div class="card-header">
                    <h3>System Reports</h3>
                </div>
                <div class="card-body">
                    <p>Reporting and analytics functionality will be implemented here.</p>
                    <p>This page will provide:</p>
                    <ul>
                        <li>User registration statistics and trends</li>
                        <li>Platform usage analytics</li>
                        <li>Export functionality for data</li>
                        <li>Performance metrics and KPIs</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.dashboard-sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>
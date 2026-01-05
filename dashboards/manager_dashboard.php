<?php
require_once '../auth/check_auth.php';

// Only allow manager access
if ($_SESSION['user_type'] !== 'manager') {
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
    <title>Manager Dashboard | Freetown Football Agency</title>
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
                <a href="manager_dashboard.php" class="menu-item active">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="manager_scouting.php" class="menu-item">
                    <span class="menu-icon">🔍</span>
                    <span>Player Scouting</span>
                </a>
                <a href="manager_contracts.php" class="menu-item">
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
                <h1>Manager Dashboard</h1>
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
                    <div class="stat-icon">👁️</div>
                    <div class="stat-info">
                        <h3>25</h3>
                        <p>Players Viewed</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🤝</div>
                    <div class="stat-info">
                        <h3>5</h3>
                        <p>Active Negotiations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h3>2</h3>
                        <p>Successful Signings</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
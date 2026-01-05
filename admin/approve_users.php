<?php
require_once '../config/config.php';
require_once '../auth/check_auth.php';

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$conn = getConnection();
$message = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $action = $_POST['action'] ?? '';
    
    if ($user_id && $action) {
        $status = ($action == 'approve') ? 'approved' : 'rejected';
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $user_id);
        
        if ($stmt->execute()) {
            $message = "User has been $status successfully.";
        } else {
            $message = "Error updating user status.";
        }
        $stmt->close();
    }
}

// Get pending users
$sql = "SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC";
$result = $conn->query($sql);
$pending_users = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Approve Users | Admin Panel</title>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar (same as index.php) -->
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
                <a href="approve_users.php" class="menu-item active">
                    <span class="menu-icon">👥</span>
                    <span>Approve Users</span>
                    <?php if (count($pending_users) > 0): ?>
                        <span style="background: red; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; margin-left: auto;">
                            <?php echo count($pending_users); ?>
                        </span>
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
                <h1>Approve Users</h1>
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

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="content-card">
                <div class="card-header">
                    <h3>Pending User Approvals (<?php echo count($pending_users); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (count($pending_users) > 0): ?>
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>User Type</th>
                                    <th>Phone</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="status-badge"><?php echo ucfirst($user['user_type']); ?></span></td>
                                    <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="action-btn btn-approve">Approve</button>
                                            <button type="submit" name="action" value="reject" class="action-btn btn-reject">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No pending users for approval.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
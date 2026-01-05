<?php
require_once '../auth/check_auth.php';

// Only allow player access
if ($_SESSION['user_type'] !== 'player') {
    header("Location: ../login.php");
    exit();
}

// Static notifications data (without database)
$notifications = [
    [
        'id' => 1,
        'icon' => '✅',
        'title' => 'Profile Viewed',
        'message' => 'Your profile was viewed by a club scout',
        'time' => 'Just now',
        'is_read' => false
    ],
    [
        'id' => 2,
        'icon' => '📝',
        'title' => 'Contract Offer',
        'message' => 'New contract offer received',
        'time' => '2 hours ago',
        'is_read' => false
    ],
    [
        'id' => 3,
        'icon' => '⚽',
        'title' => 'Trial Invitation',
        'message' => 'Trial invitation from local academy',
        'time' => 'Yesterday',
        'is_read' => true
    ],
    [
        'id' => 4,
        'icon' => '📅',
        'title' => 'Assessment Scheduled',
        'message' => 'Skills assessment scheduled for next week',
        'time' => '2 days ago',
        'is_read' => true
    ],
    [
        'id' => 5,
        'icon' => '⭐',
        'title' => 'Performance Rating',
        'message' => 'Your performance rating has been updated',
        'time' => '3 days ago',
        'is_read' => true
    ]
];

// Count unread notifications
$unread_count = 0;
foreach ($notifications as $notification) {
    if (!$notification['is_read']) {
        $unread_count++;
    }
}

// Static stats
$views_count = 12;
$opportunities_count = 3;
$contracts_count = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Player Dashboard | Freetown Football Agency</title>
    <style>
        /* Notification styles */
        .notifications-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            transition: background-color 0.2s;
            cursor: pointer;
        }
        
        .notification-item:hover {
            background-color: var(--bg-light);
        }
        
        .notification-item.unread {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        .notification-icon {
            font-size: 1.2rem;
            min-width: 24px;
            text-align: center;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }
        
        .notification-message {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.4;
        }
        
        .notification-time {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .notification-badge {
            background-color: var(--accent-red);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .mark-read-btn {
            background: none;
            border: none;
            color: var(--primary-blue);
            cursor: pointer;
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .mark-read-btn:hover {
            background-color: var(--bg-light);
        }
        
        .notification-item.read {
            opacity: 0.7;
        }
        
        .notification-item.read .notification-title {
            font-weight: 500;
        }
        
        .new-badge {
            background-color: var(--primary-blue);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 8px;
            display: inline-block;
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
                <a href="player_dashboard.php" class="menu-item active">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="notification-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="player_profile.php" class="menu-item">
                    <span class="menu-icon">👤</span>
                    <span>My Profile</span>
                </a>
                <a href="player_opportunities.php" class="menu-item">
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
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
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
                        <h3><?php echo $views_count; ?></h3>
                        <p>Profile Views</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <h3><?php echo $opportunities_count; ?></h3>
                        <p>Active Opportunities</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🤝</div>
                    <div class="stat-info">
                        <h3><?php echo $contracts_count; ?></h3>
                        <p>Contract Offers</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activities / Notifications -->
            <div class="content-card">
                <div class="card-header">
                    <h3>Recent Activities</h3>
                    <?php if ($unread_count > 0): ?>
                        <button class="mark-read-btn" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i> Mark all as read
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <ul class="notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                        <li class="notification-item <?php echo !$notification['is_read'] ? 'unread' : 'read'; ?>" 
                            data-id="<?php echo $notification['id']; ?>"
                            onclick="markAsRead(this, <?php echo $notification['id']; ?>)">
                            <div class="notification-icon">
                                <?php echo $notification['icon']; ?>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">
                                    <?php echo htmlspecialchars($notification['title']); ?>
                                    <?php if (!$notification['is_read']): ?>
                                        <span class="new-badge">New</span>
                                    <?php endif; ?>
                                </div>
                                <div class="notification-message">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </div>
                                <div class="notification-time">
                                    <?php echo $notification['time']; ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <!-- More example notifications -->
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed var(--border-color);">
                        <h4 style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 10px;">
                            Other possible notifications:
                        </h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            <span style="background: var(--bg-light); padding: 5px 10px; border-radius: 15px; font-size: 0.85rem;">
                                🏆 Tournament invitation received
                            </span>
                            <span style="background: var(--bg-light); padding: 5px 10px; border-radius: 15px; font-size: 0.85rem;">
                                📊 Stats updated by coach
                            </span>
                            <span style="background: var(--bg-light); padding: 5px 10px; border-radius: 15px; font-size: 0.85rem;">
                                🤝 Agent connection request
                            </span>
                            <span style="background: var(--bg-light); padding: 5px 10px; border-radius: 15px; font-size: 0.85rem;">
                                📈 Profile completion: 85%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="player_dashboard.php" class="menu-item active">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
                <?php if ($unread_count > 0): ?>
                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="player_profile.php" class="menu-item">
                <span class="menu-icon">👤</span>
                <span>Profile</span>
            </a>
            <a href="player_opportunities.php" class="menu-item">
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
        // Store read status in localStorage
        function getReadNotifications() {
            const read = localStorage.getItem('readNotifications');
            return read ? JSON.parse(read) : [];
        }
        
        function saveReadNotifications(readArray) {
            localStorage.setItem('readNotifications', JSON.stringify(readArray));
        }
        
        // Check localStorage on page load
        document.addEventListener('DOMContentLoaded', function() {
            const readNotifications = getReadNotifications();
            const notificationItems = document.querySelectorAll('.notification-item');
            
            notificationItems.forEach(item => {
                const id = item.dataset.id;
                if (readNotifications.includes(parseInt(id))) {
                    item.classList.remove('unread');
                    item.classList.add('read');
                }
            });
            
            updateUnreadCount();
        });
        
        // Mark single notification as read
        function markAsRead(element, id) {
            element.classList.remove('unread');
            element.classList.add('read');
            
            // Update localStorage
            const readNotifications = getReadNotifications();
            if (!readNotifications.includes(id)) {
                readNotifications.push(id);
                saveReadNotifications(readNotifications);
            }
            
            // Update badge count
            updateUnreadCount();
        }
        
        // Mark all notifications as read
        function markAllAsRead() {
            const notificationItems = document.querySelectorAll('.notification-item');
            const readNotifications = getReadNotifications();
            
            notificationItems.forEach(item => {
                const id = parseInt(item.dataset.id);
                item.classList.remove('unread');
                item.classList.add('read');
                
                if (!readNotifications.includes(id)) {
                    readNotifications.push(id);
                }
            });
            
            saveReadNotifications(readNotifications);
            updateUnreadCount();
            
            // Hide the "Mark all as read" button
            document.querySelector('.mark-read-btn').style.display = 'none';
        }
        
        // Update the unread count badge
        function updateUnreadCount() {
            const unreadItems = document.querySelectorAll('.notification-item.unread');
            const badge = document.querySelector('.notification-badge');
            const sidebarBadges = document.querySelectorAll('.sidebar-menu .notification-badge, .dashboard-nav-mobile .notification-badge');
            
            // Update count
            const unreadCount = unreadItems.length;
            
            // Update badges
            sidebarBadges.forEach(badge => {
                if (unreadCount > 0) {
                    badge.textContent = unreadCount;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            });
            
            // Update or hide mark all button
            const markAllBtn = document.querySelector('.mark-read-btn');
            if (markAllBtn) {
                if (unreadCount > 0) {
                    markAllBtn.style.display = 'inline-block';
                } else {
                    markAllBtn.style.display = 'none';
                }
            }
        }
        
        // Simulate new notification after 5 seconds
        setTimeout(function() {
            const notificationsList = document.querySelector('.notifications-list');
            if (notificationsList) {
                const newNotification = document.createElement('li');
                newNotification.className = 'notification-item unread';
                newNotification.dataset.id = Date.now(); // Unique ID
                newNotification.onclick = function() {
                    markAsRead(this, parseInt(this.dataset.id));
                };
                
                newNotification.innerHTML = `
                    <div class="notification-icon">🎯</div>
                    <div class="notification-content">
                        <div class="notification-title">
                            New Opportunity <span class="new-badge">New</span>
                        </div>
                        <div class="notification-message">
                            Scouting opportunity from European club
                        </div>
                        <div class="notification-time">
                            Just now
                        </div>
                    </div>
                `;
                
                // Insert at the top
                notificationsList.insertBefore(newNotification, notificationsList.firstChild);
                updateUnreadCount();
                
                // Show a toast notification
                showToast('New notification: Scouting opportunity from European club');
            }
        }, 5000);
        
        // Toast notification function
        function showToast(message) {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--primary-blue);
                color: white;
                padding: 15px 20px;
                border-radius: var(--radius-md);
                box-shadow: var(--shadow-lg);
                z-index: 1000;
                animation: slideIn 0.3s ease;
                max-width: 300px;
            `;
            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.2rem;">🔔</span>
                    <div>${message}</div>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Remove after 5 seconds
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 5000);
            
            // Add CSS animations
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    </script>
</body>
</html>
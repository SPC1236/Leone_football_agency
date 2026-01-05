<?php
// admin/messages.php - Contact Messages Management
require_once '../config/config.php';
require_once '../includes/functions.php';

requireLogin();
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$conn = getDBConnection();

// Handle message status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $message_id = (int)$_POST['message_id'];
    $status = sanitize($_POST['status']);
    
    try {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->execute([$status, $message_id]);
        setFlashMessage('success', 'Message status updated!');
        header("Location: messages.php");
        exit();
    } catch (Exception $e) {
        setFlashMessage('error', 'Error updating message: ' . $e->getMessage());
    }
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $message_id = (int)$_POST['message_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        setFlashMessage('success', 'Message deleted!');
        header("Location: messages.php");
        exit();
    } catch (Exception $e) {
        setFlashMessage('error', 'Error deleting message: ' . $e->getMessage());
    }
}

// Fetch all messages
$messages_query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$messages = $conn->query($messages_query)->fetchAll();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary-blue: #1a365d;
            --accent-green: #2d5a27;
            --light-gray: #f5f5f5;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
        }

        .dashboard-container { display: flex; min-height: 100vh; }

        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-blue);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header { padding: 20px; background-color: rgba(0, 0, 0, 0.2); text-align: center; }
        .sidebar-header h2 { font-size: 1.3rem; }
        .sidebar-menu { list-style: none; padding: 20px 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 15px 20px; color: white; text-decoration: none; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: var(--accent-green); }
        .sidebar-menu .icon { margin-right: 10px; font-size: 1.2rem; }

        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 30px; }

        .top-bar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .top-bar h1 { color: var(--primary-blue); }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 45px; height: 45px; border-radius: 50%; background-color: var(--accent-green); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        .logout-btn { background-color: #dc3545; color: white; padding: 8px 20px; border: none; border-radius: 5px; text-decoration: none; }

        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-btn {
            padding: 8px 15px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-btn.active {
            background-color: var(--accent-green);
            color: white;
            border-color: var(--accent-green);
        }

        .messages-grid {
            display: grid;
            gap: 20px;
        }

        .message-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #e0e0e0;
        }

        .message-card.new { border-left-color: #007bff; }
        .message-card.read { border-left-color: #6c757d; }
        .message-card.replied { border-left-color: #28a745; }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .message-info h3 {
            color: var(--primary-blue);
            margin-bottom: 5px;
        }

        .message-meta {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge.new { background-color: #cce5ff; color: #004085; }
        .badge.read { background-color: #e2e3e5; color: #383d41; }
        .badge.replied { background-color: #d4edda; color: #155724; }

        .message-content {
            margin: 15px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .message-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-mark-read { background-color: #6c757d; color: white; }
        .btn-mark-replied { background-color: #28a745; color: white; }
        .btn-delete { background-color: #dc3545; color: white; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar-menu span:not(.icon) { display: none; }
            .main-content { margin-left: 70px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <p>Freetown FA</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><span class="icon">📊</span><span>Dashboard</span></a></li>
                <li><a href="approve_users.php"><span class="icon">✅</span><span>Approve Users</span></a></li>
                <li><a href="manage_players.php"><span class="icon">⚽</span><span>Players</span></a></li>
                <li><a href="manage_agents.php"><span class="icon">👔</span><span>Agents</span></a></li>
                <li><a href="manage_managers.php"><span class="icon">📋</span><span>Managers</span></a></li>
                <li><a href="messages.php" class="active"><span class="icon">✉️</span><span>Messages</span></a></li>
                <li><a href="reports.php"><span class="icon">📈</span><span>Reports</span></a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Contact Messages</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 2)); ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
                        <p style="font-size: 0.85rem; color: #6c757d;">Administrator</p>
                    </div>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="filters">
                <strong>Filter:</strong>
                <button class="filter-btn active" onclick="filterMessages('all')">All</button>
                <button class="filter-btn" onclick="filterMessages('new')">New</button>
                <button class="filter-btn" onclick="filterMessages('read')">Read</button>
                <button class="filter-btn" onclick="filterMessages('replied')">Replied</button>
            </div>

            <div class="messages-grid">
                <?php foreach ($messages as $message): ?>
                <div class="message-card <?php echo $message['status']; ?>" data-status="<?php echo $message['status']; ?>">
                    <div class="message-header">
                        <div class="message-info">
                            <h3><?php echo htmlspecialchars($message['name']); ?></h3>
                            <div class="message-meta">
                                <strong>Email:</strong> <?php echo htmlspecialchars($message['email']); ?> | 
                                <strong>Phone:</strong> <?php echo htmlspecialchars($message['phone'] ?? 'N/A'); ?><br>
                                <strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?><br>
                                <strong>Received:</strong> <?php echo formatDate($message['created_at']); ?>
                            </div>
                        </div>
                        <span class="badge <?php echo $message['status']; ?>">
                            <?php echo ucfirst($message['status']); ?>
                        </span>
                    </div>

                    <div class="message-content">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>

                    <div class="message-actions">
                        <?php if ($message['status'] === 'new'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                            <input type="hidden" name="status" value="read">
                            <input type="hidden" name="update_status" value="1">
                            <button type="submit" class="action-btn btn-mark-read">Mark as Read</button>
                        </form>
                        <?php endif; ?>

                        <?php if ($message['status'] !== 'replied'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                            <input type="hidden" name="status" value="replied">
                            <input type="hidden" name="update_status" value="1">
                            <button type="submit" class="action-btn btn-mark-replied">Mark as Replied</button>
                        </form>
                        <?php endif; ?>

                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                            <input type="hidden" name="delete_message" value="1">
                            <button type="submit" class="action-btn btn-delete">Delete</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        function filterMessages(status) {
            const cards = document.querySelectorAll('.message-card');
            const buttons = document.querySelectorAll('.filter-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
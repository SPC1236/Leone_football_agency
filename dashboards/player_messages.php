<?php
require_once '../auth/check_auth.php';

// Only allow player access
if ($_SESSION['user_type'] !== 'player') {
    header("Location: ../login.php");
    exit();
}

// Static messages data
$messages = [
    [
        'id' => 1,
        'sender' => 'John Smith',
        'sender_role' => 'Scout',
        'sender_company' => 'Manchester United',
        'subject' => 'Trial Opportunity',
        'preview' => 'We were impressed by your profile and would like to invite you for a trial...',
        'date' => '2024-01-15 14:30',
        'is_read' => false,
        'type' => 'opportunity'
    ],
    [
        'id' => 2,
        'sender' => 'Maria Brown',
        'sender_role' => 'Agent',
        'sender_company' => 'European Sports Agency',
        'subject' => 'Contract Discussion',
        'preview' => 'I have reviewed your profile and believe we can secure you a professional contract...',
        'date' => '2024-01-14 09:15',
        'is_read' => true,
        'type' => 'contract'
    ],
    [
        'id' => 3,
        'sender' => 'Freetown Football Agency',
        'sender_role' => 'System',
        'sender_company' => '',
        'subject' => 'Profile Update Required',
        'preview' => 'Please update your medical information to complete your profile...',
        'date' => '2024-01-13 16:45',
        'is_read' => true,
        'type' => 'system'
    ],
    [
        'id' => 4,
        'sender' => 'David Clark',
        'sender_role' => 'Coach',
        'sender_company' => 'Local Academy',
        'subject' => 'Training Session',
        'preview' => 'Reminder about tomorrow\'s training session at 9 AM. Please bring your gear...',
        'date' => '2024-01-12 11:20',
        'is_read' => true,
        'type' => 'training'
    ]
];

$unread_count = count(array_filter($messages, fn($m) => !$m['is_read']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Messages | Freetown Football Agency</title>
    <style>
        .messages-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            height: calc(100vh - 200px);
        }
        
        .message-list {
            background: var(--white);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
        }
        
        .message-list-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            background: var(--primary-blue);
            color: white;
        }
        
        .message-search {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
        }
        
        .message-items {
            flex: 1;
            overflow-y: auto;
        }
        
        .message-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        
        .message-item:hover {
            background-color: var(--bg-light);
        }
        
        .message-item.active {
            background-color: rgba(13, 110, 253, 0.1);
            border-left: 3px solid var(--primary-blue);
        }
        
        .message-item.unread {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .message-preview {
            flex: 1;
            min-width: 0;
        }
        
        .message-sender {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 3px;
        }
        
        .message-subject {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 5px;
        }
        
        .message-time {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .message-detail {
            background: var(--white);
            border-radius: var(--radius-md);
            padding: 30px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .message-header {
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 30px;
        }
        
        .message-sender-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .message-sender-details h3 {
            margin: 0 0 5px 0;
        }
        
        .message-sender-details p {
            margin: 0;
            color: var(--text-secondary);
        }
        
        .message-date {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .message-body {
            flex: 1;
            line-height: 1.6;
            color: var(--text-secondary);
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .btn-reply {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-delete {
            background: var(--accent-red);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .new-message-btn {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .empty-message {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            color: var(--text-secondary);
        }
        
        .empty-icon {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 20px;
        }
        
        .message-type-badge {
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 10px;
            text-transform: uppercase;
        }
        
        .badge-opportunity {
            background: rgba(40, 167, 69, 0.15);
            color: #155724;
        }
        
        .badge-contract {
            background: rgba(0, 123, 255, 0.15);
            color: #004085;
        }
        
        .badge-system {
            background: rgba(108, 117, 125, 0.15);
            color: #383d41;
        }
        
        .badge-training {
            background: rgba(255, 193, 7, 0.15);
            color: #856404;
        }
        
        @media (max-width: 1024px) {
            .messages-container {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .message-detail {
                min-height: 400px;
            }
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
                <a href="player_dashboard.php" class="menu-item">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
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
                <a href="player_messages.php" class="menu-item active">
                    <span class="menu-icon">💬</span>
                    <span>Messages</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="notification-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
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
                <h1>Messages</h1>
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

            <button class="new-message-btn" onclick="newMessage()">
                <span>✉️</span> Compose New Message
            </button>

            <div class="messages-container">
                <!-- Message List -->
                <div class="message-list">
                    <div class="message-list-header">
                        <h3 style="margin: 0; color: white;">Inbox</h3>
                        <p style="margin: 5px 0 0 0; opacity: 0.8; font-size: 0.9rem;">
                            <?php echo $unread_count; ?> unread message<?php echo $unread_count != 1 ? 's' : ''; ?>
                        </p>
                    </div>
                    
                    <div class="message-search">
                        <input type="text" class="search-input" placeholder="Search messages..." onkeyup="filterMessages(this.value)">
                    </div>
                    
                    <div class="message-items">
                        <?php foreach ($messages as $message): ?>
                        <div class="message-item <?php echo !$message['is_read'] ? 'unread' : ''; ?> 
                            <?php echo $message['id'] == 1 ? 'active' : ''; ?>" 
                            onclick="selectMessage(<?php echo $message['id']; ?>)"
                            data-message-id="<?php echo $message['id']; ?>">
                            <div class="message-avatar">
                                <?php echo strtoupper(substr($message['sender'], 0, 1)); ?>
                            </div>
                            <div class="message-preview">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div class="message-sender">
                                        <?php echo $message['sender']; ?>
                                    </div>
                                    <div class="message-time">
                                        <?php echo date('g:i A', strtotime($message['date'])); ?>
                                    </div>
                                </div>
                                <div class="message-subject">
                                    <?php echo $message['subject']; ?>
                                    <span class="message-type-badge badge-<?php echo $message['type']; ?>">
                                        <?php echo $message['type']; ?>
                                    </span>
                                </div>
                                <div class="message-text">
                                    <?php echo $message['preview']; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Message Detail -->
                <div class="message-detail">
                    <?php 
                    $active_message = $messages[0]; // First message is active by default
                    ?>
                    <div class="message-header">
                        <div class="message-sender-info">
                            <div class="message-avatar" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                <?php echo strtoupper(substr($active_message['sender'], 0, 1)); ?>
                            </div>
                            <div class="message-sender-details">
                                <h3><?php echo $active_message['subject']; ?></h3>
                                <p>
                                    <?php echo $active_message['sender']; ?> • 
                                    <?php echo $active_message['sender_role']; ?>
                                    <?php if ($active_message['sender_company']): ?>
                                         • <?php echo $active_message['sender_company']; ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="message-date">
                            Received: <?php echo date('F j, Y g:i A', strtotime($active_message['date'])); ?>
                        </div>
                    </div>
                    
                    <div class="message-body">
                        <p>Dear <?php echo $_SESSION['full_name']; ?>,</p>
                        
                        <?php if ($active_message['id'] == 1): ?>
                            <p>We were very impressed by your profile on the Freetown Football Agency platform. Your statistics and video highlights show exceptional talent and potential.</p>
                            
                            <p>Manchester United's scouting department would like to invite you for a 3-day trial at our Carrington Training Ground. This trial would take place next month and would include:</p>
                            
                            <ul>
                                <li>Medical assessment</li>
                                <li>Technical skills evaluation</li>
                                <li>Tactical understanding assessment</li>
                                <li>Fitness testing</li>
                                <li>Friendly match with academy players</li>
                            </ul>
                            
                            <p>We would cover all travel and accommodation expenses for the duration of the trial. Please let us know your availability, and we will arrange the necessary paperwork.</p>
                            
                            <p>This is a fantastic opportunity to showcase your abilities at one of the world's top football clubs.</p>
                            
                            <p>Best regards,<br>
                            John Smith<br>
                            Senior Scout<br>
                            Manchester United Football Club</p>
                            
                        <?php elseif ($active_message['id'] == 2): ?>
                            <p>I hope this message finds you well. My name is Maria Brown, and I'm a licensed football agent with European Sports Agency.</p>
                            
                            <p>I've been following your progress through the Freetown Football Agency platform, and I believe you have the potential to secure a professional contract in Europe.</p>
                            
                            <p>I would like to discuss the possibility of representing you. My agency has strong connections with clubs in:</p>
                            
                            <ul>
                                <li>Portugal (Primeira Liga)</li>
                                <li>Belgium (First Division A)</li>
                                <li>Netherlands (Eredivisie)</li>
                                <li>Scandinavian leagues</li>
                            </ul>
                            
                            <p>Would you be available for a call next week to discuss your career goals and how I can help you achieve them?</p>
                            
                            <p>Kind regards,<br>
                            Maria Brown<br>
                            Licensed Football Agent<br>
                            European Sports Agency</p>
                            
                        <?php elseif ($active_message['id'] == 3): ?>
                            <p>This is an automated message from the Freetown Football Agency system.</p>
                            
                            <p>We noticed that your player profile is 85% complete. To ensure clubs and agents have all necessary information, please update the following sections:</p>
                            
                            <ol>
                                <li>Medical information and history</li>
                                <li>Recent match statistics (last 5 games)</li>
                                <li>Video highlights (minimum 3 minutes)</li>
                                <li>References from coaches</li>
                            </ol>
                            
                            <p>Complete profiles receive 3x more views from scouts and agents. You can update your profile by visiting the "My Profile" section of your dashboard.</p>
                            
                            <p>Thank you for using Freetown Football Agency.</p>
                            
                            <p>System Administrator<br>
                            Freetown Football Agency</p>
                            
                        <?php else: ?>
                            <p>Hi <?php echo explode(' ', $_SESSION['full_name'])[0]; ?>,</p>
                            
                            <p>This is a reminder about tomorrow's training session at the local academy. Please note the following details:</p>
                            
                            <ul>
                                <li><strong>Time:</strong> 9:00 AM - 11:30 AM</li>
                                <li><strong>Location:</strong> Main Training Field</li>
                                <li><strong>Focus:</strong> Finishing drills and small-sided games</li>
                            </ul>
                            
                            <p>Please bring:</p>
                            <ul>
                                <li>Training gear (boots for grass and artificial turf)</li>
                                <li>Shin guards</li>
                                <li>Water bottle</li>
                                <li>Any required medications</li>
                            </ul>
                            
                            <p>We'll have a special guest scout attending, so give it your best effort!</p>
                            
                            <p>See you tomorrow,<br>
                            Coach David Clark<br>
                            Freetown Local Academy</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="message-actions">
                        <button class="btn-reply" onclick="replyMessage(<?php echo $active_message['id']; ?>)">
                            <span>↩️</span> Reply
                        </button>
                        <button class="btn-delete" onclick="deleteMessage(<?php echo $active_message['id']; ?>)">
                            <span>🗑️</span> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="player_dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="player_profile.php" class="menu-item">
                <span class="menu-icon">👤</span>
                <span>Profile</span>
            </a>
            <a href="player_messages.php" class="menu-item active">
                <span class="menu-icon">💬</span>
                <span>Messages</span>
            </a>
            <a href="../logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <script>
        // Store read messages in localStorage
        function getReadMessages() {
            const read = localStorage.getItem('readMessages');
            return read ? JSON.parse(read) : [];
        }
        
        function saveReadMessages(readArray) {
            localStorage.setItem('readMessages', JSON.stringify(readArray));
        }
        
        // Mark as read when selected
        function selectMessage(messageId) {
            // Update UI
            document.querySelectorAll('.message-item').forEach(item => {
                item.classList.remove('active');
                if (parseInt(item.dataset.messageId) === messageId) {
                    item.classList.add('active');
                    item.classList.remove('unread');
                }
            });
            
            // Mark as read in localStorage
            const readMessages = getReadMessages();
            if (!readMessages.includes(messageId)) {
                readMessages.push(messageId);
                saveReadMessages(readMessages);
                updateUnreadCount();
            }
            
            // In a real app, you would load the message content via AJAX
            // For demo, we just show an alert
            loadMessageContent(messageId);
        }
        
        function loadMessageContent(messageId) {
            // In a real app, this would fetch message content from server
            // For demo, we'll just scroll to show this is working
            alert('Loading message #' + messageId + '...\n\nIn a real application, this would fetch the full message content.');
        }
        
        function filterMessages(searchTerm) {
            const items = document.querySelectorAll('.message-item');
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm.toLowerCase()) ? 'flex' : 'none';
            });
        }
        
        function newMessage() {
            const recipient = prompt('Enter recipient email:');
            if (recipient) {
                const subject = prompt('Enter subject:');
                if (subject) {
                    const body = prompt('Enter your message:');
                    if (body) {
                        alert('Message sent to ' + recipient + '!\n\nSubject: ' + subject + '\n\nMessage: ' + body);
                    }
                }
            }
        }
        
        function replyMessage(messageId) {
            const message = prompt('Enter your reply:');
            if (message) {
                alert('Reply sent for message #' + messageId + '!\n\nYour reply: ' + message);
            }
        }
        
        function deleteMessage(messageId) {
            if (confirm('Are you sure you want to delete this message?')) {
                const item = document.querySelector(`[data-message-id="${messageId}"]`);
                if (item) {
                    item.style.display = 'none';
                    alert('Message deleted!');
                }
            }
        }
        
        function updateUnreadCount() {
            const unreadItems = document.querySelectorAll('.message-item.unread');
            const badge = document.querySelector('.sidebar-menu .notification-badge');
            
            if (unreadItems.length > 0) {
                badge.textContent = unreadItems.length;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            const readMessages = getReadMessages();
            readMessages.forEach(id => {
                const item = document.querySelector(`[data-message-id="${id}"]`);
                if (item) {
                    item.classList.remove('unread');
                }
            });
            updateUnreadCount();
        });
        
        // Simulate new message
        setTimeout(function() {
            if (Math.random() > 0.5 && confirm('New message received!\n\nYou have a new message from a European agent. Would you like to view it now?')) {
                // Could add a new message dynamically
                alert('New message would appear in your inbox.');
            }
        }, 8000);
    </script>
</body>
</html>
<?php
require_once '../auth/check_auth.php';

// Only allow agent access
if ($_SESSION['user_type'] !== 'agent') {
    header("Location: ../login.php");
    exit();
}

// Static messages data for agents
$conversations = [
    [
        'id' => 1,
        'contact_name' => 'Mohamed Bangura',
        'contact_role' => 'Player',
        'last_message' => 'When is my next trial scheduled?',
        'timestamp' => '2024-01-15 14:30',
        'unread' => true,
        'avatar_color' => '#3498db'
    ],
    [
        'id' => 2,
        'contact_name' => 'Ajax Cape Town',
        'contact_role' => 'Club Representative',
        'last_message' => 'Contract terms have been updated. Please review.',
        'timestamp' => '2024-01-14 11:15',
        'unread' => false,
        'avatar_color' => '#2ecc71'
    ],
    [
        'id' => 3,
        'contact_name' => 'Freetown Football Agency',
        'contact_role' => 'System',
        'last_message' => 'Your license renewal is due next month.',
        'timestamp' => '2024-01-13 09:45',
        'unread' => false,
        'avatar_color' => '#e74c3c'
    ],
    [
        'id' => 4,
        'contact_name' => 'European Sports Network',
        'contact_role' => 'Scouting Partner',
        'last_message' => 'We have identified new talent in Ghana.',
        'timestamp' => '2024-01-12 16:20',
        'unread' => true,
        'avatar_color' => '#9b59b6'
    ],
    [
        'id' => 5,
        'contact_name' => 'John Kamara',
        'contact_role' => 'Player',
        'last_message' => 'Thank you for securing the contract extension!',
        'timestamp' => '2024-01-11 10:10',
        'unread' => false,
        'avatar_color' => '#1abc9c'
    ]
];

// Sample messages for conversation 1
$sample_messages = [
    [
        'id' => 1,
        'sender' => 'Mohamed Bangura',
        'sender_type' => 'player',
        'message' => 'Hi, when is my next trial scheduled? I want to make sure I\'m prepared.',
        'timestamp' => '2024-01-15 14:30',
        'read' => true
    ],
    [
        'id' => 2,
        'sender' => 'You',
        'sender_type' => 'agent',
        'message' => 'It\'s scheduled for next Friday at 10 AM at the Carrington Training Ground. Don\'t forget to bring your boots for both grass and artificial turf.',
        'timestamp' => '2024-01-15 14:45',
        'read' => true
    ],
    [
        'id' => 3,
        'sender' => 'Mohamed Bangura',
        'sender_type' => 'player',
        'message' => 'Great! Will there be any specific drills I should practice beforehand?',
        'timestamp' => '2024-01-15 14:50',
        'read' => true
    ],
    [
        'id' => 4,
        'sender' => 'You',
        'sender_type' => 'agent',
        'message' => 'Focus on your finishing and quick turns. They\'re looking for agility and goal-scoring ability. I\'ll send you a training plan tonight.',
        'timestamp' => '2024-01-15 15:00',
        'read' => true
    ]
];

$active_conversation = $_GET['conversation'] ?? 1;
$search = $_GET['search'] ?? '';

// Filter conversations if search is active
if ($search) {
    $conversations = array_filter($conversations, function($conv) use ($search) {
        return stripos($conv['contact_name'], $search) !== false || 
               stripos($conv['last_message'], $search) !== false;
    });
}

$unread_count = count(array_filter($conversations, fn($c) => $c['unread']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <title>Messages | Agent Dashboard</title>
    <style>
        .messages-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
            height: calc(100vh - 180px);
        }
        
        /* Conversations List */
        .conversations-panel {
            background: var(--white);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
        }
        
        .conversations-header {
            padding: 20px;
            background: var(--primary-blue);
            color: white;
        }
        
        .search-conversations {
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
        
        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }
        
        .conversation-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .conversation-item:hover {
            background-color: var(--bg-light);
        }
        
        .conversation-item.active {
            background-color: rgba(13, 110, 253, 0.1);
            border-left: 3px solid var(--primary-blue);
        }
        
        .conversation-item.unread {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        .conversation-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        .conversation-info {
            flex: 1;
            min-width: 0;
        }
        
        .conversation-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 3px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .conversation-role {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }
        
        .conversation-preview {
            color: var(--text-secondary);
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .conversation-time {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .unread-badge {
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
        }
        
        /* Chat Panel */
        .chat-panel {
            background: var(--white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .chat-header {
            padding: 20px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--white);
        }
        
        .chat-contact {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .chat-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .chat-contact-info h3 {
            margin: 0 0 5px 0;
        }
        
        .chat-contact-info p {
            margin: 0;
            color: var(--text-secondary);
        }
        
        .chat-actions {
            display: flex;
            gap: 10px;
        }
        
        .chat-action-btn {
            background: none;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 8px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }
        
        .chat-action-btn:hover {
            background: var(--bg-light);
        }
        
        .messages-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
            background: #f8f9fa;
        }
        
        .message {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            line-height: 1.4;
        }
        
        .message.received {
            align-self: flex-start;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-bottom-left-radius: 4px;
        }
        
        .message.sent {
            align-self: flex-end;
            background: var(--primary-blue);
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 5px;
            text-align: right;
        }
        
        .message.received .message-time {
            color: var(--text-muted);
        }
        
        .message.sent .message-time {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .message-input-area {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            background: var(--white);
        }
        
        .message-input-container {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .message-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            resize: none;
            min-height: 50px;
            max-height: 100px;
            font-family: inherit;
        }
        
        .message-input:focus {
            outline: none;
            border-color: var(--primary-blue);
        }
        
        .send-btn {
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 12px 25px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            height: 50px;
        }
        
        .send-btn:hover {
            background: #0056b3;
        }
        
        .attachment-btn {
            background: none;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 12px;
            cursor: pointer;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .attachment-btn:hover {
            background: var(--bg-light);
        }
        
        .empty-chat {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            color: var(--text-secondary);
            flex-direction: column;
            gap: 20px;
        }
        
        .empty-chat-icon {
            font-size: 4rem;
            opacity: 0.3;
        }
        
        .new-message-btn {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .quick-action-btn {
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 8px 15px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .quick-action-btn:hover {
            background: var(--border-color);
        }
        
        @media (max-width: 1024px) {
            .messages-container {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .chat-panel {
                min-height: 500px;
            }
        }
        
        @media (max-width: 768px) {
            .chat-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .chat-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .message {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="dashboard-sidebar">
            <div class="sidebar-header">
                <h3>Agent Dashboard</h3>
                <div class="user-role">Agent</div>
            </div>
            <div class="sidebar-menu">
                <a href="agent_dashboard.php" class="menu-item">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="agent_players.php" class="menu-item">
                    <span class="menu-icon">👥</span>
                    <span>My Players</span>
                </a>
                <a href="agent_contracts.php" class="menu-item">
                    <span class="menu-icon">📝</span>
                    <span>Contracts</span>
                </a>
                <a href="agent_scouting.php" class="menu-item">
                    <span class="menu-icon">🔍</span>
                    <span>Scouting</span>
                </a>
                <a href="agent_messages.php" class="menu-item active">
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
                <!-- Conversations List -->
                <div class="conversations-panel">
                    <div class="conversations-header">
                        <h3 style="margin: 0; color: white;">Conversations</h3>
                        <p style="margin: 5px 0 0 0; opacity: 0.8; font-size: 0.9rem;">
                            <?php echo $unread_count; ?> unread conversation<?php echo $unread_count != 1 ? 's' : ''; ?>
                        </p>
                    </div>
                    
                    <div class="search-conversations">
                        <form method="GET" style="display: flex; gap: 10px;">
                            <input type="text" name="search" class="search-input" placeholder="Search conversations..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" style="display: none;">Search</button>
                            <?php if ($search): ?>
                                <a href="agent_messages.php" style="white-space: nowrap; padding: 10px; color: var(--text-secondary);">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <div class="conversations-list">
                        <?php foreach ($conversations as $conversation): ?>
                        <div class="conversation-item <?php echo $conversation['unread'] ? 'unread' : ''; ?> 
                            <?php echo $conversation['id'] == $active_conversation ? 'active' : ''; ?>" 
                            onclick="selectConversation(<?php echo $conversation['id']; ?>)"
                            data-conversation-id="<?php echo $conversation['id']; ?>">
                            <div class="conversation-avatar" style="background-color: <?php echo $conversation['avatar_color']; ?>;">
                                <?php echo strtoupper(substr($conversation['contact_name'], 0, 1)); ?>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-name">
                                    <span><?php echo $conversation['contact_name']; ?></span>
                                    <span class="conversation-time">
                                        <?php echo date('g:i A', strtotime($conversation['timestamp'])); ?>
                                    </span>
                                </div>
                                <div class="conversation-role">
                                    <?php echo $conversation['contact_role']; ?>
                                </div>
                                <div class="conversation-preview">
                                    <?php echo $conversation['last_message']; ?>
                                </div>
                            </div>
                            <?php if ($conversation['unread']): ?>
                                <div class="unread-badge">!</div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Chat Panel -->
                <div class="chat-panel">
                    <?php 
                    $active_conv_data = array_filter($conversations, fn($c) => $c['id'] == $active_conversation);
                    $active_conv = reset($active_conv_data) ?? $conversations[0];
                    ?>
                    
                    <div class="chat-header">
                        <div class="chat-contact">
                            <div class="chat-avatar" style="background-color: <?php echo $active_conv['avatar_color']; ?>;">
                                <?php echo strtoupper(substr($active_conv['contact_name'], 0, 1)); ?>
                            </div>
                            <div class="chat-contact-info">
                                <h3><?php echo $active_conv['contact_name']; ?></h3>
                                <p><?php echo $active_conv['contact_role']; ?></p>
                            </div>
                        </div>
                        
                        <div class="chat-actions">
                            <button class="chat-action-btn" onclick="callContact(<?php echo $active_conv['id']; ?>)">
                                <span>📞</span> Call
                            </button>
                            <button class="chat-action-btn" onclick="viewProfile(<?php echo $active_conv['id']; ?>)">
                                <span>👤</span> Profile
                            </button>
                            <button class="chat-action-btn" onclick="deleteConversation(<?php echo $active_conv['id']; ?>)">
                                <span>🗑️</span> Delete
                            </button>
                        </div>
                    </div>
                    
                    <div class="messages-area" id="messagesArea">
                        <?php if ($active_conversation == 1): ?>
                            <?php foreach ($sample_messages as $message): ?>
                            <div class="message <?php echo $message['sender_type'] === 'agent' ? 'sent' : 'received'; ?>">
                                <div class="message-text">
                                    <?php echo htmlspecialchars($message['message']); ?>
                                </div>
                                <div class="message-time">
                                    <?php echo date('g:i A', strtotime($message['timestamp'])); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-chat">
                                <div class="empty-chat-icon">💬</div>
                                <div>
                                    <h3>Start a Conversation</h3>
                                    <p>Select a conversation from the list or compose a new message.</p>
                                </div>
                                
                                <div class="quick-actions">
                                    <button class="quick-action-btn" onclick="sendQuickMessage('schedule_meeting')">
                                        <span>📅</span> Schedule Meeting
                                    </button>
                                    <button class="quick-action-btn" onclick="sendQuickMessage('contract_update')">
                                        <span>📝</span> Contract Update
                                    </button>
                                    <button class="quick-action-btn" onclick="sendQuickMessage('trial_info')">
                                        <span>⚽</span> Trial Information
                                    </button>
                                    <button class="quick-action-btn" onclick="sendQuickMessage('payment_query')">
                                        <span>💰</span> Payment Query
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="message-input-area">
                        <div class="message-input-container">
                            <button class="attachment-btn" onclick="attachFile()">
                                <span>📎</span>
                            </button>
                            <textarea class="message-input" id="messageInput" 
                                      placeholder="Type your message here..." 
                                      onkeydown="handleKeyPress(event)"></textarea>
                            <button class="send-btn" onclick="sendMessage()">
                                <span>Send</span>
                                <span>✈️</span>
                            </button>
                        </div>
                        
                        <div class="quick-actions" style="margin-top: 15px;">
                            <button class="quick-action-btn" onclick="insertTemplate('meeting_request')">
                                <span>📅</span> Meeting Request
                            </button>
                            <button class="quick-action-btn" onclick="insertTemplate('contract_terms')">
                                <span>📄</span> Contract Terms
                            </button>
                            <button class="quick-action-btn" onclick="insertTemplate('scouting_report')">
                                <span>🔍</span> Scouting Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="dashboard-nav-mobile">
            <a href="agent_dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="agent_players.php" class="menu-item">
                <span class="menu-icon">👥</span>
                <span>Players</span>
            </a>
            <a href="agent_messages.php" class="menu-item active">
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
        // Store read conversations in localStorage
        function getReadConversations() {
            const read = localStorage.getItem('readConversations');
            return read ? JSON.parse(read) : [];
        }
        
        function saveReadConversations(readArray) {
            localStorage.setItem('readConversations', JSON.stringify(readArray));
        }
        
        // Mark as read when selected
        function selectConversation(conversationId) {
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('conversation', conversationId);
            window.history.pushState({}, '', url);
            
            // Update UI
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
                if (parseInt(item.dataset.conversationId) === conversationId) {
                    item.classList.add('active');
                    item.classList.remove('unread');
                    
                    // Remove unread badge
                    const badge = item.querySelector('.unread-badge');
                    if (badge) badge.remove();
                }
            });
            
            // Mark as read in localStorage
            const readConversations = getReadConversations();
            if (!readConversations.includes(conversationId)) {
                readConversations.push(conversationId);
                saveReadConversations(readConversations);
                updateUnreadCount();
            }
            
            // Load conversation messages
            loadConversationMessages(conversationId);
        }
        
        function loadConversationMessages(conversationId) {
            const messagesArea = document.getElementById('messagesArea');
            
            if (conversationId == 1) {
                // Show sample conversation
                messagesArea.innerHTML = `
                    <div class="message received">
                        <div class="message-text">Hi, when is my next trial scheduled? I want to make sure I'm prepared.</div>
                        <div class="message-time">2:30 PM</div>
                    </div>
                    <div class="message sent">
                        <div class="message-text">It's scheduled for next Friday at 10 AM at the Carrington Training Ground. Don't forget to bring your boots for both grass and artificial turf.</div>
                        <div class="message-time">2:45 PM</div>
                    </div>
                    <div class="message received">
                        <div class="message-text">Great! Will there be any specific drills I should practice beforehand?</div>
                        <div class="message-time">2:50 PM</div>
                    </div>
                    <div class="message sent">
                        <div class="message-text">Focus on your finishing and quick turns. They're looking for agility and goal-scoring ability. I'll send you a training plan tonight.</div>
                        <div class="message-time">3:00 PM</div>
                    </div>
                `;
            } else {
                // Show empty state with quick actions
                messagesArea.innerHTML = `
                    <div class="empty-chat">
                        <div class="empty-chat-icon">💬</div>
                        <div>
                            <h3>Start a Conversation</h3>
                            <p>Select a conversation from the list or compose a new message.</p>
                        </div>
                        
                        <div class="quick-actions">
                            <button class="quick-action-btn" onclick="sendQuickMessage('schedule_meeting')">
                                <span>📅</span> Schedule Meeting
                            </button>
                            <button class="quick-action-btn" onclick="sendQuickMessage('contract_update')">
                                <span>📝</span> Contract Update
                            </button>
                            <button class="quick-action-btn" onclick="sendQuickMessage('trial_info')">
                                <span>⚽</span> Trial Information
                            </button>
                            <button class="quick-action-btn" onclick="sendQuickMessage('payment_query')">
                                <span>💰</span> Payment Query
                            </button>
                        </div>
                    </div>
                `;
            }
            
            // Scroll to bottom
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }
        
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message) return;
            
            const messagesArea = document.getElementById('messagesArea');
            const timestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            // Add message to chat
            const messageElement = document.createElement('div');
            messageElement.className = 'message sent';
            messageElement.innerHTML = `
                <div class="message-text">${message}</div>
                <div class="message-time">${timestamp}</div>
            `;
            
            messagesArea.appendChild(messageElement);
            messageInput.value = '';
            
            // Scroll to bottom
            messagesArea.scrollTop = messagesArea.scrollHeight;
            
            // Auto-reply for demo purposes
            setTimeout(() => {
                const replies = [
                    "Thanks for the update. I'll review and get back to you.",
                    "Received. Let me check the schedule and confirm.",
                    "I appreciate the information. Let's discuss this further.",
                    "Noted. I'll follow up with the necessary parties.",
                    "Thank you for the message. I'll process this request."
                ];
                
                const randomReply = replies[Math.floor(Math.random() * replies.length)];
                const replyElement = document.createElement('div');
                replyElement.className = 'message received';
                replyElement.innerHTML = `
                    <div class="message-text">${randomReply}</div>
                    <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                `;
                
                messagesArea.appendChild(replyElement);
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }, 1000);
        }
        
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }
        
        function newMessage() {
            const recipient = prompt('Enter recipient name or email:');
            if (recipient) {
                const subject = prompt('Enter subject:');
                if (subject) {
                    const message = prompt('Enter your message:');
                    if (message) {
                        alert('New message composed!\n\nTo: ' + recipient + '\nSubject: ' + subject + '\n\nMessage: ' + message);
                    }
                }
            }
        }
        
        function callContact(conversationId) {
            alert('Calling contact for conversation #' + conversationId + '...\n\nIn a real system, this would initiate a voice or video call.');
        }
        
        function viewProfile(conversationId) {
            alert('Viewing profile for conversation #' + conversationId + '\n\nThis would show the contact\'s complete profile and history.');
        }
        
        function deleteConversation(conversationId) {
            if (confirm('Are you sure you want to delete this conversation?\n\nAll messages will be permanently deleted.')) {
                const item = document.querySelector(`[data-conversation-id="${conversationId}"]`);
                if (item) {
                    item.style.display = 'none';
                    alert('Conversation deleted!');
                    // Reset to first conversation
                    selectConversation(1);
                }
            }
        }
        
        function attachFile() {
            alert('File attachment dialog would open here.\n\nYou can attach contracts, scouting reports, or other documents.');
        }
        
        function sendQuickMessage(type) {
            const messages = {
                'schedule_meeting': "Hi, let's schedule a meeting to discuss the contract terms. What's your availability next week?",
                'contract_update': "I have an update on the contract negotiations. The club has agreed to improved terms.",
                'trial_info': "The trial details have been confirmed. I'll send you the schedule and requirements.",
                'payment_query': "Following up on the payment for last month's commission. Can you confirm the status?"
            };
            
            document.getElementById('messageInput').value = messages[type] || '';
        }
        
        function insertTemplate(template) {
            const templates = {
                'meeting_request': "Dear [Name],\n\nI would like to schedule a meeting to discuss [topic]. Please let me know your availability.\n\nBest regards,\n[Your Name]",
                'contract_terms': "Here are the key contract terms:\n1. Duration: [years]\n2. Salary: [amount]\n3. Bonuses: [details]\n4. Other benefits: [list]\n\nPlease review and confirm.",
                'scouting_report': "Scouting Report Summary:\n- Strengths: [list]\n- Areas for improvement: [list]\n- Recommendation: [text]\n- Potential value: [estimate]"
            };
            
            document.getElementById('messageInput').value = templates[template] || '';
        }
        
        function updateUnreadCount() {
            const unreadItems = document.querySelectorAll('.conversation-item.unread');
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
            const readConversations = getReadConversations();
            readConversations.forEach(id => {
                const item = document.querySelector(`[data-conversation-id="${id}"]`);
                if (item) {
                    item.classList.remove('unread');
                    const badge = item.querySelector('.unread-badge');
                    if (badge) badge.remove();
                }
            });
            updateUnreadCount();
            
            // Set active conversation from URL
            const urlParams = new URLSearchParams(window.location.search);
            const conversationId = urlParams.get('conversation');
            if (conversationId) {
                selectConversation(parseInt(conversationId));
            }
        });
        
        // Simulate new message notification
        setTimeout(function() {
            if (Math.random() > 0.7) {
                const contacts = ['New Club Inquiry', 'Player Agent Network', 'Transfer Market Update'];
                const randomContact = contacts[Math.floor(Math.random() * contacts.length)];
                
                if (confirm('New Message!\n\nYou have a new message from ' + randomContact + '. Would you like to view it now?')) {
                    // Add new conversation
                    alert('New conversation added to your messages.');
                }
            }
        }, 10000);
    </script>
</body>
</html>
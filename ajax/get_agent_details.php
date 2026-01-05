<?php
// ajax/get_agent_details.php
require_once '../config/config.php';
require_once '../auth/check_auth.php';

// Only allow admin access
if ($_SESSION['user_type'] !== 'admin') {
    die(json_encode(['error' => 'Unauthorized access']));
}

$agent_id = $_GET['id'] ?? 0;

if ($agent_id <= 0) {
    die('<p class="message-error">Invalid agent ID</p>');
}

$conn = getConnection();

// Get agent details
$stmt = $conn->prepare("
    SELECT a.*, u.full_name, u.email, u.phone, u.created_at as user_created,
           COUNT(DISTINCT pa.player_id) as player_count
    FROM agents a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN players_agents pa ON a.id = pa.agent_id
    WHERE a.id = ?
    GROUP BY a.id
");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$agent = $stmt->get_result()->fetch_assoc();

if (!$agent) {
    die('<p class="message-error">Agent not found</p>');
}

// Get players represented by this agent
$player_stmt = $conn->prepare("
    SELECT p.id, p.full_name, p.position, p.date_of_birth, p.nationality
    FROM players p
    JOIN players_agents pa ON p.id = pa.player_id
    WHERE pa.agent_id = ?
    ORDER BY p.full_name
");
$player_stmt->bind_param("i", $agent_id);
$player_stmt->execute();
$players_result = $player_stmt->get_result();
$players = $players_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<div class="agent-detail-grid">
    <div class="detail-item">
        <span class="detail-label">Full Name:</span>
        <span class="detail-value"><?php echo htmlspecialchars($agent['full_name']); ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Email:</span>
        <span class="detail-value"><?php echo htmlspecialchars($agent['email']); ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Phone:</span>
        <span class="detail-value"><?php echo htmlspecialchars($agent['phone'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Company:</span>
        <span class="detail-value"><?php echo htmlspecialchars($agent['company_name'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">License Number:</span>
        <span class="detail-value"><?php echo htmlspecialchars($agent['license_number']); ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">License Expiry:</span>
        <span class="detail-value"><?php echo date('F j, Y', strtotime($agent['license_expiry'] ?? 'N/A')); ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Status:</span>
        <span class="detail-value">
            <?php
            if ($agent['is_suspended']) {
                echo '<span class="status-badge status-suspended">Suspended</span>';
            } elseif ($agent['is_approved']) {
                echo '<span class="status-badge status-approved">Approved</span>';
            } else {
                echo '<span class="status-badge status-pending">Pending Approval</span>';
            }
            ?>
        </span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Players Represented:</span>
        <span class="detail-value"><?php echo $agent['player_count']; ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Years of Experience:</span>
        <span class="detail-value"><?php echo htmlspecialchars($agent['years_experience'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Specialization:</span>
        <span class="detail-value"><?php echo htmlspecialchars($agent['specialization'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">User Since:</span>
        <span class="detail-value"><?php echo date('F j, Y', strtotime($agent['user_created'])); ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Agent Since:</span>
        <span class="detail-value"><?php echo date('F j, Y', strtotime($agent['created_at'])); ?></span>
    </div>
</div>

<?php if (!empty($agent['bio'])): ?>
<div class="detail-item">
    <span class="detail-label">Bio:</span>
    <p class="detail-value"><?php echo nl2br(htmlspecialchars($agent['bio'])); ?></p>
</div>
<?php endif; ?>

<?php if (!empty($players)): ?>
<div class="players-list">
    <h4>Players Represented</h4>
    <?php foreach ($players as $player): ?>
    <div class="player-item">
        <div>
            <strong><?php echo htmlspecialchars($player['full_name']); ?></strong>
            <div style="color: #666; font-size: 14px;">
                <?php echo htmlspecialchars($player['position'] ?? 'Unknown Position'); ?> | 
                <?php echo htmlspecialchars($player['nationality'] ?? 'Unknown'); ?>
                <?php if ($player['date_of_birth']): ?>
                    | Age: <?php echo floor((time() - strtotime($player['date_of_birth'])) / 31556926); ?>
                <?php endif; ?>
            </div>
        </div>
        <a href="../admin/manage_players.php?view=<?php echo $player['id']; ?>" 
           class="action-btn btn-view" style="text-decoration: none;">
            <i class="fas fa-external-link-alt"></i> View Player
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="detail-item">
    <p>This agent doesn't represent any players yet.</p>
</div>
<?php endif; ?>
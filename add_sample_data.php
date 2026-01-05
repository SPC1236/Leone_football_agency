<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to include config
$config_path = 'config/config.php';
if (!file_exists($config_path)) {
    die("Config file not found at: $config_path");
}

require_once $config_path;

// Check if database connection works
try {
    $conn = getConnection();
    echo "<h1>✅ Database Connected Successfully</h1>";
} catch (Exception $e) {
    die("<h1>❌ Database Connection Failed:</h1><p>" . $e->getMessage() . "</p>");
}

echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
</style>";

echo "<h1>Adding Sample Data to Database</h1>";

// Sample players data
$players = [
    [
        'username' => 'john_kamara',
        'email' => 'john.kamara@example.com',
        'full_name' => 'John Kamara',
        'phone' => '+232 76 111 222',
        'position' => 'Striker',
        'age' => 23,
        'nationality' => 'Sierra Leonean',
        'current_club' => 'East End Lions',
        'height' => '182cm',
        'weight' => '75kg'
    ],
    [
        'username' => 'mohamed_bangura',
        'email' => 'mohamed.bangura@example.com',
        'full_name' => 'Mohamed Bangura',
        'phone' => '+232 76 222 333',
        'position' => 'Midfielder',
        'age' => 25,
        'nationality' => 'Sierra Leonean',
        'current_club' => 'Bo Rangers',
        'height' => '175cm',
        'weight' => '70kg'
    ],
    [
        'username' => 'sorie_kamara',
        'email' => 'sorie.kamara@example.com',
        'full_name' => 'Sorie Kamara',
        'phone' => '+232 76 333 444',
        'position' => 'Defender',
        'age' => 22,
        'nationality' => 'Sierra Leonean',
        'current_club' => 'Mighty Blackpool',
        'height' => '185cm',
        'weight' => '78kg'
    ]
];

// Sample agents data
$agents = [
    [
        'username' => 'david_johnson',
        'email' => 'david.johnson@example.com',
        'full_name' => 'David Johnson',
        'phone' => '+232 76 444 555',
        'license_number' => 'FIFA-AG-2023-001',
        'years_experience' => 8,
        'agency_name' => 'West Africa Sports Agency'
    ],
    [
        'username' => 'fatmata_koroma',
        'email' => 'fatmata.koroma@example.com',
        'full_name' => 'Fatmata Koroma',
        'phone' => '+232 76 555 666',
        'license_number' => 'FIFA-AG-2023-002',
        'years_experience' => 5,
        'agency_name' => 'Freetown Talent Management'
    ]
];

// Sample managers data
$managers = [
    [
        'username' => 'michael_stevens',
        'email' => 'michael.stevens@example.com',
        'full_name' => 'Michael Stevens',
        'phone' => '+232 76 666 777',
        'club_name' => 'Eastern United FC',
        'club_location' => 'Freetown',
        'club_league' => 'Sierra Leone Premier League',
        'club_type' => 'professional'
    ],
    [
        'username' => 'sarah_cole',
        'email' => 'sarah.cole@example.com',
        'full_name' => 'Sarah Cole',
        'phone' => '+232 76 777 888',
        'club_name' => 'Western Stars Academy',
        'club_location' => 'Bo',
        'club_league' => 'Youth Development League',
        'club_type' => 'academy'
    ]
];

// Add sample players
echo "<h2>Adding Sample Players</h2>";
foreach ($players as $player_data) {
    // Check if user already exists
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $player_data['username'], $player_data['email']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        // Create user
        $hashed_password = password_hash('player123', PASSWORD_DEFAULT);
        $user_sql = "INSERT INTO users (username, email, password, user_type, full_name, phone, status) 
                     VALUES (?, ?, ?, 'player', ?, ?, 'approved')";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("sssss", 
            $player_data['username'],
            $player_data['email'],
            $hashed_password,
            $player_data['full_name'],
            $player_data['phone']
        );
        
        if ($user_stmt->execute()) {
            $user_id = $conn->insert_id;
            echo "<p class='success'>✅ Created player user: {$player_data['full_name']}</p>";
            
            // Create player record
            $player_sql = "INSERT INTO players (user_id, position, age, nationality, current_club, height, weight) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $player_stmt = $conn->prepare($player_sql);
            $player_stmt->bind_param("issssss", 
                $user_id,
                $player_data['position'],
                $player_data['age'],
                $player_data['nationality'],
                $player_data['current_club'],
                $player_data['height'],
                $player_data['weight']
            );
            
            if ($player_stmt->execute()) {
                echo "<p class='success'>✅ Created player profile for: {$player_data['full_name']}</p>";
            } else {
                echo "<p class='error'>❌ Failed to create player profile: " . $player_stmt->error . "</p>";
            }
            $player_stmt->close();
        } else {
            echo "<p class='error'>❌ Failed to create user: " . $user_stmt->error . "</p>";
        }
        $user_stmt->close();
    } else {
        echo "<p class='warning'>⚠️ Player already exists: {$player_data['full_name']}</p>";
    }
    $check_stmt->close();
}

// Add sample agents
echo "<h2>Adding Sample Agents</h2>";
foreach ($agents as $agent_data) {
    // Check if user already exists
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $agent_data['username'], $agent_data['email']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        // Create user
        $hashed_password = password_hash('agent123', PASSWORD_DEFAULT);
        $user_sql = "INSERT INTO users (username, email, password, user_type, full_name, phone, status) 
                     VALUES (?, ?, ?, 'agent', ?, ?, 'approved')";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("sssss", 
            $agent_data['username'],
            $agent_data['email'],
            $hashed_password,
            $agent_data['full_name'],
            $agent_data['phone']
        );
        
        if ($user_stmt->execute()) {
            $user_id = $conn->insert_id;
            echo "<p class='success'>✅ Created agent user: {$agent_data['full_name']}</p>";
            
            // Create agent record
            $agent_sql = "INSERT INTO agents (user_id, license_number, years_experience, agency_name) 
                          VALUES (?, ?, ?, ?)";
            $agent_stmt = $conn->prepare($agent_sql);
            $agent_stmt->bind_param("isis", 
                $user_id,
                $agent_data['license_number'],
                $agent_data['years_experience'],
                $agent_data['agency_name']
            );
            
            if ($agent_stmt->execute()) {
                echo "<p class='success'>✅ Created agent profile for: {$agent_data['full_name']}</p>";
            } else {
                echo "<p class='error'>❌ Failed to create agent profile: " . $agent_stmt->error . "</p>";
            }
            $agent_stmt->close();
        } else {
            echo "<p class='error'>❌ Failed to create user: " . $user_stmt->error . "</p>";
        }
        $user_stmt->close();
    } else {
        echo "<p class='warning'>⚠️ Agent already exists: {$agent_data['full_name']}</p>";
    }
    $check_stmt->close();
}

// Add sample managers
echo "<h2>Adding Sample Club Managers</h2>";
foreach ($managers as $manager_data) {
    // Check if user already exists
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $manager_data['username'], $manager_data['email']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        // Create user
        $hashed_password = password_hash('manager123', PASSWORD_DEFAULT);
        $user_sql = "INSERT INTO users (username, email, password, user_type, full_name, phone, status) 
                     VALUES (?, ?, ?, 'manager', ?, ?, 'approved')";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("sssss", 
            $manager_data['username'],
            $manager_data['email'],
            $hashed_password,
            $manager_data['full_name'],
            $manager_data['phone']
        );
        
        if ($user_stmt->execute()) {
            $user_id = $conn->insert_id;
            echo "<p class='success'>✅ Created manager user: {$manager_data['full_name']}</p>";
            
            // Create manager record
            $manager_sql = "INSERT INTO club_managers (user_id, club_name, club_location, club_league, club_type) 
                            VALUES (?, ?, ?, ?, ?)";
            $manager_stmt = $conn->prepare($manager_sql);
            $manager_stmt->bind_param("issss", 
                $user_id,
                $manager_data['club_name'],
                $manager_data['club_location'],
                $manager_data['club_league'],
                $manager_data['club_type']
            );
            
            if ($manager_stmt->execute()) {
                echo "<p class='success'>✅ Created manager profile for: {$manager_data['full_name']}</p>";
            } else {
                echo "<p class='error'>❌ Failed to create manager profile: " . $manager_stmt->error . "</p>";
            }
            $manager_stmt->close();
        } else {
            echo "<p class='error'>❌ Failed to create user: " . $user_stmt->error . "</p>";
        }
        $user_stmt->close();
    } else {
        echo "<p class='warning'>⚠️ Manager already exists: {$manager_data['full_name']}</p>";
    }
    $check_stmt->close();
}

// Add some pending users for testing
echo "<h2>Adding Pending Users for Testing</h2>";

$pending_users = [
    ['username' => 'pending_player', 'email' => 'pending.player@example.com', 'full_name' => 'Pending Player', 'user_type' => 'player'],
    ['username' => 'pending_agent', 'email' => 'pending.agent@example.com', 'full_name' => 'Pending Agent', 'user_type' => 'agent'],
    ['username' => 'pending_manager', 'email' => 'pending.manager@example.com', 'full_name' => 'Pending Manager', 'user_type' => 'manager'],
];

foreach ($pending_users as $user_data) {
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $user_data['username'], $user_data['email']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        $hashed_password = password_hash('pending123', PASSWORD_DEFAULT);
        $user_sql = "INSERT INTO users (username, email, password, user_type, full_name, status) 
                     VALUES (?, ?, ?, ?, ?, 'pending')";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("sssss", 
            $user_data['username'],
            $user_data['email'],
            $hashed_password,
            $user_data['user_type'],
            $user_data['full_name']
        );
        
        if ($user_stmt->execute()) {
            echo "<p class='success'>✅ Created pending {$user_data['user_type']}: {$user_data['full_name']}</p>";
        } else {
            echo "<p class='error'>❌ Failed to create pending user: " . $user_stmt->error . "</p>";
        }
        $user_stmt->close();
    } else {
        echo "<p class='warning'>⚠️ Pending user already exists: {$user_data['full_name']}</p>";
    }
    $check_stmt->close();
}

$conn->close();

echo "<hr><h2>✅ Sample Data Added Successfully!</h2>";
echo "<p><strong>Test Accounts Created:</strong></p>";
echo "<ul>";
echo "<li><strong>Players:</strong> Username: john_kamara, Password: player123</li>";
echo "<li><strong>Agents:</strong> Username: david_johnson, Password: agent123</li>";
echo "<li><strong>Managers:</strong> Username: michael_stevens, Password: manager123</li>";
echo "</ul>";
echo "<p><strong>Pending Accounts (for approval testing):</strong></p>";
echo "<ul>";
echo "<li><strong>Player:</strong> Username: pending_player, Password: pending123</li>";
echo "<li><strong>Agent:</strong> Username: pending_agent, Password: pending123</li>";
echo "<li><strong>Manager:</strong> Username: pending_manager, Password: pending123</li>";
echo "</ul>";
echo "<p><a href='admin/index.php'>Go to Admin Dashboard</a> | <a href='index.php'>Go to Homepage</a></p>";
?>
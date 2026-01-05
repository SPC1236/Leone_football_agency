<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'leone_football_agency');

// Create database and tables if they don't exist
function initializeDatabase() {
    // First connect without database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === FALSE) {
        die("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    return $conn;
}

// Get connection to existing database
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Create tables if they don't exist
function createTables() {
    $conn = getConnection();
    
    // Users table (for all user types)
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        user_type ENUM('admin', 'player', 'agent', 'manager') NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        login_attempts INT(3) DEFAULT 0,
        last_login_attempt DATETIME DEFAULT NULL,
        profile_image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        die("Error creating users table: " . $conn->error);
    }
    
    // Players table
    $sql = "CREATE TABLE IF NOT EXISTS players (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        position VARCHAR(50),
        nationality VARCHAR(50),
        age INT(3),
        height VARCHAR(10),
        weight VARCHAR(10),
        preferred_foot ENUM('left', 'right', 'both'),
        current_club VARCHAR(100),
        previous_clubs TEXT,
        achievements TEXT,
        video_url VARCHAR(255),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        die("Error creating players table: " . $conn->error);
    }
    
    // Agents table
    $sql = "CREATE TABLE IF NOT EXISTS agents (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        license_number VARCHAR(50),
        years_experience INT(3),
        represented_players TEXT,
        agency_name VARCHAR(100),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        die("Error creating agents table: " . $conn->error);
    }
    
    // Club Managers table
    $sql = "CREATE TABLE IF NOT EXISTS club_managers (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        club_name VARCHAR(100),
        club_location VARCHAR(100),
        club_league VARCHAR(100),
        club_type ENUM('professional', 'academy', 'amateur'),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        die("Error creating club_managers table: " . $conn->error);
    }
    
    // Contacts table
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        subject VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($sql)) {
        die("Error creating contacts table: " . $conn->error);
    }
    
    // Check if admin exists, if not create one
    $sql = "SELECT id FROM users WHERE username = 'admin' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, user_type, full_name, status) 
                VALUES ('admin', 'admin@freetownfa.com', '$admin_password', 'admin', 'Administrator', 'approved')";
        
        if (!$conn->query($sql)) {
            die("Error creating admin user: " . $conn->error);
        }
        
        // Get admin ID for sample players
        $admin_id = $conn->insert_id;
        
        // Insert sample players for homepage
        $sql = "INSERT INTO players (user_id, position, nationality, age, height, weight, current_club) 
                VALUES 
                ($admin_id, 'Striker', 'Sierra Leonean', 23, '182cm', '75kg', 'East End Lions'),
                ($admin_id, 'Midfielder', 'Sierra Leonean', 25, '175cm', '70kg', 'Bo Rangers'),
                ($admin_id, 'Defender', 'Sierra Leonean', 22, '185cm', '78kg', 'Mighty Blackpool')";
        
        $conn->query($sql);
    }
    
    $conn->close();
    
    return true;
}

// Initialize database and tables
function initDatabase() {
    // Create connection without selecting database first
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === FALSE) {
        die("Error creating database: " . $conn->error);
    }
    
    $conn->close();
    
    // Now create tables
    createTables();
}

// Call initialization on first run
initDatabase();
?>
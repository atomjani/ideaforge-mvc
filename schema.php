<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/app/core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

echo "<h1>Database Schema Update</h1>";

try {
    // Update ideas table
    $pdo->exec("ALTER TABLE ideas ADD COLUMN IF NOT EXISTS problem TEXT");
    $pdo->exec("ALTER TABLE ideas ADD COLUMN IF NOT EXISTS target_audience VARCHAR(255)");
    $pdo->exec("ALTER TABLE ideas ADD COLUMN IF NOT EXISTS phase VARCHAR(50) DEFAULT 'MVP_CREATION'");
    $pdo->exec("UPDATE ideas SET phase = 'MVP_CREATION' WHERE phase IS NULL OR phase = ''");
    $pdo->exec("ALTER TABLE ideas MODIFY COLUMN phase VARCHAR(50) NOT NULL DEFAULT 'MVP_CREATION'");
    $pdo->exec("ALTER TABLE ideas ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL");
    $pdo->exec("ALTER TABLE ideas ADD COLUMN IF NOT EXISTS tags JSON");
    $pdo->exec("ALTER TABLE ideas ADD COLUMN IF NOT EXISTS is_public TINYINT DEFAULT 0");
    $pdo->exec("ALTER TABLE ideas ADD COLUMN IF NOT EXISTS share_token VARCHAR(32)");
    $pdo->exec("ALTER TABLE ideas ADD COLUMN IF NOT EXISTS share_type VARCHAR(20) DEFAULT 'private'");
    $pdo->exec("ALTER TABLE ideas ADD COLUMN IF NOT EXISTS specific_emails TEXT");
    $pdo->exec("UPDATE ideas SET share_type = CASE WHEN is_public = 1 THEN 'public' ELSE 'private' END WHERE share_type IS NULL OR share_type = ''");
    echo "<p>✅ ideas table updated</p>";
} catch (Exception $e) {
    echo "<p>⚠️ ideas: " . $e->getMessage() . "</p>";
}

try {
    // Create tasks table properly
    $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
        id VARCHAR(36) PRIMARY KEY,
        idea_id VARCHAR(36),
        name VARCHAR(255),
        description TEXT,
        priority VARCHAR(20) DEFAULT 'MUST_HAVE',
        status VARCHAR(20) DEFAULT 'BACKLOG',
        module VARCHAR(100),
        type VARCHAR(20) DEFAULT 'FEATURE',
        ice_impact INT DEFAULT 5,
        ice_confidence INT DEFAULT 5,
        ice_ease INT DEFAULT 5,
        is_implemented TINYINT DEFAULT 0,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE
    )");
    $pdo->exec("ALTER TABLE tasks ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL");
    $pdo->exec("ALTER TABLE tasks ADD COLUMN IF NOT EXISTS name VARCHAR(255)");
    echo "<p>✅ tasks table created</p>";
} catch (Exception $e) {
    echo "<p>⚠️ tasks: " . $e->getMessage() . "</p>";
}

try {
    // Create comments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id VARCHAR(36) PRIMARY KEY,
        task_id VARCHAR(36),
        user_id VARCHAR(36),
        text TEXT,
        created_at TIMESTAMP NULL,
        FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $pdo->exec("ALTER TABLE comments ADD COLUMN IF NOT EXISTS user_id VARCHAR(36)");
    echo "<p>✅ comments table created</p>";
} catch (Exception $e) {
    echo "<p>⚠️ comments: " . $e->getMessage() . "</p>";
}

try {
    // Create feedbacks table
    $pdo->exec("CREATE TABLE IF NOT EXISTS feedbacks (
        id VARCHAR(36) PRIMARY KEY,
        user_id VARCHAR(36),
        message TEXT,
        type VARCHAR(20) DEFAULT 'opinion',
        rating_overall INT DEFAULT 0,
        rating_ideas INT DEFAULT 0,
        rating_tasks INT DEFAULT 0,
        rating_ui INT DEFAULT 0,
        created_at TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $pdo->exec("ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS message TEXT");
    $pdo->exec("ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS rating_overall INT DEFAULT 0");
    $pdo->exec("ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS rating_ideas INT DEFAULT 0");
    $pdo->exec("ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS rating_tasks INT DEFAULT 0");
    $pdo->exec("ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS rating_ui INT DEFAULT 0");
    echo "<p>✅ feedbacks table created</p>";
} catch (Exception $e) {
    echo "<p>⚠️ feedbacks: " . $e->getMessage() . "</p>";
}

try {
    // Create newsletter tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
        id VARCHAR(36) PRIMARY KEY,
        email VARCHAR(255) UNIQUE,
        name VARCHAR(255),
        type VARCHAR(20) DEFAULT 'public',
        unsubscribed_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_campaigns (
        id VARCHAR(36) PRIMARY KEY,
        subject VARCHAR(255),
        body TEXT,
        recipients_type VARCHAR(20),
        recipient_count INT DEFAULT 0,
        sent_count INT DEFAULT 0,
        created_at TIMESTAMP NULL
    )");
    
    echo "<p>✅ newsletter tables created</p>";
} catch (Exception $e) {
    echo "<p>⚠️ newsletter: " . $e->getMessage() . "</p>";
}

try {
    // Create activity_log table
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
        id VARCHAR(36) PRIMARY KEY,
        user_id VARCHAR(36),
        action VARCHAR(50),
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP NULL
    )");
    
    echo "<p>✅ activity_log table created</p>";
} catch (Exception $e) {
    echo "<p>⚠️ activity_log: " . $e->getMessage() . "</p>";
}

try {
    // Create settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id VARCHAR(36) PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE,
        setting_value TEXT,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )");
    echo "<p>✅ settings table created</p>";
} catch (Exception $e) {
    echo "<p>⚠️ settings: " . $e->getMessage() . "</p>";
}

try {
    // Create analytics table
    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics (
        id VARCHAR(36) PRIMARY KEY,
        session_id VARCHAR(64),
        user_id VARCHAR(36),
        ip_address VARCHAR(45),
        user_agent TEXT,
        referer VARCHAR(500),
        current_url VARCHAR(500),
        page_name VARCHAR(100),
        time_on_page INT DEFAULT 0,
        event_type VARCHAR(50) DEFAULT 'pageview',
        created_at TIMESTAMP NULL
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_analytics_session ON analytics(session_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_analytics_created ON analytics(created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_analytics_page ON analytics(page_name)");
    echo "<p>✅ analytics table created</p>";
} catch (Exception $e) {
    echo "<p>⚠️ analytics: " . $e->getMessage() . "</p>";
}

echo "<h2>Done!</h2>";
echo "<p><a href='/'>Go to homepage</a></p>";

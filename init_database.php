<?php
try {
    $db = new PDO('sqlite:dictionary.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create dictionary table
    $db->exec("CREATE TABLE IF NOT EXISTS dictionary (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        word TEXT NOT NULL UNIQUE,
        translation TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create users table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert initial dictionary data
    $stmt = $db->prepare("INSERT OR IGNORE INTO dictionary (word, translation) VALUES (?, ?)");
    $stmt->execute(['hello', '你好']);
    $stmt->execute(['test', '测试']);
    
    // Insert initial admin user
    $stmt = $db->prepare("INSERT OR IGNORE INTO users (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', password_hash('password', PASSWORD_DEFAULT)]);
    
    echo "Database initialized successfully!\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
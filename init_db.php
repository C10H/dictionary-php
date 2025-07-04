<?php
$db = new SQLite3('dictionary.db');

// Create dictionary table
$db->exec("CREATE TABLE IF NOT EXISTS dictionary (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    word TEXT NOT NULL,
    translation TEXT NOT NULL
)");

// Create users table
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
)");

// Insert initial dictionary data
$db->exec("INSERT OR IGNORE INTO dictionary (word, translation) VALUES ('hello', '你好')");
$db->exec("INSERT OR IGNORE INTO dictionary (word, translation) VALUES ('test', '测试')");

// Insert initial admin user
$db->exec("INSERT OR IGNORE INTO users (username, password) VALUES ('admin', 'password')");

echo "Database initialized successfully!";
$db->close();
?>
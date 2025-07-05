<?php
// MySQL版本的数据库初始化脚本

$host = 'localhost';
$dbname = 'dictionary_db';
$username = 'dictionary_user';
$password = 'dictionary_pass';

echo "=== MySQL数据库初始化 ===\n\n";

try {
    // 连接MySQL服务器（不指定数据库）
    $pdo = new PDO("mysql:host=$host", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "1. 连接MySQL服务器成功\n";
    
    // 创建数据库
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "2. 创建数据库: $dbname\n";
    
    // 创建用户并授权
    $pdo->exec("CREATE USER IF NOT EXISTS '$username'@'localhost' IDENTIFIED BY '$password'");
    $pdo->exec("GRANT ALL PRIVILEGES ON $dbname.* TO '$username'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    echo "3. 创建用户: $username\n";
    
    // 连接到新创建的数据库
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "4. 连接到词典数据库成功\n";
    
    // 创建词典表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS dictionary (
            id INT AUTO_INCREMENT PRIMARY KEY,
            word VARCHAR(255) NOT NULL,
            translation TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_word (word)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    echo "5. 创建词典表成功\n";
    
    // 创建用户表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    echo "6. 创建用户表成功\n";
    
    // 插入初始词典数据
    $stmt = $pdo->prepare("INSERT IGNORE INTO dictionary (word, translation) VALUES (?, ?)");
    $stmt->execute(['hello', '你好']);
    $stmt->execute(['test', '测试']);
    $stmt->execute(['world', '世界']);
    $stmt->execute(['computer', '计算机']);
    echo "7. 插入初始词典数据\n";
    
    // 插入初始用户数据（密码使用hash）
    $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $hashedPassword]);
    echo "8. 创建管理员用户\n";
    
    echo "\n=== MySQL数据库初始化完成 ===\n";
    echo "数据库: $dbname\n";
    echo "用户: $username\n";
    echo "密码: $password\n";
    echo "管理员: admin / password\n";
    
} catch (PDOException $e) {
    echo "错误: " . $e->getMessage() . "\n";
    exit(1);
}
?>
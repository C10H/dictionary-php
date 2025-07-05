<?php
// 数据库配置文件

// 数据库类型配置 ('sqlite' 或 'mysql')
define('DB_TYPE', 'sqlite'); // 默认使用SQLite，可以改为'mysql'

// SQLite配置
define('SQLITE_DB_PATH', __DIR__ . '/dictionary.db');

// MySQL配置
define('MYSQL_HOST', 'localhost');
define('MYSQL_DBNAME', 'dictionary_db');
define('MYSQL_USERNAME', 'dictionary_user');
define('MYSQL_PASSWORD', 'dictionary_pass');

// 百度翻译API配置
define('BAIDU_APP_ID', '20240531002066782');
define('BAIDU_SECRET_KEY', '2UYrEDwvtMgOShDLo3u8');

/**
 * 获取数据库连接
 */
function getDatabase() {
    try {
        if (DB_TYPE === 'mysql') {
            $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DBNAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } else {
            // 默认使用SQLite
            $pdo = new PDO('sqlite:' . SQLITE_DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }
    } catch (PDOException $e) {
        die("数据库连接失败: " . $e->getMessage());
    }
}

/**
 * 检查用户认证（兼容MySQL和SQLite）
 */
function authenticateUser($username, $password) {
    $db = getDatabase();
    
    if (DB_TYPE === 'mysql') {
        // MySQL版本 - 使用password_verify
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return true;
        }
    } else {
        // SQLite版本 - 明文密码比较（为了兼容现有数据）
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        $stmt->execute([$username, $password]);
        
        if ($stmt->fetch()) {
            return true;
        }
    }
    
    return false;
}

/**
 * 查询词典
 */
function queryDictionary($word) {
    $db = getDatabase();
    $stmt = $db->prepare("SELECT translation FROM dictionary WHERE word = ?");
    $stmt->execute([$word]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['translation'] : null;
}

/**
 * 添加词条
 */
function addDictionaryEntry($word, $translation) {
    $db = getDatabase();
    $stmt = $db->prepare("INSERT INTO dictionary (word, translation) VALUES (?, ?)");
    return $stmt->execute([$word, $translation]);
}

/**
 * 更新词条
 */
function updateDictionaryEntry($id, $word, $translation) {
    $db = getDatabase();
    $stmt = $db->prepare("UPDATE dictionary SET word = ?, translation = ? WHERE id = ?");
    return $stmt->execute([$word, $translation, $id]);
}

/**
 * 删除词条
 */
function deleteDictionaryEntry($id) {
    $db = getDatabase();
    $stmt = $db->prepare("DELETE FROM dictionary WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * 获取所有词条
 */
function getAllDictionaryEntries() {
    $db = getDatabase();
    $stmt = $db->query("SELECT * FROM dictionary ORDER BY word");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
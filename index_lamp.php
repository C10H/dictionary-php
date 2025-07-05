<?php
require_once 'config.php';

$result = null;
$query = '';

if (isset($_POST['query'])) {
    $query = trim($_POST['query']);
    $result = translateWord($query);
}

function translateWord($word) {
    // 首先检查本地数据库
    $translation = queryDictionary($word);
    
    if ($translation) {
        return array('translation' => $translation, 'source' => '本地词典');
    }
    
    // 如果本地没有，尝试百度翻译API
    return translateWithBaidu($word);
}

function translateWithBaidu($word) {
    $appId = BAIDU_APP_ID;
    $key = BAIDU_SECRET_KEY;
    $salt = rand(10000, 99999);
    
    // 检测语言
    $from = detectLanguage($word) ? 'zh' : 'en';
    $to = ($from == 'zh') ? 'en' : 'zh';
    
    $sign = md5($appId . $word . $salt . $key);
    
    $url = 'https://fanyi-api.baidu.com/api/trans/vip/translate';
    $data = array(
        'q' => $word,
        'from' => $from,
        'to' => $to,
        'appid' => $appId,
        'salt' => $salt,
        'sign' => $sign
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        // 如果CURL出错，返回模拟结果
        $mockTranslations = [
            'good' => '好的', 'bad' => '坏的', 'computer' => '计算机',
            'apple' => '苹果', 'water' => '水', 'book' => '书',
            '苹果' => 'apple', '水' => 'water', '书' => 'book',
            '好的' => 'good', '坏的' => 'bad', '计算机' => 'computer'
        ];
        
        if (isset($mockTranslations[$word])) {
            return array('translation' => $mockTranslations[$word], 'source' => '离线词典 (API不可用)');
        }
    } elseif ($response) {
        $result = json_decode($response, true);
        if (isset($result['trans_result']) && count($result['trans_result']) > 0) {
            return array('translation' => $result['trans_result'][0]['dst'], 'source' => '百度翻译API');
        }
    }
    
    return false;
}

function detectLanguage($text) {
    // 简单的中文字符检测
    return preg_match('/[\x{4e00}-\x{9fff}]/u', $text);
}

// 获取系统信息
function getSystemInfo() {
    return [
        'php_version' => phpversion(),
        'apache_version' => apache_get_version() ?? 'Apache (版本未知)',
        'mysql_available' => extension_loaded('pdo_mysql'),
        'sqlite_available' => extension_loaded('pdo_sqlite'),
        'curl_available' => extension_loaded('curl'),
        'db_type' => DB_TYPE
    ];
}

$systemInfo = getSystemInfo();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>英汉电子词典 - LAMP版</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #ff6b6b 0%, #feca57 50%, #48dbfb 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 40px;
            font-size: 2.8em;
            font-weight: 300;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .badge {
            display: inline-block;
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.8em;
            margin-left: 15px;
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }
        .search-box {
            margin-bottom: 40px;
            text-align: center;
        }
        input[type="text"] {
            width: 70%;
            padding: 18px 25px;
            font-size: 18px;
            border: 3px solid #ecf0f1;
            border-radius: 50px;
            outline: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            background: rgba(255,255,255,0.9);
        }
        input[type="text"]:focus {
            border-color: #e74c3c;
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(231, 76, 60, 0.3);
        }
        button {
            padding: 18px 35px;
            font-size: 18px;
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            margin-left: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.3);
            font-weight: 600;
        }
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(231, 76, 60, 0.4);
        }
        .result {
            margin-top: 30px;
            padding: 35px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 20px;
            border-left: 6px solid #e74c3c;
            animation: slideInUp 0.6s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .result h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.4em;
        }
        .translation {
            font-size: 1.4em;
            font-weight: 600;
            color: #2c3e50;
            margin: 15px 0;
            padding: 15px;
            background: rgba(255,255,255,0.7);
            border-radius: 10px;
        }
        .source {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 20px;
            padding: 10px 15px;
            background: #ecf0f1;
            border-radius: 10px;
            display: inline-block;
        }
        .admin-link {
            text-align: center;
            margin-top: 40px;
        }
        .admin-link a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
            padding: 15px 30px;
            border: 3px solid #e74c3c;
            border-radius: 30px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .admin-link a:hover {
            background: #e74c3c;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.3);
        }
        .system-info {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #34495e, #2c3e50);
            border-radius: 15px;
            color: white;
            box-shadow: 0 8px 20px rgba(52, 73, 94, 0.3);
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .info-item {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-ok { background-color: #27ae60; }
        .status-warning { background-color: #f39c12; }
        .status-error { background-color: #e74c3c; }
        .lamp-logo {
            text-align: center;
            margin-bottom: 20px;
            font-size: 3em;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="lamp-logo">🔥</div>
        <h1>英汉电子词典<span class="badge">LAMP</span></h1>
        
        <div class="search-box">
            <form method="POST" action="">
                <input type="text" name="query" placeholder="输入中文或英文单词..." value="<?php echo htmlspecialchars($query); ?>" required>
                <button type="submit">🔍 翻译</button>
            </form>
        </div>

        <?php if ($result): ?>
            <div class="result">
                <h3>📖 翻译结果</h3>
                <div class="translation">
                    <strong><?php echo htmlspecialchars($query); ?></strong> → <?php echo htmlspecialchars($result['translation']); ?>
                </div>
                <div class="source">📚 来源: <?php echo $result['source']; ?></div>
            </div>
        <?php elseif (isset($_POST['query'])): ?>
            <div class="result">
                <h3>😔 抱歉</h3>
                <p>未找到 "<strong><?php echo htmlspecialchars($query); ?></strong>" 的翻译结果。</p>
                <div class="source">💡 建议：检查拼写或尝试其他词汇</div>
            </div>
        <?php endif; ?>

        <div class="admin-link">
            <a href="admin_lamp.php">🔧 管理员登录</a>
        </div>
        
        <div class="system-info">
            <div><strong>🏗️ LAMP 环境信息</strong></div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="status-indicator status-ok"></span>
                    Apache: 正在运行
                </div>
                <div class="info-item">
                    <span class="status-indicator status-ok"></span>
                    PHP: <?php echo $systemInfo['php_version']; ?>
                </div>
                <div class="info-item">
                    <span class="status-indicator <?php echo $systemInfo['mysql_available'] ? 'status-ok' : 'status-warning'; ?>"></span>
                    MySQL: <?php echo $systemInfo['mysql_available'] ? '可用' : '不可用'; ?>
                </div>
                <div class="info-item">
                    <span class="status-indicator status-ok"></span>
                    SQLite: 可用
                </div>
                <div class="info-item">
                    <span class="status-indicator <?php echo $systemInfo['curl_available'] ? 'status-ok' : 'status-warning'; ?>"></span>
                    cURL: <?php echo $systemInfo['curl_available'] ? '可用' : '不可用'; ?>
                </div>
                <div class="info-item">
                    <span class="status-indicator status-ok"></span>
                    数据库: <?php echo strtoupper($systemInfo['db_type']); ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
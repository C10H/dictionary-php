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
    curl_close($ch);
    
    if ($response) {
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
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>英汉电子词典 - LNMP版</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        h1 {
            text-align: center;
            color: #4a5568;
            margin-bottom: 40px;
            font-size: 2.5em;
            font-weight: 300;
        }
        .badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        .search-box {
            margin-bottom: 40px;
            text-align: center;
        }
        input[type="text"] {
            width: 70%;
            padding: 15px 20px;
            font-size: 18px;
            border: 3px solid #e2e8f0;
            border-radius: 50px;
            outline: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        input[type="text"]:focus {
            border-color: #4CAF50;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(76, 175, 80, 0.3);
        }
        button {
            padding: 15px 30px;
            font-size: 18px;
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            margin-left: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }
        .result {
            margin-top: 30px;
            padding: 30px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 15px;
            border-left: 6px solid #4CAF50;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .result h3 {
            color: #2d3748;
            margin-bottom: 15px;
        }
        .translation {
            font-size: 1.3em;
            font-weight: 500;
            color: #1a202c;
            margin: 10px 0;
        }
        .source {
            font-size: 14px;
            color: #718096;
            margin-top: 15px;
            padding: 8px 12px;
            background: #edf2f7;
            border-radius: 8px;
            display: inline-block;
        }
        .admin-link {
            text-align: center;
            margin-top: 40px;
        }
        .admin-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border: 2px solid #4CAF50;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .admin-link a:hover {
            background: #4CAF50;
            color: white;
            transform: translateY(-2px);
        }
        .system-info {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: #f7fafc;
            border-radius: 10px;
            font-size: 14px;
            color: #4a5568;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>英汉电子词典<span class="badge">LNMP版</span></h1>
        
        <div class="search-box">
            <form method="POST" action="">
                <input type="text" name="query" placeholder="输入中文或英文单词..." value="<?php echo htmlspecialchars($query); ?>" required>
                <button type="submit">🔍 翻译</button>
            </form>
        </div>

        <?php if ($result): ?>
            <div class="result">
                <h3>翻译结果</h3>
                <div class="translation">
                    <strong><?php echo htmlspecialchars($query); ?></strong> → <?php echo htmlspecialchars($result['translation']); ?>
                </div>
                <div class="source">📚 来源: <?php echo $result['source']; ?></div>
            </div>
        <?php elseif (isset($_POST['query'])): ?>
            <div class="result">
                <h3>抱歉</h3>
                <p>未找到 "<strong><?php echo htmlspecialchars($query); ?></strong>" 的翻译结果。</p>
                <div class="source">💡 建议：检查拼写或尝试其他词汇</div>
            </div>
        <?php endif; ?>

        <div class="admin-link">
            <a href="admin_lnmp.php">🔧 管理员登录</a>
        </div>
        
        <div class="system-info">
            <div>🏗️ 运行环境: LNMP (Linux + Nginx + MySQL + PHP)</div>
            <div>💾 数据库: <?php echo strtoupper(DB_TYPE); ?></div>
            <div>🚀 服务器: Nginx + PHP-FPM</div>
        </div>
    </div>
</body>
</html>
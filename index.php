<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>英汉电子词典</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .search-box {
            margin-bottom: 30px;
        }
        input[type="text"] {
            width: 70%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 5px;
            outline: none;
        }
        input[type="text"]:focus {
            border-color: #4CAF50;
        }
        button {
            padding: 12px 24px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .result {
            margin-top: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }
        .source {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        .admin-link {
            text-align: center;
            margin-top: 30px;
        }
        .admin-link a {
            color: #4CAF50;
            text-decoration: none;
        }
        .admin-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>英汉电子词典</h1>
        
        <div class="search-box">
            <form method="POST" action="">
                <input type="text" name="query" placeholder="输入中文或英文单词..." value="<?php echo isset($_POST['query']) ? htmlspecialchars($_POST['query']) : ''; ?>" required>
                <button type="submit">翻译</button>
            </form>
        </div>

        <?php
        if (isset($_POST['query'])) {
            $query = trim($_POST['query']);
            $result = translateWord($query);
            
            if ($result) {
                echo '<div class="result">';
                echo '<h3>翻译结果:</h3>';
                echo '<p><strong>' . htmlspecialchars($query) . '</strong> → ' . htmlspecialchars($result['translation']) . '</p>';
                echo '<div class="source">来源: ' . $result['source'] . '</div>';
                echo '</div>';
            } else {
                echo '<div class="result">';
                echo '<p>抱歉，未找到翻译结果。</p>';
                echo '</div>';
            }
        }

        function translateWord($word) {
            // First check local database
            $db = new SQLite3('dictionary.db');
            $stmt = $db->prepare("SELECT translation FROM dictionary WHERE word = ?");
            $stmt->bindValue(1, $word);
            $result = $stmt->execute();
            
            if ($row = $result->fetchArray()) {
                $db->close();
                return array('translation' => $row['translation'], 'source' => '本地词典');
            }
            
            $db->close();
            
            // If not found in local database, try Baidu API
            return translateWithBaidu($word);
        }

        function translateWithBaidu($word) {
            $appId = '20240531002066782';
            $key = '2UYrEDwvtMgOShDLo3u8';
            $salt = rand(10000, 99999);
            
            // Detect language
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
            // Simple Chinese character detection
            return preg_match('/[\x{4e00}-\x{9fff}]/u', $text);
        }
        ?>

        <div class="admin-link">
            <a href="admin.php">管理员登录</a>
        </div>
    </div>
</body>
</html>
<?php
echo "===============================\n";
echo "   英汉电子词典系统测试报告\n";
echo "===============================\n\n";

$totalTests = 0;
$passedTests = 0;

function runTest($testName, $testFunction) {
    global $totalTests, $passedTests;
    $totalTests++;
    echo "测试 {$totalTests}: {$testName}\n";
    
    try {
        $result = $testFunction();
        if ($result) {
            echo "✓ 通过\n";
            $passedTests++;
        } else {
            echo "✗ 失败\n";
        }
    } catch (Exception $e) {
        echo "✗ 错误: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// 测试1: 数据库连接
runTest("数据库连接", function() {
    $db = new SQLite3('dictionary.db');
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = [];
    while ($row = $result->fetchArray()) {
        $tables[] = $row['name'];
    }
    $db->close();
    return in_array('dictionary', $tables) && in_array('users', $tables);
});

// 测试2: 初始数据验证
runTest("初始数据验证", function() {
    $db = new SQLite3('dictionary.db');
    
    // 检查词典数据
    $result = $db->query("SELECT COUNT(*) as count FROM dictionary");
    $dictCount = $result->fetchArray()['count'];
    
    // 检查用户数据
    $result = $db->query("SELECT COUNT(*) as count FROM users");
    $userCount = $result->fetchArray()['count'];
    
    $db->close();
    return $dictCount >= 2 && $userCount >= 1;
});

// 测试3: 本地词典查询
runTest("本地词典查询", function() {
    $db = new SQLite3('dictionary.db');
    
    $stmt = $db->prepare("SELECT translation FROM dictionary WHERE word = ?");
    $stmt->bindValue(1, 'hello');
    $result = $stmt->execute();
    $translation = $result->fetchArray()['translation'];
    
    $db->close();
    return $translation === '你好';
});

// 测试4: 语言检测
runTest("中英文检测", function() {
    function detectLanguage($text) {
        return preg_match('/[\x{4e00}-\x{9fff}]/u', $text);
    }
    
    $englishTest = !detectLanguage('hello');
    $chineseTest = detectLanguage('你好');
    
    return $englishTest && $chineseTest;
});

// 测试5: 管理员认证
runTest("管理员认证", function() {
    $db = new SQLite3('dictionary.db');
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bindValue(1, 'admin');
    $stmt->bindValue(2, 'password');
    $result = $stmt->execute();
    
    $authenticated = $result->fetchArray() !== false;
    $db->close();
    
    return $authenticated;
});

// 测试6: 词条增删改
runTest("词条管理操作", function() {
    $db = new SQLite3('dictionary.db');
    
    // 添加测试词条
    $stmt = $db->prepare("INSERT INTO dictionary (word, translation) VALUES (?, ?)");
    $stmt->bindValue(1, 'test_word');
    $stmt->bindValue(2, '测试词');
    $addResult = $stmt->execute();
    
    if (!$addResult) {
        $db->close();
        return false;
    }
    
    // 查询测试词条
    $stmt = $db->prepare("SELECT translation FROM dictionary WHERE word = ?");
    $stmt->bindValue(1, 'test_word');
    $result = $stmt->execute();
    $queryResult = $result->fetchArray();
    
    if (!$queryResult || $queryResult['translation'] !== '测试词') {
        $db->close();
        return false;
    }
    
    // 更新测试词条
    $stmt = $db->prepare("UPDATE dictionary SET translation = ? WHERE word = ?");
    $stmt->bindValue(1, '更新测试词');
    $stmt->bindValue(2, 'test_word');
    $updateResult = $stmt->execute();
    
    if (!$updateResult) {
        $db->close();
        return false;
    }
    
    // 删除测试词条
    $stmt = $db->prepare("DELETE FROM dictionary WHERE word = ?");
    $stmt->bindValue(1, 'test_word');
    $deleteResult = $stmt->execute();
    
    $db->close();
    return $deleteResult;
});

// 测试7: 文件结构检查
runTest("文件结构完整性", function() {
    $requiredFiles = [
        'index.php',
        'admin.php', 
        'admin_panel.php',
        'logout.php',
        'dictionary.db'
    ];
    
    foreach ($requiredFiles as $file) {
        if (!file_exists($file)) {
            return false;
        }
    }
    
    return true;
});

// 测试8: PHP语法检查
runTest("PHP语法检查", function() {
    $phpFiles = ['index.php', 'admin.php', 'admin_panel.php', 'logout.php'];
    
    foreach ($phpFiles as $file) {
        $output = shell_exec("php -l {$file} 2>&1");
        if (strpos($output, 'No syntax errors') === false) {
            return false;
        }
    }
    
    return true;
});

// 生成测试报告
echo "===============================\n";
echo "         测试结果总结\n";
echo "===============================\n";
echo "总测试数: {$totalTests}\n";
echo "通过测试: {$passedTests}\n";
echo "失败测试: " . ($totalTests - $passedTests) . "\n";
echo "通过率: " . round(($passedTests / $totalTests) * 100, 2) . "%\n";

if ($passedTests === $totalTests) {
    echo "\n🎉 所有测试通过！系统运行正常。\n";
} else {
    echo "\n⚠️  部分测试失败，请检查系统配置。\n";
}

echo "\n===============================\n";
echo "         系统信息\n";
echo "===============================\n";
echo "PHP版本: " . phpversion() . "\n";
echo "SQLite版本: " . SQLite3::version()['versionString'] . "\n";
echo "项目目录: " . getcwd() . "\n";
echo "数据库文件: dictionary.db\n";
echo "测试时间: " . date('Y-m-d H:i:s') . "\n";
echo "\n===============================\n";
?>
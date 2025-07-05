<?php
require_once 'config.php';

echo "=== LNMP环境测试 ===\n\n";

$tests = [];
$passed = 0;
$total = 0;

function runTest($name, $test) {
    global $tests, $passed, $total;
    $total++;
    echo "测试 {$total}: {$name}\n";
    
    try {
        $result = $test();
        if ($result) {
            echo "✅ 通过\n";
            $passed++;
        } else {
            echo "❌ 失败\n";
        }
    } catch (Exception $e) {
        echo "❌ 错误: " . $e->getMessage() . "\n";
    }
    echo "\n";
    return $result;
}

// 测试1: PHP环境
runTest("PHP版本检查", function() {
    $version = phpversion();
    echo "   PHP版本: {$version}\n";
    return version_compare($version, '7.4.0', '>=');
});

// 测试2: PHP扩展
runTest("PHP扩展检查", function() {
    $required = ['pdo', 'curl', 'json', 'mbstring'];
    $optional = ['pdo_mysql', 'pdo_sqlite'];
    
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            echo "   缺少必需扩展: {$ext}\n";
            return false;
        }
        echo "   ✓ {$ext}\n";
    }
    
    foreach ($optional as $ext) {
        if (extension_loaded($ext)) {
            echo "   ✓ {$ext} (可选)\n";
        } else {
            echo "   - {$ext} (未安装)\n";
        }
    }
    
    return true;
});

// 测试3: 数据库连接
runTest("数据库连接测试", function() {
    try {
        $db = getDatabase();
        echo "   数据库类型: " . DB_TYPE . "\n";
        
        if (DB_TYPE === 'mysql') {
            $stmt = $db->query("SELECT VERSION() as version");
            $version = $stmt->fetch(PDO::FETCH_ASSOC)['version'];
            echo "   MySQL版本: {$version}\n";
        } else {
            $stmt = $db->query("SELECT sqlite_version() as version");
            $version = $stmt->fetch(PDO::FETCH_ASSOC)['version'];
            echo "   SQLite版本: {$version}\n";
        }
        
        return true;
    } catch (Exception $e) {
        echo "   错误: " . $e->getMessage() . "\n";
        return false;
    }
});

// 测试4: 表结构检查
runTest("数据库表结构检查", function() {
    try {
        $db = getDatabase();
        
        // 检查dictionary表
        if (DB_TYPE === 'mysql') {
            $stmt = $db->query("SHOW TABLES LIKE 'dictionary'");
        } else {
            $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='dictionary'");
        }
        
        if (!$stmt->fetch()) {
            echo "   dictionary表不存在\n";
            return false;
        }
        echo "   ✓ dictionary表存在\n";
        
        // 检查users表
        if (DB_TYPE === 'mysql') {
            $stmt = $db->query("SHOW TABLES LIKE 'users'");
        } else {
            $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        }
        
        if (!$stmt->fetch()) {
            echo "   users表不存在\n";
            return false;
        }
        echo "   ✓ users表存在\n";
        
        return true;
    } catch (Exception $e) {
        echo "   错误: " . $e->getMessage() . "\n";
        return false;
    }
});

// 测试5: 数据查询
runTest("数据查询测试", function() {
    try {
        $entries = getAllDictionaryEntries();
        $count = count($entries);
        echo "   词典条目数: {$count}\n";
        
        if ($count > 0) {
            $entry = $entries[0];
            echo "   示例词条: {$entry['word']} → {$entry['translation']}\n";
        }
        
        return $count >= 0;
    } catch (Exception $e) {
        echo "   错误: " . $e->getMessage() . "\n";
        return false;
    }
});

// 测试6: 用户认证
runTest("用户认证测试", function() {
    try {
        $result = authenticateUser('admin', 'password');
        if ($result) {
            echo "   ✓ 管理员账户认证成功\n";
        } else {
            echo "   ❌ 管理员账户认证失败\n";
        }
        return $result;
    } catch (Exception $e) {
        echo "   错误: " . $e->getMessage() . "\n";
        return false;
    }
});

// 测试7: 文件权限
runTest("文件权限检查", function() {
    $files = [
        'config.php' => 'r',
        'index_lnmp.php' => 'r',
        'admin_lnmp.php' => 'r',
        'admin_panel_lnmp.php' => 'r'
    ];
    
    foreach ($files as $file => $mode) {
        if (!file_exists($file)) {
            echo "   文件不存在: {$file}\n";
            return false;
        }
        
        if (!is_readable($file)) {
            echo "   文件不可读: {$file}\n";
            return false;
        }
        
        echo "   ✓ {$file}\n";
    }
    
    if (DB_TYPE === 'sqlite') {
        if (file_exists('dictionary.db')) {
            if (is_writable('dictionary.db')) {
                echo "   ✓ dictionary.db (可写)\n";
            } else {
                echo "   ❌ dictionary.db (不可写)\n";
                return false;
            }
        }
    }
    
    return true;
});

// 测试8: Web服务器环境
runTest("Web服务器环境检查", function() {
    echo "   SAPI: " . php_sapi_name() . "\n";
    echo "   服务器软件: " . ($_SERVER['SERVER_SOFTWARE'] ?? '未知') . "\n";
    echo "   文档根目录: " . ($_SERVER['DOCUMENT_ROOT'] ?? getcwd()) . "\n";
    
    // 检查是否在Web环境中运行
    $isWeb = isset($_SERVER['HTTP_HOST']);
    echo "   运行环境: " . ($isWeb ? 'Web' : 'CLI') . "\n";
    
    return true;
});

// 生成测试报告
echo "===============================\n";
echo "         测试结果总结\n";
echo "===============================\n";
echo "总测试数: {$total}\n";
echo "通过测试: {$passed}\n";
echo "失败测试: " . ($total - $passed) . "\n";
echo "通过率: " . round(($passed / $total) * 100, 2) . "%\n";

if ($passed === $total) {
    echo "\n🎉 所有测试通过！LNMP环境配置正确。\n";
    echo "\n建议下一步操作:\n";
    echo "1. 运行: sudo ./setup-lnmp.sh\n";
    echo "2. 访问: http://localhost/index_lnmp.php\n";
} else {
    echo "\n⚠️  部分测试失败，请检查LNMP环境配置。\n";
}

echo "\n===============================\n";
echo "         环境信息\n";
echo "===============================\n";
echo "PHP版本: " . phpversion() . "\n";
echo "数据库类型: " . DB_TYPE . "\n";
echo "当前目录: " . getcwd() . "\n";
echo "测试时间: " . date('Y-m-d H:i:s') . "\n";
echo "\n===============================\n";
?>
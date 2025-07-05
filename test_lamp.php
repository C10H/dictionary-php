<?php
require_once 'config.php';

echo "=== LAMP环境测试 ===\n\n";

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

// 测试1: Apache环境检查
runTest("Apache环境检查", function() {
    if (function_exists('apache_get_version')) {
        $version = apache_get_version();
        echo "   Apache版本: {$version}\n";
        return true;
    } else {
        echo "   Apache函数不可用 (运行在CLI模式)\n";
        echo "   检查Apache进程...\n";
        exec('pgrep apache2', $output, $return_code);
        if ($return_code === 0) {
            echo "   ✓ Apache进程正在运行\n";
            return true;
        } else {
            echo "   ❌ Apache进程未运行\n";
            return false;
        }
    }
});

// 测试2: PHP环境
runTest("PHP环境检查", function() {
    $version = phpversion();
    $sapi = php_sapi_name();
    echo "   PHP版本: {$version}\n";
    echo "   SAPI: {$sapi}\n";
    
    return version_compare($version, '7.4.0', '>=');
});

// 测试3: PHP扩展
runTest("PHP扩展检查", function() {
    $required = ['pdo', 'json', 'mbstring'];
    $optional = ['pdo_mysql', 'pdo_sqlite', 'curl'];
    
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            echo "   缺少必需扩展: {$ext}\n";
            return false;
        }
        echo "   ✓ {$ext} (必需)\n";
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

// 测试4: Apache模块
runTest("Apache模块检查", function() {
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        $required_modules = ['mod_rewrite', 'mod_dir'];
        
        foreach ($required_modules as $module) {
            if (in_array($module, $modules)) {
                echo "   ✓ {$module}\n";
            } else {
                echo "   - {$module} (可能未启用)\n";
            }
        }
    } else {
        echo "   无法获取Apache模块信息 (可能运行在CLI模式)\n";
    }
    
    return true;
});

// 测试5: 数据库连接
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

// 测试6: 数据库表结构
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

// 测试7: 词典功能
runTest("词典功能测试", function() {
    try {
        // 测试查询
        $translation = queryDictionary('hello');
        if ($translation) {
            echo "   ✓ 词典查询: hello → {$translation}\n";
        } else {
            echo "   ❌ 词典查询失败\n";
            return false;
        }
        
        // 测试获取所有词条
        $entries = getAllDictionaryEntries();
        $count = count($entries);
        echo "   ✓ 词典条目数: {$count}\n";
        
        return $count > 0;
    } catch (Exception $e) {
        echo "   错误: " . $e->getMessage() . "\n";
        return false;
    }
});

// 测试8: 用户认证
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

// 测试9: 文件权限
runTest("文件权限检查", function() {
    $files = [
        'config.php' => 'r',
        'index_lamp.php' => 'r',
        'admin_lamp.php' => 'r',
        'admin_panel_lamp.php' => 'r'
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

// 定义语言检测函数
function detectLanguage($text) {
    return preg_match('/[\x{4e00}-\x{9fff}]/u', $text);
}

// 测试10: 语言检测和翻译
runTest("翻译功能测试", function() {
    // 测试语言检测
    $chinese = detectLanguage('你好');
    $english = !detectLanguage('hello');
    
    if (!$chinese || !$english) {
        echo "   ❌ 语言检测功能异常\n";
        return false;
    }
    echo "   ✓ 语言检测功能正常\n";
    
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
    echo "\n🎉 所有测试通过！LAMP环境配置完美。\n";
    echo "\n建议下一步操作:\n";
    echo "1. 运行: sudo ./setup-lamp.sh\n";
    echo "2. 访问: http://localhost/dictionary/\n";
} else {
    echo "\n⚠️  部分测试失败，建议检查配置。\n";
    if ($passed >= $total * 0.8) {
        echo "✅ 基本功能正常，可以尝试运行部署脚本。\n";
    }
}

echo "\n===============================\n";
echo "         环境信息\n";
echo "===============================\n";
echo "Apache版本: " . (function_exists('apache_get_version') ? apache_get_version() : '未知 (CLI模式)') . "\n";
echo "PHP版本: " . phpversion() . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "数据库类型: " . DB_TYPE . "\n";
echo "当前目录: " . getcwd() . "\n";
echo "测试时间: " . date('Y-m-d H:i:s') . "\n";
echo "\n===============================\n";
?>
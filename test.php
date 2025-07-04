<?php
echo "=== 词典系统测试 ===\n\n";

// 测试数据库连接
echo "1. 测试数据库连接...\n";
try {
    $db = new SQLite3('dictionary.db');
    echo "✓ 数据库连接成功\n";
} catch (Exception $e) {
    echo "✗ 数据库连接失败: " . $e->getMessage() . "\n";
    exit;
}

// 测试词典表数据
echo "\n2. 测试词典表数据...\n";
$result = $db->query("SELECT * FROM dictionary");
$count = 0;
while ($row = $result->fetchArray()) {
    echo "  词条 {$count}: {$row['word']} → {$row['translation']}\n";
    $count++;
}
echo "✓ 词典表包含 {$count} 个词条\n";

// 测试用户表数据
echo "\n3. 测试用户表数据...\n";
$result = $db->query("SELECT username FROM users");
$count = 0;
while ($row = $result->fetchArray()) {
    echo "  用户 {$count}: {$row['username']}\n";
    $count++;
}
echo "✓ 用户表包含 {$count} 个用户\n";

// 测试翻译功能
echo "\n4. 测试本地词典查询...\n";
function testLocalTranslation($word) {
    global $db;
    $stmt = $db->prepare("SELECT translation FROM dictionary WHERE word = ?");
    $stmt->bindValue(1, $word);
    $result = $stmt->execute();
    
    if ($row = $result->fetchArray()) {
        return $row['translation'];
    }
    return null;
}

$testWords = ['hello', 'test', 'notfound'];
foreach ($testWords as $word) {
    $translation = testLocalTranslation($word);
    if ($translation) {
        echo "  ✓ {$word} → {$translation}\n";
    } else {
        echo "  ✗ {$word} → 未找到\n";
    }
}

// 测试语言检测
echo "\n5. 测试语言检测...\n";
function detectLanguage($text) {
    return preg_match('/[\x{4e00}-\x{9fff}]/u', $text);
}

$testTexts = ['hello', '你好', 'test', '测试'];
foreach ($testTexts as $text) {
    $isChinese = detectLanguage($text);
    $lang = $isChinese ? '中文' : '英文';
    echo "  {$text} → {$lang}\n";
}

// 测试用户验证
echo "\n6. 测试用户验证...\n";
$stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->bindValue(1, 'admin');
$stmt->bindValue(2, 'password');
$result = $stmt->execute();

if ($result->fetchArray()) {
    echo "✓ 管理员账户验证成功\n";
} else {
    echo "✗ 管理员账户验证失败\n";
}

$db->close();
echo "\n=== 测试完成 ===\n";
?>
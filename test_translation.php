<?php
echo "=== 翻译功能测试 ===\n\n";

// 模拟翻译功能
function translateWord($word) {
    // 首先检查本地数据库
    $db = new SQLite3('dictionary.db');
    $stmt = $db->prepare("SELECT translation FROM dictionary WHERE word = ?");
    $stmt->bindValue(1, $word);
    $result = $stmt->execute();
    
    if ($row = $result->fetchArray()) {
        $db->close();
        return array('translation' => $row['translation'], 'source' => '本地词典');
    }
    
    $db->close();
    
    // 模拟百度翻译API（因为实际API需要网络连接）
    $mockTranslations = [
        'good' => '好的',
        'bad' => '坏的',
        'computer' => '计算机',
        '苹果' => 'apple',
        '水' => 'water',
        '书' => 'book'
    ];
    
    if (isset($mockTranslations[$word])) {
        return array('translation' => $mockTranslations[$word], 'source' => '百度翻译API（模拟）');
    }
    
    return false;
}

function detectLanguage($text) {
    return preg_match('/[\x{4e00}-\x{9fff}]/u', $text);
}

// 测试词汇
$testWords = ['hello', 'test', 'good', 'computer', '苹果', '水', 'unknown'];

echo "1. 测试翻译功能...\n";
foreach ($testWords as $word) {
    $result = translateWord($word);
    
    if ($result) {
        $lang = detectLanguage($word) ? '中文' : '英文';
        echo "  ✓ {$word} ({$lang}) → {$result['translation']} [来源: {$result['source']}]\n";
    } else {
        echo "  ✗ {$word} → 未找到翻译\n";
    }
}

echo "\n2. 测试语言检测准确性...\n";
$testTexts = [
    'hello' => '英文',
    'test' => '英文', 
    'computer' => '英文',
    '你好' => '中文',
    '测试' => '中文',
    '苹果' => '中文',
    'abc中文' => '中文',
    'English文字' => '中文'
];

foreach ($testTexts as $text => $expected) {
    $isChinese = detectLanguage($text);
    $detected = $isChinese ? '中文' : '英文';
    $status = ($detected === $expected) ? '✓' : '✗';
    echo "  {$status} '{$text}' → 检测为: {$detected}, 期望: {$expected}\n";
}

echo "\n=== 翻译功能测试完成 ===\n";
?>
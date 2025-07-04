<?php
session_start();
echo "=== 管理员功能测试 ===\n\n";

// 测试管理员登录
echo "1. 测试管理员登录...\n";
$username = 'admin';
$password = 'password';

$db = new SQLite3('dictionary.db');
$stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->bindValue(1, $username);
$stmt->bindValue(2, $password);
$result = $stmt->execute();

if ($result->fetchArray()) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    echo "✓ 管理员登录成功\n";
} else {
    echo "✗ 管理员登录失败\n";
    exit;
}

// 测试添加词条
echo "\n2. 测试添加词条...\n";
$newWord = 'world';
$newTranslation = '世界';

$stmt = $db->prepare("INSERT INTO dictionary (word, translation) VALUES (?, ?)");
$stmt->bindValue(1, $newWord);
$stmt->bindValue(2, $newTranslation);
if ($stmt->execute()) {
    echo "✓ 成功添加词条: {$newWord} → {$newTranslation}\n";
} else {
    echo "✗ 添加词条失败\n";
}

// 测试查询所有词条
echo "\n3. 测试查询所有词条...\n";
$result = $db->query("SELECT * FROM dictionary ORDER BY word");
$count = 0;
while ($row = $result->fetchArray()) {
    echo "  词条 {$count}: {$row['word']} → {$row['translation']}\n";
    $count++;
}
echo "✓ 当前词典包含 {$count} 个词条\n";

// 测试更新词条
echo "\n4. 测试更新词条...\n";
$stmt = $db->prepare("UPDATE dictionary SET translation = ? WHERE word = ?");
$stmt->bindValue(1, '世界！');
$stmt->bindValue(2, 'world');
if ($stmt->execute()) {
    echo "✓ 成功更新词条: world → 世界！\n";
} else {
    echo "✗ 更新词条失败\n";
}

// 验证更新结果
$stmt = $db->prepare("SELECT translation FROM dictionary WHERE word = ?");
$stmt->bindValue(1, 'world');
$result = $stmt->execute();
if ($row = $result->fetchArray()) {
    echo "  验证: world → {$row['translation']}\n";
}

// 测试删除词条
echo "\n5. 测试删除词条...\n";
$stmt = $db->prepare("DELETE FROM dictionary WHERE word = ?");
$stmt->bindValue(1, 'world');
if ($stmt->execute()) {
    echo "✓ 成功删除词条: world\n";
} else {
    echo "✗ 删除词条失败\n";
}

// 验证删除结果
$stmt = $db->prepare("SELECT * FROM dictionary WHERE word = ?");
$stmt->bindValue(1, 'world');
$result = $stmt->execute();
if (!$result->fetchArray()) {
    echo "  验证: world 词条已删除\n";
}

// 最终词典状态
echo "\n6. 最终词典状态...\n";
$result = $db->query("SELECT * FROM dictionary ORDER BY word");
$count = 0;
while ($row = $result->fetchArray()) {
    echo "  词条 {$count}: {$row['word']} → {$row['translation']}\n";
    $count++;
}
echo "✓ 最终词典包含 {$count} 个词条\n";

$db->close();
echo "\n=== 管理员功能测试完成 ===\n";
?>
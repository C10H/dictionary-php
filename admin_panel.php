<?php
session_start();

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

$message = '';
$db = new SQLite3('dictionary.db');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $word = trim($_POST['word']);
                $translation = trim($_POST['translation']);
                if (!empty($word) && !empty($translation)) {
                    $stmt = $db->prepare("INSERT INTO dictionary (word, translation) VALUES (?, ?)");
                    $stmt->bindValue(1, $word);
                    $stmt->bindValue(2, $translation);
                    if ($stmt->execute()) {
                        $message = '词条添加成功';
                    } else {
                        $message = '词条添加失败';
                    }
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $word = trim($_POST['word']);
                $translation = trim($_POST['translation']);
                if (!empty($word) && !empty($translation)) {
                    $stmt = $db->prepare("UPDATE dictionary SET word = ?, translation = ? WHERE id = ?");
                    $stmt->bindValue(1, $word);
                    $stmt->bindValue(2, $translation);
                    $stmt->bindValue(3, $id);
                    if ($stmt->execute()) {
                        $message = '词条更新成功';
                    } else {
                        $message = '词条更新失败';
                    }
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $db->prepare("DELETE FROM dictionary WHERE id = ?");
                $stmt->bindValue(1, $id);
                if ($stmt->execute()) {
                    $message = '词条删除成功';
                } else {
                    $message = '词条删除失败';
                }
                break;
        }
    }
}

// Get all dictionary entries
$result = $db->query("SELECT * FROM dictionary ORDER BY word");
$entries = [];
while ($row = $result->fetchArray()) {
    $entries[] = $row;
}

$db->close();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理面板 - 英汉电子词典</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            border: 2px solid #ddd;
            border-radius: 5px;
            outline: none;
            box-sizing: border-box;
        }
        input[type="text"]:focus {
            border-color: #4CAF50;
        }
        button {
            padding: 8px 16px;
            font-size: 14px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .edit-form {
            display: none;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .edit-form.active {
            display: block;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>词典管理面板</h1>
        <div>
            <span>欢迎，<?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" style="margin-left: 20px; color: #dc3545; text-decoration: none;">退出登录</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2>添加新词条</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="word">单词:</label>
                <input type="text" id="word" name="word" required>
            </div>
            <div class="form-group">
                <label for="translation">翻译:</label>
                <input type="text" id="translation" name="translation" required>
            </div>
            <button type="submit">添加词条</button>
        </form>
    </div>

    <div class="container">
        <h2>词典管理</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>单词</th>
                    <th>翻译</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?php echo $entry['id']; ?></td>
                        <td><?php echo htmlspecialchars($entry['word']); ?></td>
                        <td><?php echo htmlspecialchars($entry['translation']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="toggleEdit(<?php echo $entry['id']; ?>)">编辑</button>
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('确定要删除这个词条吗？');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                                    <button type="submit" class="btn-danger">删除</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="edit-form" id="edit-<?php echo $entry['id']; ?>">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                                    <div class="form-group">
                                        <label>单词:</label>
                                        <input type="text" name="word" value="<?php echo htmlspecialchars($entry['word']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>翻译:</label>
                                        <input type="text" name="translation" value="<?php echo htmlspecialchars($entry['translation']); ?>" required>
                                    </div>
                                    <button type="submit">保存修改</button>
                                    <button type="button" class="btn-secondary" onclick="toggleEdit(<?php echo $entry['id']; ?>)">取消</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="index.php" style="color: #4CAF50; text-decoration: none;">← 返回词典首页</a>
    </div>

    <script>
        function toggleEdit(id) {
            const editForm = document.getElementById('edit-' + id);
            editForm.classList.toggle('active');
        }
    </script>
</body>
</html>
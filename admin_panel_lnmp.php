<?php
require_once 'config.php';
session_start();

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_lnmp.php');
    exit;
}

$message = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $word = trim($_POST['word']);
                $translation = trim($_POST['translation']);
                if (!empty($word) && !empty($translation)) {
                    if (addDictionaryEntry($word, $translation)) {
                        $message = '✅ 词条添加成功';
                    } else {
                        $message = '❌ 词条添加失败';
                    }
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $word = trim($_POST['word']);
                $translation = trim($_POST['translation']);
                if (!empty($word) && !empty($translation)) {
                    if (updateDictionaryEntry($id, $word, $translation)) {
                        $message = '✅ 词条更新成功';
                    } else {
                        $message = '❌ 词条更新失败';
                    }
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                if (deleteDictionaryEntry($id)) {
                    $message = '✅ 词条删除成功';
                } else {
                    $message = '❌ 词条删除失败';
                }
                break;
        }
    }
}

// 获取所有词条
$entries = getAllDictionaryEntries();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理面板 - LNMP词典系统</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
        }
        h1, h2 {
            color: #4a5568;
            margin-bottom: 20px;
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
        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 10px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 3px solid #e2e8f0;
            border-radius: 10px;
            outline: none;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        input[type="text"]:focus {
            border-color: #4CAF50;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
        }
        button {
            padding: 12px 24px;
            font-size: 16px;
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
        }
        .btn-danger:hover {
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #5a6268);
        }
        .btn-secondary:hover {
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            font-weight: 600;
            color: #4a5568;
        }
        tr:hover {
            background: #f8fafc;
        }
        .edit-form {
            display: none;
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
            border: 2px solid #e2e8f0;
        }
        .edit-form.active {
            display: block;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logout-link {
            color: #dc3545;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border: 2px solid #dc3545;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .logout-link:hover {
            background: #dc3545;
            color: white;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border: 2px solid #4CAF50;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .back-link a:hover {
            background: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 词典管理面板 <span class="badge">LNMP</span></h1>
        <div class="user-info">
            <span>👋 欢迎，<?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <span>💾 <?php echo strtoupper(DB_TYPE); ?></span>
            <a href="logout.php" class="logout-link">🚪 退出登录</a>
        </div>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($entries); ?></div>
            <div>词条总数</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <div class="stat-number"><?php echo strtoupper(DB_TYPE); ?></div>
            <div>数据库类型</div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="container">
        <h2>➕ 添加新词条</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="word">📝 单词:</label>
                <input type="text" id="word" name="word" required placeholder="输入英文或中文单词">
            </div>
            <div class="form-group">
                <label for="translation">🔤 翻译:</label>
                <input type="text" id="translation" name="translation" required placeholder="输入对应的翻译">
            </div>
            <button type="submit">➕ 添加词条</button>
        </form>
    </div>

    <div class="container">
        <h2>📋 词典管理</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>单词</th>
                    <th>翻译</th>
                    <?php if (DB_TYPE === 'mysql'): ?>
                    <th>创建时间</th>
                    <?php endif; ?>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?php echo $entry['id']; ?></td>
                        <td><?php echo htmlspecialchars($entry['word']); ?></td>
                        <td><?php echo htmlspecialchars($entry['translation']); ?></td>
                        <?php if (DB_TYPE === 'mysql'): ?>
                        <td><?php echo isset($entry['created_at']) ? date('Y-m-d H:i', strtotime($entry['created_at'])) : '-'; ?></td>
                        <?php endif; ?>
                        <td>
                            <div class="action-buttons">
                                <button onclick="toggleEdit(<?php echo $entry['id']; ?>)">✏️ 编辑</button>
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('确定要删除这个词条吗？');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                                    <button type="submit" class="btn-danger">🗑️ 删除</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="<?php echo DB_TYPE === 'mysql' ? '5' : '4'; ?>">
                            <div class="edit-form" id="edit-<?php echo $entry['id']; ?>">
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                                    <div class="form-group">
                                        <label>📝 单词:</label>
                                        <input type="text" name="word" value="<?php echo htmlspecialchars($entry['word']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>🔤 翻译:</label>
                                        <input type="text" name="translation" value="<?php echo htmlspecialchars($entry['translation']); ?>" required>
                                    </div>
                                    <button type="submit">💾 保存修改</button>
                                    <button type="button" class="btn-secondary" onclick="toggleEdit(<?php echo $entry['id']; ?>)">❌ 取消</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="back-link">
        <a href="index_lnmp.php">🏠 返回词典首页</a>
    </div>

    <script>
        function toggleEdit(id) {
            const editForm = document.getElementById('edit-' + id);
            editForm.classList.toggle('active');
        }
    </script>
</body>
</html>
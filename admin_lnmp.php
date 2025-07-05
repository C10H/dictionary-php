<?php
require_once 'config.php';
session_start();

// 检查是否已登录
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_panel_lnmp.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (authenticateUser($username, $password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin_panel_lnmp.php');
        exit;
    } else {
        $error_message = '用户名或密码错误';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - LNMP词典系统</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 450px;
            margin: 100px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        h1 {
            text-align: center;
            color: #4a5568;
            margin-bottom: 30px;
            font-weight: 300;
        }
        .badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            border: 3px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #4CAF50;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(76, 175, 80, 0.2);
        }
        button {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }
        .error {
            color: #e53e3e;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #fed7d7;
            border-radius: 8px;
            border: 1px solid #feb2b2;
        }
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        .back-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .system-info {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background: #f7fafc;
            border-radius: 8px;
            font-size: 13px;
            color: #4a5568;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🔐 管理员登录 <span class="badge">LNMP</span></h1>
        
        <?php if ($error_message): ?>
            <div class="error">❌ <?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">👤 用户名:</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">🔑 密码:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit">🚀 登录</button>
        </form>
        
        <div class="back-link">
            <a href="index_lnmp.php">← 返回词典首页</a>
        </div>
        
        <div class="system-info">
            <div>💾 数据库: <?php echo strtoupper(DB_TYPE); ?></div>
            <div>🏗️ 架构: LNMP</div>
        </div>
    </div>
</body>
</html>
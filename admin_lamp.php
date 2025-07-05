<?php
require_once 'config.php';
session_start();

// 检查是否已登录
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_panel_lamp.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (authenticateUser($username, $password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin_panel_lamp.php');
        exit;
    } else {
        $error_message = '用户名或密码错误';
    }
}

// 获取系统信息
function getSystemInfo() {
    return [
        'apache_version' => apache_get_version() ?? 'Apache Server',
        'php_version' => phpversion(),
        'db_type' => DB_TYPE
    ];
}

$systemInfo = getSystemInfo();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - LAMP词典系统</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 450px;
            margin: 100px auto;
            padding: 20px;
            background: linear-gradient(135deg, #ff6b6b 0%, #feca57 50%, #48dbfb 100%);
            min-height: 100vh;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 300;
            font-size: 1.8em;
        }
        .badge {
            display: inline-block;
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            box-shadow: 0 3px 6px rgba(231, 76, 60, 0.3);
        }
        .lamp-icon {
            text-align: center;
            font-size: 3em;
            margin-bottom: 20px;
            opacity: 0.8;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            border: 3px solid #ecf0f1;
            border-radius: 12px;
            outline: none;
            box-sizing: border-box;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #e74c3c;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(231, 76, 60, 0.2);
        }
        button {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.3);
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(231, 76, 60, 0.4);
        }
        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
            padding: 12px;
            background: #fdf2f2;
            border-radius: 8px;
            border: 1px solid #fbb6c4;
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        .back-link a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .back-link a:hover {
            color: #c0392b;
            text-decoration: underline;
        }
        .system-info {
            text-align: center;
            margin-top: 25px;
            padding: 15px;
            background: linear-gradient(135deg, #34495e, #2c3e50);
            border-radius: 10px;
            color: white;
            font-size: 13px;
        }
        .info-row {
            margin: 5px 0;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="lamp-icon">🔥</div>
        <h1>🔐 管理员登录 <span class="badge">LAMP</span></h1>
        
        <?php if ($error_message): ?>
            <div class="error">❌ <?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">👤 用户名:</label>
                <input type="text" id="username" name="username" required autocomplete="username" placeholder="请输入用户名">
            </div>
            
            <div class="form-group">
                <label for="password">🔑 密码:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="请输入密码">
            </div>
            
            <button type="submit">🚀 登录系统</button>
        </form>
        
        <div class="back-link">
            <a href="index_lamp.php">← 返回词典首页</a>
        </div>
        
        <div class="system-info">
            <div><strong>🏗️ LAMP 环境</strong></div>
            <div class="info-row">🖥️ <?php echo $systemInfo['apache_version']; ?></div>
            <div class="info-row">🐘 PHP <?php echo $systemInfo['php_version']; ?></div>
            <div class="info-row">💾 数据库: <?php echo strtoupper($systemInfo['db_type']); ?></div>
        </div>
    </div>
</body>
</html>
#!/bin/bash

echo "=== LAMP环境配置脚本 ==="
echo "配置英汉电子词典系统运行在Apache上"

# 检查是否以root权限运行
if [ "$EUID" -ne 0 ]; then
    echo "请使用sudo运行此脚本: sudo ./setup-lamp.sh"
    exit 1
fi

echo "1. 检查Apache状态..."
systemctl status apache2 --no-pager -l

echo "2. 创建项目目录..."
mkdir -p /var/www/html/dictionary

echo "3. 复制项目文件..."
cp -r /home/a24/dictionary_php/* /var/www/html/dictionary/

echo "4. 设置权限..."
chown -R www-data:www-data /var/www/html/dictionary
chmod -R 755 /var/www/html/dictionary
chmod 666 /var/www/html/dictionary/dictionary.db

echo "5. 创建Apache配置文件..."
cat > /etc/apache2/sites-available/dictionary.conf << 'EOF'
<VirtualHost *:80>
    ServerName dictionary.local
    DocumentRoot /var/www/html/dictionary
    DirectoryIndex index_lamp.php
    
    <Directory /var/www/html/dictionary>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # 安全设置
    <Files "*.db">
        Require all denied
    </Files>
    
    <Files "test_*.php">
        Require all denied
    </Files>
    
    <Files "init_*.php">
        Require all denied
    </Files>
    
    <Files "setup-*.sh">
        Require all denied
    </Files>
    
    # 日志配置
    ErrorLog ${APACHE_LOG_DIR}/dictionary_error.log
    CustomLog ${APACHE_LOG_DIR}/dictionary_access.log combined
</VirtualHost>
EOF

echo "6. 创建主目录重定向..."
cat > /var/www/html/index.php << 'EOF'
<?php
// 重定向到词典项目
header('Location: /dictionary/index_lamp.php');
exit;
?>
EOF

echo "7. 创建.htaccess文件..."
cat > /var/www/html/dictionary/.htaccess << 'EOF'
# 重写规则
RewriteEngine On

# 默认页面重定向
RewriteRule ^$ index_lamp.php [L]

# 管理员页面重定向
RewriteRule ^admin/?$ admin_lamp.php [L]
RewriteRule ^admin/panel/?$ admin_panel_lamp.php [L]

# 安全设置
<Files "*.db">
    Order Allow,Deny
    Deny from all
</Files>

<Files "test_*.php">
    Order Allow,Deny
    Deny from all
</Files>

<Files "init_*.php">
    Order Allow,Deny
    Deny from all
</Files>

<Files "setup-*.sh">
    Order Allow,Deny
    Deny from all
</Files>

<Files "*.md">
    Order Allow,Deny
    Deny from all
</Files>

# 禁止访问隐藏文件
<Files ".*">
    Order Allow,Deny
    Deny from all
</Files>
EOF

echo "8. 启用Apache模块..."
a2enmod rewrite
a2enmod headers

echo "9. 启用站点配置..."
a2ensite dictionary.conf

echo "10. 测试Apache配置..."
apache2ctl configtest

echo "11. 重启Apache..."
systemctl reload apache2

echo "12. 创建PHP信息页面..."
echo "<?php phpinfo(); ?>" > /var/www/html/dictionary/info.php

echo "13. 创建测试页面..."
cat > /var/www/html/dictionary/test_lamp_env.php << 'EOF'
<?php
require_once 'config.php';

echo "<h1>LAMP环境测试</h1>";
echo "<h2>Apache信息</h2>";
echo "<p>Apache版本: " . (apache_get_version() ?? '未知') . "</p>";
echo "<p>文档根目录: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>服务器软件: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

echo "<h2>PHP信息</h2>";
echo "<p>PHP版本: " . phpversion() . "</p>";
echo "<p>SAPI: " . php_sapi_name() . "</p>";

echo "<h2>扩展检查</h2>";
$extensions = ['pdo', 'pdo_sqlite', 'pdo_mysql', 'curl', 'json', 'mbstring'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "<p>{$status} {$ext}</p>";
}

echo "<h2>数据库测试</h2>";
try {
    $db = getDatabase();
    echo "<p>✅ 数据库连接成功 (" . DB_TYPE . ")</p>";
    
    $entries = getAllDictionaryEntries();
    echo "<p>✅ 词典条目: " . count($entries) . " 个</p>";
} catch (Exception $e) {
    echo "<p>❌ 数据库错误: " . $e->getMessage() . "</p>";
}

echo "<h2>快速链接</h2>";
echo "<p><a href='index_lamp.php'>主页</a> | <a href='admin_lamp.php'>管理后台</a> | <a href='info.php'>PHP信息</a></p>";
?>
EOF

echo "=== LAMP环境配置完成 ==="
echo ""
echo "🎉 配置成功！"
echo ""
echo "访问地址:"
echo "- 主页: http://localhost/dictionary/"
echo "- 词典: http://localhost/dictionary/index_lamp.php"
echo "- 管理后台: http://localhost/dictionary/admin_lamp.php"
echo "- 环境测试: http://localhost/dictionary/test_lamp_env.php"
echo "- PHP信息: http://localhost/dictionary/info.php"
echo ""
echo "也可以直接访问:"
echo "- http://localhost/ (自动重定向到词典)"
echo ""
echo "默认管理员账户: admin / password"
echo ""
echo "日志文件:"
echo "- 错误日志: /var/log/apache2/dictionary_error.log"
echo "- 访问日志: /var/log/apache2/dictionary_access.log"
echo ""
echo "检查服务状态:"
echo "- Apache: systemctl status apache2"
echo "- MySQL: systemctl status mysql"
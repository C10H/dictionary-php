#!/bin/bash

echo "=== LNMP环境配置脚本 ==="
echo "请确保以管理员权限运行此脚本"

# 检查是否以root权限运行
if [ "$EUID" -ne 0 ]; then
    echo "请使用sudo运行此脚本: sudo ./setup-lnmp.sh"
    exit 1
fi

echo "1. 更新软件包..."
apt update

echo "2. 安装Nginx..."
apt install -y nginx

echo "3. 安装PHP-FPM和扩展..."
apt install -y php-fpm php-mysql php-sqlite3 php-curl php-json php-mbstring php-xml

echo "4. 启动服务..."
systemctl start nginx
systemctl start php8.3-fpm
systemctl enable nginx
systemctl enable php8.3-fpm

echo "5. 检查服务状态..."
systemctl status nginx --no-pager -l
systemctl status php8.3-fpm --no-pager -l

echo "6. 创建项目目录..."
mkdir -p /var/www/dictionary

echo "7. 复制项目文件..."
cp -r /home/a24/dictionary_php/* /var/www/dictionary/

echo "8. 设置权限..."
chown -R www-data:www-data /var/www/dictionary
chmod -R 755 /var/www/dictionary
chmod 666 /var/www/dictionary/dictionary.db

echo "9. 创建Nginx配置..."
cat > /etc/nginx/sites-available/dictionary << 'EOF'
server {
    listen 80;
    server_name localhost dictionary.local;
    root /var/www/dictionary;
    index index.php index.html;

    # 日志配置
    access_log /var/log/nginx/dictionary_access.log;
    error_log /var/log/nginx/dictionary_error.log;

    # 主要位置配置
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP处理
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 安全设置
    location ~ /\.ht {
        deny all;
    }

    location ~* \.(db|log)$ {
        deny all;
    }

    location ~* test_.*\.php$ {
        deny all;
    }

    location ~* init_.*\.php$ {
        deny all;
    }

    # 静态文件缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

echo "10. 启用站点..."
ln -sf /etc/nginx/sites-available/dictionary /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

echo "11. 测试Nginx配置..."
nginx -t

echo "12. 重启Nginx..."
systemctl reload nginx

echo "13. 创建PHP信息页面..."
echo "<?php phpinfo(); ?>" > /var/www/dictionary/info.php

echo "=== LNMP环境配置完成 ==="
echo ""
echo "访问地址:"
echo "- 词典系统: http://localhost/"
echo "- 管理后台: http://localhost/admin.php"
echo "- PHP信息: http://localhost/info.php"
echo ""
echo "默认管理员账户: admin / password"
echo ""
echo "请检查以下服务状态:"
echo "- Nginx: systemctl status nginx"
echo "- PHP-FPM: systemctl status php8.3-fpm"
echo "- MySQL: systemctl status mysql"
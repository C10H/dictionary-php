# 部署指南

本文档详细说明了如何在不同环境中部署英汉电子词典系统。

## 📋 目录

- [系统要求](#系统要求)
- [开发环境部署](#开发环境部署)
- [生产环境部署](#生产环境部署)
- [Docker部署](#docker部署)
- [云服务器部署](#云服务器部署)
- [性能优化](#性能优化)
- [安全配置](#安全配置)
- [监控与维护](#监控与维护)
- [故障排除](#故障排除)

## 🔧 系统要求

### 最低要求
- **PHP**: 7.4 或更高版本
- **内存**: 256MB RAM
- **存储**: 50MB 可用空间
- **Web服务器**: Apache/Nginx/PHP内置服务器

### 推荐配置
- **PHP**: 8.1 或更高版本
- **内存**: 512MB RAM
- **存储**: 1GB 可用空间
- **Web服务器**: Nginx + PHP-FPM

### PHP扩展要求
```bash
# 必需扩展
php-sqlite3
php-curl
php-json
php-mbstring

# 可选扩展（提升性能）
php-opcache
php-apcu
```

## 🚀 开发环境部署

### 方式1: PHP内置服务器（推荐用于开发）

```bash
# 1. 进入项目目录
cd dictionary_php

# 2. 初始化数据库
php init_db.php

# 3. 启动开发服务器
php -S localhost:8000

# 4. 访问应用
# 主页: http://localhost:8000/index.php
# 管理后台: http://localhost:8000/admin.php
```

### 方式2: XAMPP部署

1. **下载安装XAMPP**
   - 访问 https://www.apachefriends.org/
   - 下载适合您操作系统的版本

2. **部署步骤**
   ```bash
   # 1. 复制项目到XAMPP目录
   cp -r dictionary_php /opt/lampp/htdocs/
   
   # 2. 启动XAMPP
   sudo /opt/lampp/lampp start
   
   # 3. 初始化数据库
   cd /opt/lampp/htdocs/dictionary_php
   php init_db.php
   ```

3. **访问地址**
   - 主页: http://localhost/dictionary_php/index.php
   - 管理后台: http://localhost/dictionary_php/admin.php

### 方式3: WAMP部署（Windows）

1. **安装WAMP**
   - 下载 WampServer
   - 安装并启动所有服务

2. **部署项目**
   ```cmd
   # 1. 复制项目到www目录
   copy dictionary_php C:\wamp64\www\
   
   # 2. 初始化数据库
   cd C:\wamp64\www\dictionary_php
   php init_db.php
   ```

## 🏭 生产环境部署

### Apache配置

1. **创建虚拟主机配置**
   ```apache
   # /etc/apache2/sites-available/dictionary.conf
   <VirtualHost *:80>
       ServerName dictionary.yourdomain.com
       DocumentRoot /var/www/dictionary_php
       
       <Directory /var/www/dictionary_php>
           Options -Indexes +FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
       
       # 安全设置
       <Files "*.db">
           Require all denied
       </Files>
       
       # 日志配置
       ErrorLog ${APACHE_LOG_DIR}/dictionary_error.log
       CustomLog ${APACHE_LOG_DIR}/dictionary_access.log combined
   </VirtualHost>
   ```

2. **启用站点**
   ```bash
   sudo a2ensite dictionary.conf
   sudo systemctl reload apache2
   ```

3. **创建.htaccess文件**
   ```apache
   # /var/www/dictionary_php/.htaccess
   RewriteEngine On
   
   # 重定向到index.php
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^$ index.php [L]
   
   # 安全设置
   <Files "*.db">
       Deny from all
   </Files>
   
   <Files "test_*.php">
       Deny from all
   </Files>
   
   <Files "init_*.php">
       Deny from all
   </Files>
   ```

### Nginx配置

1. **创建Nginx配置文件**
   ```nginx
   # /etc/nginx/sites-available/dictionary
   server {
       listen 80;
       server_name dictionary.yourdomain.com;
       root /var/www/dictionary_php;
       index index.php;
       
       # 访问日志
       access_log /var/log/nginx/dictionary_access.log;
       error_log /var/log/nginx/dictionary_error.log;
       
       # 主要位置配置
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       # PHP处理
       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
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
   ```

2. **启用配置**
   ```bash
   sudo ln -s /etc/nginx/sites-available/dictionary /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

## 🐳 Docker部署

### Dockerfile

```dockerfile
# Dockerfile
FROM php:8.1-apache

# 安装扩展
RUN docker-php-ext-install pdo_sqlite
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# 启用Apache模块
RUN a2enmod rewrite

# 复制项目文件
COPY . /var/www/html/

# 设置权限
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/

# 初始化数据库
RUN php /var/www/html/init_db.php

# 暴露端口
EXPOSE 80

# 启动命令
CMD ["apache2-foreground"]
```

### docker-compose.yml

```yaml
version: '3.8'

services:
  dictionary:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./dictionary.db:/var/www/html/dictionary.db
    environment:
      - PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d
    restart: unless-stopped
    
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
    depends_on:
      - dictionary
    restart: unless-stopped
```

### 部署命令

```bash
# 构建并启动容器
docker-compose up -d

# 查看运行状态
docker-compose ps

# 查看日志
docker-compose logs -f dictionary
```

## ☁️ 云服务器部署

### 阿里云ECS部署

1. **环境准备**
   ```bash
   # 更新系统
   sudo yum update -y
   
   # 安装LAMP环境
   sudo yum install -y httpd php php-sqlite3 php-curl php-json php-mbstring
   
   # 启动Apache
   sudo systemctl start httpd
   sudo systemctl enable httpd
   ```

2. **部署项目**
   ```bash
   # 上传项目文件
   scp -r dictionary_php root@your-server-ip:/var/www/html/
   
   # 设置权限
   sudo chown -R apache:apache /var/www/html/dictionary_php
   sudo chmod -R 755 /var/www/html/dictionary_php
   
   # 初始化数据库
   cd /var/www/html/dictionary_php
   php init_db.php
   ```

### 腾讯云CVM部署

1. **环境安装**
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install -y apache2 php php-sqlite3 php-curl php-json php-mbstring
   
   # 启动服务
   sudo systemctl start apache2
   sudo systemctl enable apache2
   ```

2. **SSL配置（可选）**
   ```bash
   # 安装Let's Encrypt
   sudo apt install -y certbot python3-certbot-apache
   
   # 申请SSL证书
   sudo certbot --apache -d dictionary.yourdomain.com
   ```

## ⚡ 性能优化

### PHP优化

1. **启用OPcache**
   ```ini
   # /etc/php/8.1/apache2/conf.d/10-opcache.ini
   opcache.enable=1
   opcache.enable_cli=1
   opcache.memory_consumption=128
   opcache.interned_strings_buffer=8
   opcache.max_accelerated_files=4000
   opcache.revalidate_freq=60
   opcache.fast_shutdown=1
   ```

2. **优化php.ini**
   ```ini
   # /etc/php/8.1/apache2/php.ini
   memory_limit = 256M
   max_execution_time = 30
   max_input_time = 60
   post_max_size = 8M
   upload_max_filesize = 2M
   
   # 会话设置
   session.gc_maxlifetime = 1440
   session.gc_probability = 1
   session.gc_divisor = 1000
   ```

### 数据库优化

1. **SQLite优化**
   ```php
   // 在数据库连接后添加
   $db->exec('PRAGMA journal_mode = WAL');
   $db->exec('PRAGMA synchronous = NORMAL');
   $db->exec('PRAGMA cache_size = 1000');
   $db->exec('PRAGMA temp_store = MEMORY');
   ```

2. **创建索引**
   ```sql
   CREATE INDEX idx_word ON dictionary(word);
   CREATE INDEX idx_username ON users(username);
   ```

### 缓存策略

1. **文件缓存**
   ```php
   // 简单的文件缓存实现
   function getCachedTranslation($word) {
       $cacheFile = 'cache/' . md5($word) . '.cache';
       if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 3600)) {
           return json_decode(file_get_contents($cacheFile), true);
       }
       return false;
   }
   ```

## 🔒 安全配置

### 基础安全设置

1. **修改默认密码**
   ```php
   // 生成安全密码
   $password = password_hash('your_secure_password', PASSWORD_DEFAULT);
   
   // 更新数据库
   $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
   $stmt->bindValue(1, $password);
   $stmt->execute();
   ```

2. **环境变量配置**
   ```bash
   # .env
   BAIDU_APP_ID=your_app_id
   BAIDU_SECRET_KEY=your_secret_key
   DB_PATH=/path/to/secure/location/dictionary.db
   ```

3. **文件权限**
   ```bash
   # 设置严格的文件权限
   chmod 644 *.php
   chmod 600 dictionary.db
   chmod 755 .
   ```

### 防火墙配置

```bash
# UFW防火墙
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# iptables示例
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
```

## 📊 监控与维护

### 日志配置

1. **Apache错误日志**
   ```apache
   ErrorLog /var/log/apache2/dictionary_error.log
   CustomLog /var/log/apache2/dictionary_access.log combined
   ```

2. **PHP错误日志**
   ```ini
   log_errors = On
   error_log = /var/log/php/dictionary_errors.log
   ```

### 备份策略

1. **数据库备份脚本**
   ```bash
   #!/bin/bash
   # backup.sh
   
   BACKUP_DIR="/backup/dictionary"
   DATE=$(date +%Y%m%d_%H%M%S)
   
   # 创建备份目录
   mkdir -p $BACKUP_DIR
   
   # 备份数据库
   cp dictionary.db $BACKUP_DIR/dictionary_$DATE.db
   
   # 清理旧备份（保留30天）
   find $BACKUP_DIR -name "*.db" -mtime +30 -delete
   ```

2. **定时任务**
   ```bash
   # 添加到crontab
   crontab -e
   
   # 每天凌晨2点备份
   0 2 * * * /path/to/backup.sh
   ```

### 健康检查

1. **系统监控脚本**
   ```bash
   #!/bin/bash
   # health_check.sh
   
   # 检查Apache状态
   systemctl is-active --quiet httpd && echo "Apache: OK" || echo "Apache: FAIL"
   
   # 检查PHP
   php -v > /dev/null 2>&1 && echo "PHP: OK" || echo "PHP: FAIL"
   
   # 检查数据库
   sqlite3 dictionary.db "SELECT COUNT(*) FROM dictionary;" > /dev/null 2>&1 && echo "Database: OK" || echo "Database: FAIL"
   ```

## 🔧 故障排除

### 常见问题

1. **数据库连接失败**
   ```bash
   # 检查SQLite扩展
   php -m | grep sqlite
   
   # 检查文件权限
   ls -la dictionary.db
   
   # 检查PHP错误日志
   tail -f /var/log/php/errors.log
   ```

2. **Apache无法启动**
   ```bash
   # 检查配置语法
   apache2ctl configtest
   
   # 查看错误日志
   tail -f /var/log/apache2/error.log
   
   # 检查端口占用
   netstat -tlnp | grep :80
   ```

3. **PHP页面无法访问**
   ```bash
   # 检查PHP模块
   apache2ctl -M | grep php
   
   # 重启Apache
   systemctl restart apache2
   ```

### 性能问题诊断

1. **查看系统资源**
   ```bash
   # CPU和内存使用
   top -p $(pgrep apache2)
   
   # 磁盘使用
   df -h
   
   # 网络连接
   ss -tuln
   ```

2. **数据库性能**
   ```sql
   -- 查看数据库大小
   SELECT page_count * page_size as size FROM pragma_page_count(), pragma_page_size();
   
   -- 分析查询
   EXPLAIN QUERY PLAN SELECT * FROM dictionary WHERE word = 'test';
   ```

### 日志分析

```bash
# 分析访问日志
awk '{print $1}' /var/log/apache2/access.log | sort | uniq -c | sort -nr

# 查看错误统计
grep -c "ERROR" /var/log/apache2/error.log

# 实时监控
tail -f /var/log/apache2/access.log | grep "admin"
```

## 📞 技术支持

如果在部署过程中遇到问题，请：

1. 检查系统日志
2. 验证配置文件
3. 运行测试脚本
4. 查看本文档的故障排除部分
5. 联系技术支持

---

**最后更新**: 2025-07-04
**文档版本**: 1.0
**适用系统版本**: 1.0.0
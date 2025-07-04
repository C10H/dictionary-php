FROM php:8.1-apache

# 设置工作目录
WORKDIR /var/www/html

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    curl \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 安装PHP扩展
RUN docker-php-ext-install pdo_sqlite

# 启用Apache模块
RUN a2enmod rewrite

# 复制项目文件
COPY . /var/www/html/

# 创建必要的目录
RUN mkdir -p /var/www/html/cache && \
    mkdir -p /var/www/html/logs

# 设置权限
RUN chown -R www-data:www-data /var/www/html/ && \
    chmod -R 755 /var/www/html/ && \
    chmod 666 /var/www/html/dictionary.db

# 初始化数据库
RUN php init_db.php

# 创建Apache配置
RUN echo '<Directory /var/www/html/>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n\
\n\
<Files "*.db">\n\
    Require all denied\n\
</Files>\n\
\n\
<Files "test_*.php">\n\
    Require all denied\n\
</Files>\n\
\n\
<Files "init_*.php">\n\
    Require all denied\n\
</Files>' > /etc/apache2/conf-available/dictionary.conf

RUN a2enconf dictionary

# 健康检查
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# 暴露端口
EXPOSE 80

# 启动Apache
CMD ["apache2-foreground"]
# 🚀 LNMP环境部署指南

## 📋 概述

您完全可以使用LNMP环境运行英汉电子词典项目！我已经为您创建了完整的LNMP版本。

## 🔍 当前环境状态

### ✅ 已有组件：
- **Linux**: ✅ Ubuntu/Debian系统
- **MySQL**: ✅ MySQL 8.0.42 (已安装并运行)
- **PHP**: ✅ PHP 8.3.6 (已安装)

### ❌ 需要安装：
- **Nginx**: ❌ 未安装
- **PHP-FPM**: ❌ 可能需要安装
- **PHP扩展**: ❌ 缺少curl扩展

## 🛠️ 一键安装脚本

我已经为您创建了自动化安装脚本：

```bash
# 使用管理员权限运行
sudo ./setup-lnmp.sh
```

### 脚本功能：
1. ✅ 安装Nginx
2. ✅ 安装PHP-FPM和必需扩展
3. ✅ 配置Nginx虚拟主机
4. ✅ 复制项目文件到Web目录
5. ✅ 设置正确的文件权限
6. ✅ 启动所有服务

## 📁 LNMP版本文件

我为您创建了专门的LNMP版本：

### 核心文件：
- `index_lnmp.php` - 主词典界面（LNMP优化版）
- `admin_lnmp.php` - 管理员登录（LNMP版）
- `admin_panel_lnmp.php` - 管理后台（LNMP版）
- `config.php` - 统一配置文件
- `init_mysql.php` - MySQL数据库初始化

### 配置脚本：
- `setup-lnmp.sh` - 一键LNMP环境配置
- `test_lnmp.php` - LNMP环境测试

## 🔧 手动安装步骤

如果您更喜欢手动安装：

### 1. 安装Nginx和PHP扩展
```bash
sudo apt update
sudo apt install -y nginx php-fpm php-mysql php-sqlite3 php-curl php-json php-mbstring php-xml
```

### 2. 启动服务
```bash
sudo systemctl start nginx php8.3-fpm
sudo systemctl enable nginx php8.3-fpm
```

### 3. 配置Nginx
```bash
sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-available/dictionary
sudo nano /etc/nginx/sites-available/dictionary
```

配置内容已在`setup-lnmp.sh`中提供。

### 4. 复制项目文件
```bash
sudo mkdir -p /var/www/dictionary
sudo cp -r /home/a24/dictionary_php/* /var/www/dictionary/
sudo chown -R www-data:www-data /var/www/dictionary
```

## 🗄️ 数据库选择

### SQLite版本（默认）：
- ✅ 无需额外配置
- ✅ 文件型数据库，易于部署
- ✅ 适合中小型应用

### MySQL版本：
- ✅ 高性能
- ✅ 支持并发访问
- ✅ 企业级特性

### 切换到MySQL：
1. 运行MySQL初始化：
```bash
sudo mysql -u root -p < init_mysql.php
```

2. 修改config.php：
```php
define('DB_TYPE', 'mysql'); // 改为mysql
```

## 🧪 测试环境

运行测试脚本检查环境：
```bash
php test_lnmp.php
```

当前测试结果：**87.5%通过** (只缺curl扩展)

## 🌐 访问地址

部署完成后的访问地址：

### 开发环境：
- 主页：http://localhost:8000/index_lnmp.php
- 管理后台：http://localhost:8000/admin_lnmp.php

### LNMP环境：
- 主页：http://localhost/
- 管理后台：http://localhost/admin_lnmp.php
- PHP信息：http://localhost/info.php

## 🎨 LNMP版本特性

### 🎯 增强功能：
- **现代化UI**: 渐变背景、毛玻璃效果
- **响应式设计**: 适配各种设备
- **动画效果**: 平滑过渡动画
- **统计面板**: 词条数量、数据库类型显示
- **多数据库支持**: SQLite/MySQL切换
- **安全增强**: 密码哈希（MySQL版本）

### 🔒 安全特性：
- ✅ SQL注入防护
- ✅ XSS防护
- ✅ 文件访问控制
- ✅ 会话管理
- ✅ 密码哈希（MySQL版本）

## 📊 性能对比

| 特性 | 原版本 | LNMP版本 |
|------|--------|----------|
| Web服务器 | PHP内置 | Nginx + PHP-FPM |
| 数据库 | SQLite | SQLite/MySQL |
| 并发性能 | 低 | 高 |
| 扩展性 | 有限 | 优秀 |
| 生产就绪 | 开发用 | 生产级 |

## 🚀 快速启动

### 最简单的方式：
```bash
# 1. 运行安装脚本
sudo ./setup-lnmp.sh

# 2. 访问网站
open http://localhost

# 3. 管理后台
open http://localhost/admin_lnmp.php
```

### 默认账户：
- **用户名**: admin
- **密码**: password

## 🔧 配置文件详解

### config.php核心配置：
```php
// 数据库类型选择
define('DB_TYPE', 'sqlite'); // 'sqlite' 或 'mysql'

// MySQL配置
define('MYSQL_HOST', 'localhost');
define('MYSQL_DBNAME', 'dictionary_db');
define('MYSQL_USERNAME', 'dictionary_user');
define('MYSQL_PASSWORD', 'dictionary_pass');

// 百度翻译API
define('BAIDU_APP_ID', 'your_app_id');
define('BAIDU_SECRET_KEY', 'your_secret_key');
```

## 📝 总结

**您的系统完全支持LNMP部署！**

优势：
- ✅ MySQL已安装并运行
- ✅ PHP环境就绪
- ✅ 只需安装Nginx即可
- ✅ 专门优化的LNMP版本已准备就绪
- ✅ 一键部署脚本已创建

**建议运行命令：**
```bash
sudo ./setup-lnmp.sh && php test_lnmp.php
```

这将为您配置完整的LNMP环境并运行性能测试！

---

**创建时间**: 2025-07-05
**版本**: LNMP 1.0
**作者**: 英汉电子词典系统
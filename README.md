# 英汉电子词典系统

一个基于PHP和SQLite的智能英汉双向翻译系统，支持本地词典查询和百度翻译API集成。

## 📋 功能特性

- **智能语言检测**: 自动识别输入的中文或英文
- **双重翻译来源**: 优先使用本地词典，备用百度翻译API
- **管理员后台**: 完整的词典管理系统
- **词条管理**: 支持增加、删除、修改词典内容
- **用户认证**: 安全的管理员登录系统
- **响应式设计**: 适配不同设备屏幕
- **轻量级**: 使用SQLite数据库，无需复杂配置

## 🚀 快速开始

### 系统要求

- PHP 7.4+
- SQLite3 扩展
- cURL 扩展（用于百度翻译API）

### 安装步骤

1. **克隆或下载项目**
   ```bash
   git clone <repository-url>
   cd dictionary_php
   ```

2. **初始化数据库**
   ```bash
   php init_db.php
   ```

3. **启动开发服务器**
   ```bash
   php -S localhost:8000
   ```

4. **访问系统**
   - 主词典: http://localhost:8000/index.php
   - 管理后台: http://localhost:8000/admin.php

## 📖 使用指南

### 词典查询

1. 在主页面输入框中输入中文或英文单词
2. 点击"翻译"按钮
3. 系统会自动检测语言并返回翻译结果
4. 翻译来源会标明是"本地词典"还是"百度翻译API"

### 管理员功能

1. 访问 `/admin.php` 进入登录页面
2. 使用默认账户登录：
   - 用户名: `admin`
   - 密码: `password`
3. 登录后可以进行以下操作：
   - 添加新词条
   - 编辑现有词条
   - 删除词条
   - 查看所有词条

## 🗂️ 项目结构

```
dictionary_php/
├── index.php          # 主词典界面
├── admin.php          # 管理员登录页面
├── admin_panel.php    # 管理员后台面板
├── logout.php         # 退出登录
├── init_db.php        # 数据库初始化脚本
├── dictionary.db      # SQLite数据库文件
├── test_*.php         # 测试文件
├── README.md          # 项目说明文档
└── deployment.md      # 部署文档
```

## 🗃️ 数据库结构

### dictionary 表
```sql
CREATE TABLE dictionary (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    word TEXT NOT NULL,
    translation TEXT NOT NULL
);
```

### users 表
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
);
```

### 初始数据
- **词典**: hello → 你好, test → 测试
- **管理员**: admin / password

## 🔧 配置

### 百度翻译API配置

在 `index.php` 中修改以下配置：

```php
$appId = '20240531002066782';  // 您的百度翻译API App ID
$key = '2UYrEDwvtMgOShDLo3u8';    // 您的百度翻译API 密钥
```

### 数据库配置

系统使用SQLite数据库，默认文件名为 `dictionary.db`。如需修改，请在相关PHP文件中更新数据库路径。

## 🧪 测试

项目包含多个测试文件：

- `test.php`: 基础功能测试
- `test_admin.php`: 管理员功能测试
- `test_translation.php`: 翻译功能测试
- `test_report.php`: 综合测试报告

运行测试：
```bash
php test_report.php
```

## 📱 功能截图

### 主界面
- 简洁的搜索界面
- 自动语言检测
- 翻译结果显示
- 翻译来源标识

### 管理后台
- 安全的登录系统
- 词条列表查看
- 在线编辑功能
- 批量管理操作

## 🔒 安全特性

- **SQL注入防护**: 使用预处理语句
- **XSS防护**: 输出内容HTML转义
- **会话管理**: 安全的管理员会话
- **输入验证**: 严格的用户输入验证

## 🚨 注意事项

1. **生产环境部署**: 请修改默认管理员密码
2. **API密钥保护**: 将百度翻译API密钥移至环境变量
3. **数据库备份**: 定期备份SQLite数据库文件
4. **权限设置**: 确保数据库文件有适当的读写权限

## 📝 更新日志

### v1.0.0
- 基础词典查询功能
- 百度翻译API集成
- 管理员后台系统
- 词条管理功能
- 响应式UI设计

## 🤝 贡献指南

1. Fork 项目
2. 创建功能分支
3. 提交更改
4. 发起 Pull Request

## 📄 许可证

本项目采用 MIT 许可证，详情请参阅 LICENSE 文件。

## 💬 支持与反馈

如有问题或建议，请通过以下方式联系：

- 创建 Issue
- 发送邮件
- 提交 Pull Request

## 🔗 相关链接

- [百度翻译API文档](https://fanyi-api.baidu.com/doc/21)
- [PHP官方文档](https://www.php.net/manual/)
- [SQLite文档](https://www.sqlite.org/docs.html)

---

**开发者**: 英汉电子词典系统
**版本**: 1.0.0
**最后更新**: 2025-07-04
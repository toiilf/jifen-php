# 🎮 打牌记分系统

一款基于 PHP + MySQL 的在线多人打牌记分系统，支持创建房间、多人实时记分、积分转让、历史记录查询等功能。适合朋友聚会打牌、麻将、桌游等场景使用。

## ✨ 功能特点

### 用户系统
- 🔐 **用户注册/登录**：支持自定义昵称和密码注册
- ⚡ **免注册快速加入**：一键生成随机账号，快速进入游戏
- 👤 **个人中心**：查看游戏统计、修改昵称和密码
- 📊 **历史记录**：查看所有参与过的牌局记录

### 房间系统
- 🏠 **创建房间**：自定义房间名称、密码、最大人数（2-8人）
- 🔍 **加入房间**：通过房间名称和密码加入
- 🔗 **分享链接**：复制链接或二维码邀请好友
- 👑 **房主权限**：房主可解散房间

### 记分系统
- 💸 **积分转让**：点击其他玩家进行积分转让（支持负数积分）
- 📋 **实时记录**：所有转让记录实时显示
- 🏆 **游戏记录**：每次转让自动生成游戏记录
- 📈 **统计信息**：总场次、胜场、净胜分

### 管理员后台
- 🔒 **独立管理后台**：管理员登录
- 👥 **用户管理**：查看、创建、编辑、删除用户
- 🏠 **房间管理**：查看、关闭、删除房间
- 🔐 **管理员管理**：添加、修改密码、删除管理员
- 📊 **系统统计**：用户数、房间数、游戏总数

### 实时更新
- 🔄 **AJAX轮询**：每秒自动刷新房间数据
- ⚡ **即时反馈**：积分转让后立即更新
- 💪 **稳定运行**：支持4-8小时连续运行
- 🛡️ **并发安全**：行锁 + 事务 + 死锁重试

---

## 🛠 技术栈

| 技术 | 版本 | 用途 |
|------|------|------|
| **PHP** | 7.4+ | 后端语言 |
| **MySQL** | 5.7+ | 数据库 |
| **PDO** | - | 数据库连接（防SQL注入） |
| **原生HTML/CSS/JS** | - | 前端界面 |
| **AJAX** | - | 数据实时更新 |
| **Session** | - | 用户会话管理 |
| **password_hash()** | - | 密码加密 |

---

## 📦 环境要求

### 服务器要求

| 项目 | 最低要求 | 推荐配置 |
|------|----------|----------|
| **操作系统** | Linux / Windows | Linux (CentOS/Ubuntu) |
| **Web服务器** | Nginx / Apache | Nginx 1.18+ |
| **PHP版本** | 7.4+ | PHP 8.0+ |
| **MySQL版本** | 5.7+ | MySQL 8.0+ |
| **内存** | 512MB | 1GB+ |
| **磁盘空间** | 50MB | 100MB+ |

### PHP扩展要求


# 必需扩展
- PDO
- PDO_MySQL
- Session
- JSON
- Fileinfo

# 推荐扩展
- OpenSSL
- Mbstring
- GD (用于二维码)
- cURL

# 🚀 安装部署
# 部署方式一：宝塔面板（推荐）
# 1. 上传代码
登录宝塔面板

进入「文件」管理

在网站目录下创建项目文件夹

上传所有代码文件

# 2. 创建站点
宝塔面板 →「网站」→「添加站点」

填写域名/IP和端口

PHP版本选择 7.4 或更高

网站目录指向项目根目录

# 3. 设置伪静态
网站设置 →「伪静态」

选择「ThinkPHP」或输入：
```bash
nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```
# 4. 设置目录权限
```bash
chown -R www:www /www/wwwroot/jifen/
chmod -R 755 /www/wwwroot/jifen/
chmod 777 /www/wwwroot/jifen/sessions/
```
# 5. 访问安装
访问：http://你的域名/install

按向导填写数据库信息和管理员账号

可选：勾选「清空现有数据」重新安装

点击「开始安装」

安装完成后跳转到登录页面

# 6. 配置SSL（可选）
网站设置 →「SSL」

申请Let's Encrypt证书

开启强制HTTPS

# 部署方式二：手动部署
# 1. 克隆代码
```bash
cd /var/www/html
git clone https://github.com/your-repo/jifen-php.git
cd jifen
```
# 2. 配置Nginx
```bash
nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/jifen;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 安全：禁止访问敏感文件
    
    location ~ /\.env {
        deny all;
    }
    location ~ /\.installed {
        deny all;
    }
    location ~ /install\.lock {
        deny all;
    }
}
```
# 3. 设置权限
```bash
chown -R www-data:www-data /var/www/html/jifen
chmod -R 755 /var/www/html/jifen
chmod 777 /var/www/html/jifen/sessions
```
# 4. 访问安装
访问 http://your-domain.com/install 完成安装

会话安全    	⭐⭐⭐⭐⭐	完整防护

输入验证    	⭐⭐⭐⭐⭐	完整防护

暴力破解    	⭐⭐⭐⭐⭐	完整防护


# 📖 功能详解

# 1. 用户系统
注册/登录

支持自定义昵称（2-20个字符）

密码至少6位

登录失败5次锁定5分钟（防暴力破解）

会话自动超时（30分钟）

免注册快速加入

一键生成随机昵称

默认密码：123456

自动跳转到修改资料页面


# 2. 房间系统
创建房间

房间名称：必填，最多30个字符

房间密码：可选，留空为公开房间

最大人数：2-8人可选

加入房间

输入房间名称精确匹配

有密码的房间需要输入密码

房间满员时无法加入

分享功能

复制链接：一键复制房间链接

二维码：生成二维码扫码加入


# 3. 记分系统
积分转让

点击其他玩家

输入转让金额

确认转让

所有玩家实时看到更新

支持负数积分（打牌记分允许积分亏损）

自动记录

每次转让自动记录

每日首笔转让生成游戏记录

自动计算玩家净胜分

自动更新总场次和胜场

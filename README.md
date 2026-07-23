# 计分系统 - PHP版本

一个功能完整的在线计分工具，支持创建房间、多人计分、积分转让、历史记录查询等功能。

## 功能特点

- ✅ 用户注册/登录/快速注册
- ✅ 创建/加入/退出房间
- ✅ 积分转让（实时更新）
- ✅ 自动记录游戏历史
- ✅ 个人中心（修改昵称/密码）
- ✅ 管理员后台（用户/房间/管理员管理）
- ✅ 响应式设计，支持移动端

## 技术栈

- PHP 7.4+
- MySQL 5.7+
- 原生HTML/CSS/JavaScript
- PDO数据库操作

## 安装步骤

1. 将项目部署到Web服务器（Apache/Nginx）

2. 确保PHP可写以下目录：
   - 项目根目录（用于创建 .env 和 .installed）
   - sessions/ 目录（用于存储会话）

3. 访问首页，会自动跳转到安装向导

4. 按照安装向导填写数据库信息和管理员账号

5. 安装完成后即可开始使用

伪静态：
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

## 目录结构
jifen
│
├── index.php                      # ✅ 入口文件（路由分发）
├── .env                           # 环境变量（安装后自动生成）
├── .env.example                   # 环境变量模板
├── .installed                     # 安装标记（安装后自动生成）
├── install.lock                   # 安装锁定文件（安装后自动生成）
├── .gitignore                     # Git忽略文件
├── README.md                      # 项目说明
│
├── config/                        # ✅ 配置目录
│   └── database.php               # 数据库配置
│
├── includes/                      # ✅ 公共函数目录
│   ├── functions.php              # 工具函数
│   └── auth.php                   # 认证中间件
│
├── routes/                        # ✅ 路由控制器目录
│   ├── index.php                  # 首页/安装/解锁路由
│   ├── auth.php                   # 认证路由（登录/注册/退出）
│   ├── lobby.php                  # 大厅路由（房间列表/创建/加入）
│   ├── room.php                   # 房间路由
│   ├── profile.php                # 个人中心路由
│   ├── history.php                # 历史记录路由
│   ├── admin.php                  # 管理员后台路由
│   └── api.php                    # API路由（房间数据/转让积分）
│
├── views/                         # ✅ 视图模板目录
│   ├── install.php                # 安装向导页面
│   ├── uninstall.php              # 解锁安装页面
│   ├── login.php                  # 用户登录页面
│   ├── register.php               # 用户注册页面
│   ├── lobby.php                  # 游戏大厅页面
│   ├── room.php                   # 房间页面
│   ├── profile.php                # 个人中心页面
│   ├── history.php                # 历史记录页面
│   ├── admin_login.php            # 管理员登录页面
│   ├── admin.php                  # 管理员后台页面
│   ├── join-choice.php            # 加入房间选择页面
│   ├── error.php                  # 错误提示页面
│   └── 404.php                    # 404页面
│
├── public/                        # ✅ 静态资源目录
│   └── css/
│       ├── style.css              # 公共样式
│       └── room.css               # 房间页面样式
│
└── sessions/                      # ✅ Session存储目录（需可写）

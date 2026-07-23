<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ===== SEO 信息 ===== -->
    <title>管理员登录 - 打牌记分系统</title>
    <meta name="description" content="打牌记分系统管理后台登录入口。">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            color: #333;
        }
        
        .form-group { margin-bottom: 20px; }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 16px;
            outline: none;
        }
        
        .form-group input:focus { border-color: #667eea; }
        
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-error { background: #fee; color: #c33; border: 1px solid #fcc; }
        
        .link { text-align: center; margin-top: 20px; }
        .link a { color: #667eea; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 管理员登录</h1>
        
        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/admin/login">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" placeholder="管理员用户名" required>
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" placeholder="管理员密码" required>
            </div>
            <button type="submit">登录</button>
        </form>
        
        <div class="link">
            <a href="/">返回首页</a>
        </div>
    </div>
</body>
</html>
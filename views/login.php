<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <!-- ===== SEO 信息 ===== -->
    <title>登录 - 打牌记分系统 | 在线记分工具</title>
    <meta name="description" content="登录打牌记分系统，开始您的在线记分之旅。支持创建房间、多人记分、积分转让、历史记录查询。免费使用，无需下载。">
    <meta name="keywords" content="打牌记分系统登录,在线记分登录,麻将记分,桌游记分,积分系统">
    <meta name="author" content="打牌记分系统">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph -->
    <meta property="og:title" content="登录 - 打牌记分系统">
    <meta property="og:description" content="登录打牌记分系统，开始您的在线记分之旅。支持多人房间、实时记分、积分转让。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/auth/login">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .page { width: 100%; max-width: 400px; }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .card h1 {
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .card .subtitle {
            text-align: center;
            font-size: 14px;
            color: #999;
            margin-bottom: 24px;
        }
        
        .form-group { margin-bottom: 16px; }
        
        .form-group label {
            display: block;
            font-size: 13px;
            color: #666;
            margin-bottom: 6px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 16px;
            outline: none;
        }
        
        .form-group input:focus { border-color: #667eea; }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
        }
        
        .btn-submit:active { transform: scale(0.97); }
        
        .btn-quick {
            width: 100%;
            padding: 14px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn-quick:active { transform: scale(0.97); }
        
        .link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #999;
        }
        
        .link a { color: #667eea; text-decoration: none; font-weight: 600; }
        
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="page">
        <div class="card">
            <h1>🎮 计分系统</h1>
            <div class="subtitle">输入昵称登录</div>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="/auth/login">
                <?php if (isset($redirect) && $redirect): ?>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>昵称</label>
                    <input type="text" name="username" placeholder="输入你的昵称" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>密码</label>
                    <input type="password" name="password" placeholder="输入密码" required>
                </div>
                
                <button type="submit" class="btn-submit">登录</button>
            </form>
            
            <button class="btn-quick" onclick="quickEnter()">⚡ 免注册快速加入</button>
            
            <div class="link">
                还没有账号？<a href="/auth/register">立即注册</a>
            </div>
        </div>
    </div>
    
    <script>
        function quickEnter() {
            fetch('/auth/quick-register', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' } 
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) { location.href = '/lobby'; }
                else { alert(d.message); }
            });
        }
    </script>
</body>
</html>
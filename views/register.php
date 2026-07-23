<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <title>注册 - 打牌记分系统 | 免费在线记分工具</title>
    <meta name="description" content="注册打牌记分系统账号，免费使用在线记分服务。支持创建房间、多人记分、积分转让、历史记录查询。适合朋友聚会打牌使用。">
    <meta name="keywords" content="打牌记分系统注册,在线记分注册,免费记分工具,麻将记分,桌游记分">
    <meta name="author" content="打牌记分系统">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph -->
    <meta property="og:title" content="注册 - 打牌记分系统">
    <meta property="og:description" content="注册账号，免费使用在线记分服务。支持多人房间、实时记分、积分转让。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/auth/register">
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
        
        .form-group .hint {
            font-size: 11px;
            color: #bbb;
            margin-top: 4px;
        }
        
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
            <div class="subtitle">创建账号，开始记分</div>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="/auth/register" onsubmit="return validateForm()">
                <div class="form-group">
                    <label>昵称</label>
                    <input type="text" name="username" id="username" 
                           placeholder="输入昵称（2-20个字符）" 
                           maxlength="20" required autofocus>
                    <div class="hint">支持中文、英文、数字，登录时使用此昵称</div>
                </div>
                
                <div class="form-group">
                    <label>密码</label>
                    <input type="password" name="password" id="password" 
                           placeholder="至少6个字符" 
                           minlength="6" required>
                </div>
                
                <div class="form-group">
                    <label>确认密码</label>
                    <input type="password" name="confirm_password" id="confirmPassword" 
                           placeholder="再次输入密码" 
                           minlength="6" required>
                </div>
                
                <button type="submit" class="btn-submit">注册</button>
            </form>
            
            <div class="link">
                已有账号？<a href="/auth/login">立即登录</a>
            </div>
        </div>
    </div>
    
    <script>
        function validateForm() {
            var username = document.getElementById('username').value.trim();
            var password = document.getElementById('password').value;
            var confirm = document.getElementById('confirmPassword').value;
            
            if (!username) {
                alert('请输入昵称');
                return false;
            }
            if (username.length < 2) {
                alert('昵称至少2个字符');
                return false;
            }
            if (username.length > 20) {
                alert('昵称最多20个字符');
                return false;
            }
            if (password.length < 6) {
                alert('密码至少6个字符');
                return false;
            }
            if (password !== confirm) {
                alert('两次密码不一致');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
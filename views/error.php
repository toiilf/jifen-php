<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ===== SEO 信息 ===== -->
    <title>错误 - 打牌记分系统</title>
    <meta name="description" content="打牌记分系统错误页面">
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
            text-align: center;
        }
        
        h1 { color: #333; margin-bottom: 20px; font-size: 28px; }
        
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 15px;
        }
        .alert-error { background: #fee; color: #c33; border: 1px solid #fcc; }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ 错误</h1>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($message ?? '发生了未知错误'); ?>
        </div>
        <div style="margin-top: 20px;">
            <a href="/" class="btn">返回首页</a>
        </div>
    </div>
</body>
</html>
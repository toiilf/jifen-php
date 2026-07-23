<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ===== SEO 信息 ===== -->
    <title>404 - 页面未找到 - 打牌记分系统</title>
    <meta name="description" content="您访问的页面不存在，返回打牌记分系统首页。">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- 返回状态码 -->
    <?php http_response_code(404); ?>
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
        
        h1 { font-size: 72px; color: #667eea; margin-bottom: 10px; }
        p { color: #666; font-size: 18px; margin-bottom: 20px; }
        
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
        <h1>🔍</h1>
        <h2 style="color:#333;margin-bottom:10px;">404</h2>
        <p><?php echo htmlspecialchars($title ?? '页面未找到'); ?></p>
        <div style="margin-top: 20px;">
            <a href="/" class="btn">返回首页</a>
        </div>
    </div>
</body>
</html>
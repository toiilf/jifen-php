<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>已解锁安装 - 计分系统</title>
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
            padding: 40px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .icon { font-size: 60px; margin-bottom: 16px; }
        h2 { color: #333; margin-bottom: 12px; }
        p { color: #666; margin-bottom: 8px; line-height: 1.6; }
        .deleted-files {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 12px;
            margin: 12px 0;
            font-size: 14px;
            color: #333;
        }
        .deleted-files strong { color: #dc3545; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            margin-top: 16px;
        }
        .btn-secondary {
            background: #6c757d;
            margin-left: 10px;
        }
        .btn-danger {
            background: linear-gradient(135deg, #f5576c, #ee5a24);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔓</div>
        <h2>系统已解锁</h2>
        <p><?php echo htmlspecialchars($message ?? '系统已解锁，可以重新安装。'); ?></p>
        
        <?php if (isset($deleted_files) && !empty($deleted_files)): ?>
        <div class="deleted-files">
            ✅ 已删除：<strong><?php echo implode('</strong>、<strong>', $deleted_files); ?></strong>
        </div>
        <?php endif; ?>
        
        <p style="font-size:13px;color:#999;margin-top:8px;">
            删除 install.lock 和 .installed 文件后，系统允许重新安装
        </p>
        
        <div>
            <a href="/install" class="btn">🚀 开始重新安装</a>
            <a href="/" class="btn btn-secondary">返回首页</a>
        </div>
    </div>
</body>
</html>
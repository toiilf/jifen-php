<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- ===== SEO 信息 ===== -->
    <title>安装向导 - 打牌记分系统 | 免费在线记分工具</title>
    <meta name="description" content="打牌记分系统安装向导，快速部署您的在线记分平台。支持多人房间、实时记分、积分转让、历史记录。适合打牌、麻将、桌游等场景。">
    <meta name="keywords" content="打牌记分系统,在线记分,麻将记分,桌游记分,积分系统,安装向导">
    <meta name="robots" content="noindex, nofollow">
    <meta name="author" content="打牌记分系统">
    
    <!-- Open Graph -->
    <meta property="og:title" content="安装向导 - 打牌记分系统">
    <meta property="og:description" content="快速部署您的在线记分平台，支持多人房间、实时记分、积分转让。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
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
        
        .page { width: 100%; max-width: 480px; }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .card h1 {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section {
            background: #f8f9fa;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 16px;
        }
        
        .section h3 {
            font-size: 15px;
            color: #667eea;
            margin-bottom: 14px;
        }
        
        .form-group { margin-bottom: 12px; }
        .form-group:last-child { margin-bottom: 0; }
        
        .form-group label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
        }
        
        .form-group input:focus { border-color: #667eea; }
        
        .row { display: flex; gap: 10px; }
        .row .form-group { flex: 1; }
        .row .form-group.port { max-width: 90px; flex: none; }
        
        .ssl-option {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e1e8ed;
        }
        
        .ssl-option label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #666;
            cursor: pointer;
        }
        
        .ssl-option input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .btn-test {
            padding: 8px 16px;
            background: #f0f0f0;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            margin-bottom: 12px;
        }
        .btn-test:hover { background: #e0e0e0; }
        
        .test-result {
            font-size: 12px;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            display: none;
        }
        .test-result.test-success { background: #d4edda; color: #155724; display: block; }
        .test-result.test-error { background: #f8d7da; color: #721c24; display: block; }
        
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
            transition: all 0.2s;
        }
        .btn-submit:active { transform: scale(0.97); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
        
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-error { background: #f8d7da; color: #721c24; }
        
        .success-box {
            text-align: center;
            padding: 20px;
        }
        .success-box .icon {
            font-size: 60px;
            margin-bottom: 16px;
        }
        .success-box h2 {
            font-size: 20px;
            color: #28a745;
            margin-bottom: 12px;
        }
        .success-box .info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 14px;
            margin: 16px 0;
            text-align: left;
        }
        .success-box .info p {
            font-size: 14px;
            color: #666;
            margin-bottom: 6px;
        }
        .success-box .info strong {
            color: #333;
        }
        .success-box .tip {
            font-size: 13px;
            color: #999;
            margin-top: 12px;
            line-height: 1.6;
        }
        .success-box .tip strong {
            color: #dc3545;
            background: #fff3cd;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
        }
        .success-box .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            margin-top: 10px;
            font-size: 16px;
        }
        .success-box .btn-unlock {
            display: inline-block;
            padding: 10px 24px;
            background: #ffc107;
            color: #333;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            margin-top: 8px;
            font-size: 13px;
        }
        .success-box .btn-unlock:hover { background: #e0a800; }
        
        /* ===== 清空数据选项样式 ===== */
        .clear-data-option {
            margin-top: 4px;
            padding: 12px;
            background: #fff5f5;
            border-radius: 10px;
            border: 1px solid #fcc;
        }
        .clear-data-option label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #dc3545;
            cursor: pointer;
            font-weight: 600;
        }
        .clear-data-option input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            flex-shrink: 0;
        }
        .warning-box {
            display: none;
            margin-top: 10px;
            padding: 12px;
            background: #f8d7da;
            border-radius: 8px;
            font-size: 13px;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .warning-box.show { display: block; }
        .warning-box strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }
        
        .install-hint {
            background: #e8f4fd;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 16px;
            font-size: 12px;
            color: #0056b3;
            border-left: 4px solid #667eea;
        }
        .install-hint strong { display: block; margin-bottom: 4px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="card">
            <h1>🎮 计分系统 - 安装向导</h1>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success) && $success): ?>
                <div class="success-box">
                    <div class="icon">✅</div>
                    <h2>安装完成！</h2>
                    <div class="info">
                        <p>数据库：<strong><?php echo htmlspecialchars($db_name ?? 'card_game'); ?></strong></p>
                        <p>管理员：<strong><?php echo htmlspecialchars($admin_user ?? 'admin'); ?></strong></p>
                        <p>请牢记管理员密码</p>
                    </div>
                    <p class="tip">
                        🔒 系统已锁定安装。<br>
                        如需重新安装，请使用管理员账号登录后访问 <strong>/uninstall</strong> 解锁，<br>
                        或手动删除项目根目录下的 <strong>install.lock</strong> 和 <strong>.installed</strong> 文件。
                    </p>
                    <a href="/" class="btn">🚀 开始使用</a>
                    <br>
                    <a href="/admin/login" class="btn-unlock">🔑 进入管理后台</a>
                </div>
            <?php else: ?>
                <form method="POST" action="/install" id="installForm" onsubmit="return handleSubmit()">
                    <!-- 数据库配置 -->
                    <div class="section">
                        <h3>📦 数据库配置</h3>
                        <div class="row">
                            <div class="form-group">
                                <label>主机地址</label>
                                <input type="text" name="db_host" value="<?php echo htmlspecialchars($db_host ?? 'localhost'); ?>" placeholder="localhost" required>
                            </div>
                            <div class="form-group port">
                                <label>端口</label>
                                <input type="text" name="db_port" value="<?php echo htmlspecialchars($db_port ?? '3306'); ?>" placeholder="3306" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>数据库用户名</label>
                            <input type="text" name="db_user" value="<?php echo htmlspecialchars($db_user ?? 'root'); ?>" placeholder="root" required>
                        </div>
                        <div class="form-group">
                            <label>数据库密码</label>
                            <input type="password" name="db_password" placeholder="留空表示无密码">
                        </div>
                        <div class="form-group">
                            <label>数据库名称</label>
                            <input type="text" name="db_name" value="<?php echo htmlspecialchars($db_name ?? 'card_game'); ?>" placeholder="card_game" required id="dbNameInput">
                        </div>
                        
                        <div class="ssl-option">
                            <label>
                                <input type="checkbox" name="db_ssl" id="dbSsl">
                                🔒 启用 SSL 连接（使用云数据库或需要安全连接时勾选）
                            </label>
                        </div>
                        
                        <button type="button" class="btn-test" onclick="testConnection()">🔍 测试数据库连接</button>
                        <div id="testResult" class="test-result"></div>
                    </div>
                    
                    <!-- 管理员账号 -->
                    <div class="section">
                        <h3>👤 管理员账号</h3>
                        <div class="form-group">
                            <label>管理员用户名</label>
                            <input type="text" name="admin_user" value="<?php echo htmlspecialchars($admin_user ?? 'admin'); ?>" placeholder="admin" required>
                        </div>
                        <div class="form-group">
                            <label>管理员密码</label>
                            <input type="password" name="admin_password" id="adminPassword" placeholder="至少6个字符" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>确认密码</label>
                            <input type="password" name="admin_password_confirm" id="adminPasswordConfirm" placeholder="再次输入密码" required minlength="6">
                        </div>
                    </div>
                    
                    <!-- 安装选项 -->
                    <div class="section">
                        <h3>⚙️ 安装选项</h3>
                        
                        <div class="install-hint">
                            <strong>💡 提示</strong>
                            <?php if (isset($db_name) && $db_name): ?>
                                数据库 <strong><?php echo htmlspecialchars($db_name); ?></strong> 中如果已有数据，勾选下方选项将清空重建。
                            <?php else: ?>
                                如果数据库中已有数据，勾选下方选项将清空重建。
                            <?php endif; ?>
                        </div>
                        
                        <!-- 清空数据选项 -->
                        <div class="clear-data-option">
                            <label>
                                <input type="checkbox" name="clear_data" id="clearData" onclick="toggleClearData()">
                                <span>🗑️ 清空现有数据（删除所有表并重建）</span>
                            </label>
                            <div class="warning-box" id="clearDataWarning">
                                <strong>⚠️ 危险操作</strong>
                                此操作将<strong>永久删除</strong>数据库 <strong id="warnDbName"><?php echo htmlspecialchars($db_name ?? 'card_game'); ?></strong> 中的所有数据！<br>
                                所有用户、房间、游戏记录将被清空，此操作不可恢复！
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="submitBtn">🚀 开始安装</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function toggleClearData() {
            var checked = document.getElementById('clearData').checked;
            var warning = document.getElementById('clearDataWarning');
            var btn = document.getElementById('submitBtn');
            var dbName = document.getElementById('dbNameInput').value || 'card_game';
            
            if (checked) {
                warning.className = 'warning-box show';
                btn.textContent = '⚠️ 确认清空并安装';
                btn.style.background = 'linear-gradient(135deg, #dc3545, #ee5a24)';
                document.getElementById('warnDbName').textContent = dbName;
            } else {
                warning.className = 'warning-box';
                btn.textContent = '🚀 开始安装';
                btn.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
            }
        }
        
        // 数据库名称变化时更新警告中的名称
        document.getElementById('dbNameInput').addEventListener('input', function() {
            document.getElementById('warnDbName').textContent = this.value || 'card_game';
        });
        
        function handleSubmit() {
            var pwd = document.getElementById('adminPassword').value;
            var confirm = document.getElementById('adminPasswordConfirm').value;
            
            if (pwd !== confirm) {
                alert('两次输入的管理员密码不一致！');
                return false;
            }
            if (pwd.length < 6) {
                alert('管理员密码至少6个字符！');
                return false;
            }
            
            var clearData = document.getElementById('clearData').checked;
            if (clearData) {
                var dbName = document.getElementById('dbNameInput').value || 'card_game';
                var confirmed = confirm(
                    '⚠️ 确认清空所有数据？\n\n' +
                    '此操作将删除数据库 ' + dbName + ' 中的所有数据！\n\n' +
                    '所有用户、房间、游戏记录将被清空，此操作不可恢复！\n\n' +
                    '确定继续？'
                );
                if (!confirmed) return false;
                
                // 二次确认：输入数据库名称
                var input = prompt('请输入数据库名称 "' + dbName + '" 以确认清空操作：');
                if (input !== dbName) {
                    alert('数据库名称不匹配，操作已取消');
                    return false;
                }
            }
            
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = '正在安装...';
            return true;
        }
        
        async function testConnection() {
            var resultDiv = document.getElementById('testResult');
            resultDiv.className = 'test-result';
            resultDiv.textContent = '⏳ 正在测试连接...';
            resultDiv.style.display = 'block';
            
            var data = {
                db_host: document.querySelector('input[name="db_host"]').value,
                db_port: document.querySelector('input[name="db_port"]').value,
                db_user: document.querySelector('input[name="db_user"]').value,
                db_password: document.querySelector('input[name="db_password"]').value,
                db_name: document.querySelector('input[name="db_name"]').value,
                db_ssl: document.getElementById('dbSsl').checked
            };
            
            try {
                var response = await fetch('/install/test-connection', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                var result = await response.json();
                
                resultDiv.className = 'test-result test-' + (result.success ? 'success' : 'error');
                resultDiv.textContent = (result.success ? '✅ ' : '❌ ') + result.message;
            } catch (error) {
                resultDiv.className = 'test-result test-error';
                resultDiv.textContent = '❌ 网络请求失败：' + error.message;
            }
        }
        
        // 回车键触发提交
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                var active = document.activeElement;
                if (active && active.tagName === 'INPUT') {
                    document.getElementById('installForm').submit();
                }
            }
        });
    </script>
</body>
</html>
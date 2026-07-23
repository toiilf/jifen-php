<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- ===== SEO 信息 ===== -->
    <title>个人中心 - 打牌记分系统 | 我的记分统计</title>
    <meta name="description" content="查看您的打牌记分统计，包括总场次、胜场、最近游戏记录。修改个人资料和密码。">
    <meta name="keywords" content="打牌记分系统,个人中心,记分统计,游戏记录,修改资料">
    <meta name="author" content="打牌记分系统">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph -->
    <meta property="og:title" content="个人中心 - 打牌记分系统">
    <meta property="og:description" content="查看您的记分统计，包括总场次、胜场、最近游戏记录。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:site_name" content="打牌记分系统">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/profile">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 12px;
        }
        
        .page { max-width: 600px; margin: 0 auto; }
        
        .header {
            background: white;
            border-radius: 20px;
            padding: 14px 20px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header .back { text-decoration: none; color: #667eea; font-size: 15px; font-weight: 600; }
        .header .title { font-size: 18px; font-weight: 700; color: #333; }
        .header .placeholder { width: 40px; }
        
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 14px 18px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        
        .profile-card .avatar {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
        }
        
        .profile-card .info { flex: 1; min-width: 0; }
        .profile-card .nickname { font-size: 16px; font-weight: 700; color: #333; }
        .profile-card .username { font-size: 12px; color: #999; }
        .profile-card .actions { display: flex; gap: 6px; flex-shrink: 0; }
        
        .profile-card .btn-sm {
            padding: 6px 12px;
            border-radius: 12px;
            border: none;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-edit { background: #667eea; color: white; }
        .btn-pwd { background: #f0f0f0; color: #666; }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        
        .stat-value { font-size: 24px; font-weight: 800; color: #667eea; }
        .stat-label { font-size: 11px; color: #999; margin-top: 4px; font-weight: 500; }
        
        .section-title {
            color: white;
            font-size: 15px;
            font-weight: 700;
            margin: 16px 0 8px;
            display: flex;
            justify-content: space-between;
        }
        
        .section-title a { color: white; font-size: 13px; text-decoration: none; opacity: 0.9; }
        
        .game-list { display: flex; flex-direction: column; gap: 8px; }
        
        .game-card {
            background: white;
            border-radius: 16px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        
        .game-icon {
            width: 40px; height: 40px; min-width: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .game-icon.win { background: #d4edda; }
        .game-icon.lose { background: #f8d7da; }
        
        .game-info { flex: 1; min-width: 0; }
        .game-room { font-size: 14px; font-weight: 600; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .game-meta { font-size: 11px; color: #999; margin-top: 2px; }
        
        .game-result { text-align: right; }
        .result-text {
            font-size: 12px; font-weight: 700; padding: 3px 8px;
            border-radius: 8px; display: inline-block;
        }
        .result-text.win { background: #d4edda; color: #155724; }
        .result-text.lose { background: #f8d7da; color: #721c24; }
        .score { font-size: 14px; font-weight: 700; margin-top: 4px; }
        .score.positive { color: #28a745; }
        .score.negative { color: #dc3545; }
        
        .empty-state {
            text-align: center; padding: 30px;
            color: rgba(255,255,255,0.8); font-size: 14px;
        }
        
        .modal {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); z-index: 100;
            display: none; justify-content: center; align-items: center;
        }
        .modal.show { display: flex; }
        
        .modal-content {
            background: white; border-radius: 20px; padding: 24px 20px;
            width: 90%; max-width: 380px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .modal-content h3 { font-size: 18px; margin-bottom: 16px; text-align: center; color: #333; }
        .modal-content .form-group { margin-bottom: 14px; }
        .modal-content label { display: block; font-size: 13px; color: #666; margin-bottom: 6px; font-weight: 600; }
        .modal-content input {
            width: 100%; padding: 12px 14px; border: 2px solid #e1e8ed;
            border-radius: 12px; font-size: 16px; outline: none;
        }
        .modal-content input:focus { border-color: #667eea; }
        .modal-content .btn {
            width: 100%; padding: 14px; border: none; border-radius: 12px;
            font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 6px;
        }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-secondary { background: #f0f0f0; color: #666; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <a href="/lobby" class="back">← 返回</a>
            <span class="title">个人中心</span>
            <span class="placeholder"></span>
        </div>
        
        <div class="profile-card">
            <div class="avatar"><?php echo htmlspecialchars(mb_substr($user['nickname'], -1)); ?></div>
            <div class="info">
                <div class="nickname"><?php echo htmlspecialchars($user['nickname']); ?></div>
                <div class="username">@<?php echo htmlspecialchars($user['username']); ?></div>
            </div>
            <div class="actions">
                <button class="btn-sm btn-edit" onclick="showNicknameModal()">✏️ 昵称</button>
                <button class="btn-sm btn-pwd" onclick="showPasswordModal()">🔒 密码</button>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $user['total_games']; ?></div>
                <div class="stat-label">总场次</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $user['wins']; ?></div>
                <div class="stat-label">胜场</div>
            </div>
        </div>
        
        <div class="section-title">
            <span>🎮 最近游戏</span>
            <a href="/history">查看全部 →</a>
        </div>
        <div class="game-list">
            <?php if (isset($recentGames) && count($recentGames) > 0): ?>
                <?php foreach ($recentGames as $game): ?>
                    <div class="game-card">
                        <div class="game-icon <?php echo $game['myNetScore'] >= 0 ? 'win' : 'lose'; ?>">
                            <?php echo $game['myNetScore'] >= 0 ? '🏆' : '💪'; ?>
                        </div>
                        <div class="game-info">
                            <div class="game-room"><?php echo htmlspecialchars($game['room_name']); ?></div>
                            <div class="game-meta">
                                <?php echo date('m-d H:i', strtotime($game['game_date'])); ?> · <?php echo $game['player_count']; ?>人
                            </div>
                        </div>
                        <div class="game-result">
                            <div class="result-text <?php echo $game['myNetScore'] >= 0 ? 'win' : 'lose'; ?>">
                                <?php echo $game['myNetScore'] >= 0 ? '赢' : '输'; ?>
                            </div>
                            <div class="score <?php echo $game['myNetScore'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $game['myNetScore'] >= 0 ? '+' : ''; ?><?php echo $game['myNetScore']; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">暂无游戏记录</div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="modal" id="nicknameModal">
        <div class="modal-content">
            <h3>✏️ 修改昵称</h3>
            <div class="form-group">
                <label>新昵称</label>
                <input type="text" id="newNickname" value="<?php echo htmlspecialchars($user['nickname']); ?>" maxlength="20">
            </div>
            <button class="btn btn-primary" onclick="saveNickname()">保存</button>
            <button class="btn btn-secondary" onclick="closeModal('nicknameModal')">取消</button>
        </div>
    </div>
    
    <div class="modal" id="passwordModal">
        <div class="modal-content">
            <h3>🔒 修改密码</h3>
            <div class="form-group">
                <label>当前密码</label>
                <input type="password" id="oldPassword" placeholder="输入当前密码">
            </div>
            <div class="form-group">
                <label>新密码</label>
                <input type="password" id="newPassword" placeholder="至少6个字符" minlength="6">
            </div>
            <div class="form-group">
                <label>确认新密码</label>
                <input type="password" id="confirmPassword" placeholder="再次输入新密码">
            </div>
            <button class="btn btn-primary" onclick="savePassword()">保存</button>
            <button class="btn btn-secondary" onclick="closeModal('passwordModal')">取消</button>
        </div>
    </div>
    
    <script>
        function showNicknameModal() {
            document.getElementById('nicknameModal').classList.add('show');
            document.getElementById('newNickname').focus();
        }
        
        function showPasswordModal() {
            document.getElementById('passwordModal').classList.add('show');
            document.getElementById('oldPassword').focus();
        }
        
        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
            if (id === 'passwordModal') {
                document.getElementById('oldPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
            }
        }
        
        function saveNickname() {
    var nickname = document.getElementById('newNickname').value.trim();
    if (!nickname) { alert('昵称不能为空'); return; }
    
    fetch('/profile/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nickname: nickname })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) { 
            alert('修改成功！下次登录请使用新昵称：' + nickname); 
            location.reload(); 
        } else { 
            alert(d.message); 
        }
    });
}
        
        function savePassword() {
            var oldPwd = document.getElementById('oldPassword').value;
            var newPwd = document.getElementById('newPassword').value;
            var confirmPwd = document.getElementById('confirmPassword').value;
            
            if (!oldPwd) { alert('请输入当前密码'); return; }
            if (!newPwd || newPwd.length < 6) { alert('新密码至少6个字符'); return; }
            if (newPwd !== confirmPwd) { alert('两次密码不一致'); return; }
            
            fetch('/profile/change-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ old_password: oldPwd, new_password: newPwd })
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) { alert('密码修改成功'); closeModal('passwordModal'); }
                else { alert(d.message); }
            });
        }
        
        window.onclick = function(e) {
            if (e.target.classList.contains('modal')) e.target.classList.remove('show');
        };
    </script>
</body>
</html>
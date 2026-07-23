<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php echo csrfMetaTag(); ?>
    
    <!-- ===== SEO 信息 ===== -->
    <title>房间：<?php echo htmlspecialchars($room['room_name']); ?> - 打牌记分系统</title>
    <meta name="description" content="正在打牌记分系统房间「<?php echo htmlspecialchars($room['room_name']); ?>」中，当前 <?php echo count($otherPlayers) + 1; ?>/<?php echo $room['max_players']; ?> 人。支持实时记分、积分转让。">
    <meta name="keywords" content="打牌记分系统,房间,<?php echo htmlspecialchars($room['room_name']); ?>,在线记分,实时记分,积分转让">
    <meta name="author" content="打牌记分系统">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph -->
    <meta property="og:title" content="房间「<?php echo htmlspecialchars($room['room_name']); ?>」- 打牌记分系统">
    <meta property="og:description" content="正在打牌记分系统房间中，当前 <?php echo count($otherPlayers) + 1; ?>/<?php echo $room['max_players']; ?> 人。实时记分中。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:site_name" content="打牌记分系统">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/room/<?php echo $room['id']; ?>">
    
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
            padding: 16px 20px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 18px; font-weight: 700; color: #333;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1;
            cursor: pointer; transition: color 0.2s;
        }
        
        .header h1:hover { color: #667eea; }
        
        .header .count {
            background: #667eea; color: white;
            padding: 6px 14px; border-radius: 20px;
            font-size: 14px; font-weight: 700; margin: 0 8px; white-space: nowrap;
        }
        
        .header .btn-back {
            background: #667eea; color: white; border: none;
            padding: 8px 16px; border-radius: 20px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            white-space: nowrap; text-decoration: none;
        }
        
        .my-card {
            background: white; border-radius: 16px; padding: 16px 18px;
            margin-bottom: 12px; display: flex; align-items: center; gap: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 2px solid #667eea;
        }
        
        .my-card .avatar {
            width: 46px; height: 46px; min-width: 46px; border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2); color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 700;
        }
        
        .my-card .my-info { flex: 1; }
        .my-card .name { font-size: 15px; font-weight: 700; color: #333; }
        .my-card .label { font-size: 11px; color: #999; }
        .my-card .score { font-size: 28px; font-weight: 800; color: #667eea; text-align: right; }
        .my-card .score span { font-size: 13px; font-weight: 500; color: #999; }
        .my-card .owner-badge {
            display: inline-block;
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: white; padding: 2px 8px; border-radius: 10px;
            font-size: 10px; font-weight: 600; margin-left: 6px;
        }
        
        .section-label {
            color: white; font-size: 16px; font-weight: 700;
            margin: 16px 0 10px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        
        .players { display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px; }
        
        .player {
            background: white; border-radius: 16px; padding: 18px 20px;
            display: flex; align-items: center; gap: 14px;
            cursor: pointer; transition: all 0.15s;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .player:active { transform: scale(0.98); }
        
        .player.selected {
            border: 3px solid #ff6b6b;
            box-shadow: 0 0 20px rgba(255,107,107,0.3);
            animation: pulse 1.2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 15px rgba(255,107,107,0.3); }
            50% { box-shadow: 0 0 25px rgba(255,107,107,0.5); }
        }
        
        .player .p-avatar {
            width: 50px; height: 50px; min-width: 50px; border-radius: 50%;
            background: linear-gradient(135deg, #a8e6cf, #88d8b0);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 700; color: #3d7a5c;
        }
        .player.selected .p-avatar { background: linear-gradient(135deg, #ff6b6b, #ee5a24); color: white; }
        .player .p-info { flex: 1; }
        .player .p-name { font-size: 16px; font-weight: 700; color: #333; }
        .player .p-hint { font-size: 12px; color: #bbb; margin-top: 2px; }
        .player.selected .p-hint { color: #ff6b6b; font-weight: 600; }
        .player .p-score { font-size: 22px; font-weight: 800; color: #667eea; white-space: nowrap; }
        .player .p-score span { font-size: 13px; font-weight: 500; color: #999; }
        .player .owner-tag { background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 8px; font-size: 11px; font-weight: 600; }
        
        .empty { color: rgba(255,255,255,0.7); text-align: center; padding: 30px; font-size: 15px; }
        
        .records {
            background: white; border-radius: 20px; padding: 16px;
            margin-bottom: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .records h3 { font-size: 16px; color: #333; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        
        .record { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f8f8f8; }
        .record:last-child { border-bottom: none; }
        .record .r-from { color: #ff6b6b; font-weight: 600; }
        .record .r-to { color: #2ed573; font-weight: 600; }
        .record .r-time { font-size: 12px; color: #bbb; }
        .record .r-amount { font-weight: 700; font-size: 16px; color: #667eea; }
        
        .share-buttons { display: flex; gap: 10px; }
        .share-buttons button {
            flex: 1; padding: 12px; border: none; border-radius: 12px;
            font-size: 14px; font-weight: 600; cursor: pointer;
        }
        .btn-copy-link { background: #667eea; color: white; }
        .btn-qrcode { background: #ffc107; color: #333; }
        
        .overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); z-index: 98; display: none; }
        .overlay.show { display: block; }
        
        .panel {
            position: fixed; bottom: 0; left: 0; right: 0; background: white;
            border-radius: 20px 20px 0 0; padding: 16px 14px 20px;
            z-index: 99; box-shadow: 0 -5px 30px rgba(0,0,0,0.2);
            transform: translateY(100%); transition: transform 0.3s ease;
            max-width: 600px; margin: 0 auto;
        }
        .panel.show { transform: translateY(0); }
        .panel h3 { text-align: center; font-size: 16px; margin-bottom: 12px; color: #333; }
        .panel .target { color: #ff6b6b; font-weight: 700; }
        .panel .row { display: flex; gap: 8px; align-items: center; }
        .panel input {
            flex: 1; min-width: 0; padding: 12px 14px;
            border: 2px solid #e1e8ed; border-radius: 12px;
            font-size: 18px; text-align: center; font-weight: 700; outline: none;
        }
        .panel input:focus { border-color: #ff6b6b; }
        .panel .btn-ok {
            background: #ff6b6b; color: white; border: none;
            padding: 12px 20px; border-radius: 12px;
            font-size: 15px; font-weight: 700; cursor: pointer; white-space: nowrap; flex-shrink: 0;
        }
        .panel .btn-ok:disabled { opacity: 0.6; cursor: not-allowed; }
        .panel .btn-cancel {
            width: 100%; margin-top: 8px; padding: 12px;
            background: #f0f0f0; border: none; border-radius: 12px;
            font-size: 15px; color: #666; cursor: pointer; font-weight: 600;
        }
        
        .modal {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); z-index: 200;
            display: none; justify-content: center; align-items: center;
        }
        .modal.show { display: flex; }
        
        .modal-content {
            background: white; border-radius: 20px; padding: 24px 20px;
            width: 90%; max-width: 380px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .modal-content h3 { font-size: 20px; margin-bottom: 18px; text-align: center; color: #333; }
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
        .btn-save { background: #28a745; color: white; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(-50%) translateY(-10px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        
        @media (max-width: 360px) {
            .panel { padding: 14px 10px 16px; }
            .panel h3 { font-size: 15px; }
            .panel input { font-size: 16px; padding: 10px 12px; }
            .panel .btn-ok { padding: 10px 16px; font-size: 14px; }
            .panel .btn-cancel { padding: 10px; font-size: 14px; }
        }
        
        @supports (padding-bottom: env(safe-area-inset-bottom)) {
            .panel { padding-bottom: calc(20px + env(safe-area-inset-bottom)); }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <h1 onclick="copyRoomName()" title="点击复制房间名称">房间：<?php echo htmlspecialchars($room['room_name']); ?></h1>
            <span class="count" id="playerCount"><?php echo count($otherPlayers) + 1; ?>/<?php echo $room['max_players']; ?>人</span>
            <a href="/lobby" class="btn-back">返回大厅</a>
        </div>
        
        <div class="my-card">
            <div class="avatar"><?php echo htmlspecialchars(mb_substr($myPlayer['nickname'] ?? $user['nickname'], -1)); ?></div>
            <div class="my-info">
                <div class="name">
                    <?php echo htmlspecialchars($myPlayer['nickname'] ?? $user['nickname']); ?> (我)
                    <?php if ($room['creator_id'] == $userId): ?><span class="owner-badge">👑 房主</span><?php endif; ?>
                </div>
                <div class="label">我的积分</div>
            </div>
            <div class="score" id="myScore"><?php echo $myPlayer['current_score'] ?? 0; ?> <span>分</span></div>
        </div>
        
        <div class="section-label">👥 点击下方玩家转让积分</div>
        <div class="players" id="playersList">
            <?php if (count($otherPlayers) > 0): ?>
                <?php foreach ($otherPlayers as $p): ?>
                    <div class="player" id="player-<?php echo $p['user_id']; ?>" onclick="selectPlayer(<?php echo $p['user_id']; ?>, '<?php echo addslashes($p['nickname']); ?>')">
                        <div class="p-avatar"><?php echo htmlspecialchars(mb_substr($p['nickname'], -1)); ?></div>
                        <div class="p-info">
                            <div class="p-name">
                                <?php echo htmlspecialchars($p['nickname']); ?>
                                <?php if ($p['user_id'] == $room['creator_id']): ?><span class="owner-tag">👑 房主</span><?php endif; ?>
                            </div>
                            <div class="p-hint">点击转让积分</div>
                        </div>
                        <div class="p-score"><?php echo $p['current_score'] ?? 0; ?> <span>分</span></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">等待其他玩家加入...</div>
            <?php endif; ?>
        </div>
        
        <div class="records">
            <h3>📋 转让记录</h3>
            <div id="transferHistory">
                <?php if (isset($transfers) && count($transfers) > 0): ?>
                    <?php foreach ($transfers as $t): ?>
                        <div class="record">
                            <div>
                                <span class="r-from"><?php echo htmlspecialchars($t['from_nickname']); ?></span> → <span class="r-to"><?php echo htmlspecialchars($t['to_nickname']); ?></span>
                                <div class="r-time"><?php echo date('Y-m-d H:i:s', strtotime($t['created_at'])); ?></div>
                            </div>
                            <div class="r-amount"><?php echo $t['amount']; ?> 分</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty" style="color:#999;">暂无转让记录</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="records">
            <h3>📤 邀请好友</h3>
            <div class="share-buttons">
                <button class="btn-copy-link" onclick="copyShareLink()">🔗 复制链接</button>
                <button class="btn-qrcode" onclick="showQRCode()">📱 二维码</button>
            </div>
        </div>
    </div>
    
    <div class="overlay" id="overlay" onclick="cancelTransfer()"></div>
    
    <div class="panel" id="panel">
        <h3>💸 转让给 <span class="target" id="targetName"></span></h3>
        <div class="row">
            <input type="number" id="amount" min="1" placeholder="输入金额" onkeyup="if(event.key==='Enter')doTransfer()">
            <button class="btn-ok" onclick="doTransfer()">确认</button>
        </div>
        <button class="btn-cancel" onclick="cancelTransfer()">取消</button>
    </div>
    
    <?php if (isset($showGuide) && $showGuide): ?>
    <div class="modal show" id="editModal">
        <div class="modal-content">
            <h3>✏️ 修改资料</h3>
            <p style="text-align:center;font-size:13px;color:#999;margin-bottom:16px;">系统已为您生成账号，建议修改</p>
            <div class="form-group">
                <label>昵称</label>
                <input type="text" id="newNickname" value="<?php echo htmlspecialchars($user['nickname'] ?? ''); ?>" maxlength="20">
            </div>
            <div class="form-group">
                <label>密码（留空不修改）</label>
                <input type="password" id="newPassword" placeholder="123456" minlength="6">
            </div>
            <button class="btn btn-save" onclick="saveProfile()">💾 保存</button>
            <button class="btn btn-secondary" onclick="closeEditModal()" style="margin-top:6px;">跳过</button>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        var roomId = <?php echo $room['id']; ?>;
        var userId = <?php echo $userId; ?>;
        var selectedId = null;
        
        // ===== 防抖：防止快速点击 =====
        var transferLock = false;
        
        // ===== 获取 CSRF Token =====
        function getCsrfToken() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }
        
        // ===== 核心配置 =====
        var CONFIG = {
            refreshInterval: 1500,
            maxErrors: 5,
            reconnectDelay: 3000,
            maxRetries: 10
        };
        
        // ===== 状态管理 =====
        var state = {
            timerId: null,
            isRefreshing: false,
            errorCount: 0,
            retryCount: 0,
            lastData: null,
            isRunning: true,
            isRecovering: false,
            pageVisible: true
        };
        
        // ===== 页面可见性检测 =====
        document.addEventListener('visibilitychange', function() {
            state.pageVisible = !document.hidden;
            if (state.pageVisible) {
                refreshData();
            }
        });
        
        // ===== 安全定时器 =====
        function safeInterval(callback, interval) {
            if (state.timerId) {
                clearInterval(state.timerId);
                state.timerId = null;
            }
            state.timerId = setInterval(function() {
                if (!state.pageVisible) return;
                if (state.isRefreshing) return;
                if (!state.isRunning) return;
                callback();
            }, interval);
            return state.timerId;
        }
        
        // ===== 刷新数据 =====
        function refreshData() {
            if (state.isRefreshing) return;
            state.isRefreshing = true;
            
            fetch('/api/room/' + roomId)
                .then(function(response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(function(data) {
                    state.errorCount = 0;
                    state.retryCount = 0;
                    if (data.success) {
                        updateUI(data);
                    }
                })
                .catch(function(error) {
                    state.errorCount++;
                    if (state.errorCount >= CONFIG.maxErrors) {
                        recoverConnection();
                    }
                })
                .finally(function() {
                    state.isRefreshing = false;
                });
        }
        
        // ===== 连接恢复 =====
        function recoverConnection() {
            if (state.isRecovering) return;
            state.isRecovering = true;
            state.retryCount++;
            
            if (state.retryCount > CONFIG.maxRetries) {
                state.isRecovering = false;
                return;
            }
            
            if (state.timerId) {
                clearInterval(state.timerId);
                state.timerId = null;
            }
            
            var delay = CONFIG.reconnectDelay * Math.min(state.retryCount, 5);
            setTimeout(function() {
                state.errorCount = 0;
                state.isRecovering = false;
                startPolling();
            }, delay);
        }
        
        // ===== 更新UI =====
        function updateUI(data) {
            if (!data || !data.players) return;
            
            // 数据指纹对比
            var fingerprint = JSON.stringify({
                players: data.players.map(function(p) {
                    return { id: p.user_id, score: p.current_score };
                }),
                transfers: (data.transfers || []).slice(0, 5).map(function(t) {
                    return t.id;
                })
            });
            
            if (state.lastData === fingerprint) return;
            state.lastData = fingerprint;
            
            // 更新我的积分
            var myScore = 0;
            for (var i = 0; i < data.players.length; i++) {
                if (data.players[i].user_id === userId) {
                    myScore = data.players[i].current_score || 0;
                    break;
                }
            }
            var scoreEl = document.getElementById('myScore');
            if (scoreEl) {
                var newText = myScore + ' <span>分</span>';
                if (scoreEl.innerHTML !== newText) {
                    scoreEl.innerHTML = newText;
                }
            }
            
            // 更新人数
            var countEl = document.getElementById('playerCount');
            if (countEl && data.players) {
                var newCount = data.players.length + '/' + data.room.max_players + '人';
                if (countEl.textContent !== newCount) {
                    countEl.textContent = newCount;
                }
            }
            
            // 更新玩家列表
            var list = document.getElementById('playersList');
            if (!list || !data.players) return;
            
            var html = '';
            var hasOtherPlayers = false;
            for (var i = 0; i < data.players.length; i++) {
                var p = data.players[i];
                if (p.user_id === userId) continue;
                hasOtherPlayers = true;
                var isOwner = (p.user_id === data.room.creator_id);
                var ownerTag = isOwner ? ' <span class="owner-tag">👑 房主</span>' : '';
                var avatar = p.nickname ? p.nickname.charAt(p.nickname.length - 1) : '?';
                var score = p.current_score || 0;
                
                html += '<div class="player" id="player-' + p.user_id + '" onclick="selectPlayer(' + p.user_id + ', \'' + p.nickname.replace(/'/g, "\\'") + '\')">';
                html += '<div class="p-avatar">' + avatar + '</div>';
                html += '<div class="p-info"><div class="p-name">' + p.nickname + ownerTag + '</div>';
                html += '<div class="p-hint">点击转让积分</div></div>';
                html += '<div class="p-score">' + score + ' <span>分</span></div>';
                html += '</div>';
            }
            
            if (!hasOtherPlayers) {
                html = '<div class="empty">等待其他玩家加入...</div>';
            }
            
            if (list.innerHTML !== html) {
                list.innerHTML = html;
            }
            
            // 更新转让记录
            var rec = document.getElementById('transferHistory');
            if (!rec || !data.transfers) return;
            
            if (data.transfers.length === 0) {
                var emptyHtml = '<div class="empty" style="color:#999;">暂无转让记录</div>';
                if (rec.innerHTML !== emptyHtml) {
                    rec.innerHTML = emptyHtml;
                }
            } else {
                var rh = '';
                var showCount = Math.min(data.transfers.length, 50);
                for (var j = 0; j < showCount; j++) {
                    var t = data.transfers[j];
                    var date = new Date(t.created_at);
                    var time = date.getFullYear() + '-' + 
                               String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                               String(date.getDate()).padStart(2, '0') + ' ' + 
                               String(date.getHours()).padStart(2, '0') + ':' + 
                               String(date.getMinutes()).padStart(2, '0') + ':' + 
                               String(date.getSeconds()).padStart(2, '0');
                    rh += '<div class="record">';
                    rh += '<div><span class="r-from">' + t.from_nickname + '</span> → <span class="r-to">' + t.to_nickname + '</span>';
                    rh += '<div class="r-time">' + time + '</div></div>';
                    rh += '<div class="r-amount">' + t.amount + ' 分</div>';
                    rh += '</div>';
                }
                if (rec.innerHTML !== rh) {
                    rec.innerHTML = rh;
                }
            }
        }
        
        // ===== 启动轮询 =====
        function startPolling() {
            refreshData();
            safeInterval(refreshData, CONFIG.refreshInterval);
        }
        
        // ===== 停止轮询 =====
        function stopPolling() {
            state.isRunning = false;
            if (state.timerId) {
                clearInterval(state.timerId);
                state.timerId = null;
            }
        }
        
        // ===== 选择玩家 =====
        function selectPlayer(id, name) {
            selectedId = id;
            document.getElementById('targetName').textContent = name;
            document.getElementById('amount').value = '';
            document.getElementById('overlay').classList.add('show');
            document.getElementById('panel').classList.add('show');
            var cards = document.querySelectorAll('.player');
            for (var i = 0; i < cards.length; i++) {
                cards[i].classList.remove('selected');
                if (cards[i].id === 'player-' + id) cards[i].classList.add('selected');
            }
            setTimeout(function() { document.getElementById('amount').focus(); }, 300);
        }
        
        function cancelTransfer() {
            selectedId = null;
            document.getElementById('overlay').classList.remove('show');
            document.getElementById('panel').classList.remove('show');
            var cards = document.querySelectorAll('.player');
            for (var i = 0; i < cards.length; i++) cards[i].classList.remove('selected');
        }
        
        // ===== 转让积分（含防抖和CSRF） =====
        function doTransfer() {
            // 防止重复点击
            if (transferLock) {
                alert('正在处理中，请稍候...');
                return;
            }
            
            if (!selectedId) return;
            var amount = parseInt(document.getElementById('amount').value);
            if (!amount || amount <= 0) { alert('请输入有效的转让金额'); return; }
            
            transferLock = true;
            var btn = document.querySelector('.btn-ok');
            btn.disabled = true;
            btn.textContent = '提交中...';
            
            var csrfToken = getCsrfToken();
            
            fetch('/api/transfer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    room_id: roomId, 
                    to_user_id: selectedId, 
                    amount: amount,
                    csrf_token: csrfToken
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(d) { 
                if (d.success) { 
                    cancelTransfer();
                    refreshData();
                    setTimeout(refreshData, 500);
                } else { 
                    alert(d.message || '转让失败，请重试'); 
                }
            })
            .catch(function(err) {
                alert('网络错误，请重试');
            })
            .finally(function() {
                transferLock = false;
                btn.disabled = false;
                btn.textContent = '确认';
            });
        }
        
        // ===== 复制功能 =====
        function copyRoomName() {
            var roomName = '<?php echo addslashes($room['room_name']); ?>';
            var tempInput = document.createElement('input');
            tempInput.value = roomName;
            document.body.appendChild(tempInput);
            tempInput.select();
            try { document.execCommand('copy'); } catch (err) { prompt('房间名称：', roomName); }
            document.body.removeChild(tempInput);
        }
        
        function copyShareLink() {
            var shareUrl = location.origin + '/join-room/' + roomId;
            var tempInput = document.createElement('input');
            tempInput.value = shareUrl;
            document.body.appendChild(tempInput);
            tempInput.select();
            try { document.execCommand('copy'); } catch (err) { prompt('请手动复制：', shareUrl); }
            document.body.removeChild(tempInput);
        }
        
        function showQRCode() {
            var shareUrl = location.origin + '/join-room/' + roomId;
            var qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(shareUrl);
            var overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:200;display:flex;justify-content:center;align-items:center;';
            overlay.onclick = function(e) { if (e.target === overlay) overlay.remove(); };
            var qrBox = document.createElement('div');
            qrBox.style.cssText = 'background:white;border-radius:20px;padding:24px;text-align:center;max-width:300px;width:90%;';
            qrBox.innerHTML = '<h3 style="margin-bottom:16px;font-size:16px;">📱 扫码加入房间</h3><img src="' + qrApiUrl + '" alt="二维码" style="width:200px;height:200px;border-radius:12px;"><p style="margin-top:12px;font-size:12px;color:#999;word-break:break-all;">' + shareUrl + '</p><button onclick="this.parentElement.parentElement.remove()" style="margin-top:12px;padding:10px 30px;background:#f0f0f0;border:none;border-radius:10px;font-size:14px;cursor:pointer;">关闭</button>';
            overlay.appendChild(qrBox);
            document.body.appendChild(overlay);
        }
        
        function closeEditModal() { document.getElementById('editModal').classList.remove('show'); }
        
        function saveProfile() {
            var nickname = document.getElementById('newNickname').value.trim();
            var password = document.getElementById('newPassword').value;
            if (!nickname) { alert('昵称不能为空'); return; }
            if (nickname.length < 2) { alert('昵称至少2个字符'); return; }
            if (password && password.length < 6) { alert('至少6个字符'); return; }
            
            fetch('/profile/update-all', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname: nickname, password: password })
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) { alert('修改成功！'); closeEditModal(); location.reload(); }
                else alert(d.message);
            });
        }
        
        // ===== 页面关闭清理 =====
        window.addEventListener('beforeunload', function() {
            stopPolling();
        });
        
        // ===== 启动 =====
        startPolling();
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>历史记录 - 打牌记分系统 | 我的游戏记录</title>
    <meta name="description" content="查看您的打牌历史记录，包括所有参与过的牌局、胜负情况、净胜分。全面追踪您的记分数据。">
    <meta name="keywords" content="打牌记分系统,历史记录,游戏记录,牌局记录,记分统计">
    <meta name="author" content="打牌记分系统">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph -->
    <meta property="og:title" content="历史记录 - 打牌记分系统">
    <meta property="og:description" content="查看您的打牌历史记录，追踪记分数据。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:site_name" content="打牌记分系统">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/history">
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
        
        .header .back { text-decoration: none; color: #667eea; font-size: 15px; font-weight: 600; }
        .header .title { font-size: 18px; font-weight: 700; color: #333; }
        .header .placeholder { width: 40px; }
        
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
            font-size: 16px;
            font-weight: 700;
            margin: 16px 0 10px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        
        .record-list { display: flex; flex-direction: column; gap: 8px; }
        
        .record-card {
            background: white;
            border-radius: 16px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        
        .record-icon {
            width: 46px; height: 46px; min-width: 46px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .record-icon.win { background: linear-gradient(135deg, #d4edda, #c3e6cb); }
        .record-icon.lose { background: linear-gradient(135deg, #f8d7da, #f5c6cb); }
        
        .record-info { flex: 1; min-width: 0; }
        .record-room { font-size: 15px; font-weight: 600; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .record-meta { font-size: 12px; color: #999; margin-top: 2px; }
        
        .record-result { text-align: right; white-space: nowrap; }
        .record-result .result-text {
            font-size: 13px; font-weight: 700; padding: 4px 10px;
            border-radius: 10px; display: inline-block;
        }
        .result-text.win { background: #d4edda; color: #155724; }
        .result-text.lose { background: #f8d7da; color: #721c24; }
        .record-result .score { font-size: 15px; font-weight: 700; margin-top: 4px; }
        .score.positive { color: #28a745; }
        .score.negative { color: #dc3545; }
        
        .empty-state {
            text-align: center; padding: 50px 20px;
            color: rgba(255,255,255,0.8);
        }
        .empty-state .icon { font-size: 50px; margin-bottom: 10px; }
        .empty-state .text { font-size: 15px; }
        .empty-state .sub { font-size: 12px; opacity: 0.7; margin-top: 4px; }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 16px;
            padding-bottom: 20px;
        }
        
        .pagination a {
            background: white;
            padding: 10px 16px;
            border-radius: 12px;
            text-decoration: none;
            color: #667eea;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .pagination a.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .pagination span {
            padding: 10px 6px;
            color: rgba(255,255,255,0.6);
            font-weight: 600;
        }
        
        @media (max-width: 400px) {
            .record-card { padding: 14px; }
            .record-room { font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <a href="/lobby" class="back">← 返回</a>
            <span class="title">历史记录</span>
            <span class="placeholder"></span>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total_games']; ?></div>
                <div class="stat-label">总场次</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['wins']; ?></div>
                <div class="stat-label">胜场</div>
            </div>
        </div>
        
        <div class="section-title">📋 全部记录</div>
        <div class="record-list">
            <?php if (isset($records) && count($records) > 0): ?>
                <?php foreach ($records as $r): ?>
                    <div class="record-card">
                        <div class="record-icon <?php echo $r['myNetScore'] >= 0 ? 'win' : 'lose'; ?>">
                            <?php echo $r['myNetScore'] >= 0 ? '🏆' : '💪'; ?>
                        </div>
                        <div class="record-info">
                            <div class="record-room"><?php echo htmlspecialchars($r['room_name']); ?></div>
                            <div class="record-meta">
                                <?php echo date('m-d H:i', strtotime($r['game_date'])); ?> · <?php echo $r['player_count']; ?>人局
                            </div>
                            <div style="font-size:12px;color:#999;margin-top:2px;">
                                🏆 <strong><?php echo htmlspecialchars($r['winnerName']); ?></strong> 赢 <span style="color:#28a745;">+<?php echo $r['winnerScore']; ?>分</span>
                            </div>
                        </div>
                        <div class="record-result">
                            <div class="result-text <?php echo $r['myNetScore'] >= 0 ? 'win' : 'lose'; ?>">
                                <?php echo $r['myNetScore'] >= 0 ? '我赢了' : '我输了'; ?>
                            </div>
                            <div class="score <?php echo $r['myNetScore'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $r['myNetScore'] >= 0 ? '+' : ''; ?><?php echo $r['myNetScore']; ?> 分
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="icon">📭</div>
                    <div class="text">暂无游戏记录</div>
                    <div class="sub">在房间中转让积分后自动生成</div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="/history?page=<?php echo $currentPage - 1; ?>">← 上一页</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <a class="active"><?php echo $i; ?></a>
                    <?php else: ?>
                        <a href="/history?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="/history?page=<?php echo $currentPage + 1; ?>">下一页 →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
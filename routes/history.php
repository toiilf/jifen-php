<?php
class Router_History {
    
    public function index() {
        requireAuth();
        
        $userId = $_SESSION['user']['id'];
        $page = intval($_GET['page'] ?? 1);
        $limit = 20;
        
        $db = db();
        
        $roomList = $db->fetchAll(
            "SELECT room_id, MAX(created_at) as last_time 
             FROM score_transfers 
             WHERE from_user_id = ? OR to_user_id = ?
             GROUP BY room_id
             ORDER BY last_time DESC",
            [$userId, $userId]
        );
        
        $totalRecords = count($roomList);
        $totalPages = ceil($totalRecords / $limit);
        $offset = ($page - 1) * $limit;
        $pageRooms = array_slice($roomList, $offset, $limit);
        
        $totalWins = 0;
        foreach ($roomList as $r) {
            $score = $db->fetchOne(
                "SELECT COALESCE(SUM(CASE WHEN to_user_id = ? THEN amount ELSE -amount END), 0) as net
                 FROM score_transfers WHERE room_id = ? AND (from_user_id = ? OR to_user_id = ?)",
                [$userId, $r['room_id'], $userId, $userId]
            );
            if (($score['net'] ?? 0) > 0) $totalWins++;
        }
        
        $records = [];
        foreach ($pageRooms as $r) {
            $roomId = $r['room_id'];
            
            $playerScores = $db->fetchAll(
                "SELECT u.id as user_id, u.nickname,
                        COALESCE(SUM(CASE WHEN st.to_user_id = u.id THEN st.amount ELSE -st.amount END), 0) as net_score
                 FROM score_transfers st
                 JOIN users u ON u.id = st.from_user_id OR u.id = st.to_user_id
                 WHERE st.room_id = ? AND (st.from_user_id = u.id OR st.to_user_id = u.id)
                 GROUP BY u.id, u.nickname
                 ORDER BY net_score DESC",
                [$roomId]
            );
            
            $seen = [];
            $uniqueScores = [];
            foreach ($playerScores as $s) {
                if (!isset($seen[$s['user_id']])) {
                    $seen[$s['user_id']] = true;
                    $uniqueScores[] = $s;
                }
            }
            
            $winner = $uniqueScores[0] ?? ['nickname' => '?', 'net_score' => 0];
            $myScore = 0;
            foreach ($uniqueScores as $s) {
                if ($s['user_id'] == $userId) {
                    $myScore = $s['net_score'];
                    break;
                }
            }
            
            $roomName = '房间' . $roomId;
            $playerCount = count($uniqueScores);
            
            $roomInfo = $db->fetchOne('SELECT room_name FROM rooms WHERE id = ?', [$roomId]);
            if ($roomInfo) $roomName = $roomInfo['room_name'];
            
            $records[] = [
                'room_name' => $roomName,
                'player_count' => $playerCount,
                'game_date' => $r['last_time'],
                'winnerName' => $winner['nickname'],
                'winnerScore' => $winner['net_score'],
                'myNetScore' => $myScore,
                'isMeWinner' => ($winner['user_id'] ?? 0) == $userId
            ];
        }
        
        render('history', [
            'title' => '历史记录',
            'stats' => [
                'total_games' => $totalRecords,
                'wins' => $totalWins
            ],
            'records' => $records,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords
        ]);
    }
}
<?php
class Router_Room {
    
    public function show($params) {
        requireAuth();
        
        $roomId = $params[0] ?? 0;
        $userId = $_SESSION['user']['id'];
        
        if (!$roomId) {
            render('error', ['title' => '错误', 'message' => '房间ID无效']);
            return;
        }
        
        $db = db();
        $room = $db->fetchOne(
            "SELECT r.*, u.nickname as creator_name FROM rooms r 
             JOIN users u ON r.creator_id = u.id WHERE r.id = ?",
            [$roomId]
        );
        
        if (!$room) {
            render('error', ['title' => '错误', 'message' => '房间不存在']);
            return;
        }
        
        $playerCheck = $db->fetchOne(
            'SELECT id FROM room_players WHERE room_id = ? AND user_id = ?',
            [$roomId, $userId]
        );
        
        if (!$playerCheck) {
            render('error', ['title' => '错误', 'message' => '您不在该房间中']);
            return;
        }
        
        $allPlayers = $db->fetchAll(
            "SELECT rp.*, u.username, u.nickname, u.avatar 
             FROM room_players rp 
             JOIN users u ON rp.user_id = u.id 
             WHERE rp.room_id = ? 
             ORDER BY rp.seat_number",
            [$roomId]
        );
        
        $myPlayer = null;
        $otherPlayers = [];
        foreach ($allPlayers as $p) {
            if ($p['user_id'] == $userId) {
                $myPlayer = $p;
            } else {
                $otherPlayers[] = $p;
            }
        }
        
        if (!$myPlayer) {
            $myPlayer = ['current_score' => 0, 'nickname' => $_SESSION['user']['nickname']];
        }
        
        // 获取茶水费总额
        $teaFeeTotal = 0;
        if ($room['tea_fee_enabled']) {
            $teaFeeResult = $db->fetchOne(
                'SELECT COALESCE(SUM(amount), 0) as total FROM tea_fee_records WHERE room_id = ?',
                [$roomId]
            );
            $teaFeeTotal = $teaFeeResult['total'] ?? 0;
        }
        
        $transfers = $db->fetchAll(
            "SELECT st.*, fu.nickname as from_nickname, tu.nickname as to_nickname 
             FROM score_transfers st 
             JOIN users fu ON st.from_user_id = fu.id 
             JOIN users tu ON st.to_user_id = tu.id 
             WHERE st.room_id = ? 
             ORDER BY st.created_at DESC LIMIT 50",
            [$roomId]
        );
        
        $showGuide = isset($_SESSION['showGuide']) ? $_SESSION['showGuide'] : false;
        $_SESSION['showGuide'] = false;
        
        render('room', [
            'title' => $room['room_name'],
            'room' => $room,
            'myPlayer' => $myPlayer,
            'otherPlayers' => $otherPlayers,
            'transfers' => $transfers,
            'teaFeeTotal' => $teaFeeTotal,
            'userId' => $userId,
            'user' => $_SESSION['user'],
            'showGuide' => $showGuide
        ]);
    }
    
    public function leave($params) {
        requireAuth();
        
        $roomId = $params[0] ?? 0;
        $userId = $_SESSION['user']['id'];
        
        $db = db();
        $db->update(
            'DELETE FROM room_players WHERE room_id = ? AND user_id = ?',
            [$roomId, $userId]
        );
        
        $players = $db->fetchOne(
            'SELECT COUNT(*) as count FROM room_players WHERE room_id = ?',
            [$roomId]
        );
        
        if ($players['count'] == 0) {
            $db->update(
                "UPDATE rooms SET status = 'finished' WHERE id = ?",
                [$roomId]
            );
        }
        
        jsonResponse(['success' => true]);
    }
}
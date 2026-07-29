<?php
class Router_Lobby {
    
    public function index() {
        requireAuth();
        
        $showGuide = isset($_SESSION['showGuide']) ? $_SESSION['showGuide'] : false;
        $_SESSION['showGuide'] = false;
        
        render('lobby', [
            'title' => '积分系统',
            'user' => $_SESSION['user'],
            'showGuide' => $showGuide
        ]);
    }
    
    public function myRooms() {
        requireAuth();
        
        $userId = $_SESSION['user']['id'];
        $db = db();
        
        $rooms = $db->fetchAll(
            "SELECT r.*, u.nickname as creator_name, 
                    (SELECT COUNT(*) FROM room_players WHERE room_id = r.id) as player_count 
             FROM rooms r 
             JOIN users u ON r.creator_id = u.id 
             WHERE r.creator_id = ? AND r.status != 'finished' 
             ORDER BY r.created_at DESC",
            [$userId]
        );
        
        jsonResponse(['success' => true, 'rooms' => $rooms]);
    }
    
    public function joinedRooms() {
        requireAuth();
        
        $userId = $_SESSION['user']['id'];
        $db = db();
        
        $rooms = $db->fetchAll(
            "SELECT r.id, r.room_name, r.password, r.max_players, r.status, r.tea_fee_enabled,
                    u.nickname as creator_name,
                    (SELECT COUNT(*) FROM room_players WHERE room_id = r.id) as player_count 
             FROM rooms r 
             JOIN users u ON r.creator_id = u.id 
             WHERE r.id IN (SELECT room_id FROM room_players WHERE user_id = ?) 
               AND r.creator_id != ? AND r.status != 'finished' 
             ORDER BY r.updated_at DESC",
            [$userId, $userId]
        );
        
        jsonResponse(['success' => true, 'rooms' => $rooms]);
    }
    
    public function createRoom() {
        requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $room_name = trim($data['room_name'] ?? '');
        $password = $data['password'] ?? null;
        $max_players = intval($data['max_players'] ?? 4);
        $tea_fee_enabled = isset($data['tea_fee_enabled']) && $data['tea_fee_enabled'] === true ? 1 : 0;
        $userId = $_SESSION['user']['id'];
        
        if (!$room_name) {
            jsonResponse(['success' => false, 'message' => '请输入房间名称']);
            return;
        }
        
        $db = db();
        $roomId = $db->insert(
            'INSERT INTO rooms (room_name, creator_id, password, max_players, tea_fee_enabled) VALUES (?, ?, ?, ?, ?)',
            [$room_name, $userId, $password ?: null, $max_players, $tea_fee_enabled]
        );
        
        $db->insert(
            'INSERT INTO room_players (room_id, user_id, seat_number) VALUES (?, ?, ?)',
            [$roomId, $userId, 1]
        );
        
        jsonResponse(['success' => true, 'room_id' => $roomId]);
    }
    
    public function joinRoomPost() {
        requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $room_name = trim($data['room_name'] ?? '');
        $password = $data['password'] ?? '';
        $userId = $_SESSION['user']['id'];
        
        if (!$room_name) {
            jsonResponse(['success' => false, 'message' => '请输入房间名称']);
            return;
        }
        
        $db = db();
        $room = $db->fetchOne(
            "SELECT * FROM rooms WHERE room_name = ? AND status != 'finished'",
            [$room_name]
        );
        
        if (!$room) {
            jsonResponse(['success' => false, 'message' => '房间不存在或已关闭']);
            return;
        }
        
        if ($room['password'] && $room['password'] !== $password) {
            jsonResponse(['success' => false, 'message' => '房间密码错误']);
            return;
        }
        
        $existing = $db->fetchOne(
            'SELECT id FROM room_players WHERE room_id = ? AND user_id = ?',
            [$room['id'], $userId]
        );
        
        if ($existing) {
            jsonResponse(['success' => true, 'room_id' => $room['id']]);
            return;
        }
        
        $players = $db->fetchOne(
            'SELECT COUNT(*) as count FROM room_players WHERE room_id = ?',
            [$room['id']]
        );
        
        if ($players['count'] >= $room['max_players']) {
            jsonResponse(['success' => false, 'message' => '房间已满']);
            return;
        }
        
        $maxSeat = $db->fetchOne(
            'SELECT COALESCE(MAX(seat_number), 0) + 1 as next_seat 
             FROM room_players WHERE room_id = ?',
            [$room['id']]
        );
        
        $db->insert(
            'INSERT INTO room_players (room_id, user_id, seat_number) VALUES (?, ?, ?)',
            [$room['id'], $userId, $maxSeat['next_seat'] ?? 1]
        );
        
        jsonResponse(['success' => true, 'room_id' => $room['id']]);
    }
    
    public function leaveRoom() {
        requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $room_id = $data['room_id'] ?? 0;
        $userId = $_SESSION['user']['id'];
        
        $db = db();
        $db->update(
            'DELETE FROM room_players WHERE room_id = ? AND user_id = ?',
            [$room_id, $userId]
        );
        
        jsonResponse(['success' => true, 'message' => '已退出']);
    }
    
    public function dismissRoom() {
        requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $room_id = $data['room_id'] ?? 0;
        $userId = $_SESSION['user']['id'];
        
        $db = db();
        $room = $db->fetchOne(
            'SELECT * FROM rooms WHERE id = ? AND creator_id = ?',
            [$room_id, $userId]
        );
        
        if (!$room) {
            jsonResponse(['success' => false, 'message' => '只有房主可以解散']);
            return;
        }
        
        $db->update(
            "UPDATE rooms SET status = 'finished' WHERE id = ?",
            [$room_id]
        );
        
        jsonResponse(['success' => true, 'message' => '已解散']);
    }
}
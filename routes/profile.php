<?php
class Router_Profile {
    
    public function index() {
        requireAuth();
        
        $userId = $_SESSION['user']['id'];
        $db = db();
        
        $user = $db->fetchOne('SELECT * FROM users WHERE id = ?', [$userId]);
        if (!$user) {
            render('error', ['title' => '错误', 'message' => '用户不存在']);
            return;
        }
        
        $myRooms = $db->fetchAll(
            "SELECT DISTINCT room_id FROM score_transfers 
             WHERE from_user_id = ? OR to_user_id = ?",
            [$userId, $userId]
        );
        
        $totalGames = count($myRooms);
        $wins = 0;
        $recentGames = [];
        
        foreach ($myRooms as $r) {
            $roomId = $r['room_id'];
            $score = $db->fetchOne(
                "SELECT COALESCE(SUM(CASE WHEN to_user_id = ? THEN amount ELSE -amount END), 0) as net 
                 FROM score_transfers 
                 WHERE room_id = ? AND (from_user_id = ? OR to_user_id = ?)",
                [$userId, $roomId, $userId, $userId]
            );
            $netScore = $score['net'] ?? 0;
            if ($netScore > 0) $wins++;
            
            $roomInfo = $db->fetchOne(
                "SELECT gr.*, r.room_name, 
                        (SELECT COUNT(*) FROM room_players WHERE room_id = gr.room_id) as player_count 
                 FROM game_records gr 
                 JOIN rooms r ON gr.room_id = r.id 
                 WHERE gr.room_id = ? LIMIT 1",
                [$roomId]
            );
            
            if ($roomInfo) {
                $roomInfo['myNetScore'] = $netScore;
                $recentGames[] = $roomInfo;
            }
        }
        
        usort($recentGames, function($a, $b) {
            return strtotime($b['game_date']) - strtotime($a['game_date']);
        });
        $recentGames = array_slice($recentGames, 0, 10);
        
        render('profile', [
            'title' => '个人中心',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'nickname' => $user['nickname'],
                'total_games' => $totalGames,
                'wins' => $wins
            ],
            'recentGames' => $recentGames
        ]);
    }
    
    // ===== 修改昵称（同时更新 username） =====
    public function update() {
        requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $nickname = trim($data['nickname'] ?? '');
        $userId = $_SESSION['user']['id'];
        
        // 验证昵称
        $validated = validateUsername($nickname);
        if (!$validated) {
            jsonResponse(['success' => false, 'message' => '昵称格式无效（2-50个字符，仅支持中文、英文、数字、下划线）']);
            return;
        }
        
        $db = db();
        
        // 检查新昵称是否已被其他用户使用
        $existing = $db->fetchOne(
            'SELECT id FROM users WHERE username = ? AND id != ?',
            [$validated, $userId]
        );
        if ($existing) {
            jsonResponse(['success' => false, 'message' => '该昵称已被使用']);
            return;
        }
        
        // 同时更新 username 和 nickname
        $db->update(
            'UPDATE users SET username = ?, nickname = ? WHERE id = ?',
            [$validated, $validated, $userId]
        );
        
        // 更新 Session
        $_SESSION['user']['username'] = $validated;
        $_SESSION['user']['nickname'] = $validated;
        
        jsonResponse(['success' => true]);
    }
    
    // ===== 修改密码 =====
    public function changePassword() {
        requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $old_password = $data['old_password'] ?? '';
        $new_password = $data['new_password'] ?? '';
        $userId = $_SESSION['user']['id'];
        
        $db = db();
        $user = $db->fetchOne('SELECT password FROM users WHERE id = ?', [$userId]);
        if (!$user) {
            jsonResponse(['success' => false, 'message' => '用户不存在']);
            return;
        }
        
        if (!password_verify($old_password, $user['password'])) {
            jsonResponse(['success' => false, 'message' => '当前密码错误']);
            return;
        }
        
        if (strlen($new_password) < 6) {
            jsonResponse(['success' => false, 'message' => '新密码至少6个字符']);
            return;
        }
        
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $db->update('UPDATE users SET password = ? WHERE id = ?', [$hashedPassword, $userId]);
        
        jsonResponse(['success' => true, 'message' => '密码修改成功']);
    }
    
    // ===== 修改全部资料（昵称 + 密码） =====
    public function updateAll() {
        requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $nickname = trim($data['nickname'] ?? '');
        $password = $data['password'] ?? '';
        $userId = $_SESSION['user']['id'];
        
        // 验证昵称
        $validated = validateUsername($nickname);
        if (!$validated) {
            jsonResponse(['success' => false, 'message' => '昵称格式无效（2-50个字符）']);
            return;
        }
        
        $db = db();
        
        // 检查新昵称是否已被其他用户使用
        $existing = $db->fetchOne(
            'SELECT id FROM users WHERE username = ? AND id != ?',
            [$validated, $userId]
        );
        if ($existing) {
            jsonResponse(['success' => false, 'message' => '该昵称已被使用']);
            return;
        }
        
        // 更新昵称（同时更新 username 和 nickname）
        if ($password && strlen($password) >= 6) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $db->update(
                'UPDATE users SET username = ?, nickname = ?, password = ? WHERE id = ?',
                [$validated, $validated, $hashedPassword, $userId]
            );
        } else {
            $db->update(
                'UPDATE users SET username = ?, nickname = ? WHERE id = ?',
                [$validated, $validated, $userId]
            );
        }
        
        // 更新 Session
        $_SESSION['user']['username'] = $validated;
        $_SESSION['user']['nickname'] = $validated;
        
        jsonResponse(['success' => true, 'message' => '修改成功']);
    }
}
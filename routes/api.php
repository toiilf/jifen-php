<?php
class Router_Api {
    
    public function room($params) {
        requireAuth();
        
        $roomId = validateId($params[0] ?? 0);
        if (!$roomId) {
            jsonResponse(['success' => false, 'message' => '房间ID无效']);
            return;
        }
        
        $db = db();
        
        $room = $db->fetchOne('SELECT * FROM rooms WHERE id = ?', [$roomId]);
        if (!$room) {
            jsonResponse(['success' => false, 'message' => '房间不存在']);
            return;
        }
        
        $players = $db->fetchAll(
            "SELECT rp.*, u.username, u.nickname 
             FROM room_players rp 
             JOIN users u ON rp.user_id = u.id 
             WHERE rp.room_id = ? 
             ORDER BY rp.seat_number",
            [$roomId]
        );
        
        $transfers = $db->fetchAll(
            "SELECT st.*, fu.nickname as from_nickname, tu.nickname as to_nickname 
             FROM score_transfers st 
             JOIN users fu ON st.from_user_id = fu.id 
             JOIN users tu ON st.to_user_id = tu.id 
             WHERE st.room_id = ? 
             ORDER BY st.created_at DESC LIMIT 50",
            [$roomId]
        );
        
        jsonResponse([
            'success' => true,
            'room' => $room,
            'players' => $players,
            'transfers' => $transfers
        ]);
    }
    
    public function transfer() {
        requireAuth();
        requirePost();
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            jsonResponse(['success' => false, 'message' => '无效的请求数据']);
            return;
        }
        
        // ===== CSRF 验证 =====
        $csrfToken = $data['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            jsonResponse(['success' => false, 'message' => '安全验证失败，请刷新页面重试']);
            return;
        }
        
        // ===== 参数验证 =====
        $room_id = validateId($data['room_id'] ?? 0);
        $to_user_id = validateId($data['to_user_id'] ?? 0);
        $amount = validateAmount($data['amount'] ?? 0);
        $fromUserId = $_SESSION['user']['id'];
        
        if (!$room_id) {
            jsonResponse(['success' => false, 'message' => '房间ID无效']);
            return;
        }
        if (!$to_user_id) {
            jsonResponse(['success' => false, 'message' => '目标玩家无效']);
            return;
        }
        if (!$amount) {
            jsonResponse(['success' => false, 'message' => '转让金额必须大于0']);
            return;
        }
        if ($fromUserId == $to_user_id) {
            jsonResponse(['success' => false, 'message' => '不能给自己转让积分']);
            return;
        }
        
        $db = db();
        
        // 最大重试次数（防止死锁）
        $maxRetries = 3;
        $retryCount = 0;
        
        while ($retryCount < $maxRetries) {
            try {
                $db->beginTransaction();
                
                // ============================================================
                // 1. 锁定房间
                // ============================================================
                $room = $db->fetchOne(
                    "SELECT * FROM rooms WHERE id = ? AND status != 'finished' FOR UPDATE",
                    [$room_id]
                );
                
                if (!$room) {
                    $db->rollback();
                    jsonResponse(['success' => false, 'message' => '房间不存在或已关闭']);
                    return;
                }
                
                // ============================================================
                // 2. 统一锁定顺序，防止死锁（关键！）
                // ============================================================
                // 将两个用户ID排序，保证锁定顺序一致
                $userIds = [$fromUserId, $to_user_id];
                sort($userIds, SORT_NUMERIC);
                
                $players = $db->fetchAll(
                    "SELECT * FROM room_players WHERE room_id = ? AND user_id IN (?, ?) FOR UPDATE",
                    [$room_id, $userIds[0], $userIds[1]]
                );
                
                if (count($players) !== 2) {
                    $db->rollback();
                    jsonResponse(['success' => false, 'message' => '玩家不在同一房间']);
                    return;
                }
                
                // 获取玩家数据
                $fromPlayer = null;
                $toPlayer = null;
                foreach ($players as $p) {
                    if ($p['user_id'] == $fromUserId) $fromPlayer = $p;
                    if ($p['user_id'] == $to_user_id) $toPlayer = $p;
                }
                
                // ============================================================
                // 3. 更新房间内积分
                // ============================================================
                $db->update(
                    'UPDATE room_players SET current_score = current_score - ? WHERE room_id = ? AND user_id = ?',
                    [$amount, $room_id, $fromUserId]
                );
                
                $db->update(
                    'UPDATE room_players SET current_score = current_score + ? WHERE room_id = ? AND user_id = ?',
                    [$amount, $room_id, $to_user_id]
                );
                
                // ============================================================
                // 4. 锁定并更新用户总积分（统一顺序）
                // ============================================================
                $userIds2 = [$fromUserId, $to_user_id];
                sort($userIds2, SORT_NUMERIC);
                
                $users = $db->fetchAll(
                    "SELECT * FROM users WHERE id IN (?, ?) FOR UPDATE",
                    [$userIds2[0], $userIds2[1]]
                );
                
                if (count($users) !== 2) {
                    $db->rollback();
                    jsonResponse(['success' => false, 'message' => '用户不存在']);
                    return;
                }
                
                $db->update(
                    'UPDATE users SET total_score = total_score - ? WHERE id = ?',
                    [$amount, $fromUserId]
                );
                
                $db->update(
                    'UPDATE users SET total_score = total_score + ? WHERE id = ?',
                    [$amount, $to_user_id]
                );
                
                // ============================================================
                // 5. 记录转让
                // ============================================================
                $db->insert(
                    'INSERT INTO score_transfers (room_id, from_user_id, to_user_id, amount, transfer_type) VALUES (?, ?, ?, ?, ?)',
                    [$room_id, $fromUserId, $to_user_id, $amount, 'transfer']
                );
                
                // ============================================================
                // 6. 检查并创建游戏记录
                // ============================================================
                $today = date('Y-m-d 00:00:00');
                $tomorrow = date('Y-m-d 00:00:00', strtotime('+1 day'));
                
                $existingRecord = $db->fetchOne(
                    "SELECT id FROM game_records 
                     WHERE room_id = ? AND game_date >= ? AND game_date < ? 
                     FOR UPDATE",
                    [$room_id, $today, $tomorrow]
                );
                
                if (!$existingRecord) {
                    $topPlayer = $db->fetchOne(
                        "SELECT user_id, current_score FROM room_players 
                         WHERE room_id = ? 
                         ORDER BY current_score DESC LIMIT 1",
                        [$room_id]
                    );
                    
                    $totalScores = $db->fetchOne(
                        'SELECT SUM(current_score) as total_pot FROM room_players WHERE room_id = ?',
                        [$room_id]
                    );
                    
                    if ($topPlayer) {
                        $winnerId = $topPlayer['user_id'];
                        $totalPot = abs($totalScores['total_pot'] ?? $amount);
                        
                        $db->insert(
                            'INSERT INTO game_records (room_id, winner_id, total_pot) VALUES (?, ?, ?)',
                            [$room_id, $winnerId, $totalPot]
                        );
                        
                        $allPlayers = $db->fetchAll(
                            'SELECT user_id FROM room_players WHERE room_id = ?',
                            [$room_id]
                        );
                        
                        foreach ($allPlayers as $p) {
                            $db->update(
                                'UPDATE users SET total_games = total_games + 1 WHERE id = ?',
                                [$p['user_id']]
                            );
                        }
                        
                        $db->update(
                            'UPDATE users SET wins = wins + 1 WHERE id = ?',
                            [$winnerId]
                        );
                    }
                }
                
                // ============================================================
                // 7. 提交事务
                // ============================================================
                $db->commit();
                
                generateCsrfToken();
                
                jsonResponse(['success' => true, 'message' => '积分转让成功']);
                return;  // 成功退出
                
            } catch (Exception $e) {
                $db->rollback();
                $retryCount++;
                
                // 检查是否是死锁错误 (MySQL error 1213)
                if (strpos($e->getMessage(), 'Deadlock') !== false || 
                    strpos($e->getMessage(), '1213') !== false) {
                    if ($retryCount < $maxRetries) {
                        // 死锁，等待后重试
                        usleep(100000 * $retryCount);  // 100ms * 重试次数
                        continue;
                    }
                }
                
                // 非死锁错误或重试次数用完
                error_log('转让积分失败: ' . $e->getMessage() . ' 文件: ' . $e->getFile() . ' 行: ' . $e->getLine());
                jsonResponse(['success' => false, 'message' => '积分转让失败，请重试']);
                return;
            }
        }
        
        // 重试次数用完
        jsonResponse(['success' => false, 'message' => '系统繁忙，请稍后重试']);
    }
    
    public function dismiss() {
        requireAuth();
        requirePost();
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            jsonResponse(['success' => false, 'message' => '无效的请求数据']);
            return;
        }
        
        $csrfToken = $data['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            jsonResponse(['success' => false, 'message' => '安全验证失败，请刷新页面重试']);
            return;
        }
        
        $room_id = validateId($data['room_id'] ?? 0);
        $userId = $_SESSION['user']['id'];
        
        if (!$room_id) {
            jsonResponse(['success' => false, 'message' => '房间ID无效']);
            return;
        }
        
        $db = db();
        
        try {
            $db->beginTransaction();
            
            $room = $db->fetchOne(
                'SELECT * FROM rooms WHERE id = ? AND creator_id = ? FOR UPDATE',
                [$room_id, $userId]
            );
            
            if (!$room) {
                $db->rollback();
                jsonResponse(['success' => false, 'message' => '只有房主可以解散房间']);
                return;
            }
            
            $db->update("UPDATE rooms SET status = 'finished' WHERE id = ?", [$room_id]);
            
            $db->commit();
            
            generateCsrfToken();
            
            jsonResponse(['success' => true, 'message' => '房间已解散']);
            
        } catch (Exception $e) {
            $db->rollback();
            error_log('解散房间失败: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => '解散房间失败，请重试']);
        }
    }
}
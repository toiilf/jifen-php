<?php
class Router_Admin {
    
    public function login() {
        if (isAdmin()) {
            redirect('/admin');
            return;
        }
        $error = null;
        render('admin_login', ['title' => '管理员登录', 'error' => $error]);
    }
    
    public function loginPost() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $db = db();
        $admin = $db->fetchOne('SELECT * FROM admins WHERE username = ?', [$username]);
        
        if (!$admin || !password_verify($password, $admin['password'])) {
            render('admin_login', ['title' => '管理员登录', 'error' => '用户名或密码错误']);
            return;
        }
        
        $_SESSION['isAdmin'] = true;
        $_SESSION['adminUser'] = [
            'id' => $admin['id'],
            'username' => $admin['username']
        ];
        
        redirect('/admin');
    }
    
    public function index() {
        requireAdmin();
        
        $db = db();
        
        $userStats = $db->fetchOne('SELECT COUNT(*) as total_users FROM users');
        $roomStats = $db->fetchOne("
            SELECT 
                COUNT(*) as total_rooms,
                SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting_rooms,
                SUM(CASE WHEN status = 'playing' THEN 1 ELSE 0 END) as playing_rooms,
                SUM(CASE WHEN status = 'finished' THEN 1 ELSE 0 END) as finished_rooms
            FROM rooms
        ");
        $gameStats = $db->fetchOne('SELECT COUNT(*) as total_games FROM game_records');
        $adminStats = $db->fetchOne('SELECT COUNT(*) as admin_count FROM admins');
        
        $users = $db->fetchAll('SELECT * FROM users ORDER BY created_at DESC');
        $rooms = $db->fetchAll("
            SELECT r.*, u.nickname as creator_name,
                   (SELECT COUNT(*) FROM room_players WHERE room_id = r.id) as player_count
            FROM rooms r
            JOIN users u ON r.creator_id = u.id
            ORDER BY r.created_at DESC
        ");
        $admins = $db->fetchAll('SELECT id, username, created_at FROM admins ORDER BY created_at DESC');
        
        render('admin', [
            'title' => '管理员后台',
            'stats' => [
                'total_users' => $userStats['total_users'] ?? 0,
                'total_rooms' => $roomStats['total_rooms'] ?? 0,
                'waiting_rooms' => $roomStats['waiting_rooms'] ?? 0,
                'playing_rooms' => $roomStats['playing_rooms'] ?? 0,
                'finished_rooms' => $roomStats['finished_rooms'] ?? 0,
                'total_games' => $gameStats['total_games'] ?? 0,
                'admin_count' => $adminStats['admin_count'] ?? 0
            ],
            'users' => $users,
            'rooms' => $rooms,
            'admins' => $admins
        ]);
    }
    
    public function logout() {
        $_SESSION['isAdmin'] = false;
        $_SESSION['adminUser'] = null;
        redirect('/admin/login');
    }
    
    public function apiStats() {
        requireAdmin();
        
        $db = db();
        $userStats = $db->fetchOne('SELECT COUNT(*) as total_users FROM users');
        $roomStats = $db->fetchOne("
            SELECT COUNT(*) as total_rooms,
                   SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting_rooms,
                   SUM(CASE WHEN status = 'playing' THEN 1 ELSE 0 END) as playing_rooms
            FROM rooms
        ");
        $gameStats = $db->fetchOne('SELECT COUNT(*) as total_games FROM game_records');
        $adminStats = $db->fetchOne('SELECT COUNT(*) as admin_count FROM admins');
        
        jsonResponse([
            'success' => true,
            'stats' => [
                'total_users' => $userStats['total_users'] ?? 0,
                'total_rooms' => $roomStats['total_rooms'] ?? 0,
                'waiting_rooms' => $roomStats['waiting_rooms'] ?? 0,
                'playing_rooms' => $roomStats['playing_rooms'] ?? 0,
                'total_games' => $gameStats['total_games'] ?? 0,
                'admin_count' => $adminStats['admin_count'] ?? 0
            ]
        ]);
    }
    
    public function apiUsersList() {
        requireAdmin();
        $db = db();
        $users = $db->fetchAll('SELECT * FROM users ORDER BY created_at DESC');
        jsonResponse(['success' => true, 'users' => $users]);
    }
    
    public function apiUsersCreate() {
        requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $nickname = trim($data['nickname'] ?? $username);
        $password = $data['password'] ?? '';
        
        if (!$password || strlen($password) < 6) {
            jsonResponse(['success' => false, 'message' => '密码至少6个字符']);
            return;
        }
        
        $db = db();
        $existing = $db->fetchOne('SELECT id FROM users WHERE username = ?', [$username]);
        if ($existing) {
            jsonResponse(['success' => false, 'message' => '用户名已存在']);
            return;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $db->insert(
            'INSERT INTO users (username, password, nickname) VALUES (?, ?, ?)',
            [$username, $hashedPassword, $nickname]
        );
        
        jsonResponse(['success' => true, 'message' => '用户创建成功']);
    }
    
    public function apiUsersUpdate() {
        requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        $username = trim($data['username'] ?? '');
        $nickname = trim($data['nickname'] ?? $username);
        $password = $data['password'] ?? '';
        
        $db = db();
        $existing = $db->fetchOne('SELECT id FROM users WHERE username = ? AND id != ?', [$username, $id]);
        if ($existing) {
            jsonResponse(['success' => false, 'message' => '用户名已存在']);
            return;
        }
        
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $db->update(
                'UPDATE users SET username = ?, nickname = ?, password = ? WHERE id = ?',
                [$username, $nickname, $hashedPassword, $id]
            );
        } else {
            $db->update(
                'UPDATE users SET username = ?, nickname = ? WHERE id = ?',
                [$username, $nickname, $id]
            );
        }
        
        jsonResponse(['success' => true, 'message' => '用户更新成功']);
    }
    
    public function apiUsersDelete() {
        requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        
        $db = db();
        $db->update('DELETE FROM users WHERE id = ?', [$id]);
        
        jsonResponse(['success' => true, 'message' => '用户已删除']);
    }
    
    public function apiRoomsList() {
        requireAdmin();
        
        $db = db();
        $rooms = $db->fetchAll("
            SELECT r.*, u.nickname as creator_name,
                   (SELECT COUNT(*) FROM room_players WHERE room_id = r.id) as player_count
            FROM rooms r
            JOIN users u ON r.creator_id = u.id
            ORDER BY r.created_at DESC
        ");
        
        jsonResponse(['success' => true, 'rooms' => $rooms]);
    }
    
    public function apiRoomsClose() {
        requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        
        $db = db();
        $db->update("UPDATE rooms SET status = 'finished' WHERE id = ?", [$id]);
        
        jsonResponse(['success' => true, 'message' => '房间已关闭']);
    }
    
    public function apiRoomsDelete() {
        requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        
        $db = db();
        $db->update('DELETE FROM rooms WHERE id = ?', [$id]);
        
        jsonResponse(['success' => true, 'message' => '房间已删除']);
    }
    
    public function apiAdminsList() {
        requireAdmin();
        
        $db = db();
        $admins = $db->fetchAll('SELECT id, username, created_at FROM admins ORDER BY created_at DESC');
        
        jsonResponse(['success' => true, 'admins' => $admins]);
    }
    
    public function apiAdminsCreate() {
        requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        
        if (!$password || strlen($password) < 6) {
            jsonResponse(['success' => false, 'message' => '密码至少6个字符']);
            return;
        }
        
        $db = db();
        $existing = $db->fetchOne('SELECT id FROM admins WHERE username = ?', [$username]);
        if ($existing) {
            jsonResponse(['success' => false, 'message' => '管理员用户名已存在']);
            return;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $db->insert('INSERT INTO admins (username, password) VALUES (?, ?)', [$username, $hashedPassword]);
        
        jsonResponse(['success' => true, 'message' => '管理员创建成功']);
    }
    
    public function apiAdminsUpdate() {
        requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        $password = $data['password'] ?? '';
        
        if (!$password || strlen($password) < 6) {
            jsonResponse(['success' => false, 'message' => '密码至少6个字符']);
            return;
        }
        
        $db = db();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $db->update('UPDATE admins SET password = ? WHERE id = ?', [$hashedPassword, $id]);
        
        jsonResponse(['success' => true, 'message' => '管理员密码更新成功']);
    }
    
    public function apiAdminsDelete() {
        requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        $currentAdminId = $_SESSION['adminUser']['id'] ?? 0;
        
        if ($id == $currentAdminId) {
            jsonResponse(['success' => false, 'message' => '不能删除自己']);
            return;
        }
        
        $db = db();
        $count = $db->fetchOne('SELECT COUNT(*) as count FROM admins');
        if ($count['count'] <= 1) {
            jsonResponse(['success' => false, 'message' => '至少保留一个管理员']);
            return;
        }
        
        $db->update('DELETE FROM admins WHERE id = ?', [$id]);
        
        jsonResponse(['success' => true, 'message' => '管理员已删除']);
    }
}
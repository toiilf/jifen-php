<?php
class Router_Index {
    
    public function index() {
        if (!isInstalled()) {
            redirect('/install');
            return;
        }
        
        try {
            $db = db();
            $result = $db->fetchOne(
                "SELECT TABLE_NAME FROM information_schema.TABLES 
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users'",
                [$_ENV['DB_NAME'] ?? 'ceshiji']
            );
            if (!$result) {
                redirect('/install');
                return;
            }
            if (isAuthenticated()) {
                redirect('/lobby');
                return;
            }
            redirect('/auth/login');
        } catch (Exception $e) {
            redirect('/install');
        }
    }
    
    public function joinRoom($params) {
        if (!isInstalled()) {
            redirect('/install');
            return;
        }
        
        $roomId = $params[0] ?? 0;
        
        if (isAuthenticated()) {
            $userId = $_SESSION['user']['id'];
            $db = db();
            
            $room = $db->fetchOne(
                "SELECT * FROM rooms WHERE id = ? AND status != 'finished'",
                [$roomId]
            );
            
            if (!$room) {
                render('error', ['title' => '错误', 'message' => '房间不存在或已关闭']);
                return;
            }
            
            $existing = $db->fetchOne(
                'SELECT id FROM room_players WHERE room_id = ? AND user_id = ?',
                [$roomId, $userId]
            );
            
            if ($existing) {
                redirect('/room/' . $roomId);
                return;
            }
            
            $players = $db->fetchOne(
                'SELECT COUNT(*) as count FROM room_players WHERE room_id = ?',
                [$roomId]
            );
            
            if ($players['count'] >= $room['max_players']) {
                render('error', ['title' => '错误', 'message' => '房间已满']);
                return;
            }
            
            $maxSeat = $db->fetchOne(
                'SELECT COALESCE(MAX(seat_number), 0) + 1 as next_seat 
                 FROM room_players WHERE room_id = ?',
                [$roomId]
            );
            
            $db->insert(
                'INSERT INTO room_players (room_id, user_id, seat_number) VALUES (?, ?, ?)',
                [$roomId, $userId, $maxSeat['next_seat'] ?? 1]
            );
            
            redirect('/room/' . $roomId);
            return;
        }
        
        render('join-choice', ['title' => '加入房间', 'roomId' => $roomId]);
    }
    
    public function joinRoomQuick($params) {
        if (!isInstalled()) {
            redirect('/install');
            return;
        }
        if (!isAuthenticated()) {
            redirect('/join-room/' . ($params[0] ?? ''));
            return;
        }
        
        $roomId = $params[0] ?? 0;
        $userId = $_SESSION['user']['id'];
        $db = db();
        
        $room = $db->fetchOne(
            "SELECT * FROM rooms WHERE id = ? AND status != 'finished'",
            [$roomId]
        );
        
        if (!$room) {
            render('error', ['title' => '错误', 'message' => '房间不存在或已关闭']);
            return;
        }
        
        $existing = $db->fetchOne(
            'SELECT id FROM room_players WHERE room_id = ? AND user_id = ?',
            [$roomId, $userId]
        );
        
        if ($existing) {
            redirect('/room/' . $roomId);
            return;
        }
        
        $players = $db->fetchOne(
            'SELECT COUNT(*) as count FROM room_players WHERE room_id = ?',
            [$roomId]
        );
        
        if ($players['count'] >= $room['max_players']) {
            render('error', ['title' => '错误', 'message' => '房间已满']);
            return;
        }
        
        $maxSeat = $db->fetchOne(
            'SELECT COALESCE(MAX(seat_number), 0) + 1 as next_seat 
             FROM room_players WHERE room_id = ?',
            [$roomId]
        );
        
        $db->insert(
            'INSERT INTO room_players (room_id, user_id, seat_number) VALUES (?, ?, ?)',
            [$roomId, $userId, $maxSeat['next_seat'] ?? 1]
        );
        
        $_SESSION['showGuide'] = true;
        redirect('/room/' . $roomId);
    }
    
    public function install() {
        if (isInstalled()) {
            redirect('/');
            return;
        }
        
        $dbConfig = [
            'db_host' => 'localhost',
            'db_port' => '3306',
            'db_user' => 'root',
            'db_password' => '',
            'db_name' => 'ceshiji',
            'admin_user' => 'admin'
        ];
        
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) continue;
                if (strpos($line, '=') === false) continue;
                list($k, $v) = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v);
                if ($k === 'DB_HOST') $dbConfig['db_host'] = $v;
                if ($k === 'DB_PORT') $dbConfig['db_port'] = $v;
                if ($k === 'DB_USER') $dbConfig['db_user'] = $v;
                if ($k === 'DB_NAME') $dbConfig['db_name'] = $v;
            }
        }
        
        $error = $_GET['error'] ?? null;
        
        render('install', [
            'title' => '系统安装',
            'error' => $error,
            'success' => null,
            'db_host' => $dbConfig['db_host'],
            'db_port' => $dbConfig['db_port'],
            'db_user' => $dbConfig['db_user'],
            'db_password' => $dbConfig['db_password'],
            'db_name' => $dbConfig['db_name'],
            'admin_user' => $dbConfig['admin_user']
        ]);
    }
    
    public function testConnection() {
        $data = json_decode(file_get_contents('php://input'), true);
        $db_host = $data['db_host'] ?? 'localhost';
        $db_port = $data['db_port'] ?? 3306;
        $db_user = $data['db_user'] ?? 'root';
        $db_password = $data['db_password'] ?? '';
        $db_name = $data['db_name'] ?? 'ceshiji';
        $db_ssl = $data['db_ssl'] ?? false;
        
        $dsn = "mysql:host=$db_host;port=$db_port;charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        
        if ($db_ssl) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        
        try {
            $pdo = new PDO($dsn, $db_user, $db_password, $options);
            $stmt = $pdo->query("SHOW DATABASES LIKE '$db_name'");
            $dbExists = $stmt->rowCount() > 0;
            
            jsonResponse([
                'success' => true,
                'message' => $dbExists ? '连接成功！数据库已存在' : '连接成功！数据库将被创建'
            ]);
        } catch (PDOException $e) {
            jsonResponse([
                'success' => false,
                'message' => '连接失败：' . $e->getMessage()
            ]);
        }
    }
    
    public function installPost() {
        $db_host = $_POST['db_host'] ?? 'localhost';
        $db_port = $_POST['db_port'] ?? 3306;
        $db_user = $_POST['db_user'] ?? 'root';
        $db_password = $_POST['db_password'] ?? '';
        $db_name = $_POST['db_name'] ?? 'ceshiji';
        $admin_user = $_POST['admin_user'] ?? 'admin';
        $admin_password = $_POST['admin_password'] ?? '';
        $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';
        $db_ssl = isset($_POST['db_ssl']);
        $clearData = isset($_POST['clear_data']);  // 新增：清空数据
        
        if ($admin_password !== $admin_password_confirm) {
            redirect('/install?error=' . urlencode('两次密码不一致'));
            return;
        }
        if (strlen($admin_password) < 6) {
            redirect('/install?error=' . urlencode('密码至少6个字符'));
            return;
        }
        
        $dsn = "mysql:host=$db_host;port=$db_port;charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        
        if ($db_ssl) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        
        try {
            $pdo = new PDO($dsn, $db_user, $db_password, $options);
            
            // 检查数据库是否存在
            $stmt = $pdo->query("SHOW DATABASES LIKE '$db_name'");
            $dbExists = $stmt->rowCount() > 0;
            
            // ============================================================
            // 如果勾选了清空数据，删除并重建数据库
            // ============================================================
            if ($dbExists && $clearData) {
                $pdo->exec("DROP DATABASE IF EXISTS `$db_name`");
                $dbExists = false;
            }
            
            // 创建数据库（如果不存在）
            if (!$dbExists) {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            $pdo->exec("USE `$db_name`");
            
            // ============================================================
            // 检查表是否存在
            // ============================================================
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            $tablesExist = $stmt->rowCount() > 0;
            
            if ($tablesExist && !$clearData) {
                // 表已存在且未勾选清空，提示用户
                render('install', [
                    'title' => '系统安装',
                    'error' => '数据库表已存在。如需清空数据重新安装，请勾选「清空现有数据」选项。',
                    'success' => null,
                    'db_host' => $db_host,
                    'db_port' => $db_port,
                    'db_user' => $db_user,
                    'db_password' => $db_password,
                    'db_name' => $db_name,
                    'admin_user' => $admin_user
                ]);
                return;
            }
            
            // ============================================================
            // 创建所有表
            // ============================================================
            $this->createTables($pdo);
            
            // 创建管理员账号
            $hashedPassword = password_hash($admin_password, PASSWORD_DEFAULT);
            $existingAdmin = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
            $existingAdmin->execute([$admin_user]);
            if ($existingAdmin->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
                $stmt->execute([$hashedPassword, $admin_user]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
                $stmt->execute([$admin_user, $hashedPassword]);
            }
            
            // 生成 .env 文件
            $this->generateEnvFile($db_host, $db_port, $db_user, $db_password, $db_name, $db_ssl);
            $this->createLockFiles();
            
            render('install', [
                'title' => '系统安装',
                'error' => null,
                'success' => '安装完成！' . ($clearData ? ' 已清空并重建数据库。' : ''),
                'admin_user' => $admin_user,
                'db_name' => $db_name,
                'db_host' => $db_host,
                'db_port' => $db_port,
                'db_user' => $db_user,
                'db_password' => $db_password
            ]);
            
        } catch (Exception $e) {
            redirect('/install?error=' . urlencode('安装失败：' . $e->getMessage()));
        }
    }
    
    // ============================================================
    // 辅助方法：创建表
    // ============================================================
    private function createTables($pdo) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            nickname VARCHAR(50),
            avatar VARCHAR(255) DEFAULT 'default.png',
            total_games INT DEFAULT 0,
            wins INT DEFAULT 0,
            total_score BIGINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_nickname (nickname),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin_username (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_name VARCHAR(100) NOT NULL,
            creator_id INT NOT NULL,
            password VARCHAR(255),
            max_players INT DEFAULT 4,
            status ENUM('waiting','playing','finished') DEFAULT 'waiting',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_room_name (room_name),
            INDEX idx_creator_id (creator_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            INDEX idx_status_created (status, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS room_players (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            user_id INT NOT NULL,
            seat_number INT NOT NULL,
            current_score BIGINT DEFAULT 0,
            join_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_room_user (room_id, user_id),
            INDEX idx_room_user (room_id, user_id),
            INDEX idx_room_id (room_id),
            INDEX idx_user_id (user_id),
            INDEX idx_seat_number (seat_number),
            INDEX idx_current_score (current_score),
            INDEX idx_room_score (room_id, current_score DESC)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS score_transfers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            from_user_id INT NOT NULL,
            to_user_id INT NOT NULL,
            amount BIGINT NOT NULL,
            transfer_type ENUM('win','lose','transfer') DEFAULT 'transfer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
            FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_room_id (room_id),
            INDEX idx_from_user_id (from_user_id),
            INDEX idx_to_user_id (to_user_id),
            INDEX idx_created_at (created_at DESC),
            INDEX idx_room_created (room_id, created_at DESC),
            INDEX idx_user_from (from_user_id, created_at DESC),
            INDEX idx_user_to (to_user_id, created_at DESC),
            INDEX idx_transfer_type (transfer_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS game_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            winner_id INT NOT NULL,
            total_pot BIGINT DEFAULT 0,
            game_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
            FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_room_id (room_id),
            INDEX idx_winner_id (winner_id),
            INDEX idx_game_date (game_date DESC),
            INDEX idx_room_date (room_id, game_date DESC),
            INDEX idx_winner_date (winner_id, game_date DESC)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    
    // ============================================================
    // 辅助方法：生成 .env 文件
    // ============================================================
    private function generateEnvFile($db_host, $db_port, $db_user, $db_password, $db_name, $db_ssl) {
        $sessionSecret = bin2hex(random_bytes(32));
        $envContent = "DB_HOST=$db_host\n";
        $envContent .= "DB_PORT=$db_port\n";
        $envContent .= "DB_USER=$db_user\n";
        $envContent .= "DB_PASSWORD=$db_password\n";
        $envContent .= "DB_NAME=$db_name\n";
        if ($db_ssl) {
            $envContent .= "DB_SSL=true\n";
        }
        $envContent .= "SESSION_SECRET=$sessionSecret\n";
        $envContent .= "PORT=3000\n";
        $envContent .= "TIMEZONE=Asia/Shanghai\n";
        
        $envPath = realpath(__DIR__ . '/../') . '/.env';
        file_put_contents($envPath, $envContent);
    }
    
    // ============================================================
    // 辅助方法：创建锁定文件
    // ============================================================
    private function createLockFiles() {
        $rootPath = realpath(__DIR__ . '/../');
        file_put_contents($rootPath . '/.installed', 'installed');
        file_put_contents($rootPath . '/install.lock', date('Y-m-d H:i:s') . ' - 安装完成');
    }
    
    public function uninstall() {
        if (!isAdmin()) {
            render('error', [
                'title' => '权限不足', 
                'message' => '只有管理员可以执行此操作'
            ]);
            return;
        }
        
        $rootPath = realpath(__DIR__ . '/../');
        $lockPath = $rootPath . '/install.lock';
        $installedPath = $rootPath . '/.installed';
        
        $deleted = [];
        
        if (file_exists($lockPath)) {
            unlink($lockPath);
            $deleted[] = 'install.lock';
        }
        
        if (file_exists($installedPath)) {
            unlink($installedPath);
            $deleted[] = '.installed';
        }
        
        if (empty($deleted)) {
            render('error', [
                'title' => '提示',
                'message' => '系统尚未安装或已被解锁，无需重复操作。'
            ]);
            return;
        }
        
        render('uninstall', [
            'title' => '已解锁安装',
            'message' => '已删除 ' . implode('、', $deleted) . '，系统已解锁，可以重新安装。',
            'deleted_files' => $deleted
        ]);
    }
}
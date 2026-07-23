<?php
class Router_Auth {
    
    public function login() {
        requireNotAuthenticated();
        $error = null;
        $redirect = $_GET['redirect'] ?? '';
        
        render('login', [
            'title' => '登录',
            'error' => $error,
            'redirect' => safeHtml($redirect)
        ]);
    }
    
    public function loginPost() {
        requireNotAuthenticated();
        requirePost();
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $redirect = safeHtml($_POST['redirect'] ?? '');
        
        // 验证输入
        $username = validateUsername($username);
        if (!$username) {
            render('login', ['title' => '登录', 'error' => '昵称格式无效', 'redirect' => $redirect]);
            return;
        }
        
        // 检查登录尝试次数
        if (!checkLoginAttempts($username)) {
            render('login', ['title' => '登录', 'error' => '登录尝试过多，请5分钟后再试', 'redirect' => $redirect]);
            return;
        }
        
        $db = db();
        $user = $db->fetchOne('SELECT * FROM users WHERE username = ?', [$username]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            render('login', ['title' => '登录', 'error' => '昵称或密码错误', 'redirect' => $redirect]);
            return;
        }
        
        // 登录成功，重置尝试记录
        $_SESSION['login_' . md5($username)] = null;
        
        session_regenerate_id(true);
        
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'nickname' => $user['nickname'] ?: $user['username']
        ];
        $_SESSION['last_activity'] = time();
        
        if ($redirect) {
            redirect($redirect);
            return;
        }
        
        if (isset($_SESSION['redirectRoom'])) {
            $roomId = $_SESSION['redirectRoom'];
            unset($_SESSION['redirectRoom']);
            redirect('/join-room/' . $roomId);
            return;
        }
        
        redirect('/lobby');
    }
    
    public function register() {
        requireNotAuthenticated();
        $error = null;
        
        render('register', [
            'title' => '注册',
            'error' => $error
        ]);
    }
    
    public function registerPost() {
        requireNotAuthenticated();
        requirePost();
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // 验证用户名
        $username = validateUsername($username);
        if (!$username) {
            render('register', ['title' => '注册', 'error' => '昵称格式无效（2-50个字符，仅支持中文、英文、数字、下划线）']);
            return;
        }
        
        // 验证密码
        if (!validatePassword($password)) {
            render('register', ['title' => '注册', 'error' => '密码至少6个字符']);
            return;
        }
        
        if ($password !== $confirm_password) {
            render('register', ['title' => '注册', 'error' => '两次密码不一致']);
            return;
        }
        
        $db = db();
        $existing = $db->fetchOne('SELECT id FROM users WHERE username = ?', [$username]);
        if ($existing) {
            render('register', ['title' => '注册', 'error' => '该昵称已被使用']);
            return;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userId = $db->insert(
            'INSERT INTO users (username, password, nickname) VALUES (?, ?, ?)',
            [$username, $hashedPassword, $username]
        );
        
        session_regenerate_id(true);
        
        $_SESSION['user'] = [
            'id' => $userId,
            'username' => $username,
            'nickname' => $username
        ];
        $_SESSION['last_activity'] = time();
        
        if (isset($_SESSION['redirectRoom'])) {
            $roomId = $_SESSION['redirectRoom'];
            unset($_SESSION['redirectRoom']);
            redirect('/join-room/' . $roomId);
            return;
        }
        
        redirect('/lobby');
    }
    
    public function quickRegister() {
        // 只需要 POST 方法验证，不需要 AJAX 验证
        requirePost();
        
        $nickname = randomNickname();
        $defaultPassword = '123456';
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $db = db();
        $existing = $db->fetchOne('SELECT id FROM users WHERE username = ?', [$nickname]);
        $attempts = 0;
        while ($existing && $attempts < 20) {
            $nickname = randomNickname();
            $existing = $db->fetchOne('SELECT id FROM users WHERE username = ?', [$nickname]);
            $attempts++;
        }
        
        $userId = $db->insert(
            'INSERT INTO users (username, password, nickname) VALUES (?, ?, ?)',
            [$nickname, $hashedPassword, $nickname]
        );
        
        session_regenerate_id(true);
        
        $_SESSION['user'] = [
            'id' => $userId,
            'username' => $nickname,
            'nickname' => $nickname
        ];
        $_SESSION['last_activity'] = time();
        $_SESSION['showGuide'] = true;
        
        jsonResponse([
            'success' => true,
            'nickname' => $nickname,
            'password' => $defaultPassword
        ]);
    }
    
    public function logout() {
        session_destroy();
        redirect('/auth/login');
    }
}
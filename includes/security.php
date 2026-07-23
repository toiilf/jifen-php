<?php
// includes/security.php - 安全函数库

// ===== 防止 SQL 注入 =====
function safeSql($value) {
    $db = db();
    $conn = $db->getConnection();
    if ($conn) {
        return substr($conn->quote($value), 1, -1);
    }
    return addslashes($value);
}

// ===== 防止 XSS 攻击 =====
function safeHtml($value) {
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function safeOutput($value) {
    if (is_array($value)) {
        return array_map('safeOutput', $value);
    }
    return safeHtml($value);
}

// ===== 防止 CSRF =====
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// 在表单中添加 CSRF 隐藏字段
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

// ===== 输入验证 =====
function validateUsername($username) {
    $username = trim($username);
    if (strlen($username) < 2 || strlen($username) > 50) {
        return false;
    }
    // 只允许中文、英文、数字、下划线、连字符
    if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_\-\s]+$/u', $username)) {
        return false;
    }
    return $username;
}

function validatePassword($password) {
    if (strlen($password) < 6 || strlen($password) > 255) {
        return false;
    }
    return $password;
}

function validateRoomName($name) {
    $name = trim($name);
    if (strlen($name) < 1 || strlen($name) > 100) {
        return false;
    }
    // 移除危险字符
    $name = strip_tags($name);
    $name = preg_replace('/[<>"\']/', '', $name);
    return $name;
}

function validateAmount($amount) {
    $amount = intval($amount);
    if ($amount <= 0 || $amount > 999999999) {
        return false;
    }
    return $amount;
}

function validateId($id) {
    $id = intval($id);
    if ($id <= 0) {
        return false;
    }
    return $id;
}

// ===== 速率限制（防止暴力破解） =====
function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 60) {
    $sessionKey = 'rate_limit_' . $key;
    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    $data = $_SESSION[$sessionKey];
    $elapsed = time() - $data['first_attempt'];
    
    if ($elapsed > $timeWindow) {
        // 重置
        $_SESSION[$sessionKey] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    if ($data['count'] >= $maxAttempts) {
        return false;
    }
    
    $_SESSION[$sessionKey]['count']++;
    return true;
}

// ===== 请求方法验证 =====
function requirePost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => '无效的请求方法']);
        exit;
    }
}

function requireAjax() {
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        jsonResponse(['success' => false, 'message' => '无效的请求']);
        exit;
    }
}

// ===== 安全响应头 =====
function setSecurityHeaders() {
    // 防止 XSS
    header('X-XSS-Protection: 1; mode=block');
    // 防止 MIME 类型嗅探
    header('X-Content-Type-Options: nosniff');
    // 防止点击劫持
    header('X-Frame-Options: SAMEORIGIN');
    // 内容安全策略
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://api.qrserver.com;");
    // 引用策略
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // 权限策略
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// ===== 会话安全 =====
function secureSession() {
    // 仅在 HTTPS 下启用
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    // 防止 JavaScript 访问会话 cookie
    ini_set('session.cookie_httponly', 1);
    // 使用严格的 SameSite
    ini_set('session.cookie_samesite', 'Lax');
    // 会话超时（30分钟）
    ini_set('session.gc_maxlifetime', 1800);
}

// ===== 密码强度验证 =====
function validatePasswordStrength($password) {
    $score = 0;
    if (strlen($password) >= 8) $score++;
    if (preg_match('/[a-z]/', $password)) $score++;
    if (preg_match('/[A-Z]/', $password)) $score++;
    if (preg_match('/[0-9]/', $password)) $score++;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $score++;
    return $score >= 3; // 至少中等强度
}
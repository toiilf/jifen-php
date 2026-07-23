<?php
// includes/auth.php

// ===== 会话安全配置 =====
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    @ini_set('session.cookie_secure', 1);
}
@ini_set('session.cookie_httponly', 1);
if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
    @ini_set('session.cookie_samesite', 'Lax');
}
@ini_set('session.gc_maxlifetime', 1800);

// ===== 认证函数 =====
function requireAuth() {
    if (!isAuthenticated()) {
        redirect('/auth/login');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        render('error', [
            'title' => '权限不足',
            'message' => '您没有管理员权限'
        ]);
        exit;
    }
}

function requireNotAuthenticated() {
    if (isAuthenticated()) {
        redirect('/lobby');
        exit;
    }
}

// ===== 登录失败锁定（防暴力破解） =====
function checkLoginAttempts($username) {
    $key = 'login_' . md5($username);
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    $data = $_SESSION[$key];
    $elapsed = time() - $data['first_attempt'];
    
    if ($elapsed > 300) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    if ($data['count'] >= 5) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}

// ===== 会话超时检查 =====
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_destroy();
        redirect('/auth/login');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

// ============================================================
// 注意：CSRF 函数已移至 functions.php，此处不再重复定义
// generateCsrfToken() 和 verifyCsrfToken() 在 functions.php 中
// ============================================================
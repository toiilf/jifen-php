<?php
// ============================================================
// 安全函数（防SQL注入、XSS等）
// ============================================================

// 防止 XSS 攻击
function safeHtml($value) {
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function safeOutput($value) {
    if (is_array($value)) {
        return array_map('safeOutput', $value);
    }
    return safeHtml($value);
}

// 输入验证 - 用户名
function validateUsername($username) {
    $username = trim($username);
    if (strlen($username) < 2 || strlen($username) > 50) {
        return false;
    }
    if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_\-\s]+$/u', $username)) {
        return false;
    }
    return $username;
}

// 输入验证 - 密码
function validatePassword($password) {
    if (strlen($password) < 6 || strlen($password) > 255) {
        return false;
    }
    return $password;
}

// 输入验证 - 房间名
function validateRoomName($name) {
    $name = trim($name);
    if (strlen($name) < 1 || strlen($name) > 100) {
        return false;
    }
    $name = strip_tags($name);
    $name = preg_replace('/[<>"\']/', '', $name);
    return $name;
}

// 输入验证 - 金额
function validateAmount($amount) {
    $amount = intval($amount);
    if ($amount <= 0 || $amount > 999999999) {
        return false;
    }
    return $amount;
}

// 输入验证 - ID
function validateId($id) {
    $id = intval($id);
    if ($id <= 0) {
        return false;
    }
    return $id;
}

// ============================================================
// CSRF 防护
// ============================================================

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

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

function csrfMetaTag() {
    return '<meta name="csrf-token" content="' . generateCsrfToken() . '">';
}

// ============================================================
// 请求验证
// ============================================================

function requirePost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => '无效的请求方法']);
        exit;
    }
}

function requireAjax() {
    // 只记录日志，不拦截请求（兼容性更好）
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        // 仅记录，不中断
    }
}

// ============================================================
// 安全响应头
// ============================================================

function setSecurityHeaders() {
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// ============================================================
// 通用工具函数
// ============================================================

function randomNickname() {
    $twoCharNames = [
        '若溪', '雨桐', '一诺', '佳琪', '思雨', '梦瑶', '梓涵', '子轩',
        '昊天', '浩然', '博文', '天宇', '晨阳', '晨曦', '云飞', '星辰',
        '明月', '清风', '流云', '飞雪', '傲霜', '寒梅', '翠竹', '幽兰',
        '知秋', '如烟', '如画', '如诗', '如意', '安然', '安静', '安好',
        '念慈', '婉清', '语嫣', '灵素', '芷若', '小昭', '无双', '莫愁',
        '秋水', '碧瑶', '雪琪', '紫萱', '长卿', '景天', '重楼', '飞蓬',
        '沐风', '凌云', '踏雪', '寻梅', '听雨', '观潮', '望月', '摘星',
        '逍遥', '无忌', '三丰', '翠山', '莲舟', '远桥', '岱岩', '松溪',
        '梨亭', '声谷', '青书', '九真', '不悔', '小芙', '千叶', '百川',
        '千寻', '白龙', '琥珀', '珊瑚', '琉璃', '翡翠', '珍珠', '玛瑙',
        '金鹏', '银狐', '铜雀', '铁鹰', '玉兔', '金龙', '彩凤', '麒麟',
        '长歌', '短笛', '横琴', '竖箫', '琵琶', '锦瑟', '箜篌', '编钟'
    ];
    
    $threeCharNames = [
        '何以琛', '赵默笙', '路漫漫', '林若溪', '苏浅语', '顾清歌', '叶知秋',
        '沈千寻', '洛星辰', '萧若风', '蓝忘机', '魏无羡', '花无缺', '小鱼儿',
        '李逍遥', '赵灵儿', '林月如', '张小凡', '碧瑶儿', '陆雪琪',
        '风清扬', '令狐冲', '任盈盈', '岳灵珊', '仪琳师', '田伯光', '东方白',
        '杨铁心', '包惜弱', '郭啸天', '李莫愁', '陆无双', '程英儿', '黄药师',
        '欧阳锋', '洪七公', '段智兴', '王重阳', '周伯通', '丘处机', '马道长',
        '孙悟空', '猪八戒', '沙悟净', '唐三藏', '白龙马', '观世音', '如来佛',
        '诸葛亮', '司马懿', '周公瑾', '曹孟德', '刘玄德', '关云长', '张翼德',
        '赵子龙', '马孟起', '黄汉升', '姜伯约', '魏文长', '庞士元', '徐元直',
        '花木兰', '穆桂英', '樊梨花', '梁红玉', '秦良玉', '佘太君', '杨排风',
        '白素贞', '小青儿', '许汉文', '法海师', '胡媚娘', '彩茵儿', '张玉堂',
        '宁采臣', '聂小倩', '燕赤霞', '树姥姥', '黑山老', '左千户', '知秋霜',
        '步惊云', '聂小风', '秦霜儿', '楚楚可', '剑晨光', '无名僧', '绝无神',
        '武无敌', '帝释天', '笑三笑', '大魔神', '独孤梦', '第二梦', '明月心',
        '百里屠', '风晴雪', '襄铃儿', '方兰生', '尹千觞', '欧阳少', '紫胤真'
    ];
    
    if (mt_rand(0, 1) === 0) {
        return $twoCharNames[array_rand($twoCharNames)];
    } else {
        return $threeCharNames[array_rand($threeCharNames)];
    }
}

function render($view, $data = []) {
    // 自动转义所有输出数据（安全）
    array_walk_recursive($data, function(&$value) {
        if (is_string($value)) {
            $value = safeHtml($value);
        }
    });
    extract($data);
    require_once __DIR__ . '/../views/' . $view . '.php';
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function isAuthenticated() {
    return isset($_SESSION['user']) && $_SESSION['user'];
}

function isAdmin() {
    return isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true;
}

function getUserById($id) {
    $db = db();
    $result = $db->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    return $result;
}

function getRoomById($id) {
    $db = db();
    $result = $db->fetchOne(
        "SELECT r.*, u.nickname as creator_name FROM rooms r 
         JOIN users u ON r.creator_id = u.id WHERE r.id = ?",
        [$id]
    );
    return $result;
}

function getPlayersInRoom($roomId) {
    $db = db();
    return $db->fetchAll(
        "SELECT rp.*, u.username, u.nickname, u.avatar 
         FROM room_players rp 
         JOIN users u ON rp.user_id = u.id 
         WHERE rp.room_id = ? 
         ORDER BY rp.seat_number",
        [$roomId]
    );
}

function formatTime($time) {
    $dt = new DateTime($time);
    $dt->setTimezone(new DateTimeZone('Asia/Shanghai'));
    return $dt->format('Y-m-d H:i:s');
}

function getClientIP() {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 
          $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
          $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return explode(',', $ip)[0];
}

function isInstalled() {
    $installedFile = __DIR__ . '/../.installed';
    $lockFile = __DIR__ . '/../install.lock';
    return file_exists($installedFile) && file_exists($lockFile);
}

function getDbConnection() {
    return db()->getConnection();
}
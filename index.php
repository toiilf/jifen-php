<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 禁用缓存
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

session_start();

require_once __DIR__ . '/config/database.php';   // 1. 数据库
require_once __DIR__ . '/includes/functions.php'; // 2. 函数（包含安全函数）
require_once __DIR__ . '/includes/auth.php';      // 3. 认证（依赖 functions.php）

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = rtrim($path, '/');
if (empty($path)) $path = '/';

// ===== 调试：记录请求路径 =====
error_log("请求路径: " . $path);

// ===== 先处理静态文件 =====
$staticFiles = ['css', 'js', 'images', 'favicon.ico'];
foreach ($staticFiles as $dir) {
    if (strpos($path, '/' . $dir . '/') === 0 || $path == '/' . $dir) {
        $filePath = __DIR__ . '/public' . $path;
        if (file_exists($filePath) && is_file($filePath)) {
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
            ];
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            readfile($filePath);
            exit;
        }
    }
}

// ===== 处理动态路由（按优先级顺序） =====

// 1. API 路由: /api/room/1, /api/transfer, /api/room/dismiss
if (preg_match('#^/api/room/(\d+)$#', $path, $matches)) {
    $controllerName = 'api';
    $actionName = 'room';
    $params = [$matches[1]];
}
elseif ($path === '/api/transfer') {
    $controllerName = 'api';
    $actionName = 'transfer';
    $params = [];
}
elseif ($path === '/api/room/dismiss') {
    $controllerName = 'api';
    $actionName = 'dismiss';
    $params = [];
}

// 2. 房间路由: /room/1, /room/leave/1
elseif (preg_match('#^/room/(\d+)$#', $path, $matches)) {
    $controllerName = 'room';
    $actionName = 'show';
    $params = [$matches[1]];
}
elseif (preg_match('#^/room/leave/(\d+)$#', $path, $matches)) {
    $controllerName = 'room';
    $actionName = 'leave';
    $params = [$matches[1]];
}

// 3. 加入房间路由: /join-room/xxx, /join-room/xxx/quick
elseif (preg_match('#^/join-room/([^/]+)/quick$#', $path, $matches)) {
    $controllerName = 'index';
    $actionName = 'joinRoomQuick';
    $params = [$matches[1]];
}
elseif (preg_match('#^/join-room/([^/]+)$#', $path, $matches)) {
    $controllerName = 'index';
    $actionName = 'joinRoom';
    $params = [$matches[1]];
}
elseif ($path === '/uninstall') {
    $controllerName = 'index';
    $actionName = 'uninstall';
    $params = [];
}

// 4. 精确匹配路由
else {
    $routes = [
        '/' => ['controller' => 'index', 'action' => 'index'],
        '/install' => ['controller' => 'index', 'action' => 'install'],
        '/install/test-connection' => ['controller' => 'index', 'action' => 'testConnection'],
        '/reinstall' => ['controller' => 'index', 'action' => 'reinstall'],
        
        '/auth/login' => ['controller' => 'auth', 'action' => 'login'],
        '/auth/register' => ['controller' => 'auth', 'action' => 'register'],
        '/auth/quick-register' => ['controller' => 'auth', 'action' => 'quickRegister'],
        '/auth/logout' => ['controller' => 'auth', 'action' => 'logout'],
        
        '/lobby' => ['controller' => 'lobby', 'action' => 'index'],
        '/lobby/my-rooms' => ['controller' => 'lobby', 'action' => 'myRooms'],
        '/lobby/joined-rooms' => ['controller' => 'lobby', 'action' => 'joinedRooms'],
        '/lobby/create-room' => ['controller' => 'lobby', 'action' => 'createRoom'],
        '/lobby/join-room' => ['controller' => 'lobby', 'action' => 'joinRoomPost'],
        '/lobby/leave-room' => ['controller' => 'lobby', 'action' => 'leaveRoom'],
        '/lobby/dismiss-room' => ['controller' => 'lobby', 'action' => 'dismissRoom'],
        
        '/profile' => ['controller' => 'profile', 'action' => 'index'],
        '/profile/update' => ['controller' => 'profile', 'action' => 'update'],
        '/profile/change-password' => ['controller' => 'profile', 'action' => 'changePassword'],
        '/profile/update-all' => ['controller' => 'profile', 'action' => 'updateAll'],
        
        '/history' => ['controller' => 'history', 'action' => 'index'],
        
        '/admin/login' => ['controller' => 'admin', 'action' => 'login'],
        '/admin' => ['controller' => 'admin', 'action' => 'index'],
        '/admin/logout' => ['controller' => 'admin', 'action' => 'logout'],
        '/admin/api/stats' => ['controller' => 'admin', 'action' => 'apiStats'],
        '/admin/api/users/list' => ['controller' => 'admin', 'action' => 'apiUsersList'],
        '/admin/api/users/create' => ['controller' => 'admin', 'action' => 'apiUsersCreate'],
        '/admin/api/users/update' => ['controller' => 'admin', 'action' => 'apiUsersUpdate'],
        '/admin/api/users/delete' => ['controller' => 'admin', 'action' => 'apiUsersDelete'],
        '/admin/api/rooms/list' => ['controller' => 'admin', 'action' => 'apiRoomsList'],
        '/admin/api/rooms/close' => ['controller' => 'admin', 'action' => 'apiRoomsClose'],
        '/admin/api/rooms/delete' => ['controller' => 'admin', 'action' => 'apiRoomsDelete'],
        '/admin/api/admins/list' => ['controller' => 'admin', 'action' => 'apiAdminsList'],
        '/admin/api/admins/create' => ['controller' => 'admin', 'action' => 'apiAdminsCreate'],
        '/admin/api/admins/update' => ['controller' => 'admin', 'action' => 'apiAdminsUpdate'],
        '/admin/api/admins/delete' => ['controller' => 'admin', 'action' => 'apiAdminsDelete'],
    ];
    
    $matched = false;
    foreach ($routes as $route => $routeConfig) {
        if ($path === $route) {
            $controllerName = $routeConfig['controller'];
            $actionName = $routeConfig['action'];
            $params = [];
            $matched = true;
            break;
        }
    }
    
    if (!$matched) {
        http_response_code(404);
        require_once __DIR__ . '/views/404.php';
        exit;
    }
}

// ===== 加载控制器 =====
error_log("控制器: " . $controllerName . ", 方法: " . $actionName . ", 参数: " . print_r($params, true));

$controllerFile = __DIR__ . '/routes/' . $controllerName . '.php';
if (!file_exists($controllerFile)) {
    http_response_code(404);
    require_once __DIR__ . '/views/404.php';
    exit;
}

require_once $controllerFile;
$controllerClass = 'Router_' . ucfirst($controllerName);
if (!class_exists($controllerClass)) {
    http_response_code(404);
    require_once __DIR__ . '/views/404.php';
    exit;
}

$controller = new $controllerClass();

// 处理 POST 请求
if ($requestMethod === 'POST') {
    $postAction = $actionName . 'Post';
    if (method_exists($controller, $postAction)) {
        $actionName = $postAction;
    }
}

if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    require_once __DIR__ . '/views/404.php';
    exit;
}


$controller->$actionName($params);
<?php
// 加载环境变量
function loadEnv() {
    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) return;
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

loadEnv();

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? 3306;
        $user = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '';
        $database = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'card_game';
        $ssl = ($_ENV['DB_SSL'] ?? $_SERVER['DB_SSL'] ?? 'false') === 'true';
        
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,  // 禁用模拟预处理，防止SQL注入
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::MYSQL_ATTR_LOCAL_INFILE => false,  // 禁用本地文件读取
        ];
        
        if ($ssl) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        
        try {
            $this->connection = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            $this->connection = null;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // 使用预处理语句执行查询（防SQL注入）
    public function query($sql, $params = []) {
        if (!$this->connection) return false;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : null;
    }
    
    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $this->connection->lastInsertId() : 0;
    }
    
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : 0;
    }
    
    public function beginTransaction() {
        if ($this->connection) $this->connection->beginTransaction();
    }
    
    public function commit() {
        if ($this->connection) $this->connection->commit();
    }
    
    public function rollback() {
        if ($this->connection) $this->connection->rollback();
    }
    
    // 转义输入（备用）
    public function escape($value) {
        if ($this->connection) {
            return substr($this->connection->quote($value), 1, -1);
        }
        return addslashes($value);
    }
}

function db() {
    return Database::getInstance();
}
<?php
/**
 * Database Connection Class
 * Database connection and query management
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
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
    
    /**
     * Execute a SELECT query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            throw new Exception("Query execution failed");
        }
    }
    
    /**
     * Execute an INSERT, UPDATE, or DELETE query
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Execute error: " . $e->getMessage());
            throw new Exception("Operation execution failed");
        }
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * Initialize database schema
     */
    public static function initSchema() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        
        // Workflows table
        $conn->exec("CREATE TABLE IF NOT EXISTS workflows (
            id VARCHAR(36) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            data LONGTEXT NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_is_active (is_active),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Executions table
        $conn->exec("CREATE TABLE IF NOT EXISTS executions (
            id VARCHAR(36) PRIMARY KEY,
            workflow_id VARCHAR(36) NOT NULL,
            status ENUM('running', 'success', 'failed', 'cancelled') DEFAULT 'running',
            started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            finished_at TIMESTAMP NULL,
            duration INT,
            input_data LONGTEXT,
            output_data LONGTEXT,
            logs LONGTEXT,
            error_message TEXT,
            FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE CASCADE,
            INDEX idx_workflow_id (workflow_id),
            INDEX idx_status (status),
            INDEX idx_started_at (started_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Webhooks table
        $conn->exec("CREATE TABLE IF NOT EXISTS webhooks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            workflow_id VARCHAR(36) NOT NULL,
            path VARCHAR(255) UNIQUE NOT NULL,
            method VARCHAR(10) DEFAULT 'POST',
            is_active TINYINT(1) DEFAULT 1,
            last_triggered_at TIMESTAMP NULL,
            trigger_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE CASCADE,
            INDEX idx_path (path),
            INDEX idx_workflow_id (workflow_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        return true;
    }
}

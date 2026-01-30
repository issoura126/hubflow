<?php
/**
 * N8N Pro - Installation Handler
 * Handles database setup and initialization
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u372572592_N8N');
define('DB_USER', 'u372572592_N8N');
define('DB_PASS', '1@s~?^k5Q');
define('DB_CHARSET', 'utf8mb4');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'check_connection':
            checkConnection();
            break;
        case 'create_database':
            createDatabase();
            break;
        case 'initialize_schema':
            initializeSchema();
            break;
        case 'verify':
            verifyInstallation();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Check database connection
 */
function checkConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Database connection successful',
            'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Connection failed: ' . $e->getMessage()
        ]);
    }
}

/**
 * Create database
 */
function createDatabase() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
                    DEFAULT CHARACTER SET utf8mb4 
                    COLLATE utf8mb4_unicode_ci");
        
        echo json_encode([
            'success' => true,
            'message' => 'Database created successfully'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Initialize database schema
 */
function initializeSchema() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $tables = [];
        
        // Table: workflows
        $pdo->exec("CREATE TABLE IF NOT EXISTS `workflows` (
            `id` VARCHAR(36) PRIMARY KEY COMMENT 'Unique workflow identifier',
            `name` VARCHAR(255) NOT NULL COMMENT 'Workflow name',
            `description` TEXT COMMENT 'Workflow description',
            `data` LONGTEXT NOT NULL COMMENT 'JSON workflow data',
            `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Active status',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_is_active` (`is_active`),
            INDEX `idx_created_at` (`created_at`),
            INDEX `idx_name` (`name`(50))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $tables[] = 'workflows';
        
        // Table: executions
        $pdo->exec("CREATE TABLE IF NOT EXISTS `executions` (
            `id` VARCHAR(36) PRIMARY KEY,
            `workflow_id` VARCHAR(36) NOT NULL,
            `status` ENUM('running', 'success', 'failed', 'cancelled') DEFAULT 'running',
            `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `finished_at` TIMESTAMP NULL,
            `duration` INT,
            `input_data` LONGTEXT,
            `output_data` LONGTEXT,
            `logs` LONGTEXT,
            `error_message` TEXT,
            FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
            INDEX `idx_workflow_id` (`workflow_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_started_at` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $tables[] = 'executions';
        
        // Table: webhooks
        $pdo->exec("CREATE TABLE IF NOT EXISTS `webhooks` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `workflow_id` VARCHAR(36) NOT NULL,
            `path` VARCHAR(255) UNIQUE NOT NULL,
            `method` VARCHAR(10) DEFAULT 'POST',
            `is_active` TINYINT(1) DEFAULT 1,
            `last_triggered_at` TIMESTAMP NULL,
            `trigger_count` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
            INDEX `idx_path` (`path`),
            INDEX `idx_workflow_id` (`workflow_id`),
            INDEX `idx_is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $tables[] = 'webhooks';
        
        // Table: node_results
        $pdo->exec("CREATE TABLE IF NOT EXISTS `node_results` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `execution_id` VARCHAR(36) NOT NULL,
            `node_id` VARCHAR(50) NOT NULL,
            `node_type` VARCHAR(50) NOT NULL,
            `status` ENUM('success', 'failed', 'skipped') DEFAULT 'success',
            `input_data` LONGTEXT,
            `output_data` LONGTEXT,
            `error_message` TEXT,
            `execution_time` INT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`execution_id`) REFERENCES `executions`(`id`) ON DELETE CASCADE,
            INDEX `idx_execution_id` (`execution_id`),
            INDEX `idx_node_type` (`node_type`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $tables[] = 'node_results';
        
        // Table: scheduled_tasks
        $pdo->exec("CREATE TABLE IF NOT EXISTS `scheduled_tasks` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `workflow_id` VARCHAR(36) NOT NULL,
            `cron_expression` VARCHAR(100) NOT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `next_run` TIMESTAMP NULL,
            `last_run` TIMESTAMP NULL,
            `run_count` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
            INDEX `idx_workflow_id` (`workflow_id`),
            INDEX `idx_is_active` (`is_active`),
            INDEX `idx_next_run` (`next_run`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $tables[] = 'scheduled_tasks';
        
        // Table: api_credentials
        $pdo->exec("CREATE TABLE IF NOT EXISTS `api_credentials` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `type` VARCHAR(50) NOT NULL,
            `credentials` TEXT NOT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_type` (`type`),
            INDEX `idx_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $tables[] = 'api_credentials';
        
        // Table: workflow_variables
        $pdo->exec("CREATE TABLE IF NOT EXISTS `workflow_variables` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `workflow_id` VARCHAR(36) NULL,
            `variable_key` VARCHAR(100) NOT NULL,
            `variable_value` TEXT,
            `variable_type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
            `is_encrypted` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_workflow_key` (`workflow_id`, `variable_key`),
            INDEX `idx_workflow_id` (`workflow_id`),
            INDEX `idx_variable_key` (`variable_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $tables[] = 'workflow_variables';
        
        echo json_encode([
            'success' => true,
            'message' => 'Schema initialized successfully',
            'tables_created' => count($tables),
            'tables' => $tables
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Verify installation
 */
function verifyInstallation() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Check all tables exist
        $requiredTables = [
            'workflows',
            'executions',
            'webhooks',
            'node_results',
            'scheduled_tasks',
            'api_credentials',
            'workflow_variables'
        ];
        
        $stmt = $pdo->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $missingTables = array_diff($requiredTables, $existingTables);
        
        if (empty($missingTables)) {
            // Count rows in workflows table
            $stmt = $pdo->query("SELECT COUNT(*) FROM workflows");
            $workflowCount = $stmt->fetchColumn();
            
            echo json_encode([
                'success' => true,
                'message' => 'Installation verified successfully',
                'tables_found' => count($existingTables),
                'workflows_count' => $workflowCount,
                'database' => DB_NAME
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Missing tables: ' . implode(', ', $missingTables)
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

-- ================================================
-- N8N Pro - Database Setup Script
-- Enhanced Database Schema with Full Support
-- ================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS `n8n_pro` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `n8n_pro`;

-- ================================================
-- Table: workflows
-- Stores workflow definitions and configurations
-- ================================================
CREATE TABLE IF NOT EXISTS `workflows` (
    `id` VARCHAR(36) PRIMARY KEY COMMENT 'Unique workflow identifier (UUID)',
    `name` VARCHAR(255) NOT NULL COMMENT 'Workflow name',
    `description` TEXT COMMENT 'Workflow description',
    `data` LONGTEXT NOT NULL COMMENT 'JSON workflow data (nodes, connections)',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Active status (0=inactive, 1=active)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_name` (`name`(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Workflow definitions';

-- ================================================
-- Table: executions
-- Tracks workflow execution history and logs
-- ================================================
CREATE TABLE IF NOT EXISTS `executions` (
    `id` VARCHAR(36) PRIMARY KEY COMMENT 'Unique execution identifier (UUID)',
    `workflow_id` VARCHAR(36) NOT NULL COMMENT 'Associated workflow ID',
    `status` ENUM('running', 'success', 'failed', 'cancelled') DEFAULT 'running' COMMENT 'Execution status',
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Execution start time',
    `finished_at` TIMESTAMP NULL COMMENT 'Execution finish time',
    `duration` INT COMMENT 'Execution duration in milliseconds',
    `input_data` LONGTEXT COMMENT 'JSON input data',
    `output_data` LONGTEXT COMMENT 'JSON output data',
    `logs` LONGTEXT COMMENT 'JSON execution logs',
    `error_message` TEXT COMMENT 'Error message if failed',
    FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
    INDEX `idx_workflow_id` (`workflow_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Workflow execution history';

-- ================================================
-- Table: webhooks
-- Webhook endpoints for workflow triggers
-- ================================================
CREATE TABLE IF NOT EXISTS `webhooks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Auto-incrementing webhook ID',
    `workflow_id` VARCHAR(36) NOT NULL COMMENT 'Associated workflow ID',
    `path` VARCHAR(255) UNIQUE NOT NULL COMMENT 'Unique webhook path/endpoint',
    `method` VARCHAR(10) DEFAULT 'POST' COMMENT 'HTTP method (GET, POST, etc.)',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Active status',
    `last_triggered_at` TIMESTAMP NULL COMMENT 'Last trigger timestamp',
    `trigger_count` INT DEFAULT 0 COMMENT 'Total trigger count',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
    FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
    INDEX `idx_path` (`path`),
    INDEX `idx_workflow_id` (`workflow_id`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Webhook endpoints';

-- ================================================
-- Table: node_results
-- Stores individual node execution results
-- ================================================
CREATE TABLE IF NOT EXISTS `node_results` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Auto-incrementing result ID',
    `execution_id` VARCHAR(36) NOT NULL COMMENT 'Associated execution ID',
    `node_id` VARCHAR(50) NOT NULL COMMENT 'Node identifier',
    `node_type` VARCHAR(50) NOT NULL COMMENT 'Node type',
    `status` ENUM('success', 'failed', 'skipped') DEFAULT 'success' COMMENT 'Node execution status',
    `input_data` LONGTEXT COMMENT 'JSON input data',
    `output_data` LONGTEXT COMMENT 'JSON output data',
    `error_message` TEXT COMMENT 'Error message if failed',
    `execution_time` INT COMMENT 'Execution time in milliseconds',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp',
    FOREIGN KEY (`execution_id`) REFERENCES `executions`(`id`) ON DELETE CASCADE,
    INDEX `idx_execution_id` (`execution_id`),
    INDEX `idx_node_type` (`node_type`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Node execution results';

-- ================================================
-- Table: scheduled_tasks
-- Scheduled workflow executions
-- ================================================
CREATE TABLE IF NOT EXISTS `scheduled_tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Auto-incrementing task ID',
    `workflow_id` VARCHAR(36) NOT NULL COMMENT 'Associated workflow ID',
    `cron_expression` VARCHAR(100) NOT NULL COMMENT 'Cron schedule expression',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Active status',
    `next_run` TIMESTAMP NULL COMMENT 'Next scheduled run',
    `last_run` TIMESTAMP NULL COMMENT 'Last execution time',
    `run_count` INT DEFAULT 0 COMMENT 'Total execution count',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
    FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
    INDEX `idx_workflow_id` (`workflow_id`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_next_run` (`next_run`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Scheduled workflow tasks';

-- ================================================
-- Table: api_credentials
-- Stores API keys and credentials securely
-- ================================================
CREATE TABLE IF NOT EXISTS `api_credentials` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Auto-incrementing credential ID',
    `name` VARCHAR(100) NOT NULL COMMENT 'Credential name',
    `type` VARCHAR(50) NOT NULL COMMENT 'Service type (ollama, openai, etc.)',
    `credentials` TEXT NOT NULL COMMENT 'Encrypted credentials JSON',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Active status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    INDEX `idx_type` (`type`),
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API credentials storage';

-- ================================================
-- Table: workflow_variables
-- Global and workflow-specific variables
-- ================================================
CREATE TABLE IF NOT EXISTS `workflow_variables` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Auto-incrementing variable ID',
    `workflow_id` VARCHAR(36) NULL COMMENT 'Workflow ID (NULL for global variables)',
    `variable_key` VARCHAR(100) NOT NULL COMMENT 'Variable key name',
    `variable_value` TEXT COMMENT 'Variable value',
    `variable_type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string' COMMENT 'Value type',
    `is_encrypted` TINYINT(1) DEFAULT 0 COMMENT 'Encryption status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    FOREIGN KEY (`workflow_id`) REFERENCES `workflows`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_workflow_key` (`workflow_id`, `variable_key`),
    INDEX `idx_workflow_id` (`workflow_id`),
    INDEX `idx_variable_key` (`variable_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Workflow variables';

-- ================================================
-- Insert Sample Data (Optional)
-- ================================================

-- Sample workflow for testing
INSERT INTO `workflows` (`id`, `name`, `description`, `data`, `is_active`) VALUES
('sample-workflow-001', 'Sample HTTP Request', 'Example workflow with HTTP request', 
'{"nodes":[{"id":"node_1","type":"http_request","name":"HTTP Request","icon":"🌐","x":100,"y":100,"config":{"url":"https://api.example.com","method":"GET"}}],"connections":[]}', 
1);

-- ================================================
-- Database User Setup (Optional)
-- Create dedicated database user with proper permissions
-- ================================================

-- Uncomment and modify the following lines to create a dedicated user:
-- CREATE USER IF NOT EXISTS 'n8n_user'@'localhost' IDENTIFIED BY 'secure_password_here';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON n8n_pro.* TO 'n8n_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ================================================
-- Performance Optimization
-- ================================================

-- Optimize tables
OPTIMIZE TABLE `workflows`;
OPTIMIZE TABLE `executions`;
OPTIMIZE TABLE `webhooks`;
OPTIMIZE TABLE `node_results`;
OPTIMIZE TABLE `scheduled_tasks`;

-- ================================================
-- Database Information
-- ================================================
SELECT 
    'Database created successfully!' as status,
    DATABASE() as database_name,
    VERSION() as mysql_version,
    @@character_set_database as charset,
    @@collation_database as collation;

-- Show all tables
SHOW TABLES;

-- Show table structures
SHOW CREATE TABLE workflows;
SHOW CREATE TABLE executions;
SHOW CREATE TABLE webhooks;

-- ================================================
-- Verification Queries
-- ================================================
SELECT COUNT(*) as total_workflows FROM workflows;
SELECT COUNT(*) as total_executions FROM executions;
SELECT COUNT(*) as total_webhooks FROM webhooks;

-- End of database setup script

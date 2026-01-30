<?php
/**
 * Workflows API
 * API لإدارة سير العمل
 */

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/workflow_engine.php';
require_once '../includes/node_registry.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$db = Database::getInstance();

try {
    switch ($method) {
        case 'GET':
            if ($path === '/' || $path === '') {
                // List all workflows
                listWorkflows($db);
            } elseif (preg_match('/^\/([a-f0-9\-]+)$/', $path, $matches)) {
                // Get single workflow
                getWorkflow($db, $matches[1]);
            } elseif (preg_match('/^\/([a-f0-9\-]+)\/executions$/', $path, $matches)) {
                // Get workflow executions
                getExecutions($db, $matches[1]);
            } else {
                sendError('Endpoint not found', 404);
            }
            break;
            
        case 'POST':
            if ($path === '/' || $path === '') {
                // Create workflow
                createWorkflow($db);
            } elseif ($path === '/execute') {
                // Execute workflow
                executeWorkflow();
            } else {
                sendError('Endpoint not found', 404);
            }
            break;
            
        case 'PUT':
            if (preg_match('/^\/([a-f0-9\-]+)$/', $path, $matches)) {
                // Update workflow
                updateWorkflow($db, $matches[1]);
            } else {
                sendError('Endpoint not found', 404);
            }
            break;
            
        case 'DELETE':
            if (preg_match('/^\/([a-f0-9\-]+)$/', $path, $matches)) {
                // Delete workflow
                deleteWorkflow($db, $matches[1]);
            } else {
                sendError('Endpoint not found', 404);
            }
            break;
            
        default:
            sendError('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}

/**
 * List all workflows
 */
function listWorkflows($db) {
    $sql = "SELECT id, name, description, is_active, created_at, updated_at 
            FROM workflows 
            ORDER BY updated_at DESC";
    $workflows = $db->query($sql);
    
    sendSuccess(['workflows' => $workflows]);
}

/**
 * Get single workflow
 */
function getWorkflow($db, $id) {
    $sql = "SELECT * FROM workflows WHERE id = ?";
    $result = $db->query($sql, [$id]);
    
    if (empty($result)) {
        sendError('Workflow not found', 404);
    }
    
    $workflow = $result[0];
    $workflow['data'] = json_decode($workflow['data'], true);
    
    sendSuccess($workflow);
}

/**
 * Create workflow
 */
function createWorkflow($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['name']) || !isset($input['nodes']) || !isset($input['connections'])) {
        sendError('Missing required fields', 400);
    }
    
    $id = generateUuid();
    $name = $input['name'];
    $description = $input['description'] ?? '';
    $data = json_encode([
        'id' => $id,
        'name' => $name,
        'description' => $description,
        'nodes' => $input['nodes'],
        'connections' => $input['connections']
    ]);
    
    $sql = "INSERT INTO workflows (id, name, description, data) VALUES (?, ?, ?, ?)";
    $db->execute($sql, [$id, $name, $description, $data]);
    
    sendSuccess(['id' => $id, 'message' => 'Workflow created successfully']);
}

/**
 * Update workflow
 */
function updateWorkflow($db, $id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['name']) || !isset($input['nodes']) || !isset($input['connections'])) {
        sendError('Missing required fields', 400);
    }
    
    $name = $input['name'];
    $description = $input['description'] ?? '';
    $data = json_encode([
        'id' => $id,
        'name' => $name,
        'description' => $description,
        'nodes' => $input['nodes'],
        'connections' => $input['connections']
    ]);
    
    $sql = "UPDATE workflows SET name = ?, description = ?, data = ? WHERE id = ?";
    $db->execute($sql, [$name, $description, $data, $id]);
    
    sendSuccess(['message' => 'Workflow updated successfully']);
}

/**
 * Delete workflow
 */
function deleteWorkflow($db, $id) {
    $sql = "DELETE FROM workflows WHERE id = ?";
    $db->execute($sql, [$id]);
    
    sendSuccess(['message' => 'Workflow deleted successfully']);
}

/**
 * Execute workflow
 */
function executeWorkflow() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['workflow_id'])) {
        sendError('workflow_id is required', 400);
    }
    
    $workflowId = $input['workflow_id'];
    $inputData = $input['input_data'] ?? [];
    
    $engine = new WorkflowEngine();
    $result = $engine->execute($workflowId, $inputData);
    
    sendSuccess($result);
}

/**
 * Get workflow executions
 */
function getExecutions($db, $workflowId) {
    $sql = "SELECT id, status, started_at, finished_at, duration, error_message 
            FROM executions 
            WHERE workflow_id = ? 
            ORDER BY started_at DESC 
            LIMIT 50";
    $executions = $db->query($sql, [$workflowId]);
    
    sendSuccess(['executions' => $executions]);
}

/**
 * Send success response
 */
function sendSuccess($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Send error response
 */
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Generate UUID
 */
function generateUuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

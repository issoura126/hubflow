<?php
/**
 * Webhook Handler
 * معالج طلبات Webhook
 */

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/workflow_engine.php';
require_once '../includes/node_registry.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$path = trim($path, '/');

if (empty($path)) {
    http_response_code(404);
    echo json_encode(['error' => 'Webhook path is required']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Get request body
    $body = file_get_contents('php://input');
    $data = json_decode($body, true) ?? [];
    
    // Get headers
    $headers = getallheaders();
    
    // Find workflows with matching webhook
    $sql = "SELECT w.id, w.data 
            FROM workflows w
            INNER JOIN webhooks wh ON w.id = wh.workflow_id
            WHERE wh.path = ? AND wh.is_active = 1 AND w.is_active = 1";
    $workflows = $db->query($sql, [$path]);
    
    if (empty($workflows)) {
        http_response_code(404);
        echo json_encode(['error' => 'No workflow found for this webhook path']);
        exit;
    }
    
    $results = [];
    $engine = new WorkflowEngine();
    
    foreach ($workflows as $workflow) {
        $workflowData = json_decode($workflow['data'], true);
        
        // Execute workflow with webhook data
        $inputData = [
            'webhook_data' => $data,
            'path' => $path,
            'method' => $method,
            'headers' => $headers
        ];
        
        $result = $engine->execute($workflow['id'], $inputData);
        
        $results[] = [
            'workflow_id' => $workflow['id'],
            'success' => $result['success'],
            'output' => $result['output'] ?? null,
            'execution_id' => $result['execution_id']
        ];
        
        // Update webhook stats
        $updateSql = "UPDATE webhooks 
                      SET last_triggered_at = NOW(), trigger_count = trigger_count + 1 
                      WHERE path = ?";
        $db->execute($updateSql, [$path]);
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'results' => $results
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

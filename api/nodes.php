<?php
/**
 * Nodes API
 * API للحصول على العقد المتاحة
 */

require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/node_registry.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $registry = new NodeRegistry();
    $nodes = $registry->getAllNodes();
    
    http_response_code(200);
    echo json_encode(['nodes' => $nodes], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

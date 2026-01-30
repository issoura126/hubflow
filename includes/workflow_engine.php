<?php
/**
 * Workflow Execution Engine
 * محرك تنفيذ سير العمل
 */

class WorkflowEngine {
    private $db;
    private $nodeRegistry;
    private $logs = [];
    private $nodeOutputs = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->nodeRegistry = new NodeRegistry();
    }
    
    /**
     * Execute a workflow
     */
    public function execute($workflowId, $inputData = []) {
        $executionId = $this->generateUuid();
        $startTime = microtime(true);
        
        try {
            // Get workflow data
            $workflow = $this->getWorkflow($workflowId);
            if (!$workflow) {
                throw new Exception("Workflow not found");
            }
            
            $workflowData = json_decode($workflow['data'], true);
            $nodes = $workflowData['nodes'] ?? [];
            $connections = $workflowData['connections'] ?? [];
            
            // Create execution record
            $this->createExecutionRecord($executionId, $workflowId, $inputData);
            
            $this->addLog('info', "Starting workflow: {$workflow['name']}");
            $this->addLog('info', "Number of nodes: " . count($nodes));
            
            // Build execution order
            $executionOrder = $this->buildExecutionOrder($nodes, $connections);
            
            // Execute nodes
            foreach ($executionOrder as $nodeId) {
                $node = $this->findNodeById($nodes, $nodeId);
                if (!$node) continue;
                
                $this->addLog('info', "Executing node: {$node['name']} ({$node['type']})");
                
                try {
                    // Get input data for this node
                    $nodeInput = $this->getNodeInput($nodeId, $connections, $inputData);
                    
                    // Execute node
                    $nodeInstance = $this->nodeRegistry->getNode($node['type']);
                    if (!$nodeInstance) {
                        throw new Exception("Unknown node type: {$node['type']}");
                    }
                    
                    $output = $nodeInstance->run($nodeInput, $node['config'] ?? []);
                    $this->nodeOutputs[$nodeId] = $output;
                    
                    $this->addLog('success', "تم Executing node: {$node['name']} ");
                    
                } catch (Exception $e) {
                    $this->addLog('error', "Error in node {$node['name']}: " . $e->getMessage());
                    throw $e;
                }
            }
            
            $duration = round((microtime(true) - $startTime) * 1000);
            $this->addLog('info', "Execution completed in {$duration}ms");
            
            // Update execution record
            $this->updateExecutionRecord($executionId, 'success', $this->nodeOutputs, $duration);
            
            return [
                'success' => true,
                'execution_id' => $executionId,
                'output' => $this->nodeOutputs,
                'logs' => $this->logs,
                'duration' => $duration
            ];
            
        } catch (Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            $this->addLog('error', "Execution failed: " . $e->getMessage());
            
            $this->updateExecutionRecord($executionId, 'failed', $this->nodeOutputs, $duration, $e->getMessage());
            
            return [
                'success' => false,
                'execution_id' => $executionId,
                'error' => $e->getMessage(),
                'logs' => $this->logs,
                'duration' => $duration
            ];
        }
    }
    
    /**
     * Build execution order using topological sort
     */
    private function buildExecutionOrder($nodes, $connections) {
        $graph = [];
        $inDegree = [];
        
        // Initialize graph
        foreach ($nodes as $node) {
            $graph[$node['id']] = [];
            $inDegree[$node['id']] = 0;
        }
        
        // Build adjacency list
        foreach ($connections as $conn) {
            $from = $conn['from'];
            $to = $conn['to'];
            $graph[$from][] = $to;
            $inDegree[$to]++;
        }
        
        // Find nodes with no incoming edges
        $queue = [];
        foreach ($inDegree as $nodeId => $degree) {
            if ($degree === 0) {
                $queue[] = $nodeId;
            }
        }
        
        // Topological sort
        $executionOrder = [];
        while (!empty($queue)) {
            $current = array_shift($queue);
            $executionOrder[] = $current;
            
            foreach ($graph[$current] as $neighbor) {
                $inDegree[$neighbor]--;
                if ($inDegree[$neighbor] === 0) {
                    $queue[] = $neighbor;
                }
            }
        }
        
        // Check for cycles
        if (count($executionOrder) !== count($nodes)) {
            // Return nodes in original order if cycle detected
            return array_column($nodes, 'id');
        }
        
        return $executionOrder;
    }
    
    /**
     * Get input data for a node
     */
    private function getNodeInput($nodeId, $connections, $initialInput) {
        // Find incoming connections
        $incoming = array_filter($connections, function($conn) use ($nodeId) {
            return $conn['to'] === $nodeId;
        });
        
        if (empty($incoming)) {
            return $initialInput;
        }
        
        // Merge outputs from incoming nodes
        $mergedInput = [];
        foreach ($incoming as $conn) {
            $fromNode = $conn['from'];
            if (isset($this->nodeOutputs[$fromNode])) {
                $output = $this->nodeOutputs[$fromNode];
                if (is_array($output)) {
                    $mergedInput = array_merge($mergedInput, $output);
                } else {
                    $mergedInput[$fromNode] = $output;
                }
            }
        }
        
        return $mergedInput;
    }
    
    /**
     * Find node by ID
     */
    private function findNodeById($nodes, $nodeId) {
        foreach ($nodes as $node) {
            if ($node['id'] === $nodeId) {
                return $node;
            }
        }
        return null;
    }
    
    /**
     * Add log entry
     */
    private function addLog($level, $message, $context = []) {
        $this->logs[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];
    }
    
    /**
     * Get workflow
     */
    private function getWorkflow($workflowId) {
        $sql = "SELECT * FROM workflows WHERE id = ? AND is_active = 1";
        $result = $this->db->query($sql, [$workflowId]);
        return $result[0] ?? null;
    }
    
    /**
     * Create execution record
     */
    private function createExecutionRecord($executionId, $workflowId, $inputData) {
        $sql = "INSERT INTO executions (id, workflow_id, status, input_data, logs) 
                VALUES (?, ?, 'running', ?, '[]')";
        $this->db->execute($sql, [
            $executionId,
            $workflowId,
            json_encode($inputData)
        ]);
    }
    
    /**
     * Update execution record
     */
    private function updateExecutionRecord($executionId, $status, $outputData, $duration, $error = null) {
        $sql = "UPDATE executions 
                SET status = ?, finished_at = NOW(), duration = ?, 
                    output_data = ?, logs = ?, error_message = ?
                WHERE id = ?";
        $this->db->execute($sql, [
            $status,
            $duration,
            json_encode($outputData),
            json_encode($this->logs),
            $error,
            $executionId
        ]);
    }
    
    /**
     * Generate UUID
     */
    private function generateUuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

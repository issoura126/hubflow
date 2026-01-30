<?php
/**
 * Node Registry
 * سجل العقد المتاحة
 */

class NodeRegistry {
    private $nodes = [];
    
    public function __construct() {
        $this->loadBuiltinNodes();
    }
    
    /**
     * Load all built-in nodes
     */
    private function loadBuiltinNodes() {
        $nodesDir = NODES_PATH;
        
        $builtinNodes = [
            // Triggers
            'webhook',
            'schedule',
            
            // Actions
            'http_request',
            'email_send',
            'file_write',
            'file_read',
            
            // Logic
            'condition',
            'delay',
            
            // Data Processing
            'transform',
            'json_parse',
            'mysql_query',
            'php_run',
            
            // AI Nodes (Ollama Integration)
            'ollama_ai',
            'ai_text_analyzer',
            'ai_image_analyzer',
            'ai_data_enrichment',
            'ai_code_generator'
        ];
        
        foreach ($builtinNodes as $nodeName) {
            $filePath = $nodesDir . '/' . $nodeName . '.php';
            if (file_exists($filePath)) {
                require_once $filePath;
                
                // Convert snake_case to PascalCase
                $className = str_replace('_', '', ucwords($nodeName, '_')) . 'Node';
                
                if (class_exists($className)) {
                    $this->registerNode($nodeName, $className);
                }
            }
        }
    }
    
    /**
     * Register a node type
     */
    public function registerNode($type, $className) {
        $this->nodes[$type] = $className;
    }
    
    /**
     * Get node instance
     */
    public function getNode($type) {
        if (!isset($this->nodes[$type])) {
            return null;
        }
        
        $className = $this->nodes[$type];
        return new $className();
    }
    
    /**
     * Get all registered nodes metadata
     */
    public function getAllNodes() {
        $result = [];
        
        foreach ($this->nodes as $type => $className) {
            try {
                $instance = new $className();
                $metadata = $instance->getMetadata();
                $result[$type] = $metadata;
            } catch (Exception $e) {
                $result[$type] = [
                    'name' => $type,
                    'description' => 'No description available',
                    'category' => 'general'
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Check if node type exists
     */
    public function hasNode($type) {
        return isset($this->nodes[$type]);
    }
}

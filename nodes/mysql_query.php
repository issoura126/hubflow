<?php
/**
 * MySQL Query Node
 * عقدة تنفيذ استعلامات MySQL
 */

class MysqlQueryNode {
    
    public function getMetadata() {
        return [
            'name' => 'MySQL Query',
            'description' => 'تنفيذ استعلامات قاعدة البيانات MySQL',
            'category' => 'database',
            'icon' => '🗄️',
            'inputs' => ['data'],
            'outputs' => ['result'],
            'config' => [
                [
                    'name' => 'operation',
                    'label' => 'نوع العملية',
                    'type' => 'select',
                    'options' => ['SELECT', 'INSERT', 'UPDATE', 'DELETE'],
                    'default' => 'SELECT',
                    'required' => true
                ],
                [
                    'name' => 'query',
                    'label' => 'الاستعلام (Query)',
                    'type' => 'code',
                    'placeholder' => 'SELECT * FROM table WHERE id = {{id}}',
                    'required' => true,
                    'description' => 'استعلام SQL (يمكن استخدام المتغيرات مثل {{field}})'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $operation = strtoupper($config['operation'] ?? 'SELECT');
        $query = $config['query'] ?? '';
        
        if (empty($query)) {
            throw new Exception("الاستعلام مطلوب");
        }
        
        // Replace variables in query
        $query = $this->replaceVariables($query, $data);
        
        // Get database connection
        $db = Database::getInstance();
        
        try {
            if ($operation === 'SELECT') {
                $result = $db->query($query);
                return [
                    'success' => true,
                    'rows' => $result,
                    'count' => count($result)
                ];
            } else {
                $db->execute($query);
                return [
                    'success' => true,
                    'affected_rows' => 1,
                    'operation' => $operation
                ];
            }
        } catch (Exception $e) {
            throw new Exception("خطأ في تنفيذ الاستعلام: " . $e->getMessage());
        }
    }
    
    private function replaceVariables($text, $data) {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
            $key = $matches[1];
            $value = $data[$key] ?? null;
            
            // Escape for SQL security
            if (is_string($value)) {
                return "'" . addslashes($value) . "'";
            } elseif (is_null($value)) {
                return 'NULL';
            } else {
                return $value;
            }
        }, $text);
    }
}

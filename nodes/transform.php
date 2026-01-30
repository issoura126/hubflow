<?php
/**
 * Transform Node
 * عقدة تحويل وتعديل البيانات
 */

class TransformNode {
    
    public function getMetadata() {
        return [
            'name' => 'Transform',
            'description' => 'تحويل وتعديل البيانات باستخدام قواعد مخصصة',
            'category' => 'data',
            'icon' => '⚙️',
            'inputs' => ['data'],
            'outputs' => ['transformed'],
            'config' => [
                [
                    'name' => 'mode',
                    'label' => 'نوع التحويل',
                    'type' => 'select',
                    'options' => ['map', 'filter', 'extract', 'merge', 'custom'],
                    'default' => 'map',
                    'required' => true
                ],
                [
                    'name' => 'mapping',
                    'label' => 'قواعد التحويل',
                    'type' => 'json',
                    'placeholder' => '{"new_field": "{{old_field}}"}',
                    'required' => false,
                    'description' => 'قواعد تعيين الحقول الجديدة'
                ],
                [
                    'name' => 'code',
                    'label' => 'كود PHP مخصص',
                    'type' => 'code',
                    'required' => false,
                    'description' => 'كود PHP للتحويل المخصص (يتوفر $data)'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $mode = $config['mode'] ?? 'map';
        
        switch ($mode) {
            case 'map':
                return $this->mapData($data, $config);
            case 'extract':
                return $this->extractFields($data, $config);
            case 'custom':
                return $this->customTransform($data, $config);
            default:
                return $data;
        }
    }
    
    private function mapData($data, $config) {
        $mapping = $this->parseJson($config['mapping'] ?? '{}');
        $result = [];
        
        foreach ($mapping as $newKey => $template) {
            $result[$newKey] = $this->replaceVariables($template, $data);
        }
        
        return $result;
    }
    
    private function extractFields($data, $config) {
        $fields = explode(',', $config['fields'] ?? '');
        $result = [];
        
        foreach ($fields as $field) {
            $field = trim($field);
            if (isset($data[$field])) {
                $result[$field] = $data[$field];
            }
        }
        
        return $result;
    }
    
    private function customTransform($data, $config) {
        $code = $config['code'] ?? '';
        
        if (empty($code)) {
            return $data;
        }
        
        // Execute custom PHP code (be careful with security!)
        try {
            $result = $data;
            eval($code);
            return $result;
        } catch (Exception $e) {
            throw new Exception("خطأ في تنفيذ الكود: " . $e->getMessage());
        }
    }
    
    private function parseJson($value) {
        if (is_array($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        return [];
    }
    
    private function replaceVariables($text, $data) {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
            $key = $matches[1];
            return $data[$key] ?? $matches[0];
        }, $text);
    }
}

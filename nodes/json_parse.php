<?php
/**
 * JSON Parse Node
 * عقدة تحليل JSON
 */

class JsonParseNode {
    
    public function getMetadata() {
        return [
            'name' => 'JSON Parse',
            'description' => 'تحليل وتحويل بيانات JSON',
            'category' => 'data',
            'icon' => '📋',
            'inputs' => ['data'],
            'outputs' => ['parsed'],
            'config' => [
                [
                    'name' => 'mode',
                    'label' => 'الوضع',
                    'type' => 'select',
                    'options' => ['parse', 'stringify', 'extract'],
                    'default' => 'parse',
                    'required' => true
                ],
                [
                    'name' => 'json_field',
                    'label' => 'حقل JSON',
                    'type' => 'string',
                    'placeholder' => 'data',
                    'required' => false,
                    'description' => 'اسم الحقل الذي يحتوي على JSON'
                ],
                [
                    'name' => 'path',
                    'label' => 'المسار (Extract Mode)',
                    'type' => 'string',
                    'placeholder' => 'user.name',
                    'required' => false,
                    'description' => 'مسار الحقل المراد استخراجه (مثال: user.name)'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $mode = $config['mode'] ?? 'parse';
        
        switch ($mode) {
            case 'parse':
                return $this->parseJson($data, $config);
                
            case 'stringify':
                return $this->stringifyJson($data);
                
            case 'extract':
                return $this->extractFromJson($data, $config);
                
            default:
                return $data;
        }
    }
    
    private function parseJson($data, $config) {
        $jsonField = $config['json_field'] ?? 'json';
        $jsonString = $data[$jsonField] ?? json_encode($data);
        
        if (is_array($jsonString) || is_object($jsonString)) {
            return $jsonString;
        }
        
        $parsed = json_decode($jsonString, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("خطأ في تحليل JSON: " . json_last_error_msg());
        }
        
        return is_array($parsed) ? $parsed : ['result' => $parsed];
    }
    
    private function stringifyJson($data) {
        return [
            'json' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'json_compact' => json_encode($data, JSON_UNESCAPED_UNICODE)
        ];
    }
    
    private function extractFromJson($data, $config) {
        $path = $config['path'] ?? '';
        
        if (empty($path)) {
            return $data;
        }
        
        // Split path by dots
        $keys = explode('.', $path);
        $result = $data;
        
        foreach ($keys as $key) {
            if (is_array($result) && isset($result[$key])) {
                $result = $result[$key];
            } elseif (is_object($result) && isset($result->$key)) {
                $result = $result->$key;
            } else {
                return ['error' => 'Path not found', 'path' => $path];
            }
        }
        
        return ['extracted' => $result, 'path' => $path];
    }
}

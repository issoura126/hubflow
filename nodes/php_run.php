<?php
/**
 * PHP Run Node
 * عقدة تنفيذ كود PHP
 */

class PhpRunNode {
    
    public function getMetadata() {
        return [
            'name' => 'PHP Run',
            'description' => 'تنفيذ كود PHP مخصص',
            'category' => 'code',
            'icon' => '🐘',
            'inputs' => ['data'],
            'outputs' => ['result'],
            'config' => [
                [
                    'name' => 'code',
                    'label' => 'كود PHP',
                    'type' => 'code',
                    'placeholder' => '$result = $data;' . "\n" . '$result["processed"] = true;' . "\n" . 'return $result;',
                    'required' => true,
                    'description' => 'كود PHP (يتوفر المتغير $data ويجب إرجاع $result)'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $code = $config['code'] ?? '';
        
        if (empty($code)) {
            throw new Exception("الكود مطلوب");
        }
        
        try {
            // Initialize result with input data
            $result = $data;
            
            // Execute the code
            // WARNING: eval() is dangerous! Use with caution in production
            eval($code);
            
            return is_array($result) ? $result : ['result' => $result];
            
        } catch (Exception $e) {
            throw new Exception("خطأ في تنفيذ الكود: " . $e->getMessage());
        } catch (ParseError $e) {
            throw new Exception("خطأ في صياغة الكود: " . $e->getMessage());
        }
    }
}

<?php
/**
 * Condition Node
 * عقدة الشروط المنطقية
 */

class ConditionNode {
    
    public function getMetadata() {
        return [
            'name' => 'Condition',
            'description' => 'تقييم الشروط المنطقية والتفرع حسب النتيجة',
            'category' => 'logic',
            'icon' => '🔀',
            'inputs' => ['data'],
            'outputs' => ['true', 'false'],
            'config' => [
                [
                    'name' => 'field',
                    'label' => 'الحقل',
                    'type' => 'string',
                    'placeholder' => 'status',
                    'required' => true,
                    'description' => 'اسم الحقل المراد فحصه'
                ],
                [
                    'name' => 'operator',
                    'label' => 'العملية',
                    'type' => 'select',
                    'options' => ['==', '!=', '>', '<', '>=', '<=', 'contains', 'startsWith', 'endsWith', 'isEmpty'],
                    'default' => '==',
                    'required' => true
                ],
                [
                    'name' => 'value',
                    'label' => 'القيمة',
                    'type' => 'string',
                    'required' => false,
                    'description' => 'القيمة المراد المقارنة بها'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $field = $config['field'] ?? '';
        $operator = $config['operator'] ?? '==';
        $value = $config['value'] ?? '';
        
        // Get field value from data
        $fieldValue = $data[$field] ?? null;
        
        // Evaluate condition
        $result = $this->evaluate($fieldValue, $operator, $value);
        
        return [
            'condition_result' => $result,
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'field_value' => $fieldValue,
            'output' => $result ? 'true' : 'false'
        ];
    }
    
    private function evaluate($fieldValue, $operator, $value) {
        switch ($operator) {
            case '==':
                return $fieldValue == $value;
            case '!=':
                return $fieldValue != $value;
            case '>':
                return $fieldValue > $value;
            case '<':
                return $fieldValue < $value;
            case '>=':
                return $fieldValue >= $value;
            case '<=':
                return $fieldValue <= $value;
            case 'contains':
                return strpos((string)$fieldValue, (string)$value) !== false;
            case 'startsWith':
                return strpos((string)$fieldValue, (string)$value) === 0;
            case 'endsWith':
                $length = strlen((string)$value);
                return substr((string)$fieldValue, -$length) === (string)$value;
            case 'isEmpty':
                return empty($fieldValue);
            default:
                return false;
        }
    }
}

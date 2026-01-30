<?php
/**
 * Delay Node
 * عقدة التأخير
 */

class DelayNode {
    
    public function getMetadata() {
        return [
            'name' => 'Delay',
            'description' => 'تأخير التنفيذ لفترة زمنية محددة',
            'category' => 'utility',
            'icon' => '⏱️',
            'inputs' => ['data'],
            'outputs' => ['data'],
            'config' => [
                [
                    'name' => 'duration',
                    'label' => 'المدة (ثانية)',
                    'type' => 'number',
                    'default' => 1,
                    'required' => true,
                    'description' => 'مدة التأخير بالثواني'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $duration = intval($config['duration'] ?? 1);
        
        if ($duration > 0 && $duration <= 300) { // Max 5 minutes
            sleep($duration);
        }
        
        return array_merge($data, [
            'delayed_by' => $duration,
            'delayed_at' => date('Y-m-d H:i:s')
        ]);
    }
}

<?php
/**
 * Schedule Node
 * عقدة الجدولة (Trigger)
 */

class ScheduleNode {
    
    public function getMetadata() {
        return [
            'name' => 'Schedule',
            'description' => 'تشغيل Workflow حسب جدول زمني',
            'category' => 'triggers',
            'icon' => '⏰',
            'inputs' => [],
            'outputs' => ['trigger'],
            'config' => [
                [
                    'name' => 'interval',
                    'label' => 'الفترة الزمنية',
                    'type' => 'select',
                    'options' => ['every_minute', 'every_5_minutes', 'every_15_minutes', 'every_30_minutes', 'hourly', 'daily', 'weekly', 'monthly', 'custom'],
                    'default' => 'hourly',
                    'required' => true
                ],
                [
                    'name' => 'cron_expression',
                    'label' => 'تعبير Cron (Custom)',
                    'type' => 'string',
                    'placeholder' => '0 */6 * * *',
                    'required' => false,
                    'description' => 'تعبير Cron عند اختيار Custom'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $interval = $config['interval'] ?? 'hourly';
        $cronExpression = $config['cron_expression'] ?? '';
        
        // Convert interval to cron
        $cron = $this->intervalToCron($interval, $cronExpression);
        
        return [
            'triggered' => true,
            'trigger_time' => date('Y-m-d H:i:s'),
            'interval' => $interval,
            'cron' => $cron,
            'next_run' => $this->getNextRunTime($cron)
        ];
    }
    
    private function intervalToCron($interval, $custom = '') {
        $crons = [
            'every_minute' => '* * * * *',
            'every_5_minutes' => '*/5 * * * *',
            'every_15_minutes' => '*/15 * * * *',
            'every_30_minutes' => '*/30 * * * *',
            'hourly' => '0 * * * *',
            'daily' => '0 0 * * *',
            'weekly' => '0 0 * * 0',
            'monthly' => '0 0 1 * *'
        ];
        
        if ($interval === 'custom' && !empty($custom)) {
            return $custom;
        }
        
        return $crons[$interval] ?? '0 * * * *';
    }
    
    private function getNextRunTime($cron) {
        // Simple next run calculation (for display purposes)
        // In production, use a proper cron parser library
        return date('Y-m-d H:i:s', strtotime('+1 hour'));
    }
}

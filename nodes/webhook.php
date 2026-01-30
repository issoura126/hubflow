<?php
/**
 * Webhook Node
 * عقدة استقبال البيانات من Webhooks
 */

class WebhookNode {
    
    public function getMetadata() {
        return [
            'name' => 'Webhook',
            'description' => 'استقبال البيانات من طلبات HTTP الخارجية',
            'category' => 'triggers',
            'icon' => '🔗',
            'inputs' => [],
            'outputs' => ['data'],
            'config' => [
                [
                    'name' => 'path',
                    'label' => 'مسار Webhook',
                    'type' => 'string',
                    'placeholder' => 'my-webhook',
                    'required' => true,
                    'description' => 'المسار الفريد للـ webhook (مثال: my-webhook)'
                ],
                [
                    'name' => 'method',
                    'label' => 'HTTP Method',
                    'type' => 'select',
                    'options' => ['POST', 'GET', 'PUT', 'DELETE'],
                    'default' => 'POST',
                    'required' => true
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        // Webhook node passes through received data
        return [
            'webhook_data' => $data['webhook_data'] ?? [],
            'path' => $data['path'] ?? '',
            'method' => $data['method'] ?? 'POST',
            'headers' => $data['headers'] ?? [],
            'received_at' => date('Y-m-d H:i:s')
        ];
    }
}

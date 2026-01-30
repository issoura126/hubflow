<?php
/**
 * Email Send Node
 * عقدة إرسال البريد الإلكتروني
 */

class EmailSendNode {
    
    public function getMetadata() {
        return [
            'name' => 'Email Send',
            'description' => 'إرسال رسائل البريد الإلكتروني',
            'category' => 'communication',
            'icon' => '📧',
            'inputs' => ['data'],
            'outputs' => ['result'],
            'config' => [
                [
                    'name' => 'to',
                    'label' => 'إلى (To)',
                    'type' => 'string',
                    'placeholder' => 'email@example.com',
                    'required' => true,
                    'description' => 'عنوان البريد الإلكتروني للمستلم'
                ],
                [
                    'name' => 'subject',
                    'label' => 'الموضوع (Subject)',
                    'type' => 'string',
                    'placeholder' => 'موضوع الرسالة',
                    'required' => true
                ],
                [
                    'name' => 'message',
                    'label' => 'الرسالة (Message)',
                    'type' => 'text',
                    'required' => true,
                    'description' => 'نص الرسالة'
                ],
                [
                    'name' => 'from',
                    'label' => 'من (From)',
                    'type' => 'string',
                    'placeholder' => 'noreply@example.com',
                    'required' => false
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $to = $config['to'] ?? '';
        $subject = $config['subject'] ?? '';
        $message = $config['message'] ?? '';
        $from = $config['from'] ?? 'noreply@localhost';
        
        if (empty($to) || empty($subject) || empty($message)) {
            throw new Exception("الحقول المطلوبة: To, Subject, Message");
        }
        
        // Replace variables
        $to = $this->replaceVariables($to, $data);
        $subject = $this->replaceVariables($subject, $data);
        $message = $this->replaceVariables($message, $data);
        
        // Set headers
        $headers = [
            'From: ' . $from,
            'Reply-To: ' . $from,
            'X-Mailer: Mini-n8n PHP',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        // Send email
        $success = mail($to, $subject, $message, implode("\r\n", $headers));
        
        if (!$success) {
            throw new Exception("فشل إرسال البريد الإلكتروني");
        }
        
        return [
            'success' => true,
            'to' => $to,
            'subject' => $subject,
            'sent_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function replaceVariables($text, $data) {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
            $key = $matches[1];
            return $data[$key] ?? $matches[0];
        }, $text);
    }
}

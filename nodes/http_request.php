<?php
/**
 * HTTP Request Node
 * عقدة إرسال طلبات HTTP
 */

class HttpRequestNode {
    
    public function getMetadata() {
        return [
            'name' => 'HTTP Request',
            'description' => 'إرسال طلبات HTTP إلى APIs خارجية',
            'category' => 'actions',
            'icon' => '🌐',
            'inputs' => ['data'],
            'outputs' => ['response'],
            'config' => [
                [
                    'name' => 'method',
                    'label' => 'طريقة الطلب',
                    'type' => 'select',
                    'options' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
                    'default' => 'GET',
                    'required' => true
                ],
                [
                    'name' => 'url',
                    'label' => 'URL',
                    'type' => 'string',
                    'placeholder' => 'https://api.example.com/endpoint',
                    'required' => true,
                    'description' => 'عنوان API المراد استدعاؤه'
                ],
                [
                    'name' => 'headers',
                    'label' => 'Headers',
                    'type' => 'json',
                    'placeholder' => '{"Content-Type": "application/json"}',
                    'required' => false,
                    'description' => 'رؤوس الطلب بصيغة JSON'
                ],
                [
                    'name' => 'body',
                    'label' => 'Body',
                    'type' => 'json',
                    'required' => false,
                    'description' => 'بيانات الطلب (POST, PUT, PATCH)'
                ],
                [
                    'name' => 'timeout',
                    'label' => 'Timeout',
                    'type' => 'number',
                    'default' => 30,
                    'description' => 'مهلة الطلب بالثواني'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $method = strtoupper($config['method'] ?? 'GET');
        $url = $config['url'] ?? '';
        $headers = $this->parseJson($config['headers'] ?? '{}');
        $body = $config['body'] ?? null;
        $timeout = intval($config['timeout'] ?? 30);
        
        if (empty($url)) {
            throw new Exception("URL مطلوب");
        }
        
        // Replace variables in URL
        $url = $this->replaceVariables($url, $data);
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        
        // Set method
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }
        
        // Set headers
        if (!empty($headers)) {
            $headerArray = [];
            foreach ($headers as $key => $value) {
                $headerArray[] = "$key: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        }
        
        // Set body
        if (!empty($body) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $bodyData = is_string($body) ? $body : json_encode($this->parseJson($body));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyData);
        }
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("خطأ في الطلب: $error");
        }
        
        // Try to parse JSON response
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $responseData = $response;
        }
        
        return [
            'status_code' => $httpCode,
            'body' => $responseData,
            'raw' => $response,
            'success' => $httpCode >= 200 && $httpCode < 300
        ];
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

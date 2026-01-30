<?php
/**
 * File Write Node
 * عقدة كتابة الملفات
 */

class FileWriteNode {
    
    public function getMetadata() {
        return [
            'name' => 'File Write',
            'description' => 'كتابة البيانات إلى ملف',
            'category' => 'files',
            'icon' => '💾',
            'inputs' => ['data'],
            'outputs' => ['result'],
            'config' => [
                [
                    'name' => 'file_path',
                    'label' => 'مسار الملف',
                    'type' => 'string',
                    'placeholder' => 'uploads/output.txt',
                    'required' => true,
                    'description' => 'المسار الكامل للملف المراد كتابته'
                ],
                [
                    'name' => 'content',
                    'label' => 'المحتوى',
                    'type' => 'text',
                    'placeholder' => 'النص المراد كتابته',
                    'required' => true
                ],
                [
                    'name' => 'mode',
                    'label' => 'وضع الكتابة',
                    'type' => 'select',
                    'options' => ['overwrite', 'append'],
                    'default' => 'overwrite',
                    'description' => 'overwrite: استبدال المحتوى، append: إضافة للمحتوى'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $filePath = $config['file_path'] ?? '';
        $content = $config['content'] ?? '';
        $mode = $config['mode'] ?? 'overwrite';
        
        if (empty($filePath) || empty($content)) {
            throw new Exception("مسار الملف والمحتوى مطلوبان");
        }
        
        // Replace variables
        $filePath = $this->replaceVariables($filePath, $data);
        $content = $this->replaceVariables($content, $data);
        
        // Security: Prevent directory traversal
        $filePath = str_replace(['../', '..\\'], '', $filePath);
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new Exception("فشل إنشاء المجلد: $directory");
            }
        }
        
        // Write to file
        $flags = ($mode === 'append') ? FILE_APPEND : 0;
        $result = file_put_contents($filePath, $content, $flags);
        
        if ($result === false) {
            throw new Exception("فشل كتابة الملف");
        }
        
        return [
            'success' => true,
            'file_path' => $filePath,
            'bytes_written' => $result,
            'mode' => $mode,
            'written_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function replaceVariables($text, $data) {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
            $key = $matches[1];
            return $data[$key] ?? $matches[0];
        }, $text);
    }
}

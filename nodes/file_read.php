<?php
/**
 * File Read Node
 * عقدة قراءة الملفات
 */

class FileReadNode {
    
    public function getMetadata() {
        return [
            'name' => 'File Read',
            'description' => 'قراءة محتوى الملفات',
            'category' => 'files',
            'icon' => '📂',
            'inputs' => ['data'],
            'outputs' => ['content'],
            'config' => [
                [
                    'name' => 'file_path',
                    'label' => 'مسار الملف',
                    'type' => 'string',
                    'placeholder' => 'uploads/file.txt',
                    'required' => true,
                    'description' => 'المسار الكامل أو النسبي للملف'
                ],
                [
                    'name' => 'encoding',
                    'label' => 'الترميز',
                    'type' => 'select',
                    'options' => ['utf-8', 'iso-8859-1', 'windows-1256'],
                    'default' => 'utf-8'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $filePath = $config['file_path'] ?? '';
        $encoding = $config['encoding'] ?? 'utf-8';
        
        if (empty($filePath)) {
            throw new Exception("مسار الملف مطلوب");
        }
        
        // Replace variables
        $filePath = $this->replaceVariables($filePath, $data);
        
        // Security: Prevent directory traversal
        $filePath = str_replace(['../', '..\\'], '', $filePath);
        
        // Check if file exists
        if (!file_exists($filePath)) {
            throw new Exception("الملف غير موجود: $filePath");
        }
        
        // Read file content
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            throw new Exception("فشل قراءة الملف");
        }
        
        // Convert encoding if needed
        if ($encoding !== 'utf-8') {
            $content = mb_convert_encoding($content, 'utf-8', $encoding);
        }
        
        // Detect file type
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileType = mime_content_type($filePath);
        
        return [
            'content' => $content,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'extension' => $extension,
            'size' => filesize($filePath),
            'modified_at' => date('Y-m-d H:i:s', filemtime($filePath))
        ];
    }
    
    private function replaceVariables($text, $data) {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
            $key = $matches[1];
            return $data[$key] ?? $matches[0];
        }, $text);
    }
}

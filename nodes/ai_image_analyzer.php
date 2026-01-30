<?php
/**
 * AI Image Analyzer Node
 * Image analysis using vision-capable Ollama models
 */

class AiImageAnalyzerNode {
    
    public function getMetadata() {
        return [
            'name' => 'AI Image Analyzer',
            'description' => 'Analyze images using vision-capable AI models (LLaVA, Bakllava)',
            'category' => 'ai',
            'icon' => '🖼️',
            'inputs' => ['image_path', 'image_url'],
            'outputs' => ['description', 'analysis'],
            'config' => [
                [
                    'name' => 'ollama_host',
                    'label' => 'Ollama Host',
                    'type' => 'string',
                    'default' => 'http://localhost:11434',
                    'required' => true
                ],
                [
                    'name' => 'model',
                    'label' => 'Vision Model',
                    'type' => 'select',
                    'options' => [
                        'llava' => 'LLaVA (Recommended)',
                        'llava:13b' => 'LLaVA 13B',
                        'llava:34b' => 'LLaVA 34B',
                        'bakllava' => 'Bakllava',
                        'llava-phi3' => 'LLaVA Phi-3',
                        'llava-llama3' => 'LLaVA Llama 3'
                    ],
                    'default' => 'llava',
                    'required' => true,
                    'description' => 'Vision-capable model for image analysis'
                ],
                [
                    'name' => 'image_source',
                    'label' => 'Image Source',
                    'type' => 'select',
                    'options' => [
                        'url' => 'Image URL',
                        'path' => 'Local File Path',
                        'base64' => 'Base64 Encoded'
                    ],
                    'default' => 'url',
                    'required' => true
                ],
                [
                    'name' => 'image_input',
                    'label' => 'Image Input',
                    'type' => 'textarea',
                    'placeholder' => 'URL, file path, or base64 data...',
                    'required' => true,
                    'description' => 'Image URL, local path, or base64 encoded data'
                ],
                [
                    'name' => 'analysis_prompt',
                    'label' => 'Analysis Prompt',
                    'type' => 'textarea',
                    'default' => 'Describe this image in detail.',
                    'placeholder' => 'What would you like to know about the image?',
                    'required' => true,
                    'description' => 'Question or instruction for image analysis'
                ],
                [
                    'name' => 'detailed_analysis',
                    'label' => 'Detailed Analysis',
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Enable comprehensive image analysis'
                ],
                [
                    'name' => 'detect_objects',
                    'label' => 'Detect Objects',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'List all detected objects'
                ],
                [
                    'name' => 'detect_text',
                    'label' => 'Extract Text (OCR)',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Extract any text visible in image'
                ],
                [
                    'name' => 'detect_colors',
                    'label' => 'Analyze Colors',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Analyze dominant colors'
                ],
                [
                    'name' => 'temperature',
                    'label' => 'Temperature',
                    'type' => 'number',
                    'default' => 0.3,
                    'min' => 0,
                    'max' => 1,
                    'step' => 0.1,
                    'description' => 'Creativity level for analysis'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $host = $config['ollama_host'] ?? 'http://localhost:11434';
        $model = $config['model'] ?? 'llava';
        $imageSource = $config['image_source'] ?? 'url';
        $imageInput = $this->replaceVariables($config['image_input'] ?? '', $data);
        $prompt = $config['analysis_prompt'] ?? 'Describe this image in detail.';
        
        if (empty($imageInput)) {
            throw new Exception("Image input is required");
        }
        
        // Get image data based on source type
        $imageData = $this->getImageData($imageInput, $imageSource);
        
        // Build comprehensive prompt
        $fullPrompt = $this->buildAnalysisPrompt($prompt, $config);
        
        // Call Ollama vision API
        $response = $this->analyzeImage($host, $model, $imageData, $fullPrompt, $config);
        
        return [
            'success' => true,
            'description' => $response,
            'model' => $model,
            'image_source' => $imageSource,
            'analysis_type' => $this->getAnalysisTypes($config),
            'timestamp' => date('c')
        ];
    }
    
    private function getImageData($input, $source) {
        switch ($source) {
            case 'url':
                return $this->downloadImageAsBase64($input);
            
            case 'path':
                if (!file_exists($input)) {
                    throw new Exception("Image file not found: $input");
                }
                return base64_encode(file_get_contents($input));
            
            case 'base64':
                // Remove data URI prefix if present
                if (preg_match('/^data:image\/\w+;base64,(.+)$/', $input, $matches)) {
                    return $matches[1];
                }
                return $input;
            
            default:
                throw new Exception("Invalid image source type: $source");
        }
    }
    
    private function downloadImageAsBase64($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$imageData) {
            throw new Exception("Failed to download image from URL");
        }
        
        return base64_encode($imageData);
    }
    
    private function buildAnalysisPrompt($basePrompt, $config) {
        $prompts = [$basePrompt];
        
        if (!empty($config['detect_objects'])) {
            $prompts[] = "List all objects you can identify in the image.";
        }
        
        if (!empty($config['detect_text'])) {
            $prompts[] = "Extract any text visible in the image.";
        }
        
        if (!empty($config['detect_colors'])) {
            $prompts[] = "Describe the dominant colors and color scheme.";
        }
        
        if (!empty($config['detailed_analysis'])) {
            $prompts[] = "Provide details about composition, lighting, mood, and style.";
        }
        
        return implode(" ", $prompts);
    }
    
    private function analyzeImage($host, $model, $imageBase64, $prompt, $config) {
        $ch = curl_init();
        
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'images' => [$imageBase64],
            'stream' => false,
            'options' => [
                'temperature' => floatval($config['temperature'] ?? 0.3),
                'num_predict' => 512
            ]
        ];
        
        curl_setopt($ch, CURLOPT_URL, "$host/api/generate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Vision models need more time
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Ollama API Error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Ollama API returned status code: $httpCode");
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse Ollama response");
        }
        
        return $data['response'] ?? '';
    }
    
    private function getAnalysisTypes($config) {
        $types = ['description'];
        
        if (!empty($config['detect_objects'])) $types[] = 'objects';
        if (!empty($config['detect_text'])) $types[] = 'ocr';
        if (!empty($config['detect_colors'])) $types[] = 'colors';
        if (!empty($config['detailed_analysis'])) $types[] = 'detailed';
        
        return $types;
    }
    
    private function replaceVariables($text, $data) {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
            return $data[$matches[1]] ?? $matches[0];
        }, $text);
    }
}

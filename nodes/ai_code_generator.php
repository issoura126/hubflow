<?php
/**
 * AI Code Generator Node
 * Generate code in multiple languages using AI
 */

class AiCodeGeneratorNode {
    
    public function getMetadata() {
        return [
            'name' => 'AI Code Generator',
            'description' => 'Generate, refactor, and debug code using AI',
            'category' => 'ai',
            'icon' => '💻',
            'inputs' => ['requirements'],
            'outputs' => ['code', 'documentation'],
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
                    'label' => 'Model',
                    'type' => 'select',
                    'options' => [
                        'codellama' => 'Code Llama (Recommended)',
                        'codellama:13b' => 'Code Llama 13B',
                        'codellama:34b' => 'Code Llama 34B',
                        'llama3.2' => 'Llama 3.2',
                        'deepseek-coder' => 'DeepSeek Coder',
                        'phind-codellama' => 'Phind Code Llama',
                        'wizardcoder' => 'WizardCoder',
                        'starcoder2' => 'StarCoder 2'
                    ],
                    'default' => 'codellama',
                    'required' => true
                ],
                [
                    'name' => 'operation',
                    'label' => 'Operation',
                    'type' => 'select',
                    'options' => [
                        'generate' => 'Generate Code',
                        'refactor' => 'Refactor Code',
                        'debug' => 'Debug Code',
                        'explain' => 'Explain Code',
                        'optimize' => 'Optimize Code',
                        'test' => 'Generate Tests',
                        'document' => 'Generate Documentation',
                        'convert' => 'Convert Language'
                    ],
                    'default' => 'generate',
                    'required' => true
                ],
                [
                    'name' => 'programming_language',
                    'label' => 'Programming Language',
                    'type' => 'select',
                    'options' => [
                        'python' => 'Python',
                        'javascript' => 'JavaScript',
                        'typescript' => 'TypeScript',
                        'java' => 'Java',
                        'csharp' => 'C#',
                        'cpp' => 'C++',
                        'go' => 'Go',
                        'rust' => 'Rust',
                        'php' => 'PHP',
                        'ruby' => 'Ruby',
                        'swift' => 'Swift',
                        'kotlin' => 'Kotlin',
                        'scala' => 'Scala',
                        'r' => 'R',
                        'sql' => 'SQL',
                        'bash' => 'Bash/Shell'
                    ],
                    'default' => 'python',
                    'required' => true
                ],
                [
                    'name' => 'input_text',
                    'label' => 'Input Text',
                    'type' => 'textarea',
                    'placeholder' => 'Requirements, existing code, or instructions...',
                    'required' => true,
                    'description' => 'Code requirements or existing code to work with'
                ],
                [
                    'name' => 'target_language',
                    'label' => 'Target Language (for conversion)',
                    'type' => 'select',
                    'options' => [
                        'python' => 'Python',
                        'javascript' => 'JavaScript',
                        'typescript' => 'TypeScript',
                        'java' => 'Java',
                        'go' => 'Go',
                        'rust' => 'Rust'
                    ],
                    'default' => 'javascript',
                    'required' => false
                ],
                [
                    'name' => 'code_style',
                    'label' => 'Code Style',
                    'type' => 'select',
                    'options' => [
                        'clean' => 'Clean Code',
                        'functional' => 'Functional',
                        'oop' => 'Object-Oriented',
                        'minimal' => 'Minimal',
                        'verbose' => 'Verbose with Comments'
                    ],
                    'default' => 'clean',
                    'description' => 'Preferred coding style'
                ],
                [
                    'name' => 'include_comments',
                    'label' => 'Include Comments',
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Add explanatory comments'
                ],
                [
                    'name' => 'include_tests',
                    'label' => 'Include Unit Tests',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Generate unit tests with code'
                ],
                [
                    'name' => 'framework',
                    'label' => 'Framework/Library',
                    'type' => 'string',
                    'placeholder' => 'React, FastAPI, Spring Boot...',
                    'required' => false,
                    'description' => 'Specific framework to use'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $host = $config['ollama_host'] ?? 'http://localhost:11434';
        $model = $config['model'] ?? 'codellama';
        $operation = $config['operation'] ?? 'generate';
        $language = $config['programming_language'] ?? 'python';
        $inputText = $this->replaceVariables($config['input_text'] ?? '', $data);
        
        if (empty($inputText)) {
            throw new Exception("Input text is required");
        }
        
        $result = $this->performOperation($host, $model, $operation, $language, $inputText, $config);
        
        return [
            'success' => true,
            'operation' => $operation,
            'language' => $language,
            'code' => $result['code'] ?? '',
            'explanation' => $result['explanation'] ?? '',
            'tests' => $result['tests'] ?? '',
            'documentation' => $result['documentation'] ?? '',
            'timestamp' => date('c')
        ];
    }
    
    private function performOperation($host, $model, $operation, $language, $input, $config) {
        switch ($operation) {
            case 'generate':
                return $this->generateCode($host, $model, $language, $input, $config);
            
            case 'refactor':
                return $this->refactorCode($host, $model, $language, $input);
            
            case 'debug':
                return $this->debugCode($host, $model, $language, $input);
            
            case 'explain':
                return $this->explainCode($host, $model, $input);
            
            case 'optimize':
                return $this->optimizeCode($host, $model, $language, $input);
            
            case 'test':
                return $this->generateTests($host, $model, $language, $input);
            
            case 'document':
                return $this->generateDocumentation($host, $model, $input);
            
            case 'convert':
                $targetLang = $config['target_language'] ?? 'javascript';
                return $this->convertLanguage($host, $model, $language, $targetLang, $input);
            
            default:
                throw new Exception("Unknown operation: $operation");
        }
    }
    
    private function generateCode($host, $model, $language, $requirements, $config) {
        $style = $config['code_style'] ?? 'clean';
        $includeComments = $config['include_comments'] ?? true;
        $includeTests = $config['include_tests'] ?? false;
        $framework = $config['framework'] ?? '';
        
        $prompt = "Generate $language code for the following requirements:\n\n$requirements\n\n";
        $prompt .= "Code style: $style\n";
        $prompt .= $includeComments ? "Include explanatory comments.\n" : "Minimal comments only.\n";
        
        if ($framework) {
            $prompt .= "Use $framework framework.\n";
        }
        
        $prompt .= "\nProvide clean, production-ready code only. No explanations outside code comments.";
        
        $code = $this->callOllama($host, $model, $prompt);
        $code = $this->extractCode($code);
        
        $result = ['code' => $code];
        
        if ($includeTests) {
            $testPrompt = "Generate unit tests for this $language code:\n\n$code";
            $tests = $this->callOllama($host, $model, $testPrompt);
            $result['tests'] = $this->extractCode($tests);
        }
        
        return $result;
    }
    
    private function refactorCode($host, $model, $language, $code) {
        $prompt = "Refactor this $language code to improve readability, performance, and maintainability:\n\n$code\n\nProvide only the refactored code.";
        
        $refactored = $this->callOllama($host, $model, $prompt);
        
        return [
            'code' => $this->extractCode($refactored),
            'explanation' => 'Code has been refactored for better structure and maintainability'
        ];
    }
    
    private function debugCode($host, $model, $language, $code) {
        $prompt = "Debug this $language code and fix any issues:\n\n$code\n\nProvide:\n1. List of issues found\n2. Fixed code\n3. Explanation of fixes";
        
        $response = $this->callOllama($host, $model, $prompt);
        
        return [
            'code' => $this->extractCode($response),
            'explanation' => $response
        ];
    }
    
    private function explainCode($host, $model, $code) {
        $prompt = "Explain this code in detail, including:\n1. What it does\n2. How it works\n3. Key components\n4. Potential improvements\n\nCode:\n$code";
        
        $explanation = $this->callOllama($host, $model, $prompt);
        
        return [
            'code' => $code,
            'explanation' => $explanation
        ];
    }
    
    private function optimizeCode($host, $model, $language, $code) {
        $prompt = "Optimize this $language code for better performance and efficiency:\n\n$code\n\nProvide optimized code with comments explaining improvements.";
        
        $optimized = $this->callOllama($host, $model, $prompt);
        
        return [
            'code' => $this->extractCode($optimized),
            'explanation' => 'Code optimized for better performance'
        ];
    }
    
    private function generateTests($host, $model, $language, $code) {
        $prompt = "Generate comprehensive unit tests for this $language code:\n\n$code\n\nInclude edge cases and error handling tests.";
        
        $tests = $this->callOllama($host, $model, $prompt);
        
        return [
            'code' => $code,
            'tests' => $this->extractCode($tests)
        ];
    }
    
    private function generateDocumentation($host, $model, $code) {
        $prompt = "Generate comprehensive documentation for this code:\n\n$code\n\nInclude:\n1. Overview\n2. Functions/Methods description\n3. Parameters and return values\n4. Usage examples\n5. Notes and warnings";
        
        $documentation = $this->callOllama($host, $model, $prompt);
        
        return [
            'code' => $code,
            'documentation' => $documentation
        ];
    }
    
    private function convertLanguage($host, $model, $fromLang, $toLang, $code) {
        $prompt = "Convert this $fromLang code to $toLang:\n\n$code\n\nMaintain the same functionality and logic. Provide only the converted code.";
        
        $converted = $this->callOllama($host, $model, $prompt);
        
        return [
            'code' => $this->extractCode($converted),
            'explanation' => "Converted from $fromLang to $toLang"
        ];
    }
    
    private function callOllama($host, $model, $prompt) {
        $ch = curl_init();
        
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0.2, // Low for code generation
                'num_predict' => 2048
            ]
        ];
        
        curl_setopt($ch, CURLOPT_URL, "$host/api/generate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Ollama API error: HTTP $httpCode");
        }
        
        $data = json_decode($response, true);
        return $data['response'] ?? '';
    }
    
    private function extractCode($text) {
        // Try to extract code from markdown code blocks
        if (preg_match('/```(?:\w+)?\n(.*?)\n```/s', $text, $matches)) {
            return trim($matches[1]);
        }
        
        // If no code block found, return as is
        return trim($text);
    }
    
    private function replaceVariables($text, $data) {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
            return $data[$matches[1]] ?? $matches[0];
        }, $text);
    }
}

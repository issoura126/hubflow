<?php
/**
 * Ollama AI Node
 * Local AI Integration with Ollama
 * Supports multiple models, streaming, embeddings, and advanced features
 */

class OllamaAiNode {
    
    private $defaultHost = 'http://localhost:11434';
    
    public function getMetadata() {
        return [
            'name' => 'Ollama AI',
            'description' => 'Local AI integration with Ollama - Chat, Completions, Embeddings, and more',
            'category' => 'ai',
            'icon' => '🤖',
            'inputs' => ['prompt', 'context'],
            'outputs' => ['response', 'metadata'],
            'config' => [
                [
                    'name' => 'operation',
                    'label' => 'Operation',
                    'type' => 'select',
                    'options' => [
                        'chat' => 'Chat Completion',
                        'generate' => 'Text Generation',
                        'embeddings' => 'Generate Embeddings',
                        'list_models' => 'List Available Models',
                        'pull_model' => 'Pull Model',
                        'create_model' => 'Create Custom Model'
                    ],
                    'default' => 'chat',
                    'required' => true,
                    'description' => 'Select the Ollama operation to perform'
                ],
                [
                    'name' => 'ollama_host',
                    'label' => 'Ollama Host',
                    'type' => 'string',
                    'default' => 'http://localhost:11434',
                    'placeholder' => 'http://localhost:11434',
                    'required' => true,
                    'description' => 'Ollama server address'
                ],
                [
                    'name' => 'model',
                    'label' => 'Model Name',
                    'type' => 'select',
                    'options' => [
                        'llama3.2' => 'Llama 3.2 (Latest)',
                        'llama3.1' => 'Llama 3.1',
                        'llama2' => 'Llama 2',
                        'mistral' => 'Mistral',
                        'mixtral' => 'Mixtral',
                        'codellama' => 'Code Llama',
                        'phi' => 'Phi',
                        'neural-chat' => 'Neural Chat',
                        'starling-lm' => 'Starling',
                        'orca-mini' => 'Orca Mini',
                        'vicuna' => 'Vicuna',
                        'gemma' => 'Gemma',
                        'qwen' => 'Qwen',
                        'custom' => 'Custom Model'
                    ],
                    'default' => 'llama3.2',
                    'required' => true,
                    'description' => 'AI model to use'
                ],
                [
                    'name' => 'custom_model',
                    'label' => 'Custom Model Name',
                    'type' => 'string',
                    'placeholder' => 'model:tag',
                    'required' => false,
                    'description' => 'Custom model name (used when model is set to "custom")'
                ],
                [
                    'name' => 'prompt',
                    'label' => 'Prompt/Message',
                    'type' => 'textarea',
                    'placeholder' => 'Enter your prompt or message here...',
                    'required' => true,
                    'description' => 'The prompt or message to send to the AI'
                ],
                [
                    'name' => 'system_prompt',
                    'label' => 'System Prompt',
                    'type' => 'textarea',
                    'placeholder' => 'You are a helpful assistant...',
                    'required' => false,
                    'description' => 'System instructions for the AI (for chat mode)'
                ],
                [
                    'name' => 'temperature',
                    'label' => 'Temperature',
                    'type' => 'number',
                    'default' => 0.7,
                    'min' => 0,
                    'max' => 2,
                    'step' => 0.1,
                    'description' => 'Creativity level (0=focused, 2=creative)'
                ],
                [
                    'name' => 'top_p',
                    'label' => 'Top P',
                    'type' => 'number',
                    'default' => 0.9,
                    'min' => 0,
                    'max' => 1,
                    'step' => 0.1,
                    'description' => 'Nucleus sampling threshold'
                ],
                [
                    'name' => 'top_k',
                    'label' => 'Top K',
                    'type' => 'number',
                    'default' => 40,
                    'min' => 1,
                    'max' => 100,
                    'description' => 'Limit token selection to top K tokens'
                ],
                [
                    'name' => 'max_tokens',
                    'label' => 'Max Tokens',
                    'type' => 'number',
                    'default' => 2048,
                    'min' => 1,
                    'max' => 32768,
                    'description' => 'Maximum number of tokens to generate'
                ],
                [
                    'name' => 'stream',
                    'label' => 'Enable Streaming',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Stream responses in real-time'
                ],
                [
                    'name' => 'context_window',
                    'label' => 'Context Window',
                    'type' => 'number',
                    'default' => 4096,
                    'min' => 512,
                    'max' => 32768,
                    'description' => 'Context window size'
                ],
                [
                    'name' => 'repeat_penalty',
                    'label' => 'Repeat Penalty',
                    'type' => 'number',
                    'default' => 1.1,
                    'min' => 0,
                    'max' => 2,
                    'step' => 0.1,
                    'description' => 'Penalty for repeating tokens'
                ],
                [
                    'name' => 'stop_sequences',
                    'label' => 'Stop Sequences',
                    'type' => 'string',
                    'placeholder' => 'stop1,stop2',
                    'required' => false,
                    'description' => 'Comma-separated stop sequences'
                ],
                [
                    'name' => 'keep_alive',
                    'label' => 'Keep Alive',
                    'type' => 'string',
                    'default' => '5m',
                    'placeholder' => '5m',
                    'description' => 'How long to keep model in memory (e.g., 5m, 1h)'
                ],
                [
                    'name' => 'use_mirostat',
                    'label' => 'Use Mirostat',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Enable Mirostat sampling'
                ],
                [
                    'name' => 'seed',
                    'label' => 'Random Seed',
                    'type' => 'number',
                    'placeholder' => '42',
                    'required' => false,
                    'description' => 'Random seed for reproducibility'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $operation = $config['operation'] ?? 'chat';
        
        switch ($operation) {
            case 'chat':
                return $this->chatCompletion($data, $config);
            case 'generate':
                return $this->generateText($data, $config);
            case 'embeddings':
                return $this->generateEmbeddings($data, $config);
            case 'list_models':
                return $this->listModels($config);
            case 'pull_model':
                return $this->pullModel($config);
            case 'create_model':
                return $this->createModel($data, $config);
            default:
                throw new Exception("Unknown operation: $operation");
        }
    }
    
    /**
     * Chat Completion
     */
    private function chatCompletion($data, $config) {
        $host = $config['ollama_host'] ?? $this->defaultHost;
        $model = $this->getModelName($config);
        $prompt = $this->replaceVariables($config['prompt'] ?? '', $data);
        $systemPrompt = $config['system_prompt'] ?? '';
        
        if (empty($prompt)) {
            throw new Exception("Prompt is required");
        }
        
        $messages = [];
        
        // Add system message if provided
        if (!empty($systemPrompt)) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }
        
        // Add user message
        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];
        
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'stream' => $config['stream'] ?? false,
            'options' => $this->buildOptions($config)
        ];
        
        if (!empty($config['keep_alive'])) {
            $payload['keep_alive'] = $config['keep_alive'];
        }
        
        $response = $this->makeRequest("$host/api/chat", $payload);
        
        return [
            'success' => true,
            'response' => $response['message']['content'] ?? '',
            'model' => $model,
            'created_at' => $response['created_at'] ?? date('c'),
            'total_duration' => $response['total_duration'] ?? 0,
            'load_duration' => $response['load_duration'] ?? 0,
            'prompt_eval_count' => $response['prompt_eval_count'] ?? 0,
            'eval_count' => $response['eval_count'] ?? 0,
            'raw_response' => $response
        ];
    }
    
    /**
     * Generate Text (non-chat mode)
     */
    private function generateText($data, $config) {
        $host = $config['ollama_host'] ?? $this->defaultHost;
        $model = $this->getModelName($config);
        $prompt = $this->replaceVariables($config['prompt'] ?? '', $data);
        
        if (empty($prompt)) {
            throw new Exception("Prompt is required");
        }
        
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => $config['stream'] ?? false,
            'options' => $this->buildOptions($config)
        ];
        
        if (!empty($config['system_prompt'])) {
            $payload['system'] = $config['system_prompt'];
        }
        
        if (!empty($config['keep_alive'])) {
            $payload['keep_alive'] = $config['keep_alive'];
        }
        
        $response = $this->makeRequest("$host/api/generate", $payload);
        
        return [
            'success' => true,
            'response' => $response['response'] ?? '',
            'model' => $model,
            'created_at' => $response['created_at'] ?? date('c'),
            'context' => $response['context'] ?? [],
            'total_duration' => $response['total_duration'] ?? 0,
            'load_duration' => $response['load_duration'] ?? 0,
            'prompt_eval_count' => $response['prompt_eval_count'] ?? 0,
            'eval_count' => $response['eval_count'] ?? 0,
            'raw_response' => $response
        ];
    }
    
    /**
     * Generate Embeddings
     */
    private function generateEmbeddings($data, $config) {
        $host = $config['ollama_host'] ?? $this->defaultHost;
        $model = $this->getModelName($config);
        $prompt = $this->replaceVariables($config['prompt'] ?? '', $data);
        
        if (empty($prompt)) {
            throw new Exception("Text is required for embeddings");
        }
        
        $payload = [
            'model' => $model,
            'prompt' => $prompt
        ];
        
        if (!empty($config['keep_alive'])) {
            $payload['keep_alive'] = $config['keep_alive'];
        }
        
        $response = $this->makeRequest("$host/api/embeddings", $payload);
        
        return [
            'success' => true,
            'embedding' => $response['embedding'] ?? [],
            'embedding_length' => count($response['embedding'] ?? []),
            'model' => $model,
            'raw_response' => $response
        ];
    }
    
    /**
     * List Available Models
     */
    private function listModels($config) {
        $host = $config['ollama_host'] ?? $this->defaultHost;
        
        $response = $this->makeRequest("$host/api/tags", [], 'GET');
        
        return [
            'success' => true,
            'models' => $response['models'] ?? [],
            'count' => count($response['models'] ?? [])
        ];
    }
    
    /**
     * Pull Model from Registry
     */
    private function pullModel($config) {
        $host = $config['ollama_host'] ?? $this->defaultHost;
        $model = $this->getModelName($config);
        
        $payload = [
            'name' => $model,
            'stream' => false
        ];
        
        $response = $this->makeRequest("$host/api/pull", $payload);
        
        return [
            'success' => true,
            'status' => $response['status'] ?? 'completed',
            'model' => $model
        ];
    }
    
    /**
     * Create Custom Model
     */
    private function createModel($data, $config) {
        $host = $config['ollama_host'] ?? $this->defaultHost;
        $modelName = $config['custom_model'] ?? 'custom-model';
        $modelfile = $config['prompt'] ?? ''; // Use prompt field as modelfile content
        
        if (empty($modelfile)) {
            throw new Exception("Modelfile content is required");
        }
        
        $payload = [
            'name' => $modelName,
            'modelfile' => $modelfile,
            'stream' => false
        ];
        
        $response = $this->makeRequest("$host/api/create", $payload);
        
        return [
            'success' => true,
            'status' => $response['status'] ?? 'success',
            'model' => $modelName
        ];
    }
    
    /**
     * Build Options Array
     */
    private function buildOptions($config) {
        $options = [];
        
        if (isset($config['temperature'])) {
            $options['temperature'] = floatval($config['temperature']);
        }
        
        if (isset($config['top_p'])) {
            $options['top_p'] = floatval($config['top_p']);
        }
        
        if (isset($config['top_k'])) {
            $options['top_k'] = intval($config['top_k']);
        }
        
        if (isset($config['max_tokens'])) {
            $options['num_predict'] = intval($config['max_tokens']);
        }
        
        if (isset($config['context_window'])) {
            $options['num_ctx'] = intval($config['context_window']);
        }
        
        if (isset($config['repeat_penalty'])) {
            $options['repeat_penalty'] = floatval($config['repeat_penalty']);
        }
        
        if (isset($config['seed']) && !empty($config['seed'])) {
            $options['seed'] = intval($config['seed']);
        }
        
        if (!empty($config['stop_sequences'])) {
            $stops = array_map('trim', explode(',', $config['stop_sequences']));
            $options['stop'] = $stops;
        }
        
        if (!empty($config['use_mirostat'])) {
            $options['mirostat'] = 1;
            $options['mirostat_tau'] = 5.0;
            $options['mirostat_eta'] = 0.1;
        }
        
        return $options;
    }
    
    /**
     * Get Model Name
     */
    private function getModelName($config) {
        $model = $config['model'] ?? 'llama3.2';
        
        if ($model === 'custom' && !empty($config['custom_model'])) {
            return $config['custom_model'];
        }
        
        return $model;
    }
    
    /**
     * Make HTTP Request to Ollama
     */
    private function makeRequest($url, $payload = [], $method = 'POST') {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes for AI operations
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
        }
        
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
            throw new Exception("Failed to parse Ollama response: " . json_last_error_msg());
        }
        
        return $data;
    }
    
    /**
     * Replace Variables in Text
     */
    private function replaceVariables($text, $data) {
        if (!is_string($text)) {
            return $text;
        }
        
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
            $key = $matches[1];
            return $data[$key] ?? $matches[0];
        }, $text);
    }
}

<?php
/**
 * AI Text Analyzer Node
 * Advanced text analysis using Ollama AI
 */

class AiTextAnalyzerNode {
    
    public function getMetadata() {
        return [
            'name' => 'AI Text Analyzer',
            'description' => 'Analyze text with AI: sentiment, entities, keywords, summary',
            'category' => 'ai',
            'icon' => '🔍',
            'inputs' => ['text'],
            'outputs' => ['analysis'],
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
                        'llama3.2' => 'Llama 3.2',
                        'llama3.1' => 'Llama 3.1',
                        'mistral' => 'Mistral',
                        'phi' => 'Phi'
                    ],
                    'default' => 'llama3.2',
                    'required' => true
                ],
                [
                    'name' => 'text',
                    'label' => 'Text to Analyze',
                    'type' => 'textarea',
                    'placeholder' => 'Enter text to analyze...',
                    'required' => true
                ],
                [
                    'name' => 'analysis_type',
                    'label' => 'Analysis Type',
                    'type' => 'multiselect',
                    'options' => [
                        'sentiment' => 'Sentiment Analysis',
                        'entities' => 'Entity Extraction',
                        'keywords' => 'Keyword Extraction',
                        'summary' => 'Text Summary',
                        'topics' => 'Topic Modeling',
                        'language' => 'Language Detection',
                        'classification' => 'Text Classification'
                    ],
                    'default' => ['sentiment', 'summary'],
                    'description' => 'Select analysis types to perform'
                ],
                [
                    'name' => 'custom_prompt',
                    'label' => 'Custom Analysis Prompt',
                    'type' => 'textarea',
                    'placeholder' => 'Optional: Add custom analysis instructions',
                    'required' => false
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $host = $config['ollama_host'] ?? 'http://localhost:11434';
        $model = $config['model'] ?? 'llama3.2';
        $text = $this->replaceVariables($config['text'] ?? '', $data);
        $analysisTypes = $config['analysis_type'] ?? ['sentiment', 'summary'];
        
        if (empty($text)) {
            throw new Exception("Text is required for analysis");
        }
        
        $results = [];
        
        // Perform each analysis type
        if (in_array('sentiment', $analysisTypes)) {
            $results['sentiment'] = $this->analyzeSentiment($host, $model, $text);
        }
        
        if (in_array('entities', $analysisTypes)) {
            $results['entities'] = $this->extractEntities($host, $model, $text);
        }
        
        if (in_array('keywords', $analysisTypes)) {
            $results['keywords'] = $this->extractKeywords($host, $model, $text);
        }
        
        if (in_array('summary', $analysisTypes)) {
            $results['summary'] = $this->generateSummary($host, $model, $text);
        }
        
        if (in_array('topics', $analysisTypes)) {
            $results['topics'] = $this->extractTopics($host, $model, $text);
        }
        
        if (in_array('language', $analysisTypes)) {
            $results['language'] = $this->detectLanguage($host, $model, $text);
        }
        
        if (in_array('classification', $analysisTypes)) {
            $results['classification'] = $this->classifyText($host, $model, $text);
        }
        
        // Custom analysis if provided
        if (!empty($config['custom_prompt'])) {
            $results['custom_analysis'] = $this->customAnalysis($host, $model, $text, $config['custom_prompt']);
        }
        
        return [
            'success' => true,
            'text_length' => strlen($text),
            'word_count' => str_word_count($text),
            'analysis' => $results,
            'timestamp' => date('c')
        ];
    }
    
    private function analyzeSentiment($host, $model, $text) {
        $prompt = "Analyze the sentiment of the following text. Respond with ONLY a JSON object containing: sentiment (positive/negative/neutral), score (0-1), and confidence (0-1).\n\nText: $text";
        
        $response = $this->callOllama($host, $model, $prompt);
        return $this->parseJsonResponse($response, [
            'sentiment' => 'neutral',
            'score' => 0.5,
            'confidence' => 0.5
        ]);
    }
    
    private function extractEntities($host, $model, $text) {
        $prompt = "Extract named entities from the text. Respond with ONLY a JSON object containing: persons (array), organizations (array), locations (array), dates (array), other (array).\n\nText: $text";
        
        $response = $this->callOllama($host, $model, $prompt);
        return $this->parseJsonResponse($response, [
            'persons' => [],
            'organizations' => [],
            'locations' => [],
            'dates' => [],
            'other' => []
        ]);
    }
    
    private function extractKeywords($host, $model, $text) {
        $prompt = "Extract the top 10 most important keywords from the text. Respond with ONLY a JSON array of strings.\n\nText: $text";
        
        $response = $this->callOllama($host, $model, $prompt);
        $parsed = $this->parseJsonResponse($response, []);
        return is_array($parsed) ? $parsed : [];
    }
    
    private function generateSummary($host, $model, $text) {
        $prompt = "Provide a concise summary of the following text in 2-3 sentences.\n\nText: $text";
        
        return $this->callOllama($host, $model, $prompt);
    }
    
    private function extractTopics($host, $model, $text) {
        $prompt = "Identify the main topics discussed in this text. Respond with ONLY a JSON array of topic strings.\n\nText: $text";
        
        $response = $this->callOllama($host, $model, $prompt);
        $parsed = $this->parseJsonResponse($response, []);
        return is_array($parsed) ? $parsed : [];
    }
    
    private function detectLanguage($host, $model, $text) {
        $prompt = "Detect the language of this text. Respond with ONLY a JSON object containing: language (full name), code (ISO 639-1), confidence (0-1).\n\nText: $text";
        
        $response = $this->callOllama($host, $model, $prompt);
        return $this->parseJsonResponse($response, [
            'language' => 'Unknown',
            'code' => 'unknown',
            'confidence' => 0
        ]);
    }
    
    private function classifyText($host, $model, $text) {
        $prompt = "Classify this text into one of these categories: Business, Technology, Science, Health, Entertainment, Sports, Politics, Education, Other. Respond with ONLY a JSON object containing: category (string), confidence (0-1), subcategories (array).\n\nText: $text";
        
        $response = $this->callOllama($host, $model, $prompt);
        return $this->parseJsonResponse($response, [
            'category' => 'Other',
            'confidence' => 0,
            'subcategories' => []
        ]);
    }
    
    private function customAnalysis($host, $model, $text, $customPrompt) {
        $prompt = "$customPrompt\n\nText: $text";
        return $this->callOllama($host, $model, $prompt);
    }
    
    private function callOllama($host, $model, $prompt) {
        $ch = curl_init();
        
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0.1, // Low temperature for consistent analysis
                'num_predict' => 1024
            ]
        ];
        
        curl_setopt($ch, CURLOPT_URL, "$host/api/generate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Ollama API error: HTTP $httpCode");
        }
        
        $data = json_decode($response, true);
        return $data['response'] ?? '';
    }
    
    private function parseJsonResponse($response, $default) {
        // Try to extract JSON from response
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }
        
        if (preg_match('/\[.*\]/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }
        
        return $default;
    }
    
    private function replaceVariables($text, $data) {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($data) {
            return $data[$matches[1]] ?? $matches[0];
        }, $text);
    }
}

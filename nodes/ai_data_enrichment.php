<?php
/**
 * AI Data Enrichment Node
 * Enrich and enhance data using AI
 */

class AiDataEnrichmentNode {
    
    public function getMetadata() {
        return [
            'name' => 'AI Data Enrichment',
            'description' => 'Enrich and enhance data using AI: generate, expand, validate',
            'category' => 'ai',
            'icon' => '✨',
            'inputs' => ['data'],
            'outputs' => ['enriched_data'],
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
                        'mixtral' => 'Mixtral'
                    ],
                    'default' => 'llama3.2',
                    'required' => true
                ],
                [
                    'name' => 'enrichment_type',
                    'label' => 'Enrichment Type',
                    'type' => 'select',
                    'options' => [
                        'generate_description' => 'Generate Description',
                        'expand_abbreviations' => 'Expand Abbreviations',
                        'validate_data' => 'Validate Data',
                        'categorize' => 'Categorize Items',
                        'generate_tags' => 'Generate Tags',
                        'translate' => 'Translate',
                        'reformat' => 'Reformat Data',
                        'extract_insights' => 'Extract Insights',
                        'generate_variations' => 'Generate Variations'
                    ],
                    'default' => 'generate_description',
                    'required' => true
                ],
                [
                    'name' => 'input_field',
                    'label' => 'Input Field',
                    'type' => 'string',
                    'placeholder' => 'field_name',
                    'required' => true,
                    'description' => 'Field name to enrich'
                ],
                [
                    'name' => 'output_field',
                    'label' => 'Output Field',
                    'type' => 'string',
                    'placeholder' => 'enriched_field',
                    'required' => true,
                    'description' => 'Field name for enriched data'
                ],
                [
                    'name' => 'custom_instructions',
                    'label' => 'Custom Instructions',
                    'type' => 'textarea',
                    'placeholder' => 'Additional instructions for AI...',
                    'required' => false
                ],
                [
                    'name' => 'target_language',
                    'label' => 'Target Language (for translation)',
                    'type' => 'string',
                    'placeholder' => 'English, Spanish, French...',
                    'required' => false
                ],
                [
                    'name' => 'batch_processing',
                    'label' => 'Batch Processing',
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Process multiple items at once'
                ]
            ]
        ];
    }
    
    public function run($data, $config) {
        $host = $config['ollama_host'] ?? 'http://localhost:11434';
        $model = $config['model'] ?? 'llama3.2';
        $enrichmentType = $config['enrichment_type'] ?? 'generate_description';
        $inputField = $config['input_field'] ?? '';
        $outputField = $config['output_field'] ?? 'enriched';
        $batchProcessing = $config['batch_processing'] ?? false;
        
        if (empty($inputField)) {
            throw new Exception("Input field is required");
        }
        
        // Get input value
        $inputValue = $data[$inputField] ?? null;
        
        if ($inputValue === null) {
            throw new Exception("Input field '$inputField' not found in data");
        }
        
        // Process based on type
        if ($batchProcessing && is_array($inputValue)) {
            $enrichedData = [];
            foreach ($inputValue as $item) {
                $enrichedData[] = $this->enrichSingleItem($host, $model, $item, $enrichmentType, $config);
            }
        } else {
            $enrichedData = $this->enrichSingleItem($host, $model, $inputValue, $enrichmentType, $config);
        }
        
        // Return enriched data
        $result = $data;
        $result[$outputField] = $enrichedData;
        
        return [
            'success' => true,
            'enrichment_type' => $enrichmentType,
            'input_field' => $inputField,
            'output_field' => $outputField,
            'data' => $result
        ];
    }
    
    private function enrichSingleItem($host, $model, $item, $type, $config) {
        switch ($type) {
            case 'generate_description':
                return $this->generateDescription($host, $model, $item, $config);
            
            case 'expand_abbreviations':
                return $this->expandAbbreviations($host, $model, $item);
            
            case 'validate_data':
                return $this->validateData($host, $model, $item, $config);
            
            case 'categorize':
                return $this->categorizeItem($host, $model, $item);
            
            case 'generate_tags':
                return $this->generateTags($host, $model, $item);
            
            case 'translate':
                $targetLang = $config['target_language'] ?? 'English';
                return $this->translate($host, $model, $item, $targetLang);
            
            case 'reformat':
                return $this->reformatData($host, $model, $item, $config);
            
            case 'extract_insights':
                return $this->extractInsights($host, $model, $item);
            
            case 'generate_variations':
                return $this->generateVariations($host, $model, $item);
            
            default:
                throw new Exception("Unknown enrichment type: $type");
        }
    }
    
    private function generateDescription($host, $model, $item, $config) {
        $instructions = $config['custom_instructions'] ?? '';
        $prompt = "Generate a detailed description for: $item";
        if ($instructions) {
            $prompt .= "\n\nAdditional instructions: $instructions";
        }
        
        return $this->callOllama($host, $model, $prompt);
    }
    
    private function expandAbbreviations($host, $model, $item) {
        $prompt = "Expand all abbreviations and acronyms in the following text, providing full forms: $item";
        return $this->callOllama($host, $model, $prompt);
    }
    
    private function validateData($host, $model, $item, $config) {
        $instructions = $config['custom_instructions'] ?? 'common data quality issues';
        $prompt = "Validate this data and identify any issues ($instructions): $item\n\nRespond with JSON: {\"valid\": true/false, \"issues\": [], \"suggestions\": []}";
        
        $response = $this->callOllama($host, $model, $prompt);
        return $this->parseJsonFromText($response, ['valid' => true, 'issues' => [], 'suggestions' => []]);
    }
    
    private function categorizeItem($host, $model, $item) {
        $prompt = "Categorize this item into appropriate categories. Respond with JSON array: $item";
        $response = $this->callOllama($host, $model, $prompt);
        return $this->parseJsonFromText($response, []);
    }
    
    private function generateTags($host, $model, $item) {
        $prompt = "Generate relevant tags for: $item\n\nRespond with JSON array of strings.";
        $response = $this->callOllama($host, $model, $prompt);
        return $this->parseJsonFromText($response, []);
    }
    
    private function translate($host, $model, $item, $targetLang) {
        $prompt = "Translate the following text to $targetLang. Only output the translation:\n\n$item";
        return $this->callOllama($host, $model, $prompt);
    }
    
    private function reformatData($host, $model, $item, $config) {
        $instructions = $config['custom_instructions'] ?? 'a cleaner, more structured format';
        $prompt = "Reformat this data into $instructions:\n\n$item";
        return $this->callOllama($host, $model, $prompt);
    }
    
    private function extractInsights($host, $model, $item) {
        $prompt = "Extract key insights and interesting patterns from this data:\n\n$item\n\nRespond with JSON: {\"insights\": [], \"patterns\": [], \"recommendations\": []}";
        $response = $this->callOllama($host, $model, $prompt);
        return $this->parseJsonFromText($response, ['insights' => [], 'patterns' => [], 'recommendations' => []]);
    }
    
    private function generateVariations($host, $model, $item) {
        $prompt = "Generate 5 variations of: $item\n\nRespond with JSON array.";
        $response = $this->callOllama($host, $model, $prompt);
        return $this->parseJsonFromText($response, []);
    }
    
    private function callOllama($host, $model, $prompt) {
        $ch = curl_init();
        
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0.7,
                'num_predict' => 1024
            ]
        ];
        
        curl_setopt($ch, CURLOPT_URL, "$host/api/generate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Ollama API error: HTTP $httpCode");
        }
        
        $data = json_decode($response, true);
        return $data['response'] ?? '';
    }
    
    private function parseJsonFromText($text, $default) {
        // Try to extract JSON
        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) return $json;
        }
        
        if (preg_match('/\[.*\]/s', $text, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) return $json;
        }
        
        return $default;
    }
}

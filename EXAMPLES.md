# 🎯 N8N Pro Enhanced - AI Workflow Examples

Complete examples of AI-powered workflows using local Ollama integration.

---

## 📚 Table of Contents

1. [Basic AI Examples](#basic-ai-examples)
2. [Content Creation](#content-creation)
3. [Data Processing](#data-processing)
4. [Code Automation](#code-automation)
5. [Image Processing](#image-processing)
6. [Customer Support](#customer-support)
7. [Advanced Workflows](#advanced-workflows)

---

## 🚀 Basic AI Examples

### Example 1: Simple AI Chat

**Workflow:**
```
[Manual Trigger] → [Ollama AI]
```

**Configuration:**
```json
{
  "node": "Ollama AI",
  "config": {
    "operation": "chat",
    "model": "llama3.2",
    "prompt": "Explain quantum computing in simple terms",
    "system_prompt": "You are a helpful science teacher",
    "temperature": 0.7
  }
}
```

**Use Case:** Quick AI responses for any question

---

### Example 2: Text Sentiment Analysis

**Workflow:**
```
[Webhook] → [AI Text Analyzer] → [Condition] → [Email Send]
```

**Webhook Configuration:**
```json
{
  "path": "analyze-feedback",
  "method": "POST"
}
```

**AI Text Analyzer:**
```json
{
  "text": "{{webhook_body}}",
  "analysis_type": ["sentiment", "keywords"],
  "model": "llama3.2"
}
```

**Condition:**
```json
{
  "field": "analysis.sentiment.sentiment",
  "operator": "equals",
  "value": "negative"
}
```

**Use Case:** Auto-detect negative customer feedback and alert team

---

## ✍️ Content Creation

### Example 3: Blog Post Generator

**Workflow:**
```
[Schedule] → [HTTP Request] → [Ollama AI] → [AI Text Analyzer] → [File Write]
```

**Schedule:** Daily at 9 AM

**HTTP Request:**
```json
{
  "url": "https://api.example.com/trending-topics",
  "method": "GET"
}
```

**Ollama AI:**
```json
{
  "operation": "generate",
  "model": "llama3.2",
  "prompt": "Write a 500-word blog post about: {{topic}}",
  "temperature": 0.8,
  "max_tokens": 2048
}
```

**AI Text Analyzer:**
```json
{
  "text": "{{response}}",
  "analysis_type": ["keywords", "summary"]
}
```

**File Write:**
```json
{
  "path": "/var/www/blog/posts/{{date}}-{{topic}}.md",
  "content": "{{response}}"
}
```

**Use Case:** Automated blog content generation

---

### Example 4: Product Description Generator

**Workflow:**
```
[MySQL Query] → [AI Data Enrichment] → [Transform] → [HTTP Request]
```

**MySQL Query:**
```sql
SELECT id, name, category, features 
FROM products 
WHERE description IS NULL 
LIMIT 10
```

**AI Data Enrichment:**
```json
{
  "enrichment_type": "generate_description",
  "input_field": "features",
  "output_field": "ai_description",
  "model": "llama3.2",
  "custom_instructions": "Create compelling product descriptions that highlight benefits"
}
```

**Transform:**
```json
{
  "mode": "json",
  "operation": "add_fields",
  "fields": {
    "description": "{{ai_description}}",
    "updated_at": "{{now}}"
  }
}
```

**HTTP Request:**
```json
{
  "url": "https://api.shop.com/products/{{id}}",
  "method": "PUT",
  "body": "{{transformed_data}}"
}
```

**Use Case:** Auto-generate descriptions for e-commerce products

---

## 📊 Data Processing

### Example 5: Data Validation & Cleaning

**Workflow:**
```
[File Read] → [JSON Parse] → [AI Data Enrichment] → [Condition] → [File Write]
```

**File Read:**
```json
{
  "path": "/data/customer_data.json"
}
```

**AI Data Enrichment:**
```json
{
  "enrichment_type": "validate_data",
  "input_field": "customer_info",
  "output_field": "validation_result",
  "custom_instructions": "Check for: missing fields, invalid emails, incorrect phone formats"
}
```

**Condition:**
```json
{
  "field": "validation_result.valid",
  "operator": "equals",
  "value": true
}
```

**Use Case:** Automated data quality assurance

---

### Example 6: Multi-Language Translation Pipeline

**Workflow:**
```
[Webhook] → [AI Text Analyzer] → [AI Data Enrichment] → [Transform] → [MySQL Query]
```

**AI Text Analyzer (Detect Language):**
```json
{
  "text": "{{content}}",
  "analysis_type": ["language"],
  "model": "llama3.2"
}
```

**AI Data Enrichment (Translate):**
```json
{
  "enrichment_type": "translate",
  "input_field": "content",
  "output_field": "translations",
  "target_language": "English, Spanish, French, Arabic",
  "batch_processing": true
}
```

**Use Case:** Auto-translate content to multiple languages

---

## 💻 Code Automation

### Example 7: Code Generator from Requirements

**Workflow:**
```
[Webhook] → [AI Code Generator] → [AI Code Generator] → [File Write] → [HTTP Request]
```

**Webhook:**
```json
{
  "path": "generate-code",
  "method": "POST"
}
```

**AI Code Generator #1 (Generate Code):**
```json
{
  "operation": "generate",
  "programming_language": "python",
  "input_text": "{{requirements}}",
  "code_style": "clean",
  "include_comments": true,
  "model": "codellama"
}
```

**AI Code Generator #2 (Generate Tests):**
```json
{
  "operation": "test",
  "programming_language": "python",
  "input_text": "{{generated_code}}",
  "model": "codellama"
}
```

**File Write:**
```json
{
  "path": "/generated/{{project_name}}/",
  "files": [
    {"name": "main.py", "content": "{{code}}"},
    {"name": "test_main.py", "content": "{{tests}}"}
  ]
}
```

**Use Case:** Automated code generation with tests

---

### Example 8: Code Review & Optimization

**Workflow:**
```
[File Read] → [AI Code Generator] → [AI Code Generator] → [Transform] → [Email Send]
```

**AI Code Generator #1 (Explain):**
```json
{
  "operation": "explain",
  "input_text": "{{code}}",
  "model": "codellama"
}
```

**AI Code Generator #2 (Optimize):**
```json
{
  "operation": "optimize",
  "programming_language": "python",
  "input_text": "{{code}}",
  "model": "codellama"
}
```

**Use Case:** Automated code review and optimization suggestions

---

## 🖼️ Image Processing

### Example 9: Automated Image Captioning

**Workflow:**
```
[Webhook] → [AI Image Analyzer] → [Transform] → [MySQL Query]
```

**Webhook:**
```json
{
  "path": "analyze-image",
  "method": "POST"
}
```

**AI Image Analyzer:**
```json
{
  "image_source": "url",
  "image_input": "{{image_url}}",
  "analysis_prompt": "Describe this image in detail for accessibility",
  "detect_objects": true,
  "detect_text": true,
  "detect_colors": true,
  "model": "llava"
}
```

**MySQL Query:**
```sql
UPDATE images 
SET 
  alt_text = '{{description}}',
  detected_objects = '{{objects}}',
  extracted_text = '{{text}}',
  dominant_colors = '{{colors}}'
WHERE id = {{image_id}}
```

**Use Case:** Auto-generate image alt text for accessibility

---

### Example 10: Image Content Moderation

**Workflow:**
```
[Schedule] → [MySQL Query] → [AI Image Analyzer] → [Condition] → [Email Send]
```

**AI Image Analyzer:**
```json
{
  "image_source": "url",
  "image_input": "{{image_url}}",
  "analysis_prompt": "Analyze this image for: inappropriate content, violence, adult content. Respond with JSON: {safe: true/false, concerns: [], confidence: 0-1}",
  "model": "llava",
  "temperature": 0.1
}
```

**Condition:**
```json
{
  "field": "analysis.safe",
  "operator": "equals",
  "value": false
}
```

**Use Case:** Automated image content moderation

---

## 🎧 Customer Support

### Example 11: Smart Support Ticket Routing

**Workflow:**
```
[Webhook] → [AI Text Analyzer] → [AI Data Enrichment] → [Condition] → [HTTP Request]
```

**AI Text Analyzer:**
```json
{
  "text": "{{ticket_description}}",
  "analysis_type": ["sentiment", "classification"],
  "model": "llama3.2"
}
```

**AI Data Enrichment:**
```json
{
  "enrichment_type": "categorize",
  "input_field": "ticket_description",
  "output_field": "category",
  "custom_instructions": "Categories: Technical, Billing, Account, Feature Request, Bug Report"
}
```

**Condition (Route by Category):**
```json
{
  "field": "category",
  "operator": "equals",
  "value": "Technical"
}
```

**Use Case:** Automatically categorize and route support tickets

---

### Example 12: AI-Powered FAQ Generator

**Workflow:**
```
[Schedule] → [MySQL Query] → [Ollama AI] → [Transform] → [File Write]
```

**MySQL Query:**
```sql
SELECT question, count(*) as frequency
FROM support_tickets
GROUP BY question
ORDER BY frequency DESC
LIMIT 20
```

**Ollama AI:**
```json
{
  "operation": "generate",
  "model": "llama3.2",
  "prompt": "Based on these frequently asked questions: {{questions}}\n\nGenerate a comprehensive FAQ with clear, helpful answers.",
  "temperature": 0.6,
  "max_tokens": 4096
}
```

**Use Case:** Auto-generate FAQ from support ticket patterns

---

## 🔥 Advanced Workflows

### Example 13: Complete Content Pipeline

**Workflow:**
```
[Schedule]
    ↓
[HTTP Request: Get Trending Topics]
    ↓
[Ollama AI: Generate Article]
    ↓
[AI Text Analyzer: Extract Keywords]
    ↓
[AI Data Enrichment: Generate Tags]
    ↓
[AI Image Analyzer: Find Related Images]
    ↓
[Transform: Format for CMS]
    ↓
[HTTP Request: Publish to WordPress]
    ↓
[Email Send: Notify Team]
```

**Use Case:** Fully automated content creation and publishing

---

### Example 14: Intelligent Data Enrichment Pipeline

**Workflow:**
```
[MySQL Query: Get Raw Data]
    ↓
[AI Data Enrichment: Generate Descriptions]
    ↓
[AI Data Enrichment: Categorize Items]
    ↓
[AI Data Enrichment: Generate Tags]
    ↓
[AI Data Enrichment: Translate to Multiple Languages]
    ↓
[Transform: Format Data]
    ↓
[MySQL Query: Update Database]
```

**Use Case:** Comprehensive product data enrichment for e-commerce

---

### Example 15: Multi-Modal AI Analysis

**Workflow:**
```
[Webhook: Receive Product URL]
    ↓
[HTTP Request: Fetch Product Page]
    ↓
[Transform: Extract Images & Text]
    ↓
[Parallel Execution]
    ├─→ [AI Image Analyzer: Analyze Product Images]
    └─→ [AI Text Analyzer: Analyze Description]
    ↓
[Transform: Merge Results]
    ↓
[Ollama AI: Generate Complete Product Analysis]
    ↓
[MySQL Query: Store Results]
```

**Use Case:** Complete product analysis combining vision and text AI

---

## 🎓 Tips & Best Practices

### Performance Optimization

1. **Model Selection:**
   - Use `phi` for simple, fast tasks
   - Use `llama3.2` for balanced performance
   - Use `mixtral` for complex reasoning

2. **Batch Processing:**
   - Process multiple items together when possible
   - Use `batch_processing: true` in AI nodes

3. **Caching:**
   - Store AI results in database to avoid re-processing
   - Use `keep_alive` parameter to keep models in memory

4. **Temperature Settings:**
   - Low (0.1-0.3): Consistent, factual responses
   - Medium (0.5-0.7): Balanced creativity
   - High (0.8-1.0): Creative, varied responses

### Error Handling

```
[AI Node]
    ↓
[Condition: Check Success]
    ├─→ Success: Continue workflow
    └─→ Failure: [Email Send: Alert Team]
```

### Variable Usage

Use variables to pass data between nodes:
- `{{webhook_body}}` - Data from webhook
- `{{previous_node.output}}` - Output from previous node
- `{{analysis.sentiment}}` - Nested data access

---

<div align="center">

## 🚀 Start Building Your AI Workflows!

These examples are just the beginning. Combine nodes creatively to build powerful automated workflows with local AI.

**Need help?** Check the [full documentation](README.md) or [join our community](#)

</div>

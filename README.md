# 🚀 N8N Pro Enhanced - Advanced Workflow Automation with Local AI

![Version](https://img.shields.io/badge/version-4.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-777BB4.svg)
![Ollama](https://img.shields.io/badge/Ollama-AI%20Ready-green.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

A professional n8n-inspired workflow automation engine with **powerful local AI integration** using Ollama - No API keys, No cloud dependencies, Complete privacy!

## 🌟 What's New in v4.0 - AI Enhanced Edition

### 🤖 Local AI Integration with Ollama
- **5 Advanced AI Nodes** for complete workflow automation
- **100% Local Processing** - Your data never leaves your server
- **No API Costs** - Run unlimited AI tasks for free
- **Privacy First** - Complete control over your data
- **Multi-Model Support** - Use Llama, Mistral, CodeLlama, and more

### 🎯 New AI-Powered Nodes

#### 1. 🤖 Ollama AI Node
Complete AI integration with multiple operations:
- **Chat Completion** - Conversational AI with context
- **Text Generation** - Generate content from prompts
- **Embeddings** - Create vector embeddings for semantic search
- **Model Management** - List, pull, and create custom models
- Advanced parameters: temperature, top_p, top_k, context window
- Streaming support for real-time responses
- Custom system prompts and fine-tuned control

#### 2. 🔍 AI Text Analyzer Node
Advanced text analysis capabilities:
- **Sentiment Analysis** - Detect positive/negative/neutral sentiment
- **Entity Extraction** - Extract persons, organizations, locations
- **Keyword Extraction** - Identify important keywords
- **Text Summarization** - Generate concise summaries
- **Topic Modeling** - Discover main topics
- **Language Detection** - Identify text language
- **Text Classification** - Auto-categorize content

#### 3. 🖼️ AI Image Analyzer Node
Vision AI for image understanding:
- **Image Description** - Detailed image descriptions
- **Object Detection** - Identify objects in images
- **OCR (Text Extraction)** - Extract text from images
- **Color Analysis** - Analyze dominant colors
- **Composition Analysis** - Understand image structure
- Supports LLaVA, Bakllava vision models
- Works with URLs, local files, or base64 images

#### 4. ✨ AI Data Enrichment Node
Enhance your data with AI:
- **Generate Descriptions** - Auto-describe products, items
- **Expand Abbreviations** - Convert acronyms to full forms
- **Data Validation** - AI-powered data quality checks
- **Auto-Categorization** - Smart category assignment
- **Tag Generation** - Generate relevant tags
- **Translation** - Multi-language translation
- **Extract Insights** - Discover patterns and insights
- **Generate Variations** - Create content variations

#### 5. 💻 AI Code Generator Node
Professional code generation:
- **Generate Code** - From natural language descriptions
- **Refactor Code** - Improve code quality
- **Debug Code** - Find and fix bugs automatically
- **Explain Code** - Understand complex code
- **Optimize Code** - Performance improvements
- **Generate Tests** - Auto-create unit tests
- **Generate Documentation** - Complete code docs
- **Convert Languages** - Translate between programming languages
- Supports: Python, JavaScript, TypeScript, Java, Go, Rust, PHP, and more
- Uses CodeLlama and specialized coding models

## ✨ Core Features

### 🎯 Workflow Automation
- **Drag & Drop Interface** - Intuitive visual workflow builder
- **20+ Node Types** - Comprehensive automation toolkit
- **Visual Workflow Editor** - Design complex automations visually
- **Parallel Execution** - Execute multiple nodes simultaneously
- **Detailed Execution Logs** - Track every step
- **Webhook Support** - Receive data from external sources
- **Task Scheduling** - Cron-based scheduling
- **Auto-Save** - Never lose your work

### 🔧 Available Nodes

#### Triggers
- **Webhook** - Receive HTTP requests
- **Schedule** - Cron-based scheduling

#### Actions
- **HTTP Request** - Make API calls
- **Email Send** - Send emails via SMTP
- **File Write** - Write data to files
- **File Read** - Read data from files

#### Logic
- **Condition** - Branch workflows
- **Delay** - Pause execution

#### Data Processing
- **Transform** - Modify and transform data
- **JSON Parse** - Parse JSON strings
- **MySQL Query** - Execute database queries
- **PHP Run** - Execute custom PHP code

#### AI Nodes (New!)
- **Ollama AI** - Complete AI integration
- **AI Text Analyzer** - Advanced text analysis
- **AI Image Analyzer** - Vision AI
- **AI Data Enrichment** - Data enhancement
- **AI Code Generator** - Code generation

## 📋 Requirements

### System Requirements
- **PHP** >= 7.4
- **MySQL** >= 5.7
- **Apache/Nginx** with mod_rewrite
- **PDO MySQL Driver**
- **cURL Extension**
- **JSON Extension**

### AI Requirements (Ollama)
- **Ollama** >= 0.1.0 ([Install Guide](#ollama-installation))
- **8GB RAM** minimum (16GB recommended for larger models)
- **GPU** optional but recommended for faster inference
- **Storage**: 5-50GB depending on models

## 🛠️ Installation

### Step 1: Install N8N Pro Enhanced

1. **Download the project**
```bash
git clone https://github.com/your-repo/n8n-pro-enhanced.git
cd n8n-pro-enhanced
```

2. **Create database**
```sql
CREATE DATABASE n8n_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. **Configure settings**
Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'n8n_pro');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

4. **Run installation**
Open your browser: `http://localhost/n8n-pro-enhanced/install.php`

### Step 2: Install Ollama

#### On Linux:
```bash
curl -fsSL https://ollama.ai/install.sh | sh
```

#### On macOS:
```bash
brew install ollama
```

#### On Windows:
Download from [ollama.ai](https://ollama.ai/download)

### Step 3: Install AI Models

```bash
# Essential models for N8N Pro Enhanced

# General AI (Required)
ollama pull llama3.2

# Code Generation (Recommended)
ollama pull codellama

# Vision AI (For image analysis)
ollama pull llava

# Additional models (Optional)
ollama pull mistral      # Fast and efficient
ollama pull mixtral      # Advanced reasoning
ollama pull phi          # Lightweight option
```

### Step 4: Start Ollama

```bash
# Start Ollama server
ollama serve

# Verify installation
curl http://localhost:11434/api/tags
```

### Step 5: Test Your Setup

1. Open N8N Pro Enhanced: `http://localhost/n8n-pro-enhanced/`
2. Create a new workflow
3. Add an "Ollama AI" node
4. Configure it with a simple prompt
5. Execute and see the magic! ✨

## 📖 Quick Start Guide

### Example 1: AI-Powered Content Analysis Workflow

```
[Webhook] → [AI Text Analyzer] → [Condition] → [Email Send]
```

1. **Webhook**: Receives text content
2. **AI Text Analyzer**: Analyzes sentiment and extracts keywords
3. **Condition**: Checks if sentiment is negative
4. **Email Send**: Sends alert if negative sentiment detected

### Example 2: Image Processing Workflow

```
[HTTP Request] → [AI Image Analyzer] → [Transform] → [MySQL Query]
```

1. **HTTP Request**: Downloads image
2. **AI Image Analyzer**: Describes image content
3. **Transform**: Formats data
4. **MySQL Query**: Stores analysis results

### Example 3: AI Code Assistant Workflow

```
[Webhook] → [AI Code Generator] → [File Write] → [HTTP Request]
```

1. **Webhook**: Receives code requirements
2. **AI Code Generator**: Generates code with tests
3. **File Write**: Saves generated code
4. **HTTP Request**: Notifies completion

### Example 4: Data Enrichment Pipeline

```
[MySQL Query] → [AI Data Enrichment] → [Transform] → [HTTP Request]
```

1. **MySQL Query**: Fetches product data
2. **AI Data Enrichment**: Generates descriptions and tags
3. **Transform**: Formats enriched data
4. **HTTP Request**: Sends to external API

## 🎨 AI Node Configuration Examples

### Ollama AI - Chat Completion
```json
{
  "operation": "chat",
  "model": "llama3.2",
  "prompt": "Explain quantum computing in simple terms",
  "system_prompt": "You are a helpful science teacher",
  "temperature": 0.7,
  "max_tokens": 2048
}
```

### AI Text Analyzer - Multi-Analysis
```json
{
  "text": "{{input_text}}",
  "analysis_type": ["sentiment", "entities", "keywords", "summary"],
  "model": "llama3.2"
}
```

### AI Image Analyzer - Complete Analysis
```json
{
  "image_source": "url",
  "image_input": "https://example.com/image.jpg",
  "analysis_prompt": "Describe this image in detail",
  "detect_objects": true,
  "detect_text": true,
  "detect_colors": true
}
```

### AI Code Generator - Generate Python Function
```json
{
  "operation": "generate",
  "programming_language": "python",
  "input_text": "Create a function to calculate fibonacci sequence",
  "code_style": "clean",
  "include_comments": true,
  "include_tests": true
}
```

## 🔌 API Documentation

### AI Nodes API

#### Execute AI Workflow
```http
POST /api/workflows.php/execute
Content-Type: application/json

{
  "workflow_id": "uuid",
  "input_data": {
    "text": "Your input text",
    "image_url": "https://example.com/image.jpg"
  }
}
```

#### Check Ollama Status
```http
GET /api/ai_status.php
```

Response:
```json
{
  "ollama_running": true,
  "models_available": ["llama3.2", "codellama", "llava"],
  "version": "0.1.0"
}
```

## 🚀 Advanced Usage

### Using Variables in AI Prompts

```
Prompt: "Analyze this text: {{webhook_body}}"
```

Variables are automatically replaced with data from previous nodes.

### Chaining AI Nodes

```
[Ollama AI] → [AI Text Analyzer] → [AI Data Enrichment]
```

Output from one AI node becomes input for the next, enabling complex AI pipelines.

### Custom AI Models

Create custom models with specific behaviors:

```bash
# Create modelfile
cat > Modelfile << EOF
FROM llama3.2
PARAMETER temperature 0.8
SYSTEM You are a professional content writer specializing in technical documentation.
EOF

# Create custom model
ollama create technical-writer -f Modelfile
```

Use in workflow:
```json
{
  "model": "custom",
  "custom_model": "technical-writer"
}
```

## 🎯 Use Cases

### 1. Content Creation & Management
- Auto-generate product descriptions
- Summarize articles and documents
- Extract keywords for SEO
- Translate content to multiple languages

### 2. Customer Support Automation
- Analyze customer feedback sentiment
- Auto-categorize support tickets
- Generate response suggestions
- Extract action items from conversations

### 3. Data Processing & Enrichment
- Validate and clean data
- Generate missing information
- Categorize and tag data
- Extract insights from datasets

### 4. Code Development & Documentation
- Generate code from requirements
- Refactor and optimize existing code
- Create unit tests automatically
- Generate comprehensive documentation

### 5. Image & Media Processing
- Auto-describe images for accessibility
- Extract text from images (OCR)
- Categorize and tag images
- Analyze image content and quality

### 6. Research & Analysis
- Summarize research papers
- Extract key findings
- Identify trends and patterns
- Generate research reports

## 📊 Performance Tips

### Ollama Optimization
- Use **GPU acceleration** for 3-5x faster inference
- Keep frequently used models loaded with `keep_alive` parameter
- Use smaller models (llama2:7b, phi) for simple tasks
- Use larger models (llama2:70b, mixtral) for complex reasoning

### Model Selection Guide
- **llama3.2**: Best all-around model
- **mistral**: Fast and efficient
- **codellama**: Best for code generation
- **llava**: Required for image analysis
- **phi**: Lightweight, good for simple tasks

### Workflow Optimization
- Use **parallel execution** where possible
- Cache AI results to avoid redundant processing
- Use **batch processing** for multiple items
- Set appropriate **timeout values** for long AI operations

## 🔐 Security Best Practices

- ✅ Ollama runs **locally** - no data sent to external services
- ✅ Use **HTTPS** in production
- ✅ Implement **rate limiting** for public webhooks
- ✅ Restrict **file access** permissions
- ✅ Use **prepared statements** for database queries
- ✅ Enable **API authentication** for production
- ✅ Regular **security updates**

## 🐛 Troubleshooting

### Ollama Connection Issues
```bash
# Check if Ollama is running
curl http://localhost:11434/api/tags

# Start Ollama service
ollama serve

# Check Ollama logs
journalctl -u ollama -f  # Linux
```

### Model Not Found
```bash
# List installed models
ollama list

# Pull missing model
ollama pull llama3.2
```

### Slow AI Performance
- Check if GPU is being used: `ollama ps`
- Reduce `max_tokens` parameter
- Use smaller models for simple tasks
- Close other GPU-intensive applications

### Memory Issues
- Use smaller models (7B instead of 13B/34B)
- Reduce `context_window` parameter
- Close unused models: `ollama rm model_name`
- Increase system swap space

## 🗺️ Roadmap

### Version 4.1 (Coming Soon)
- [ ] AI Conversation Memory - Context across multiple executions
- [ ] Fine-tuning Interface - Train custom models
- [ ] Voice AI Node - Speech-to-text and text-to-speech
- [ ] AI Agent Node - Autonomous AI agents
- [ ] Batch AI Processing - Process multiple items efficiently
- [ ] AI Model Monitoring - Usage statistics and performance
- [ ] RAG (Retrieval Augmented Generation) - Knowledge base integration
- [ ] Multi-modal Workflows - Combine text, image, and code AI

### Version 4.2
- [ ] Docker Compose setup with Ollama
- [ ] AI Workflow Templates Library
- [ ] Collaborative AI workflows
- [ ] API key management for cloud AI services (optional)
- [ ] Advanced AI analytics dashboard

## 📝 License

This project is licensed under the **MIT License** - free to use, modify, and distribute.

## 🙏 Acknowledgments

- **n8n.io** - Original concept inspiration
- **Ollama** - Amazing local AI platform
- **Meta AI** - Llama models
- **Mistral AI** - Mistral models
- **Haotian Liu** - LLaVA vision model
- **Tailwind CSS** - Beautiful design system

## 📞 Support & Community

- **Documentation**: [Full Docs](docs/)
- **Issues**: [GitHub Issues](https://github.com/your-repo/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-repo/discussions)
- **Discord**: [Join Community](#)
- **Email**: support@example.com

## 💡 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### How to Contribute
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## ⭐ Star History

If you find this project useful, please consider giving it a star! ⭐

---

<div align="center">

**Built with ❤️ for the automation community**

**Powered by Local AI with Ollama 🤖**

**Version 4.0.0** | [Changelog](CHANGELOG.md) | [Documentation](docs/)

</div>

# 🤖 دليل تثبيت واستخدام Ollama - الذكاء الاصطناعي المحلي

## 📖 ما هو Ollama؟

**Ollama** هو منصة مجانية ومفتوحة المصدر لتشغيل نماذج الذكاء الاصطناعي محلياً على جهازك. 

### ✨ المميزات:
- 🔒 **خصوصية كاملة** - بياناتك لا تغادر جهازك أبداً
- 💰 **مجاني تماماً** - لا توجد تكاليف API أو اشتراكات
- ⚡ **سريع** - يعمل على جهازك مباشرة
- 🎯 **سهل الاستخدام** - تثبيت بسيط في دقائق
- 🌍 **متعدد النماذج** - دعم Llama, Mistral, CodeLlama وأكثر

---

## 🛠️ التثبيت

### 1️⃣ على Linux (Ubuntu/Debian/Fedora)

```bash
# طريقة التثبيت السريعة
curl -fsSL https://ollama.ai/install.sh | sh
```

أو التثبيت اليدوي:

```bash
# تحميل Ollama
wget https://ollama.ai/download/ollama-linux-amd64
sudo mv ollama-linux-amd64 /usr/local/bin/ollama
sudo chmod +x /usr/local/bin/ollama

# إنشاء خدمة systemd
sudo cat > /etc/systemd/system/ollama.service << EOF
[Unit]
Description=Ollama Service
After=network-online.target

[Service]
ExecStart=/usr/local/bin/ollama serve
User=ollama
Group=ollama
Restart=always
RestartSec=3

[Install]
WantedBy=default.target
EOF

# إنشاء مستخدم ollama
sudo useradd -r -s /bin/false -m -d /usr/share/ollama ollama

# تفعيل وتشغيل الخدمة
sudo systemctl daemon-reload
sudo systemctl enable ollama
sudo systemctl start ollama
```

### 2️⃣ على macOS

```bash
# باستخدام Homebrew
brew install ollama

# أو تحميل المثبت
# قم بزيارة: https://ollama.ai/download
# وحمل ملف .pkg لنظام macOS
```

### 3️⃣ على Windows

1. قم بتحميل **Ollama for Windows** من: https://ollama.ai/download
2. شغل ملف التثبيت `OllamaSetup.exe`
3. اتبع خطوات التثبيت
4. Ollama سيعمل تلقائياً في الخلفية

### 4️⃣ باستخدام Docker

```bash
# تشغيل Ollama في Docker
docker run -d -v ollama:/root/.ollama -p 11434:11434 --name ollama ollama/ollama

# مع دعم GPU (NVIDIA)
docker run -d --gpus=all -v ollama:/root/.ollama -p 11434:11434 --name ollama ollama/ollama
```

---

## ✅ التحقق من التثبيت

```bash
# التحقق من نسخة Ollama
ollama --version

# التحقق من أن الخدمة تعمل
curl http://localhost:11434/api/tags

# يجب أن تحصل على استجابة JSON
```

---

## 📦 تثبيت النماذج (Models)

### النماذج الأساسية الموصى بها لـ N8N Pro Enhanced:

```bash
# 1. Llama 3.2 - النموذج الأساسي (موصى به)
ollama pull llama3.2
# الحجم: ~2GB
# الاستخدام: مهام عامة، محادثات، تحليل نصوص

# 2. CodeLlama - لتوليد الأكواد
ollama pull codellama
# الحجم: ~4GB
# الاستخدام: كتابة وتحليل الأكواد البرمجية

# 3. LLaVA - لتحليل الصور
ollama pull llava
# الحجم: ~5GB
# الاستخدام: وصف الصور، استخراج النصوص من الصور

# 4. Mistral - سريع وفعال
ollama pull mistral
# الحجم: ~4GB
# الاستخدام: مهام سريعة، إنتاجية عالية
```

### نماذج إضافية (اختيارية):

```bash
# Mixtral - للمهام المعقدة
ollama pull mixtral
# الحجم: ~26GB

# Phi - خفيف وسريع
ollama pull phi
# الحجم: ~1.6GB

# Neural Chat - للمحادثات
ollama pull neural-chat
# الحجم: ~4GB

# Gemma - من Google
ollama pull gemma
# الحجم: ~5GB
```

---

## 🚀 الاستخدام الأساسي

### 1. تشغيل خادم Ollama

```bash
# Linux/macOS
ollama serve

# Windows: يعمل تلقائياً في الخلفية
```

### 2. اختبار النموذج من سطر الأوامر

```bash
# محادثة تفاعلية
ollama run llama3.2

# مثال:
# >>> اكتب لي قصة قصيرة عن الذكاء الاصطناعي

# محادثة بسؤال واحد
ollama run llama3.2 "ما هو الذكاء الاصطناعي؟"
```

### 3. استخدام API

```bash
# Chat API
curl http://localhost:11434/api/chat -d '{
  "model": "llama3.2",
  "messages": [
    {
      "role": "user",
      "content": "مرحباً، كيف حالك؟"
    }
  ]
}'

# Generate API
curl http://localhost:11434/api/generate -d '{
  "model": "llama3.2",
  "prompt": "اشرح لي الذكاء الاصطناعي ببساطة"
}'
```

---

## 🎯 ربط Ollama مع N8N Pro Enhanced

### 1. التأكد من تشغيل Ollama

```bash
# التحقق من الحالة
curl http://localhost:11434/api/tags

# إذا لم يعمل، قم بتشغيله
ollama serve
```

### 2. إنشاء Workflow بسيط

1. افتح N8N Pro Enhanced
2. أضف نود **Ollama AI**
3. اضبط الإعدادات:
   - **Ollama Host**: `http://localhost:11434`
   - **Model**: `llama3.2`
   - **Operation**: `chat`
   - **Prompt**: `اشرح لي البرمجة`
4. نفذ الـ Workflow
5. شاهد النتيجة! ✨

### 3. مثال Workflow متقدم

```
[Webhook] → [Ollama AI] → [AI Text Analyzer] → [Transform] → [MySQL Query]
```

---

## ⚙️ الإعدادات المتقدمة

### تخصيص نموذج AI

```bash
# إنشاء Modelfile
cat > Modelfile << EOF
FROM llama3.2

# معاملات النموذج
PARAMETER temperature 0.8
PARAMETER top_p 0.9
PARAMETER top_k 40

# تعليمات النظام
SYSTEM أنت مساعد ذكي متخصص في البرمجة والتقنية. تجيب بوضوح وتفصيل.
EOF

# إنشاء نموذج مخصص
ollama create my-assistant -f Modelfile

# استخدام النموذج
ollama run my-assistant "ساعدني في كتابة كود Python"
```

### تحسين الأداء

#### 1. استخدام GPU (موصى به)

```bash
# Linux - NVIDIA GPU
# تأكد من تثبيت NVIDIA drivers و CUDA

# التحقق من استخدام GPU
nvidia-smi

# macOS - يستخدم GPU تلقائياً
```

#### 2. تخصيص الذاكرة

```bash
# تعيين حجم Context
ollama run llama3.2 --num-ctx 4096

# تقليل استخدام الذاكرة
ollama run llama3.2:7b  # استخدم النسخة الأصغر
```

#### 3. Keep Alive - إبقاء النموذج في الذاكرة

```bash
# عبر API
curl http://localhost:11434/api/generate -d '{
  "model": "llama3.2",
  "prompt": "Hello",
  "keep_alive": "10m"
}'

# 10m = 10 دقائق
# 0 = تفريغ فوراً
# -1 = إبقاء دائماً
```

---

## 📊 إدارة النماذج

```bash
# عرض النماذج المثبتة
ollama list

# حذف نموذج
ollama rm model-name

# نسخ نموذج
ollama cp llama3.2 my-llama

# عرض معلومات النموذج
ollama show llama3.2

# تحديث النماذج
ollama pull llama3.2
```

---

## 🔧 حل المشاكل الشائعة

### 1. خطأ في الاتصال

```bash
# التحقق من أن Ollama يعمل
ps aux | grep ollama

# إعادة تشغيل الخدمة (Linux)
sudo systemctl restart ollama

# التحقق من المنفذ
netstat -tlnp | grep 11434
```

### 2. بطء في الأداء

**الحلول:**
- استخدم نموذج أصغر (7B بدلاً من 13B/34B)
- قلل `num_predict` (عدد الرموز المولدة)
- فعّل GPU إذا كان متاحاً
- أغلق التطبيقات الأخرى المستهلكة للذاكرة

### 3. نفاد الذاكرة (Out of Memory)

```bash
# استخدم نموذج أصغر
ollama pull phi  # ~1.6GB فقط

# أو استخدم النسخة المُكَمَّمة (quantized)
ollama pull llama2:7b-q4_0  # نسخة مضغوطة

# زيادة Swap (Linux)
sudo fallocate -l 8G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
```

### 4. النموذج لا يستجيب بالعربية

```bash
# استخدم prompt واضح
ollama run llama3.2 "أجب بالعربية: ما هو الذكاء الاصطناعي؟"

# أو في System Prompt
SYSTEM أنت مساعد ذكي تتحدث العربية بطلاقة.
```

---

## 💡 نصائح للاستخدام الأمثل

### اختيار النموذج المناسب:

| المهمة | النموذج الموصى به | الحجم |
|--------|-------------------|-------|
| محادثة عامة | llama3.2 | 2GB |
| كتابة أكواد | codellama | 4GB |
| تحليل صور | llava | 5GB |
| مهام سريعة | phi | 1.6GB |
| تحليل معقد | mixtral | 26GB |

### معاملات مهمة:

- **temperature**: 0 = دقيق ومُحدد، 1 = إبداعي ومتنوع
- **top_p**: يتحكم في تنوع الإجابات (0.9 موصى به)
- **num_predict**: عدد الرموز المولدة (2048 للنصوص القصيرة)

---

## 📚 موارد إضافية

### الوثائق الرسمية:
- [موقع Ollama الرسمي](https://ollama.ai)
- [GitHub Repository](https://github.com/ollama/ollama)
- [قائمة النماذج](https://ollama.ai/library)
- [API Documentation](https://github.com/ollama/ollama/blob/main/docs/api.md)

### المجتمع:
- [Discord](https://discord.gg/ollama)
- [Reddit](https://reddit.com/r/ollama)
- [GitHub Discussions](https://github.com/ollama/ollama/discussions)

---

## 🎓 أمثلة متقدمة مع N8N Pro Enhanced

### مثال 1: روبوت دعم فني ذكي

```
[Webhook: استقبال استفسار العميل]
    ↓
[AI Text Analyzer: تحليل المشكلة]
    ↓
[Condition: نوع المشكلة]
    ↓
[Ollama AI: توليد حل مفصل]
    ↓
[Email Send: إرسال الحل للعميل]
```

### مثال 2: محلل محتوى تلقائي

```
[Schedule: كل ساعة]
    ↓
[HTTP Request: جلب المقالات الجديدة]
    ↓
[AI Text Analyzer: استخراج الكلمات المفتاحية]
    ↓
[AI Data Enrichment: توليد ملخص]
    ↓
[MySQL Query: حفظ في قاعدة البيانات]
```

### مثال 3: مولد أكواد تلقائي

```
[Webhook: استقبال المتطلبات]
    ↓
[AI Code Generator: كتابة الكود]
    ↓
[AI Code Generator: توليد Tests]
    ↓
[File Write: حفظ الملفات]
    ↓
[HTTP Request: إرسال إشعار]
```

---

<div dir="rtl" align="center">

## 🎉 الآن أنت جاهز!

**لديك الآن قوة الذكاء الاصطناعي على جهازك الخاص**

بدون تكاليف • بدون حدود • خصوصية كاملة

**ابدأ في بناء workflows ذكية الآن! 🚀**

</div>

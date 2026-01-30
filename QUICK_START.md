# ⚡ N8N Pro - Quick Start Guide

Get up and running with N8N Pro in less than 5 minutes!

## 📦 Step 1: Prerequisites

Before you begin, make sure you have:
- ✅ PHP 7.4 or higher installed
- ✅ MySQL 5.7 or higher installed
- ✅ Apache or Nginx web server
- ✅ Basic knowledge of web servers

## 🚀 Step 2: Installation

### Option A: Using Git

```bash
# Clone the repository
git clone https://github.com/your-repo/n8n-pro.git
cd n8n-pro

# Create the database
mysql -u root -p -e "CREATE DATABASE n8n_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Option B: Manual Download

1. Download the ZIP file
2. Extract to your web server directory
3. Create database manually

## ⚙️ Step 3: Configuration

Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'n8n_pro');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

## 🎯 Step 4: Run Installation

1. Open your browser
2. Navigate to: `http://localhost/n8n-pro/install.php`
3. Click "Start Installation"
4. Wait for completion
5. Click "Go to Dashboard"

## 🎨 Step 5: Create Your First Workflow

1. **Add Nodes**: Drag nodes from the left sidebar
2. **Connect**: Click "Connect" button on a node, then click another node
3. **Configure**: Click "Settings" on each node to configure
4. **Save**: Click "Save" or press Ctrl+S
5. **Execute**: Click "Execute" or press Ctrl+Enter

## 🔧 Common Tasks

### Create a Simple HTTP Workflow

```
[Webhook] --> [HTTP Request] --> [Transform] --> [Email Send]
```

1. Add Webhook node, set path to `my-webhook`
2. Add HTTP Request node, configure URL
3. Add Transform node to process data
4. Add Email Send node for notifications

### Test Your Webhook

```bash
curl -X POST http://localhost/n8n-pro/api/webhook.php/my-webhook \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

## ⌨️ Keyboard Shortcuts

- `Ctrl+S` - Save workflow
- `Ctrl+Enter` - Execute workflow
- `Delete` - Delete selected node
- `Escape` - Cancel connection mode

## 🐛 Troubleshooting

### Database Connection Error
- Check your credentials in `config.php`
- Ensure MySQL is running
- Verify database exists

### Nodes Not Showing
- Check file permissions on `nodes/` directory
- Verify all node files are present

### Execution Fails
- Check execution logs (click Execute button)
- Verify node configurations
- Check PHP error logs

## 📚 Next Steps

- Read the full [README.md](README.md) for detailed documentation
- Explore available nodes
- Create custom nodes
- Set up webhooks for external integrations

## 🆘 Need Help?

- Check our [Documentation](README.md)
- Open an [Issue](https://github.com/your-repo/issues)
- Join our [Community](https://discord.gg/your-invite)

---

**Happy Automating! 🚀**

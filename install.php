<?php
/**
 * N8N Pro - Installation Script
 * Quick setup for database and initial configuration
 */

require_once 'includes/config.php';
require_once 'includes/database.php';

$installed = false;
$error = null;
$success = null;

// Check if already installed
if (isset($_GET['check'])) {
    try {
        $db = Database::getInstance();
        $result = $db->query("SHOW TABLES LIKE 'workflows'");
        if (!empty($result)) {
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        // Not installed yet
    }
}

// Handle installation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Test database connection
        $db = Database::getInstance();
        
        // Initialize schema
        Database::initSchema();
        
        $success = "Installation completed successfully! You can now start using N8N Pro.";
        $installed = true;
        
    } catch (Exception $e) {
        $error = "Installation failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>N8N Pro - Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen flex items-center justify-center p-6">
    
    <div class="max-w-2xl w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mb-4 shadow-xl">
                <i class="fas fa-bolt text-white text-4xl"></i>
            </div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                N8N Pro
            </h1>
            <p class="text-gray-600 text-lg">Advanced Workflow Automation Platform</p>
            <p class="text-sm text-gray-500 mt-1">Version <?php echo APP_VERSION; ?></p>
        </div>

        <!-- Installation Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            
            <?php if (!$installed): ?>
                
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-rocket text-blue-600"></i>
                        Quick Installation
                    </h2>
                    <p class="text-gray-600">Set up your N8N Pro instance in seconds</p>
                </div>

                <!-- System Requirements -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        System Requirements
                    </h3>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li><i class="fas fa-chevron-right text-blue-500 mr-2"></i>PHP >= 7.4</li>
                        <li><i class="fas fa-chevron-right text-blue-500 mr-2"></i>MySQL >= 5.7</li>
                        <li><i class="fas fa-chevron-right text-blue-500 mr-2"></i>PDO MySQL Extension</li>
                        <li><i class="fas fa-chevron-right text-blue-500 mr-2"></i>cURL Extension</li>
                    </ul>
                </div>

                <!-- Current Configuration -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-cog"></i>
                        Current Configuration
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Database Host:</span>
                            <span class="font-mono font-semibold text-gray-900"><?php echo DB_HOST; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Database Name:</span>
                            <span class="font-mono font-semibold text-gray-900"><?php echo DB_NAME; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Database User:</span>
                            <span class="font-mono font-semibold text-gray-900"><?php echo DB_USER; ?></span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">
                        <i class="fas fa-info-circle"></i>
                        To change these settings, edit <code class="bg-white px-2 py-1 rounded">includes/config.php</code>
                    </p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-circle text-red-600 text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <h4 class="font-bold text-red-900 mb-1">Installation Error</h4>
                                <p class="text-red-700 text-sm"><?php echo htmlspecialchars($error); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Installation Instructions -->
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h4 class="font-bold text-yellow-900 mb-2 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        Before Installation
                    </h4>
                    <ol class="list-decimal list-inside space-y-1 text-sm text-yellow-800">
                        <li>Make sure you have created the MySQL database: <strong><?php echo DB_NAME; ?></strong></li>
                        <li>Ensure the database user has proper permissions</li>
                        <li>Check that all required PHP extensions are enabled</li>
                    </ol>
                </div>

                <!-- Install Button -->
                <form method="POST">
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold py-4 px-6 rounded-xl hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02] flex items-center justify-center gap-3">
                        <i class="fas fa-play-circle text-2xl"></i>
                        <span class="text-lg">Start Installation</span>
                    </button>
                </form>

            <?php else: ?>
                
                <!-- Success Message -->
                <div class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                        <i class="fas fa-check-circle text-green-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Installation Successful!</h2>
                    <p class="text-gray-600 mb-6"><?php echo $success; ?></p>
                    
                    <!-- Next Steps -->
                    <div class="bg-blue-50 rounded-lg p-6 mb-6 text-left">
                        <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-list-check"></i>
                            Next Steps
                        </h3>
                        <ol class="space-y-2 text-sm text-blue-800">
                            <li class="flex items-start gap-2">
                                <span class="font-bold">1.</span>
                                <span>Click the button below to access your dashboard</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-bold">2.</span>
                                <span>Create your first workflow by dragging nodes</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-bold">3.</span>
                                <span>Configure nodes and connect them together</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-bold">4.</span>
                                <span>Execute and monitor your workflows</span>
                            </li>
                        </ol>
                    </div>

                    <a href="index.php" class="inline-flex items-center gap-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold py-4 px-8 rounded-xl hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                        <i class="fas fa-arrow-right text-xl"></i>
                        <span class="text-lg">Go to Dashboard</span>
                    </a>
                </div>

            <?php endif; ?>

        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-600 text-sm">
            <p>
                <i class="fas fa-code"></i>
                Built with ❤️ for the automation community
            </p>
            <p class="mt-2">
                <a href="https://github.com" class="text-blue-600 hover:text-blue-700 mx-2">
                    <i class="fab fa-github"></i> GitHub
                </a>
                |
                <a href="#" class="text-blue-600 hover:text-blue-700 mx-2">
                    <i class="fas fa-book"></i> Documentation
                </a>
                |
                <a href="#" class="text-blue-600 hover:text-blue-700 mx-2">
                    <i class="fas fa-life-ring"></i> Support
                </a>
            </p>
        </div>
    </div>

</body>
</html>

<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Advanced Workflow Automation</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        * {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .workflow-canvas {
            background: linear-gradient(to right, #f3f4f6 1px, transparent 1px),
                        linear-gradient(to bottom, #f3f4f6 1px, transparent 1px);
            background-size: 25px 25px;
            background-color: #fafafa;
            min-height: 700px;
            position: relative;
            overflow: auto;
            cursor: grab;
        }
        
        .workflow-canvas:active {
            cursor: grabbing;
        }
        
        .node {
            position: absolute;
            background: white;
            border: 2px solid #3b82f6;
            border-radius: 12px;
            padding: 16px;
            min-width: 220px;
            max-width: 280px;
            cursor: move;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            z-index: 10;
        }
        
        .node:hover {
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
            border-color: #2563eb;
            z-index: 20;
        }
        
        .node.selected {
            border-color: #8b5cf6;
            border-width: 3px;
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.4);
            z-index: 30;
        }
        
        .node.dragging {
            opacity: 0.8;
            cursor: grabbing;
            z-index: 100;
        }
        
        .node.connecting {
            border-color: #10b981;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        .node-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: move;
        }
        
        .node-icon {
            font-size: 20px;
        }
        
        .node-type-badge {
            display: inline-block;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 12px;
            background: #f3f4f6;
            color: #6b7280;
            font-weight: 500;
        }
        
        .connection-line {
            stroke: #3b82f6;
            stroke-width: 2.5;
            fill: none;
            marker-end: url(#arrowhead);
            transition: stroke 0.2s;
            pointer-events: stroke;
        }
        
        .connection-line:hover {
            stroke: #8b5cf6;
            stroke-width: 3;
            cursor: pointer;
        }
        
        .palette-item {
            padding: 14px;
            margin-bottom: 8px;
            background: white;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .palette-item:hover {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            border-color: transparent;
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .palette-item .icon {
            font-size: 18px;
            margin-right: 8px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 28px;
            max-width: 700px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s;
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(40px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:active {
            transform: scale(0.95);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }
        
        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-success:hover {
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-danger:hover {
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 16px;
            transition: all 0.2s;
            font-size: 14px;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        
        .form-textarea {
            min-height: 120px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            resize: vertical;
        }
        
        .notification {
            position: fixed;
            top: 24px;
            right: 24px;
            padding: 14px 24px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            z-index: 2000;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            animation: slideInRight 0.3s;
            max-width: 400px;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .logs-container {
            background: #1f2937;
            color: #10b981;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 500px;
            overflow-y: auto;
            line-height: 1.6;
        }
        
        .log-entry {
            margin-bottom: 8px;
            padding: 6px;
            border-left: 3px solid transparent;
        }
        
        .log-entry.info { color: #60a5fa; border-color: #60a5fa; }
        .log-entry.success { color: #34d399; border-color: #34d399; }
        .log-entry.error { color: #f87171; border-color: #f87171; }
        
        .sidebar {
            height: calc(100vh - 80px);
            overflow-y: auto;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        .zoom-controls {
            position: absolute;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 8px;
            z-index: 50;
        }
        
        .zoom-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: white;
            border: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .zoom-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bolt text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                <?php echo APP_NAME; ?>
                            </h1>
                            <p class="text-xs text-gray-500">Advanced Workflow Automation v<?php echo APP_VERSION; ?></p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="newWorkflow()" class="btn btn-secondary">
                        <i class="fas fa-plus"></i>
                        <span>New Project</span>
                    </button>
                    <button onclick="saveWorkflow()" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span>Save</span>
                    </button>
                    <button onclick="executeWorkflow()" class="btn btn-success">
                        <i class="fas fa-play"></i>
                        <span>Execute</span>
                    </button>
                    <button onclick="showWorkflowsList()" class="btn btn-secondary">
                        <i class="fas fa-folder-open"></i>
                        <span>Projects</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex h-screen">
        
        <!-- Sidebar - Node Palette -->
        <div class="w-80 bg-white border-r border-gray-200 p-6 sidebar">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-puzzle-piece text-blue-600"></i>
                    Available Nodes
                </h3>
                <input type="text" id="searchNodes" placeholder="Search nodes..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       onkeyup="filterNodes()">
            </div>
            <div id="nodePalette"></div>
        </div>

        <!-- Main Canvas Area -->
        <div class="flex-1 p-6 overflow-hidden">
            <div class="bg-white rounded-xl shadow-lg p-6 h-full flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex-1">
                        <input type="text" id="workflowName" placeholder="Project Name" 
                               class="text-2xl font-bold border-none outline-none focus:ring-0 w-full" 
                               value="New Workflow">
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-info-circle"></i>
                            Drag nodes from the sidebar and connect them to create your workflow
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="clearCanvas()" class="btn btn-danger">
                            <i class="fas fa-trash"></i>
                            <span>Clear All</span>
                        </button>
                        <button onclick="autoArrangeNodes()" class="btn btn-secondary">
                            <i class="fas fa-sitemap"></i>
                            <span>Auto-Arrange</span>
                        </button>
                    </div>
                </div>
                
                <div class="workflow-canvas rounded-lg flex-1 relative" id="workflowCanvas">
                    <svg id="connectionsSvg" class="absolute inset-0 w-full h-full pointer-events-none" style="pointer-events: none;">
                        <defs>
                            <marker id="arrowhead" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto">
                                <polygon points="0 0, 10 3, 0 6" fill="#3b82f6" />
                            </marker>
                        </defs>
                    </svg>
                    
                    <!-- Zoom Controls -->
                    <div class="zoom-controls">
                        <button class="zoom-btn" onclick="zoomIn()" title="Zoom In">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="zoom-btn" onclick="zoomReset()" title="Reset Zoom">
                            <i class="fas fa-compress"></i>
                        </button>
                        <button class="zoom-btn" onclick="zoomOut()" title="Zoom Out">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Node Configuration Modal -->
    <div id="nodeConfigModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-cog text-blue-600"></i>
                    Node Configuration
                </h3>
                <button onclick="closeNodeConfig()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <div id="nodeConfigForm"></div>
            <div class="flex gap-3 mt-8">
                <button onclick="saveNodeConfig()" class="btn btn-primary flex-1">
                    <i class="fas fa-check"></i>
                    <span>Save Changes</span>
                </button>
                <button onclick="deleteNode()" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    <span>Delete</span>
                </button>
                <button onclick="closeNodeConfig()" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    <span>Cancel</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Workflows List Modal -->
    <div id="workflowsListModal" class="modal">
        <div class="modal-content" style="max-width: 900px;">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-folder-open text-blue-600"></i>
                    Saved Projects
                </h3>
                <button onclick="closeWorkflowsList()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <div id="workflowsList"></div>
            <div class="flex gap-3 mt-6">
                <button onclick="closeWorkflowsList()" class="btn btn-secondary flex-1">
                    <i class="fas fa-times"></i>
                    <span>Close</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Execution Logs Modal -->
    <div id="logsModal" class="modal">
        <div class="modal-content" style="max-width: 1000px;">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-terminal text-green-600"></i>
                    Execution Logs
                </h3>
                <button onclick="closeLogs()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <div id="logsContent" class="logs-container"></div>
            <div class="flex gap-3 mt-6">
                <button onclick="closeLogs()" class="btn btn-secondary flex-1">
                    <i class="fas fa-times"></i>
                    <span>Close</span>
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/app.js"></script>
</body>
</html>

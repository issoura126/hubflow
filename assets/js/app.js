// N8N Pro - Advanced Workflow Automation
// Enhanced Frontend Application with Fixed Drag & Drop

let nodes = [];
let connections = [];
let availableNodeTypes = {};
let selectedNode = null;
let connectingFrom = null;
let currentWorkflowId = null;
let nodeIdCounter = 1;
let canvasOffset = { x: 0, y: 0 };
let canvasZoom = 1;
let isDraggingCanvas = false;
let canvasStartPos = { x: 0, y: 0 };

const API_BASE = 'api';

// Initialize application
document.addEventListener('DOMContentLoaded', async () => {
    await loadAvailableNodes();
    renderNodePalette();
    setupCanvasEvents();
    showNotification('Welcome to N8N Pro! 🚀', 'success');
});

/**
 * Load available node types from API
 */
async function loadAvailableNodes() {
    try {
        const response = await fetch(`${API_BASE}/nodes.php`);
        const data = await response.json();
        availableNodeTypes = data.nodes;
        console.log('Loaded nodes:', availableNodeTypes);
    } catch (error) {
        console.error('Failed to load nodes:', error);
        showNotification('Failed to load available nodes', 'error');
    }
}

/**
 * Render node palette
 */
function renderNodePalette() {
    const palette = document.getElementById('nodePalette');
    palette.innerHTML = '';
    
    // Group nodes by category
    const categories = {};
    for (const [type, info] of Object.entries(availableNodeTypes)) {
        const category = info.category || 'general';
        if (!categories[category]) {
            categories[category] = [];
        }
        categories[category].push({ type, ...info });
    }
    
    // Render categories
    for (const [category, nodeList] of Object.entries(categories)) {
        const categoryDiv = document.createElement('div');
        categoryDiv.className = 'mb-6';
        
        const categoryTitle = document.createElement('h4');
        categoryTitle.className = 'font-bold text-sm text-gray-600 mb-3 flex items-center gap-2';
        categoryTitle.innerHTML = `
            <i class="fas fa-layer-group text-blue-500"></i>
            <span>${translateCategory(category)}</span>
        `;
        categoryDiv.appendChild(categoryTitle);
        
        nodeList.forEach(node => {
            const item = document.createElement('div');
            item.className = 'palette-item';
            item.dataset.type = node.type;
            item.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="icon">${node.icon || '📦'}</span>
                        <div>
                            <div class="font-semibold text-sm">${node.name}</div>
                            <div class="text-xs opacity-70 mt-1">${node.description || ''}</div>
                        </div>
                    </div>
                    <i class="fas fa-plus text-sm opacity-50"></i>
                </div>
            `;
            item.onclick = () => addNodeToCanvas(node.type);
            categoryDiv.appendChild(item);
        });
        
        palette.appendChild(categoryDiv);
    }
}

/**
 * Filter nodes in palette
 */
function filterNodes() {
    const searchTerm = document.getElementById('searchNodes').value.toLowerCase();
    const items = document.querySelectorAll('.palette-item');
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Translate category names
 */
function translateCategory(category) {
    const translations = {
        'triggers': 'Triggers',
        'actions': 'Actions',
        'code': 'Code',
        'files': 'Files',
        'communication': 'Communication',
        'utility': 'Utilities',
        'logic': 'Logic',
        'data': 'Data',
        'general': 'General',
        'database': 'Database'
    };
    return translations[category] || category.charAt(0).toUpperCase() + category.slice(1);
}

/**
 * Add node to canvas
 */
function addNodeToCanvas(type) {
    const nodeInfo = availableNodeTypes[type];
    if (!nodeInfo) return;
    
    const canvas = document.getElementById('workflowCanvas');
    const canvasRect = canvas.getBoundingClientRect();
    
    // Calculate position with better distribution
    const x = 100 + (nodes.length * 40) % 600;
    const y = 100 + Math.floor(nodes.length / 15) * 120;
    
    const node = {
        id: `node_${nodeIdCounter++}`,
        type: type,
        name: nodeInfo.name,
        icon: nodeInfo.icon || '📦',
        x: x,
        y: y,
        config: {}
    };
    
    nodes.push(node);
    renderCanvas();
    showNotification(`Added: ${nodeInfo.name}`, 'success');
}

/**
 * Render canvas
 */
function renderCanvas() {
    const canvas = document.getElementById('workflowCanvas');
    
    // Remove existing node elements
    const existingNodes = canvas.querySelectorAll('.node');
    existingNodes.forEach(node => node.remove());
    
    // Render nodes
    nodes.forEach(node => {
        const nodeEl = createNodeElement(node);
        canvas.appendChild(nodeEl);
    });
    
    // Render connections
    renderConnections();
}

/**
 * Create node DOM element
 */
function createNodeElement(node) {
    const nodeEl = document.createElement('div');
    nodeEl.className = 'node';
    nodeEl.id = `node-${node.id}`;
    nodeEl.style.left = `${node.x}px`;
    nodeEl.style.top = `${node.y}px`;
    
    const nodeInfo = availableNodeTypes[node.type];
    
    nodeEl.innerHTML = `
        <div class="node-header">
            <span class="node-icon">${node.icon}</span>
            <span class="flex-1">${node.name}</span>
        </div>
        <div class="text-xs text-gray-500 mb-3">
            <span class="node-type-badge">${node.type}</span>
        </div>
        <div class="flex gap-2">
            <button class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-100 transition"
                    onclick="event.stopPropagation(); openNodeConfig('${node.id}')">
                <i class="fas fa-cog"></i> Settings
            </button>
            <button class="flex-1 px-3 py-2 bg-green-50 text-green-600 rounded-lg text-xs font-medium hover:bg-green-100 transition"
                    onclick="event.stopPropagation(); startConnection('${node.id}')">
                <i class="fas fa-link"></i> Connect
            </button>
        </div>
    `;
    
    // Make draggable - FIXED VERSION
    makeNodeDraggable(nodeEl, node);
    
    // Click to select
    nodeEl.onclick = (e) => {
        if (e.target === nodeEl || e.target.closest('.node-header')) {
            selectNode(node.id);
        }
    };
    
    return nodeEl;
}

/**
 * Make node draggable - ENHANCED WITH FIXES
 */
function makeNodeDraggable(nodeEl, node) {
    let isDragging = false;
    let startX, startY, initialX, initialY;
    
    const header = nodeEl.querySelector('.node-header');
    
    header.style.cursor = 'move';
    
    const onMouseDown = (e) => {
        if (e.button !== 0) return; // Only left click
        
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        initialX = node.x;
        initialY = node.y;
        
        nodeEl.classList.add('dragging');
        nodeEl.style.zIndex = '100';
        
        // Prevent text selection during drag
        e.preventDefault();
        
        selectNode(node.id);
    };
    
    const onMouseMove = (e) => {
        if (!isDragging) return;
        
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        
        node.x = Math.max(0, initialX + dx);
        node.y = Math.max(0, initialY + dy);
        
        nodeEl.style.left = `${node.x}px`;
        nodeEl.style.top = `${node.y}px`;
        
        renderConnections();
    };
    
    const onMouseUp = () => {
        if (!isDragging) return;
        
        isDragging = false;
        nodeEl.classList.remove('dragging');
        nodeEl.style.zIndex = '';
    };
    
    // Attach events to header only
    header.addEventListener('mousedown', onMouseDown);
    
    // Attach global move and up handlers
    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
    
    // Store cleanup function
    nodeEl._cleanupDrag = () => {
        header.removeEventListener('mousedown', onMouseDown);
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
    };
}

/**
 * Select node
 */
function selectNode(nodeId) {
    selectedNode = nodeId;
    
    document.querySelectorAll('.node').forEach(n => {
        n.classList.remove('selected');
    });
    
    const nodeEl = document.getElementById(`node-${nodeId}`);
    if (nodeEl) {
        nodeEl.classList.add('selected');
    }
}

/**
 * Start connection process
 */
function startConnection(fromNodeId) {
    if (!connectingFrom) {
        connectingFrom = fromNodeId;
        showNotification('Select target node to connect', 'info');
        
        const fromEl = document.getElementById(`node-${fromNodeId}`);
        if (fromEl) {
            fromEl.classList.add('connecting');
        }
        
        // Highlight possible targets
        document.querySelectorAll('.node').forEach(n => {
            if (n.id !== `node-${fromNodeId}`) {
                n.style.borderColor = '#10b981';
                n.style.opacity = '0.8';
            }
        });
    } else {
        // Create connection
        if (fromNodeId !== connectingFrom) {
            // Check if connection already exists
            const exists = connections.some(c => 
                c.from === connectingFrom && c.to === fromNodeId
            );
            
            if (!exists) {
                connections.push({
                    from: connectingFrom,
                    to: fromNodeId
                });
                renderConnections();
                showNotification('Connection created successfully', 'success');
            } else {
                showNotification('Connection already exists', 'warning');
            }
        }
        
        // Reset connecting state
        const fromEl = document.getElementById(`node-${connectingFrom}`);
        if (fromEl) {
            fromEl.classList.remove('connecting');
        }
        
        connectingFrom = null;
        
        document.querySelectorAll('.node').forEach(n => {
            n.style.borderColor = '';
            n.style.opacity = '';
        });
    }
}

/**
 * Render connections between nodes
 */
function renderConnections() {
    const svg = document.getElementById('connectionsSvg');
    const canvas = document.getElementById('workflowCanvas');
    const canvasRect = canvas.getBoundingClientRect();
    
    // Clear existing paths
    svg.querySelectorAll('path').forEach(p => p.remove());
    
    connections.forEach(conn => {
        const fromNode = nodes.find(n => n.id === conn.from);
        const toNode = nodes.find(n => n.id === conn.to);
        
        if (!fromNode || !toNode) return;
        
        const fromEl = document.getElementById(`node-${conn.from}`);
        const toEl = document.getElementById(`node-${conn.to}`);
        
        if (!fromEl || !toEl) return;
        
        const fromRect = fromEl.getBoundingClientRect();
        const toRect = toEl.getBoundingClientRect();
        
        // Calculate connection points
        const x1 = fromRect.left + fromRect.width - canvasRect.left + canvas.scrollLeft;
        const y1 = fromRect.top + fromRect.height / 2 - canvasRect.top + canvas.scrollTop;
        const x2 = toRect.left - canvasRect.left + canvas.scrollLeft;
        const y2 = toRect.top + toRect.height / 2 - canvasRect.top + canvas.scrollTop;
        
        // Create curved path
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        const midX = (x1 + x2) / 2;
        const d = `M ${x1} ${y1} C ${midX} ${y1}, ${midX} ${y2}, ${x2} ${y2}`;
        
        path.setAttribute('d', d);
        path.setAttribute('class', 'connection-line');
        path.style.pointerEvents = 'stroke';
        path.style.cursor = 'pointer';
        
        // Click to delete connection
        path.onclick = () => deleteConnection(conn);
        
        svg.appendChild(path);
    });
}

/**
 * Delete a connection
 */
function deleteConnection(conn) {
    if (confirm('Delete this connection?')) {
        connections = connections.filter(c => 
            !(c.from === conn.from && c.to === conn.to)
        );
        renderConnections();
        showNotification('Connection deleted', 'success');
    }
}

/**
 * Open node configuration modal
 */
function openNodeConfig(nodeId) {
    selectedNode = nodeId;
    const node = nodes.find(n => n.id === nodeId);
    if (!node) return;
    
    const nodeInfo = availableNodeTypes[node.type];
    const form = document.getElementById('nodeConfigForm');
    
    form.innerHTML = `
        <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <label class="form-label text-blue-800">Node Type</label>
            <div class="flex items-center gap-2 text-blue-900 font-semibold">
                <span class="text-2xl">${node.icon}</span>
                <span>${nodeInfo.name}</span>
            </div>
            <p class="text-xs text-blue-600 mt-2">${nodeInfo.description || ''}</p>
        </div>
    `;
    
    // Render config fields
    (nodeInfo.config || []).forEach(field => {
        const value = node.config[field.name] || field.default || '';
        
        form.innerHTML += `
            <div class="mb-5">
                <label class="form-label">
                    ${field.label || field.name}
                    ${field.required ? '<span class="text-red-500">*</span>' : ''}
                </label>
                ${field.description ? `<p class="text-xs text-gray-500 mb-2">${field.description}</p>` : ''}
                ${renderConfigField(field, value)}
            </div>
        `;
    });
    
    document.getElementById('nodeConfigModal').classList.add('active');
}

/**
 * Render configuration field based on type
 */

// Normalize node config field options to a consistent array of {value,label}
function normalizeFieldOptions(options) {
    if (!options) return [];
    // Array of primitives or objects
    if (Array.isArray(options)) {
        if (options.length && typeof options[0] === 'object' && options[0] !== null) {
            return options.map(o => {
                const value = (o.value ?? o.id ?? o.key ?? o.name ?? '').toString();
                const label = (o.label ?? o.name ?? o.value ?? value).toString();
                return { value, label };
            }).filter(o => o.value.length > 0);
        }
        return options.map(v => {
            const s = v?.toString?.() ?? String(v);
            return { value: s, label: s };
        }).filter(o => o.value.length > 0);
    }
    // Comma-separated string
    if (typeof options === 'string') {
        return options.split(',').map(s => s.trim()).filter(Boolean).map(s => ({ value: s, label: s }));
    }
    // Object map
    if (typeof options === 'object') {
        return Object.entries(options).map(([k, v]) => ({ value: String(k), label: String(v) }));
    }
    return [];
}

function renderConfigField(field, value) {
    const dataAttr = `data-field="${field.name}"`;
    
    switch (field.type) {
        case 'select': {
    const opts = normalizeFieldOptions(field.options);
    let html = `<select class="form-select" ${dataAttr}>`;
    opts.forEach(({ value: optValue, label: optLabel }) => {
        const isSelected = String(value ?? '') === String(optValue);
        html += `<option value="${optValue}" ${isSelected ? 'selected' : ''}>${optLabel}</option>`;
    });
    html += '</select>';
    return html;
}
case 'multiselect':
            // Handle multiselect with checkboxes
            let multiselectHtml = '<div class="multiselect-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px;">';
            const selectedValues = (Array.isArray(value) ? value : (value ? [value] : [])).map(v => String(v));
            
            if (typeof field.options === 'object' && !Array.isArray(field.options)) {
                // Options as key-value pairs
                Object.entries(field.options).forEach(([key, label]) => {
                    const isChecked = selectedValues.includes(String(key));
                    multiselectHtml += `
                        <label class="flex items-center gap-2 mb-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                            <input type="checkbox" 
                                   class="multiselect-option" 
                                   ${dataAttr} 
                                   value="${key}" 
                                   ${isChecked ? 'checked' : ''}
                                   style="width: 16px; height: 16px;">
                            <span class="text-sm">${label}</span>
                        </label>
                    `;
                });
            } else {
                // Options as array
                normalizeFieldOptions(field.options).forEach(({ value: opt, label: optLabel }) => {
                    const isChecked = selectedValues.includes(String(opt));
                    multiselectHtml += `
                        <label class="flex items-center gap-2 mb-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                            <input type="checkbox" 
                                   class="multiselect-option" 
                                   ${dataAttr} 
                                   value="${opt}" 
                                   ${isChecked ? 'checked' : ''}
                                   style="width: 16px; height: 16px;">
                            <span class="text-sm">${optLabel}</span>
                        </label>
                    `;
                });
            }
            multiselectHtml += '</div>';
            return multiselectHtml;
            
        case 'textarea':
        case 'code':
        case 'text':
        case 'json':
            return `<textarea class="form-textarea" ${dataAttr} placeholder="${field.placeholder || ''}" rows="${field.rows || 4}">${value}</textarea>`;
            
        case 'number':
            return `<input type="number" class="form-input" ${dataAttr} value="${value}" placeholder="${field.placeholder || ''}">`;
            
        default:
            return `<input type="text" class="form-input" ${dataAttr} value="${value}" placeholder="${field.placeholder || ''}">`;
    }
}

/**
 * Save node configuration
 */
function saveNodeConfig() {
    if (!selectedNode) return;
    
    const node = nodes.find(n => n.id === selectedNode);
    if (!node) return;
    
    const form = document.getElementById('nodeConfigForm');
    const inputs = form.querySelectorAll('[data-field]');
    
    node.config = {};
    const processedFields = new Set();
    
    inputs.forEach(input => {
        const fieldName = input.getAttribute('data-field');
        
        // Handle multiselect checkboxes
        if (input.classList.contains('multiselect-option')) {
            if (!processedFields.has(fieldName)) {
                // Get all checked values for this field
                const checkboxes = form.querySelectorAll(`input.multiselect-option[data-field="${fieldName}"]`);
                const selectedValues = [];
                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        selectedValues.push(cb.value);
                    }
                });
                node.config[fieldName] = selectedValues;
                processedFields.add(fieldName);
            }
            return;
        }
        
        // Skip if already processed
        if (processedFields.has(fieldName)) return;
        
        let value = input.value;
        
        // Try to parse JSON
        if (value && typeof value === 'string' && (value.trim().startsWith('{') || value.trim().startsWith('['))) {
            try {
                value = JSON.parse(value);
            } catch (e) {
                // Keep as string
            }
        }
        
        node.config[fieldName] = value;
        processedFields.add(fieldName);
    });
    
    closeNodeConfig();
    renderCanvas();
    showNotification('Node settings saved', 'success');
}

/**
 * Delete node
 */
function deleteNode() {
    if (!selectedNode) return;
    
    if (confirm('Are you sure you want to delete this node?')) {
        // Remove node
        nodes = nodes.filter(n => n.id !== selectedNode);
        
        // Remove connections
        connections = connections.filter(c => 
            c.from !== selectedNode && c.to !== selectedNode
        );
        
        renderCanvas();
        closeNodeConfig();
        showNotification('Node deleted', 'success');
    }
}

/**
 * Close node configuration modal
 */
function closeNodeConfig() {
    document.getElementById('nodeConfigModal').classList.remove('active');
    selectedNode = null;
}

/**
 * Save workflow
 */
async function saveWorkflow() {
    const name = document.getElementById('workflowName').value || 'New Workflow';
    
    if (nodes.length === 0) {
        showNotification('Add at least one node', 'warning');
        return;
    }
    
    const workflow = {
        name: name,
        description: '',
        nodes: nodes,
        connections: connections
    };
    
    try {
        showNotification('Saving...', 'info');
        
        let response;
        if (currentWorkflowId) {
            // Update existing
            response = await fetch(`${API_BASE}/workflows.php/${currentWorkflowId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(workflow)
            });
        } else {
            // Create new
            response = await fetch(`${API_BASE}/workflows.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(workflow)
            });
            
            const data = await response.json();
            currentWorkflowId = data.id;
        }
        
        if (response.ok) {
            showNotification('Workflow saved successfully! ✓', 'success');
        } else {
            throw new Error('Save failed');
        }
    } catch (error) {
        console.error('Failed to save:', error);
        showNotification('Failed to save workflow', 'error');
    }
}

/**
 * Execute workflow
 */
async function executeWorkflow() {
    if (nodes.length === 0) {
        showNotification('Add at least one node', 'warning');
        return;
    }
    
    if (!currentWorkflowId) {
        await saveWorkflow();
    }
    
    if (!currentWorkflowId) {
        showNotification('Must save workflow first', 'error');
        return;
    }
    
    try {
        showNotification('Executing... ⚡', 'info');
        
        const response = await fetch(`${API_BASE}/workflows.php/execute`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                workflow_id: currentWorkflowId,
                input_data: {}
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(`Execution completed! ⏱ ${result.duration}ms`, 'success');
            showLogs(result.logs);
        } else {
            showNotification('Execution failed: ' + result.error, 'error');
            showLogs(result.logs);
        }
    } catch (error) {
        console.error('Execution failed:', error);
        showNotification('Execution failed', 'error');
    }
}

/**
 * Show execution logs
 */
function showLogs(logs) {
    const content = document.getElementById('logsContent');
    
    content.innerHTML = logs.map(log => {
        const levelClass = log.level === 'error' ? 'error' : 
                          log.level === 'success' ? 'success' : 'info';
        
        return `
            <div class="log-entry ${levelClass}">
                <span class="opacity-70">[${log.timestamp}]</span>
                <span class="font-bold">[${log.level.toUpperCase()}]</span>
                <span>${log.message}</span>
            </div>
        `;
    }).join('');
    
    document.getElementById('logsModal').classList.add('active');
}

/**
 * Close logs modal
 */
function closeLogs() {
    document.getElementById('logsModal').classList.remove('active');
}

/**
 * Show workflows list
 */
async function showWorkflowsList() {
    try {
        const response = await fetch(`${API_BASE}/workflows.php`);
        const data = await response.json();
        
        const list = document.getElementById('workflowsList');
        
        if (data.workflows.length === 0) {
            list.innerHTML = `
                <div class="text-center py-16">
                    <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No saved workflows</p>
                    <p class="text-gray-400 text-sm mt-2">Start by creating a new workflow</p>
                </div>
            `;
        } else {
            list.innerHTML = data.workflows.map(wf => `
                <div class="border-2 border-gray-200 rounded-xl p-5 mb-4 hover:border-blue-400 hover:shadow-lg transition cursor-pointer bg-white"
                     onclick="loadWorkflow('${wf.id}')">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-bold text-lg text-gray-800 mb-2">
                                <i class="fas fa-project-diagram text-blue-500"></i>
                                ${wf.name}
                            </h4>
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-clock"></i>
                                ${new Date(wf.created_at).toLocaleString()}
                            </p>
                            ${wf.description ? `<p class="text-sm text-gray-600 mt-2">${wf.description}</p>` : ''}
                        </div>
                        <div class="flex gap-2">
                            <button class="btn btn-primary text-sm px-4 py-2" 
                                    onclick="event.stopPropagation(); loadWorkflow('${wf.id}')">
                                <i class="fas fa-folder-open"></i>
                                Open
                            </button>
                            <button class="btn btn-danger text-sm px-4 py-2" 
                                    onclick="event.stopPropagation(); deleteWorkflowConfirm('${wf.id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        document.getElementById('workflowsListModal').classList.add('active');
    } catch (error) {
        console.error('Failed to load workflows:', error);
        showNotification('Failed to load workflows', 'error');
    }
}

/**
 * Close workflows list modal
 */
function closeWorkflowsList() {
    document.getElementById('workflowsListModal').classList.remove('active');
}

/**
 * Load workflow
 */
async function loadWorkflow(workflowId) {
    try {
        const response = await fetch(`${API_BASE}/workflows.php/${workflowId}`);
        const workflow = await response.json();
        
        currentWorkflowId = workflowId;
        nodes = workflow.data.nodes;
        connections = workflow.data.connections;
        
        document.getElementById('workflowName').value = workflow.name;
        
        renderCanvas();
        closeWorkflowsList();
        showNotification('Workflow loaded successfully', 'success');
    } catch (error) {
        console.error('Failed to load workflow:', error);
        showNotification('Failed to load workflow', 'error');
    }
}

/**
 * Delete workflow (with confirmation)
 */
async function deleteWorkflowConfirm(workflowId) {
    if (!confirm('Are you sure you want to delete this workflow permanently?')) return;
    
    try {
        await fetch(`${API_BASE}/workflows.php/${workflowId}`, {
            method: 'DELETE'
        });
        
        showNotification('Workflow deleted', 'success');
        showWorkflowsList(); // Refresh list
    } catch (error) {
        console.error('Failed to delete workflow:', error);
        showNotification('Failed to delete workflow', 'error');
    }
}

/**
 * Create new workflow
 */
function newWorkflow() {
    if (nodes.length > 0 && !confirm('Create new workflow? Unsaved changes will be lost.')) {
        return;
    }
    
    currentWorkflowId = null;
    nodes = [];
    connections = [];
    document.getElementById('workflowName').value = 'New Workflow';
    renderCanvas();
    showNotification('New workflow created', 'success');
}

/**
 * Clear canvas
 */
function clearCanvas() {
    if (!confirm('Are you sure you want to clear all nodes?')) return;
    
    nodes = [];
    connections = [];
    renderCanvas();
    showNotification('Canvas cleared', 'success');
}

/**
 * Auto-arrange nodes
 */
function autoArrangeNodes() {
    if (nodes.length === 0) return;
    
    // Simple grid layout
    const cols = Math.ceil(Math.sqrt(nodes.length));
    const spacingX = 300;
    const spacingY = 150;
    
    nodes.forEach((node, index) => {
        const col = index % cols;
        const row = Math.floor(index / cols);
        
        node.x = 50 + col * spacingX;
        node.y = 50 + row * spacingY;
    });
    
    renderCanvas();
    showNotification('Nodes auto-arranged', 'success');
}

/**
 * Zoom controls
 */
function zoomIn() {
    canvasZoom = Math.min(canvasZoom + 0.1, 2);
    applyZoom();
}

function zoomOut() {
    canvasZoom = Math.max(canvasZoom - 0.1, 0.5);
    applyZoom();
}

function zoomReset() {
    canvasZoom = 1;
    applyZoom();
}

function applyZoom() {
    const canvas = document.getElementById('workflowCanvas');
    canvas.style.transform = `scale(${canvasZoom})`;
    canvas.style.transformOrigin = 'top left';
    showNotification(`Zoom: ${Math.round(canvasZoom * 100)}%`, 'info');
}

/**
 * Setup canvas events
 */
function setupCanvasEvents() {
    const canvas = document.getElementById('workflowCanvas');
    
    // Click outside to deselect
    canvas.addEventListener('click', (e) => {
        if (e.target === canvas || e.target.id === 'connectionsSvg') {
            selectedNode = null;
            document.querySelectorAll('.node').forEach(n => {
                n.classList.remove('selected');
            });
            
            // Cancel connection mode
            if (connectingFrom) {
                const fromEl = document.getElementById(`node-${connectingFrom}`);
                if (fromEl) {
                    fromEl.classList.remove('connecting');
                }
                connectingFrom = null;
                
                document.querySelectorAll('.node').forEach(n => {
                    n.style.borderColor = '';
                    n.style.opacity = '';
                });
            }
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Delete selected node with Delete key
        if (e.key === 'Delete' && selectedNode) {
            deleteNode();
        }
        
        // Save with Ctrl+S
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            saveWorkflow();
        }
        
        // Execute with Ctrl+Enter
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            executeWorkflow();
        }
        
        // Escape to cancel connection
        if (e.key === 'Escape' && connectingFrom) {
            const fromEl = document.getElementById(`node-${connectingFrom}`);
            if (fromEl) {
                fromEl.classList.remove('connecting');
            }
            connectingFrom = null;
            
            document.querySelectorAll('.node').forEach(n => {
                n.style.borderColor = '';
                n.style.opacity = '';
            });
            showNotification('Connection cancelled', 'info');
        }
    });
    
    // Update connections on scroll
    canvas.addEventListener('scroll', () => {
        renderConnections();
    });
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    const colors = {
        success: 'bg-gradient-to-r from-green-500 to-green-600',
        error: 'bg-gradient-to-r from-red-500 to-red-600',
        info: 'bg-gradient-to-r from-blue-500 to-blue-600',
        warning: 'bg-gradient-to-r from-yellow-500 to-yellow-600'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };
    
    const notification = document.createElement('div');
    notification.className = `notification ${colors[type]} flex items-center gap-3`;
    notification.innerHTML = `
        <i class="fas ${icons[type]} text-xl"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 300);
    }, 3500);
}

/**
 * Update connections when window resizes
 */
window.addEventListener('resize', () => {
    if (nodes.length > 0) {
        renderConnections();
    }
});

// Auto-save every 2 minutes
setInterval(() => {
    if (currentWorkflowId && nodes.length > 0) {
        saveWorkflow();
    }
}, 120000);

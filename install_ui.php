<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت N8N Pro - الإصدار المحسّن</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .step-card { transition: all 0.3s ease; }
        .step-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .success-icon { animation: scaleIn 0.5s ease; }
        @keyframes scaleIn { from { transform: scale(0); } to { transform: scale(1); } }
        .loading { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-12 max-w-5xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold text-gray-800 mb-4">
                <i class="fas fa-robot text-blue-600"></i>
                تثبيت N8N Pro
            </h1>
            <p class="text-xl text-gray-600">نظام أتمتة سير العمل المتقدم - الإصدار المحسّن</p>
            <div class="mt-4 inline-block bg-green-100 text-green-800 px-6 py-2 rounded-full text-sm font-semibold">
                <i class="fas fa-check-circle"></i> تم إصلاح جميع الأخطاء
            </div>
        </div>

        <!-- Installation Steps -->
        <div id="installationSteps" class="space-y-6">
            <!-- Step 1: Database Check -->
            <div class="step-card bg-white rounded-2xl shadow-lg p-8">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-xl">
                        1
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">فحص الاتصال بقاعدة البيانات</h2>
                </div>
                <div id="step1-status" class="mr-16">
                    <button onclick="checkDatabase()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-database"></i> فحص الاتصال
                    </button>
                </div>
            </div>

            <!-- Step 2: Create Database -->
            <div class="step-card bg-white rounded-2xl shadow-lg p-8 opacity-50" id="step2-card">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold text-xl">
                        2
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">إنشاء قاعدة البيانات</h2>
                </div>
                <div id="step2-status" class="mr-16">
                    <button onclick="createDatabase()" disabled class="bg-gray-400 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed" id="createDbBtn">
                        <i class="fas fa-plus-circle"></i> إنشاء قاعدة البيانات
                    </button>
                </div>
            </div>

            <!-- Step 3: Initialize Schema -->
            <div class="step-card bg-white rounded-2xl shadow-lg p-8 opacity-50" id="step3-card">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold text-xl">
                        3
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">تهيئة الجداول</h2>
                </div>
                <div id="step3-status" class="mr-16">
                    <button onclick="initializeSchema()" disabled class="bg-gray-400 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed" id="initSchemaBtn">
                        <i class="fas fa-table"></i> تهيئة الجداول
                    </button>
                </div>
            </div>

            <!-- Step 4: Verify Installation -->
            <div class="step-card bg-white rounded-2xl shadow-lg p-8 opacity-50" id="step4-card">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-600 font-bold text-xl">
                        4
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">التحقق من التثبيت</h2>
                </div>
                <div id="step4-status" class="mr-16">
                    <button onclick="verifyInstallation()" disabled class="bg-gray-400 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed" id="verifyBtn">
                        <i class="fas fa-check-double"></i> التحقق من النظام
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div id="successMessage" class="hidden mt-12 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl shadow-2xl p-8 text-center">
            <i class="fas fa-check-circle text-6xl mb-4 success-icon"></i>
            <h2 class="text-3xl font-bold mb-4">تم التثبيت بنجاح! 🎉</h2>
            <p class="text-xl mb-6">النظام جاهز للاستخدام الآن</p>
            <a href="index.php" class="inline-block bg-white text-green-600 px-8 py-4 rounded-lg font-bold text-lg hover:bg-gray-100 transition">
                <i class="fas fa-rocket"></i> بدء استخدام N8N Pro
            </a>
        </div>

        <!-- Error Log -->
        <div id="errorLog" class="hidden mt-8 bg-red-50 border-2 border-red-200 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-red-800 mb-4">
                <i class="fas fa-exclamation-triangle"></i> سجل الأخطاء
            </h3>
            <div id="errorContent" class="text-red-700 font-mono text-sm"></div>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center text-gray-600">
            <p class="mb-2">N8N Pro v4.2 - Enhanced Edition</p>
            <p class="text-sm">تم إصلاح مشكلة إعدادات العقد وإضافة دعم قاعدة بيانات كامل</p>
        </div>
    </div>

    <script>
        let currentStep = 1;

        function showLoading(elementId, message) {
            document.getElementById(elementId).innerHTML = `
                <div class="flex items-center gap-3 text-blue-600">
                    <i class="fas fa-spinner loading text-2xl"></i>
                    <span class="font-semibold">${message}</span>
                </div>
            `;
        }

        function showSuccess(elementId, message) {
            document.getElementById(elementId).innerHTML = `
                <div class="flex items-center gap-3 text-green-600">
                    <i class="fas fa-check-circle text-2xl success-icon"></i>
                    <span class="font-semibold">${message}</span>
                </div>
            `;
        }

        function showError(elementId, message) {
            document.getElementById(elementId).innerHTML = `
                <div class="flex items-center gap-3 text-red-600">
                    <i class="fas fa-times-circle text-2xl"></i>
                    <span class="font-semibold">${message}</span>
                </div>
            `;
            showErrorLog(message);
        }

        function showErrorLog(error) {
            document.getElementById('errorLog').classList.remove('hidden');
            document.getElementById('errorContent').innerHTML += `
                <div class="mb-2 p-3 bg-white rounded border border-red-300">
                    [${new Date().toLocaleTimeString('ar')}] ${error}
                </div>
            `;
        }

        function enableNextStep(stepNumber) {
            const card = document.getElementById(`step${stepNumber}-card`);
            const btn = document.getElementById(stepNumber === 2 ? 'createDbBtn' : 
                                              stepNumber === 3 ? 'initSchemaBtn' : 
                                              'verifyBtn');
            if (card) card.classList.remove('opacity-50');
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                btn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
            }
        }

        async function checkDatabase() {
            showLoading('step1-status', 'جاري فحص الاتصال...');
            
            try {
                const response = await fetch('install_handler.php?action=check_connection', {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('step1-status', 'تم الاتصال بنجاح!');
                    enableNextStep(2);
                    currentStep = 2;
                } else {
                    showError('step1-status', 'فشل الاتصال: ' + result.error);
                }
            } catch (error) {
                showError('step1-status', 'خطأ في الاتصال: ' + error.message);
            }
        }

        async function createDatabase() {
            showLoading('step2-status', 'جاري إنشاء قاعدة البيانات...');
            
            try {
                const response = await fetch('install_handler.php?action=create_database', {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('step2-status', 'تم إنشاء قاعدة البيانات بنجاح!');
                    enableNextStep(3);
                    currentStep = 3;
                } else {
                    showError('step2-status', 'فشل إنشاء قاعدة البيانات: ' + result.error);
                }
            } catch (error) {
                showError('step2-status', 'خطأ: ' + error.message);
            }
        }

        async function initializeSchema() {
            showLoading('step3-status', 'جاري إنشاء الجداول...');
            
            try {
                const response = await fetch('install_handler.php?action=initialize_schema', {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('step3-status', 'تم إنشاء ' + result.tables_created + ' جداول بنجاح!');
                    enableNextStep(4);
                    currentStep = 4;
                } else {
                    showError('step3-status', 'فشل إنشاء الجداول: ' + result.error);
                }
            } catch (error) {
                showError('step3-status', 'خطأ: ' + error.message);
            }
        }

        async function verifyInstallation() {
            showLoading('step4-status', 'جاري التحقق...');
            
            try {
                const response = await fetch('install_handler.php?action=verify', {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('step4-status', 'التحقق ناجح - النظام جاهز!');
                    document.getElementById('successMessage').classList.remove('hidden');
                    document.getElementById('installationSteps').classList.add('opacity-50');
                } else {
                    showError('step4-status', 'فشل التحقق: ' + result.error);
                }
            } catch (error) {
                showError('step4-status', 'خطأ: ' + error.message);
            }
        }
    </script>
</body>
</html>

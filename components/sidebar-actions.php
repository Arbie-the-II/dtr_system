<div class="lg:col-span-3">
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-4 py-3">
            <h2 class="flex items-center text-lg font-semibold text-white">
                <i class="fas fa-tasks mr-2"></i>
                Actions
            </h2>
        </div>
        <div class="p-4 space-y-3">
            <a href="./auth/internRegistration.php" class="flex items-center justify-between w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-medium py-3 px-4 rounded-lg transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                <span class="flex items-center">
                    <i class="fas fa-user-plus mr-2"></i>
                    Register Intern
                </span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <button type="button" id="face-scanner-button" class="flex items-center justify-between w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium py-3 px-4 rounded-lg transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-lg">
                <span class="flex items-center">
                    <i class="fas fa-camera mr-2"></i>
                    Face Scanner
                </span>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <div class="px-4 pt-2 pb-4">
            <h3 class="flex items-center text-md font-semibold text-gray-700 mb-3 border-b pb-2">
                <i class="fas fa-ellipsis-h text-primary-500 mr-2"></i>
                More Actions
            </h3>
            <div class="space-y-2">
                <button id="delete-button" class="flex items-center justify-between w-full bg-white hover:bg-red-50 text-gray-700 hover:text-red-600 font-medium py-2 px-3 rounded-lg border border-gray-200 hover:border-red-200 transition duration-300 ease-in-out">
                    <span class="flex items-center">
                        <i class="fas fa-trash-alt text-red-500 mr-2"></i>
                        Delete Selected Intern
                    </span>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </button>
                <button onclick="openAdminModal()" class="flex items-center justify-between w-full bg-white hover:bg-orange-50 text-gray-700 hover:text-orange-600 font-medium py-2 px-3 rounded-lg border border-gray-200 hover:border-orange-200 transition duration-300 ease-in-out">
                    <span class="flex items-center">
                        <i class="fas fa-clock text-orange-500 mr-2"></i>
                        No. of Hours per Day
                    </span>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </button>
                <button onclick="openCastPmModal()" class="flex items-center justify-between w-full bg-white hover:bg-yellow-50 text-gray-700 hover:text-yellow-600 font-medium py-2 px-3 rounded-lg border border-gray-200 hover:border-yellow-200 transition duration-300 ease-in-out">
                    <span class="flex items-center">
                        <i class="fas fa-sign-out-alt text-yellow-500 mr-2"></i>
                        Cast PM out
                    </span>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </button>
                <button id="about-us-button" class="flex items-center justify-between w-full bg-white hover:bg-primary-50 text-gray-700 hover:text-primary-600 font-medium py-2 px-3 rounded-lg border border-gray-200 hover:border-primary-200 transition duration-300 ease-in-out">
                    <span class="flex items-center">
                        <i class="fas fa-info-circle text-primary-500 mr-2"></i>
                        About Us
                    </span>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mt-6 transition-all duration-300 hover:shadow-xl">
    
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3">
        <div class="flex items-center">
            <h3 class="text-sm font-bold text-white flex items-center tracking-wide uppercase">
                <i class="fas fa-clock mr-2"></i>
                Daily Requirement
            </h3>
        </div>
    </div>

    <div class="px-6 py-6">
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 mr-4 shadow-sm border border-blue-100">
                <i class="fas fa-stopwatch text-xl"></i>
            </div>
            <div>
    <?php 
        $today = date('Y-m-d');
        $display_limit = 8.0; // The ultimate "Safety" default

        if (isset($conn)) {
            // 1. Check for historical limits active today or earlier
            $stmt = $conn->prepare("SELECT setting_value FROM settings_history 
                                   WHERE setting_key = 'max_daily_hours' 
                                   AND effective_date <= ? 
                                   ORDER BY effective_date DESC LIMIT 1");
            $stmt->execute([$today]);
            $history_val = $stmt->fetchColumn();
            
            if ($history_val !== false) {
                $display_limit = (float)$history_val;
            } else {
                // 2. Fallback: Check the global system settings if history is empty
                $sys_stmt = $conn->prepare("SELECT setting_value FROM system_settings 
                                           WHERE setting_key = 'max_daily_hours' LIMIT 1");
                $sys_stmt->execute();
                $sys_val = $sys_stmt->fetchColumn();
                
                if ($sys_val !== false) {
                    $display_limit = (float)$sys_val;
                }
            }
        }
    ?>
    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Active Limit</p>
    <div class="flex items-baseline gap-1">
        <span class="text-2xl font-black text-gray-800">
            <?php echo ($display_limit == (int)$display_limit) ? (int)$display_limit : number_format($display_limit, 1); ?>
        </span>
        <span class="text-xs font-bold text-gray-500 uppercase">
            <?php echo ($display_limit > 1) ? 'hours' : 'hour'; ?>
        </span>
    </div>
</div>
        </div>
    </div>
</div>
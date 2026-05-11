<div id="adminLimitModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-300 opacity-0" id="adminLimitOverlay">
        
        <div id="adminLimitContent" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-auto overflow-hidden transform transition-all duration-300 scale-90">
            
            <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 py-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-shield-halved mr-3"></i>
                        Change Daily Hour Limit
                    </h3>
                    <button onclick="closeAdminModal()" class="text-white/80 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="px-8 py-8">
                <form id="adminSettingsForm" action="main.php" method="POST">
                    <input type="hidden" name="action" value="update_admin_settings">

                    <div class="mb-5">
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2 ml-1">Effective Date</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-orange-600 transition-colors">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <input type="date" name="effective_date" required value="<?php echo date('Y-m-d'); ?>" 
                                   class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500 focus:bg-white p-3.5 pl-12 rounded-xl transition-all outline-none text-gray-700">
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2 ml-1">New Hour Limit</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-orange-600 transition-colors">
                                <i class="fas fa-clock"></i>
                            </span>
                            <input type="number" step="0.5" name="new_hour_limit" placeholder="8.0" required 
                                   class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500 focus:bg-white p-3.5 pl-12 rounded-xl transition-all outline-none text-gray-700">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2 ml-1">Security PIN</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-orange-600 transition-colors">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="admin_pin" placeholder="••••" required 
                                   class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500 focus:bg-white p-3.5 pl-12 rounded-xl transition-all outline-none text-gray-700">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2 italic px-1">
                            <?php 
                                $check_pin = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'admin_pin_hash'")->fetchColumn();
                                echo empty($check_pin) ? "No PIN detected. The PIN entered now will be saved as your Master PIN." : "Authorize with your administrator PIN.";
                            ?>
                        </p>
                    </div>
                </form>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button type="button" onclick="closeAdminModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2.5 px-6 rounded-xl transition duration-300">
                    Cancel
                </button>
                <button type="submit" form="adminSettingsForm" class="bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-bold py-2.5 px-8 rounded-xl shadow-lg shadow-orange-200 transition duration-300 transform hover:-translate-y-0.5 active:translate-y-0">
                    Confirm Changes
                </button>
            </div>
        </div>
    </div>
</div>
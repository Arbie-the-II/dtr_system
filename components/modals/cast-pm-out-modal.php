<div id="castPmModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4 transition-opacity duration-300 opacity-0" id="castPmOverlay">
        
        <div id="castPmContent" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-auto overflow-hidden transform transition-all duration-300 scale-90 flex flex-col max-h-[95vh]">
            
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-users mr-3"></i>
                        Bulk Cast PM Out
                    </h3>
                    <button type="button" onclick="closeCastPmModal()" class="text-white/80 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-6 overflow-y-auto custom-scrollbar flex-grow">
                <form id="castPmForm" action="main.php" method="POST">
                    <input type="hidden" name="action" value="cast_pm_timeout">

                    <div class="mb-5">
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2 ml-1">Select Intern</label>
                        <div class="relative group mb-3">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-yellow-600">
                                <i class="fas fa-user-plus"></i>
                            </span>
                            <select id="internSelector" onchange="addInternToList()" 
                                    class="w-full bg-gray-50 border-2 border-transparent focus:border-yellow-500 focus:bg-white p-3 pl-11 rounded-xl transition-all outline-none text-gray-700 text-sm">
                                <option value="" selected disabled>Choose an intern...</option>
                                <?php 
                                try {
                                    if (isset($conn)) {
                                        $query = "SELECT Intern_id, Intern_Name FROM interns ORDER BY Intern_Name ASC";
                                        $result = $conn->query($query);
                                        if ($result) {
                                            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='{$row['Intern_id']}' data-name='".htmlspecialchars($row['Intern_Name'])."'>".htmlspecialchars($row['Intern_Name'])."</option>";
                                            }
                                        }
                                    }
                                } catch (Exception $e) {
                                    echo "<option disabled>Error loading interns</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2 ml-1">Selected List</label>
                        <div class="relative">
                            <textarea id="selectedInternsDisplay" readonly 
                                      placeholder="Names will appear here..."
                                      class="w-full bg-gray-100 border-2 border-gray-200 p-3 rounded-xl text-sm text-gray-600 cursor-not-allowed min-h-[80px] outline-none resize-none"></textarea>
                            
                            <input type="hidden" name="intern_id_csv" id="internIdsHidden">
                            
                            <button type="button" onclick="clearInternSelection()" class="text-xs text-red-500 mt-1 hover:underline flex items-center">
                                <i class="fas fa-trash-alt mr-1"></i> Clear Selection
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2 ml-1">Effective Date</label>
                            <input type="date" name="target_date" id="cast_target_date" required value="<?php echo date('Y-m-d'); ?>" 
                                   class="w-full bg-gray-50 border-2 border-transparent focus:border-yellow-500 p-3 rounded-xl outline-none text-sm text-gray-700">
                        </div>
                        <div>
                            <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2 ml-1">Cast Status</label>
                            <select name="is_active" class="w-full bg-gray-50 border-2 border-transparent focus:border-yellow-500 p-3 rounded-xl outline-none text-sm text-gray-700">
                                <option value="1" selected>Active (Apply Limit)</option>
                                <option value="0">Disabled (Return to Default)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2 ml-1">New PM Out</label>
                        <input type="time" name="new_pm_timeout" required 
                               class="w-full bg-gray-50 border-2 border-transparent focus:border-yellow-500 p-3 rounded-xl outline-none text-sm text-gray-700">
                    </div>

                    <div class="mb-2">
                        <label class="block text-gray-500 text-xs font-bold uppercase tracking-widest mb-2 ml-1">Security PIN</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="admin_pin" id="admin_pin_input" placeholder="••••" required 
                                   class="w-full bg-gray-50 border-2 border-transparent focus:border-yellow-500 p-3 pl-11 rounded-xl outline-none text-gray-700">
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 flex flex-wrap justify-end gap-3 flex-shrink-0 border-t">
                <button type="button" onclick="closeCastPmModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-5 rounded-xl transition text-sm">
                    Cancel
                </button>
                
                <!--<button type="button" onclick="submitRemoveCast()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-5 rounded-xl shadow-md transition text-sm">
                    <i class="fas fa-trash-alt mr-2"></i> Remove Cast
                </button>-->

                <button type="submit" form="castPmForm" class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg transition text-sm">
                    Apply Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedInternNames = [];
let selectedInternIds = [];

function addInternToList() {
    const selector = document.getElementById('internSelector');
    const display = document.getElementById('selectedInternsDisplay');
    const hiddenInput = document.getElementById('internIdsHidden');
    
    const selectedOption = selector.options[selector.selectedIndex];
    const id = selectedOption.value;
    const fullName = selectedOption.getAttribute('data-name');

    if (id && !selectedInternIds.includes(id)) {
        let nameParts = fullName.trim().split(' ');
        let displayName = fullName;
        
        if(nameParts.length > 1) {
            let last = nameParts.pop();
            let first = nameParts.join(' ');
            displayName = last + " " + first;
        }

        selectedInternIds.push(id);
        selectedInternNames.push(displayName);

        display.value = selectedInternNames.join(', ');
        hiddenInput.value = selectedInternIds.join(',');
    }
    selector.selectedIndex = 0;
}

function clearInternSelection() {
    selectedInternNames = [];
    selectedInternIds = [];
    document.getElementById('selectedInternsDisplay').value = '';
    document.getElementById('internIdsHidden').value = '';
}

function submitRemoveCast() {
    const form = document.getElementById('castPmForm');
    const actionInput = form.querySelector('input[name="action"]');
    const pinInput = form.querySelector('input[name="admin_pin"]'); // Get PIN input
    const ids = document.getElementById('internIdsHidden').value;

    if (!ids) {
        alert("Please select at least one intern first.");
        return;
    }

    // Require PIN for deletion
    if (!pinInput.value) {
        alert("Please enter the Security PIN to confirm deletion.");
        pinInput.focus();
        return;
    }

    if (confirm("Are you sure you want to PERMANENTLY DELETE these custom limits?")) {
        actionInput.value = "remove_cast_pm"; // This triggers the DELETE block in PHP
        form.submit();
    }
}
// Helper to open/close (if not already handled in main index)
function closeCastPmModal() {
    const modal = document.getElementById('castPmModal');
    modal.classList.add('hidden');
}
</script>
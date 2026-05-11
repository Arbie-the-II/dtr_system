<div id="export-master-modal" class="fixed inset-0 z-50 hidden">
    <div class="modal-overlay fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-file-export mr-2"></i> Master Export
                    </h3>
                    <button id="close-master-modal" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-6 text-center">
                <p class="text-gray-500 text-xs uppercase font-bold mb-1">Full Report</p>
                <h4 class="text-xl font-bold text-gray-800 mb-6">Daily Time Record DICT Internship</h4>
                
                <div class="space-y-3">
                    <button id="confirm-master-export" class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-300">
                        <i class="fas fa-file-csv"></i> Download Master CSV
                    </button>
                </div>
                
                <p class="text-xs text-gray-500 mt-4 font-mono">Daily_Time_Record_DICT_Internship.csv</p>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button id="cancel-master-export" class="text-gray-600 hover:text-gray-800 font-medium px-4 py-2">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
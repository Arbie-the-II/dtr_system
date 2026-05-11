<div id="export-modal" class="fixed inset-0 z-50 hidden">
    <div class="modal-overlay fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto overflow-hidden">
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-file-export mr-2"></i> Export & View Options
                    </h3>
                    <button id="close-export-modal" class="text-white hover:text-gray-200"><i class="fas fa-times"></i></button>
                </div>
            </div>
            
            <div class="px-6 py-6 text-center">
                <p class="text-gray-500 text-xs uppercase font-bold mb-1">Intern Selected</p>
                <h4 id="export-student-name" class="text-xl font-bold text-gray-800 mb-6"></h4>
                
                <div class="space-y-3">
                    <button id="view-form6-btn" class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-300">
                        <i class="fas fa-eye"></i> View Form 6 (DTR)
                    </button>

                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="flex-shrink mx-4 text-gray-400 text-xs uppercase">or</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>

                    <button id="confirm-export" class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-300">
                        <i class="fas fa-file-csv"></i> Download CSV
                    </button>
                </div>
                
                <p class="text-xs text-gray-500 mt-4" id="export-filename">intern_timesheet.csv</p>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button id="cancel-export" class="text-gray-600 hover:text-gray-800 font-medium px-4 py-2">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
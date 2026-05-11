<div id="pinModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-lg font-bold mb-4">Admin Security Access</h2>
        <form action="main.php" method="POST">
            <input type="hidden" name="action" value="update_hour_limit">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Enter Security PIN</label>
                <input type="password" name="admin_pin" required class="w-full border p-2 rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">New Max Hours (e.g., 8.5)</label>
                <input type="number" step="0.5" name="max_hours" required class="w-full border p-2 rounded">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closePinModal()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded">Apply Limit</button>
            </div>
        </form>
    </div>
</div>
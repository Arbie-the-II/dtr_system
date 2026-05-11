<div class="bg-white rounded-xl shadow-md overflow-hidden mt-4">
    <div class="p-4 bg-blue-50 border-blue-600 border-b-2 flex items-center">
        <i class="fas fa-table text-primary-600 mr-2"></i>
        <h2 class="text-lg font-semibold text-gray-800">Timesheet Records</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 ">
            <thead class="bg-blue-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">AM Time In</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">AM Time Out</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">PM Time In</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">PM Time Out</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">AM Hours</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">PM Hours</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">OT Hours</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">Pause</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">Total Hours</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">Notes</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-black uppercase tracking-wider">Photos</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if($timesheet_stmt->rowCount() > 0): ?>
                    <?php 
                    // Reset the pointer to the beginning of the result set
                    $timesheet_stmt->execute();
                    while($row = $timesheet_stmt->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                        <?php if($row['render_date'] != NULL && $row['render_date'] != '0000-00-00'): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                    <?php echo date('M d, Y', strtotime($row['render_date'])); ?>
                                </span>
                            </td>
                            
                            <!-- Clickable AM Time In -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="text-gray-700 cursor-pointer hover:text-primary-600 hover:underline" 
                                      onclick="openTimeAdjustmentModal('<?php echo $row['record_id']; ?>', '<?php echo $row['intern_id']; ?>', 'am_timein', '<?php echo formatTime($row['am_timein']); ?>', '<?php echo $row['render_date']; ?>')">
                                    <?php echo formatTime($row['am_timein']); ?>
                                </span>
                            </td>
                            
                            <!-- Clickable AM Time Out -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="text-gray-700 cursor-pointer hover:text-primary-600 hover:underline" 
                                      onclick="openTimeAdjustmentModal('<?php echo $row['record_id']; ?>', '<?php echo $row['intern_id']; ?>', 'am_timeOut', '<?php echo formatTime($row['am_timeOut']); ?>', '<?php echo $row['render_date']; ?>')">
                                    <?php echo formatTime($row['am_timeOut']); ?>
                                </span>
                            </td>
                            
                            <!-- Clickable PM Time In -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="text-gray-700 cursor-pointer hover:text-primary-600 hover:underline" 
                                      onclick="openTimeAdjustmentModal('<?php echo $row['record_id']; ?>', '<?php echo $row['intern_id']; ?>', 'pm_timein', '<?php echo formatTime($row['pm_timein']); ?>', '<?php echo $row['render_date']; ?>')">
                                    <?php echo formatTime($row['pm_timein']); ?>
                                </span>
                            </td>
                            
                            <!-- Clickable PM Time Out -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="text-gray-700 cursor-pointer hover:text-primary-600 hover:underline" 
                                      onclick="openTimeAdjustmentModal('<?php echo $row['record_id']; ?>', '<?php echo $row['intern_id']; ?>', 'pm_timeout', '<?php echo formatTime($row['pm_timeout']); ?>', '<?php echo $row['render_date']; ?>')">
                                    <?php echo formatTime($row['pm_timeout']); ?>
                                </span>
                            </td>
                            //AM Hours with 12:00 PM cutoff and 5-hour max cap
<td class="px-6 py-4 whitespace-nowrap text-sm">
    <?php if(!isTimeEmpty($row['am_timein']) && !isTimeEmpty($row['am_timeOut'])): ?>
        <?php 
            $time_in_ts = strtotime($row['am_timein']);
            $time_out_ts = strtotime($row['am_timeOut']);
            
            // Create a reference for 12:00 PM on the same date as the record
            $noon_ts = strtotime(date('Y-m-d', $time_in_ts) . ' 12:00:00');

            // STOP the clock at 12:00 PM: use the smaller of the two timestamps
            $effective_out = min($time_out_ts, $noon_ts);
            
            // Calculate seconds worked (ensure it's not negative)
            $am_seconds = max(0, $effective_out - $time_in_ts);
            
            // Apply a 5-hour maximum cap
            $max_am_seconds = 5 * 3600; 
            
            if ($am_seconds >= $max_am_seconds) {
                $am_display = "5hr";
            } else {
                $h = floor($am_seconds / 3600);
                $m = floor(($am_seconds / 60) % 60);
                
                // Format display to hide "0m"
                if ($h > 0 && $m > 0) {
                    $am_display = $h . "hr " . $m . "m";
                } elseif ($h > 0) {
                    $am_display = $h . "hr";
                } else {
                    $am_display = $m . "m";
                }
            }
        ?>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <?php echo $am_display; ?>
        </span>
    <?php else: ?>
        <span class="text-gray-400">-</span>
    <?php endif; ?>
</td>
                      // PM Hours with Cast Logic & Grace Period      
<td class="px-6 py-4 whitespace-nowrap text-sm text-center">
    <?php 
    if(!empty($row['pm_timein']) && $row['pm_timein'] !== '00:00:00' && !empty($row['pm_timeout']) && $row['pm_timeout'] !== '00:00:00'): 
        
        $row_date = date('Y-m-d', strtotime($row['render_date']));
        $intern_id = $row['intern_id']; 

        // --- UPDATED CAST PM LOGIC: CHECK INTERN CUSTOM LIMIT ONLY ---
        // Added 'is_active = 1' so the disable feature works
        // Looks for the LATEST rule. If the latest rule is is_active = 0, $custom_limit becomes false.
        $custom_stmt = $conn->prepare("SELECT custom_pm_out, is_active FROM intern_custom_limits 
                                    WHERE intern_id = ? AND effective_date <= ? 
                                    ORDER BY effective_date DESC LIMIT 1");
        $custom_stmt->execute([$intern_id, $row_date]);
        $custom_row = $custom_stmt->fetch(PDO::FETCH_ASSOC);

        // Only apply the custom time if a record exists AND it is active
        $custom_limit = ($custom_row && $custom_row['is_active'] == 1) ? $custom_row['custom_pm_out'] : false;
        // --- STEP 3: CONNECTION LOGIC ---
        if ($custom_limit) {
            // Priority: Active Intern-specific limit
            $official_end_time = strtotime($row_date . ' ' . $custom_limit);
            $status_icon = 'fa-user-clock'; 
            $bg_color = 'bg-purple-100 text-purple-800';
        } else {
            // Priority 2: Standard System Rule (Falls back here if Cast is Disabled)
            $hist = $conn->prepare("SELECT setting_value FROM settings_history 
                                    WHERE effective_date <= ? AND setting_key = 'max_daily_hours'
                                    ORDER BY effective_date DESC LIMIT 1");
            $hist->execute([$row_date]);
            $db_rule = $hist->fetchColumn();
            $required_hrs = ($db_rule !== false) ? (float)$db_rule : 8.0;

            $pm_shift_length = ($required_hrs <= 8) ? 5 : 6; 
            $official_end_time = strtotime($row_date . ' ' . (13 + $pm_shift_length) . ':00:00');
            $status_icon = ''; 
            $bg_color = 'bg-blue-100 text-blue-800';
        }

        // --- CALCULATION (Keep existing logic) ---
        $official_start_time = strtotime($row_date . ' 13:00:00');
        $actual_in = strtotime($row_date . ' ' . $row['pm_timein']);
        $actual_out = strtotime($row_date . ' ' . $row['pm_timeout']);

        $start_counting = max($actual_in, $official_start_time);
        $end_counting = min($actual_out, $official_end_time);
        
        $pm_seconds = $end_counting - $start_counting;

        if ($pm_seconds > 0): 
            $h = floor($pm_seconds / 3600);
            $m = floor(($pm_seconds / 60) % 60);
            $pm_display = ($h > 0 ? $h . "hr " : "") . ($m > 0 ? $m . "m" : "");
            ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $bg_color; ?>">
                <?php if($status_icon): ?><i class="fas <?php echo $status_icon; ?> mr-1 text-[10px]"></i><?php endif; ?>
                <?php echo $pm_display; ?>
            </span>
        <?php else: ?>
            <span class="text-gray-400">-</span>
        <?php endif; ?>
        
    <?php else: ?>
        <span class="text-gray-400">-</span>
    <?php endif; ?>
</td>                            

                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if(isset($row['overtime_hours']) && !isTimeEmpty($row['overtime_hours'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        <?php echo formatDuration($row['overtime_hours']); ?>
                                        <span class="ml-1 cursor-help group relative" 
                                            title="Overtime details">
                                            <i class="fas fa-info-circle"></i>
                                            <div class="hidden group-hover:block absolute z-10 w-48 -ml-24 -mt-32 bg-white shadow-lg rounded-md p-2 text-xs border border-gray-200">
                                                <p class="font-semibold text-gray-700 mb-1">Overtime Details:</p>
                                                <p><span class="font-medium">Start:</span> <?php echo formatTime($row['overtime_start']); ?></p>
                                                <p><span class="font-medium">End:</span> <?php echo formatTime($row['overtime_end']); ?></p>
                                                <p><span class="font-medium">Duration:</span> <?php echo formatDuration($row['overtime_hours']); ?></p>
                                            </div>
                                        </span>
                                    </span>
                                <?php else: ?>
                                    <?php if(!isTimeEmpty($row['overtime_start']) && isTimeEmpty($row['overtime_end'])): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            In progress
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 cursor-pointer hover:text-primary-600 hover:underline" 
                                            onclick="openTimeAdjustmentModal('<?php echo $row['record_id']; ?>', '<?php echo $row['intern_id']; ?>', 'overtime_start', '<?php echo formatTime($row['overtime_start'] ?? '00:00:00'); ?>', '<?php echo $row['render_date']; ?>')">
                                            -
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if(isset($row['pause_duration']) && !isTimeEmpty($row['pause_duration'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <?php echo formatDuration($row['pause_duration']); ?>
                                        <?php if(!empty($row['pause_reason'])): ?>
                                            <span class="ml-1 cursor-help" title="<?php echo htmlspecialchars($row['pause_reason']); ?>">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <!-- Total Hours with dynamic locking and status indicators -->
                            <?php
                                // 1. Fetch ALL history rules, sorted by date (newest first)
                                // This ensures that when we loop, we hit the most recent rule applicable to the record date
                                $history_stmt = $conn->prepare("SELECT setting_value, effective_date FROM settings_history WHERE setting_key = 'max_daily_hours' ORDER BY effective_date DESC");
                                $history_stmt->execute();
                                $limits_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);

                                // 2. Safety check for the function
                                if (!function_exists('getLimitForDate')) {
                                    function getLimitForDate($record_date, $history, $fallback_limit) {
                                        foreach ($history as $rule) {
                                            // Check if the record date is on or after the rule's start date
                                            if ($record_date >= $rule['effective_date']) {
                                                return (float)$rule['setting_value'];
                                            }
                                        }
                                        // If no history matches (date is older than all history), use the system default
                                        return (float)$fallback_limit;
                                    }
                                }
                                ?>
// total hours calculation with dynamic limit retrieval
<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
    <?php if(!isTimeEmpty($row['am_timein']) || !isTimeEmpty($row['pm_timein'])): ?>
        <?php 
            $row_date = date('Y-m-d', strtotime($row['render_date']));
            $intern_id = $row['intern_id']; // Ensure intern_id is captured
            
            // 1. Get the current rule (8 or 10) for this specific date
            $hist = $conn->prepare("SELECT setting_value FROM settings_history 
                                    WHERE effective_date <= ? AND setting_key = 'max_daily_hours'
                                    ORDER BY effective_date DESC LIMIT 1");
            $hist->execute([$row_date]);
            $db_rule = $hist->fetchColumn();
            $required_hrs = ($db_rule !== false) ? (float)$db_rule : 8.0;

            $total_seconds = 0;

            // 2. AM CALCULATION (Matches your AM Hours column logic)
            if(!isTimeEmpty($row['am_timein']) && !isTimeEmpty($row['am_timeOut'])) {
                $time_in_ts = strtotime($row['am_timein']);
                $time_out_ts = strtotime($row['am_timeOut']);
                $noon_ts = strtotime(date('Y-m-d', $time_in_ts) . ' 12:00:00');

                $effective_am_out = min($time_out_ts, $noon_ts);
                $am_diff = max(0, $effective_am_out - $time_in_ts);
                
                $am_max_limit = ($required_hrs <= 8) ? 4 : 5;
                $total_seconds += min($am_diff, $am_max_limit * 3600);
            }

            // 3. PM CALCULATION (Updated for intern_custom_limits with is_active)
            if(!isTimeEmpty($row['pm_timein']) && !isTimeEmpty($row['pm_timeout'])) {
                
                // --- START: CAST PM LOGIC ---
                // Check if an active custom limit exists for this specific intern and date
                $custom_stmt = $conn->prepare("SELECT custom_pm_out FROM intern_custom_limits 
                                              WHERE intern_id = ? AND effective_date = ? AND is_active = 1");
                $custom_stmt->execute([$intern_id, $row_date]);
                $custom_limit = $custom_stmt->fetchColumn();

                $off_start = strtotime($row_date . ' 13:00:00'); // 1:00 PM
                
                if ($custom_limit) {
                    // Priority: Use the active custom limit
                    $off_end = strtotime($row_date . ' ' . $custom_limit);
                    $is_casted = true;
                } else {
                    // Fallback: Use standard office rule (8hr = 5hrs, 10hr = 6hrs)
                    $pm_shift_len = ($required_hrs <= 8) ? 5 : 6;
                    $off_end = strtotime($row_date . ' ' . (13 + $pm_shift_len) . ':00:00');
                    $is_casted = false;
                }
                // --- END: CAST PM LOGIC ---

                $act_in  = strtotime($row_date . ' ' . $row['pm_timein']);
                $act_out = strtotime($row_date . ' ' . $row['pm_timeout']);

                $effective_pm_start = max($act_in, $off_start);
                $effective_pm_end   = min($act_out, $off_end);
                
                $pm_sec = max(0, $effective_pm_end - $effective_pm_start);
                $total_seconds += $pm_sec;
            }

            $actual_hrs_decimal = $total_seconds / 3600;

            // 4. STATUS DETERMINATION
            $half_day_target = $required_hrs / 2;
            $extension_limit = $half_day_target + 0.5;

            $is_completed = ($actual_hrs_decimal >= ($required_hrs - 0.01));
            $is_half_day = (!$is_completed && 
                            $actual_hrs_decimal >= ($half_day_target - 0.01) && 
                            $actual_hrs_decimal <= $extension_limit);

            // 5. Formatting Display Text
            $h = floor($total_seconds / 3600);
            $m = floor(($total_seconds / 60) % 60);
            
            if ($is_completed) {
                $display_text = (int)$required_hrs . "hr";
            } else {
                if ($h > 0 && $m > 0) {
                    $display_text = "{$h}hr {$m}m";
                } elseif ($h > 0) {
                    $display_text = "{$h}hr";
                } else {
                    $display_text = "{$m}m";
                }
            }
        ?>

        <div class="flex items-center justify-center gap-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $is_completed ? 'bg-green-100 text-green-800' : ($is_half_day ? 'bg-blue-100 text-blue-800' : 'bg-primary-100 text-primary-800'); ?>">
                <?php if(isset($is_casted) && $is_casted): ?>
                    <i class="fas fa-user-clock mr-1 text-[10px]" title="Custom limit applied"></i>
                <?php endif; ?>
                <?php echo $display_text; ?>
            </span>

            <?php if ($is_completed): ?>
                <i class="fas fa-check-circle text-green-500"></i>
                <span class="text-[10px] font-bold text-green-600 uppercase">Completed</span>
            <?php elseif ($is_half_day): ?>
                <i class="fas fa-adjust text-orange-500"></i>
                <span class="text-[10px] font-bold text-orange-600 uppercase">Half-Day</span>
            <?php else: ?>
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <span class="text-[10px] font-bold text-red-600 uppercase">Under-time</span>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <span class="text-gray-400">-</span>
    <?php endif; ?>
</td>                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <!-- Notes button with improved design and notification -->
                                <?php 
                                // Check if note exists for this date
                                $note_exists = false;
                                $note_content = '';
                                $note_id = 0;
                                try {
                                    $check_note = $conn->prepare("SELECT id, note_content FROM intern_notes WHERE intern_id = :intern_id AND note_date = :note_date");
                                    $check_note->bindParam(':intern_id', $row['intern_id']);
                                    $check_note->bindParam(':note_date', $row['render_date']);
                                    $check_note->execute();
                                    
                                    if($check_note->rowCount() > 0) {
                                        $note_data = $check_note->fetch(PDO::FETCH_ASSOC);
                                        $note_exists = true;
                                        $note_content = htmlspecialchars($note_data['note_content']);
                                        $note_id = $note_data['id'];
                                    }
                                } catch (Exception $e) {
                                    // Ignore errors
                                }
                                ?>
                                <button 
                                    onclick="openNoteModal('<?php echo $row['intern_id']; ?>', '<?php echo $row['render_date']; ?>', <?php echo $note_exists ? 'true' : 'false'; ?>, '<?php echo $note_content; ?>', <?php echo $note_id; ?>)" 
                                    class="relative flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-md border <?php echo $note_exists ? 'bg-gradient-to-r from-primary-50 to-primary-100 border-primary-200 text-primary-700' : 'bg-gray-50 hover:bg-gray-100 border-gray-200 text-gray-600'; ?> transition-all duration-200 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-opacity-50"
                                    title="<?php echo $note_exists ? mb_substr($note_content, 0, 30) . (mb_strlen($note_content) > 30 ? '...' : '') : 'Add Note'; ?>"
                                >
                                    <i class="<?php echo $note_exists ? 'fas fa-file-alt text-primary-500' : 'far fa-file-alt text-gray-500'; ?>"></i>
                                    <span class="text-xs font-medium"><?php echo $note_exists ? 'Note' : 'Add Note'; ?></span>
                                    
                                    <?php if($note_exists): ?>
                                    <span class="absolute -top-1.5 -right-1.5 flex h-4 w-4">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-300 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-4 w-4 bg-primary-500"></span>
                                    </span>
                                    <?php endif; ?>
                                </button>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button 
                                    class="view-photos-btn relative flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-md border bg-gray-50 hover:bg-gray-100 border-gray-200 text-gray-600 transition-all duration-200 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-opacity-50"
                                    data-record-id="<?php echo $row['record_id']; ?>"
                                    data-intern-id="<?php echo $row['intern_id']; ?>"
                                    data-date="<?php echo date('M d, Y', strtotime($row['render_date'])); ?>"
                                    data-intern-name="<?php echo htmlspecialchars($row['intern_name']); ?>"
                                >
                                    <i class="fas fa-camera text-primary-500"></i>
                                    <span class="text-xs font-medium">View Photos</span>
                                    
                                    <?php
                                    // Check if photos exist for this record
                                    $photos_count = 0;
                                    try {
                                        // Check if table exists first
                                        $table_check = $conn->query("SHOW TABLES LIKE 'timesheet_photos'");
                                        if ($table_check->rowCount() > 0) {
                                            // Table exists, now check for photos
                                            $photos_stmt = $conn->prepare("SELECT COUNT(*) as count FROM timesheet_photos WHERE record_id = :record_id");
                                            $photos_stmt->bindParam(':record_id', $row['record_id']);
                                            $photos_stmt->execute();
                                            $photos_count = $photos_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                        }
                                    } catch (Exception $e) {
                                        // Ignore errors
                                    }
                                    
                                    if($photos_count > 0): 
                                    ?>
                                    <span class="absolute -top-1.5 -right-1.5 flex h-4 w-4">
                                        <span class="relative inline-flex rounded-full h-4 w-4 bg-primary-500 text-white text-xs flex items-center justify-center"><?php echo $photos_count; ?></span>
                                    </span>
                                    <?php endif; ?>
                                </button>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="px-6 py-10 text-center text-sm text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-300 mb-3">
                                    <i class="fas fa-clipboard-list text-3xl"></i>
                                </div>
                                <p class="font-medium text-gray-600">No records found</p>
                                <?php if(empty($selected_intern_id)): ?>
                                    <p class="text-xs mt-1 text-gray-500">Please select a student to view their timesheet data</p>
                                <?php else: ?>
                                    <p class="text-xs mt-1 text-gray-500">No timesheet entries found for this student</p>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

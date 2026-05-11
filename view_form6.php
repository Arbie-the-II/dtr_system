<?php
include './main.php'; 
include './utilities/exportForm6_utils.php';
$intern_id = $_GET['id'] ?? null;
if (!$intern_id) die("Error: No Intern ID provided.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Form 6 - Official Time Record</title>
     <link rel="icon" href="./assets/images/Dict.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* A4 Page Simulation Styling */
        body { background-color: #f1f5f9; margin: 0; padding: 0; }
        
        .page-container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 2rem auto;
            background: white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        .line { border-bottom: 1px solid black; display: inline-block; min-width: 220px; padding-left: 5px; }
        
        .table-f6 { border: 1px solid black !important; width: 100%; border-collapse: collapse; }
        .table-f6 th, .table-f6 td { 
            border: 1px solid black !important; 
            text-align: center; 
            padding: 8px; 
            font-size: 12px;
        }
        .table-f6 th { 
            background-color: #ffffcc !important; 
            font-weight: bold;
            print-color-adjust: exact; 
            -webkit-print-color-adjust: exact; 
        }

        .sig-area { 
            border-top: 1px solid black; 
            display: inline-block; 
            width: 260px; 
            padding-top: 5px; 
            font-weight: bold; 
            font-size: 13px; 
        }

        /* Control Bar styling */
        .no-print-bar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        @media print {
            @page { size: A4; margin: 0; }
            body { background: white; padding: 0; margin: 0; }
            .no-print-bar { display: none !important; }
            .page-container { 
                margin: 0 !important; 
                box-shadow: none !important; 
                width: 100%; 
                padding: 15mm; 
                min-height: auto;
            }
            .table-f6 th { background-color: #ffffcc !important; }
        }
    </style>
</head>
<body>

    <div class="no-print-bar shadow-sm">
        <div class="max-w-5xl mx-auto flex justify-between items-center px-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-clock text-indigo-600"></i>
                <span class="font-bold text-gray-700 tracking-wide uppercase text-xs">Form 6 DTR Preview</span>
            </div>
            <div class="flex gap-2">
                <button onclick="window.close()" class="px-4 py-2 text-xs font-semibold text-gray-500 hover:text-gray-800 transition">Cancel</button>
                <button onclick="window.print()" class="px-6 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg shadow-md hover:bg-indigo-700 transition flex items-center gap-2">
                    <i class="fas fa-print"></i> PRINT A4
                </button>
            </div>
        </div>
    </div>

    <div class="page-container">
        <?php generateForm6Preview($conn, $intern_id); ?>
    </div>

</body>
</html>
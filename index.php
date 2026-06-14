<?php
require_once 'db.php';
require_once 'functions.php';
$conn = getDBConnection();
if (!$conn) {
    die('Database connection failed');
}
$usernames = getUsernames($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ===== Google Adsense ===== -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3865173708892194"
         crossorigin="anonymous"></script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=800, initial-scale=1.0, minimum-scale=0.1, maximum-scale=10.0, user-scalable=yes">

    <title>No-Nonsense Cocktails</title>
   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            tailwind.config = { content: [], theme: { extend: {} } };
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
   
    <style>
        :root { --accent: #e76f51; }
        @media (prefers-color-scheme: dark) { :root { --accent: #f4a261; } }

        body {
            background: #f8f1e3;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 0.85rem;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        @media (prefers-color-scheme: dark) {
            body { background: #1e1e1e; color: #e0e0e0; }
        }

        /* ============================================
           CONSOLIDATED WIDTH RULES (replaces all the scattered 800px !important rules)
        ============================================ */
        .main-container,
        .navbar,
        #recipe_details,
        .navbar .container-fluid,
        .main-container > .card:first-of-type {
            width: 100%;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ============================================
           BASE STYLES
        ============================================ */
        .card {
            border: 1px solid #ccc;
            border-radius: 0;
            box-shadow: none;
            margin-bottom: 3px;
        }
        .card-body {
            padding: 3px 6px;
        }
        .card-header {
            padding: 2px 6px;
            font-weight: 600;
            background: #f0f0f0;
            font-size: 0.9rem;
        }

        /* FILTER ROWS - keep on one line, shrink on mobile */
        .search-boxes .excel-row {
            display: flex !important;
            gap: 2px;
            margin-bottom: 2px;
            flex-wrap: nowrap !important;
            align-items: center;
        }
        .excel-cell {
            padding: 2px 4px !important;
        }

        .term-select, .operator-select, .logic-select {
            border: 1px solid #ccc !important;
            background-color: #ffffff !important;
            font-size: 0.85rem !important;
            padding: 4px 8px !important;
            height: auto !important;
            border-radius: 4px !important;
        }
        .value-input {
            border: 1px solid #ccc;
        }

        /* METADATA */
        #recipe_details .excel-row {
            display: flex !important;
            margin-bottom: 1px;
            border-bottom: 1px solid #e5e5e5;
            padding: 0;
            line-height: 1;
        }
        #recipe_details .label-cell {
            font-weight: 600;
            width: 160px;
            flex-shrink: 0;
            padding-right: 8px;
            text-align: right;
            white-space: nowrap;
        }
        #recipe_details .content-cell {
            flex: 1;
            padding-left: 4px;
            min-width: 0;
        }
        #recipe_details {
            border: 1px solid #ccc;
            background-color: #ffffff;
        }

        /* INGREDIENTS TABLE */
        .ingredient-table {
            width: 520px;
            border-collapse: collapse;
            font-size: 0.82rem;
            margin-top: 2px;
            margin-bottom: 4px;
            table-layout: fixed;
        }
        .ingredient-table th,
        .ingredient-table td {
            border: 1px solid #ccc;
            padding: 1px 3px !important;
            vertical-align: middle;
        }
        .ingredient-table th {
            background: #f0f0f0;
            font-weight: 600;
        }
        .ingredient-table th:nth-child(1), .ingredient-table td:nth-child(1) { width: 28px; text-align: left; }
        .ingredient-table th:nth-child(2), .ingredient-table td:nth-child(2) { 
            width: 170px; text-align: left; padding-right: 4px; word-break: break-word; white-space: normal;
        }
        .ingredient-table th:nth-child(3), .ingredient-table td:nth-child(3) { width: 82px; text-align: right; padding-left: 4px; }
        .ingredient-table th:nth-child(4), .ingredient-table td:nth-child(4) { width: 65px; text-align: right; }

        .btn { padding: 2px 8px; font-size: 0.82rem; }
        #user-select { font-size: 0.82rem; }
        #recipe_details .card-body { padding: 4px 6px !important; }

        /* Manual flex control for filter row cells */
        .term-select-cell { flex: 0 0 175px; }
        .excel-cell:has(.operator-select) { flex: 0 0 58px; }
        .excel-cell:has(.value-input) { flex: 0 0 200px; }
        .term-select, .operator-select, .value-input { width: 100% !important; }
        #recipe_details .ingredient-table { margin-left: 168px; }

        /* ============================================
           RESPONSIVE SHRINKING (guarantees no horizontal overflow on mobile)
        ============================================ */

        /* Large phones / small tablets */
        @media (max-width: 768px) {
            .search-boxes .excel-row { gap: 1px; margin-bottom: 1px; }
            .excel-cell { padding: 1px 2px !important; }
            .term-select, .operator-select, .logic-select,
            .value-input, .excel-cell select, .excel-cell input {
                font-size: 0.8rem !important;
                padding: 2px 4px !important;
            }
            button, .btn { font-size: 0.8rem !important; padding: 2px 6px !important; }
        }

        /* Most phones */
        @media (max-width: 480px) {
            .search-boxes .excel-row { gap: 1px; }
            .excel-cell { padding: 0 1px !important; }
            .term-select, .operator-select, .logic-select,
            .value-input {
                font-size: 0.75rem !important;
                padding: 1px 3px !important;
            }
            button, .btn { font-size: 0.75rem !important; padding: 2px 6px !important; }

            /* Metadata */
            #recipe_details .label-cell { width: 120px; padding-right: 4px; font-size: 0.75rem; }
            #recipe_details .content-cell { font-size: 0.75rem; padding-left: 2px; }
            #recipe_details .excel-row { margin-bottom: 0; padding: 0; }

            /* Ingredients table */
            .ingredient-table th, .ingredient-table td {
                font-size: 0.75rem !important;
                padding: 2px 4px !important;
            }
        }

        /* Very narrow phones - aggressive shrinking */
        @media (max-width: 400px) {
            .search-boxes .excel-row { gap: 0; }
            .excel-cell { padding: 0 1px !important; }
            .term-select, .operator-select, .logic-select,
            .value-input {
                font-size: 0.7rem !important;
                padding: 1px 2px !important;
            }
            button, .btn { font-size: 0.7rem !important; padding: 1px 4px !important; }

            /* Metadata */
            #recipe_details .label-cell { width: 100px; font-size: 0.7rem; }
            #recipe_details .content-cell { font-size: 0.7rem; }

            /* Ingredients table */
            .ingredient-table th, .ingredient-table td {
                font-size: 0.7rem !important;
                padding: 1px 2px !important;
            }
        }

        /* ============================================
           DARK MODE (consolidated where practical)
        ============================================ */
        @media (prefers-color-scheme: dark) {
            .card { border-color: #555; }
            .card-header { background: #2d2d2d; }

            /* Metadata + Ingredients */
            #recipe_details,
            #recipe_details .label-cell,
            #recipe_details .content-cell,
            #recipe_details .card-body,
            #recipe_details *,
            .ingredient-table,
            .ingredient-table th,
            .ingredient-table td {
                color: #e0e0e0 !important;
            }
            #recipe_details { background-color: #2a2a2a !important; border-color: #555; }
            .ingredient-table { border-color: #555; }
            .ingredient-table th { background: #2d2d2d !important; }
            .ingredient-table tr:nth-child(even) { background-color: #252525 !important; }

            /* Filter section (all rows) */
            .main-container > .card:first-of-type,
            .main-container > .card:first-of-type .card-body,
            .search-boxes,
            .search-boxes .excel-row,
            .search-boxes .excel-cell,
            #name-source-row,
            #name-source-row .card,
            #name-source-row .card-body {
                background-color: #2a2a2a !important;
                color: #e0e0e0 !important;
                border-color: #555 !important;
            }
            .main-container > .card:first-of-type { border: 1px solid #555 !important; }

            /* Dropdowns and inputs in filters */
            .term-select,
            .operator-select,
            .logic-select,
            .value-input,
            .excel-cell select,
            .excel-cell input,
            #name-select,
            #source-select {
                background-color: #3a3a3a !important;
                border-color: #666 !important;
                color: #e0e0e0 !important;
            }

            /* Second filter row (Possible Cocktails + Sources) */
            #name-source-row,
            #name-source-row.card,
            #name-source-row > .card,
            #name-source-row .card,
            #name-source-row .card-body,
            #name-source-row .excel-row,
            #name-source-row .excel-cell,
            #name-source-row * {
                background-color: #2a2a2a !important;
                color: #e0e0e0 !important;
                border-color: #555 !important;
            }
            #name-source-row .badge {
                background-color: #555 !important;
                color: #e0e0e0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid d-flex align-items-center">
            <!-- Left: Logo + Title -->
            <div class="d-flex align-items-center">
                <img src="images/Coldberry_01_TM.jpg" alt="Logo" style="height: 36px;" class="me-2">
                <h1 class="h5 mb-0 text-white">No-Nonsense Cocktails</h1>
            </div>
            
            <!-- Center: 4 Buttons -->
            <div class="d-flex align-items-center gap-1 mx-auto ms-12">
                <button id="reset-button" class="btn btn-outline-light">Reset</button>
                <button id="lucky-button" class="btn btn-outline-light">I'm Feeling Lucky</button>
                <button id="copy-permalink" class="btn btn-outline-light">Share Link</button>
                <button id="create-qr-code" class="btn btn-outline-light">QR Code</button>
            </div>
            
            <!-- Right: User Dropdown -->
            <div class="d-flex align-items-center ms-3">
                <strong class="me-1 text-white" style="font-size:0.82rem;">User:</strong>
                <select id="user-select" class="form-select">
                    <option value="">Select...</option>
                    <option value="All">All Users</option>
                    <?php foreach ($usernames as $user): ?>
                        <option value="<?php echo htmlspecialchars($user); ?>"><?php echo htmlspecialchars($user); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <div class="search-boxes">
                    <div class="excel-row">
                        <!-- STEP 1 -->
                        <div class="excel-cell term-select-cell">
                            <select class="term-select form-select" name="term[]">
                                <option value="" selected>STEP 1: Select a Filter</option>
                                <option value="All">All</option>
                                <option value="adaption_of">Adaptation of</option>
                                <option value="base">Base</option>
                                <option value="characteristics">Characteristics</option>
                                <option value="color">Color</option>
                                <option value="family">Family</option>
                                <option value="garnish">Garnish</option>
                                <option value="glass">Glass</option>
                                <option value="ice">Ice</option>
                                <option value="ingredients">Ingredients</option>
                                <option value="instructions">Instructions</option>
                                <option value="last_date">Last Date</option>
                                <option value="mixer">Mixer</option>
                                <option value="name">Name</option>
                                <option value="servings">Servings</option>
                                <option value="shaken_stirred">Shaken/Stirred</option>
                                <option value="source">Source</option>
                                <option value="stars_out_of_3">Stars out of 3</option>
                                <option value="variations">Variations</option>
                            </select>
                        </div>
                        <!-- Operator -->
                        <div class="excel-cell">
                            <select class="operator-select form-select" name="operator[]">
                                <option value="=" selected>=</option>
                                <option value="<>">≠</option>
                            </select>
                        </div>
                        <!-- STEP 2 -->
                        <div class="excel-cell">
                            <input type="text" class="value-input form-control" name="value[]" placeholder="STEP 2: Select or Type a Value">
                        </div>

                        <!-- + button -->
                        <div class="excel-cell" style="flex: 0 0 32px;"><button class="add-box btn btn-sm btn-outline-secondary w-100">+</button></div>
                        
                        <!-- – button -->
                        <div class="excel-cell" style="flex: 0 0 32px;"><button class="remove-box btn btn-sm btn-outline-secondary w-100" style="display:none;">–</button></div>

                        <!-- Logic -->
                        <div class="excel-cell logic-cell" style="flex: 0 0 64px;">
                            <select class="logic-select form-select" name="logic[]" style="display:none;">
                                <option value="AND" selected>AND</option>
                                <option value="OR">OR</option>
                            </select>
                        </div>

                        <!-- Ingredients Order dropdown -->
                        <div class="excel-cell" style="min-width: 170px; flex: 0 0 auto; margin-left: auto;">
                            <div style="font-size: 0.75rem; color: #6c757d; margin-bottom: 1px;">Ingredients Order</div>
                            <select id="ingredients-order-select" class="form-select form-select-sm">
                                <option value="Recipe" selected>Recipe</option>
                                <option value="Vol Asc">Vol Asc</option>
                                <option value="Vol Desc">Vol Desc</option>
                                <option value="Cost Asc">Cost Asc</option>
                                <option value="Cost Desc">Cost Desc</option>
                                <option value="Alpha Asc">Alpha Asc</option>
                                <option value="Alpha Desc">Alpha Desc</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="card">
            <div class="card-body">
                <div class="results-row d-flex align-items-center">
                    <strong>Possible Cocktails:</strong>
                    <span id="name-count" class="badge bg-primary ms-1">0</span>
                    <select id="name-select" class="form-select mx-1" style="max-width: 260px;"></select>
                   
                    <strong class="ms-2">Sources:</strong>
                    <span id="source-count" class="badge bg-primary ms-1">0</span>
                    <select id="source-select" class="form-select mx-1" style="max-width: 260px;"></select>
                </div>
            </div>
        </div>

        <div id="recipe_details" class="card"></div>
    </div>

    <!-- QR Code Popup -->
    <div id="qr-code-popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:15px; border:1px solid #ccc; box-shadow:0 0 15px rgba(0,0,0,0.3); z-index:9999;">
        <div id="qr-code"></div>
        <button id="close-qr-code" class="btn btn-secondary mt-2">Close</button>
    </div>

    <script src="scripts.js"></script>
<?php require_once 'footer.php'; ?>
</body>
</html>

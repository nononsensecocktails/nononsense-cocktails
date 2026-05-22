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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No-Nonsense Cocktails</title>
    <meta name="description" content="Find your next perfect cocktail. Filter by ingredients, glass, ratings, and more.">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <style>
        :root {
            --accent: #e76f51;
        }
        
        @media (prefers-color-scheme: dark) {
            :root {
                --accent: #f4a261;
            }
        }

        body {
            background: #f8f1e3;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 0.88rem;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: #1e1e1e;
                color: #e0e0e0;
            }
        }

        .main-container {
            min-width: 1280px;
            margin: 2px auto;
            padding: 0 6px;
            overflow-x: auto;
        }

        .navbar {
            background: #2a2a2a;
            color: white;
            padding: 4px 10px;
            font-size: 0.9rem;
        }

        .excel-row {
            display: flex;
            gap: 3px;
            margin-bottom: 3px;
            flex-wrap: nowrap;
            align-items: center;
        }

        .excel-cell {
            flex: 1;
            min-width: 95px;
        }

        .card {
            border: 1px solid #ccc;
            border-radius: 0;
            box-shadow: none;
            margin-bottom: 4px;
        }

        .card-body {
            padding: 4px 6px;
        }

        .card-header {
            padding: 4px 8px;
            font-weight: 600;
            background: #f0f0f0;
            font-size: 0.9rem;
        }

        @media (prefers-color-scheme: dark) {
            .card { border-color: #555; }
            .card-header { background: #2d2d2d; }
        }

        .search-boxes .excel-row {
            background: #fff;
            border: 1px solid #d0d0d0;
            padding: 2px 5px;
            border-radius: 2px;
        }

        @media (prefers-color-scheme: dark) {
            .search-boxes .excel-row {
                background: #2d2d2d;
                border-color: #555;
            }
        }

        /* Results row - ultra tight */
        .results-row {
            display: flex;
            gap: 8px;
            align-items: center;
            padding: 4px 6px;
            font-size: 0.9rem;
        }

        /* Recipe details - spreadsheet tight + labels immediately next to values */
        #recipe_details .excel-row {
            margin-bottom: 2px;
            border-bottom: 1px solid #e5e5e5;
            padding: 2px 0;
        }

        @media (prefers-color-scheme: dark) {
            #recipe_details .excel-row {
                border-color: #444;
            }
        }

        #recipe_details .label-cell {
            font-weight: 600;
            width: 130px;
            flex-shrink: 0;
            padding-right: 6px;
            text-align: right;
        }

        #recipe_details .content-cell {
            flex: 1;
            padding-left: 4px;
        }

        /* Ingredients table - very tight */
        .ingredient-table {
            border: 1px solid #ccc;
            border-collapse: collapse;
            width: 100%;
            background: white;
            font-size: 0.85rem;
        }

        @media (prefers-color-scheme: dark) {
            .ingredient-table {
                background: #2d2d2d;
                border-color: #555;
            }
        }

        .ingredient-row td {
            border: 1px solid #e5e5e5;
            padding: 2px 5px;
            vertical-align: middle;
        }

        @media (prefers-color-scheme: dark) {
            .ingredient-row td {
                border-color: #444;
            }
        }

        .ingredient-number {
            width: 26px;
            text-align: right;
            font-weight: 500;
        }

        select.form-select, input.form-control {
            font-size: 0.88rem;
            padding: 2px 6px;
            height: 30px;
            border-radius: 2px;
        }

        .btn {
            padding: 3px 9px;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .header-user {
            min-width: 150px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid d-flex align-items-center">
            <div class="d-flex align-items-center">
                <img src="images/Coldberry_01_TM.jpg" alt="Logo" style="height: 38px;" class="me-2">
                <h1 class="h5 mb-0 text-white">No-Nonsense Cocktails</h1>
            </div>

            <div class="ms-auto d-flex align-items-center gap-2">
                <button id="reset-button" class="btn btn-outline-light">Reset</button>
                <button id="copy-permalink" class="btn btn-outline-light">Share Link</button>
                <button id="lucky-button" class="btn btn-outline-light">I'm Feeling Lucky</button>
                <button id="create-qr-code" class="btn btn-outline-light">QR Code</button>
                
                <div class="d-flex align-items-center header-user ms-3">
                    <strong class="me-1 text-white" style="font-size:0.85rem;">User:</strong>
                    <select id="user-select" class="form-select">
                        <option value="">Select...</option>
                        <option value="All">All Users</option>
                        <?php foreach ($usernames as $user): ?>
                            <option value="<?php echo htmlspecialchars($user); ?>"><?php echo htmlspecialchars($user); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-container">
        
        <!-- Filters -->
        <div class="card">
            <div class="card-header">Filters</div>
            <div class="card-body">
                <div class="search-boxes">
                    <div class="excel-row">
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
                        <div class="excel-cell">
                            <select class="operator-select form-select" name="operator[]">
                                <option value="=" selected>=</option>
                                <option value="<>">≠</option>
                            </select>
                        </div>
                        <div class="excel-cell">
                            <input type="text" class="value-input form-control" name="value[]" placeholder="STEP 2: Select or Type a Value">
                        </div>
                        <div class="excel-cell" style="flex: 0 0 34px;">
                            <button class="add-box btn btn-sm btn-outline-secondary w-100">+</button>
                        </div>
                        <div class="excel-cell" style="flex: 0 0 34px;">
                            <button class="remove-box btn btn-sm btn-outline-secondary w-100" style="display:none;">–</button>
                        </div>
                        <div class="excel-cell logic-cell" style="flex: 0 0 68px;">
                            <select class="logic-select form-select" name="logic[]" style="display:none;">
                                <option value="AND" selected>AND</option>
                                <option value="OR">OR</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="card">
            <div class="card-body">
                <div class="results-row">
                    <strong>Possible Cocktails:</strong>
                    <span id="name-count" class="badge bg-primary ms-1">0</span>
                    <select id="name-select" class="form-select flex-grow-1 mx-2"></select>
                    
                    <strong class="ms-2">Sources:</strong>
                    <span id="source-count" class="badge bg-primary ms-1">0</span>
                    <select id="source-select" class="form-select flex-grow-1 mx-2"></select>
                </div>
            </div>
        </div>

        <!-- Recipe Details -->
        <div id="recipe_details" class="card"></div>
    </div>

    <!-- QR Code Popup -->
    <div id="qr-code-popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:15px; border:1px solid #ccc; box-shadow:0 0 15px rgba(0,0,0,0.3); z-index:9999;">
        <div id="qr-code"></div>
        <button id="close-qr-code" class="btn btn-secondary mt-2">Close</button>
    </div>

    <script src="scripts.js"></script>
</body>
</html>

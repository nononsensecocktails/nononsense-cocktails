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
            font-size: 0.95rem;
            line-height: 1.4;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: #1e1e1e;
                color: #e0e0e0;
            }
        }

        .main-container {
            min-width: 1280px;           /* Spreadsheet-style fixed width */
            margin: 1rem auto;
            padding: 0 1rem;
            overflow-x: auto;            /* Horizontal scrollbar when window is too narrow */
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .navbar {
            background: #2a2a2a;
            color: white;
            padding: 0.75rem 1rem;
        }

        .excel-row {
            display: flex;
            gap: 6px;
            margin-bottom: 6px;
            flex-wrap: nowrap;           /* NEVER wrap */
            align-items: center;
        }

        .excel-cell {
            flex: 1;
            min-width: 110px;
        }

        .card {
            border: none;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            margin-bottom: 1rem;
        }

        .card-body {
            padding: 12px 16px;
        }

        .search-boxes .excel-row {
            background: #fff;
            border: 1px solid #d0d0d0;
            padding: 4px 8px;
            border-radius: 4px;
        }

        @media (prefers-color-scheme: dark) {
            .search-boxes .excel-row {
                background: #2d2d2d;
                border-color: #555;
            }
        }

        /* Recipe details - clean & compact */
        #recipe_details .excel-row {
            margin-bottom: 4px;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 4px;
        }

        @media (prefers-color-scheme: dark) {
            #recipe_details .excel-row {
                border-color: #444;
            }
        }

        #recipe_details .label-cell {
            font-weight: 600;
            width: 160px;
            flex-shrink: 0;
        }

        /* Ingredients table */
        .ingredient-table {
            border: 1px solid #ccc;
            border-collapse: collapse;
            width: 100%;
            background: white;
        }

        @media (prefers-color-scheme: dark) {
            .ingredient-table {
                background: #2d2d2d;
                border-color: #555;
            }
        }

        .ingredient-row {
            border-bottom: 1px solid #e5e5e5;
        }

        @media (prefers-color-scheme: dark) {
            .ingredient-row {
                border-bottom-color: #444;
            }
        }

        .ingredient-number {
            width: 32px;
            text-align: right;
            font-weight: 500;
            padding-right: 8px;
        }

        /* Make sure dropdowns and inputs stay readable */
        select.form-select, input.form-control {
            font-size: 0.95rem;
            padding: 4px 8px;
            height: auto;
        }

        .btn {
            padding: 6px 12px;
            font-size: 0.9rem;
        }

        /* Header buttons stay on one line until really narrow */
        .header-buttons {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <div class="d-flex align-items-center w-100">
                <img src="images/Coldberry_01_TM.jpg" alt="Logo" style="height: 48px;" class="me-3">
                <h1 class="h4 mb-0 text-white">No-Nonsense Cocktails</h1>
                
                <div class="ms-auto d-flex gap-2 header-buttons">
                    <button id="reset-button" class="btn btn-outline-light">Reset</button>
                    <button id="copy-permalink" class="btn btn-outline-light">Share Link</button>
                    <button id="lucky-button" class="btn btn-outline-light">I'm Feeling Lucky</button>
                    <button id="create-qr-code" class="btn btn-outline-light">QR Code</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-container">
        
        <!-- User Selector -->
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto"><strong>User:</strong></div>
                    <div class="col">
                        <select id="user-select" class="form-select">
                            <option value="">Select user...</option>
                            <option value="All">All Users</option>
                            <?php foreach ($usernames as $user): ?>
                                <option value="<?php echo htmlspecialchars($user); ?>"><?php echo htmlspecialchars($user); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <div class="search-boxes">
                    <!-- Original filter row structure that scripts.js expects -->
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
                        <div class="excel-cell" style="flex: 0 0 42px;">
                            <button class="add-box btn btn-sm btn-outline-secondary w-100">+</button>
                        </div>
                        <div class="excel-cell" style="flex: 0 0 42px;">
                            <button class="remove-box btn btn-sm btn-outline-secondary w-100" style="display:none;">–</button>
                        </div>
                        <div class="excel-cell logic-cell" style="flex: 0 0 80px;">
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
                <div class="excel-row" style="gap: 20px; align-items: center;">
                    <div style="flex: 0 0 220px;">
                        <strong>Possible Cocktails:</strong>
                        <span id="name-count" class="badge bg-primary ms-2">0</span>
                    </div>
                    <div style="flex: 1;">
                        <select id="name-select" class="form-select"></select>
                    </div>
                    <div style="flex: 0 0 160px;">
                        <strong>Sources:</strong>
                        <span id="source-count" class="badge bg-primary ms-2">0</span>
                    </div>
                    <div style="flex: 1;">
                        <select id="source-select" class="form-select"></select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recipe Details (populated by scripts.js) -->
        <div id="recipe_details" class="card"></div>
    </div>

    <!-- QR Code Modal -->
    <div id="qr-code-popup" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Share via QR Code</h5>
                    <button type="button" class="btn-close" id="close-qr-code" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" id="qr-code"></div>
            </div>
        </div>
    </div>

    <script src="scripts.js"></script>
</body>
</html>

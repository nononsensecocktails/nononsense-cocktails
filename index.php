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
        body {
            background: linear-gradient(180deg, #f8f1e3 0%, #f0e6d2 100%);
            font-family: system-ui, -apple-system, sans-serif;
        }
        .navbar {
            background: #2a2a2a;
            color: white;
        }
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .btn-accent {
            background-color: var(--accent);
            border-color: var(--accent);
            color: white;
        }
        .excel-row {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .excel-cell {
            flex: 1;
            min-width: 120px;
        }
        @media (max-width: 768px) {
            .excel-row {
                flex-direction: column;
            }
            .main-container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-dark py-3">
        <div class="container">
            <div class="d-flex align-items-center">
                <img src="images/Coldberry_01_TM.jpg" alt="Logo" style="height: 48px;" class="me-3">
                <h1 class="h3 mb-0 text-white">No-Nonsense Cocktails</h1>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button id="reset-button" class="btn btn-outline-light">Reset</button>
                <button id="copy-permalink" class="btn btn-outline-light">Share Link</button>
                <button id="lucky-button" class="btn btn-outline-light">I'm Feeling Lucky</button>
                <button id="create-qr-code" class="btn btn-outline-light">QR Code</button>
            </div>
        </div>
    </nav>

    <div class="main-container">
        
        <!-- User Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 col-3"><strong>User:</strong></div>
                    <div class="col-md-10 col-9">
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
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <div class="search-boxes">
                    <!-- Original filter row that scripts.js expects -->
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
                        <div class="excel-cell" style="flex: 0 0 40px;">
                            <button class="add-box btn btn-sm btn-outline-secondary w-100">+</button>
                        </div>
                        <div class="excel-cell" style="flex: 0 0 40px;">
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
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <strong class="me-2">Possible Cocktails:</strong>
                            <span id="name-count" class="badge bg-primary fs-5">0</span>
                        </div>
                        <select id="name-select" class="form-select mt-2"></select>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <strong class="me-2">Sources:</strong>
                            <span id="source-count" class="badge bg-primary fs-5">0</span>
                        </div>
                        <select id="source-select" class="form-select mt-2"></select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recipe Details -->
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

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
        .filter-card {
            background: white;
        }
        .btn-accent {
            background-color: var(--accent);
            border-color: var(--accent);
            color: white;
        }
        .recipe-card {
            background: white;
        }
        .ingredient-bar {
            height: 8px;
            background: linear-gradient(to right, #e76f51, #f4a261);
            border-radius: 9999px;
            margin: 4px 0;
        }
        @media (max-width: 768px) {
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
            <div class="d-flex gap-2">
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
                    <div class="col-md-2">
                        <strong>User:</strong>
                    </div>
                    <div class="col-md-10">
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
        <div class="card filter-card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body" id="search-boxes-container">
                <!-- scripts.js will populate this area (same as before) -->
                <div class="search-boxes">
                    <!-- Existing filter rows go here - JS will manage them -->
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
                        <select id="name-select" class="form-select mt-2">
                            <option value="">STEP 3: Select a Name</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <strong class="me-2">Sources:</strong>
                            <span id="source-count" class="badge bg-primary fs-5">0</span>
                        </div>
                        <select id="source-select" class="form-select mt-2">
                            <option value="">STEP 4: Select a Source</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recipe Details -->
        <div id="recipe_details" class="recipe-card card"></div>
    </div>

    <!-- QR Popup -->
    <div id="qr-code-popup" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Share via QR Code</h5>
                    <button type="button" class="btn-close" id="close-qr-code"></button>
                </div>
                <div class="modal-body text-center" id="qr-code"></div>
            </div>
        </div>
    </div>

    <script src="scripts.js"></script>
</body>
</html>

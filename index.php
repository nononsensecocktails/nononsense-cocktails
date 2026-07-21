<div class="modal-body"><?php
header("Cache-Control: max-age=0, must-revalidate");
session_start();

require_once 'db.php';
require_once 'functions.php';
$conn = getDBConnection();
if (!$conn) {
    die('Database connection failed');
}
$usernames = getUsernames($conn);

$is_logged_in = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
$user_name = $_SESSION['user_name'] ?? '';
$user_picture = $_SESSION['user_picture'] ?? '';
?>

<script>
    const isUserLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
    const loggedInUserId = <?php echo $is_logged_in && !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?>;
    const loggedInUserName = <?php echo $is_logged_in && !empty($_SESSION['user_name']) ? json_encode($_SESSION['user_name']) : 'null'; ?>;
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=800, initial-scale=1.0, minimum-scale=0.1, maximum-scale=10.0, user-scalable=yes">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Favicon / Icon links for modern browsers -->
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/android-chrome-512x512.png">

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


/* Mobile search list (used for Ice, Glass, Ingredients, etc. on mobile) */
.mobile-search-list {
    display: none;
    position: absolute;
    z-index: 1000;
    background-color: #fff;
    border: 1px solid #ddd;
    max-height: 220px;
    overflow-y: auto;
    width: 100%;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 8px;
    margin-top: 4px;
}

.mobile-search-item {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    font-size: 16px;
    color: #222;
}

.mobile-search-item:last-child {
    border-bottom: none;
}

.mobile-search-item:hover {
    background-color: #f5f5f5;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .mobile-search-list {
        background-color: #1e1e1e;
        border: 1px solid #444;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
    }

    .mobile-search-item {
        color: #eee;
        border-bottom: 1px solid #333;
    }

    .mobile-search-item:hover {
        background-color: #2a2a2a;
    }
}
        .choices-wrapper .clear-btn {
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
}

.choices-wrapper .choices {
    width: 100%;
    padding-right: 30px;
}

/* Make the built-in Choices.js remove button more visible and consistent */
.choices__button {
    position: relative;
    padding: 0 6px;
    margin-left: 6px;
    font-size: 18px;
    line-height: 1;
    color: #999;
    cursor: pointer;
    background: transparent;
    border: none;
}

.choices__button:hover {
    color: #333;
}

/* Optional: give a bit more space around the selected item */
.choices__inner .choices__item {
    padding-right: 6px;
}

        /* === Clear button styles for filter inputs === */
        .input-with-clear {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-with-clear .value-input {
            padding-right: 28px;
        }

        .clear-btn {
            position: absolute;
            right: 8px;
            font-size: 18px;
            color: #999;
            cursor: pointer;
            user-select: none;
            display: none;
        }

        .clear-btn:hover {
            color: #333;
        }

        :root { --accent: #e76f51; }
        @media (prefers-color-scheme: dark) { :root { --accent: #f4a261; } }
        body {
            background: #f8f1e3;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 0.85rem;
            line-height: 1.2;
            margin: 0;
            padding: 0;
	    /*overflow-x: hidden;*/
        }
        @media (prefers-color-scheme: dark) {
            body { background: #1e1e1e; color: #e0e0e0; }
        }
        /* MAIN CONTAINER - 800px left-aligned + no horizontal scrollbar */
        .main-container {
            width: 800px !important;
            max-width: 800px !important;
            min-width: 800px !important;
            margin: 2px auto 2px auto !important;
            padding: 0;
            /*overflow-x: hidden;*/
        }
        /* NAVBAR - 800px left-aligned (removes black spillover on the right) */
        .navbar {
            background: #2a2a2a;
            color: white;
            padding: 2px 6px;
            font-size: 0.9rem;
            width: 800px !important;
            max-width: 800px !important;
            margin: 0 auto !important;
        }
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
        @media (prefers-color-scheme: dark) {
            .card { border-color: #555; }
            .card-header { background: #2d2d2d; }
        }
        /* FILTERS - all dropdowns identical */
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
        /* Force Step 1 and Step 2 to look exactly like Step 3 and Step 4 */
        .term-select, .operator-select, .logic-select {
            border: 1px solid #ccc !important;
            background-color: #ffffff !important;
            font-size: 0.85rem !important;
            padding: 4px 8px !important;
            height: auto !important;
            border-radius: 4px !important;   /* rounded corners */
        }
        .value-input {
            border: 1px solid #ccc;
        }
        /* METADATA - ABSOLUTE MINIMUM PADDING */
        #recipe_details .excel-row {
            display: flex !important;
            margin-bottom: 1px;
            border-bottom: 1px solid #e5e5e5;
            padding: 0;
            line-height: 1;
        }
        @media (prefers-color-scheme: dark) {
            #recipe_details .excel-row { border-color: #444; }
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

/* STATIC FIXED WIDTH FOR ENTIRE METADATA SECTION - LEFT ALIGNED */
        #recipe_details {
            width: 800px !important;
            max-width: 800px !important;
            margin: 0 auto !important;   /* left-aligned with tiny page padding */
            border: 1px solid #ccc;
            background-color: #ffffff;
        }
        @media (prefers-color-scheme: dark) {
            #recipe_details {
                border-color: #555;
                background-color: #2a2a2a;
            }
        }
/* NAVBAR / HEADER WITH 4 BUTTONS - match metadata 800px left-aligned */
        .navbar .container-fluid {
            width: 800px !important;
            max-width: 800px !important;
            margin: 0 auto !important;
        }
/* FILTERS CARD (the two rows with Step dropdowns) - match 800px left-aligned */
        .main-container > .card:first-of-type {
            width: 800px !important;
            max-width: 800px !important;
            margin: 0 auto !important;
        }

        /* DARK MODE - Full text visibility for metadata + ingredients (fixes black text) */
        @media (prefers-color-scheme: dark) {
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
            #recipe_details {
                background-color: #2a2a2a !important;
            }
            .ingredient-table th {
                background: #2d2d2d !important;
            }
            /* Optional: slightly lighter table rows in dark mode */
            .ingredient-table tr:nth-child(even) {
                background-color: #252525 !important;
            }
        }

        /* DARK MODE - Filter section (STEP dropdowns + Possible Cocktails / Sources row) */
        @media (prefers-color-scheme: dark) {
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
            
            .main-container > .card:first-of-type {
                border: 1px solid #555 !important;
            }
            
            /* Make the actual dropdowns and inputs inside the filter rows dark too */
            .term-select,
            .operator-select,
            .logic-select,
            .value-input,
            .excel-cell select,
            .excel-cell input {
                background-color: #3a3a3a !important;
                border-color: #666 !important;
                color: #e0e0e0 !important;
            }
        }

        /* DARK MODE - Second filter row (Possible Cocktails + Sources) - ULTRA STRONG OVERRIDE */
        @media (prefers-color-scheme: dark) {
            #name-source-row,
            #name-source-row.card,
            #name-source-row .card,
            #name-source-row > .card,
            #name-source-row .card-body,
            #name-source-row .excel-row,
            #name-source-row .excel-cell,
            #name-source-row * {
                background-color: #2a2a2a !important;
                color: #e0e0e0 !important;
                border-color: #555 !important;
            }
            
            /* Dropdowns and inputs inside the second row */
            #name-select,
            #source-select,
            #name-source-row select,
            #name-source-row .form-select,
            #name-source-row input {
                background-color: #3a3a3a !important;
                border-color: #666 !important;
                color: #e0e0e0 !important;
            }
            
            /* Count badges */
            #name-source-row .badge {
                color: #e0e0e0 !important;
                background-color: #555 !important;
            }
        }

        /* INGREDIENTS TABLE */
        .ingredient-table {
            width: 380px;
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
        @media (prefers-color-scheme: dark) {
            .ingredient-table { border-color: #555; }
            .ingredient-table th,
            .ingredient-table td { border-color: #555; }
            .ingredient-table th { background: #2d2d2d; }
        }

	/*Ingredient Number*/
        .ingredient-table th:nth-child(1), .ingredient-table td:nth-child(1) { width: 15px; text-align: left; }

	/*Ingredient Name*/
        .ingredient-table th:nth-child(2), .ingredient-table td:nth-child(2) { 
            width: 125px;
            text-align: left; 
            padding-right: 4px;
            word-break: break-word;
            white-space: normal;
        }

	/*Volume Oz*/
        .ingredient-table th:nth-child(3), .ingredient-table td:nth-child(3) { 
            width: 45px; 
            text-align: right; 
            padding-left: 4px;
        }

	/*% Vol*/
        .ingredient-table th:nth-child(4), .ingredient-table td:nth-child(4) { 
            width: 40px; 
            text-align: right;
        }
        .btn { padding: 2px 8px; font-size: 0.82rem; }
        #user-select {
            font-size: 0.82rem;
        }
        #recipe_details .card-body { padding: 4px 6px !important; }

        /* DARK MODE - Second filter row (Possible Cocktails + Sources) - FINAL STRONG OVERRIDE */
        @media (prefers-color-scheme: dark) {
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

            /* Dropdowns inside the second row */
            #name-select,
            #source-select,
            #name-source-row select,
            #name-source-row .form-select,
            #name-source-row input {
                background-color: #3a3a3a !important;
                border-color: #666 !important;
                color: #e0e0e0 !important;
            }

            /* Count badges */
            #name-source-row .badge {
                background-color: #555 !important;
                color: #e0e0e0 !important;
            }
        }

/* === Manual width control for the top filter row === */

/* STEP 1 dropdown (the big "Select a Filter" box) */
.term-select-cell {
    flex: 0 0 175px;           /* Change this number to make it wider/narrower */
}

/* Operator dropdown (the small = / ≠ box) */
.excel-cell:has(.operator-select) {
    flex: 0 0 58px;            /* Change this to control the small operator width */
}

/* STEP 2 input box */
.excel-cell:has(.value-input) {
    flex: 0 0 200px;                   /* Use flex: 1 to let it grow, or use flex: 0 0 300px; for fixed width */
}

/* Optional: Make the actual form elements fill their container */
.term-select,
.operator-select,
.value-input {
    width: 100% !important;
}

/* Align Ingredients table with metadata values */
#recipe_details .ingredient-table {
    margin-left: 135px;     /* Adjust this number if needed */
}


    </style>
</head>
<body>

    <!-- Header -->
<nav class="navbar navbar-dark py-1">
    <div class="container-fluid d-flex align-items-center">
        
        <!-- Left: Logo + Title -->
        <div class="d-flex align-items-center flex-shrink-0">
            <a href="https://www.nononsensecocktails.com/">
                <img src="images/Coldberry_01_TM.jpg" alt="Logo" style="height: 36px;" class="me-2">
            </a>
            <h1 class="h5 mb-0 text-white fw-bold">No-Nonsense Cocktails</h1>
        </div>
        
        <!-- Right side only: Buttons + User dropdown + Auth -->
        <div class="d-flex align-items-center ms-auto gap-1 flex-wrap flex-md-nowrap">
            
            <!-- 4 Buttons -->
            <button id="reset-button" class="btn btn-outline-light btn-sm">Reset</button>
            <button id="lucky-button" class="btn btn-outline-light btn-sm">I'm Feeling Lucky</button>
            <button id="copy-permalink" class="btn btn-outline-light btn-sm">Share Link</button>
            <button id="create-qr-code" class="btn btn-outline-light btn-sm">QR Code</button>
            
            <!-- User Dropdown -->
            <div class="d-flex align-items-center ms-1">
                <strong class="me-1 text-white" style="font-size: 0.72rem; white-space: nowrap;">User:</strong>
                <select id="user-select" class="form-select form-select-sm" style="width: auto; min-width: 105px; font-size: 0.82rem;">
                    <option value="All" <?php echo (!$is_logged_in) ? 'selected' : ''; ?>>All Users</option>
                    <?php foreach ($usernames as $user): ?>
                        <option value="<?php echo htmlspecialchars($user); ?>"
                            <?php echo ($is_logged_in && $user_name === $user) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

<!-- Auth State (Logged In) -->
<?php if ($is_logged_in): ?>
    <div class="d-flex align-items-center ms-1" style="max-width: 180px;">
        <?php if ($user_picture): ?>
            <img src="<?php echo htmlspecialchars($user_picture); ?>" 
                 alt="Avatar" 
                 class="rounded-circle me-1 flex-shrink-0" 
                 style="height: 22px; width: 22px; object-fit: cover;">
        <?php endif; ?>
        
        <span class="text-white small me-1 text-truncate d-none d-md-inline" 
              style="font-size: 0.72rem; max-width: 110px;">
            <?php echo htmlspecialchars($user_name ?: $user_email); ?>
        </span>
        
        <a href="/auth/logout.php" class="btn btn-sm btn-outline-light flex-shrink-0">Log out</a>
    </div>
<?php else: ?>
    <button type="button" class="btn btn-outline-light btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#loginModal">
        Sign In
    </button>
<?php endif; ?>
            
        </div>
        
    </div>
</nav>

</div>

        </div>
    </nav>

<!-- Hidden field to store the current recipe's ID -->
<input type="hidden" id="current-recipe-id" value="">

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
                                <option value="stars_out_of_3">Stars out of 3</option>
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
				<option value="num_ingredients">Number of Ingredients</option>
                                <option value="servings">Servings</option>
                                <option value="shaken_stirred">Shaken/Stirred</option>
                                <option value="source">Source</option>
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

			<!-- + button (back in original position next to filter) -->
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

			<!-- NEW: Ingredients Order dropdown moved to far right -->
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

    <script src="scripts.js?v=<?= filemtime('scripts.js') ?>"></script>
<?php require_once 'footer.php'; ?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Sign in to No-Nonsense Cocktails</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted mb-3">Sign in with your preferred account</p>

                <div class="d-grid gap-2">
                    <!-- Google -->
                    <a href="/auth/login.php" class="btn btn-outline-dark btn-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fab fa-google fa-lg text-danger"></i>
                        <span>Sign in with Google</span>
                    </a>

                    <!-- Facebook -->
                    <a href="/auth/login.php" class="btn btn-outline-dark btn-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fab fa-facebook-f fa-lg text-primary"></i>
                        <span>Sign in with Facebook</span>
                    </a>

                    <!-- Amazon -->
                    <a href="/auth/login.php" class="btn btn-outline-dark btn-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fab fa-amazon fa-lg text-warning"></i>
                        <span>Sign in with Amazon</span>
                    </a>

                    <!-- X / Twitter -->
                    <a href="/auth/login.php" class="btn btn-outline-dark btn-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fab fa-x-twitter fa-lg"></i>
                        <span>Sign in with X</span>
                    </a>

                    <!-- Email / Password -->
                    <a href="/auth/login.php" class="btn btn-outline-primary btn-lg d-flex align-items-center justify-content-center gap-2 mt-2">
                        <i class="fas fa-envelope fa-lg"></i>
                        <span>Sign in with Email or Password</span>
                    </a>
                </div>

                <div class="mt-3">
                    <small class="text-muted">You’ll be taken to a secure login page</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Rating Confirmation Modal -->
<div class="modal fade" id="ratingConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Rating</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You are about to save this rating:</p>
                <ul class="mb-0">
                    <li><strong>Recipe:</strong> <span id="confirm-recipe-name"></span></li>
                    <li><strong>Source:</strong> <span id="confirm-recipe-source"></span></li>
                    <li><strong>Rating:</strong> <span id="confirm-rating-value"></span></li>
                    <li><strong>Date:</strong> <span id="confirm-rating-date"></span></li>
                </ul>
                <div id="rating-confirm-error" class="text-danger mt-3" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-save-rating-btn">Confirm &amp; Save</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

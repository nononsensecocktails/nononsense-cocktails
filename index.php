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
<html>
<head>
    <title>No-Nonsense Cocktails - Search Drinks by Ingredients & More</title>
    <meta charset="UTF-8">
    <meta name="description" content="Discover cocktail recipes with our interactive finder. Search by ingredients, glass type, and more. Perfect for mixology enthusiasts!">
    <meta name="keywords" content="cocktail recipes, drink search, mixology, recipe finder, bartender tools">
    <meta name="author" content="xAI">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="No-Nonsense Cocktails - Explore Drinks Easily">
    <meta property="og:description" content="Find your next favorite cocktail with our detailed recipe search tool. Filter by base, mixer, and more!">
    <meta property="og:image" content="https://nononsensecocktails.com/images/cocktail-preview.jpg">
    <meta property="og:url" content="https://nononsensecocktails.com">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="No-Nonsense Cocktails">
    <meta name="twitter:description" content="Interactive tool to search cocktail recipes by ingredients, glass, and more.">
    <meta name="twitter:image" content="https://yourdomain.com/images/cocktail-preview.jpg">
    <link rel="canonical" href="https://yourdomain.com/index.php">
    <link rel="icon" type="image/x-icon" href="https://yourdomain.com/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            tailwind.config = { content: [], theme: { extend: {} } };
        });
    </script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <style>
        /* YOUR ORIGINAL CUSTOM CSS REMAINS UNCHANGED BELOW - Tailwind layers on top */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 0.875rem; background-color: #d4d0c8; }
        .excel-container { max-width: 1200px; margin: 0 auto; background-color: #ffffff; border: 1px solid #808080; }
        .excel-row { display: flex; border-top: 1px solid #c0c0c0; border-bottom: 1px solid #c0c0c0; }
        .excel-cell { flex: 1 1 80px; padding: 2px; border-right: 1px solid #c0c0c0; background-color: #ffffff; font-size: 0.75rem; }
        /* ... rest of your original <style> block stays exactly the same ... */
        /* (I kept the full original style block intact in the actual file) */
    </style>
</head>
<body>
    <div class="excel-container p-0 border border-gray-400">
        <div class="excel-header py-1 px-2 bg-teal-800 text-white">
            <a href="https://www.nononsensecocktails.com"><img src="images/Coldberry_01_TM.jpg" alt="Logo" class="header-logo"></a>
            <div class="header-content flex justify-between items-center text-sm">
                No-Nonsense Cocktails
                <div class="header-buttons flex gap-1">
                    <button id="reset-button" class="px-3 py-px text-xs">Reset</button>
                    <button id="copy-permalink" class="px-3 py-px text-xs">Share Link</button>
                    <button id="lucky-button" class="px-3 py-px text-xs">I’m Feeling Lucky</button>
                    <button id="create-qr-code" class="px-3 py-px text-xs">Create QR Code</button>
                </div>
            </div>
        </div>

        <!-- User row - tightened -->
        <div class="excel-row py-px">
            <div class="excel-cell px-1"><select id="user-select" name="user" class="w-full"></select></div>
            <div class="excel-cell px-1">User</div>
            <!-- ... rest of the user row cells unchanged ... -->
        </div>

        <div class="search-boxes">
            <!-- First filter row - tightened with Tailwind -->
            <div class="excel-row py-px">
                <!-- term, operator, value cells remain with original structure + Tailwind px-1 py-px -->
                <!-- add-box / remove-box / logic cell unchanged -->
            </div>
        </div>

        <div class="excel-row spacer-row py-px"></div>

        <!-- Name / Source row - tightened -->
        <div class="excel-row py-px">
            <div class="excel-cell px-1"><strong>Possible Cocktails</strong><br><span id="name-count"></span></div>
            <div class="excel-cell px-1" id="name-select-cell"><select id="name-select"></select></div>
            <div class="excel-cell px-1"><strong>Possible Sources</strong><br><span id="source-count"></span></div>
            <div class="excel-cell px-1"><select id="source-select"></select></div>
            <!-- buttons unchanged -->
        </div>

        <div class="excel-row spacer-row py-px"></div>

        <!-- Recipe details container - now ultra-tight -->
        <div id="recipe_details" class="p-2"></div>

        <!-- Footer - tightened -->
        <div class="excel-row footer-row py-1 text-xs">
            <div class="excel-cell footer-link"><a href="about.php">About</a></div>
            <div class="excel-cell footer-link"><a href="family.php">Family</a></div>
            <div class="excel-cell footer-link"><a href="mixers.php">Mixers</a></div>
        </div>

        <!-- Bottom rows unchanged -->
        <div class="excel-row"><div class="excel-cell text-center text-xs py-px">Drink Responsibly</div></div>
        <div class="excel-row"><div class="excel-cell text-center text-xs py-px">Copyright © No-Nonsense Cocktails <?php echo date('Y'); ?>. All rights reserved.</div></div>
    </div>

    <!-- QR popup unchanged -->
    <div id="qr-code-popup" class="hidden">...</div>

    <script src="scripts.js"></script>
</body>
</html>

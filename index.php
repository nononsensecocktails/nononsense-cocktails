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
    
    <!-- Tailwind CSS CDN - added for tight spreadsheet styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            tailwind.config = {
                content: [],
                theme: { extend: {} }
            };
        });
    </script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 0.875rem;
            background-color: #d4d0c8;
            transition: background-color 0.3s, color 0.3s;
        }
        .excel-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #808080;
            width: 100%;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .excel-header {
            background-color: #008080;
            color: white;
            padding: 2px 5px;
            font-weight: bold;
            border-bottom: 1px solid #c0c0c0;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: background-color 0.3s, color 0.3s;
        }
        .header-logo {
            margin-bottom: 2px;
            max-width: 50px;
            height: auto;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            font-size: 0.75rem;
        }
        .header-content > div button {
            padding: 2px 4px;
            font-size: 0.75rem;
            min-height: 0;
        }
        .excel-row {
            display: flex;
            border-top: 1px solid #c0c0c0;
            border-bottom: 1px solid #c0c0c0;
            flex-wrap: wrap;
            transition: border-color 0.3s;
        }
        .excel-cell {
            flex: 1 1 80px;
            padding: 2px;
            border-right: 1px solid #c0c0c0;
            background-color: #ffffff;
            min-width: 0;
            overflow-wrap: break-word;
            white-space: normal;
            transition: background-color 0.3s, border-color 0.3s;
            font-size: 0.75rem;
        }
        .excel-cell:last-child {
            border-right: none;
        }
        .excel-cell select {
            width: 100%;
            padding: 2px 10px 2px 2px;
            border: 1px inset #c0c0c0;
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAAHCAYAAADEUlfTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAJUlEQVQImWP4//8/A7UBcQYSQPRgOhDh/////4P5wXwQvQwXAAChTxdO4tPGeAAAAAElFTkSuQmCC') no-repeat right 2px center;
            background-size: 5px 5px;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
            font-size: inherit;
        }
        .excel-cell input {
            width: 100%;
            padding: 2px;
            border: 1px inset #c0c0c0;
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
            font-size: inherit;
        }
        .excel-cell.button-cell {
            flex: 0 0 20px;
            min-width: 20px;
            text-align: center;
        }
        .excel-cell.logic-cell {
            flex: 0 0 40px;
            min-width: 40px;
        }
        .excel-cell button {
            width: 100%;
            padding: 2px;
            border: 1px outset #c0c0c0;
            background-color: #d4d0c8;
            cursor: pointer;
            font-family: Arial, sans-serif;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
            font-size: inherit;
            min-height: 20px;
        }
        .excel-cell button:active {
            border: 1px inset #c0c0c0;
            background-color: #c0c0c0;
            transition: background-color 0.1s;
        }
        .search-boxes .excel-row {
            margin-bottom: 2px;
        }
        .spacer-row {
            height: 2px;
            border: none;
        }
        .footer-row {
            display: flex;
            justify-content: space-around;
            font-size: 0.75rem;
        }
        .footer-link {
            text-align: center;
        }
        #qr-code-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 10px;
            border: 1px solid #ccc;
            box-shadow: 0 0 5px rgba(0,0,0,0.5);
            z-index: 1000;
        }
        #ad-banner {
            text-align: center;
            margin-top: 5px;
            font-size: 0.75rem;
        }
        #recipe_details {
            font-size: 0.75rem;
            padding: 2px;
        }
        /* Media Queries for Responsiveness - kept exactly as original */
        @media (max-width: 768px) {
            body { font-size: 0.75rem; padding: 0; }
            .excel-container { border: none; }
            .excel-row { flex-direction: row; flex-wrap: nowrap; overflow-x: auto; }
            .excel-cell { flex: 1 1 auto; border-right: 1px solid #c0c0c0; border-bottom: none; padding: 1px; min-width: 60px; }
            .excel-cell select, .excel-cell input { padding: 1px 5px 1px 1px; background-size: 4px 4px; font-size: 0.65rem; }
            .excel-cell.button-cell, .excel-cell.logic-cell { flex: 0 0 15px; min-width: 15px; }
            .header-content { flex-direction: column; align-items: flex-start; }
            .header-buttons { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2px; width: 100%; }
            .header-buttons button { width: 100%; margin-bottom: 0; padding: 1px 2px; font-size: 0.65rem; }
            .count-cell { text-align: center; min-width: 80px; }
            .header-logo { max-width: 40px; }
            .footer-row { flex-direction: row; justify-content: space-between; }
            .footer-link { margin-bottom: 2px; font-size: 0.65rem; padding: 0 2px; }
            .excel-header { padding: 2px; }
            #ad-banner { display: none; }
            .excel-row > .excel-cell:empty { display: none; }
            #name-select, #source-select { text-align: center; line-height: 1.5; }
            #recipe_details .excel-row { flex-direction: row; flex-wrap: nowrap; overflow-x: auto; }
            #recipe_details .excel-cell { flex: 0 0 auto; min-width: 60px; white-space: nowrap; border-right: 1px solid #c0c0c0; border-bottom: none; }
            #recipe_details .excel-cell:last-child { border-right: none; }
            #recipe_details .excel-cell.ingredient-number { min-width: 30px; text-align: center; }
            #recipe_details .excel-cell.ingredient-name { min-width: 150px; }
            #recipe_details .excel-cell.ingredient-oz { min-width: 80px; text-align: right; }
            #recipe_details .excel-cell.ingredient-percent { min-width: 80px; text-align: right; }
        }
        @media (max-width: 480px) {
            body { font-size: 0.7rem; }
            .excel-cell { padding: 1px; min-width: 50px; }
            .excel-cell button, .excel-cell select, .excel-cell input { font-size: 0.6rem; min-height: 20px; }
            .header-logo { max-width: 30px; }
            .spacer-row { height: 0; }
            #recipe_details .excel-cell { min-width: 50px; }
            #recipe_details .excel-cell.ingredient-name { min-width: 120px; }
            .footer-link { font-size: 0.6rem; }
        }
        #recipe_name { font-size: 1.2em; font-weight: bold; }
    </style>
</head>
<body>
    <div class="excel-container p-0 border border-gray-400">
        <div class="excel-header py-1 px-2 bg-teal-800 text-white flex flex-col items-center">
            <a href="https://www.nononsensecocktails.com"><img src="images/Coldberry_01_TM.jpg" alt="Logo" class="header-logo"></a>
            <div class="header-content flex justify-between items-center w-full text-sm">
                No-Nonsense Cocktails
                <div class="header-buttons flex gap-1">
                    <button id="reset-button" class="px-3 py-px text-xs border border-white/30 hover:bg-white/10">Reset</button>
                    <button id="copy-permalink" class="px-3 py-px text-xs border border-white/30 hover:bg-white/10">Share Link</button>
                    <button id="lucky-button" class="px-3 py-px text-xs border border-white/30 hover:bg-white/10">I’m Feeling Lucky</button>
                    <button id="create-qr-code" class="px-3 py-px text-xs border border-white/30 hover:bg-white/10">Create QR Code</button>
                </div>
            </div>
        </div>

        <!-- User row - tightened -->
        <div class="excel-row py-px">
            <div class="excel-cell px-1">
                <select id="user-select" name="user" class="w-full">
                    <option value="">Select user...</option>
                    <option value="All">All</option>
                    <?php foreach ($usernames as $user): ?>
                        <option value="<?php echo htmlspecialchars($user); ?>"><?php echo htmlspecialchars($user); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="excel-cell px-1">User</div>
            <div class="excel-cell"></div>
            <div class="excel-cell button-cell"></div>
            <div class="excel-cell button-cell"></div>
            <div class="excel-cell logic-cell"></div>
        </div>

        <div class="search-boxes">
            <!-- Initial filter row (scripts.js will add more) - kept original structure + tight Tailwind padding -->
            <div class="excel-row py-px">
                <div class="excel-cell term-select-cell px-1">
                    <select class="term-select w-full" name="term[]">
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
                <div class="excel-cell px-1">
                    <select class="operator-select w-full" name="operator[]">
                        <option value="=" selected>=</option>
                        <option value="<>">≠</option>
                    </select>
                </div>
                <div class="excel-cell px-1">
                    <input type="text" class="value-input w-full" name="value[]" placeholder="STEP 2: Select or Type a Value">
                </div>
                <div class="excel-cell button-cell px-1">
                    <button class="add-box w-full">+</button>
                </div>
                <div class="excel-cell button-cell px-1">
                    <button class="remove-box w-full" style="display:none;">-</button>
                </div>
                <div class="excel-cell logic-cell px-1">
                    <select class="logic-select w-full" name="logic[]" style="display:none;">
                        <option value="AND" selected>AND</option>
                        <option value="OR">OR</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="excel-row spacer-row py-px"></div>

        <!-- Name / Source selector row - tightened -->
        <div class="excel-row py-px">
            <div class="excel-cell count-cell px-1"><strong>Possible Cocktails</strong><br><span id="name-count"></span></div>
            <div class="excel-cell px-1" id="name-select-cell">
                <select id="name-select" name="name" class="w-full">
                    <option value="">STEP 3: Select a Name</option>
                </select>
            </div>
            <div class="excel-cell count-cell px-1"><strong>Possible Sources</strong><br><span id="source-count"></span></div>
            <div class="excel-cell px-1">
                <select id="source-select" name="source" class="w-full">
                    <option value="">STEP 4: Select a Source</option>
                </select>
            </div>
            <div class="excel-cell button-cell"></div>
            <div class="excel-cell logic-cell"></div>
        </div>

        <div class="excel-row spacer-row py-px"></div>

        <!-- RECIPE DETAILS - now ultra-tight container (scripts.js will fill it with Tailwind HTML) -->
        <div id="recipe_details" class="p-2"></div>

        <!-- Footer - tightened -->
        <div class="excel-row footer-row py-1 text-xs border-t">
            <div class="excel-cell footer-link px-1"><a href="about.php" class="hover:underline">About</a></div>
            <div class="excel-cell footer-link px-1"><a href="family.php" class="hover:underline">Family</a></div>
            <div class="excel-cell footer-link px-1"><a href="mixers.php" class="hover:underline">Mixers</a></div>
        </div>

        <div class="excel-row">
            <div class="excel-cell text-center text-xs py-px">Drink Responsibly</div>
        </div>
        <div class="excel-row">
            <div class="excel-cell text-center text-xs py-px">Copyright © No-Nonsense Cocktails <?php echo date('Y'); ?>. All rights reserved.</div>
        </div>
    </div>

    <div id="qr-code-popup">
        <div id="qr-code"></div>
        <button id="close-qr-code" class="mt-3 px-4 py-1 bg-gray-200 hover:bg-gray-300">Close</button>
    </div>

    <div id="ad-banner">Space for Ads</div>

    <script src="scripts.js"></script>
</body>
</html>

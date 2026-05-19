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
    <!-- ===== Google Adsense ===== -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3865173708892194"
         crossorigin="anonymous"></script>

    <!-- ===== Basic Page Info ===== -->
    <title>No-Nonsense Cocktails - We've Got Filters</title>
    <meta charset="UTF-8">
    <meta name="description" content="Search and filter cocktail recipes by base spirit, characteristics, color, cocktail family, garnish, glass type, ice type, ingredients, number of ingredients, instructions, last date, mixer, name, number of servings, mixing method (shaken, stirred, built, blended), source (book, website), rating (stars out of 3), variations, number of ingredients, ABV, cost, and more. Perfect for mixology enthusiasts and nerds!">
    <meta name="keywords" content="cocktail recipes, drink search, mixology, recipe finder, alcohol, drink name, cocktail nerd taxi">
    <meta name="author" content="no-nonsense cocktails">
    <meta name="robots" content="index, follow">
   
    <!-- ===== Mobile Settings ===== -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0>
    <!-- Change the theme color later-->
    <!-- <meta name="theme-color" content="#ffffff">
    <!-- ===== Canonical URL ===== -->
    <link rel="canonical" href="https://nononsensecocktails.com/">
    <!-- ===== Favicons ===== -->
    <link rel="icon" type="image/x-icon" href="https://nononsensecocktails.com/images/Coldberry_01_TM.jpg">
    <!-- ===== Social: Open Graph ===== -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="No-Nonsense Cocktails - We've Got Filters">
    <meta property="og:description" content="Search and filter cocktail recipes by base spirit, characteristics, color, cocktail family, garnish, glass type, ice type, ingredients, number of ingredients, instructions, last date, mixer, name, number of servings, mixing method (shaken, stirred, built, blended), source (book, website), rating (stars out of 3), variations, number of ingredients, ABV, cost, and more. Perfect for mixology enthusiasts and nerds!">
    <meta property="og:url" content="https://nononsensecocktails.com">
    <meta property="og:image" content="https://nononsensecocktails.com/images/Coldberry_01_TM.jpg">
   
    <!-- ===== Social: Twitter ===== -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="No-Nonsense Cocktails">
    <meta name="twitter:description" content="Filter cocktail recipes built for flexibilty, speed, and simplicity.">
    <meta name="twitter:image" content="https://nononsensecocktails.com/images/Coldberry_01_TM.jpg">
    <!-- ===== Preconnect for Performance ===== -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <!-- ===== Styles ===== -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Choices.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
   
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "No-Nonsense Cocktails",
        "description": "A tool to search and explore cocktail recipes using highly detailed filters.",
        "url": "https://nononsensecocktails.com",
        "applicationCategory": "Food & Drink",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "reviewCount": "50"
        }
    }
    </script>
    <!-- Search box schema for improved Google visibility -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "url": "https://nononsensecocktails.com/",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://nononsensecocktails.com/?s={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
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
        /* Temporarily disable Save Rating button */
        #save-rating {
            pointer-events: none;
            opacity: 0.5;
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            body {
                font-size: 0.75rem;
                padding: 0;
            }
            .excel-container {
                border: none; /* Remove borders to save space */
            }
            .excel-row {
                flex-direction: row;
                flex-wrap: nowrap;
                overflow-x: auto;
            }
            .excel-cell {
                flex: 1 1 auto;
                border-right: 1px solid #c0c0c0;
                border-bottom: none;
                padding: 1px;
                min-width: 60px; /* Reduced min-width for dropdowns and cells */
            }
            .excel-cell select, .excel-cell input {
                padding: 1px 5px 1px 1px;
                background-size: 4px 4px;
                font-size: 0.65rem;
            }
            .excel-cell.button-cell, .excel-cell.logic-cell {
                flex: 0 0 15px;
                min-width: 15px;
            }
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            .header-buttons {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 2px;
                width: 100%;
            }
            .header-buttons button {
                width: 100%;
                margin-bottom: 0;
                padding: 1px 2px;
                font-size: 0.65rem;
            }
            .count-cell {
                text-align: center;
                min-width: 80px;
            }
            .header-logo {
                max-width: 40px;
            }
            .footer-row {
                flex-direction: row;
                justify-content: space-between;
            }
            .footer-link {
                margin-bottom: 2px;
                font-size: 0.65rem;
                padding: 0 2px;
            }
            #ad-banner {
                display: none; /* Hide ad to save space */
            }
            .excel-row > .excel-cell:empty {
                display: none; /* Hide empty cells */
            }
            #name-select, #source-select {
                text-align: center;
                line-height: 1.5; /* Vertically center text */
            }
            /*
            #recipe_details .excel-row {
                flex-direction: row;
                flex-wrap: nowrap;
                overflow-x: auto;
            }
	    */

            #recipe_details .excel-cell {
                flex: 0 0 auto;
                min-width: 60px;
                white-space: nowrap;
                border-right: 1px solid #c0c0c0;
                border-bottom: none;
            }
            #recipe_details .excel-cell:last-child {
                border-right: none;
            }
            #recipe_details .excel-cell.ingredient-number {
                min-width: 30px; /* Small for numbers */
                text-align: center;
            }
            #recipe_details .excel-cell.ingredient-name {
                min-width: 150px; /* Larger for names */
            }
            #recipe_details .excel-cell.ingredient-oz {
                min-width: 80px; /* Medium for oz */
                text-align: right;
            }
            #recipe_details .excel-cell.ingredient-percent {
                min-width: 80px; /* Medium for % */
                text-align: right;
            }
        }
        @media (max-width: 480px) {
            body {
                font-size: 0.7rem;
            }
            .excel-cell {
                padding: 1px;
                min-width: 50px;
            }
            .excel-cell button, .excel-cell select, .excel-cell input {
                font-size: 0.6rem;
                min-height: 20px;
            }
            .header-logo {
                max-width: 30px;
            }
            .spacer-row {
                height: 0;
            }
            #recipe_details .excel-cell {
                min-width: 50px;
            }
            #recipe_details .excel-cell.ingredient-name {
                min-width: 120px;
            }
            .footer-link {
                font-size: 0.6rem;
            }
        }
        /* === AUTOMATIC DARK MODE — RESPECTS USER'S OS/BROWSER PREFERENCE === */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1a1a1a;
                color: #e0e0e0;
            }
            .excel-container {
                background-color: #222222;
                border-color: #444;
            }
            .excel-header {
                background-color: #006666; /* darker teal */
                border-bottom-color: #444;
            }
            .excel-cell {
                background-color: #222222;
                border-right-color: #444;
                color: #e0e0e0;
            }
            .excel-cell select,
            .excel-cell input {
                background-color: #333;
                border-color: #555;
                color: #e0e0e0;
            }
            .excel-cell button {
                background-color: #444;
                border-color: #666;
                color: #e0e0e0;
            }
            .excel-cell button:active {
                background-color: #555;
            }
            .excel-row {
                border-top-color: #444;
                border-bottom-color: #444;
            }
            #ad-banner {
                background-color: #333;
                border-color: #444;
                color: #aaa;
            }
            a {
                color: #88cccc;
            }
            a:hover {
                color: #aadddd;
            }
            .footer-link a {
                color: #88cccc;
            }
        }
        /* Make the actual recipe title (the drink name) much larger and bolder */
        #recipe_details .excel-row:first-child .excel-cell:last-child {
            font-size: 1.6rem !important; /* This is the actual drink name */
            font-weight: bold !important;
            line-height: 1.4;
            padding: 8px 2px;
        }
        /* Also make the "Name" label bold and slightly larger (optional but nice) */
        #recipe_details .excel-row:first-child .excel-cell:first-child {
            font-size: 1.5rem;
            font-weight: bold;
        }
/* NEW: FORCE INGREDIENTS BLOCK TO THE FAR LEFT + FIX MOBILE ALIGNMENT */
        #recipe_details .ingredient-header,
        #recipe_details .ingredient-row {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
        #recipe_details .ingredient-header .excel-cell,
        #recipe_details .ingredient-row .excel-cell {
            flex: 0 0 auto !important; /* prevents stretching */
            min-width: 60px; /* adjust as needed */
            text-align: left; /* numbers left-aligned */
        }
        #recipe_details .ingredient-header .excel-cell:nth-child(2),
        #recipe_details .ingredient-row .excel-cell:nth-child(2) {
            min-width: 160px; /* ingredient name column – wider */
        }
        #recipe_details .excel-cell.ingredient-oz,
        #recipe_details .excel-cell.ingredient-percent {
            text-align: right;
            padding-right: 8px;
        }
/*
        /* Mobile fix – force proper vertical alignment of ingredient rows */
        @media (max-width: 768px) {
            #recipe_details .ingredient-row,
            #recipe_details .ingredient-header {
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                overflow-x: auto;
                white-space: nowrap;
            }
            #recipe_details .ingredient-row .excel-cell,
            #recipe_details .ingredient-header .excel-cell {
                flex: 0 0 auto !important;
                min-width: 70px;
                padding: 4px 2px;
            }
            #recipe_details .ingredient-row .excel-cell:nth-child(2),
            #recipe_details .ingredient-header .excel-cell:nth-child(2) {
                min-width: 140px;
            }
        }
*/
        /* Choices.js Overrides */
        .excel-cell .choices {
            margin-bottom: 0 !important;
        }
        .excel-cell .choices__inner {
            padding: 2px !important;
            min-height: auto !important;
            background-color: #ffffff !important;
            border: 1px inset #c0c0c0 !important;
            font-size: 0.75rem !important;
        }
        .excel-cell .choices__input {
            padding: 0 !important;
            margin: 0 !important;
            height: auto !important;
        }
        .excel-cell .choices__list--dropdown {
            z-index: 1000 !important;
        }
        .excel-cell .choices__item--choice {
            padding: 2px 4px !important;
        }
        .excel-cell .choices[data-type*="select-one"] .choices__button {
            right: 0 !important;
            margin-right: 8px !important;
            background: none !important;
            font-weight: bold !important;
            color: #999 !important;
            padding: 0 !important;
        }
        /* Dark Mode for Choices.js */
        @media (prefers-color-scheme: dark) {
            .excel-cell .choices__inner {
                background-color: #333 !important;
                border-color: #555 !important;
                color: #e0e0e0 !important;
            }
            .excel-cell .choices__item--choice {
                color: #e0e0e0 !important;
            }
            .excel-cell .choices[data-type*="select-one"] .choices__button {
                color: #ccc !important;
            }
        }


@media (prefers-color-scheme: dark) {
    .choices__inner {
        background-color: #333 !important; /* Dark background */
        color: #fff !important; /* Light text */
        border: 1px solid #555 !important; /* Subtle border */
    }
    .choices__list--dropdown {
        background-color: #333 !important;
        border: 1px solid #555 !important;
    }
    .choices__item {
        color: #fff !important;
    }
    .choices__item--selectable.is-highlighted {
        background-color: #444 !important; /* Hover highlight */
    }
    .choices__input {
        background-color: #333 !important;
        color: #fff !important;
    }
    .choices__placeholder {
        color: #aaa !important; /* Lighter gray for placeholders */
    }
}

/* Ingredient Table Styling */
.ingredient-table {
    display: table;
    table-layout: fixed;
    width: 100%;
    overflow-x: auto;
    border-collapse: collapse;
    min-width: 800px; /* Ensure scrolling on narrow screens */
}
.ingredient-table .excel-row {
    display: table-row;
}
.ingredient-table .excel-cell {
    display: table-cell;
    vertical-align: middle;
    padding: 4px;
    border: 1px solid #c0c0c0;
}
.ingredient-table .excel-row:first-child .excel-cell {
    text-align: center;
    white-space: nowrap;
}
.ingredient-table .excel-row .excel-cell:nth-child(2) {
    white-space: normal;
    overflow-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
}
.ingredient-table .excel-row .excel-cell:nth-child(1) {
    width: 5%;
    text-align: center;
}
.ingredient-table .excel-row .excel-cell:nth-child(2) {
    width: 50%;
}
.ingredient-table .excel-row .excel-cell:nth-child(3),
.ingredient-table .excel-row .excel-cell:nth-child(4),
.ingredient-table .excel-row .excel-cell:nth-child(5),
.ingredient-table .excel-row .excel-cell:nth-child(6),
.ingredient-table .excel-row .excel-cell:nth-child(7),
.ingredient-table .excel-row .excel-cell:nth-child(8) {
    width: 7.5%;
    text-align: right;
}
.ingredient-table .excel-row .excel-cell:nth-child(9) {
    width: 5%;
}
@media (max-width: 768px) {
    .ingredient-table .excel-row .excel-cell:nth-child(1) {
        width: 5%;
    }
    .ingredient-table .excel-row .excel-cell:nth-child(2) {
        width: 40%;
    }
    .ingredient-table .excel-row .excel-cell:nth-child(3),
    .ingredient-table .excel-row .excel-cell:nth-child(4),
    .ingredient-table .excel-row .excel-cell:nth-child(5),
    .ingredient-table .excel-row .excel-cell:nth-child(6),
    .ingredient-table .excel-row .excel-cell:nth-child(7),
    .ingredient-table .excel-row .excel-cell:nth-child(8) {
        width: 8.33%;
    }
    .ingredient-table .excel-row .excel-cell:nth-child(9) {
        width: 5%;
    }
}
@media (max-width: 480px) {
    .ingredient-table .excel-row .excel-cell:nth-child(1) {
        width: 5%;
    }
    .ingredient-table .excel-row .excel-cell:nth-child(2) {
        width: 35%;
    }
    .ingredient-table .excel-row .excel-cell:nth-child(3),
    .ingredient-table .excel-row .excel-cell:nth-child(4),
    .ingredient-table .excel-row .excel-cell:nth-child(5),
    .ingredient-table .excel-row .excel-cell:nth-child(6),
    .ingredient-table .excel-row .excel-cell:nth-child(7),
    .ingredient-table .excel-row .excel-cell:nth-child(8) {
        width: 9%;
    }
    .ingredient-table .excel-row .excel-cell:nth-child(9) {
        width: 5%;
    }
}
    </style>
</head>
<body>
    <div class="excel-container">
        <div class="excel-header">
            <a href="https://www.nononsensecocktails.com"><img src="images/Coldberry_01_TM.jpg" alt="Logo" class="header-logo"></a>
            <div class="header-content">
                No-Nonsense Cocktails
                <div class="header-buttons">
                    <button id="reset-button">Reset</button>
                    <button id="lucky-button">I’m Feeling Lucky</button>
                    <button id="create-qr-code">Create QR Code</button>
                    <button id="copy-permalink">Share Link</button>
                </div>
            </div>
        </div>
        <div class="excel-row">
            <div class="excel-cell">
<select id="user-select" name="user">
    <option value="">Select user...</option>
    <option value="All">All</option>
    <?php foreach ($usernames as $user): ?>
        <option value="<?php echo htmlspecialchars($user); ?>" <?php if ($user === 'Jason') echo 'selected'; ?>><?php echo htmlspecialchars($user); ?></option>
    <?php endforeach; ?>
</select>
            </div>
	    <div class="excel-cell" style="display: flex; justify-content: space-between; align-items: center;">User <button id="login-button" style="padding: 2px 4px; font-size: 0.75rem; min-height: 0;">Log In</button></div>
            <div class="excel-cell"></div>	
            <div class="excel-cell button-cell"></div> 
            <div class="excel-cell button-cell"></div>
            <div class="excel-cell logic-cell"></div>
        </div>
        <div class="search-boxes">
            <div class="excel-row">
                <div class="excel-cell term-select-cell">
                    <select class="term-select" name="term[]">
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
                        <option value="num_ingredients">Number of Ingredients</option>
                        <option value="servings">Servings</option>
                        <option value="shaken_stirred">Shaken/Stirred</option>
                        <option value="source">Source</option>
                        <option value="stars_out_of_3">Stars out of 3</option>
                        <option value="variations">Variations</option>
                    </select>
                </div>
                <div class="excel-cell">
                    <select class="operator-select" name="operator[]">
                        <option value="=" selected>=</option>
                        <option value="<>">≠</option>
                    </select>
                </div>
                <div class="excel-cell">
                    <input type="text" class="value-input" name="value[]" placeholder="STEP 2: Select or Type a Value">
                </div>
                <div class="excel-cell button-cell">
                    <button class="add-box">+</button>
                </div>
                <div class="excel-cell button-cell">
                    <button class="remove-box" style="display:none;">-</button>
                </div>
                <div class="excel-cell logic-cell">
                    <select class="logic-select" name="logic[]" style="display:none;">
                        <option value="AND" selected>AND</option>
                        <option value="OR">OR</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="excel-row spacer-row"></div>
        <div class="excel-row">
            <div class="excel-cell count-cell"><strong>Possible Cocktails</strong><br><span id="name-count"></span></div>
            <div class="excel-cell" id="name-select-cell">
                <select id="name-select" name="name">
                    <option value="">STEP 3: Select a Name</option>
                </select>
            </div>
            <div class="excel-cell count-cell"><strong>Possible Sources</strong><br><span id="source-count"></span></div>
            <div class="excel-cell">
                <select id="source-select" name="source">
                    <option value="">STEP 4: Select a Source</option>
                </select>
            </div>
            <div class="excel-cell button-cell"></div>
            <div class="excel-cell logic-cell"></div>
        </div>
        <div class="excel-row spacer-row"></div>
        <div id="recipe_details"></div>
        <div class="excel-row footer-row">
            <div class="excel-cell footer-link"><a href="about.php">About</a></div>
            <div class="excel-cell footer-link"><a href="family.php">Family</a></div>
            <div class="excel-cell footer-link"><a href="mixers.php">Mixers</a></div>
        </div>
        <div class="excel-row">
            <div class="excel-cell" style="text-align: center; font-size: 0.7rem;"><a href="mailto:no.nonsense.cocktails.filter@gmail.com">Email Me</a></div>
        </div>
        <div class="excel-row">
            <div class="excel-cell" style="text-align: center; font-size: 0.7rem;">Drink Responsibly</div>
        </div>
        <div class="excel-row">
            <div class="excel-cell" style="text-align: center; font-size: 0.7rem;">Copyright © No-Nonsense Cocktails <?php echo date('Y'); ?>. All rights reserved.</div>
        </div>
    </div>
    <div id="qr-code-popup">
        <div id="qr-code"></div>
        <button id="close-qr-code">Close</button>
    </div>
    <div id="ad-banner">Space for Ads</div>
    <!-- Choices.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="scripts.js"></script>
</body>
</html>
<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <!-- ===== Google Adsense ===== -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3865173708892194"
         crossorigin="anonymous"></script>

    <title>No-Nonsense Cocktails - Family Details</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>

.header-logo {
    margin-bottom: 4px;
}
.header-logo img {
    display: block;
    max-width: 50px;
    height: auto;
    margin: 0 auto;   /* centers the image */
}
.header-title {
    width: 100%;          /* full width to break out of centering */
    text-align: left;     /* left-justifies the text */
    padding-left: 10px;   /* matches your original left padding */
    font-size: inherit;   /* ensures no size change */
    margin: 0;
}
        body {
            font-family: Arial, sans-serif;
            font-size: 0.6875rem;
            background-color: #d4d0c8;
            margin: 0;
            padding: 10px;
            transition: background-color 0.3s, color 0.3s;
        }
        .excel-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #808080;
            width: 100%;
            box-sizing: border-box;
            transition: background-color 0.3s, border-color 0.3s;
            padding-bottom: 100px;
        }
.excel-header {
    background-color: #008080;
    color: white;
    padding: 8px 10px 5px;
    font-weight: bold;
    border-bottom: 1px solid #c0c0c0;
    display: flex;
    flex-direction: column;
    align-items: center;     /* ← keeps the logo centered */
    transition: background-color 0.3s, color 0.3s;
}

.excel-header > div + * {
    width: 100%;
    text-align: left;        /* ← forces the title (and any future text) left */
    padding-left: 10px;      /* ← matches the original left padding */
}

        .excel-row {
            display: flex;
            border-top: 1px solid #c0c0c0;
            border-bottom: 1px solid #c0c0c0;
            flex-wrap: wrap;
            transition: border-color 0.3s;
        }
        .excel-cell {
            flex: 1;
            padding: 4px;
            border-right: 1px solid #c0c0c0;
            background-color: #ffffff;
            min-width: 0;
            overflow-wrap: break-word;
            white-space: normal;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .excel-cell:last-child {
            border-right: none;
        }
        .excel-cell select {
            width: 100%;
            padding: 2px 20px 2px 2px;
            border: 1px inset #c0c0c0;
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAAHCAYAAADEUlfTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAJUlEQVQImWP4//8/A7UBcQYSQPRgOhDh/////4P5wXwQvQwXAAChTxdO4tPGeAAAAAElFTkSuQmCC') no-repeat right 5px center;
            background-size: 7px 7px;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }
        .excel-cell input {
            width: 100%;
            padding: 2px;
            border: 1px inset #c0c0c0;
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }
        .excel-cell.button-cell {
            flex: 0 0 30px;
            min-width: 30px;
            text-align: center;
        }
        .excel-cell.logic-cell {
            flex: 0 0 60px;
            min-width: 60px;
        }
        .excel-cell button {
            width: 100%;
            padding: 2px;
            border: 1px outset #c0c0c0;
            background-color: #d4d0c8;
            cursor: pointer;
            font-family: Arial, sans-serif;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }
        .excel-cell button:active {
            border: 1px inset #c0c0c0;
            background-color: #c0c0c0;
            transition: none;
        }

        .footer-row {
            justify-content: center;
        }
        .footer-link {
            flex: 0 0 100px;
            text-align: center;
            margin: 0 10px;
        }
        .spacer-row {
            border-bottom: none;
            height: 10px;
            width: 100%;
            background-color: inherit;
        }
        @media (prefers-color-scheme: dark) {
            body { background-color: #1e1e1e; color: #e0e0e0; }
            .excel-container { background-color: #2d2d2d; border-color: #555555; }
            .excel-header { background-color: #005555; color: #e0e0e0; border-bottom-color: #666666; }
            .excel-row { border-top: 1px solid #666666; border-bottom: 1px solid #666666; }
            .excel-cell { background-color: #2d2d2d; border-right-color: #666666; color: #e0e0e0; }
            .excel-cell select, .excel-cell input { background-color: #3c3c3c; border-color: #666666; color: #e0e0e0; }
            .excel-cell select { background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAAHCAYAAADEUlfTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAJUlEQVQImWP4//8/A7UBcQYSQPRgOhDh/////4P5wXwQvQwXAAChTxdO4tPGeAAAAAElFTkSuQmCC'); }
            .excel-cell button { background-color: #4a4a4a; border-color: #666666; color: #e0e0e0; }
            .excel-cell button:active { background-color: #666666; border-color: #888888; }
            .search-boxes .excel-row:nth-child(even) .excel-cell { background-color: #363636; }
            #recipe_details .excel-row { background-color: #2d2d2d; }
            #recipe_details .excel-row:nth-child(even) .excel-cell { background-color: #363636; }
            #recipe_details a { color: #66b3ff; }
            .ingredient-number { border-right-color: #666666; }
            .term-select-cell { background-color: #ff0000; }
            #name-select-cell { background-color: #ff0000; }
        }
        @media (max-width: 768px) {
            .excel-row { flex-direction: column; }
            .excel-cell { border-right: none; border-bottom: 1px solid #c0c0c0; width: 100%; }
            .excel-cell:last-child { border-bottom: none; }
            .excel-cell.button-cell, .excel-cell.logic-cell { flex: 1; min-width: 0; }
            #recipe_details .excel-cell.label-cell { flex: 1; }
            #recipe_details .excel-cell.content-cell { flex: 1; }
            .ingredient-row .excel-cell:nth-child(1) { flex: 0 0 100%; text-align: right; border-bottom: none; }
            .ingredient-row .excel-cell:nth-child(2) { flex: 1; padding-left: 4px; }
            .ingredient-row .excel-cell:nth-child(3) { flex: 1; padding-left: 4px; text-align: left; border-right: none; }
            .ingredient-row .excel-cell:nth-child(4) { flex: 1; padding-left: 4px; text-align: left; }
            .count-cell { flex: 1; font-size: 0.5375rem; }
            .footer-row { justify-content: space-around; }
            .footer-link { flex: 1; margin: 5px 0; }
            .excel-header { flex-direction: column; align-items: flex-start; }
            #reset-button, #copy-permalink, #lucky-button { margin-top: 5px; margin-left: 0; }
            .spacer-row { width: 100%; }
        }

        #ad-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 90px;
            background-color: #cccccc;
            color: #333333;
            text-align: center;
            line-height: 90px;
            font-size: 0.9375rem;
            z-index: 1000;
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
                background-color: #006666;   /* darker teal */
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

    </style>
</head>
<body>
    <div class="excel-container">

<div class="excel-header">
    <!-- Centered small logo -->
    <div class="header-logo">
        <a href="https://nononsensecocktails.com">
            <img src="images/Coldberry_01_TM.jpg" alt="Logo">
        </a>
    </div>
   
    <div class="header-title">
        No-Nonsense Cocktails - Family Details
    </div>
</div>

        <div class="excel-row spacer-row"></div>
        <div class="ingredient-table">
            <!-- Header Row -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell"><strong>Family</strong></div>
                <div class="excel-cell"><strong>Characteristics</strong></div>
                <div class="excel-cell"><strong>Source</strong></div>
                <div class="excel-cell"><strong>Page</strong></div>
                <div class="excel-cell"><strong>Examples</strong></div>
            </div>
            <!-- Old Fashioned -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">Old Fashioned</div>
                <div class="excel-cell">An Old-Fashioned is spirit driven,<br>An Old-Fashioned is balanced by a small amount of sweetness.<br>An Old-Fashioned is seasoned with bitters and a garnish</div>
                <div class="excel-cell">Cocktail Codex</div>
                <div class="excel-cell">6</div>
                <div class="excel-cell"></div>
            </div>
            <!-- Martini -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">Martini</div>
                <div class="excel-cell">A Martini is composed of alcohol and aromatized wine, typically gin or vodka and dry vermouth.<br>A Martini is flexible in regard to the proportions of those ingredients, and its balance is dependent on the preference of the drinker.<br>A Martini's garnish has a big impact on the overall flavor and experience of the drink.</div>
                <div class="excel-cell">Cocktail Codex</div>
                <div class="excel-cell">64</div>
                <div class="excel-cell">Manhattan, Negroni</div>
            </div>
            <!-- Daiquiri -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">Daiquiri</div>
                <div class="excel-cell">A Daiquiri is composed of a spirit, citrus, and a sweetener, typically rum, lime juice, and simple syrup.<br>A Daiquiri is flexible in regard to the proportions of citrus to sweetener, which depend on the preference of the drinker and the acidity and sweetness of the citrus juice.<br>A Daiquiri requires a level of improvisation due to the inconsistency of citrus juices.</div>
                <div class="excel-cell">Cocktail Codex</div>
                <div class="excel-cell">105</div>
                <div class="excel-cell">Whiskey Sour</div>
            </div>
            <!-- Sidecar -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">Sidecar</div>
                <div class="excel-cell">A Sidecar's core flavor is composed of a spirit and a substantial amount of flavorful liqueur.<br>A Sidecar is both balanced and seasoned by liqueur, which also provides sweetness, sometimes in combination with another sweetener.<br>A Sidecar is also balanced by highly acidic citrus juice, typically lemon or lime.</div>
                <div class="excel-cell">Cocktail Codex</div>
                <div class="excel-cell">154</div>
                <div class="excel-cell"></div>
            </div>
            <!-- Highball -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">Highball</div>
                <div class="excel-cell">A Highball is composed of a core spirit that also provides seasoning, and is balanced by a nonalcoholic mixer.<br>A Highball's core can be split between any number of spirits, wines, or fortified wines.<br>A Highball can be effervescent or still.</div>
                <div class="excel-cell">Cocktail Codex</div>
                <div class="excel-cell">201</div>
                <div class="excel-cell"></div>
            </div>
            <!-- Flip -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">Flip</div>
                <div class="excel-cell">A Flip's characteristic flavor arises from the combination of a core spirit or fortified wine and a rich ingredient.<br>A Flip is balanced by its rich ingredients, such as eggs, dairy, coconut milk, or dense liqueurs and syrups.<br>A Flip is seasoned with aromatic spices on top ot the finished cocktail, a role that can also be played by a highly flavorful liqueur, such as amaro.</div>
                <div class="excel-cell">Cocktail Codex</div>
                <div class="excel-cell">243</div>
                <div class="excel-cell"></div>
            </div>
            <!-- Punch -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">Punch</div>
                <div class="excel-cell">Large volumn/format</div>
                <div class="excel-cell"></div>
                <div class="excel-cell"></div>
                <div class="excel-cell"></div>
            </div>
            <!-- Shot -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">Shot</div>
                <div class="excel-cell">Its a shot of alcohol</div>
                <div class="excel-cell"></div>
                <div class="excel-cell"></div>
                <div class="excel-cell"></div>
            </div>
            <!-- Tiki -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">Tiki</div>
                <div class="excel-cell">Really just a Daiquiri but with flare</div>
                <div class="excel-cell"></div>
                <div class="excel-cell"></div>
                <div class="excel-cell"></div>
            </div>
        </div>
        <div class="excel-row spacer-row"></div>
        <div class="excel-row footer-row">
            <div class="excel-cell footer-link"><a href="about.php">About</a></div>
            <div class="excel-cell footer-link"><a href="index.php">Home</a></div>
            <div class="excel-cell footer-link"><a href="mixers.php">Mixers</a></div>
        </div>
        <div class="excel-row">
            <div class="excel-cell" style="text-align: center; font-size: 0.7rem;"><a href="mailto:no.nonsense.cocktails.filter@gmail.com">Email Me</a></div>
        </div>
        <div class="excel-row">
            <div class="excel-cell" style="text-align: center;">Drink Responsibly</div>
        </div>
        <div class="excel-row">
            <div class="excel-cell" style="text-align: center;">Copyright © No-Nonsense Cocktails <?php echo date('Y'); ?>. All rights reserved.</div>
        </div>
    </div>
    <div id="ad-banner">Space for Ads</div>
</body>
</html>
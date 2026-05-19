<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <!-- ===== Google Adsense ===== -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3865173708892194"
         crossorigin="anonymous"></script>

    <title>No-Nonsense Cocktails - About</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
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
    padding: 8px 10px 5px; /* slightly more top padding for logo */
    font-weight: bold;
    border-bottom: 1px solid #c0c0c0;
    display: flex;
    flex-direction: column; /* stack logo on top */
    align-items: center; /* centers the logo */
    transition: background-color 0.3s, color 0.3s;
}
.header-logo {
    margin-bottom: 4px;
}
.header-logo img {
    display: block;
    max-width: 50px;
    height: auto;
    margin: 0 auto; /* centers the image */
}
.header-title {
    width: 100%;
    text-align: left;
    padding-left: 10px; /* matches original left padding */
    font-size: inherit; /* keeps original font size */
    margin: 0;
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
            padding: 2px 4px;
            border-right: 1px solid #c0c0c0;
            background-color: #ffffff;
            min-width: 0;
            overflow-wrap: break-word;
            white-space: normal;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .excel-cell p {
            margin: 4px 0;
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
        .search-boxes .excel-row:nth-child(even) .excel-cell {
            background-color: #f5f5f5;
            transition: background-color 0.3s;
        }
        #recipe_details .excel-row {
            background-color: #ffffff;
            transition: background-color 0.3s;
        }
        #recipe_details .excel-row:nth-child(even) .excel-cell {
            background-color: #f5f5f5;
            transition: background-color 0.3s;
        }
        #recipe_details .excel-cell {
            padding: 4px;
        }
        #recipe_details .excel-cell.label-cell {
            flex: 0 0 150px;
            white-space: nowrap;
            font-weight: bold;
        }
        #recipe_details .excel-cell.content-cell {
            flex: 2;
        }
        #recipe_details a {
            color: #0000ff;
            text-decoration: underline;
            cursor: pointer;
            transition: color 0.3s;
        }
        .ingredient-row {
            display: flex;
        }
        .ingredient-row .excel-cell:nth-child(1) {
            flex: 0 0 150px;
            text-align: right;
            padding-right: 4px;
            border-right: 1px solid #c0c0c0;
        }
        .ingredient-row .excel-cell:nth-child(2) {
            flex: 0 0 300px;
            padding-left: 4px;
            border-right: 1px solid #c0c0c0;
        }
        .ingredient-row .excel-cell:nth-child(3) {
            flex: 0 0 100px;
            padding-left: 4px;
            border-right: 1px solid #c0c0c0;
        }
        .ingredient-row .excel-cell:nth-child(4) {
            flex: 0 0 80px;
            padding-left: 4px;
            text-align: right;
        }
        .ingredient-row .excel-cell:nth-child(5) {
            flex: 1;
        }
        // Comment out or remove these if not needed for alternating rows on this page
        .ingredient-table .excel-row:nth-child(even) {
            background-color: #f5f5f5;
        }
        .ingredient-table .excel-row:nth-child(odd) {
            background-color: #ffffff;
        }
        .ingredient-table .excel-cell {
            background-color: transparent;
        }
        
        .ingredient-number {
            flex: 0 0 20px;
            text-align: right;
            padding-right: 4px;
            border-left: none;
            border-right: 1px solid #c0c0c0;
            transition: border-color 0.3s;
        }
        .count-cell {
            flex: 0 0 120px;
            text-align: center;
            font-size: 0.6375rem;
        }
        .footer-row {
            justify-content: center;
        }
        .footer-link {
            flex: 0 0 100px;
            text-align: center;
            margin: 0 10px;
        }
        #reset-button, #copy-permalink, #lucky-button {
            padding: 2px 8px;
            font-size: 0.7375rem;
            margin-left: 5px;
        }
        .spacer-row {
            border-bottom: none;
            height: 10px;
            width: 100%;
            background-color: inherit;
        }
        .term-select-cell {
            background-color: #ff0000;
        }
        #name-select-cell {
            background-color: #ff0000;
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
        #controls {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #ffffff;
        }
        .term-select, .operator-select, .value-input, #name-select, #source-select {
            font-size: 0.9375rem;
        }
        #rate-drink-row select:disabled,
        #rate-drink-row input:disabled,
        #rate-drink-row button:disabled {
            background-color: #e0e0e0;
            color: #a0a0a0;
        }
        #rate-drink-row button:disabled {
            cursor: not-allowed;
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
            /* Add these overrides if keeping the alternating row styles */
            .ingredient-table .excel-row:nth-child(even) {
                background-color: #363636;
            }
            .ingredient-table .excel-row:nth-child(odd) {
                background-color: #2d2d2d;
            }
            .ingredient-table .excel-cell {
                background-color: transparent;
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
  
    <!-- Original title, left-justified, unchanged size & position -->
    <div class="header-title">
        No-Nonsense Cocktails - About
    </div>
</div>
        <div class="excel-row spacer-row"></div>
        <div class="ingredient-table">
            <div class="excel-row">
                <div class="excel-cell">
                    <p>Hi I'm Jason. I like cocktails, but I love spreadsheets... and I have a terrible memory.</p>
                </div>
            </div>
            <div class="excel-row">
                <div class="excel-cell">
                    <p>That's how I thought this page would start, and I guess it did... but that's not how it ends.</p>
                </div>
            </div>
            <div class="excel-row">
                <div class="excel-cell">
                    <p>Way back in 2020 during the global COVID pandemic I, like a lot of other people, had a bunch of free time on my hands. On April 12th I purchased a booked titled <a href="https://www.amazon.com/Drinking-French-Cocktails-Ap%C3%A9ritifs-Traditions/dp/1607749297">Drinking French by David Liebovitz</a> (I already owned all of David's other food cookbooks which are also great) and started making cocktails at home. At the time it was just something to pass the time at home. After that came <a href="https://www.amazon.com/Amaro-Spirited-Bittersweet-Liqueurs-Cocktails/dp/1607747480/">Amaro by Brad Parsons</a>, then <a href="https://www.amazon.com/Death-Co-Welcome-Home-Cocktail/dp/1984858416/">Death & Co by David Kaplan, Nick Fauchald, and Alex Day</a>... you get the idea. This was great for a while as I had plenty of time and a stock of post-it notes and page tabs. But as my liqueur closet began to grow, it was easier to make more and more cocktails, but a lot harder to find a recipe or figure out what I could make becuase I was missing a quarter once of that one niche bottle I hadn't yet picked up at Binny's. Around this time me and my "cocktail boyfriends" (as my wife likes to call them) who were also in the same boat had a lively text group where we traded recipes we found and it was clear we needed a way to organize all of this.</p>
                </div>
            </div>
            <div class="excel-row">
                <div class="excel-cell">
                    <p>Most people hate spreadsheets, and that's fine, but I've been working in Excel my entire life and can write some formulas and VBA code that will make your head spin. I'm not good at much in this life, but I know I'm pretty good at organizing data and making it usable. It turns out cocktail recipes lend themselves really well to my skillset. Unlike food recipes, cocktails contain a limited number of standardized ingredients and preparation methods. Every avocado is slightly different, home ovens can vary by more than a handful of degrees even if they say they're at 425F, and some food recipes have dozens of ingredients and different preparation methods. But you know every bottle of even Green Chartreuse is within a hair's breath of being exactly like the last one, ice always turns to water at 32F (yes I know that's not technically correct), and even a novice can stir several different liquors in a mixing glass for 20 seconds. This is in no way downplaying the talent of professional bartenders but hopefully you see my point, concerning ingredients.</p>
                </div>
            </div>
            <div class="excel-row">
                <div class="excel-cell">
                    <p>The one really fun thing about cocktails is that even though the ingredients are standardized, the naming conventions are definitely not. Besides possibly naming Manhattan riffs after NYC neighborhoods, really the sky is the limit and can be quite fun, comical, and interesting. In this way any old list of ingredients can quickly be turned into an inside joke, cultural touchstone, or random observation. Besides how a drink tastes I believe the name is really it's most defining characteristic.</p>
                </div>
            </div>
            <div class="excel-row">
                <div class="excel-cell">
                    <p>In October 2021 I created the original version of this project in GoogleSheets for me, my friends, and anyone we knew who was looking for cocktail recipes, or just couldn't remember what went into a Paloma. I added a bunch of recipes and added as many features as I could, including my fan favorite and perhaps slightly confusing 3 Star rating system. I continued to add recipes as fast as I could with the goal of having as many as I could to satisfy even the nerdiest cocktail nerds. I fully realize there are way too many possible combinations and names to save every cocktail recipe in this database, but I'll do my best to capture as many as possible.</p>
                </div>
            </div>
            <div class="excel-row">
                <div class="excel-cell">
                    <p>I'm not the first person to say it, but every cocktail recipe is really a riff on 5 or maybe 6 core recipes. Call these mother or master recipes or whatever you like (I follow the Death & Co. cocktail recipe family convention) but a martini is a manhattan is a negroni. Nearly all the recipes on this site are not my own, and I don't claim them to be. As every bartender stands on shoulders of the giants that came before them, and every tiki drink owes it's life to Don the Beachcomber... this website has benefitted from and pays homage to all the great cocktail recipe book authors, other websites, blogs, YouTube channels, and other sources of recipes you can search for her. While you may just be here because you looking for something new to try, or still can't remember what goes into a Paloma... I thoroughly encourange everyone to purchase the books and read them cover to cover as I have, watch the YouTube videos, read the websites, and if they are still with us go sit at the bars where the bartenders who created these cocktails are still practicing their craft for our enjoyment. </p>
                </div>
            </div>
            <div class="excel-row">
                <div class="excel-cell">
                    <p>So please, explore this website and its many filters and options. Let me know what you think, <a href="mailto:no.nonsense.cocktails.filter@gmail.com">send me recipes</a> if you'd like them to be included, and share a link or QR code with your cocktail nerd friends. I'll keep adding recipes until I can't find any more.</p>
                </div>
            </div>
            <div class="excel-row">
                <div class="excel-cell">
                    <p><b><u>A quick note on how to use this website</u></b>. This is not a tutorial or history lesson. There are tons and tons of great books, websites, and videos out there showing you how to mix drinks and the histories behind them. This site is just <b>No-Nonsense</b> recipes. No fluff, no long drawn out narratives. Just an easy to use yet extremely powerful search and filtering system to quickly find the specs for as many cocktail builds as possible. That being said, if you have questions or need suggestions, <a href="mailto:no.nonsense.cocktails.filter@gmail.com">Email Me</a> and I'd love to help. Happy Imbibing!!!</p>
                </div>
            </div>
        </div>
        <div class="excel-row spacer-row"></div>
        <div class="excel-row footer-row">
            <div class="excel-cell footer-link"><a href="index.php">Home</a></div>
            <div class="excel-cell footer-link"><a href="family.php">Family</a></div>
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
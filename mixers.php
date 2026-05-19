<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <!-- ===== Google Adsense ===== -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3865173708892194"
         crossorigin="anonymous"></script>

    <title>No-Nonsense Cocktails - Mixers</title>
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
    padding: 8px 10px 5px;   /* slightly more top padding for logo */
    font-weight: bold;
    border-bottom: 1px solid #c0c0c0;
    display: flex;
    flex-direction: column;  /* stack logo on top */
    align-items: center;     /* centers the logo */
    transition: background-color 0.3s, color 0.3s;
}
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
    width: 100%;
    text-align: left;
    padding-left: 10px;   /* matches original left padding */
    font-size: inherit;   /* keeps original font size */
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
            flex: 0 0 50px;
            text-align: right;
            padding-right: 4px;
            border-right: 1px solid #c0c0c0;
        }
        .ingredient-row .excel-cell:nth-child(2) {
            flex: 0 0 150px;
            padding-left: 4px;
            border-right: 1px solid #c0c0c0;
        }
        .ingredient-row .excel-cell:nth-child(3) {
            flex: 2;
            padding-left: 4px;
            border-right: 1px solid #c0c0c0;
        }
        .ingredient-row .excel-cell:nth-child(4) {
            flex: 1;
            padding-left: 4px;
        }
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
        No-Nonsense Cocktails - Mixers
    </div>
</div>


        <div class="excel-row spacer-row"></div>
        <div class="ingredient-table">
            <!-- Header Row -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell"><strong>Number</strong></div>
                <div class="excel-cell"><strong>Mixer</strong></div>
                <div class="excel-cell"><strong>Recipe</strong></div>
                <div class="excel-cell"><strong>Comments</strong></div>
            </div>
            <!-- 1 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">1</div>
                <div class="excel-cell">Agave Syrup</div>
                <div class="excel-cell">By weight: 650 light blue agave nectar, 2 hot water, lasts 1 month</div>
                <div class="excel-cell">NoMad p216</div>
            </div>
            <!-- 2 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">2</div>
                <div class="excel-cell">Aperol Sugar</div>
                <div class="excel-cell">2 (500mL) Cups white sugar, 5oz (150mL) Aperol, blend in food processor, spread on baking sheet, dehydrate until crystalized and dry, blitz in food processor, push through sieve to remove large particles</div>
                <div class="excel-cell"></div>
            </div>
            <!-- 3 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">3</div>
                <div class="excel-cell">Basil-Fennel Syrup</div>
                <div class="excel-cell">By weight; 1 basil leaves, 1 fennel roughly chopped, 8 simple syrup, Charge twice with iSi canister, lasts 1 month</div>
                <div class="excel-cell">NoMad p217</div>
            </div>
            <!-- 4 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">4</div>
                <div class="excel-cell">Cane Sugar Syrup</div>
                <div class="excel-cell">2 Cane sugar, 1 water</div>
                <div class="excel-cell">Death & Co</div>
            </div>
            <!-- 5 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">5</div>
                <div class="excel-cell">Chamomile Honey Syrup</div>
                <div class="excel-cell">By weight: 25 chamomile tea, 225 filtered water, 350 clover honey, steep tea for 3', strain, add honey, lasts 1 month</div>
                <div class="excel-cell">NoMad p221</div>
            </div>
            <!-- 6 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">6</div>
                <div class="excel-cell">Chamomile-Infused Old Overholt Rye</div>
                <div class="excel-cell">1 Liter to 0.25 Cup loose Chamomile tea, 1.75 hours. 1 L = 33.814 Oz, 4 drinks; 8oz OO + ~1/16 Cup (0.059) = 1 Tbls</div>
                <div class="excel-cell">Death & Co p281</div>
            </div>
            <!-- 7 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">7</div>
                <div class="excel-cell">Chinese Five Spice Syrup</div>
                <div class="excel-cell">1 tsp Chinese five spice powder, 1/3 cup sugar, 1/3 cup water. Heat the spices in a pan until they become fragrant. Add sugar and water, stir, and bring to a boil. Turn off the heat, let sit covered for 15 minutes, strain through a coffee filter, bottle, and refrigerate.</div>
                <div class="excel-cell"><a href="https://cocktailvirgin.blogspot.com/2023/12/the-five-elements.html">https://cocktailvirgin.blogspot.com/2023/12/the-five-elements.html</a></div>
            </div>
            <!-- 8 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">8</div>
                <div class="excel-cell">Cinnamon Bark Syrup</div>
                <div class="excel-cell">1 oz cinnamon sticks, 2 C water, 2 C superfine sugar, heat, steep overnight</div>
                <div class="excel-cell">Death & Co, p276</div>
            </div>
            <!-- 9 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">9</div>
                <div class="excel-cell">Cinnamon Syrup</div>
                <div class="excel-cell">1 C Sugar, 1 C Water, 40g cinnamon sticks, heat, cool, store up to 1 month</div>
                <div class="excel-cell">Bitters, 192</div>
            </div>
            <!-- 10 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">10</div>
                <div class="excel-cell">Coca-Cola Reduction</div>
                <div class="excel-cell">One 12oz can of Coca-Cola, 1 Tbls Sugar, 0.5 tsp lemon juice. Over high heat reduce soda to .75 Cup (5-7 minutes), add sugar and juice, store up to 2 weeks</div>
                <div class="excel-cell"></div>
            </div>
            <!-- 11 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">11</div>
                <div class="excel-cell">Cold Brew Coffee Concentrate</div>
                <div class="excel-cell">By weight: 455 coffee medium grind, 2000 water, cold brew for 18 hours in refrigerator, strain, lasts 1 week</div>
                <div class="excel-cell">NoMad p226</div>
            </div>
            <!-- 12 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">12</div>
                <div class="excel-cell">Creme de Cacao Infused Whipped Cream</div>
                <div class="excel-cell">2 cups (480 ml) heavy cream OR full-fat coconut milk, 2 oz. (60 ml) crème de cacao, 1 tbs (15 ml) powdered sugar</div>
                <div class="excel-cell">Anders Erickson</div>
            </div>
            <!-- 13 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">13</div>
                <div class="excel-cell">Demerara Syrup - Amaro</div>
                <div class="excel-cell">1 demerara, 1 water</div>
                <div class="excel-cell">Amaro</div>
            </div>
            <!-- 14 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">14</div>
                <div class="excel-cell">Demerara Syrup - Death & Co</div>
                <div class="excel-cell">2 demerara, 1 water</div>
                <div class="excel-cell">Death & Co</div>
            </div>
            <!-- 15 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">15</div>
                <div class="excel-cell">Ginger Beer</div>
                <div class="excel-cell">80 fl oz water, 1 C minced ginger, 2 oz light brown sugar, 1 oz lime juice. Boil water, then turn off heat. Add minced ginger and cover. Allow to infuse for 1 hour. Strain mixture though a chinois. Press down on the ginger with the back of a spoon to force as much liquid through the chinois as possible. Once the ginger beer has been strained, add citrus and sugar, then stir, bottle, and store in the refrigerator.</div>
                <div class="excel-cell">PDT p29</div>
            </div>
            <!-- 16 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">16</div>
                <div class="excel-cell">Ginger Lime Syrup</div>
                <div class="excel-cell">By weight; 600 water, 800 light brown sugar, 300 ginger chopped, simmer 45', remove from heat add 20 lime zest, steep 30', strain out ginger & lime zest, add 95 lime juice, refrigerate 1 month</div>
                <div class="excel-cell">NoMad p231</div>
            </div>
            <!-- 17 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">17</div>
                <div class="excel-cell">Honey Syrup</div>
                <div class="excel-cell">2 Honey, 1 water</div>
                <div class="excel-cell">Death & Co</div>
            </div>
            <!-- 18 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">18</div>
                <div class="excel-cell">Lime Syrup</div>
                <div class="excel-cell">1 C sugar, 1 C water, zest of 6 limes</div>
                <div class="excel-cell">Bitters</div>
            </div>
            <!-- 19 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">19</div>
                <div class="excel-cell">Orgeat</div>
                <div class="excel-cell">3 cups white sugar, 2 cups unsweetened almond milk, 1 tsp orange blossom water, 1/2 tsp rose water, 1/2 tsp almond extract. In a medium sauce pan, combine sugar and almond milk. Stir over heat until sugar is completely dissolved. Remove from heat and add orange blossom water, rose water, and almond extract. Stir to combine.</div>
                <div class="excel-cell"><a href="https://www.youtube.com/watch?v=CvEDhkR6K14">https://www.youtube.com/watch?v=CvEDhkR6K14</a></div>
            </div>
            <!-- 20 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">20</div>
                <div class="excel-cell">Orgeat</div>
                <div class="excel-cell"></div>
                <div class="excel-cell"><a href="https://www.seriouseats.com/how-to-make-orgeat-recipe-almond-syrup-for-cocktails">https://www.seriouseats.com/how-to-make-orgeat-recipe-almond-syrup-for-cocktails</a></div>
            </div>
            <!-- 21 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">21</div>
                <div class="excel-cell">Passionfruit Syrup</div>
                <div class="excel-cell">By weight; 1000 passionfruit puree, 200 sugar, combine and stir until incorporated, lasts 1 month</div>
                <div class="excel-cell">NoMad p240</div>
            </div>
            <!-- 22 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">22</div>
                <div class="excel-cell">Pineapple Gomme</div>
                <div class="excel-cell">By weight: 800 simple syrup, 200 diced pineapple, charge twice with iSi canister, lasts 1 month</div>
                <div class="excel-cell">NoMad p240</div>
            </div>
            <!-- 23 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">23</div>
                <div class="excel-cell">Rich Simple Syrup</div>
                <div class="excel-cell">2 Sugar, 1 water</div>
                <div class="excel-cell">Death & Co</div>
            </div>
            <!-- 24 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">24</div>
                <div class="excel-cell">Rosemary Simple Syrup</div>
                <div class="excel-cell">.5 C Sugar, .5 C Water, 2 Tbls coarsely chopped fresh Rosemary</div>
                <div class="excel-cell">Drinking French</div>
            </div>
            <!-- 25 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">25</div>
                <div class="excel-cell">Saline Solution</div>
                <div class="excel-cell">50g kosher salt, 500mL water, stir to combine, refrigerate, store indefinitely</div>
                <div class="excel-cell">NoMad p245</div>
            </div>
            <!-- 26 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">26</div>
                <div class="excel-cell">Sarsaparilla Tincture</div>
                <div class="excel-cell">By weight; 500 Everclear, 250 dried sarsaparilla root, steep at room temp for 10 days, strain, store at room temp indefinitely</div>
                <div class="excel-cell">NoMad p245</div>
            </div>
            <!-- 27 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">27</div>
                <div class="excel-cell">Simple Syrup</div>
                <div class="excel-cell">1 Sugar, 1 water</div>
                <div class="excel-cell">Classic</div>
            </div>
            <!-- 28 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">28</div>
                <div class="excel-cell">Spiced Honey Syrup</div>
                <div class="excel-cell">.5 C Wildflower honey, .25 C Water, 2 x 3" cinnamon sticks broken, 1 tsp whole allspice berries, 1 tsp whole cloves. Makes .75 C, warm, steep 2 hours, keeps 1 Month</div>
                <div class="excel-cell"></div>
            </div>
            <!-- 29 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">29</div>
                <div class="excel-cell">Spicy Ginger Syrup</div>
                <div class="excel-cell">By weight; 250 ginger juice, 250 turbinado sugar. Juice ginger, warm juice and dissolve sugar, Lasts 2 weeks</div>
                <div class="excel-cell">NoMad p247</div>
            </div>
            <!-- 30 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">30</div>
                <div class="excel-cell">Tellicherry Black Pepper Syrup</div>
                <div class="excel-cell">By weight: 150 Tellicherry black pepper coarsely ground, 800 Demerara Simple Syrup, charge twice with iSi canister, strain, Lasts 1 month</div>
                <div class="excel-cell">NoMad p248</div>
            </div>
            <!-- 31 -->
            <div class="excel-row ingredient-row">
                <div class="excel-cell">31</div>
                <div class="excel-cell">Vanilla Syrup</div>
                <div class="excel-cell">1/4 Cup Water, 1/4 Cup Sugar, 1/8 tsp Vanilla Paste = 3oz Simple Syrup + 0.5tsp vanilla paste</div>
                <div class="excel-cell">Death & Co, p277</div>
            </div>
        </div>
        <div class="excel-row spacer-row"></div>
        <div class="excel-row footer-row">
            <div class="excel-cell footer-link"><a href="about.php">About</a></div>
            <div class="excel-cell footer-link"><a href="family.php">Family</a></div>
            <div class="excel-cell footer-link"><a href="Home.php">Index</a></div>
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
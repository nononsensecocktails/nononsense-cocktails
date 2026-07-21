$(document).ready(function() {
    console.log('jQuery loaded and document ready');
    const dropdownFields = [
        'adaption_of', 'base', 'characteristics', 'color', 'family',
        'glass', 'ice', 'ingredients', 'num_ingredients', 'last_date', 'mixer', 'servings',
        'shaken_stirred', 'source', 'stars_out_of_3', 'variations'
    ];
    const numericOrDateFields = ['last_date', 'num_ingredients', 'servings'];
    // NEW: Fields that can use AND logic
    const allowAndFields = ['ingredients', 'characteristics', 'garnish', 'instructions'];
    let unitConversions = {};
    let pendingFilterChange = false;

// NEW: State for the Ingredients Order dropdown
    let currentRecipeData = null;
    let ingredientsOrder = 'Recipe';

    function loadUnitConversions(callback) {
        $.ajax({
            url: 'filter.php',
            method: 'GET',
            data: { action: 'getUnitConversions' },
            dataType: 'json',
            success: function(data) {
                console.log('Unit conversions loaded:', data);
                unitConversions = data;
                callback();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Unit conversions AJAX failed:', textStatus, errorThrown);
                unitConversions = { 'oz': 1.0 };
                callback();
            }
        });
    }
    function loadTotalCocktails() {
        var user = $('#user-select').val();
        var filters = getFilters();
        $.ajax({
            url: 'filter.php',
            method: 'GET',
            data: {
                action: 'getTotalCocktails',
                user: user,
                filters: JSON.stringify(filters)
            },
            dataType: 'json',
            success: function(data) {
                console.log('Total cocktails:', data);
                if (data && data.total !== undefined) {
                    $('#name-count').text(data.total);
                } else {
                    $('#name-count').text('N/A');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Total cocktails AJAX failed:', textStatus, errorThrown);
                $('#name-count').text('N/A');
            }
        });
    }
function loadDistinctValues(term, filtersBefore) {
    return new Promise(function(resolve, reject) {
        var user = $('#user-select').val();
        $.ajax({
            url: 'filter.php',
            method: 'GET',
            data: {
                action: 'getDistinctValues',
                term: term,
                user: user,
                filters: JSON.stringify(filtersBefore)
            },
            dataType: 'json',
            success: function(data) {
                console.log('Distinct values for ' + term + ':', data);
                resolve(data || []);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Distinct values AJAX failed for ' + term + ':', textStatus, errorThrown);
                alert('Failed to load options for ' + term + '. Check console for details.');
                resolve([]);
            }
        });
    });
}

function updateOperatorSelect($row, term) {
    var $operatorCell = $row.find('.excel-cell:nth-child(2)');
    var currentOperator = $operatorCell.find('.operator-select').val() || '=';
    var $select = $('<select class="operator-select form-select" name="operator[]"></select>');

    if (numericOrDateFields.includes(term) || term === 'stars_out_of_3') {
        $select.append('<option value="=">=</option>');
        $select.append('<option value="<>">≠</option>');
        $select.append('<option value=">=">>=</option>');
        $select.append('<option value="<"><</option>');
    } else {
        $select.append('<option value="=">=</option>');
        $select.append('<option value="<>">≠</option>');
    }

    $operatorCell.empty().append($select);
    $select.val(currentOperator in $select.find('option').map(function() { return this.value; }).get() ? currentOperator : '=');
}

    // NEW: Smart filter collection — skips same-term filters for parallel fields
    function getFiltersBeforeForDropdown($currentRow, currentTerm) {
        var filters = [];
        var currentLogic = $currentRow.find('.logic-select').val() || 'AND'; // OR or AND for current row
        $('.search-boxes .excel-row').each(function() {
            if ($(this).is($currentRow)) return false;
            var term = $(this).find('.term-select').val();
            var operator = $(this).find('.operator-select').val();
            var value = $(this).find('.value-input').val();
            var logic = $(this).find('.logic-select').val() || 'AND';
            if (!term || !value) return true;
            // Critical rule:
            // If current row uses OR → skip ALL previous rows with same term
            // If current row uses AND → include previous same-term rows (for narrowing)
            if (term === currentTerm && currentLogic === 'OR') {
                return true; // skip this previous filter entirely
            }
            var f = { term: term, operator: operator, value: value };
            if (filters.length > 0) f.logic = logic;
            filters.push(f);
        });
        return filters;
    }

function updateValueInput($row, term, initialValue = '') {
    var $valueCell = $row.find('.excel-cell:nth-child(3)');
    var currentValue = $row.find('.value-input').val() || initialValue.trim();
    $valueCell.empty();

    var textInputOnlyFields = ['name', 'garnish', 'instructions', 'All'];

    // === Plain text input with Clear X (Name, Garnish, Instructions, All) ===
    if (textInputOnlyFields.includes(term)) {
        var placeholder = 'Type to search...';
        if (term === 'name') {
            placeholder = 'Type name or partial name';
        } else if (term === 'All') {
            placeholder = 'Search all fields...';
        } else {
            placeholder = 'Type ' + term.replace(/_/g, ' ') ;
        }

        var $wrapper = $('<div class="input-with-clear"></div>');
        var $input = $('<input type="text" class="value-input form-control" name="value[]">');
        $input.attr('placeholder', placeholder);

        if (currentValue) {
            $input.val(currentValue);
        }

        var $clearBtn = $('<span class="clear-btn" aria-label="Clear">×</span>');

        $wrapper.append($input).append($clearBtn);
        $valueCell.append($wrapper);

        $clearBtn.on('click', function() {
            $input.val('').trigger('change');
            $(this).hide();
        });

        $input.on('input change', function() {
            if ($(this).val()) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }
        });

        if (currentValue) {
            $clearBtn.show();
        } else {
            $clearBtn.hide();
        }

        $input.on('change input', function () {
            if (typeof updateAllBelow === 'function') {
                updateAllBelow($row);
            }
            $(document).trigger('filtersChanged');
        });

        return;
    }

    if (dropdownFields.includes(term)) {

        // === Mobile: Custom searchable input + scrollable list (with clear X) ===
        if (/Mobi|Android/i.test(navigator.userAgent) || window.innerWidth < 768) {
            var $input = $('<input type="text" class="value-input form-control" name="value[]" placeholder="Type to search...">');
            if (currentValue) $input.val(currentValue);

            var $wrapper = $('<div class="input-with-clear"></div>');
            var $clearBtn = $('<span class="clear-btn" aria-label="Clear">×</span>');

            $wrapper.append($input).append($clearBtn);
            $valueCell.css('position', 'relative').append($wrapper);

            var $list = $('<div class="mobile-search-list"></div>');
            $valueCell.append($list);

            var allOptions = [];

            loadDistinctValues(term, getFiltersBeforeForDropdown($row, term)).then(function(values) {
                allOptions = values || [];

                function renderList(filtered) {
                    $list.empty();
                    filtered.forEach(function(val) {
                        var $item = $('<div class="mobile-search-item">' + val + '</div>');
                        $item.on('click', function() {
                            $input.val(val).trigger('change');
                            $list.hide();
                            $clearBtn.show();
                        });
                        $list.append($item);
                    });
                }

                renderList(allOptions);

                $input.on('input', function() {
                    var val = $(this).val().toLowerCase();
                    var filtered = allOptions.filter(function(opt) {
                        return opt.toLowerCase().includes(val);
                    });
                    renderList(filtered);
                    $list.show();
                });

                $input.on('focus', function() {
                    if (!$input.val()) {
                        renderList(allOptions);   // ← Reset to full list when empty
                    }
                    $list.show();
                });

                $(document).on('click', function(e) {
                    if (!$(e.target).closest($valueCell).length) {
                        $list.hide();
                    }
                });
            });

            $clearBtn.on('click', function() {
                $input.val('').trigger('change');
                renderList(allOptions);           // ← Restore full list after clearing
                $list.show();
                $clearBtn.hide();
            });

            $input.on('input change', function() {
                if ($(this).val()) {
                    $clearBtn.show();
                } else {
                    $clearBtn.hide();
                }
            });

            if (currentValue) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }

            $input.on('change input', function () {
                if (typeof updateAllBelow === 'function') {
                    updateAllBelow($row);
                }
                $(document).trigger('filtersChanged');
            });

            return;
        }

        // === Desktop: Choices.js (uses built-in remove button) ===
        var $select = $('<select class="value-input choices-filter" name="value[]"></select>');
        $select.append('<option value="">Any ' + term.replace(/_/g, ' ') + '</option>');

        $valueCell.append($select);

        var filtersBefore = getFiltersBeforeForDropdown($row, term);

        loadDistinctValues(term, filtersBefore).then(function(values) {
            $.each(values, function(index, value) {
                $select.append($('<option>', { value: value, text: value }));
            });

            if ($select.data('choices')) {
                $select.data('choices').destroy();
            }

            new Choices($select[0], {
                searchEnabled: true,
                searchPlaceholderValue: 'Type to search...',
                shouldSort: false,
                removeItemButton: true,
                itemSelectText: '',
                searchResultLimit: -1,
                searchChoices: true,
                searchFields: ['label'],
                duplicateItemsAllowed: false,
                position: 'auto'
            });
        });

        $select.on('change', function () {
            if (typeof updateAllBelow === 'function') {
                updateAllBelow($row);
            }
            $(document).trigger('filtersChanged');
        });

        return;
    }

    // === Fallback for non-dropdown fields ===
    var $fallbackInput = $('<input type="text" class="value-input form-control" name="value[]">');
    $fallbackInput.attr('placeholder', 'STEP 2: Select or Type a Value');

    if (currentValue) {
        $fallbackInput.val(currentValue);
    }

    $valueCell.append($fallbackInput);

    $fallbackInput.on('change input', function () {
        if (typeof updateAllBelow === 'function') {
            updateAllBelow($row);
        }
        $(document).trigger('filtersChanged');
    });
}
    function getFiltersBefore($row) {
        var filters = [];
        $('.search-boxes .excel-row').each(function() {
            if ($(this).is($row)) return false;
            var term = $(this).find('.term-select').val();
            var operator = $(this).find('.operator-select').val();
            var value = $(this).find('.value-input').val();
            var logic = $(this).find('.logic-select').val() || 'AND';
            if (value && term && term !== "") {
                var filter = {term: term, operator: operator, value: value};
                if (filters.length > 0) filter.logic = logic;
                filters.push(filter);
            }
        });
        return filters;
    }
    function getFilters() {
        var filters = [];
        $('.search-boxes .excel-row').each(function() {
            var term = $(this).find('.term-select').val();
            var operator = $(this).find('.operator-select').val();
            var value = $(this).find('.value-input').val();
            var logic = $(this).find('.logic-select').val() || 'AND';
            if (term && value) {
                var f = {
                    term: term,
                    operator: operator,
                    value: value
                };
                if (filters.length > 0) {
                    f.logic = logic;
                }
                filters.push(f);
            }
        });
        return filters;
    }
    // NEW: Smart AND/OR visibility
    function updateLogicVisibility() {
        $('.search-boxes .excel-row').each(function(index) {
            var $row = $(this);
            var $logic = $row.find('.logic-select');
            var currentLogic = $logic.val(); // Remember current selection
            if (index === 0) {
                $logic.hide();
                return;
            }
            // Always show AND/OR on rows 2+
            $logic.show();
            // Only rebuild if empty or missing options
            if ($logic.find('option').length === 0) {
                $logic.append('<option value="AND">AND</option>');
                $logic.append('<option value="OR">OR</option>');
            }
            // Restore previous selection (default to AND if none)
            if (currentLogic === 'OR' || currentLogic === 'AND') {
                $logic.val(currentLogic);
            } else {
                $logic.val('AND');
            }
        });
    }

function resetFilters() {
    $('.search-boxes').html(`
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

            <!-- Ingredients Order dropdown - keep on far right after reset -->
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
    `);

    // Re-attach the change handler for the Ingredients Order dropdown after reset
    // (this is needed because we rebuilt the HTML)
    $('#ingredients-order-select').on('change', function () {
        ingredientsOrder = $(this).val();
        if (currentRecipeData) {
            renderIngredientsTable(currentRecipeData);
        }
    });

    updateOperatorSelect($('.search-boxes .excel-row:first'), '');
    updateValueInput($('.search-boxes .excel-row:first'), '');
    updateLogicVisibility();
    updateNames();
}

    $(document).on('click', '.add-box', function() {
        var newBox = $('.search-boxes .excel-row:first').clone(true);
        newBox.find('.value-input').val('');
        newBox.find('.remove-box').show();
        newBox.find('.add-box').text('+');
        newBox.find('.term-select').val('');
        newBox.find('.excel-cell').last().remove();
        $(this).closest('.excel-row').after(newBox);
        var term = newBox.find('.term-select').val();
        updateOperatorSelect(newBox, term);
        updateValueInput(newBox, term);
        updateLogicVisibility();
        updateAllBelow(newBox);
    });
    $(document).on('click', '.remove-box', function() {
        if ($('.search-boxes .excel-row').length > 1) {
            pendingFilterChange = true; // Reset name/source because filters changed
            var $row = $(this).closest('.excel-row');
            $row.remove();
            updateLogicVisibility();
           
            // DO NOT rebuild all dropdowns — this was wiping values!
            // Just update the results
            updateNames();
        }
    });
    $(document).on('change', '.term-select', function() {
        var $row = $(this).closest('.excel-row');
        var term = $(this).val();
        updateOperatorSelect($row, term);
        updateValueInput($row, term);
        updateLogicVisibility();
        // DO NOT call updateAllBelow() here
        // Changing the term is NOT a filter change yet
        // Only selecting a VALUE should trigger name update
    });
    $(document).on('change', '.operator-select', function() {
        var $row = $(this).closest('.excel-row');
        updateAllBelow($row);
    });
    $(document).on('change', '.value-input', function() {
    pendingFilterChange = true; // <-- Mark that a real change happened
    var $row = $(this).closest('.excel-row');
    updateAllBelow($row);
    });
    $(document).on('change', '.logic-select', function() {
    pendingFilterChange = true; // <-- AND/OR is a real change
    var $row = $(this).closest('.excel-row');
    updateAllBelow($row);
    });
    // When user changes AND/OR dropdown, refresh the value dropdown immediately
    $(document).on('change', '.logic-select', function() {
        var $row = $(this).closest('.excel-row');
        var term = $row.find('.term-select').val();
        if (term) {
            updateValueInput($row, term);
        }
    });
    function updateAllBelow($startRow) {
        var foundStart = $startRow.length === 0;
        $('.search-boxes .excel-row').each(function() {
            if ($(this).is($startRow)) {
                foundStart = true;
                return true;
            }
            if (foundStart) {
                var term = $(this).find('.term-select').val();
                updateValueInput($(this), term);
            }
        });
        updateNames();
    }

function updateNames() {
    var user = $('#user-select').val();
    var filters = getFilters();
    // FIX: Skip reset if loading from URL params
    if (window.skipNameReset) {
        delete window.skipNameReset;
        // Still fetch/update counts/names, but don't clear dropdown
        console.log('Fetching names with user:', user, 'filters:', filters);
        $.ajax({
            url: 'filter.php',
            method: 'GET',
            data: {
                action: 'getNames',
                user: user,
                filters: JSON.stringify(filters)
            },
            dataType: 'json',
            success: function(data) {
                console.log('Names data:', data);
                if (Array.isArray(data)) {
                    var nameSelect = $('#name-select');
                    var currentName = nameSelect.val();
                    // Add any new names that aren't already in the list
                    data.forEach(function(name) {
                        if (name && !nameSelect.find('option[value="' + name + '"]').length) {
                            nameSelect.append($('<option>').val(name).text(name));
                        }
                    });
                    $('#name-count').text(data.length);
                    // Restore current selection if still valid
                    if (currentName && data.includes(currentName)) {
                        nameSelect.val(currentName);
                    }
                    // If only one result, auto-select it
                    else if (data.length === 1) {
                        nameSelect.val(data[0]).trigger('change');
                    }
                } else {
                    console.error('Expected array, got:', data);
                    $('#name-select').html('<option value="">Error loading names</option>');
                    $('#name-count').text('0');
                    $('#recipe_details').empty();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Names AJAX failed:', textStatus, errorThrown);
                $('#name-select').html('<option value="">Error loading names</option>');
                $('#name-count').text('0');
                $('#recipe_details').empty();
            }
        });
    } else {
        // Original logic with resets
        // Reset name/source if filters were actually changed (not just adding blank row)
        if (pendingFilterChange) {
            $('#name-select').val('').trigger('change');
            $('#source-select').html('<option value="">STEP 4: Select a Source</option>');
            $('#source-count').text('');
            $('#recipe_details').empty();
            pendingFilterChange = false;
        }
        if (filters.length === 0) {
            $('#name-select').html('<option value="">STEP 3: Select a Name</option>');
            $('#name-count').text('');
            return;
        }
        console.log('Fetching names with user:', user, 'filters:', filters);
        $.ajax({
            url: 'filter.php',
            method: 'GET',
            data: {
                action: 'getNames',
                user: user,
                filters: JSON.stringify(filters)
            },
            dataType: 'json',
            success: function(data) {
                console.log('Names data:', data);
                if (Array.isArray(data)) {
                    var nameSelect = $('#name-select');
                    var currentName = nameSelect.val();
                    // Only fully rebuild the name list if a real filter value changed
                    // (not just when user picks a term in a new row)
                    if (pendingFilterChange || !currentName) {
                        nameSelect.empty();
                        nameSelect.append('<option value="">STEP 3: Select a Name</option>');
                    }
                    // Add any new names that aren't already in the list
                    data.forEach(function(name) {
                        if (name && !nameSelect.find('option[value="' + name + '"]').length) {
                            nameSelect.append($('<option>').val(name).text(name));
                        }
                    });
                    // Remove names no longer valid (only if filter actually changed)
                    if (pendingFilterChange) {
                        nameSelect.find('option:not(:first)').each(function() {
                            var val = $(this).val();
                            if (val && !data.includes(val)) {
                                $(this).remove();
                            }
                        });
                    }
                    $('#name-count').text(data.length);
                    // Restore current selection if still valid
                    if (currentName && data.includes(currentName)) {
                        nameSelect.val(currentName);
                    }
                    // If only one result and we're not in reset mode, auto-select it
                    else if (data.length === 1 && !pendingFilterChange) {
                        nameSelect.val(data[0]).trigger('change');
                    }
                    // If current name is no longer valid due to real filter change
                    else if (pendingFilterChange && currentName && !data.includes(currentName)) {
                        nameSelect.val('').trigger('change');
                        $('#source-select').html('<option value="">STEP 4: Select a Source</option>');
                        $('#source-count').text('');
                        $('#recipe_details').empty();
                    }
                } else {
                    console.error('Expected array, got:', data);
                    $('#name-select').html('<option value="">Error loading names</option>');
                    $('#name-count').text('0');
                    $('#recipe_details').empty();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Names AJAX failed:', textStatus, errorThrown);
                $('#name-select').html('<option value="">Error loading names</option>');
                $('#name-count').text('0');
                $('#recipe_details').empty();
            }
        });
    }
}
    function updateSources() {
        var name = $('#name-select').val();
        var user = $('#user-select').val();
        var filters = getFilters();
        if (!name) {
            console.log('No name selected, skipping sources update');
            $('#source-select').html('<option value="">STEP 4: Select a Source</option>');
            $('#source-count').text('');
            $('#recipe_details').empty();
            return;
        }
        console.log('Fetching sources with name:', name, 'user:', user, 'filters:', filters);
        $.ajax({
            url: 'filter.php',
            method: 'GET',
            data: {
                action: 'getSources',
                name: name,
                user: user,
                filters: JSON.stringify(filters)
            },
            dataType: 'json',
            success: function(data) {
                console.log('Sources data:', data);
                if (Array.isArray(data)) {
                    var sourceSelect = $('#source-select');
                    sourceSelect.empty();
                    if (data.length > 1) {
                        sourceSelect.append('<option value="">STEP 4: Select a Source</option>');
                    }
                    data.forEach(function(source) {
                        sourceSelect.append($('<option>').val(source).text(source));
                    });
                    var count = data.length;
                    $('#source-count').text(count);
                    if (count === 1) {
                        sourceSelect.val(data[0]);
                        console.log('Single source found, calling updateRecipeDetails()');
                        updateRecipeDetails();
                    } else {
                        console.log('Multiple sources found, waiting for user selection');
                        $('#recipe_details').empty();
                    }
                } else {
                    console.error('Expected array, got:', data);
                    $('#source-select').html('<option value="">Error loading sources</option>');
                    $('#source-count').text('0');
                    $('#recipe_details').empty();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Sources AJAX failed:', textStatus, errorThrown);
                $('#source-select').html('<option value="">Error loading sources</option>');
                $('#source-count').text('0');
                $('#recipe_details').empty();
            }
        });
    }


function parseVolume(volumeStr) {
    if (!volumeStr) return { numeric: 0, unit: '', display: '' };

    // Handle bare special units like "Top", "Splash", "Rinse" that have no number
    const lowered = volumeStr.toLowerCase().trim();
    if (unitConversions.hasOwnProperty(lowered)) {
        return {
            numeric: unitConversions[lowered],
            unit: lowered,
            display: volumeStr
        };
    }

    const match = volumeStr.match(/^([\d.]+)\s*(\w+)?$/);
    if (!match) return { numeric: 0, unit: '', display: volumeStr };
    const numeric = parseFloat(match[1]) || 0;
    const originalUnit = match[2] || '';
    const unit = originalUnit.toLowerCase();
    let display = volumeStr;
    if (unit === 'oz') {
        display = numeric.toFixed(2);
    } else if (originalUnit) {
        display = match[1] + ' ' + originalUnit;
    } else {
        display = numeric.toFixed(2);
    }
    const conversionFactor = unitConversions[unit] ?? 1.0;
    return { numeric: numeric * conversionFactor, unit: unit, display: display };
}

function getColor(value, min, max) {
    if (min === max) return '';
    // Cap at the provided max so anything >= max gets the same full color
    var capped = Math.min(value, max);
    var ratio = (capped - min) / (max - min);
    // Hue: 0° = red → 120° = green (smooth red-orange-yellow-green)
    var hue = ratio * 120;
    // Tweak saturation/lightness as needed for readability on your table background
    return `hsl(${hue}, 70%, 55%)`;
}

    function getRatingColor(rating) {
        const colors = {
            '1': '#ff0000',
            '2': '#ff6666',
            '3': '#ffff00',
            '4': '#66ff66',
            '5': '#00ff00'
        };
        return colors[rating] || '#e0e0e0';
    }

function updateRateDrinkSection() {
    // No longer needed – visibility and enabled state are now controlled
    // exclusively by isUserLoggedIn inside updateRecipeDetails().
    // Left as a no-op so existing calls do not break.
}

function updateRatingDisplay(stars, last_date) {
    var ratingBgColor = getRatingColor(stars);
    var displayValue = formatStarsValue(stars);
    $('#stars-display').html(
        '<span style="background-color:' + ratingBgColor + '; color: #000000;">' + displayValue + '</span>'
    );
    $('#last-date-display').text(last_date || 'Not set');
    $('#stars-select').val(stars || '');
    $('#last-date-input').val(new Date().toISOString().split('T')[0]);
}


    function loadRandomRecipe() {
        var user = $('#user-select').val();
        var filters = getFilters();
        console.log('Fetching random recipe with user:', user, 'filters:', filters);
        $.ajax({
            url: 'filter.php',
            method: 'GET',
            data: {
                action: 'getRandomRecipe',
                user: user,
                filters: JSON.stringify(filters)
            },
            dataType: 'json',
            success: function(data) {
                console.log('Random recipe:', data);
                if (data && data.Name && data.Source) {
                    $('#name-select').empty().append('<option value="' + data.Name + '">' + data.Name + '</option>').val(data.Name).trigger('change');
                    $('#name-count').text('1');
                    $('#source-select').empty().append('<option value="' + data.Source + '">' + data.Source + '</option>').val(data.Source);
                    $('#source-count').text('1');
                    updateRecipeDetails();
                } else {
                    console.error('Invalid random recipe data:', data);
                    alert('No recipes match your filters.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Random recipe AJAX failed:', textStatus, errorThrown);
                alert('Error fetching random recipe.');
            }
        });
    }

function loadFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    let filters = [];
    // FIX: Parse individual params
    let index = 0;
    while (urlParams.has(`term${index}`)) {
        const term = decodeURIComponent(urlParams.get(`term${index}`));  // Decode for safety
        const operator = urlParams.get(`operator${index}`);  // Already decoded correctly now
        const value = decodeURIComponent(urlParams.get(`value${index}`));
        const logic = urlParams.get(`logic${index}`) || 'AND';
        if (term && operator && value) {
            const f = { term, operator, value };
            if (index > 0) f.logic = logic;
            filters.push(f);
        }
        index++;
    }
    const name = decodeURIComponent(urlParams.get('name') || '');
    const source = decodeURIComponent(urlParams.get('source') || '');

    // FIX: Force user to 'All' for link loads to ensure full distinct values
    $('#user-select').val('All').trigger('change');

    // FIX: Handle recipe FIRST – populate dropdowns before any resets
    let hasRecipeParams = name || source;
    if (hasRecipeParams) {
        // Populate name-select with the name option
        let $nameSelect = $('#name-select');
        $nameSelect.empty().append('<option value="">STEP 3: Select a Name</option>');
if (name) {
    // Safe way to create option (handles apostrophes and special characters correctly)
    const $opt = $('<option></option>').attr('value', name).text(name);
    $nameSelect.append($opt);
    $nameSelect.val(name);
}
        // Load sources immediately
        updateSources();  // This will populate source-select
        // Defer source selection to after sources load (async)
// Defer source selection to after sources load (async)
setTimeout(() => {
    if (source && $('#source-select').find(`option[value="${source}"]`).length > 0) {
        $('#source-select').val(source).trigger('change');
    } else if (source) {
        // Fallback: force the value even if option doesn't exist yet
        $('#source-select').val(source).trigger('change');
    }
}, 250);
    }

    // Then handle filters (if no recipe)
    if (filters.length > 0 && !hasRecipeParams) {
        resetFilters();
        const $firstRow = $('.search-boxes .excel-row:first');
        const first = filters[0];
        $firstRow.find('.term-select').val(first.term);
        updateOperatorSelect($firstRow, first.term);
        $firstRow.find('.operator-select').val(first.operator);
        // FIX: Pass initialValue for restore
        updateValueInput($firstRow, first.term, first.value);
        console.log('Set filter row:', { term: first.term, operator: first.operator, value: first.value }); // Debug
        for (let i = 1; i < filters.length; i++) {
            var newBox = $('.search-boxes .excel-row:first').clone(true);
            newBox.find('.remove-box').show();
            newBox.find('.add-box').text('+');
            newBox.find('.term-select').val('');
            $('.search-boxes .excel-row:last').after(newBox);
            const filt = filters[i];
            newBox.find('.term-select').val(filt.term);
            updateOperatorSelect(newBox, filt.term);
            newBox.find('.operator-select').val(filt.operator);
            updateValueInput(newBox, filt.term, filt.value);
            newBox.find('.logic-select').val(filt.logic || 'AND');
        }
        updateLogicVisibility();
    }

    // Finally, update names (with flag to skip reset)
    window.skipNameReset = hasRecipeParams || filters.length > 0;  // Global flag for updateNames
    loadTotalCocktails();
    updateNames();
}

    $(document).on('click', '#save-rating', function() {
        var name = $('#name-select').val();
        var source = $('#source-select').val();
        var stars = $('#stars-select').val();
        var last_date = $('#last-date-input').val();

        // Validation before opening the modal
        if (!stars) {
            alert('Please select stars.');
            return;
        }
        if (!last_date) {
            alert('Please select a last date.');
            return;
        }

        // Populate the confirmation modal
        $('#confirm-recipe-name').text(name || '');
        $('#confirm-recipe-source').text(source || '');
        $('#confirm-rating-value').text(stars);
        $('#confirm-rating-date').text(last_date);
        $('#rating-confirm-error').hide().text('');

        // Prevent changes while the modal is open
        $('#stars-select, #last-date-input').prop('disabled', true);

        // Show the modal
        var modal = new bootstrap.Modal(document.getElementById('ratingConfirmModal'));
        modal.show();
    });

    // Confirm button inside the modal
    $(document).on('click', '#confirm-save-rating-btn', function() {
        var name = $('#name-select').val();
        var source = $('#source-select').val();
        var stars = $('#stars-select').val();
        var last_date = $('#last-date-input').val();

        // Always use the logged-in user (never the User: dropdown)
        if (!isUserLoggedIn || !loggedInUserId || !loggedInUserName) {
            $('#rating-confirm-error').text('You must be logged in to save a rating.').show();
            return;
        }

        var $error = $('#rating-confirm-error');
        $error.hide().text('');

        $.ajax({
            url: 'filter.php',
            method: 'POST',
            data: {
                action: 'saveRating',
                name: name,
                source: source,
                stars: stars,
                last_date: last_date,
                user_id: loggedInUserId,
                username: loggedInUserName
            },
            dataType: 'json',
            success: function(response) {
                console.log('Save rating response:', response);
                if (response.success) {
                    // Close modal and update display (no success alert)
                    var modalEl = document.getElementById('ratingConfirmModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    updateRatingDisplay(stars, last_date);
                    // Re-enable the controls
                    $('#stars-select, #last-date-input').prop('disabled', false);
                } else {
                    // Keep modal open and show error
                    $error.text('Failed to save rating: ' + (response.error || 'Unknown error')).show();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Save rating AJAX failed:', textStatus, errorThrown);
                $error.text('Error saving rating. Please try again.').show();
            }
        });
    });

    // When the modal is closed (Cancel or X), re-enable the controls
    $('#ratingConfirmModal').on('hidden.bs.modal', function () {
        $('#stars-select, #last-date-input').prop('disabled', false);
    });

$('#user-select').on('change', function() {
    $('.search-boxes .excel-row').each(function() {
        var term = $(this).find('.term-select').val();
        updateValueInput($(this), term);
    });
    var hasUserDependentFilters = false;
    $('.search-boxes .excel-row').each(function() {
        var term = $(this).find('.term-select').val();
        if (term === 'stars_out_of_3' || term === 'last_date') {
            hasUserDependentFilters = true;
            return false; // Break loop
        }
    });
    var currentName = $('#name-select').val();
    var currentSource = $('#source-select').val();
    loadTotalCocktails();
    if (hasUserDependentFilters) {
        updateNames(function() {
            if (currentName && $('#name-select option[value="' + currentName + '"]').length) {
                $('#name-select').val(currentName);
                updateSources(function() {
                    if (currentSource && $('#source-select option[value="' + currentSource + '"]').length) {
                        $('#source-select').val(currentSource);
                        updateRecipeDetails();
                    }
                });
            }
        });
    } else if (currentName && currentSource) {
        updateRecipeDetails();
    }
    if ($('#rate-drink-row').length) {
        updateRateDrinkSection();
    }
});

$('#name-select').off('change').on('change', updateSources);
$('#source-select').off('change').on('change', updateRecipeDetails);

// NEW: Re-sort ingredients immediately when the user changes the Ingredients Order dropdown
$(document).on('change', '#ingredients-order-select', function () {
    ingredientsOrder = $(this).val();
    if (currentRecipeData) {
        renderIngredientsTable(currentRecipeData);
    }
});

    $('#lucky-button').on('click', loadRandomRecipe);
$('#reset-button').on('click', function() {
    resetFilters();
    $('#name-select').html('<option value="">STEP 3: Select a Name</option>');
    $('#source-select').html('<option value="">STEP 4: Select a Source</option>');

    // Refresh counts instead of clearing them (so they persist with correct totals)
    loadTotalCocktails();

    // Note: source-count will keep its previous value (or update naturally when a name is selected later)
    // If you ever want to force-clear source-count after reset, add: $('#source-count').text('');

    $('#recipe_details').empty();
});

function generateCurrentUrl() {
    const filters = getFilters();
    let name = $('#name-select').val() || '';
    let source = $('#source-select').val() || '';

    // Aggressive decode to prevent double-encoding no matter where the value came from
    const decodeIfNeeded = (val) => {
        if (!val) return '';
        try {
            // Decode repeatedly until it's clean (handles multiple layers of encoding)
            let decoded = val;
            while (/%[0-9A-Fa-f]{2}/.test(decoded)) {
                decoded = decodeURIComponent(decoded);
            }
            return decoded;
        } catch (e) {
            return val;
        }
    };

    name = decodeIfNeeded(name);
    source = decodeIfNeeded(source);

    const params = new URLSearchParams();

    if ((!name || !source) && filters.length > 0) {
        filters.forEach((f, index) => {
            params.set(`term${index}`, encodeURIComponent(f.term));
            params.set(`operator${index}`, f.operator);
            params.set(`value${index}`, encodeURIComponent(f.value));
            if (index > 0 && f.logic) {
                params.set(`logic${index}`, encodeURIComponent(f.logic));
            }
        });
    }

    if (name)  params.set('name', encodeURIComponent(name));
    if (source) params.set('source', encodeURIComponent(source));

    const base = window.location.origin + window.location.pathname;
    return params.toString() ? `${base}?${params.toString()}` : base;
}

$('#copy-permalink').off('click').on('click', async function () {
    const link = generateCurrentUrl();
    const nameVal = $('#name-select').val() || '';
    const sourceVal = $('#source-select').val() || '';

    // Build a nice title for the share sheet / preview
    const shareTitle = (nameVal && sourceVal)
        ? `${nameVal} from ${sourceVal} — No-Nonsense Cocktails`
        : 'No-Nonsense Cocktails';

    // Try Web Share API first (best experience on iOS/Android)
    if (navigator.share) {
        try {
            await navigator.share({
                title: shareTitle,
                text: shareTitle,
                url: link
            });
            return; // User successfully shared via native share sheet
        } catch (err) {
            // User cancelled the share sheet or an error occurred.
            // Fall through to clipboard fallback.
        }
    }

    // Fallback: Copy only the clean URL to clipboard
    // (This avoids the "paste into browser → Google search" problem)
    try {
        await navigator.clipboard.writeText(link);
        const message = (nameVal && sourceVal)
            ? `Link for “${nameVal}” copied!`
            : 'Link copied to clipboard!';
        alert(message);
    } catch (err) {
        alert('Copy failed. Here is the link:\n' + link);
    }
});

    $('#create-qr-code').off('click').on('click', function () {
        const link = generateCurrentUrl();
        $('#qr-code').empty();
        new QRCode(document.getElementById('qr-code'), {
            text: link,
            width: 256,
            height: 256,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        $('#qr-code-popup').show();
    });
    $('#close-qr-code').on('click', function() {
        $('#qr-code-popup').hide();
    });
    loadUnitConversions(function() {
        loadFromUrl();
        updateOperatorSelect($('.search-boxes .excel-row:first'), $('.term-select').val());
        updateValueInput($('.search-boxes .excel-row:first'), $('.term-select').val());
        updateLogicVisibility();
    });

function formatStarsValue(stars) {
    if (!stars) return 'Not rated';
    const num = parseFloat(stars);
    if (isNaN(num) || !isFinite(num)) {
        return stars;   // 'Revisit', 'Next', 'TBD', or any other non-numeric status
    }
    // Original numeric formatting (preserved exactly)
    let str = num.toFixed(4);
    const [intPart, decPart] = str.split('.');
    let trimmed = decPart.replace(/0+$/, '');
    if (trimmed.length === 0) trimmed = '00';
    else if (trimmed.length === 1) trimmed += '0';
    return intPart + '.' + trimmed;
}

function updateRecipeDetails() {
    var name = $('#name-select').val();
    var source = $('#source-select').val();
    var user = $('#user-select').val();
    if (name && source) {
        $.ajax({
            url: 'filter.php',
            method: 'GET',
            data: { action: 'getRecipeDetails', name: name, source: source, user: user },
            dataType: 'json',
            success: function(data) {
                if (data && typeof data === 'object') {
                    currentRecipeData = data;
                    // Store the current recipe's ID in the hidden field
                    document.getElementById('current-recipe-id').value = data.ID;
                    var today = new Date().toISOString().split('T')[0];

                    var detailsHtml = `
                        <div class="card-body">
                            <div class="excel-row">
                                <div class="excel-cell label-cell">Name</div>
                                <div class="excel-cell content-cell"><strong>${data.Name || ''}</strong></div>
                            </div>

                            <div class="excel-row">
                                <div class="excel-cell label-cell">Stars Out of 3</div>
                                <div class="excel-cell content-cell" id="stars-display">${formatStarsValue(data.stars_out_of_3)}</div>
                                <div class="excel-cell label-cell rate-control">Rate this Drink:</div>
                                <div class="excel-cell rate-control">
                                    <select id="stars-select">
                                        <option value="">Select Stars</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="Next">Next</option>
                                        <option value="Revisit">Revisit</option>
                                    </select>
                                </div>
                            </div>

                            <div class="excel-row">
                                <div class="excel-cell label-cell">Last Date</div>
                                <div class="excel-cell content-cell" id="last-date-display">${data.last_date || 'Not set'}</div>
                                <div class="excel-cell rate-control"><input type="date" id="last-date-input" value="${today}"></div>
                                <div class="excel-cell rate-control"><button id="save-rating" class="btn btn-success btn-sm">Save Rating</button></div>
                            </div>
							
                            <div class="excel-row"><div class="excel-cell label-cell">Source</div><div class="excel-cell content-cell">${data.Source || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Page</div><div class="excel-cell content-cell">${data.Page || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Shaken/Stirred</div><div class="excel-cell content-cell">${data['Shaken/Stirred'] || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Ice</div><div class="excel-cell content-cell">${data.Ice || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Glass</div><div class="excel-cell content-cell">${data.Glass || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Garnish</div><div class="excel-cell content-cell">${data.Garnish || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Notes</div><div class="excel-cell content-cell">${data.Instructions || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Servings</div><div class="excel-cell content-cell">${data.Servings || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Base</div><div class="excel-cell content-cell">${data.Base || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Family</div><div class="excel-cell content-cell">${data.Family || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Link</div><div class="excel-cell content-cell"><a href="${data.Link || '#'}" target="_blank">${data.Link || ''}</a></div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Mixer</div><div class="excel-cell content-cell">${data.Mixer || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Color</div><div class="excel-cell content-cell">${data.Color || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Characteristics</div><div class="excel-cell content-cell">${data.Characteristics || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Adaptation of</div><div class="excel-cell content-cell">${data['Adaptation of'] || ''}</div></div>
                            <div class="excel-row"><div class="excel-cell label-cell">Variations</div><div class="excel-cell content-cell">${data.Variations || ''}</div></div>

                            <div class="mt-3">
                                <table class="ingredient-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Ingredient</th>
                                            <th>Volume Oz</th>
                                            <th>% Vol</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    `;

                    $('#recipe_details').html(detailsHtml);
                    renderIngredientsTable(data);
                    if (data.stars_out_of_3) $('#stars-select').val(data.stars_out_of_3);

                    // Visibility & enabled state controlled solely by login status
                    if (isUserLoggedIn) {
                        $('.rate-control').show();
                        $('#stars-select, #last-date-input, #save-rating').prop('disabled', false);
                    } else {
                        $('.rate-control').hide();
                    }
                }
            }
        });
    }
}

function renderIngredientsTable(data) {
    var ingredients = (data.Ingredients || '').split(';').filter(Boolean).map(function(ingredient) {
        var parts = ingredient.split(':');
        var name = parts[0] ? parts[0].trim() : '';
        var volumeStr = parts.length === 2 ? parts[1].trim() : '';
        var parsed = parseVolume(volumeStr);
        return { name: name, volume: parsed.display, numericVolume: parsed.numeric };
    });

    // Apply sort order chosen in the Ingredients Order dropdown
    if (ingredientsOrder === 'Vol Desc') {
        ingredients.sort(function(a, b) {
            return b.numericVolume - a.numericVolume || (a.volume < b.volume ? -1 : 1);
        });
    } else if (ingredientsOrder === 'Vol Asc') {
        ingredients.sort(function(a, b) {
            return a.numericVolume - b.numericVolume || (a.volume < b.volume ? -1 : 1);
        });
    } else if (ingredientsOrder === 'Alpha Asc') {
        ingredients.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });
    } else if (ingredientsOrder === 'Alpha Desc') {
        ingredients.sort(function(a, b) {
            return b.name.localeCompare(a.name);
        });
    } else if (ingredientsOrder === 'Cost Asc' || ingredientsOrder === 'Cost Desc') {
        // Cost-based sorting not yet implemented.
        // For now we keep the original recipe order.
        // TODO: implement when ingredient cost data is available.
    }
    // 'Recipe' (default) and Cost options → keep original data order (no sort)

    var totalVolume = ingredients.reduce(function(sum, ingredient) {
        return sum + (isNaN(ingredient.numericVolume) ? 0 : ingredient.numericVolume);
    }, 0);

    var tbodyHtml = '';
    ingredients.forEach(function(ingredient, index) {
        var percentVol = (ingredient.numericVolume && totalVolume > 0) 
            ? (ingredient.numericVolume / totalVolume * 100).toFixed(2) 
            : '';
	var colorStyle = percentVol ? `background-color: ${getColor(parseFloat(percentVol), 0, 60)};` : '';
        tbodyHtml += `
            <tr>
                <td>${index + 1}</td>
                <td style="word-break: break-word; white-space: normal;">${ingredient.name}</td>
                <td class="text-end">${ingredient.volume}</td>
                <td class="text-end" style="${colorStyle}">${percentVol ? percentVol + '%' : ''}</td>
            </tr>`;
    });

    tbodyHtml += `
        <tr>
            <td></td>
            <td><strong>Total</strong></td>
            <td class="text-end"><strong>${totalVolume.toFixed(2)}</strong></td>
            <td class="text-end"><strong>100.00%</strong></td>
        </tr>`;

    $('#recipe_details .ingredient-table tbody').html(tbodyHtml);
}




});

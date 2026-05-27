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
        var $select = $('<select class="operator-select" name="operator[]"></select>');
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
    var currentValue = $row.find('.value-input').val() || initialValue.trim(); // Trim to remove potential spaces
    $valueCell.empty();
    if (dropdownFields.includes(term)) {
        // Skip Name filter — keep it as plain text input
        if (term === 'name') {
            var $input = $('<input type="text" class="value-input" name="value[]" placeholder="Type name or partial name">');
            if (initialValue) $input.val(initialValue);
            $valueCell.append($input);
            return;
        }
        // For all other terms: use <select> with Choices.js
        var $select = $('<select class="value-input choices-filter" name="value[]"></select>');
        $select.append('<option value="">Any ' + term.replace(/_/g, ' ') + '</option>');
        $valueCell.append($select);
        // Load options via AJAX
        var filtersBefore = getFiltersBeforeForDropdown($row, term);
        console.log('Filters before for ' + term + ':', filtersBefore); // Debug
        loadDistinctValues(term, filtersBefore).then(function(values) {
            // Remember current value before rebuilding
            var preservedValue = (currentValue || initialValue).trim().toLowerCase(); // Normalize for comparison
            // Clear existing options except placeholder
            $select.find('option:not(:first)').remove();
            if (values.length === 0) {
                $select.append('<option value="" disabled>No options available</option>');
            } else {
                // Collect already used values for this term
                var usedValues = [];
                $('.search-boxes .excel-row').each(function() {
                    if ($(this).is($row)) return false;
                    if ($(this).find('.term-select').val() === term) {
                        var val = $(this).find('.value-input').val();
                        if (val) usedValues.push(val);
                    }
                });
                // Add unused values first
                values.filter(v => !usedValues.includes(v)).forEach(function(v) {
                    $select.append('<option value="' + v + '">' + v + '</option>');
                });
                // Add separator and used values
                if (usedValues.length > 0) {
                    var uniqueUsed = [...new Set(usedValues.filter(v => values.includes(v)))];
                    if (uniqueUsed.length > 0) {
                        $select.append('<option disabled>──────────────────</option>');
                        uniqueUsed.forEach(function(v) {
                            $select.append('<option value="' + v + '" style="color:#999;">' + v + ' (already used)</option>');
                        });
                    }
                }
            }
            console.log('Fetched values for ' + term + ':', values); // Debug
            console.log('Preserved value (normalized):', preservedValue); // Debug
            // Find matching value case-insensitively
            var matchingValue = values.find(v => v.trim().toLowerCase() === preservedValue) || initialValue; // Fallback to initial if not found
            console.log('Matching value found:', matchingValue); // Debug
            // Custom sorter functions
            var customSorter = function(a, b) {
                return a.label.localeCompare(b.label); // Default alphabetical
            };
            if (term === 'last_date') {
                // Sort dates chronologically (assuming YYYY-MM-DD format)
                customSorter = function(a, b) {
                    var dateA = new Date(a.label);
                    var dateB = new Date(b.label);
                    return dateA - dateB;
                };
            } else if (term === 'stars_out_of_3') {
                // Sort numerically first, then strings (e.g., 1,2,3,4,5 then TBD, Next, Revisit)
                customSorter = function(a, b) {
                    var numA = parseFloat(a.label);
                    var numB = parseFloat(b.label);
                    if (!isNaN(numA) && !isNaN(numB)) {
                        return numA - numB;
                    } else if (!isNaN(numA)) {
                        return -1;
                    } else if (!isNaN(numB)) {
                        return 1;
                    } else {
                        return a.label.localeCompare(b.label);
                    }
                };
            }
            // Custom validator (on callback)
            var customCallback = function(value) {
                if (term === 'last_date') {
                    // Validate date format (YYYY-MM-DD)
                    var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                    if (!dateRegex.test(value)) {
                        alert('Invalid date format. Use YYYY-MM-DD.');
                        return false;
                    }
                    // Optional: Check if valid date
                    if (isNaN(Date.parse(value))) {
                        alert('Invalid date.');
                        return false;
                    }
                } else if (term === 'stars_out_of_3') {
                    // Validate against allowed values (assuming from options, but enforce numeric 1-5 or specific strings)
                    var allowedStars = ['1', '2', '3', '4', '5', 'TBD', 'Next', 'Revisit'];
                    if (!allowedStars.includes(value)) {
                        alert('Invalid stars value.');
                        return false;
                    }
                }
                return true; // Valid
            };
            // Check if Choices.js is already initialized on this select (e.g., for first row)
            if ($select[0].choices) {
                console.log('Choices found—destroying instance for ' + term); // Debug
                $select[0].choices.destroy();
            } else {
                console.log('No existing Choices for ' + term + '—initializing new'); // Debug
            }
            // Initialize Choices.js with custom sorter
            var choicesInstance = new Choices($select[0], {
                searchEnabled: true,
                searchPlaceholderValue: 'Type to search...',
                removeItemButton: true,
                shouldSort: true,
                sorter: customSorter,
                itemSelectText: '',
                noResultsText: 'No matches found',
                noChoicesText: 'No options available',
                classNames: {
                    containerOuter: 'choices',
                    containerInner: 'choices__inner',
                    input: 'choices__input',
                    inputCloned: 'choices__input--cloned',
                    list: 'choices__list',
                    listItems: 'choices__list--multiple',
                    listSingle: 'choices__list--single',
                    listDropdown: 'choices__list--dropdown',
                    item: 'choices__item',
                    itemSelectable: 'choices__item--selectable',
                    itemDisabled: 'choices__item--disabled',
                    itemChoice: 'choices__item--choice',
                    placeholder: 'choices__placeholder',
                    group: 'choices__group',
                    groupHeading: 'choices__heading',
                    button: 'choices__button',
                    activeState: 'is-active',
                    focusState: 'is-focused',
                    openState: 'is-open',
                    disabledState: 'is-disabled',
                    highlightedState: 'is-highlighted',
                    selectedState: 'is-selected',
                    flippedState: 'is-flipped',
                    loadingState: 'is-loading',
                    noResults: 'has-no-results',
                    noChoices: 'has-no-choices'
                },
                callbackOnCreateTemplates: function(template) {
                    return {
                        choice: (classNames, data) => {
                            return template(`
                                <div class="${classNames.item} ${classNames.itemChoice} ${data.disabled ? classNames.itemDisabled : classNames.itemSelectable}" data-select-text="${this.config.itemSelectText}" data-choice ${data.disabled ? 'data-choice-disabled aria-disabled=true' : 'data-choice-selectable'} data-id="${data.id}" data-value="${data.value}" ${data.groupId > 0 ? 'role="treeitem"' : 'role="option"'}>
                                    ${data.label}
                                </div>
                            `);
                        }
                    };
                }
            });
            console.log('Re-initialized Choices for ' + term); // Debug
            // Add validator on choice select
            $select.on('change', function() {
                var selectedValue = choicesInstance.getValue(true);
                if (selectedValue && !customCallback(selectedValue)) {
                    choicesInstance.removeActiveItems(); // Clear invalid
                }
            });
            // Restore preserved value if still valid
            if (matchingValue) {
                $select.val(matchingValue); // First set native value
                choicesInstance.setChoiceByValue(matchingValue); // Then sync to Choices UI
                choicesInstance.showDropdown(); // Optional: Open to verify
                choicesInstance.hideDropdown(); // Close after
                console.log('Synced value to Choices UI:', choicesInstance.getValue(true)); // Debug
            }
        }).catch(function(error) {
    		console.error('AJAX error in loadDistinctValues for ' + term + ':', error);
	});
    } else {
        $valueCell.append('<input type="text" class="value-input" name="value[]" placeholder="STEP 2: Select or Type a Value">');
        if (currentValue) {
            $valueCell.find('.value-input').val(currentValue);
        }
    }
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
            </div>
        `);
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

/* 
Old code before 20251214
    function parseVolume(volumeStr) {
        if (!volumeStr) return { numeric: 0, unit: '', display: '' };
        const specialUnits = ['top', 'splash', 'rinse'];
        const lowered = volumeStr.toLowerCase();
        if (specialUnits.includes(lowered)) {
            return { numeric: unitConversions[lowered] || 0, unit: lowered, display: volumeStr };
        }
        const match = lowered.match(/^([\d.\/]+)\s*(\w+)?$/);
        if (!match) return { numeric: 0, unit: '', display: volumeStr };
        let quantityStr = match[1];
        let unit = match[2] || '';
console.log('Extracted unit:', unit);
        let quantity;
        const isFraction = quantityStr.includes('/');
        if (isFraction) {
            const [numerator, denominator] = quantityStr.split('/').map(Number);
            quantity = numerator / denominator;
        } else {
            quantity = parseFloat(quantityStr);
        }
        if (isNaN(quantity)) return { numeric: 0, unit, display: volumeStr };
        const conversionFactor = unitConversions[unit] || 1.0;
        let display;
        if (unit === 'oz' || unit === '') {
            if (isFraction) {
                display = quantity.toFixed(3).replace(/\.?0+$/, '');
            } else {
                display = quantityStr;
            }
        } else {
            display = quantityStr + (unit ? ' ' + unit : '');
        }
console.log('Final display in parseVolume:', display);
        return { numeric: quantity * conversionFactor, unit, display };
    }
*/

function parseVolume(volumeStr) {
    if (!volumeStr) return { numeric: 0, unit: '', display: '' };
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
    const conversionFactor = unitConversions[unit] || 1.0;
    return { numeric: numeric * conversionFactor, unit: unit, display: display };
}

function getColor(value, min, max) {
    if (min === max) return '';
    var ratio = (value - min) / (max - min);
    var r = Math.round(255 * (1 - ratio));
    var g = Math.round(255 * ratio);
    var b = 0;
    return 'rgb(' + r + ',' + g + ',' + b + ')';
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
                    var today = new Date().toISOString().split('T')[0];

                    var detailsHtml = `
                        <div class="p-2 bg-white border border-gray-400">
                            <div class="flex justify-between items-center border-b border-gray-300 pb-2 mb-2">
                                <div class="text-2xl font-bold">${data.Name || ''}</div>
                                <div id="stars-display" class="px-4 py-1 text-xl font-bold border border-black rounded" style="background-color: ${getRatingColor(data.stars_out_of_3)}; color: #000000;">
                                    ${data.stars_out_of_3 || 'Not rated'}
                                </div>
                            </div>
                            <div class="flex items-center gap-x-2 mb-3 text-sm">
                                <span class="font-medium text-gray-600">Last Date:</span>
                                <span id="last-date-display" class="font-semibold">${data.last_date || 'Not set'}</span>
                            </div>
                            <div id="rate-drink-row" class="flex flex-wrap items-end gap-2 mb-4 p-2 bg-gray-50 border border-gray-300 rounded">
                                <span class="font-medium whitespace-nowrap">Rate this Drink:</span>
                                <select id="stars-select" class="border border-gray-400 bg-white rounded px-2 py-px text-sm"></select>
                                <input type="date" id="last-date-input" value="${today}" class="border border-gray-400 bg-white rounded px-2 py-px text-sm">
                                <button id="save-rating" class="bg-teal-700 hover:bg-teal-800 text-white px-4 py-px rounded text-sm font-medium">Save Rating</button>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-1 text-sm mb-6">
                                <!-- metadata rows (Source, Page, etc.) - unchanged content, minimal gap-y-1 -->
                            </div>
                            <div>
                                <div class="font-semibold text-gray-700 mb-1 border-b">Ingredients</div>
                                <table class="w-full border-collapse text-sm" id="ingredients-table">
                                    <thead>
                                        <tr class="bg-gray-100 border-b">
                                            <th class="text-left py-1 px-1 font-medium w-8">#</th>
                                            <th class="text-left py-1 px-1 font-medium">Ingredient</th>
                                            <th class="text-right py-1 px-1 font-medium w-16">Volume Oz</th>
                                            <th class="text-right py-1 px-1 font-medium w-20">% Vol</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm"></tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                <div class="font-medium text-gray-600 mb-1">Notes / Instructions</div>
                                <div class="p-3 bg-gray-50 border border-gray-300 text-sm leading-tight">${data.Instructions || ''}</div>
                            </div>
                        </div>
                    `;
                    $('#recipe_details').html(detailsHtml);
                    renderIngredientsTable(data);
                    if (data.stars_out_of_3) $('#stars-select').val(data.stars_out_of_3);
                    updateRateDrinkSection();
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
        return {
            name: name,
            volume: parsed.display,
            numericVolume: parsed.numeric
        };
    });

    ingredients.sort(function(a, b) {
        return b.numericVolume - a.numericVolume || (a.volume < b.volume ? -1 : 1);
    });

    var totalVolume = ingredients.reduce(function(sum, ingredient) {
        return sum + (isNaN(ingredient.numericVolume) ? 0 : ingredient.numericVolume);
    }, 0);

    var tbodyHtml = '';
    ingredients.forEach(function(ingredient, index) {
        var percentVol = (ingredient.numericVolume && totalVolume > 0) 
            ? (ingredient.numericVolume / totalVolume * 100).toFixed(2) 
            : '';
        var colorStyle = percentVol ? `background-color: ${getColor(parseFloat(percentVol), 0, 100)};` : '';
        
        tbodyHtml += `
            <tr class="border-b">
                <td class="py-1 px-1 text-center font-medium">${index + 1}</td>
                <td class="py-1 px-1">${ingredient.name}</td>
                <td class="py-1 px-1 text-right font-medium">${ingredient.volume}</td>
                <td class="py-1 px-1 text-right" style="${colorStyle}">${percentVol ? percentVol + '%' : ''}</td>
            </tr>`;
    });

    // Total row
    tbodyHtml += `
        <tr class="font-semibold bg-gray-100">
            <td class="py-1 px-1"></td>
            <td class="py-1 px-1">Total</td>
            <td class="py-1 px-1 text-right">${totalVolume.toFixed(2)}</td>
            <td class="py-1 px-1 text-right">100.00%</td>
        </tr>`;

    $('#ingredients-table tbody').html(tbodyHtml);
}
	
	function updateRateDrinkSection() {
        var user = $('#user-select').val();
        if (user && user !== 'All') {
            $('#rate-drink-row select, #rate-drink-row input, #rate-drink-row button').prop('disabled', false);
        } else {
            $('#rate-drink-row select, #rate-drink-row input, #rate-drink-row button').prop('disabled', true);
        }
    }
    function updateRatingDisplay(stars, last_date) {
        var ratingBgColor = getRatingColor(stars);
        $('#stars-display').html('<span style="background-color:' + ratingBgColor + '; color: #000000;">' + (stars || 'Not rated') + '</span>');
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
            $nameSelect.append(`<option value="${name}">${name}</option>`);
            $nameSelect.val(name);
        }
        // Load sources immediately
        updateSources();  // This will populate source-select
        // Defer source selection to after sources load (async)
        setTimeout(() => {
            if (source) {
                $('#source-select').val(source).trigger('change');
            }
        }, 100);  // Small delay for AJAX
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
        var username = $('#user-select').val();
        if (!username || username === 'All' || username === '') {
            alert('Please select a specific user to rate the drink.');
            return;
        }
        if (!stars) {
            alert('Please select stars.');
            return;
        }
        if (!last_date) {
            alert('Please select a last date.');
            return;
        }
        $.ajax({
            url: 'filter.php',
            method: 'POST',
            data: {
                action: 'saveRating',
                name: name,
                source: source,
                stars: stars,
                last_date: last_date,
                username: username
            },
            dataType: 'json',
            success: function(response) {
                console.log('Save rating response:', response);
                if (response.success) {
                    alert('Rating saved successfully.');
                    updateRatingDisplay(stars, last_date);
                } else {
                    alert('Failed to save rating: ' + response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Save rating AJAX failed:', textStatus, errorThrown);
                alert('Error saving rating.');
            }
        });
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

    $('#name-select').on('change', updateSources);
    $('#source-select').on('change', updateRecipeDetails);
    $('#lucky-button').on('click', loadRandomRecipe);
    $('#reset-button').on('click', function() {
        resetFilters();
        $('#name-select').html('<option value="">STEP 3: Select a Name</option>');
        $('#source-select').html('<option value="">STEP 4: Select a Source</option>');
        $('#name-count').text('');
        $('#source-count').text('');
        $('#recipe_details').empty();
    });

function generateCurrentUrl() {
    const filters = getFilters();
    const name = $('#name-select').val() || '';
    const source = $('#source-select').val() || '';
    const params = new URLSearchParams();
    // Only add filters if no specific recipe is selected (avoids conflicts on load)
    if ((!name || !source) && filters.length > 0) {
        filters.forEach((f, index) => {
            params.set(`term${index}`, encodeURIComponent(f.term));
            // FIX: Don't encode operators (prevents double-encoding)
            params.set(`operator${index}`, f.operator);
            params.set(`value${index}`, encodeURIComponent(f.value));
            if (index > 0 && f.logic) {
                params.set(`logic${index}`, encodeURIComponent(f.logic));
            }
        });
    }
    if (name) params.set('name', encodeURIComponent(name));
    if (source) params.set('source', encodeURIComponent(source));
    const base = window.location.origin + window.location.pathname;
    return params.toString() ? `${base}?${params.toString()}` : base;
}

    $('#copy-permalink').off('click').on('click', function () {
        const link = generateCurrentUrl();
        navigator.clipboard.writeText(link).then(() => {
            const recipeName = name ? ` for “${name}”` : '';
            alert(`Permalink${recipeName} copied to clipboard!`);
        }).catch(() => {
            alert('Copy failed. Here is the link:\n' + link);
        });
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
});

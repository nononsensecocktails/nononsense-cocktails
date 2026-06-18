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

    // NEW: Undo state for filter changes
    let lastFilterSnapshot = null;
    let lastChangedRow = null;

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
    return new Promise(function(resolve) {
        var $valueCell = $row.find('.excel-cell:nth-child(3)');
        var currentValue = $row.find('.value-input').val() || initialValue.trim();
        $valueCell.empty();

        if (dropdownFields.includes(term)) {
            if (term === 'name') {
                var $input = $('<input type="text" class="value-input" name="value[]" placeholder="Type name or partial name">');
                if (initialValue) $input.val(initialValue);
                $valueCell.append($input);
                resolve();
                return;
            }

            var $select = $('<select class="value-input choices-filter" name="value[]"></select>');
            $select.append('<option value="">Any ' + term.replace(/_/g, ' ') + '</option>');
            $valueCell.append($select);

            var filtersBefore = getFiltersBeforeForDropdown($row, term);
            loadDistinctValues(term, filtersBefore).then(function(values) {
                var preservedValue = (currentValue || initialValue).trim().toLowerCase();
                $select.find('option:not(:first)').remove();

                if (values.length === 0) {
                    $select.append('<option value="" disabled>No options available</option>');
                } else {
                    var usedValues = [];
                    $('.search-boxes .excel-row').each(function() {
                        if ($(this).is($row)) return false;
                        if ($(this).find('.term-select').val() === term) {
                            var val = $(this).find('.value-input').val();
                            if (val) usedValues.push(val);
                        }
                    });

                    values.filter(v => !usedValues.includes(v)).forEach(function(v) {
                        $select.append('<option value="' + v + '">' + v + '</option>');
                    });

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

                var matchingValue = values.find(v => v.trim().toLowerCase() === preservedValue) || initialValue;

                var choicesInstance = null;

                if (typeof Choices !== 'undefined') {
                    if ($select[0].choices) {
                        $select[0].choices.destroy();
                    }

                    choicesInstance = new Choices($select[0], {
                        searchEnabled: true,
                        searchPlaceholderValue: 'Type to search...',
                        removeItemButton: true,
                        shouldSort: true,
                        itemSelectText: '',
                        noResultsText: 'No matches found',
                        noChoicesText: 'No options available'
                    });

                    if (matchingValue) {
                        $select.val(matchingValue);
                        choicesInstance.setChoiceByValue(matchingValue);
                    }
                } else {
                    console.warn('Choices.js not loaded for ' + term);
                    values.forEach(function(v) {
                        $select.append('<option value="' + v + '">' + v + '</option>');
                    });
                    if (matchingValue) {
                        $select.val(matchingValue);
                    }
                }

                resolve();
            }).catch(function(error) {
                console.error('loadDistinctValues error for ' + term, error);
                resolve();
            });

        } else {
            $valueCell.append('<input type="text" class="value-input" name="value[]" placeholder="STEP 2: Select or Type a Value">');
            if (currentValue) {
                $valueCell.find('.value-input').val(currentValue);
            }
            resolve();
        }
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

    }
}

// NEW: Undo snapshot helpers
function captureFilterSnapshot() {
    var snapshot = [];
    $('.search-boxes .excel-row').each(function() {
        var $row = $(this);
        var term = $row.find('.term-select').val();
        var operator = $row.find('.operator-select').val();
        var value = $row.find('.value-input').val();
        var logic = $row.find('.logic-select').val() || 'AND';
        if (term) {
            snapshot.push({
                term: term,
                operator: operator,
                value: value,
                logic: logic
            });
        }
    });
    snapshot.ingredientsOrder = ingredientsOrder;
    return snapshot;
}

function restoreFromSnapshot(snapshot) {
    if (!snapshot || snapshot.length === 0) return;

    $('.search-boxes').empty();

    snapshot.forEach(function(item, index) {
        var newBox;
        if (index === 0) {
            newBox = $(`
                <div class="excel-row">
                    <div class="excel-cell term-select-cell">
                        <select class="term-select" name="term[]"></select>
                    </div>
                    <div class="excel-cell"><select class="operator-select" name="operator[]"></select></div>
                    <div class="excel-cell"><input type="text" class="value-input" name="value[]"></div>
                    <div class="excel-cell button-cell"><button class="add-box">+</button></div>
                    <div class="excel-cell button-cell"><button class="remove-box" style="display:none;">-</button></div>
                    <div class="excel-cell logic-cell"><select class="logic-select" name="logic[]" style="display:none;"></select></div>
                </div>
            `);
        } else {
            newBox = $('.search-boxes .excel-row:first').clone(true);
            newBox.find('.remove-box').show();
            newBox.find('.add-box').text('+');
        }

        newBox.find('.term-select').val(item.term);
        updateOperatorSelect(newBox, item.term);
        newBox.find('.operator-select').val(item.operator);
        updateValueInput(newBox, item.term, item.value || '').then(function() {
            newBox.find('.logic-select').val(item.logic || 'AND');
            if (index === snapshot.length - 1) {
                updateLogicVisibility();
                updateNames();
            }
        });
        $('.search-boxes').append(newBox);
    });

    if (snapshot.ingredientsOrder) {
        ingredientsOrder = snapshot.ingredientsOrder;
        $('#ingredients-order-select').val(ingredientsOrder);
    }
}

function removeAllUndoButtons() {
    $('.undo-filter-btn').remove();
    lastFilterSnapshot = null;
    lastChangedRow = null;
}

function showUndoButtonForRow($row) {
    removeAllUndoButtons();

    var $undoBtn = $('<button class="undo-filter-btn btn btn-sm btn-outline-secondary ms-1" style="font-size:0.75rem; padding:1px 6px;">Undo</button>');
    $undoBtn.on('click', function() {
        if (lastFilterSnapshot) {
            restoreFromSnapshot(lastFilterSnapshot);
        }
        removeAllUndoButtons();
    });

    var $buttonCell = $row.find('.button-cell').last();
    if ($buttonCell.length) {
        $buttonCell.append($undoBtn);
    } else {
        $row.append($undoBtn);
    }

    lastChangedRow = $row;
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
        var $currentRow = $(this).closest('.excel-row');
        lastFilterSnapshot = captureFilterSnapshot();

        var newBox = $('.search-boxes .excel-row:first').clone(true);
        newBox.find('.value-input').val('');
        newBox.find('.remove-box').show();
        newBox.find('.add-box').text('+');
        newBox.find('.term-select').val('');
        newBox.find('.excel-cell').last().remove();
        $currentRow.after(newBox);
        var term = newBox.find('.term-select').val();
        updateOperatorSelect(newBox, term);
        updateValueInput(newBox, term).then(function() {
            updateLogicVisibility();
            updateAllBelow(newBox).then(function() {
                showUndoButtonForRow(newBox);
            });
        });
    });

    $(document).on('click', '.remove-box', function() {
        if ($('.search-boxes .excel-row').length > 1) {
            pendingFilterChange = true;
            var $row = $(this).closest('.excel-row');
            lastFilterSnapshot = captureFilterSnapshot();
            $row.remove();
            updateLogicVisibility();
            updateNames();
            var $prevRow = $('.search-boxes .excel-row').last();
            if ($prevRow.length) {
                showUndoButtonForRow($prevRow);
            }
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
        lastFilterSnapshot = captureFilterSnapshot();
        updateAllBelow($row).then(function() {
            showUndoButtonForRow($row);
        });
    });
    $(document).on('change', '.value-input', function() {
        pendingFilterChange = true;
        var $row = $(this).closest('.excel-row');
        lastFilterSnapshot = captureFilterSnapshot();
        updateAllBelow($row).then(function() {
            showUndoButtonForRow($row);
        });
    });

    $(document).on('change', '.logic-select', function() {
        pendingFilterChange = true;
        var $row = $(this).closest('.excel-row');
        lastFilterSnapshot = captureFilterSnapshot();
        var term = $row.find('.term-select').val();
        if (term) {
            // Refresh this row first (its own options depend on the new logic), then lower rows
            updateValueInput($row, term).then(function() {
                updateAllBelow($row).then(function() {
                    showUndoButtonForRow($row);
                });
            });
        } else {
            updateAllBelow($row).then(function() {
                showUndoButtonForRow($row);
            });
        }
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
        var promises = [];
        var foundStart = $startRow.length === 0;
        $('.search-boxes .excel-row').each(function() {
            if ($(this).is($startRow)) {
                foundStart = true;
                return true;
            }
            if (foundStart) {
                var term = $(this).find('.term-select').val();
                if (term) {
                    promises.push(updateValueInput($(this), term));
                }
            }
        });
        // Return the promise so callers can chain .then()
        return Promise.all(promises).then(function() {
            updateNames();
        });
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

function updateRateDrinkSection() {
    var user = $('#user-select').val();
    if (user && user !== 'All') {
        $('#stars-select, #last-date-input, #save-rating').prop('disabled', false);
    } else {
        $('#stars-select, #last-date-input, #save-rating').prop('disabled', true);
    }
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
    removeAllUndoButtons();
    resetFilters();
    $('#name-select').html('<option value="">STEP 3: Select a Name</option>');
    $('#source-select').html('<option value="">STEP 4: Select a Source</option>');

    loadTotalCocktails();
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


$('#copy-permalink').off('click').on('click', function () {
    const link = generateCurrentUrl();
    const nameVal = $('#name-select').val() || '';
    const sourceVal = $('#source-select').val() || '';

    let textToCopy = link;

    if (nameVal && sourceVal) {
        // Copy a nice formatted line that includes the recipe name + source
        textToCopy = `${nameVal} from ${sourceVal} — No-Nonsense Cocktails\n${link}`;
    }

    navigator.clipboard.writeText(textToCopy).then(() => {
        if (nameVal && sourceVal) {
            alert(`Link for “${nameVal}” from ${sourceVal} copied!`);
        } else {
            alert('Permalink copied to clipboard!');
        }
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
        updateValueInput($('.search-boxes .excel-row:first'), $('.term-select').val()).then(function() {
            updateLogicVisibility();
        });
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
                                <div class="excel-cell label-cell">Rate this Drink:</div>
                                <div class="excel-cell">
                                    <select id="stars-select">
                                        <option value="">Select Stars</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="TBD">TBD</option>
                                        <option value="Next">Next</option>
                                        <option value="Revisit">Revisit</option>
                                    </select>
                                </div>
                            </div>

                            <div class="excel-row">
                                <div class="excel-cell label-cell">Last Date</div>
                                <div class="excel-cell content-cell" id="last-date-display">${data.last_date || 'Not set'}</div>
                                <div class="excel-cell"><input type="date" id="last-date-input" value="${today}"></div>
                                <div class="excel-cell"><button id="save-rating" class="btn btn-success btn-sm" style="display: none;">Save Rating</button></div>
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
        var colorStyle = percentVol ? `background-color: ${getColor(parseFloat(percentVol), 0, 100)};` : '';
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

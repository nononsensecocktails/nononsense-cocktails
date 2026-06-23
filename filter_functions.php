<?php
require_once 'db.php';
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/home/m2igrnpfhd75/public_html/php_errors.log');

function getFilters() {
    return [
        'adaption_of' => ['table' => 'r', 'column' => '`Adaptation of`', 'type' => 'string'],
        'base' => ['table' => 'r', 'column' => 'Base', 'type' => 'string'],
        'characteristics' => ['table' => 'r', 'column' => 'Characteristics', 'type' => 'string'],
        'color' => ['table' => 'r', 'column' => 'Color', 'type' => 'string'],
        'family' => ['table' => 'r', 'column' => 'Family', 'type' => 'string'],
        'garnish' => ['table' => 'r', 'column' => 'Garnish', 'type' => 'string'],
        'glass' => ['table' => 'r', 'column' => 'Glass', 'type' => 'string'],
        'ice' => ['table' => 'r', 'column' => 'Ice', 'type' => 'string'],
        'ingredients' => ['table' => 'r', 'column' => 'Ingredients', 'type' => 'string'],
        'instructions' => ['table' => 'r', 'column' => 'Instructions', 'type' => 'string'],
        'last_date' => ['table' => 'ur', 'column' => 'last_date', 'type' => 'date'],
        'mixer' => ['table' => 'r', 'column' => 'Mixer', 'type' => 'string'],
        'name' => ['table' => 'r', 'column' => 'Name', 'type' => 'string'],
        'num_ingredients' => ['table' => 'r', 'column' => 'Num_Ingredients', 'type' => 'numeric'],
        'servings' => ['table' => 'r', 'column' => 'Servings', 'type' => 'numeric'],
        'shaken_stirred' => ['table' => 'r', 'column' => '`Shaken/Stirred`', 'type' => 'string'],
        'source' => ['table' => 'r', 'column' => 'Source', 'type' => 'string'],
        'stars_out_of_3' => ['table' => 'ur', 'column' => 'stars_out_of_3', 'type' => 'string'],
        'variations' => ['table' => 'r', 'column' => 'Variations', 'type' => 'string']
    ];
}

function has_ur_filters($filters_data) {
    foreach ($filters_data as $filter) {
        $term = $filter['term'] ?? '';
        if ($term === 'stars_out_of_3' || $term === 'last_date') {
            return true;
        }
    }
    return false;
}

/* -------------------------------------------------------------------------
   NEW CLEAN FUNCTION – extracts distinct ingredient names only
   ------------------------------------------------------------------------- */
function getDistinctIngredientNames($conn, $user, $filters_data) {
    $filters = getFilters();
    $params = [];
    $where_clause = buildWhereClause($filters_data, $filters, $params, $user);
    $join = "LEFT JOIN (
                SELECT recipe_id, username, stars_out_of_3, last_date
                FROM user_ratings ur1
                WHERE last_date = (SELECT MAX(last_date) FROM user_ratings ur2
                                   WHERE ur2.recipe_id = ur1.recipe_id AND ur2.username = ur1.username)
            ) ur ON r.ID = ur.recipe_id" . ($user !== 'All' ? " AND ur.username = :current_user" : "");
    if ($user !== 'All') {
        $params[':current_user'] = $user;
    }
    $sql = "SELECT r.Ingredients
            FROM recipes r
            $join
            WHERE (COALESCE(r.Hide, 0) = 0 OR EXISTS (
                    SELECT 1 FROM user_ratings ur3 WHERE ur3.recipe_id = r.ID AND ur3.username = r.Source))
              AND r.Ingredients IS NOT NULL AND r.Ingredients != ''";
    if ($user === 'All') {
        if (has_ur_filters($filters_data) && $where_clause !== '1=1') {
            $sql .= " GROUP BY r.ID HAVING ($where_clause)";
        } else {
            if ($where_clause !== '1=1') {
                $sql .= " AND ($where_clause)";
            }
            $sql .= " GROUP BY r.ID";
        }
    } else {
        if ($where_clause !== '1=1') {
            $sql .= " AND ($where_clause)";
        }
    }
    $stmt = $conn->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $names = [];
    foreach ($rows as $str) {
        $items = array_map('trim', explode(';', $str));
        foreach ($items as $item) {
            if ($item === '') continue;
            $parts = explode(':', $item, 2);
            $name = trim($parts[0]);
            if ($name && $name !== '0') $names[] = $name;
        }
    }
    $names = array_unique($names);
    sort($names);
    return $names;
}

/* -------------------------------------------------------------------------
   Original buildCondition (updated – remove special case for 'name' to use partial LIKE/NOT LIKE)
   ------------------------------------------------------------------------- */
function buildCondition($term, $operator, $value, &$params, $index, $filters, $user) {
    if ($term === 'All') {
        $all_conditions = [];
        foreach ($filters as $key => $filter) {
            if ($filter['table'] === 'r' && $filter['type'] === 'string') {
                $param_name = ':' . $key . $index;
                $params[$param_name] = "%$value%";
                $all_conditions[] = "{$filter['table']}.{$filter['column']} LIKE $param_name";
            }
        }
        return !empty($all_conditions) ? '(' . implode(' OR ', $all_conditions) . ')' : '1=1';
    }
    if (!array_key_exists($term, $filters)) {
        error_log(date('Y-m-d H:i:s') . " Invalid term: $term\n", 3, '/home/m2igrnpfhd75/public_html/php_errors.log');
        return '1=1';
    }
    $table = $filters[$term]['table'];
    $column = $filters[$term]['column'];
    $type = $filters[$term]['type'] ?? 'string';
    $param_name = ':' . $term . $index;
    if ($table === 'ur' && $user === 'All') {
        if ($term === 'stars_out_of_3') {
            if ($value === 'TBD') {
                if ($operator === '=') {
                    return "(COUNT(CASE WHEN ur.stars_out_of_3 REGEXP '^[0-9]+(\.[0-9]+)?$' THEN 1 ELSE NULL END) = 0)";
                } elseif ($operator === '<>') {
                    return "(COUNT(CASE WHEN ur.stars_out_of_3 REGEXP '^[0-9]+(\.[0-9]+)?$' THEN 1 ELSE NULL END) > 0)";
                } else {
                    return '1=0';
                }
            } elseif (!is_numeric($value)) {
                $params[$param_name] = $value;
                if ($operator === '=') {
                    return "(SUM(CASE WHEN ur.stars_out_of_3 = $param_name THEN 1 ELSE 0 END) = COUNT(ur.recipe_id) AND COUNT(ur.recipe_id) > 1)";
                } elseif ($operator === '<>') {
                    return "(SUM(CASE WHEN ur.stars_out_of_3 = $param_name THEN 1 ELSE 0 END) < COUNT(ur.recipe_id) OR COUNT(ur.recipe_id) <= 1)";
                } else {
                    return '1=0';
                }
            } else {
                $params[$param_name] = floatval($value);
                return "(AVG(CASE WHEN ur.stars_out_of_3 REGEXP '^[0-9]+(\.[0-9]+)?$' THEN CAST(ur.stars_out_of_3 AS DECIMAL(10,4)) ELSE NULL END) $operator $param_name)";
            }
        } elseif ($term === 'last_date') {
            $params[$param_name] = date('Y-m-d', strtotime($value));
            return "(MAX(ur.last_date) $operator $param_name)";
        }
    } else {
        if ($term === 'stars_out_of_3') {
            if ($value === 'TBD' && $operator === '=') {
                return "($table.$column = 'TBD' OR $table.$column IS NULL)";
            } elseif ($value === 'TBD' && $operator === '<>') {
                return "($table.$column <> 'TBD' AND $table.$column IS NOT NULL)";
            } elseif (in_array($operator, ['>=', '<'])) {
                if (is_numeric($value)) {
                    $params[$param_name] = $value;
                    return "($table.$column REGEXP '^[0-9]+(\.[0-9]+)?$' AND CAST($table.$column AS DECIMAL) $operator $param_name)";
                } else {
                    return '1=0';
                }
            } else {
                $params[$param_name] = $value;
                return "$table.$column $operator $param_name";
            }
        } else {
            if ($type === 'string') {
                if ($term === 'source' && $operator === '=') {
                    $params[$param_name] = $value;
                    return "$table.$column = $param_name";
                }
                if ($term === 'source' && $operator === '<>') {
                    $params[$param_name] = $value;
                    return "$table.$column <> $param_name";
                }
                if ($operator === '=') {
                    $params[$param_name] = "%$value%";
                    return "$table.$column LIKE $param_name";
                } elseif ($operator === '<>') {
                    $params[$param_name] = "%$value%";
                    return "$table.$column NOT LIKE $param_name";
                }
            } elseif ($type === 'numeric') {
                $params[$param_name] = floatval($value);
                return "$table.$column $operator $param_name";
            } elseif ($type === 'date') {
                $params[$param_name] = date('Y-m-d', strtotime($value));
                return "$table.$column $operator $param_name";
            }
        }
    }
    return '1=1';
}

/* -------------------------------------------------------------------------
   Updated buildWhereClause – now builds sequentially with logic, inverts for consecutive same-term negatives
   ------------------------------------------------------------------------- */
function buildWhereClause($filters_data, $filters, &$params, $user = '') {
    if (empty($filters_data)) {
        return '1=1';
    }
    $conditions = [];
    $prev_term = null;
    $prev_operator = null;
    foreach ($filters_data as $i => $filter) {
        $term = $filter['term'] ?? '';
        $operator = $filter['operator'] ?? '=';
        $value = $filter['value'] ?? '';
        $logic = isset($filter['logic']) ? strtoupper($filter['logic']) : null;
        if (!$term || $value === '') continue;
        $condition = buildCondition($term, $operator, $value, $params, $i, $filters, $user);
        if ($condition === '1=1' || $condition === '1=0') continue;
        if (!empty($conditions) && $logic) {
            $effective_logic = $logic;
            if ($prev_term === $term && $prev_operator === '<>' && $operator === '<>') {
                $effective_logic = ($logic === 'AND') ? 'OR' : 'AND';
            }
            $conditions[] = $effective_logic;
        }
        $conditions[] = "($condition)";
        $prev_term = $term;
        $prev_operator = $operator;
    }
    if (empty($conditions)) {
        return '1=1';
    }
    return implode(' ', $conditions);
}

/* -------------------------------------------------------------------------
   All other functions – 100% identical to your working v1.0 except:
   - getDistinctValues() now uses the new clean function for ingredients
   ------------------------------------------------------------------------- */
function getTotalCocktails($conn, $user, $filters_data) {
    $filters = getFilters();
    $params = [];
    $where_clause = buildWhereClause($filters_data, $filters, $params, $user);
    $join = "LEFT JOIN (
                SELECT recipe_id, username, stars_out_of_3, last_date
                FROM user_ratings ur1
                WHERE last_date = (SELECT MAX(last_date) FROM user_ratings ur2 WHERE ur2.recipe_id = ur1.recipe_id AND ur2.username = ur1.username)
            ) ur ON r.ID = ur.recipe_id" . ($user !== 'All' ? " AND ur.username = :current_user" : "");
    if ($user !== 'All') {
        $params[':current_user'] = $user;
    }
    $base_where = "WHERE (COALESCE(r.Hide, 0) = 0 OR EXISTS (SELECT 1 FROM user_ratings ur3 WHERE ur3.recipe_id = r.ID AND ur3.username = r.Source))";
    if ($user === 'All' && has_ur_filters($filters_data)) {
        $inner_sql = "SELECT r.ID FROM recipes r $join $base_where GROUP BY r.ID HAVING ($where_clause)";
        $sql = "SELECT COUNT(*) as total FROM ($inner_sql) sub";
    } else {
        $sql = "SELECT COUNT(DISTINCT r.ID) as total FROM recipes r $join $base_where";
        if ($where_clause !== '1=1') {
            $sql .= " AND ($where_clause)";
        }
    }
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) $stmt->bindValue($key, $value);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    return ['total' => $total];
}

function getDistinctValues($conn, $term, $user, $filters_data) {
    $filters = getFilters();
    if (!array_key_exists($term, $filters)) return [];
    if ($term === 'stars_out_of_3') {
        if ($user !== 'All') {
            $sql = "SELECT DISTINCT ur.stars_out_of_3
                    FROM user_ratings ur
                    WHERE ur.username = :current_user
                      AND ur.stars_out_of_3 IS NOT NULL";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':current_user', $user);
            $stmt->execute();
            $values = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $sql_unrated = "SELECT 1 FROM recipes r
                            WHERE (COALESCE(r.Hide, 0) = 0 OR EXISTS (SELECT 1 FROM user_ratings ur3 WHERE ur3.recipe_id = r.ID AND ur3.username = r.Source))
                              AND NOT EXISTS (SELECT 1 FROM user_ratings ur4 WHERE ur4.recipe_id = r.ID AND ur4.username = :current_user)
                            LIMIT 1";
            $stmt_unrated = $conn->prepare($sql_unrated);
            $stmt_unrated->bindValue(':current_user', $user);
            $stmt_unrated->execute();
            if ($stmt_unrated->fetchColumn() && !in_array('TBD', $values)) {
                $values[] = 'TBD';
            }
            sort($values);
            return $values;
        } else {
            $values = ['1', '2', '3', '4', '5', '6', 'TBD', 'Revisit', 'Next'];
            sort($values);
            return $values;
        }
    }
    if ($term === 'ingredients') {
        return getDistinctIngredientNames($conn, $user, $filters_data);
    }
    $params = [];
    $where_clause = buildWhereClause($filters_data, $filters, $params, $user);
    $table = $filters[$term]['table'];
    $column = $filters[$term]['column'];
    $join = "LEFT JOIN (
                SELECT recipe_id, username, stars_out_of_3, last_date
                FROM user_ratings ur1
                WHERE last_date = (SELECT MAX(last_date) FROM user_ratings ur2 WHERE ur2.recipe_id = ur1.recipe_id AND ur2.username = ur1.username)
            ) ur ON r.ID = ur.recipe_id" . ($user !== 'All' ? " AND ur.username = :current_user" : "");
    if ($user !== 'All') {
        $params[':current_user'] = $user;
    }
    $needs_aggregate = ($user === 'All' && $table === 'ur');
    if ($needs_aggregate) {
        if ($term === 'last_date') {
            $column_expr = "MAX(ur.last_date)";
        } 
    } else {
        $column_expr = "$table.$column";
    }
    $agg = "$column_expr AS val";
    $not_null = "$column_expr IS NOT NULL AND $column_expr != ''";
    $base_where = "WHERE (COALESCE(r.Hide, 0) = 0 OR EXISTS (SELECT 1 FROM user_ratings ur3 WHERE ur3.recipe_id = r.ID AND ur3.username = r.Source))";
    $order_dir = ($term === 'last_date') ? 'DESC' : 'ASC';
    if ($needs_aggregate || ($user === 'All' && has_ur_filters($filters_data))) {
        $inner_sql = "SELECT $agg FROM recipes r $join $base_where";
        $inner_sql .= " GROUP BY r.ID";
        $having_parts = [];
        if ($where_clause !== '1=1') {
            $having_parts[] = "($where_clause)";
        }
        $having_parts[] = "($not_null)";
        if (!empty($having_parts)) {
            $inner_sql .= " HAVING " . implode(' AND ', $having_parts);
        }
        $sql = "SELECT DISTINCT val FROM ($inner_sql) sub ORDER BY val $order_dir";
    } else {
        $sql = "SELECT DISTINCT $agg FROM recipes r $join $base_where";
        if ($where_clause !== '1=1') {
            $sql .= " AND ($where_clause)";
        }
        $sql .= " AND $not_null";
        $sql .= " ORDER BY val $order_dir";
    }
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) $stmt->bindValue($key, $value);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

function getRandomRecipe($conn, $user, $filters_data) {
    $filters = getFilters();
    $params = [];
    $where_clause = buildWhereClause($filters_data, $filters, $params, $user);
    $join = "LEFT JOIN (
                SELECT recipe_id, username, stars_out_of_3, last_date
                FROM user_ratings ur1
                WHERE last_date = (SELECT MAX(last_date) FROM user_ratings ur2 WHERE ur2.recipe_id = ur1.recipe_id AND ur2.username = ur1.username)
            ) ur ON r.ID = ur.recipe_id" . ($user !== 'All' ? " AND ur.username = :current_user" : "");
    if ($user !== 'All') {
        $params[':current_user'] = $user;
    }
    $base_where = "WHERE (COALESCE(r.Hide, 0) = 0 OR EXISTS (SELECT 1 FROM user_ratings ur3 WHERE ur3.recipe_id = r.ID AND ur3.username = r.Source))";
    if ($user === 'All' && has_ur_filters($filters_data)) {
        $inner_sql = "SELECT r.Name, r.Source FROM recipes r $join $base_where GROUP BY r.ID HAVING ($where_clause)";
        $sql = "SELECT Name, Source FROM ($inner_sql) sub ORDER BY RAND() LIMIT 1";
    } else {
        $sql = "SELECT r.Name, r.Source FROM recipes r $join $base_where";
        if ($where_clause !== '1=1') {
            $sql .= " AND ($where_clause)";
        }
        $sql .= " ORDER BY RAND() LIMIT 1";
    }
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) $stmt->bindValue($key, $value);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function getNames($conn, $user, $filters_data) {
    $filters = getFilters();
    $params = [];
    $where_clause = buildWhereClause($filters_data, $filters, $params, $user);
    $join = "LEFT JOIN (
                SELECT recipe_id, username, stars_out_of_3, last_date
                FROM user_ratings ur1
                WHERE last_date = (SELECT MAX(last_date) FROM user_ratings ur2 WHERE ur2.recipe_id = ur1.recipe_id AND ur2.username = ur1.username)
            ) ur ON r.ID = ur.recipe_id" . ($user !== 'All' ? " AND ur.username = :current_user" : "");
    if ($user !== 'All') {
        $params[':current_user'] = $user;
    }
    $base_where = "WHERE (COALESCE(r.Hide, 0) = 0 OR EXISTS (SELECT 1 FROM user_ratings ur3 WHERE ur3.recipe_id = r.ID AND ur3.username = r.Source))";
    if ($user === 'All' && has_ur_filters($filters_data)) {
        $inner_sql = "SELECT r.Name FROM recipes r $join $base_where GROUP BY r.ID HAVING ($where_clause)";
        $sql = "SELECT DISTINCT Name FROM ($inner_sql) sub ORDER BY Name";
    } else {
        $sql = "SELECT DISTINCT r.Name FROM recipes r $join $base_where";
        if ($where_clause !== '1=1') {
            $sql .= " AND ($where_clause)";
        }
        $sql .= " ORDER BY r.Name";
    }
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) $stmt->bindValue($key, $value);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

function getSources($conn, $name, $user, $filters_data) {
    if (!$name) return [];
    $filters = getFilters();
    $params = [':name' => $name];
    $where_clause = buildWhereClause($filters_data, $filters, $params, $user);
    $join = "LEFT JOIN (
                SELECT recipe_id, username, stars_out_of_3, last_date
                FROM user_ratings ur1
                WHERE last_date = (SELECT MAX(last_date) FROM user_ratings ur2 WHERE ur2.recipe_id = ur1.recipe_id AND ur2.username = ur1.username)
            ) ur ON r.ID = ur.recipe_id" . ($user !== 'All' ? " AND ur.username = :current_user" : "");
    if ($user !== 'All') {
        $params[':current_user'] = $user;
    }
    $base_where = "WHERE r.Name = :name AND (COALESCE(r.Hide, 0) = 0 OR EXISTS (SELECT 1 FROM user_ratings ur3 WHERE ur3.recipe_id = r.ID AND ur3.username = r.Source))";
    if ($user === 'All' && has_ur_filters($filters_data)) {
        $inner_sql = "SELECT r.Source FROM recipes r $join $base_where GROUP BY r.ID HAVING ($where_clause)";
        $sql = "SELECT DISTINCT Source FROM ($inner_sql) sub";
    } else {
        $sql = "SELECT DISTINCT r.Source FROM recipes r $join $base_where";
        if ($where_clause !== '1=1') {
            $sql .= " AND ($where_clause)";
        }
    }
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) $stmt->bindValue($key, $value);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

function getRecipeDetails($conn, $name, $source, $user) {
    if (!$name || !$source) return [];
    $sql = "SELECT ID FROM recipes WHERE Name = :name AND Source = :source";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':source', $source);
    $stmt->execute();
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$recipe) return [];
    $recipe_id = $recipe['ID'];
    $join = "LEFT JOIN (
                SELECT recipe_id, username, stars_out_of_3, last_date
                FROM user_ratings ur1
                WHERE last_date = (SELECT MAX(last_date) FROM user_ratings ur2 WHERE ur2.recipe_id = ur1.recipe_id AND ur2.username = ur1.username)
            ) ur ON r.ID = ur.recipe_id" . ($user !== 'All' ? " AND ur.username = :current_user" : "");
    if ($user !== 'All') {
        $stars_select = "(SELECT stars_out_of_3 FROM user_ratings WHERE recipe_id = r.ID AND username = :current_user ORDER BY last_date DESC LIMIT 1) as stars_out_of_3";
        $last_date_select = "(SELECT last_date FROM user_ratings WHERE recipe_id = r.ID AND username = :current_user ORDER BY last_date DESC LIMIT 1) as last_date";
    } else {
        $stars_select = "
            CASE
                WHEN COUNT(ur.recipe_id) > 1 AND MAX(ur.stars_out_of_3) = MIN(ur.stars_out_of_3) AND MAX(ur.stars_out_of_3) NOT REGEXP '^[0-9]+(\.[0-9]+)?$' THEN MAX(ur.stars_out_of_3)
                ELSE AVG(CASE WHEN ur.stars_out_of_3 REGEXP '^[0-9]+(\.[0-9]+)?$' THEN CAST(ur.stars_out_of_3 AS DECIMAL(10,4)) ELSE NULL END)
            END as stars_out_of_3";
        $last_date_select = "MAX(ur.last_date) as last_date";
    }
    $sql = "SELECT r.*,
                   $stars_select,
                   $last_date_select
            FROM recipes r
            $join
            WHERE r.ID = :recipe_id
              AND (COALESCE(r.Hide, 0) = 0 OR EXISTS (SELECT 1 FROM user_ratings ur WHERE ur.recipe_id = r.ID AND ur.username = r.Source))";
    if ($user === 'All') {
        $sql .= " GROUP BY r.ID";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':recipe_id', $recipe_id);
    if ($user !== 'All') {
        $stmt->bindValue(':current_user', $user);
    }
    $stmt->execute();
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    // Parse ingredients and calculate new columns
    if (!empty($recipe)) {
        $ingredients_str = $recipe['Ingredients'] ?? '';
        $parsed = [];
        $total_volume = 0.0;
        $conversions = getUnitConversions($conn);
        $special_units = ['top', 'splash', 'rinse']; // Add more if needed
        $ingredient_list = array_map('trim', explode(';', $ingredients_str));
        foreach ($ingredient_list as $item) {
            if (!$item) continue;
            $parts = explode(':', $item, 2);
            $name = trim($parts[0]);
            if (count($parts) < 2) continue;
            $quantity_str = trim($parts[1]);
            $lowered = strtolower($quantity_str);
            $volume_oz = 0.0;
            $display_volume = $quantity_str;
            // Check for special units without number
            if (in_array($lowered, $special_units)) {
                $volume_oz = $conversions[$lowered] ?? 0.0;
            } else {
                // Primary match for decimal + unit (allow attached or spaced)
                if (preg_match('/^(\d+(?:\.\d+)?)(?:\s*([a-zA-Z]+))?$/', $lowered, $matches)) {
                    $amount = floatval($matches[1]);
                    $unit = isset($matches[2]) ? strtolower($matches[2]) : '';
                    $conversion = $conversions[$unit] ?? 1.0;
                    $volume_oz = $amount * $conversion;
                    $display_volume = ($unit === 'oz' || $unit === '') ? sprintf('%.2f', $amount) : $quantity_str;
                } else {
                    // Secondary match for integer + unit (e.g., "2 dashes")
                    if (preg_match('/^(\d+)\s*(\w+)$/', $lowered, $matches)) {
                        $amount = floatval($matches[1]);
                        $unit = strtolower($matches[2]);
                        // Singularize plural units
                        $singular = preg_replace('/(es|s)$/', '', $unit);
                        $conversion = $conversions[$singular] ?? $conversions[$unit] ?? 1.0;
                        $volume_oz = $amount * $conversion;
                        $display_volume = $quantity_str;
                    }
                }
            }
            // Query ingredients table
            $stmt_ing = $conn->prepare("SELECT ABV, Cost, Size_oz FROM ingredients WHERE Name = :name");
            $stmt_ing->bindValue(':name', $name);
            $stmt_ing->execute();
            $ing = $stmt_ing->fetch(PDO::FETCH_ASSOC);
            if ($ing) {
                $abv = $ing['ABV'] !== null ? floatval($ing['ABV']) : 0.0;
                $size_oz = floatval($ing['Size_oz']);
                $cost_per_oz = $size_oz > 0 ? floatval($ing['Cost']) / $size_oz : 0.0;
                $abv_disp = sprintf('%.2f%%', $abv*100);
                $cost_disp = sprintf('$%.2f', $cost_per_oz * $volume_oz);
            } else {
                $abv = 0.0;
                $cost_per_oz = 0.0;
                $abv_disp = 'NA';
                $cost_disp = 'NA';
            }
            $parsed[] = [
                'name' => $name,
                'quantity' => $display_volume,
                'volume_oz' => $volume_oz,
                'abv' => $abv_disp,
                'cost' => $cost_disp,
            ];
            $total_volume += $volume_oz;
        }
        // Calculate contributions
        $alcohol_contribs = [];
        $cost_contribs = [];
        $total_alcohol = 0.0;
        $total_cost = 0.0;
        foreach ($parsed as $i => $ing) {
            $abv_num = is_numeric(trim($ing['abv'], '%')) ? floatval(trim($ing['abv'], '%')) : 0.0;
            $alcohol_contrib = $ing['volume_oz'] * ($abv_num / 100.0);
            $total_alcohol += $alcohol_contrib;
            $alcohol_contribs[$i] = $alcohol_contrib;
            $cost_num = is_numeric(trim($ing['cost'], '$')) ? floatval(trim($ing['cost'], '$')) : 0.0;
            $total_cost += $cost_num;
            $cost_contribs[$i] = $cost_num;
        }
        // Check for non-alcoholic
        $is_non_alcoholic = ($total_alcohol == 0.0);
        if ($is_non_alcoholic) {
            $total_abv = 0.0;
        } else {
            $total_abv = $total_volume > 0 ? ($total_alcohol / $total_volume) * 100 : 0.0;
        }
        // Assign percentages
        $total_vol_percent_sum = 0.0;
        $total_abv_percent_sum = 0.0;
        $total_cost_percent_sum = 0.0;
        foreach ($parsed as $i => &$ing) {
            $vol_percent = $total_volume > 0 ? ($ing['volume_oz'] / $total_volume) * 100 : 0.0;
            $ing['vol_percent'] = sprintf('%.2f%%', $vol_percent);
            $total_vol_percent_sum += $vol_percent;
            $abv_percent = $is_non_alcoholic ? 0.0 : ($total_alcohol > 0 ? ($alcohol_contribs[$i] / $total_alcohol) * 100 : 0.0);
            $ing['abv_percent'] = sprintf('%.2f%%', $abv_percent);
            $total_abv_percent_sum += $abv_percent;
            $cost_percent = $total_cost > 0 ? ($cost_contribs[$i] / $total_cost) * 100 : 0.0;
            $ing['cost_percent'] = sprintf('%.2f%%', $cost_percent);
            $total_cost_percent_sum += $cost_percent;
        }
        // Calculate sums for totals
        $total_abv_disp = sprintf('%.2f%%', $total_abv);
        $total_cost_disp = sprintf('$%.2f', $total_cost);
        $recipe['parsed_ingredients'] = $parsed;
        $recipe['totals'] = [
            'volume_oz' => sprintf('%.2f', $total_volume),
            'vol_percent' => sprintf('%.2f%%', $total_vol_percent_sum),
            'abv' => $total_abv_disp,
            'abv_percent' => sprintf('%.2f%%', $total_abv_percent_sum),
            'cost' => $total_cost_disp,
            'cost_percent' => sprintf('%.2f%%', $total_cost_percent_sum),
        ];
    }
    return $recipe;
}

function saveRating($conn, $name, $source, $stars, $last_date, $username) {
    if (!$name || !$source || !$stars || !$last_date || !$username) {
        return ['success' => false, 'error' => 'Missing parameters'];
    }
    $sql = "SELECT ID, Name, Source FROM recipes WHERE Name = :name AND Source = :source";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':source', $source);
    $stmt->execute();
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$recipe) return ['success' => false, 'error' => 'Recipe not found'];
    $recipe_id = $recipe['ID'];
    $sql = "INSERT INTO user_ratings (recipe_id, name, source, username, stars_out_of_3, last_date)
            VALUES (:recipe_id, :name, :source, :username, :stars, :last_date)
            ON DUPLICATE KEY UPDATE stars_out_of_3 = :stars";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':recipe_id', $recipe_id);
    $stmt->bindValue(':name', $recipe['Name']);
    $stmt->bindValue(':source', $recipe['Source']);
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':stars', $stars);
    $stmt->bindValue(':last_date', date('Y-m-d', strtotime($last_date)));
    $stmt->execute();
    return ['success' => true];
}

function getUnitConversions($conn) {
    $sql = "SELECT unit_name, ounces FROM unit_conversions";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $conv = [];
    foreach ($rows as $r) {
        $conv[strtolower($r['unit_name'])] = floatval($r['ounces']);
    }
    return $conv;
}
?>

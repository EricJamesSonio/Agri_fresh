<?php

// Generic DB helper: execute a CREATE TABLE or any DDL wrapped with a key label
function createTable(mysqli $con, string $key, string $sql): void {
    // Ensure IF NOT EXISTS to avoid fatal on reruns
    if (stripos($sql, 'CREATE TABLE') !== false && stripos($sql, 'IF NOT EXISTS') === false) {
        $sql = preg_replace('/CREATE\s+TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', $sql, 1);
    }
    if (!$con->query($sql)) {
        throw new Exception("Failed to create table '{$key}': " . $con->error);
    }
    echo "[OK] table {$key} ensured\n";
}

// Execute a query with optional params (prepared). Returns mysqli_result|bool
function execQuery(mysqli $con, string $sql, array $params = []) {
    if (empty($params)) {
        return $con->query($sql);
    }
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $con->error);
    }
    [$types, $flat] = buildTypesAndParams($params);
    $stmt->bind_param($types, ...$flat);
    $stmt->execute();
    return $stmt->get_result() ?: true;
}

// Check if a record exists based on specific columns
function recordExists(mysqli $con, string $table, array $conditions): bool {
    $whereClauses = [];
    $params = [];
    
    foreach ($conditions as $col => $val) {
        if (is_null($val)) {
            $whereClauses[] = "`{$col}` IS NULL";
        } else {
            $whereClauses[] = "`{$col}` = ?";
            $params[] = $val;
        }
    }
    
    $whereClause = implode(' AND ', $whereClauses);
    $sql = "SELECT 1 FROM `{$table}` WHERE {$whereClause} LIMIT 1";
    
    $result = execQuery($con, $sql, $params);
    if ($result instanceof mysqli_result) {
        return $result->num_rows > 0;
    }
    return false;
}

// Insert a single row only if it doesn't exist (based on unique columns)
function insertIfNotExists(mysqli $con, string $table, array $data, array $uniqueColumns = []): bool {
    if (empty($uniqueColumns)) {
        // If no unique columns specified, use all columns for checking
        $uniqueColumns = array_keys($data);
    }
    
    // Build conditions array for checking existence
    $conditions = [];
    foreach ($uniqueColumns as $col) {
        if (array_key_exists($col, $data)) {
            $conditions[$col] = $data[$col];
        }
    }
    
    // Check if record exists
    if (recordExists($con, $table, $conditions)) {
        return false; // Record exists, skip insertion
    }
    
    // Insert the record
    $columns = '`' . implode('`, `', array_keys($data)) . '`';
    $placeholders = rtrim(str_repeat('?,', count($data)), ',');
    $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
    
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $con->error);
    }
    
    [$types, $flat] = buildTypesAndParams(array_values($data));
    $stmt->bind_param($types, ...$flat);
    $stmt->execute();
    
    return true; // Record was inserted
}

// Smart insert multiple rows: only inserts new records, skips existing ones
function insertDataSmart(mysqli $con, string $table, array $cols, array $rows, array $uniqueColumns = []): void {
    if (empty($rows)) return;
    
    $insertedCount = 0;
    $skippedCount = 0;
    
    foreach ($rows as $row) {
        if (!is_array($row) || count($row) !== count($cols)) {
            throw new InvalidArgumentException('Row values must match column count.');
        }
        
        // Build associative array for this row
        $data = array_combine($cols, $row);
        
        if (insertIfNotExists($con, $table, $data, $uniqueColumns)) {
            $insertedCount++;
        } else {
            $skippedCount++;
        }
    }
    
    echo "[OK] {$table}: inserted {$insertedCount} new row(s), skipped {$skippedCount} existing row(s)\n";
}

// Original insert function (renamed for backward compatibility)
function insertData(mysqli $con, string $table, array $cols, array $rows): void {
    if (empty($rows)) return;
    $columns = '`' . implode('`, `', $cols) . '`';
    $placeholders = '(' . rtrim(str_repeat('?,', count($cols)), ',') . ')';
    $sql = "INSERT INTO `{$table}` ({$columns}) VALUES {$placeholders}";
    $stmt = $con->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $con->error);
    }
    foreach ($rows as $row) {
        if (!is_array($row) || count($row) !== count($cols)) {
            throw new InvalidArgumentException('Row values must match column count.');
        }
        [$types, $flat] = buildTypesAndParams($row);
        $stmt->bind_param($types, ...$flat);
        $stmt->execute();
    }
    echo "[OK] inserted " . count($rows) . " row(s) into {$table}\n";
}

// Insert or update: if record exists (based on unique columns), update it; otherwise insert
function insertOrUpdate(mysqli $con, string $table, array $data, array $uniqueColumns, array $updateColumns = []): void {
    if (empty($updateColumns)) {
        // If no update columns specified, update all columns except unique ones
        $updateColumns = array_diff(array_keys($data), $uniqueColumns);
    }
    
    // Build conditions for checking existence
    $conditions = [];
    foreach ($uniqueColumns as $col) {
        if (array_key_exists($col, $data)) {
            $conditions[$col] = $data[$col];
        }
    }
    
    if (recordExists($con, $table, $conditions)) {
        // Record exists, update it
        $setClauses = [];
        $params = [];
        
        foreach ($updateColumns as $col) {
            if (array_key_exists($col, $data)) {
                $setClauses[] = "`{$col}` = ?";
                $params[] = $data[$col];
            }
        }
        
        $whereClauses = [];
        foreach ($conditions as $col => $val) {
            if (is_null($val)) {
                $whereClauses[] = "`{$col}` IS NULL";
            } else {
                $whereClauses[] = "`{$col}` = ?";
                $params[] = $val;
            }
        }
        
        $setClause = implode(', ', $setClauses);
        $whereClause = implode(' AND ', $whereClauses);
        $sql = "UPDATE `{$table}` SET {$setClause} WHERE {$whereClause}";
        
        execQuery($con, $sql, $params);
        echo "[OK] updated record in {$table}\n";
    } else {
        // Record doesn't exist, insert it
        insertIfNotExists($con, $table, $data);
        echo "[OK] inserted new record in {$table}\n";
    }
}

// Batch insert or update
function insertOrUpdateBatch(mysqli $con, string $table, array $cols, array $rows, array $uniqueColumns, array $updateColumns = []): void {
    if (empty($rows)) return;
    
    $insertedCount = 0;
    $updatedCount = 0;
    
    foreach ($rows as $row) {
        if (!is_array($row) || count($row) !== count($cols)) {
            throw new InvalidArgumentException('Row values must match column count.');
        }
        
        $data = array_combine($cols, $row);
        
        // Build conditions for checking existence
        $conditions = [];
        foreach ($uniqueColumns as $col) {
            if (array_key_exists($col, $data)) {
                $conditions[$col] = $data[$col];
            }
        }
        
        if (recordExists($con, $table, $conditions)) {
            // Update existing record
            if (empty($updateColumns)) {
                $updateColumns = array_diff($cols, $uniqueColumns);
            }
            
            $setClauses = [];
            $params = [];
            
            foreach ($updateColumns as $col) {
                if (array_key_exists($col, $data)) {
                    $setClauses[] = "`{$col}` = ?";
                    $params[] = $data[$col];
                }
            }
            
            $whereClauses = [];
            foreach ($conditions as $col => $val) {
                if (is_null($val)) {
                    $whereClauses[] = "`{$col}` IS NULL";
                } else {
                    $whereClauses[] = "`{$col}` = ?";
                    $params[] = $val;
                }
            }
            
            if (!empty($setClauses)) {
                $setClause = implode(', ', $setClauses);
                $whereClause = implode(' AND ', $whereClauses);
                $sql = "UPDATE `{$table}` SET {$setClause} WHERE {$whereClause}";
                
                execQuery($con, $sql, $params);
                $updatedCount++;
            }
        } else {
            // Insert new record
            insertIfNotExists($con, $table, $data);
            $insertedCount++;
        }
    }
    
    echo "[OK] {$table}: inserted {$insertedCount} new row(s), updated {$updatedCount} existing row(s)\n";
}

// Get id by arbitrary column
function getIdBy(mysqli $con, string $table, string $col, $val, string $idCol = 'id') {
    $sql = "SELECT `{$idCol}` AS id FROM `{$table}` WHERE `{$col}` = ? LIMIT 1";
    $res = execQuery($con, $sql, [$val]);
    if ($res instanceof mysqli_result) {
        $row = $res->fetch_assoc();
        return $row['id'] ?? null;
    }
    return null;
}

// Convenience for common patterns: tries `name` then `category_name`
function getIdByName(mysqli $con, string $table, $name, string $idCol = 'id') {
    $id = getIdBy($con, $table, 'name', $name, $idCol);
    if ($id) return $id;
    return getIdBy($con, $table, 'category_name', $name, $idCol);
}

// Clear all data from a table (useful for fresh seeding)
function clearTable(mysqli $con, string $table): void {
    $sql = "DELETE FROM `{$table}`";
    if (!$con->query($sql)) {
        throw new Exception("Failed to clear table '{$table}': " . $con->error);
    }
    echo "[OK] cleared all data from {$table}\n";
}

// Reset auto increment to 1
function resetAutoIncrement(mysqli $con, string $table): void {
    $sql = "ALTER TABLE `{$table}` AUTO_INCREMENT = 1";
    if (!$con->query($sql)) {
        throw new Exception("Failed to reset auto increment for '{$table}': " . $con->error);
    }
    echo "[OK] reset auto increment for {$table}\n";
}

// Helpers
function buildTypesAndParams(array $params): array {
    $types = '';
    $flat = [];
    foreach ($params as $p) {
        $flat[] = $p;
        if (is_int($p)) {
            $types .= 'i';
        } elseif (is_float($p)) {
            $types .= 'd';
        } elseif (is_null($p)) {
            // send as string, letting NULL be handled by proper default/null column
            $types .= 's';
        } elseif (is_bool($p)) {
            $types .= 'i';
        } else {
            $types .= 's';
        }
    }
    return [$types, $flat];
}


<?php
// Run from CLI: php run_db_fixes.php
// This script will check and fix common DB issues for Jobly_System.
// It uses your existing `app/config/database.php` configuration.

define('PREVENT_DIRECT_ACCESS', true);
chdir(dirname(__DIR__)); // ensure repo root when run from scripts/

require_once __DIR__ . '/../app/config/database.php';

if (! isset($database['main'])) {
    echo "Database configuration 'main' not found in app/config/database.php\n";
    exit(1);
}

$dbconf = $database['main'];
$host = $dbconf['hostname'];
$port = isset($dbconf['port']) ? $dbconf['port'] : 3306;
$user = $dbconf['username'];
$pass = $dbconf['password'];
$dbname = $dbconf['database'];
$charset = isset($dbconf['charset']) ? $dbconf['charset'] : 'utf8mb4';
$collation = isset($dbconf['collation']) ? $dbconf['collation'] : 'utf8mb4_unicode_ci';

if (empty($host) || empty($user) || empty($dbname)) {
    echo "Please ensure DB_HOST, DB_USERNAME and DB_DATABASE are set in your environment or .env.\n";
    exit(1);
}

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    echo "Failed to connect to database: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Connected to database: {$dbname}@{$host}:{$port}\n";

// 1) Find tables that are not utf8mb4_unicode_ci and convert them (target only 'reviews' by default)
$tablesToCheck = ['reviews'];
foreach ($tablesToCheck as $table) {
    $stmt = $pdo->prepare("SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
    $stmt->execute([$dbname, $table]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (! $row) {
        echo "Table {$table} not found, skipping.\n";
        continue;
    }
    $currentColl = $row['TABLE_COLLATION'];
    echo "Table {$table} current collation: " . ($currentColl ?: 'NULL') . "\n";
    if (stripos($currentColl, 'utf8mb4_unicode_ci') === false) {
        echo "Converting table {$table} to {$collation}...\n";
        try {
            $pdo->exec("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE {$collation}");
            echo "Converted {$table}.\n";
        } catch (Exception $e) {
            echo "Failed to convert {$table}: " . $e->getMessage() . "\n";
        }
    } else {
        echo "{$table} already uses {$collation}.\n";
    }
}

// 2) Add missing columns if not exists
$colsToEnsure = [
    ['table' => 'company_applications', 'column' => 'applied_at', 'sql' => "DATETIME NULL DEFAULT NULL"],
    ['table' => 'applicants', 'column' => 'last_login', 'sql' => "DATETIME NULL DEFAULT NULL"],
    ['table' => 'companies', 'column' => 'last_login', 'sql' => "DATETIME NULL DEFAULT NULL"],
];

foreach ($colsToEnsure as $c) {
    $table = $c['table'];
    $column = $c['column'];
    $sqlDef = $c['sql'];

    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$dbname, $table, $column]);
    $exists = (int) $stmt->fetchColumn();
    if ($exists) {
        echo "Column {$column} already exists on {$table}.\n";
        continue;
    }
    echo "Adding column {$column} to {$table}...\n";
    try {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$sqlDef}");
        echo "Added {$column} to {$table}.\n";
    } catch (Exception $e) {
        echo "Failed to add {$column} to {$table}: " . $e->getMessage() . "\n";
    }
}

// 3) Set connection collation for this session to avoid mixes
try {
    $pdo->exec("SET NAMES utf8mb4 COLLATE {$collation}");
    $pdo->exec("SET collation_connection = {$pdo->quote($collation)}");
    echo "Set session collation to {$collation}.\n";
} catch (Exception $e) {
    echo "Failed to set session collation: " . $e->getMessage() . "\n";
}

echo "Done. Review output above; check your app and re-run failing flows.\n";

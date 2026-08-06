<?php
require_once 'config.php';

try {
    $queries = [
        "ALTER TABLE products ADD COLUMN msrp DECIMAL(10,2) DEFAULT 0.00;",
        "ALTER TABLE products ADD COLUMN condition_grade VARCHAR(50) DEFAULT '';",
        "ALTER TABLE products ADD COLUMN mileage VARCHAR(50) DEFAULT '';",
        "ALTER TABLE products ADD COLUMN warranty_status VARCHAR(100) DEFAULT '';",
        "ALTER TABLE products ADD COLUMN inspection_status VARCHAR(100) DEFAULT '';",
        "ALTER TABLE products ADD COLUMN thumbnail_url VARCHAR(255) DEFAULT '';"
    ];

    foreach ($queries as $query) {
        try {
            $pdo->exec($query);
            echo "Successfully executed: $query <br>";
        } catch (PDOException $e) {
            echo "Skipped (or error): " . $e->getMessage() . " <br>";
        }
    }
    echo "Migration complete!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

#!/usr/bin/env php
<?php
/**
 * OSPOS CSV Import CLI Script (CI4)
 * Usage: php bin/import_items.php /path/to/csv/folder
 */

// Load CI4 bootstrap
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../app/Config/Paths.php';
require __DIR__.'/../vendor/codeigniter4/framework/system/bootstrap.php';

$folder = $argv[1] ?? null;
if(!$folder || !is_dir($folder)) die("❌ Invalid folder\n");

$importModel = new \App\Models\ImportModel();

$files = glob($folder.'/*.csv');
if (empty($files)) die("❌ No CSV files found in the folder\n");

foreach($files as $file){
    echo "Processing: $file\n";
    $report = $importModel->importItemsFromCSV($file);
    print_r($report);
    @rename($file, $folder.'/processed/'.basename($file));
}

echo "✅ Import completed!\n";
?>


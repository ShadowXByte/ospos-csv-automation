<?php
/**
 * OSPOS Bulk CSV Import - Automated Installer
 * Detects OSPOS, copies files, and modifies controllers automatically
 * 
 * Usage:
 *   php install.php                    (auto-detect OSPOS)
 *   php install.php /path/to/ospos     (use custom path)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class BulkImportInstaller
{
    private $osposPath;
    private $errors = [];
    private $warnings = [];
    private $baseDir;

    public function __construct()
    {
        $this->baseDir = __DIR__;
        $this->displayBanner();
    }

    /**
     * Display welcome banner
     */
    private function displayBanner()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║      OSPOS Bulk CSV Import - Automated Installer              ║\n";
        echo "║                        Version 1.0                             ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    /**
     * Main installation process
     */
    public function run($customPath = null)
    {
        echo "[1/5] Detecting OSPOS installation...\n";
        if (!$this->findOSPOS($customPath)) {
            echo "❌ Could not find OSPOS. Please check the path and try again.\n\n";
            return false;
        }
        echo "    ✓ Found OSPOS at: {$this->osposPath}\n\n";

        echo "[2/5] Validating OSPOS installation...\n";
        if (!$this->validateOSPOS()) {
            echo "❌ OSPOS validation failed.\n\n";
            return false;
        }
        echo "    ✓ OSPOS structure is valid\n\n";

        echo "[3/5] Copying files to OSPOS...\n";
        if (!$this->copyFiles()) {
            echo "❌ Failed to copy files.\n\n";
            return false;
        }
        echo "    ✓ All files copied successfully\n\n";

        echo "[4/5] Modifying controller files...\n";
        if (!$this->modifyControllers()) {
            echo "❌ Failed to modify controllers.\n\n";
            return false;
        }
        echo "    ✓ Controllers modified with bulk import methods\n\n";

        echo "[5/5] Verifying installation...\n";
        if (!$this->verifyInstallation()) {
            echo "⚠️  Installation completed with warnings.\n\n";
        } else {
            echo "    ✓ Installation verified successfully\n\n";
        }

        $this->displaySuccess();
        return true;
    }

    /**
     * Find OSPOS installation path
     */
    private function findOSPOS($customPath = null)
    {
        // Try custom path first
        if ($customPath && $this->isValidOSPOS($customPath)) {
            $this->osposPath = rtrim($customPath, '/\\');
            return true;
        }

        // Try auto-detect paths
        $commonPaths = [
            getcwd(),
            dirname(getcwd()),
            '/var/www/ospos',
            '/var/www/html/ospos',
            '/home/ospos',
        ];

        foreach ($commonPaths as $path) {
            if (empty($path)) continue;
            if ($this->isValidOSPOS($path)) {
                $this->osposPath = rtrim($path, '/\\');
                return true;
            }
        }

        // Ask user for path
        return $this->askForPath();
    }

    /**
     * Ask user for OSPOS path
     */
    private function askForPath()
    {
        echo "Could not auto-detect OSPOS installation.\n";
        echo "Enter the path to your OSPOS root directory (containing 'app' folder):\n";
        echo "> ";
        
        $userPath = trim(fgets(STDIN));
        
        if ($this->isValidOSPOS($userPath)) {
            $this->osposPath = rtrim($userPath, '/\\');
            return true;
        }

        echo "❌ Invalid path. Please ensure it contains the 'app' directory.\n";
        return false;
    }

    /**
     * Check if path contains valid OSPOS installation
     */
    private function isValidOSPOS($path)
    {
        if (!is_dir($path)) return false;

        $requiredDirs = [
            'app/Controllers',
            'app/Views',
            'app/Libraries',
            'app/Models',
            'public'
        ];

        foreach ($requiredDirs as $dir) {
            if (!is_dir($path . '/' . $dir)) {
                return false;
            }
        }

        // Check for Items controller (exists in OSPOS 3.4.1+)
        return file_exists($path . '/app/Controllers/Items.php');
    }

    /**
     * Validate OSPOS is writable
     */
    private function validateOSPOS()
    {
        $checkDirs = [
            'app/Libraries',
            'app/Views/items',
            'app/Views/customers',
            'app/Controllers'
        ];

        foreach ($checkDirs as $dir) {
            $fullPath = $this->osposPath . '/' . $dir;
            if (!is_writable($fullPath)) {
                $this->warnings[] = "Directory not writable: $dir";
            }
        }

        return true;
    }

    /**
     * Copy all files to OSPOS
     */
    private function copyFiles()
    {
        $files = [
            'files/app/libraries/BulkImport.php' => 'app/Libraries/BulkImport.php',
            'files/app/views/items/form_csv_import.php' => 'app/Views/items/form_csv_import.php',
            'files/app/views/customers/form_csv_import.php' => 'app/Views/customers/form_csv_import.php',
            'files/app/language/en/BulkImport.php' => 'app/Language/en/BulkImport.php'
        ];

        foreach ($files as $source => $destination) {
            $sourceFile = $this->baseDir . '/' . $source;
            $destFile = $this->osposPath . '/' . $destination;
            $destDir = dirname($destFile);

            echo "    Copying $destination... ";

            if (!file_exists($sourceFile)) {
                echo "SKIPPED (source not found)\n";
                continue;
            }

            // Create parent directory if needed
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0755, true);
            }

            if (@copy($sourceFile, $destFile)) {
                @chmod($destFile, 0644);
                echo "✓\n";
            } else {
                echo "FAILED\n";
                $this->errors[] = "Failed to copy: $destination";
                return false;
            }
        }

        return true;
    }

    /**
     * Modify controller files to add bulk import method
     */
    private function modifyControllers()
    {
        $controllers = [
            'Items' => 'app/Controllers/Items.php',
            'Customers' => 'app/Controllers/Customers.php'
        ];

        foreach ($controllers as $name => $path) {
            echo "    Modifying $name.php... ";
            if ($this->modifyController($name, $path)) {
                echo "✓\n";
            } else {
                echo "FAILED\n";
                return false;
            }
        }

        return true;
    }

    /**
     * Add bulk import code to controller
     */
    private function modifyController($name, $relativePath)
    {
        $filePath = $this->osposPath . '/' . $relativePath;

        if (!file_exists($filePath)) {
            $this->warnings[] = "Controller file not found: $relativePath";
            return true;
        }

        $content = file_get_contents($filePath);

        // Check if already modified
        if (strpos($content, 'postImportBulkCsvFiles') !== false) {
            $this->warnings[] = "Controller already modified: $name";
            return true;
        }

        // Add use statement if missing
        if (strpos($content, 'use App\\Libraries\\BulkImport') === false) {
            $pattern = '/^(<\?php\s*namespace\s+[^;]+;)/m';
            $replacement = "$1\nuse App\\Libraries\\BulkImport;";
            $content = preg_replace($pattern, $replacement, $content, 1);
        }

        // Add the method before closing brace
        $method = $this->getMethodCode();
        $content = rtrim($content);

        if (substr($content, -1) === '}') {
            $content = substr($content, 0, -1) . "\n" . $method . "\n}\n";
        } else {
            $content .= "\n" . $method . "\n}\n";
        }

        return @file_put_contents($filePath, $content) !== false;
    }

    /**
     * Get the bulk import method code
     */
    private function getMethodCode()
    {
        return <<<'PHP'

    /**
     * Bulk import items/customers from multiple CSV files (ENHANCEMENT)
     * @return void
     * @noinspection PhpUnused
     */
    public function postImportBulkCsvFiles(): void
    {
        if (empty($_FILES['file_paths']) || $_FILES['file_paths']['error'][0] === UPLOAD_ERR_NO_FILE) {
            echo json_encode(['success' => false, 'message' => lang('Items.csv_import_failed')]);
            return;
        }

        try {
            $bulkImport = new BulkImport();
            $uploadedFiles = [];

            for ($i = 0; $i < count($_FILES['file_paths']['name']); $i++) {
                if ($_FILES['file_paths']['error'][$i] === UPLOAD_ERR_OK) {
                    $uploadedFiles[] = (object)[
                        'tmp_name' => $_FILES['file_paths']['tmp_name'][$i],
                        'name' => $_FILES['file_paths']['name'][$i],
                        'error' => $_FILES['file_paths']['error'][$i]
                    ];
                }
            }

            if (empty($uploadedFiles)) {
                echo json_encode(['success' => false, 'message' => lang('Items.csv_import_failed')]);
                return;
            }

            $importCallback = function($csvPath) {
                if (!file_exists($csvPath)) {
                    return ['success' => false, 'message' => 'File not found'];
                }

                $_FILES['file_path'] = [
                    'name' => basename($csvPath),
                    'tmp_name' => $csvPath,
                    'error' => UPLOAD_ERR_OK
                ];

                ob_start();
                $this->postImportCsvFile();
                $output = ob_get_clean();
                return json_decode($output, true) ?? ['success' => false];
            };

            $results = $bulkImport->processMultipleUploads($uploadedFiles, $importCallback);
            echo json_encode($results);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
PHP;
    }

    /**
     * Verify installation completed successfully
     */
    private function verifyInstallation()
    {
        $filesToCheck = [
            $this->osposPath . '/app/Libraries/BulkImport.php',
            $this->osposPath . '/app/Views/items/form_csv_import.php',
            $this->osposPath . '/app/Views/customers/form_csv_import.php'
        ];

        $allExist = true;
        foreach ($filesToCheck as $file) {
            if (!file_exists($file)) {
                $this->warnings[] = "Expected file not found: $file";
                $allExist = false;
            }
        }

        return $allExist;
    }

    /**
     * Display success message
     */
    private function displaySuccess()
    {
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║           ✓ INSTALLATION SUCCESSFUL!                          ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n";
        echo "\n";

        echo "📋 INSTALLATION SUMMARY:\n";
        echo "   OSPOS Directory: {$this->osposPath}\n";
        echo "   Files Copied: 4\n";
        echo "   Controllers Modified: 2 (Items.php, Customers.php)\n";
        echo "\n";

        echo "🎯 NEXT STEPS:\n";
        echo "   1. Clear browser cache (Ctrl+Shift+Delete)\n";
        echo "   2. Log into OSPOS\n";
        echo "   3. Go to Items → Import Items button\n";
        echo "   4. You should see 'Single File' and 'Bulk Import' tabs\n";
        echo "   5. Try it: Select multiple CSV files and import all at once!\n";
        echo "\n";

        echo "📚 DOCUMENTATION:\n";
        echo "   • README.md ...................... Feature overview\n";
        echo "   • BULK_IMPORT_QUICK_START.md ... Troubleshooting\n";
        echo "   • BULK_IMPORT_GUIDE.md ......... Technical details\n";
        echo "\n";

        if (!empty($this->warnings)) {
            echo "⚠️  WARNINGS:\n";
            foreach ($this->warnings as $w) {
                echo "   • $w\n";
            }
            echo "\n";
        }

        echo "✅ Ready to use! Your OSPOS now has bulk CSV import.\n\n";
    }
}

// Run installer
if (php_sapi_name() !== 'cli') {
    die("This installer must be run from command line.\nUsage: php install.php [/path/to/ospos]\n");
}

$installer = new BulkImportInstaller();
$customPath = $argv[1] ?? null;
$success = $installer->run($customPath);

exit($success ? 0 : 1);
?>



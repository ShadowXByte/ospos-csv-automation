<?php

namespace App\Libraries;

use Exception;

/**
 * BulkImport Library
 * Handles bulk CSV file imports by processing multiple files in sequence
 * and aggregating results
 */
class BulkImport
{
    private array $importResults = [];
    private int $totalFiles = 0;
    private int $processedFiles = 0;
    private array $fileErrors = [];

    /**
     * Process multiple CSV files from a directory
     *
     * @param string $folderPath - Directory containing CSV files
     * @param callable $importCallback - Callback function to handle individual file import
     * @param array $options - Options: ['recursive' => false, 'pattern' => '*.csv', 'move_processed' => true]
     * @return array Results with aggregated statistics
     */
    public function processFolderImport(string $folderPath, callable $importCallback, array $options = []): array
    {
        $options = array_merge([
            'recursive'       => false,
            'pattern'         => '*.csv',
            'move_processed'  => true,
            'processed_dir'   => 'imported_'
        ], $options);

        // Validate folder
        if (!is_dir($folderPath)) {
            throw new Exception("Folder not found: $folderPath");
        }

        // Get CSV files
        $pattern = rtrim($folderPath, '/') . '/' . $options['pattern'];
        $csvFiles = glob($pattern, GLOB_BRACE);

        if (empty($csvFiles)) {
            return $this->buildEmptyResult("No CSV files found in $folderPath");
        }

        $this->totalFiles = count($csvFiles);
        $this->importResults = [];
        $this->fileErrors = [];

        // Process each file
        foreach ($csvFiles as $filePath) {
            $fileName = basename($filePath);
            $this->processedFiles++;

            try {
                // Call the import callback with file path
                $result = call_user_func($importCallback, $filePath);

                // Store result with file info
                $this->importResults[] = [
                    'file'      => $fileName,
                    'path'      => $filePath,
                    'status'    => $result['success'] ?? false,
                    'message'   => $result['message'] ?? 'Import completed',
                    'result'    => $result,
                    'processed' => true
                ];

                // Move processed file if option enabled
                if ($options['move_processed'] && file_exists($filePath)) {
                    $this->moveProcessedFile($filePath, $options['processed_dir']);
                }

            } catch (Exception $e) {
                $this->fileErrors[$fileName] = $e->getMessage();
                $this->importResults[] = [
                    'file'      => $fileName,
                    'path'      => $filePath,
                    'status'    => false,
                    'message'   => 'Error: ' . $e->getMessage(),
                    'result'    => [],
                    'processed' => false,
                    'error'     => $e->getMessage()
                ];
            }
        }

        return $this->buildAggregatedResult();
    }

    /**
     * Process multiple uploaded files
     *
     * @param array $uploadedFiles - Array of uploaded file objects from CodeIgniter
     * @param callable $importCallback - Callback to handle each file
     * @return array Aggregated results
     */
    public function processMultipleUploads(array $uploadedFiles, callable $importCallback): array
    {
        $this->totalFiles = count($uploadedFiles);
        $this->importResults = [];
        $this->fileErrors = [];
        $this->processedFiles = 0;

        foreach ($uploadedFiles as $file) {
            $fileName = $file->getClientName();
            $this->processedFiles++;

            try {
                if (!$file->isValid()) {
                    throw new Exception("Invalid file: " . $file->getErrorString());
                }

                // Call import callback
                $result = call_user_func($importCallback, $file->getTempName());

                $this->importResults[] = [
                    'file'      => $fileName,
                    'status'    => $result['success'] ?? false,
                    'message'   => $result['message'] ?? 'Import completed',
                    'result'    => $result,
                    'processed' => true
                ];

            } catch (Exception $e) {
                $this->fileErrors[$fileName] = $e->getMessage();
                $this->importResults[] = [
                    'file'      => $fileName,
                    'status'    => false,
                    'message'   => 'Error: ' . $e->getMessage(),
                    'result'    => [],
                    'processed' => false,
                    'error'     => $e->getMessage()
                ];
            }
        }

        return $this->buildAggregatedResult();
    }

    /**
     * Build aggregated results from all processed files
     */
    private function buildAggregatedResult(): array
    {
        $totalSuccess = 0;
        $totalFailed = 0;
        $successfulFiles = 0;
        $failedFiles = 0;

        foreach ($this->importResults as $result) {
            if ($result['processed']) {
                if ($result['status']) {
                    $successfulFiles++;
                    // Sum statistics if available
                    if (isset($result['result']['success'])) {
                        $totalSuccess += $result['result']['success'] ?? 0;
                    }
                } else {
                    $failedFiles++;
                    if (isset($result['result']['failed'])) {
                        $totalFailed += $result['result']['failed'] ?? 0;
                    }
                }
            } else {
                $failedFiles++;
            }
        }

        return [
            'success'              => count($this->fileErrors) === 0,
            'total_files'          => $this->totalFiles,
            'processed_files'      => $this->processedFiles,
            'successful_files'     => $successfulFiles,
            'failed_files'         => $failedFiles,
            'total_items_imported' => $totalSuccess,
            'total_items_failed'   => $totalFailed,
            'files'                => $this->importResults,
            'errors'               => $this->fileErrors,
            'summary'              => $this->buildSummaryMessage($successfulFiles, $failedFiles, $totalSuccess, $totalFailed)
        ];
    }

    /**
     * Build summary message
     */
    private function buildSummaryMessage(int $successful, int $failed, int $itemsSuccess, int $itemsFailed): string
    {
        $parts = [];

        if ($successful > 0) {
            $parts[] = "✓ {$successful} file(s) imported successfully";
            if ($itemsSuccess > 0) {
                $parts[] = "({$itemsSuccess} items)";
            }
        }

        if ($failed > 0) {
            $parts[] = "✗ {$failed} file(s) failed";
            if ($itemsFailed > 0) {
                $parts[] = "({$itemsFailed} items)";
            }
        }

        return implode(' ', $parts) ?: 'No files processed';
    }

    /**
     * Move processed file to archive directory
     */
    private function moveProcessedFile(string $filePath, string $newDir): void
    {
        $dir = dirname($filePath);
        $fileName = basename($filePath);
        $timestamp = date('Y-m-d_H-i-s');
        $processedDir = "{$dir}/{$newDir}{$timestamp}";

        // Create directory if it doesn't exist
        if (!is_dir($processedDir)) {
            mkdir($processedDir, 0755, true);
        }

        $newPath = "{$processedDir}/{$fileName}";
        @rename($filePath, $newPath);
    }

    /**
     * Build empty result
     */
    private function buildEmptyResult(string $message): array
    {
        return [
            'success'              => false,
            'total_files'          => 0,
            'processed_files'      => 0,
            'successful_files'     => 0,
            'failed_files'         => 0,
            'total_items_imported' => 0,
            'total_items_failed'   => 0,
            'files'                => [],
            'errors'               => [],
            'summary'              => $message
        ];
    }

    /**
     * Get import results
     */
    public function getResults(): array
    {
        return $this->importResults;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->fileErrors;
    }

    /**
     * Get processed files count
     */
    public function getProcessedCount(): int
    {
        return $this->processedFiles;
    }
}
?>

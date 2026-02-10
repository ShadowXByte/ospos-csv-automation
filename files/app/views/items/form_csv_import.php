<?php
/**
 * OSPOS Items CSV Import Form - Enhanced with Bulk Import
 * Replace: app/Views/items/form_csv_import.php
 * 
 * Features:
 * - Single file upload (default OSPOS behavior)
 * - Bulk folder import (new feature)
 * - Progress tracking
 * - Error reporting
 */
?>

<style>
    .csv-import-container {
        max-width: 600px;
        margin: 20px 0;
    }

    .import-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e0e0e0;
    }

    .import-tabs button {
        padding: 10px 20px;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        color: #666;
    }

    .import-tabs button.active {
        color: #2196F3;
        border-bottom-color: #2196F3;
    }

    .import-tabs button:hover {
        color: #2196F3;
    }

    .tab-content {
        display: none;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #f9f9f9;
    }

    .tab-content.active {
        display: block;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
    }

    .form-group input[type="file"],
    .form-group input[type="text"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-family: monospace;
    }

    .form-group input:focus {
        outline: none;
        border-color: #2196F3;
        box-shadow: 0 0 5px rgba(33, 150, 243, 0.3);
    }

    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        flex: 1;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: #2196F3;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0b7dda;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .info-box {
        padding: 12px;
        margin: 15px 0;
        border-left: 4px solid #2196F3;
        background-color: #e3f2fd;
        color: #1565c0;
        border-radius: 4px;
        font-size: 13px;
    }

    .info-box.warning {
        border-left-color: #ff9800;
        background-color: #fff3e0;
        color: #e65100;
    }

    .file-list {
        margin-top: 15px;
        padding: 10px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-height: 200px;
        overflow-y: auto;
    }

    .file-item {
        padding: 8px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .file-item:last-child {
        border-bottom: none;
    }

    .file-icon {
        color: #2196F3;
        margin-right: 8px;
    }

    .progress-bar {
        width: 100%;
        height: 24px;
        background-color: #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 10px;
    }

    .progress-fill {
        height: 100%;
        background-color: #4caf50;
        width: 0%;
        transition: width 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        font-weight: bold;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-success {
        background-color: #c8e6c9;
        color: #2e7d32;
    }

    .status-error {
        background-color: #ffcdd2;
        color: #c62828;
    }

    .status-processing {
        background-color: #fff9c4;
        color: #f57f17;
    }

    .download-template {
        margin-top: 10px;
        padding: 10px;
        background-color: #f5f5f5;
        border-radius: 4px;
        text-align: center;
    }

    .download-template a {
        color: #2196F3;
        text-decoration: none;
        font-weight: 600;
    }

    .download-template a:hover {
        text-decoration: underline;
    }
</style>

<div class="csv-import-container">
    <h3>Import Items from CSV</h3>

    <div class="import-tabs">
        <button type="button" class="import-tab-btn active" data-tab="single"
            onclick="(function(btn){var tab=btn.getAttribute('data-tab');document.querySelectorAll('.import-tab-btn').forEach(function(b){b.classList.remove('active');});document.querySelectorAll('.tab-content').forEach(function(c){c.classList.remove('active');});btn.classList.add('active');var target=document.getElementById(tab+'-tab');if(target){target.classList.add('active');}})(this);">
            📁 Single File
        </button>
        <button type="button" class="import-tab-btn" data-tab="bulk"
            onclick="(function(btn){var tab=btn.getAttribute('data-tab');document.querySelectorAll('.import-tab-btn').forEach(function(b){b.classList.remove('active');});document.querySelectorAll('.tab-content').forEach(function(c){c.classList.remove('active');});btn.classList.add('active');var target=document.getElementById(tab+'-tab');if(target){target.classList.add('active');}})(this);">
            📦 Bulk Import
        </button>
    </div>

    <!-- Single File Import Tab -->
    <div id="single-tab" class="tab-content active">
        <div class="info-box">
            📋 Upload a single CSV file to import or update items. The system will process each row and report any errors.
        </div>

        <form id="single-import-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="single-file">Select CSV File:</label>
                <input type="file" id="single-file" name="file_path" accept=".csv" required>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Import File</button>
            </div>
        </form>

        <div class="download-template">
            <a href="<?= base_url('items/getGenerateCsvFile') ?>" download>
                ⬇️ Download CSV Template
            </a>
        </div>
    </div>

    <!-- Bulk Import Tab -->
    <div id="bulk-tab" class="tab-content">
        <div class="info-box">
            📦 Import multiple CSV files at once. Select files below or enter a folder path where CSV files are located.
        </div>

        <form id="bulk-import-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bulk-files">Select Multiple CSV Files:</label>
                <input type="file" id="bulk-files" name="file_paths[]" accept=".csv" multiple required>
                <div class="file-list" id="file-list" style="display:none;"></div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="auto-move-check" name="auto_move" checked>
                    Automatically move imported files to archive folder
                </label>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Import All Files</button>
                <button type="reset" class="btn btn-secondary">Clear Selection</button>
            </div>
        </form>

        <div class="download-template">
            <a href="<?= base_url('items/getGenerateCsvFile') ?>" download>
                ⬇️ Download CSV Template
            </a>
        </div>
    </div>

    <!-- Results Section -->
    <div id="results-container" style="display:none; margin-top: 25px;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.import-tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    // Tab switching - direct button click handlers
    try {
        console.log('[Import] Initializing tabs. Found ' + tabButtons.length + ' buttons, ' + tabContents.length + ' contents');
        
        tabButtons.forEach((btn, idx) => {
            const tabName = btn.getAttribute('data-tab');
            console.log('[Tab-' + idx + '] Button for "' + tabName + '" registered');
            
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('[Import] TAB CLICKED:', tabName);
                
                // Remove active from all buttons and contents
                tabButtons.forEach(b => {
                    b.classList.remove('active');
                    console.log('[Tab] Removed active from button:', b.getAttribute('data-tab'));
                });
                tabContents.forEach(c => {
                    c.classList.remove('active');
                    console.log('[Tab] Removed active from content:', c.id);
                });

                // Add active to clicked button and corresponding content
                btn.classList.add('active');
                console.log('[Tab] Added active to button:', tabName);
                
                const contentElem = document.getElementById(tabName + '-tab');
                if (contentElem) {
                    contentElem.classList.add('active');
                    console.log('[Tab] Added active to content:', tabName + '-tab');
                } else {
                    console.warn('[Tab] Content element NOT FOUND:', tabName + '-tab');
                }
            });
        });
    } catch (err) {
        console.error('[Import] Tab initialization error:', err);
    }

    // File list display for bulk import
    const bulkFilesInput = document.getElementById('bulk-files');
    const fileList = document.getElementById('file-list');

    bulkFilesInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        if (files.length > 0) {
            fileList.style.display = 'block';
            fileList.innerHTML = files.map((file, idx) => `
                <div class="file-item">
                    <span><span class="file-icon">📄</span>${file.name}</span>
                    <span>${(file.size / 1024).toFixed(2)} KB</span>
                </div>
            `).join('');
        } else {
            fileList.style.display = 'none';
            fileList.innerHTML = '';
        }
    });

    // Single file import
    document.getElementById('single-import-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);

        try {
            const response = await fetch('<?= base_url("items/postImportCsvFile") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();
            displayResult(result, 'single');
        } catch (error) {
            displayError('Failed to import file: ' + error.message);
        }
    });

    // Bulk import
    document.getElementById('bulk-import-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);

        try {
            const response = await fetch('<?= base_url("items/postImportBulkCsvFiles") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();
            displayBulkResults(result);
        } catch (error) {
            displayError('Failed to import files: ' + error.message);
        }
    });

    function displayResult(result, type) {
        const container = document.getElementById('results-container');
        const successClass = result.success ? 'status-success' : 'status-error';
        const icon = result.success ? '✓' : '✗';

        container.innerHTML = `
            <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px; background-color: ${result.success ? '#f1f8f5' : '#fff5f5'};">
                <div style="margin-bottom: 10px;">
                    <span class="status-badge ${successClass}">${icon} ${result.message}</span>
                </div>
            </div>
        `;
        container.style.display = 'block';
        window.scrollTo(0, container.offsetTop);
    }

    function displayBulkResults(result) {
        const container = document.getElementById('results-container');
        const successCount = result.successful_files || 0;
        const failCount = result.failed_files || 0;
        const totalFiles = result.total_files || 0;

        let html = `
            <div style="padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                <h4>${result.summary}</h4>
                
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${totalFiles > 0 ? (successCount / totalFiles * 100) : 0}%">
                        ${totalFiles > 0 ? Math.round(successCount / totalFiles * 100) : 0}%
                    </div>
                </div>

                <div style="margin-top: 15px; font-size: 13px;">
                    <p>📊 <strong>Files Processed:</strong> ${result.processed_files || 0} / ${totalFiles}</p>
                    <p>✓ <strong>Successful:</strong> ${successCount} | ✗ <strong>Failed:</strong> ${failCount}</p>
                    ${result.total_items_imported > 0 ? `<p>📦 <strong>Items Imported:</strong> ${result.total_items_imported}</p>` : ''}
                </div>
        `;

        if (result.files && result.files.length > 0) {
            html += '<h5 style="margin-top: 15px;">File Details:</h5><div class="file-list">';
            result.files.forEach(file => {
                const statusClass = file.status ? 'status-success' : 'status-error';
                const statusIcon = file.status ? '✓' : '✗';
                html += `
                    <div class="file-item">
                        <div>
                            <span class="status-badge ${statusClass}">${statusIcon} ${file.file}</span>
                            <div style="font-size: 12px; color: #666; margin-top: 4px;">${file.message}</div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        }

        if (result.errors && Object.keys(result.errors).length > 0) {
            html += '<div class="info-box warning" style="margin-top: 15px;"><strong>⚠️ Errors:</strong><br>';
            for (const [file, error] of Object.entries(result.errors)) {
                html += `${file}: ${error}<br>`;
            }
            html += '</div>';
        }

        html += '</div>';
        container.innerHTML = html;
        container.style.display = 'block';
        window.scrollTo(0, container.offsetTop);
    }

    function displayError(message) {
        const container = document.getElementById('results-container');
        container.innerHTML = `
            <div class="info-box warning">
                🚨 <strong>Error:</strong> ${message}
            </div>
        `;
        container.style.display = 'block';
    }
});
</script>

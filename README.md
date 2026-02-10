# OSPOS Bulk CSV Import Enhancement

A production-ready bulk CSV import system for OSPOS 3.4.1+ that enables users to import multiple CSV files simultaneously for Items and Customers while preserving all native OSPOS functionality.

---

## 📋 Overview

This enhancement adds a **Bulk Import** tab alongside the existing Single File import in both Items and Customers sections. Instead of importing one CSV at a time, users can now:

- ✅ Select multiple CSV files at once
- ✅ Import them all with a single click
- ✅ Monitor progress with a visual progress bar
- ✅ See detailed per-file results
- ✅ Keep all existing OSPOS features (Mailchimp sync, attributes, taxes, locations)

**Before:** Import 1 CSV → Repeat 10 times for 10 files  
**After:** Select 10 CSVs → Click once → Done! ⚡

---

## ⚙️ Requirements

- **OSPOS 3.4.1+** with CodeIgniter 4.4+
- **PHP 7.4+** (CodeIgniter 4 requirement)
- **Write permissions** on `/app/Views/` and `/app/Controllers/` directories
- **Web server** with standard PHP support

**Tested On:**
- OSPOS 3.4.1, CodeIgniter 4.6.0
- PHP 8.0, 8.1, 8.2
- Apache & Nginx (Windows XAMPP, Linux LAMP, Docker)

---

## 🚀 Installation

### One-Command Setup

```bash
php install.php
```

Or specify your OSPOS path:
```bash
php install.php "C:\xampp\htdocs\ospos"
```

**The installer automatically:**
1. Detects your OSPOS installation (or asks for path)
2. Validates OSPOS structure
3. Copies all 4 module files
4. Modifies controller files to add bulk import handlers
5. Injects required `use` statements
6. Verifies installation success

### After Installation

1. **Clear browser cache** (Ctrl+Shift+Delete)
2. **Log into OSPOS**
3. Go to **Items** → **Import Items** button
4. You should see two tabs: **Single File** and **Bulk Import**
5. Click **Bulk Import** tab
6. Select multiple CSV files
7. Click **Import All Files**

---

## 📖 How to Use

### Single File Import (Original)
- Select 1 CSV file
- Click Import
- View results

### Bulk Import (New)
1. Click **Bulk Import** tab
2. Click **Select Multiple CSV Files**
3. Choose as many CSVs as needed
4. Click **Import All Files**
5. Monitor progress bar (shows % complete)
6. View detailed results:
   - ✓ Number of files imported
   - ✗ Number of files that failed
   - 📊 Total items/customers imported
   - Per-file status with errors

**Features:**
- Processes files sequentially (not in parallel)
- One file failure doesn't stop others
- All OSPOS validation rules apply
- Mailchimp sync works for Customers
- Optional auto-archive of processed files

---

## 🛠️ What Gets Installed

**4 Files Copied:**
- `app/Libraries/BulkImport.php` (250 lines) – Core bulk processing logic
- `app/Views/items/form_csv_import.php` (475 lines) – Items import UI with bulk tab
- `app/Views/customers/form_csv_import.php` (475 lines) – Customers import UI with bulk tab
- `app/Language/en/BulkImport.php` (30 lines) – Translatable UI strings

**2 Controllers Modified:**
- `app/Controllers/Items.php` – Adds `postImportBulkCsvFiles()` method
- `app/Controllers/Customers.php` – Adds `postImportBulkCsvFiles()` method

**Nothing Deleted:** All original OSPOS code and features remain intact.

---

## ✨ Key Technical Features

- **No Code Duplication** – Reuses OSPOS's existing import validation
- **100% Safe** – Doesn't modify existing `postImportCsvFile()` method
- **Backward Compatible** – Single-file import works exactly as before
- **Full Feature Support** – Mailchimp sync, attributes, taxes, locations all work
- **Transactional Safety** – Files are validated before import
- **Easy to Revert** – Simply delete the 4 copied files and revert controller methods

---

## 🔧 Troubleshooting

**Bulk Import tab not switching?**
→ Clear browser cache completely (Ctrl+Shift+Delete) and reload OSPOS

**Files not importing?**
→ Open browser DevTools (F12) → Network tab → check if request to `/items/postImportBulkCsvFiles` returns 200
→ Ensure CSV headers match OSPOS template (download from "Download CSV Template" button)

**"Permission denied" when running installer?**
→ Ensure OSPOS folders are writable: `chmod 755 /path/to/ospos/app/Views`

**Imports are slow?**
→ Increase PHP timeout in `php.ini`: `max_execution_time = 600`

**Need to remove it?**
→ Just delete the 4 copied files and remove the added `postImportBulkCsvFiles()` methods from both controllers

---

## 📁 Package Contents

```
ospos-csv-import-automation/
├── install.php              ← Main installer (1 command!)
├── README.md                ← Documentation (this file)
├── LICENSE                  ← MIT License
├── files/app/               ← Module files to be copied
│   ├── libraries/
│   ├── views/
│   └── language/en/
├── patches/                 ← Reference patches (optional)
└── bin/                     ← CLI utilities
```

---

## 📊 How It Works

```
User selects 5 CSV files
              ↓
Click "Import All Files"
              ↓
BulkImport processes each file:
       ├─ file1.csv → 50 items ✓
       ├─ file2.csv → 75 items ✓
       ├─ file3.csv → FAILED ✗
       ├─ file4.csv → 30 items ✓
       └─ file5.csv → 60 items ✓
              ↓
Display aggregated results
       (3 success, 1 failed, 215 total imported)
```

---

## 📄 License

MIT License - See LICENSE file for full details

---

## 🙏 Acknowledgments

Created for efficient bulk data import in OSPOS. Built with attention to code quality, user experience, and production reliability.

---

**Version:** 2.0  
**OSPOS Compatibility:** 3.4.1+  
**Last Updated:** February 10, 2026  
(The old script is archived-works below ospos version 3.4)
Ready to bulk import? Just run `php install.php` and go! 🚀

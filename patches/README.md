# Patches Directory - Complete Guide

This directory contains the 4 patch files needed to integrate CSV import functionality into your existing OSPOS installation.

---

## 📋 Quick Overview

After applying these patches, the existing **Items → Import CSV** and **Customers → Import CSV** pages in OSPOS will be enhanced with:

✅ **Manual File Upload** - Upload single CSV files  
✅ **Auto-Import Folder** - Process multiple CSV files from a folder  
✅ **Import Reports** - Real-time results and error details  
✅ **File Organization** - Auto-move processed/failed files  

---

## 🎯 The 4 Patches

### 1️⃣ `items_controller.patch`

**What it patches:** `app/Controllers/Items.php`  
**What it does:** Adds CSV processing to the existing `import()` method

**Changes made:**
- Detects POST requests with `import_type` parameter
- Handles "manual" mode: processes single uploaded CSV file
- Handles "auto" mode: processes all CSV files in a folder
- Integrates ImportModel for database operations
- Returns import report to view

**Method modified:** `import()`  
**Method size:** Before: 2 lines → After: 35 lines  
**Dependencies:** ImportModel class

**Code location in your file:**
- Find the `import()` method (usually around line 15-30)
- Replace the entire method with the patch content

---

### 2️⃣ `items_view.patch`

**What it patches:** `app/Views/items/import.php`  
**What it does:** Enhances the Items import page with manual/auto toggle

**Changes made:**
- Adds radio button options: "Manual File Upload" vs "Auto-Import From Folder"
- Shows form conditionally based on selected radio button
- Manual form: file upload input with multipart encoding
- Auto form: folder path text input
- Displays import report with success/failure counts
- JavaScript for dynamic form toggling

**Method modified:** Entire file content  
**Lines changed:** Complete replacement (original 5-10 lines → enhanced 70+ lines)  
**Dependencies:** CSS (uses Bootstrap classes from OSPOS)

**File handling:**
- Backup original file first
- Replace entire content with patch content
- Don't worry about preserving old HTML - the patch is the complete file

---

### 3️⃣ `customers_controller.patch`

**What it patches:** `app/Controllers/Customers.php`  
**What it does:** Adds CSV processing to the existing `import()` method

**Changes made:**
- Identical logic to items_controller.patch
- Uses ImportCustomersModel instead of ImportModel
- Processes customer CSV files instead of item CSVs
- All other functionality is the same

**Method modified:** `import()`  
**Method size:** Before: 2 lines → After: 35 lines  
**Dependencies:** ImportCustomersModel class

**Code location in your file:**
- Find the `import()` method in Customers.php
- Replace the entire method with the patch content

---

### 4️⃣ `customers_view.patch`

**What it patches:** `app/Views/customers/import.php`  
**What it does:** Enhances the Customers import page with manual/auto toggle

**Changes made:**
- Identical structure to items_view.patch
- Updated folder placeholders for customers
- Updated labels for customers
- All other functionality is the same

**Method modified:** Entire file content  
**Lines changed:** Complete replacement  
**Dependencies:** CSS (uses Bootstrap classes from OSPOS)

---

## 📥 How to Apply Patches

### Method 1: Manual Copy-Paste (Windows/Mac/Linux) - Recommended

**For Items Controller:**
1. Open `patches/items_controller.patch` in a text editor
2. Open your `app/Controllers/Items.php` in an editor
3. Find the `import()` method (around line 15-30)
4. Copy the new method from the patch file
5. Replace the old `import()` method with the new one
6. Save `Items.php`

**For Items View:**
1. Open `patches/items_view.patch` in a text editor
2. Open your `app/Views/items/import.php`
3. Select ALL content in the view file
4. Delete all content
5. Copy the entire content from `items_view.patch`
6. Paste into the now-empty view file
7. Save `app/Views/items/import.php`

**Repeat for Customers (use the customers_* patch files)**

### Method 2: Git Patch Command (Linux/Mac) - Advanced

If you have Git and the `patch` command:

```bash
cd /path/to/ospos

# Apply Items controller patch
patch -p1 < /path/to/patches/items_controller.patch

# Apply Items view patch
patch -p1 < /path/to/patches/items_view.patch

# Apply Customers controller patch
patch -p1 < /path/to/patches/customers_controller.patch

# Apply Customers view patch
patch -p1 < /path/to/patches/customers_view.patch
```

If a patch fails to apply, 

 use Method 1 (manual) instead.

---

## ⚠️ Before You Start

### Backup Original Files

Make copies of the files you're about to patch:

**Items:**
- `app/Controllers/Items.php` → `Items.php.backup`
- `app/Views/items/import.php` → `import.php.backup`

**Customers:**
- `app/Controllers/Customers.php` → `Customers.php.backup`
- `app/Views/customers/import.php` → `import.php.backup`

You can restore these if something goes wrong.

### Requirements

✅ Models must be deployed first - Run `php install.php` before applying patches  
✅ Language file must be in place - installer creates `app/Language/english/import_lang.php`  
✅ Folders must exist - installer creates import_processed/ and import_failed/ directories  

---

## 🔍 What to Check After Applying

### After patching Items controller:

1. File should contain `use App\Models\ImportModel;` at top
2. `import()` method should have 35+ lines
3. Method should reference `$this->request->getPost('import_type')`
4. Should have both 'manual' and 'auto' handling

### After patching Items view:

1. File should start with `<h3><?= lang('items')`
2. Should contain radio button HTML: `<input type="radio" name="import_type"`
3. Should have two divs: `id="manual-upload"` and `id="auto-upload"`
4. Should have JavaScript at bottom: `document.querySelectorAll`

### After patching Customers controller:

1. File should contain `use App\Models\ImportCustomersModel;` at top
2. `import()` method should reference `ImportCustomersModel`
3. All other structure identical to Items

### After patching Customers view:

1. File should start with `<h3><?= lang('customers')`
2. Structure identical to Items view
3. Folder placeholder should mention customers

---

## ✅ Verification Steps

### Step 1: Verify Patched Items

1. In browser, navigate to **Items → Import CSV**
2. You should see two radio buttons:
   - ✓ "Manual File Upload"
   - ✓ "Auto-Import From Folder"
3. Select "Manual File Upload"
4. File input should appear
5. Select "Auto-Import From Folder"
6. Folder path input should appear
7. Select "Manual File Upload" again
8. File input should re-appear

### Step 2: Test Items Upload

1. Have `sample_items.csv` ready (provided with module)
2. Click "Manual File Upload"
3. Upload `sample_items.csv`
4. You should see Import Report showing:
   - Success: 10
   - Failed: 0
5. Check your database - 10 new items should exist

### Step 3: Verify Patched Customers

1. Navigate to **Customers → Import CSV**
2. Same radio buttons should appear
3. Verify both manual and auto modes work

### Step 4: Test Customers Upload

1. Have `sample_customers.csv` ready
2. Upload and verify
3. Check database for 10 new customers

---

## 🐛 Troubleshooting Patches

| Problem | Cause | Solution |
|---------|-------|----------|
| Radio buttons don't appear | View patch not applied correctly | Check file has input elements with name="import_type" |
| "Method not found" error | Controller patch incomplete | Verify import() method exists and is complete |
| File won't upload | View missing form encoding | Check for enctype="multipart/form-data" in manual form |
| Report won't display | Missing return statement | Check last line returns view with ['report' => $report] |
| Syntax error in controller | Patch applied incorrectly | Check PHP syntax using `php -l app/Controllers/Items.php` |
| 404 on /items/import | OSPOS doesn't have this route | Add to app/Config/Routes.php if needed |

---

## 🔄 If You Need to Revert

### Restore from Backup

```bash
# Restore Items
cp Items.php.backup app/Controllers/Items.php
cp import.php.backup app/Views/items/import.php

# Restore Customers
cp Customers.php.backup app/Controllers/Customers.php
cp import_customers.php.backup app/Views/customers/import.php
```

The models and language files can stay in place - they won't interfere with OSPOS.

---

## 📚 Related Documentation

- **INTEGRATION_INSTRUCTIONS.md** - Step-by-step guide (detailed version of this file)
- **PATCH_REFERENCE.md** - Quick patch file reference
- **INTEGRATION_GUIDE.md** - Complete integration guide with code examples
- **README.md** - Main user guide with troubleshooting
- **QUICK_START.md** - 5-minute quickstart

---

## ✨ What Each Patch Enables

| Feature | Items | Customers |
|---------|-------|-----------|
| Manual file upload | items_controller + items_view | customers_controller + customers_view |
| Auto-folder import | items_controller + items_view | customers_controller + customers_view |
| Import reports | items_controller + items_view | customers_controller + customers_view |
| CSV parsing | ImportModel (via installer) | ImportCustomersModel (via installer) |
| File organization | ImportModel (via installer) | ImportCustomersModel (via installer) |

---

## 🎯 Using These Patches

### Purpose: Integrate import into existing pages
- Patches enhance existing Items & Customers pages
- No new routes needed
- Uses existing OSPOS permissions
- Clean integration

### Alternative: Use Pre-built Controllers
- If you prefer, standalone controllers are available in `files/app/controllers/`
- Requires adding routes and menu items
- Not recommended - uses more resources

---

## 📞 Support

**For patch application issues:**  
→ See [INTEGRATION_INSTRUCTIONS.md](../INTEGRATION_INSTRUCTIONS.md)

**For general integration questions:**  
→ See [INTEGRATION_GUIDE.md](../INTEGRATION_GUIDE.md)

**For troubleshooting:**  
→ See [README.md](../README.md) Troubleshooting section

---

## 📋 Patch File Checklist

Before starting, verify all 4 patches are present:

- [ ] `items_controller.patch` - ✅ Present
- [ ] `items_view.patch` - ✅ Present
- [ ] `customers_controller.patch` - ✅ Present
- [ ] `customers_view.patch` - ✅ Present

---

## ✅ Ready to Patch?

1. **Backup original files** (see "Before You Start" section above)
2. **Run installer** - `php install.php` (if not done already)
3. **Apply patches** - Follow [INTEGRATION_INSTRUCTIONS.md](../INTEGRATION_INSTRUCTIONS.md)
4. **Verify** - Test with sample CSV files
5. **Troubleshoot** - Refer to [README.md](../README.md) if issues

---

**Patches Status:** ✅ Complete and Ready  
**Quality:** ✅ Production Ready  
**Last Updated:** February 2026  
**Version:** 1.0.0  

Ready to enhance your OSPOS? 🚀

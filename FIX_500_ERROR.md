# Fix 500 Error - Unit Management

## Issue
Getting 500 error when accessing Unit Management page.

## Possible Causes & Solutions

### 1. Migration Not Run (Most Likely)

**Problem:** The `units` table doesn't exist in the database yet.

**Solution:**
```bash
cd admin_panel
php artisan migrate
```

**Expected Output:**
```
Migrating: 2025_01_08_000001_create_units_table
Migrated:  2025_01_08_000001_create_units_table
```

**Verify:**
```bash
php artisan tinker
>>> Schema::hasTable('units');
# Should return: true
>>> \App\Model\Unit::count();
# Should return: 5
```

---

### 2. Cache Issues

**Problem:** Old cached routes/views causing conflicts.

**Solution:**
```bash
cd admin_panel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

---

### 3. Autoload Issues

**Problem:** New classes not autoloaded.

**Solution:**
```bash
cd admin_panel
composer dump-autoload
```

---

### 4. Check Laravel Logs

**Location:** `admin_panel/storage/logs/laravel.log`

**View Last Error:**
```bash
# On Windows PowerShell
Get-Content admin_panel/storage/logs/laravel.log -Tail 50

# On Linux/Mac
tail -50 admin_panel/storage/logs/laravel.log
```

---

## Step-by-Step Fix

### Step 1: Clear All Caches
```bash
cd admin_panel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 2: Run Migration
```bash
php artisan migrate
```

If you get "Nothing to migrate", check if migration already ran:
```bash
php artisan migrate:status
```

### Step 3: Verify Database
```bash
php artisan tinker
```

Then run:
```php
// Check if table exists
Schema::hasTable('units');

// Check if units exist
\App\Model\Unit::count();

// Get all units
\App\Model\Unit::all();

// Exit tinker
exit
```

### Step 4: Verify Routes
```bash
php artisan route:list | grep unit
```

**Expected Output:**
```
GET|HEAD  admin/business-settings/unit/management ........... admin.business-settings.unit.index
POST      admin/business-settings/unit/store ................ admin.business-settings.unit.store
GET|HEAD  admin/business-settings/unit/edit/{id} ............ admin.business-settings.unit.edit
POST      admin/business-settings/unit/update ............... admin.business-settings.unit.update
GET|HEAD  admin/business-settings/unit/toggle-status/{id} ... admin.business-settings.unit.toggle-status
DELETE    admin/business-settings/unit/delete/{id} .......... admin.business-settings.unit.delete
```

### Step 5: Test Access
```bash
# Start server if not running
php artisan serve
```

Then navigate to:
```
http://localhost:8000/admin/business-settings/unit/management
```

---

## Common Errors & Fixes

### Error: "Class 'App\Model\Unit' not found"

**Fix:**
```bash
composer dump-autoload
php artisan cache:clear
```

---

### Error: "Table 'database.units' doesn't exist"

**Fix:**
```bash
php artisan migrate
```

---

### Error: "Route [admin.business-settings.unit.index] not defined"

**Fix:**
```bash
php artisan route:clear
php artisan cache:clear
```

---

### Error: "View [admin-views.business-settings.unit-management] not found"

**Fix:**
```bash
php artisan view:clear
```

Check if file exists:
```
admin_panel/resources/views/admin-views/business-settings/unit-management.blade.php
```

---

## Quick Fix Script

Create a file `fix.bat` (Windows) or `fix.sh` (Linux/Mac):

**Windows (fix.bat):**
```batch
@echo off
cd admin_panel
echo Clearing caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo.
echo Running migration...
php artisan migrate
echo.
echo Dumping autoload...
composer dump-autoload
echo.
echo Done! Try accessing the page now.
pause
```

**Linux/Mac (fix.sh):**
```bash
#!/bin/bash
cd admin_panel
echo "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo ""
echo "Running migration..."
php artisan migrate
echo ""
echo "Dumping autoload..."
composer dump-autoload
echo ""
echo "Done! Try accessing the page now."
```

Run it:
```bash
# Windows
fix.bat

# Linux/Mac
chmod +x fix.sh
./fix.sh
```

---

## Verify Everything Works

### 1. Check Files Exist
```
✓ admin_panel/app/Model/Unit.php
✓ admin_panel/app/Http/Controllers/Admin/UnitController.php
✓ admin_panel/database/migrations/2025_01_08_000001_create_units_table.php
✓ admin_panel/resources/views/admin-views/business-settings/unit-management.blade.php
```

### 2. Check Database
```sql
-- Connect to your database
SHOW TABLES LIKE 'units';

-- Should show the units table
SELECT * FROM units;

-- Should show 5 default units
```

### 3. Check Routes
```bash
php artisan route:list | grep unit
```

### 4. Check Logs
```bash
# Check for errors
cat admin_panel/storage/logs/laravel.log | grep ERROR
```

---

## Still Getting Error?

### Get Detailed Error Info

**Enable Debug Mode:**

Edit `admin_panel/.env`:
```env
APP_DEBUG=true
```

**Refresh the page** - you'll see detailed error message.

**Important:** Set back to `false` in production!

---

### Check Specific Issues

**1. Check Controller:**
```bash
php artisan tinker
>>> $controller = new \App\Http\Controllers\Admin\UnitController();
>>> echo "Controller loaded successfully!";
```

**2. Check Model:**
```bash
php artisan tinker
>>> $unit = new \App\Model\Unit();
>>> echo "Model loaded successfully!";
```

**3. Check View:**
```bash
php artisan tinker
>>> view()->exists('admin-views.business-settings.unit-management');
# Should return: true
```

---

## Manual Database Setup (If Migration Fails)

If migration fails, create table manually:

```sql
CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(50) NOT NULL,
  `unit_short_name` varchar(20) NOT NULL,
  `unit_type` enum('weight','volume','length','piece','other') DEFAULT 'other',
  `is_active` tinyint(1) DEFAULT 1,
  `is_default` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_unit_short_name_unique` (`unit_short_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default units
INSERT INTO `units` (`unit_name`, `unit_short_name`, `unit_type`, `is_active`, `is_default`, `sort_order`, `created_at`, `updated_at`) VALUES
('Kilogram', 'kg', 'weight', 1, 1, 1, NOW(), NOW()),
('Gram', 'gm', 'weight', 1, 1, 2, NOW(), NOW()),
('Liter', 'ltr', 'volume', 1, 1, 3, NOW(), NOW()),
('Milliliter', 'ml', 'volume', 1, 1, 4, NOW(), NOW()),
('Piece', 'pc', 'piece', 1, 1, 5, NOW(), NOW());
```

---

## Contact Support

If still having issues, provide:

1. **Error message** from `storage/logs/laravel.log`
2. **Output of:**
   ```bash
   php artisan route:list | grep unit
   php artisan migrate:status
   ```
3. **Database name** and **Laravel version**:
   ```bash
   php artisan --version
   ```

---

## Success Indicators

✅ No errors in logs  
✅ Migration ran successfully  
✅ Routes are registered  
✅ Page loads without 500 error  
✅ Default units are displayed  

---

**Most Common Fix:**
```bash
cd admin_panel
php artisan migrate
php artisan cache:clear
```

**Then refresh the page!**


# Quick View Modal - Troubleshooting Guide

## Issue: Quick View button not working

If clicking the "Quick View" button doesn't open the modal, follow these troubleshooting steps:

---

## ✅ Step 1: Hard Refresh Browser (Most Common Fix)

The browser may be caching old JavaScript. Clear the cache:

### Chrome/Edge/Firefox
- **Windows**: Press `Ctrl + Shift + R` or `Ctrl + F5`
- **Mac**: Press `Cmd + Shift + R`

### Alternative: Clear Browser Cache
1. Open DevTools (F12)
2. Right-click the refresh button
3. Select "Empty Cache and Hard Reload"

---

## ✅ Step 2: Verify Assets Are Built

Run in terminal:

```bash
cd /d/WHS5
npm run build
php artisan view:clear
php artisan config:clear
```

---

## ✅ Step 3: Check Browser Console

1. Open DevTools (F12)
2. Go to **Console** tab
3. Click "Quick View" button
4. Look for errors

### Expected behavior:
- No errors in console
- Network tab shows request to `/teams/{id}/quick-view`
- Modal should appear

### Common errors:

#### Error: "bootstrap is not defined"
**Fix**: Vite build issue. Run `npm run build`

#### Error: "Uncaught TypeError: Cannot read properties of null"
**Fix**: Modal element not found. Run `php artisan view:clear`

#### Error: Network request fails (404)
**Fix**: Route not registered. Run `php artisan route:clear`

---

## ✅ Step 4: Verify Button HTML

Right-click "Quick View" button → Inspect Element

### Should look like:
```html
<button type="button" class="whs-action-btn"
        data-quick-view
        data-member-id="123"
        aria-label="Quick view Harper Collins">
  <i class="icon-base ti ti-eye"></i>
  <span>Quick View</span>
</button>
```

### ❌ If missing `data-quick-view` or `data-member-id`:
- Run `php artisan view:clear`
- Hard refresh browser (Ctrl+Shift+R)

---

## ✅ Step 5: Check Modal Exists in DOM

In DevTools Console, run:

```javascript
document.getElementById('employeeQuickViewModal')
```

### Expected: Returns the modal element
### ❌ If returns `null`:
- Run `php artisan view:clear`
- Hard refresh browser

---

## ✅ Step 6: Test JavaScript Handler

In DevTools Console, run:

```javascript
// Test if event listener is attached
const btn = document.querySelector('[data-quick-view]');
console.log(btn); // Should show button element

// Test click manually
if (btn) {
  btn.click(); // Should open modal
}
```

---

## ✅ Step 7: Verify Route Works

Test the API endpoint directly in browser:

```
http://127.0.0.1:8002/teams/1/quick-view
```

### Expected: JSON response like:
```json
{
  "success": true,
  "html": "...",
  "member_id": 1,
  "view_url": "http://127.0.0.1:8002/teams/1",
  "edit_url": "http://127.0.0.1:8002/teams/1/edit"
}
```

### ❌ If 404 error:
```bash
php artisan route:clear
php artisan route:list --name=teams.quick-view
```

---

## ✅ Step 8: Check for JavaScript Errors

In DevTools → Sources tab:

1. Find `public/build/assets/app-*.js`
2. Search for `employeeQuickViewModal`
3. Set breakpoint on line 241 (modal initialization)
4. Refresh page
5. Breakpoint should hit (confirms JS loaded)

---

## 🔧 Nuclear Option: Full Cache Clear

If all else fails:

```bash
cd /d/WHS5

# Clear all Laravel caches
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear

# Rebuild assets
npm run build

# Restart development server
# Stop current server (Ctrl+C)
php artisan serve --host=127.0.0.1 --port=8002
```

Then:
1. Close ALL browser tabs for `127.0.0.1:8002`
2. Clear browser cache completely
3. Open fresh browser window
4. Navigate to `http://127.0.0.1:8002/teams`
5. Test Quick View button

---

## ✅ Verification Checklist

After troubleshooting, verify:

- [ ] Hard refresh browser (Ctrl+Shift+R)
- [ ] No console errors in DevTools
- [ ] Button has `data-quick-view` attribute (inspect element)
- [ ] Modal element exists in DOM
- [ ] Route `/teams/{id}/quick-view` works
- [ ] Assets built successfully (`npm run build`)
- [ ] Laravel caches cleared
- [ ] Development server running

---

## 📊 Quick Diagnostic Test

Run this in browser DevTools Console:

```javascript
// Diagnostic test
console.log('=== Quick View Diagnostic ===');
console.log('Modal exists:', !!document.getElementById('employeeQuickViewModal'));
console.log('Buttons found:', document.querySelectorAll('[data-quick-view]').length);
console.log('Bootstrap loaded:', typeof window.bootstrap !== 'undefined');

// Test button
const testBtn = document.querySelector('[data-quick-view]');
if (testBtn) {
  console.log('First button ID:', testBtn.dataset.memberId);
  console.log('Button clickable:', testBtn.offsetParent !== null);
} else {
  console.error('❌ No quick view buttons found!');
}
```

### Expected Output:
```
=== Quick View Diagnostic ===
Modal exists: true
Buttons found: 3
Bootstrap loaded: true
First button ID: "1"
Button clickable: true
```

---

## 🆘 Still Not Working?

Check these files for correctness:

1. **Button**: `resources/views/content/TeamManagement/Index.blade.php:219`
2. **Modal**: `resources/views/layouts/commonMaster.blade.php:30-64`
3. **JavaScript**: `resources/js/app.js:238-304`
4. **Route**: `routes/web.php:296`
5. **Controller**: `app/Modules/TeamManagement/Controllers/TeamController.php:412`

All files should match the implementation from `QUICK_VIEW_REFACTOR.md`.

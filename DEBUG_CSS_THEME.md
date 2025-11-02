# CSS Theme Debug Instructions

## Step 1: Open Browser DevTools Console

1. Press `F12` on the vehicles page
2. Go to **Console** tab
3. Paste this code and press Enter:

```javascript
// Check what CSS is actually loaded
const hero = document.querySelector('.whs-hero');
const card = document.querySelector('.whs-card');
const chip = document.querySelector('.whs-chip');

if (hero) {
  const heroStyle = window.getComputedStyle(hero);
  console.group('🎭 WHS Hero Styles');
  console.log('Background:', heroStyle.background);
  console.log('Border color:', heroStyle.borderColor);
  console.log('Box shadow:', heroStyle.boxShadow);
  console.groupEnd();
}

if (card) {
  const cardStyle = window.getComputedStyle(card);
  console.group('🃏 WHS Card Styles');
  console.log('Background:', cardStyle.background);
  console.log('Border color:', cardStyle.borderColor);
  console.log('Box shadow:', cardStyle.boxShadow);
  console.groupEnd();
}

if (chip) {
  const chipStyle = window.getComputedStyle(chip);
  console.group('🏷️ WHS Chip Styles');
  console.log('Background:', chipStyle.background);
  console.log('Border color:', chipStyle.borderColor);
  console.groupEnd();
}

// Check if CSS file is cached
console.group('📄 CSS File Loading');
const cssLink = document.querySelector('link[href*="sensei-theme"]');
if (cssLink) {
  console.log('CSS File:', cssLink.href);
  console.log('Full URL:', cssLink.href);
}
console.groupEnd();
```

## Step 2: Check Network Tab

1. Go to **Network** tab in DevTools
2. Filter by "CSS"
3. Refresh the page (F5)
4. Look for `sensei-theme-DZhGvaI8.css`
5. Check the **Size** column:
   - If it says "(disk cache)" or "(memory cache)" → **CACHED (BAD)**
   - If it shows actual size like "84.9 kB" → **FRESH (GOOD)**

## Step 3: Force Hard Reload

If the file shows as cached:

1. Open DevTools (F12)
2. **Right-click the refresh button** (while DevTools is open)
3. Select **"Empty Cache and Hard Reload"**

## Step 4: Nuclear Option - Disable Cache

1. With DevTools open (F12)
2. Go to **Network** tab
3. Check the **"Disable cache"** checkbox
4. Refresh the page

## Step 5: Incognito Mode Test

1. Open new **Incognito/Private window** (Ctrl + Shift + N)
2. Navigate to `http://127.0.0.1:8002/vehicles`
3. Check if backgrounds are light

If incognito works, it's definitely a cache issue in regular mode.

## Expected Output (Light Theme)

### Hero
- **Border color**: `rgba(15, 23, 42, 0.12)` (dark borders)
- **Box shadow**: Contains `rgba(15, 23, 42, ...)` (light shadow)

### Card
- **Border color**: `rgba(15, 23, 42, 0.12)` (dark borders)
- **Background**: Should use `--sensei-surface` token

### Chip
- **Background**: `rgba(255, 255, 255, 0.9)` (white background)
- **Border color**: `rgba(15, 23, 42, 0.15)` (dark border)

## If Still Dark (Wrong Values)

### Hero
- **Border color**: `rgba(255, 255, 255, 0.08)` ❌ WHITE (dark theme)
- **Box shadow**: Contains `rgba(12, 18, 26, ...)` ❌ DARK shadow

### Chip
- **Background**: `rgba(34, 41, 54, 0.85)` ❌ DARK background

This means the browser is using the OLD CSS file.

## Solution: Clear Site Data

1. Open DevTools (F12)
2. Go to **Application** tab (Chrome/Edge) or **Storage** tab (Firefox)
3. Left sidebar → **Storage** → Click **"Clear site data"**
4. Check ALL boxes:
   - ✅ Cookies and site data
   - ✅ Cached images and files
   - ✅ Local storage
5. Click **"Clear site data"**
6. Close DevTools
7. Refresh page

---

## Alternative: Manual Cache Clear

If the above doesn't work:

1. Close ALL browser tabs/windows
2. Open browser settings
3. Search for "Clear browsing data"
4. Select **"Cached images and files"**
5. Time range: **"Last 24 hours"**
6. Click **"Clear data"**
7. Restart browser
8. Navigate to `http://127.0.0.1:8002/vehicles`
